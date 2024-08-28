<?php

namespace App\Models;

class Message_group_members_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'message_group_members';
        parent::__construct($this->table);
    }

    function save_member($data = array(), $id = 0) {
        $user_id = $this->_get_clean_value($data, "user_id");
        $message_group_id = $this->_get_clean_value($data, "message_group_id");
        $group_name = $this->_get_clean_value($data, "group_name");
        if (!$user_id) {
            return false;
        }

        $exists = $this->get_one_where($where = array("user_id" => $user_id, "message_group_id" => $message_group_id));
        // print_r($exists);
        if ($exists->id && $exists->deleted == 0) {
            //already exists
            return "exists";
        } else if ($exists->id && $exists->deleted == 1) {
            //undelete the record
            if (parent::delete($exists->id, true)) {
                return $exists->id;
            }
        } else {
            //add new
            return parent::ci_save($data, $id);
        }
    }

    function delete($id = 0, $undo = false) {
        return parent::delete($id, $undo);
    }

    function get_details($options = array()) {
        $message_group_members_table = $this->db->prefixTable('message_group_members');
        $users_table = $this->db->prefixTable('users');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $message_group_members_table.id=$id";
        }

        $message_group_id = $this->_get_clean_value($options, "message_group_id");
        if ($message_group_id) {
            $where .= " AND $message_group_members_table.message_group_id=$message_group_id";
        }

        $user_type = $this->_get_clean_value($options, "user_type");
        $show_user_wise = $this->_get_clean_value($options, "show_user_wise");
        if ($show_user_wise) {
            if ($user_type == "client_contacts") {
                $where .= " AND $message_group_members_table.user_id IN (SELECT $users_table.id FROM $users_table WHERE $users_table.deleted=0 AND $users_table.user_type='client')";
            } else {
                $where .= " AND $message_group_members_table.user_id IN (SELECT $users_table.id FROM $users_table WHERE $users_table.deleted=0 AND $users_table.user_type='staff')";
            }
        }

        $sql = "SELECT $message_group_members_table.*, CONCAT($users_table.first_name, ' ',$users_table.last_name) AS member_name, $users_table.image as member_image, $users_table.job_title, $users_table.user_type
        FROM $message_group_members_table
        LEFT JOIN $users_table ON $users_table.id= $message_group_members_table.user_id
        WHERE $message_group_members_table.deleted=0 $where";
        return $this->db->query($sql);
    }

    function get_message_group_members_dropdown_list($message_group_id = 0, $user_ids = array(), $add_client_contacts = false, $show_active_users_only = false) {
        $message_group_members_table = $this->db->prefixTable('message_group_members');
        $users_table = $this->db->prefixTable('users');

        $where = " AND $message_group_members_table.message_group_id=$message_group_id";

        if (is_array($user_ids) && count($user_ids)) {
            $users_list = join(",", $user_ids);
            $where .= " AND $users_table.id IN($users_list)";
        }

        $user_where = "";
        if (!$add_client_contacts) {
            $user_where .= " AND $users_table.user_type='staff'";
        }

        if ($show_active_users_only) {
            $user_where .= " AND $users_table.status='active'";
        }

        if ($user_where) {
            $where .= " AND $message_group_members_table.user_id IN (SELECT $users_table.id FROM $users_table WHERE $users_table.deleted=0 $user_where)";
        }

        $sql = "SELECT $message_group_members_table.user_id, CONCAT($users_table.first_name, ' ',$users_table.last_name) AS member_name, $users_table.status AS member_status, $users_table.user_type
        FROM $message_group_members_table
        LEFT JOIN $users_table ON $users_table.id= $message_group_members_table.user_id
        WHERE 1=1 $where 
        GROUP BY $message_group_members_table.user_id 
        ORDER BY $users_table.user_type, $users_table.first_name ASC";
        return $this->db->query($sql);
    }

    function get_rest_team_members_for_a_group($message_group_id = 0) {
        $message_group_members_table = $this->db->prefixTable('message_group_members');
        $users_table = $this->db->prefixTable('users');

        $where = "";

        if($message_group_id != 0)
        {
            $where = " AND $users_table.id NOT IN (SELECT $message_group_members_table.user_id FROM $message_group_members_table WHERE $message_group_members_table.message_group_id=$message_group_id  AND deleted=0)";
        }

        $sql = "SELECT $users_table.id, CONCAT($users_table.first_name, ' ',$users_table.last_name) AS member_name, $users_table.user_type
        FROM $users_table
        LEFT JOIN $message_group_members_table ON $message_group_members_table.user_id=$users_table.id
        WHERE $users_table.status='active' AND $users_table.deleted=0 $where 
        GROUP BY $users_table.id ORDER BY $users_table.first_name ASC";

        return $this->db->query($sql);
    }

    function get_message_statistics($options = array()) {
        $message_group_members_table = $this->db->prefixTable('message_group_members');
        $users_table = $this->db->prefixTable('users');

        $info = new \stdClass();

        $where = "";
        $offset = convert_seconds_to_time_format(get_timezone_offset());

        $user_id = $this->_get_clean_value($options, "user_id");
        if ($user_id) {
            $where .= " AND $message_group_members_table.user_id=$user_id";
        }

        $group_id = $this->_get_clean_value($options, "group_id");
        if ($group_id) {
            $where .= " AND $message_group_members_table.message_group_id=$group_id";
        }

        //ignor sql mode here 
        try {
            $this->db->query("SET sql_mode = ''");
        } catch (\Exception $e) {
            
        }

        $group_users_data = "SELECT CONCAT($users_table.first_name, ' ',$users_table.last_name) AS user_name, $users_table.image as user_avatar
                FROM $message_group_members_table 
                LEFT JOIN $users_table ON $users_table.id = $message_group_members_table.user_id
                WHERE $message_group_members_table.deleted=0 $where
                GROUP BY $message_group_members_table.user_id";

        $info->group_users_data = $this->db->query($group_users_data)->getResult();
        return $info;
    }

}
