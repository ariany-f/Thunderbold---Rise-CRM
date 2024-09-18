<?php

namespace App\Models;

class Help_categories_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'help_categories';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $help_categories_table = $this->db->prefixTable('help_categories');
        $help_articles_table = $this->db->prefixTable('help_articles');
        $client_groups_table = $this->db->prefixTable('client_groups');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $help_categories_table.id=$id";
        }

        $type = $this->_get_clean_value($options, "type");
        if ($type) {
            $where .= " AND $help_categories_table.type='$type'";
        }

        
        $only_active_categories = $this->_get_clean_value($options, "only_active_categories");
        if ($only_active_categories) {
            $where .= " AND $help_categories_table.status='active'";
        }

        $client_group_ids = $this->_get_clean_value($options, "client_group_ids");
        $user_type = $this->_get_clean_value($options, "user_type");
        $where .= $this->prepare_share_with_query($help_categories_table, $user_type, $client_group_ids);

        
        $sql = "SELECT $help_categories_table.*, 
                (SELECT GROUP_CONCAT($client_groups_table.title) FROM $client_groups_table WHERE FIND_IN_SET(CONCAT('cg:', $client_groups_table.id), $help_categories_table.share_with)) AS client_groups,
                (SELECT COUNT($help_articles_table.id) AS total_articles FROM $help_articles_table WHERE $help_articles_table.category_id=$help_categories_table.id AND $help_articles_table.deleted=0 AND  $help_articles_table.status='active') AS total_articles
        FROM $help_categories_table
        WHERE $help_categories_table.deleted=0 $where 
        ORDER BY $help_categories_table.sort";
        return $this->db->query($sql);
    }

    private function prepare_share_with_query($help_categories_table, $user_type, $client_group_ids) {
        $where = "";
        if ($user_type) { //if no user type found, we'll assume the user has permission to access all
            if ($user_type === "staff") {
                $where = " AND FIND_IN_SET('all_members',$help_categories_table.share_with)";
            } else {
                $client_groups_where = "";

                if($client_group_ids)
                {
                    $client_group_ids = explode(',', $client_group_ids);
                    foreach ($client_group_ids as $group_id) {
                        $client_groups_where .= " OR FIND_IN_SET('cg:$group_id', $help_categories_table.share_with)";
                    }
                }

                $where = " AND (FIND_IN_SET('all_clients', $help_categories_table.share_with) $client_groups_where )";
            }
        }

        return $where;
    }

}
