<?php

namespace App\Models;

class Project_resources_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'project_resources';
        parent::__construct($this->table);
    }

    function save_resource($data = array(), $id = 0) {
        $user_id = $this->_get_clean_value($data, "user_id");
        $project_id = $this->_get_clean_value($data, "project_id");
        if (!$user_id || !$project_id) {
            return false;
        }

        if($id !== 0)
        {
            return parent::ci_save($data, $id);
        }

        $exists = $this->get_one_where($where = array("user_id" => $user_id, "project_id" => $project_id));

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
        $project_resources_table = $this->db->prefixTable('project_resources');
        $users_table = $this->db->prefixTable('users');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $project_resources_table.id=$id";
        }

        $user_id = $this->_get_clean_value($options, "user_id");
        if ($user_id) {
            $where .= " AND $project_resources_table.user_id=$user_id";
        }

        $project_id = $this->_get_clean_value($options, "project_id");
        if ($project_id) {
            $where .= " AND $project_resources_table.project_id=$project_id";
        }

        $is_leader = $this->_get_clean_value($options, "is_leader");
        if ($is_leader) {
            $where .= " AND $project_resources_table.is_leader=$is_leader";
        }

        $sql = "SELECT $project_resources_table.*, $users_table.image as resource_avatar, CONCAT($users_table.first_name, ' ',$users_table.last_name) AS resource_name, $users_table.image as resource_image, $users_table.job_title, $users_table.user_type
        FROM $project_resources_table
        LEFT JOIN $users_table ON $users_table.id= $project_resources_table.user_id
        WHERE $project_resources_table.deleted=0 $where";
        return $this->db->query($sql);
    }

    function get_project_resources_dropdown_list($project_id = 0, $user_ids = array(), $add_client_contacts = false, $show_active_users_only = false) {
        $project_resources_table = $this->db->prefixTable('project_resources');
        $users_table = $this->db->prefixTable('users');

        $where = " AND $project_resources_table.project_id=$project_id";

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
            $where .= " AND $project_resources_table.user_id IN (SELECT $users_table.id FROM $users_table WHERE $users_table.deleted=0 $user_where)";
        }

        $sql = "SELECT $project_resources_table.user_id, $users_table.image as resource_avatar, CONCAT($users_table.first_name, ' ',$users_table.last_name) AS resource_name, $users_table.status AS resource_status, $users_table.user_type
        FROM $project_resources_table
        LEFT JOIN $users_table ON $users_table.id= $project_resources_table.user_id
        WHERE $project_resources_table.deleted=0 $where 
        GROUP BY $project_resources_table.user_id 
        ORDER BY $users_table.user_type, $users_table.first_name ASC";
        return $this->db->query($sql);
    }

    function is_user_a_project_resource($project_id = 0, $user_id = 0) {
        $info = $this->get_one_where(array("project_id" => $project_id, "user_id" => $user_id, "deleted" => 0));
        if ($info->id) {
            return true;
        }
    }

    function get_rest_team_resources_for_a_project($project_id = 0) {
        $project_resources_table = $this->db->prefixTable('project_resources');
        $users_table = $this->db->prefixTable('users');

        $sql = "SELECT $users_table.id, $users_table.image as resource_avatar, CONCAT($users_table.first_name, ' ',$users_table.last_name) AS resource_name
        FROM $users_table
        LEFT JOIN $project_resources_table ON $project_resources_table.user_id=$users_table.id
        WHERE $users_table.user_type='staff' AND $users_table.status='active' AND $users_table.deleted=0
        ORDER BY $users_table.first_name ASC";

        return $this->db->query($sql);
    }

    function get_client_contacts_of_the_project_client($project_id = 0) {
        $project_resources_table = $this->db->prefixTable('project_resources');
        $users_table = $this->db->prefixTable('users');
        $projects_table = $this->db->prefixTable('projects');

        $sql = "SELECT $users_table.id, CONCAT($users_table.first_name, ' ',$users_table.last_name) AS contact_name
        FROM $users_table
        LEFT JOIN $project_resources_table ON $project_resources_table.user_id=$users_table.id
        WHERE $users_table.user_type='client' AND $users_table.deleted=0 AND $users_table.client_id=(SELECT $projects_table.client_id FROM $projects_table WHERE $projects_table.id=$project_id) AND $users_table.id NOT IN (SELECT $project_resources_table.user_id FROM $project_resources_table WHERE $project_resources_table.project_id='$project_id' AND deleted=0)
        ORDER BY $users_table.first_name ASC";

        return $this->db->query($sql);
    }

}
