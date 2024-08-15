<?php

namespace App\Models;

class Message_groups_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'message_groups';
        parent::__construct($this->table);
    }

    
    function schema() {
        return array(
            "id" => array(
                "label" => app_lang("id"),
                "type" => "int"
            ),
            "group_name" => array(
                "label" => app_lang("group_name"),
                "type" => "text"
            ),
            "collaborators" => array(
                "label" => app_lang("collaborators"),
                "type" => "foreign_key",
                "link_type" => "user_group_list",
                "linked_model" => model("App\Models\Users_model"),
                "label_fields" => array("user_group_name"),
            ),
        );
    }

    /*
     * prepare details info of a message
     */
    function get_details($options = array()) {
        $messages_table = $this->db->prefixTable('messages');
        $message_groups_table = $this->db->prefixTable('message_groups');
        $message_group_members_table = $this->db->prefixTable('message_group_members');
        $users_table = $this->db->prefixTable('users');
     
        $where = "1=1";

        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $message_groups_table.id=$id";
        }

        $user_id = $this->_get_clean_value($options, "user_id");
        if ($user_id) {
            $where .= " AND ($messages_table.from_user_id=$user_id OR $messages_table.to_user_id=$user_id OR $message_group_members_table.user_id=$user_id) ";
        }

        $order = "";
        $sort_by_group_name = $this->_get_clean_value($options, "sort_by_group_name");
        if ($sort_by_group_name) {
            $order = " ORDER BY $message_groups_table.group_name ASC";
        }

        $available_order_by_list = array(
            "id" => $message_groups_table . ".id",
            "group_name" => $message_groups_table . ".group_name"
        );

        $order_by = get_array_value($available_order_by_list, $this->_get_clean_value($options, "order_by"));

        if ($order_by) {
            $order_dir = $this->_get_clean_value($options, "order_dir");
            $order = " ORDER BY $order_by $order_dir ";
        }

        //prepare custom fild binding query
        $custom_fields = get_array_value($options, "custom_fields");
        $custom_field_filter = get_array_value($options, "custom_field_filter");
        $custom_field_query_info = $this->prepare_custom_field_query_string("tasks", $custom_fields, $message_groups_table, $custom_field_filter);
        $select_custom_fieds = get_array_value($custom_field_query_info, "select_string");
      

        $this->db->query('SET SQL_BIG_SELECTS=1');

        $sql = "SELECT SQL_CALC_FOUND_ROWS $message_groups_table.*
        FROM $message_groups_table
        LEFT JOIN $messages_table ON $messages_table.to_group_id = $message_groups_table.id
        LEFT JOIN $message_group_members_table ON $message_group_members_table.message_group_id = $message_groups_table.id
        WHERE $where 
        $order";

        $raw_query = $this->db->query($sql);

        $total_rows = $this->db->query("SELECT FOUND_ROWS() as found_rows")->getRow();

     
        return $raw_query;
    }


    function get_groups_for_messaging($options = array()) {
        $groups_table = $this->db->prefixTable('message_groups');
        $message_group_members_table = $this->db->prefixTable('message_group_members');
        $projects_table = $this->db->prefixTable('projects');
    
        $where = "1=1";
    
        $user_id = $this->_get_clean_value($options, "user_id");
        if ($user_id) {
            $where .= " AND $message_group_members_table.user_id=$user_id";
        }
    
        $sql = "SELECT 
                    $groups_table.id, 
                    $groups_table.group_name, 
                    $groups_table.project_id, 
                    $projects_table.is_ticket as is_ticket,
                    (SELECT COUNT(*) FROM $message_group_members_table WHERE $message_group_members_table.message_group_id = $groups_table.id) as member_count
                FROM 
                    $groups_table
                LEFT JOIN 
                    $message_group_members_table 
                    ON $message_group_members_table.message_group_id = $groups_table.id
                LEFT JOIN 
                    $projects_table 
                    ON $projects_table.id = $groups_table.project_id
                WHERE 
                    $where 
                GROUP BY 
                    $groups_table.id 
                ORDER BY 
                    $groups_table.id ASC";
    
        return $this->db->query($sql);
    }
}
