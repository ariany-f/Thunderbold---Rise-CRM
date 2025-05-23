<?php

namespace App\Models;

class Messages_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'messages';
        parent::__construct($this->table);
    }

    /*
     * prepare details info of a message
     */

    function get_details($options = array()) {
        $messages_table = $this->db->prefixTable('messages');
        $users_table = $this->db->prefixTable('users');
        $message_group_members_table = $this->db->prefixTable('message_group_members');
        $message_groups_table = $this->db->prefixTable('message_groups');
        $tasks_table = $this->db->prefixTable('tasks');
        $task_status_table = $this->db->prefixTable('task_status');

        $mode = $this->_get_clean_value($options, "mode");

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $messages_table.id=$id";
        }

        $message_id = $this->_get_clean_value($options, "message_id");
        if ($message_id) {
            $where .= " AND $messages_table.message_id=$message_id";
        }

        $user_id = $this->_get_clean_value($options, "user_id");
        if ($user_id) {
            $where .= " AND ($messages_table.from_user_id=$user_id OR $messages_table.to_user_id=$user_id OR $message_group_members_table.user_id=$user_id) ";
        }


        $join_with = "$messages_table.from_user_id";
        $join_another = "$messages_table.to_user_id";
        if ($user_id && $mode === "inbox") {
            $where .= " AND $messages_table.message_id=0 ";
        } else if ($user_id && $mode === "sent_items") {
            $where .= " AND $messages_table.message_id=0 ";
            $join_with = "$messages_table.to_user_id";
            $join_another = "$messages_table.from_user_id";
        }

        $last_message_id = $this->_get_clean_value($options, "last_message_id");
        if ($last_message_id) {
            $where .= " AND $messages_table.id>$last_message_id";
        }


        $top_message_id = $this->_get_clean_value($options, "top_message_id");
        if ($top_message_id) {
            $where .= " AND $messages_table.id<$top_message_id";
        }

        $limit = $this->_get_clean_value($options, "limit");
        $limit = $limit ? $limit : "30";
        $offset = $this->_get_clean_value($options, "offset");
        $offset = $offset ? $offset : "0";

        $sql = "SELECT * FROM 
            (SELECT 0 AS reply_message_id,
            COALESCE($message_groups_table.group_name, '') AS group_name, 
            COALESCE($message_groups_table.id, '') AS group_id, 
            COALESCE($message_groups_table.project_id, '') AS project_id, 
            $messages_table.*, 
            COALESCE($tasks_table.title, '') AS task_title,
            COALESCE($tasks_table.status, '') AS task_status,
            COALESCE($task_status_table.key_name, '') AS task_status_key_name,
            COALESCE($tasks_status_table.color, '') AS task_status_color,
            CONCAT($users_table.first_name, ' ', $users_table.last_name) AS user_name, 
            $users_table.image AS user_image, 
            $users_table.user_type, 
            CONCAT(another_user.first_name, ' ', another_user.last_name) AS another_user_name, 
            another_user.id AS another_user_id, 
            another_user.last_online AS another_user_last_online
        FROM $messages_table
        LEFT JOIN $tasks_table ON $tasks_table.id=$messages_table.task_id AND $tasks_table.deleted=0
        LEFT JOIN $task_status_table ON $tasks_table.status_id = $task_status_table.id 
        LEFT JOIN $users_table ON $users_table.id=$join_with
        LEFT JOIN $users_table AS another_user ON another_user.id=$join_another
        LEFT JOIN $message_groups_table ON $message_groups_table.id=$messages_table.to_group_id
        LEFT JOIN $message_group_members_table ON $message_group_members_table.message_group_id=$message_groups_table.id
        LEFT JOIN $users_table AS group_user ON group_user.id=$message_group_members_table.user_id
        WHERE $messages_table.deleted=0 $where
        GROUP BY $messages_table.id ORDER BY $messages_table.id DESC LIMIT $offset, $limit) new_message ORDER BY id ASC";

        $query = $this->db->query($sql);

        $data = new \stdClass();
        $data->result = $query->getResult();
        $data->row = $query->getRow();
        $data->found_rows = 0;

        if ($message_id) {
            $data->found_rows = $this->db->query("SELECT COUNT(id) AS found_rows FROM $messages_table WHERE $messages_table.message_id = $message_id")->getRow()->found_rows;
        }

        return $data;
    }

    /*
     * prepare inbox/sent items list
     */
     function get_list($options = array()) {
        $messages_table = $this->db->prefixTable('messages');
        $users_table = $this->db->prefixTable('users');
        $projects_table = $this->db->prefixTable('projects');
        $message_groups_table = $this->db->prefixTable('message_groups');
        $message_group_members_table = $this->db->prefixTable('message_group_members');
    
        $mode = $this->_get_clean_value($options, "mode");
        $user_id = $this->_get_clean_value($options, "user_id");
    
        if ($user_id && $mode === "inbox") {
            $where_user = "to_user_id = $user_id";
            $select_user = "from_user_id";
            $where_group = "";  // Não precisa de verificação de grupo no modo "inbox"
        } else if ($user_id && $mode === "sent_items") {
            $where_user = "from_user_id = $user_id";
            $select_user = "to_user_id";
            $where_group = "";  // Não precisa de verificação de grupo no modo "sent_items"
        } else if ($user_id && $mode === "list_groups") {
            $where_user = "";
            $select_user = "from_user_id";
            $where_group = "to_group_id IN (
                SELECT $message_groups_table.id 
                FROM $message_groups_table 
                INNER JOIN $message_group_members_table 
                ON $message_group_members_table.message_group_id = $message_groups_table.id 
                WHERE $message_group_members_table.user_id = $user_id
            )";
        }

        $group_id = $this->_get_clean_value($options, "group_id");
        if ($group_id) {
            $where_group .= " AND ($messages_table.to_group_id=$group_id) ";
        }

        $message_id = $this->_get_clean_value($options, "message_id");
        if ($message_id) {
            $where_group .= " AND ($messages_table.id=$message_id) ";
        }
    
        $where = "$where_user $where_group";
    
        $user_ids = $this->_get_clean_value($options, "user_ids");
        if ($user_ids) {
            $where .= " AND $select_user IN($user_ids)";
        }
    
        $notification_sql = "";
        $is_notification = $this->_get_clean_value($options, "is_notification");
        if ($is_notification) {
            $notification_sql = " ORDER BY timestamp($messages_table.created_at) DESC LIMIT 10 ";
        }

        $limit_offset = "";
        $limit = $this->_get_clean_value($options, "limit");
        if ($limit) {
            $skip = $this->_get_clean_value($options, "skip");
            $offset = $skip ? $skip : 0;
            $limit_offset = " LIMIT $limit OFFSET $offset ";
        }
    
        // Ignorar sql mode aqui 
        $this->db->query("SET sql_mode = ''");
    
        if($mode != 'list_groups')
        {
            $sql = "SELECT SQL_CALC_FOUND_ROWS y.*, $projects_table.is_ticket, $message_groups_table.project_id, COALESCE($message_groups_table.group_name, '') AS group_name, $messages_table.status, $messages_table.read_by, $messages_table.created_at, $messages_table.files, $messages_table.ended,
                        CONCAT($users_table.first_name, ' ', $users_table.last_name) AS user_name, $users_table.image AS user_image, $users_table.last_online
                    FROM (
                        SELECT max(x.id) as id, main_message_id, subject, 
                            IF(subject='', (SELECT subject FROM $messages_table WHERE id=main_message_id), '') as reply_subject, 
                            $select_user
                        FROM (
                            SELECT id, IF(message_id=0, id, message_id) as main_message_id, subject, $select_user 
                            FROM $messages_table
                            WHERE deleted=0 AND ($where) 
                            AND FIND_IN_SET($user_id, $messages_table.deleted_by_users) = 0
                        ) x
                        GROUP BY main_message_id
                    ) y
                    LEFT JOIN $users_table ON $users_table.id = y.$select_user
                    LEFT JOIN $messages_table ON $messages_table.id = y.id 
                    LEFT JOIN $message_groups_table ON $message_groups_table.id=$messages_table.to_group_id
                    LEFT JOIN $message_group_members_table ON $message_group_members_table.message_group_id=$message_groups_table.id
                    LEFT JOIN $projects_table ON $projects_table.id = $message_groups_table.project_id
                    GROUP BY $messages_table.id 
                    $notification_sql $limit_offset";
        }
        else
        {
            $sql = "SELECT y.*, $projects_table.is_ticket, $message_groups_table.project_id, COUNT(DISTINCT CASE 
                            WHEN $message_group_members_table.deleted = 0 THEN $message_group_members_table.user_id 
                        END) AS count_members,  COALESCE($message_groups_table.group_name, '') AS group_name, 
                        $messages_table.status, $messages_table.read_by, $messages_table.created_at, $messages_table.files, $messages_table.ended, $messages_table.from_user_id,
                        CONCAT(another_user.first_name, ' ', another_user.last_name) AS another_user_name, 
                        another_user.image AS another_user_image,
                        another_user.id AS another_user_id, 
                        another_user.last_online AS another_user_last_online,
                        CONCAT($users_table.first_name, ' ', $users_table.last_name) AS user_name, 
                        $users_table.image AS user_image, 
                        $users_table.last_online
                    FROM (
                        SELECT max(x.id) as id, main_message_id, subject, task_id, 
                            IF(subject='', (SELECT subject FROM $messages_table WHERE id=main_message_id), '') as reply_subject, 
                            $select_user
                        FROM (
                            SELECT id, IF(message_id=0, id, message_id) as main_message_id, task_id, subject, $select_user 
                            FROM $messages_table
                            WHERE deleted=0
                            AND FIND_IN_SET($user_id, $messages_table.deleted_by_users) = 0
                        ) x
                        GROUP BY main_message_id
                    ) y
                    LEFT JOIN $users_table ON $users_table.id = y.$select_user
                    LEFT JOIN $messages_table ON $messages_table.id = y.id 
                    LEFT JOIN $users_table AS another_user ON another_user.id = $messages_table.$select_user
                    RIGHT JOIN $message_groups_table ON $message_groups_table.id=$messages_table.to_group_id
                    LEFT JOIN $message_group_members_table ON $message_group_members_table.message_group_id=$message_groups_table.id
                    LEFT JOIN $projects_table ON $projects_table.id = $message_groups_table.project_id
                    WHERE $where_group
                    GROUP BY y.main_message_id 
                    $notification_sql";
        }
    
        $raw_query = $this->db->query($sql);

        $total_rows = $this->db->query("SELECT FOUND_ROWS() as found_rows")->getRow();

        if ($limit) {
            return array(
                "data" => $raw_query->getResult(),
                "recordsTotal" => $total_rows->found_rows,
                "recordsFiltered" => $total_rows->found_rows,
            );
        } else {
            return $raw_query;
        }
    }
    

    function get_chat_list($options = array()) {

        $messages_table = $this->db->prefixTable('messages');
        $users_table = $this->db->prefixTable('users');
        $message_groups_table = $this->db->prefixTable('message_groups');
        $message_group_members_table = $this->db->prefixTable('message_group_members');

        $login_user_id = $this->_get_clean_value($options, "login_user_id");

        $where = "";
        $user_id = $this->_get_clean_value($options, "user_id");
        if ($user_id) {
            $where .= " AND ($messages_table.to_user_id=$user_id OR $messages_table.from_user_id=$user_id) ";
        }

        $group_id = $this->_get_clean_value($options, "group_id");
        if ($group_id) {
            $where .= " AND ($messages_table.to_group_id=$group_id) ";
        }

        $ended = $this->_get_clean_value($options, "ended");
        if ($ended) {
            $where .= " AND ($messages_table.ended=$ended) ";
        }

        $user_ids = $this->_get_clean_value($options, "user_ids");
        if ($user_ids) {
            $where .= " AND ($messages_table.to_user_id IN($user_ids) OR $messages_table.from_user_id IN($user_ids))";
        }

        $this->db->query("SET sql_mode = ''"); //ignor sql mode here

        $sql = "SELECT $messages_table.id, $messages_table.ended, COALESCE($message_groups_table.group_name, '') AS group_name, $messages_table.subject, $messages_table.from_user_id, IF(another_m.mex_created_at, another_m.mex_created_at, $messages_table.created_at) AS message_time, 
                IF(another_m.status, another_m.status, $messages_table.status) AS status, (SELECT from_user_id FROM $messages_table WHERE $messages_table.id=another_m.max_id) AS last_from_user_id,
                CONCAT($users_table.first_name, ' ', $users_table.last_name) AS user_name, $users_table.image AS user_image, $users_table.last_online
                FROM $messages_table
                LEFT JOIN (SELECT MAX(id) as max_id, MAX(message_id) as mex_message_id, MAX(created_at) as mex_created_at, MAX(status) as status FROM $messages_table WHERE deleted=0 and  message_id!=0 GROUP BY message_id) AS another_m ON $messages_table.id=another_m.mex_message_id
                LEFT JOIN $users_table ON ($users_table.id=$messages_table.from_user_id OR $users_table.id=$messages_table.to_user_id) AND $users_table.id != $login_user_id
                LEFT JOIN $message_groups_table ON $message_groups_table.id = $messages_table.to_group_id
                LEFT JOIN $message_group_members_table ON $message_group_members_table.message_group_id = $message_groups_table.id AND $message_group_members_table.user_id = $login_user_id
                WHERE $messages_table.deleted=0 AND $messages_table.message_id=0 $where AND
                FIND_IN_SET($login_user_id, $messages_table.deleted_by_users) = 0 AND ($messages_table.from_user_id=$login_user_id OR $messages_table.to_user_id=$login_user_id OR $message_group_members_table.id IS NOT NULL) 
                GROUP BY id
                ORDER BY message_time DESC LIMIT 0, 30";

        return $this->db->query($sql);
    }

    function count_notifications($user_id, $last_message_checke_at = "0", $active_message_id = 0, $user_ids = "") {
        $messages_table = $this->db->prefixTable('messages');
        $message_group_members_table = $this->db->prefixTable('message_group_members');

        $where = "";
        if ($active_message_id) {
            $where = " AND $messages_table.message_id!=$active_message_id";
        }

        if ($user_ids) {
            $where .= " AND ($messages_table.to_user_id IN($user_ids) OR $messages_table.from_user_id IN($user_ids)) ";
        }

        $sql = "SELECT COUNT($messages_table.id) AS total_notifications
        FROM $messages_table
        WHERE $messages_table.deleted=0 AND $messages_table.status='unread' 
        AND (
                $messages_table.to_user_id = $user_id 
            OR 
                $messages_table.to_group_id IN (SELECT $message_group_members_table.message_group_id FROM $message_group_members_table WHERE $message_group_members_table.user_id = $user_id)
        )
        AND timestamp($messages_table.created_at)>timestamp('$last_message_checke_at') $where
        ORDER BY timestamp($messages_table.created_at) DESC";

        $result = $this->db->query($sql);
        if ($result->resultID->num_rows) {
            return $result->getRow()->total_notifications;
        }
    }

    /* update message ustats */

    function set_message_status_as_read($message_id, $user_id = 0) {
        $messages_table = $this->db->prefixTable('messages');
        $message_group_members_table = $this->db->prefixTable('message_group_members');
        
        // Obter o valor atual da coluna read_by
        $query = $this->db->query("SELECT read_by FROM $messages_table WHERE (message_id = $message_id OR id = $message_id) AND (FIND_IN_SET($user_id, $messages_table.read_by) = 0 OR status = 'unread')");
        $row = $query->getRow();
        
        // Se a coluna read_by já tiver valores, concatenar o novo user_id
        if($row)
        {
            $current_read_by = $row->read_by;
            if ($current_read_by) {
                $new_read_by = $current_read_by . ',' . $user_id;
            } else {
                $new_read_by = $user_id;
            }
        
            // Atualizar a coluna read_by e status
            $sql = "UPDATE $messages_table 
                    SET status = 'read', 
                        read_by = '$new_read_by' 
                    WHERE (to_user_id = $user_id
                    OR to_group_id IN (SELECT $message_group_members_table.message_group_id FROM $message_group_members_table WHERE $message_group_members_table.user_id = $user_id))
                    AND (message_id = $message_id OR id = $message_id)";
            
            return $this->db->query($sql);
        }
    }

    function count_unread_group_message($user_id = 0, $user_ids = "") {
        $messages_table = $this->db->prefixTable('messages');
        $message_group_members_table = $this->db->prefixTable('message_group_members');
        $message_groups_table = $this->db->prefixTable('message_groups');

        $where = "";
        if ($user_ids) {
            $where .= " AND ($messages_table.to_group_id IN(SELECT $message_group_members_table.message_group_id FROM $message_group_members_table WHERE $message_group_members_table.user_id IN ($user_ids))) ";
        }

        $sql = "SELECT COUNT($messages_table.id) as total
        FROM $messages_table
        WHERE 
            $messages_table.deleted=0 AND 
            $messages_table.from_user_id <> $user_id AND 
            (
                $messages_table.status='unread' OR 
                FIND_IN_SET($user_id, $messages_table.read_by) = 0
            ) AND 
            (
                $messages_table.to_group_id IN (SELECT $message_group_members_table.message_group_id FROM $message_group_members_table INNER JOIN $message_groups_table ON $message_group_members_table.message_group_id = $message_groups_table.id  WHERE $message_groups_table.deleted = 0 AND $message_group_members_table.user_id = $user_id)
            ) $where";
        return $this->db->query($sql)->getRow()->total;
    }


    function count_unread_inbox_message($user_id = 0, $user_ids = "") {
        $messages_table = $this->db->prefixTable('messages');
        $message_group_members_table = $this->db->prefixTable('message_group_members');

        $where = "";
        if ($user_ids) {
            $where .= " AND ($messages_table.to_user_id IN($user_ids) OR $messages_table.from_user_id IN($user_ids)) ";
        }

        $sql = "SELECT COUNT($messages_table.id) as total
        FROM $messages_table
        WHERE $messages_table.deleted=0 AND $messages_table.from_user_id <> $user_id AND ($messages_table.status='unread' OR FIND_IN_SET($user_id, $messages_table.read_by) = 0) AND $messages_table.to_user_id = $user_id $where";
        return $this->db->query($sql)->getRow()->total;
    }

    function count_unread_message($user_id = 0, $user_ids = "") {
        $messages_table = $this->db->prefixTable('messages');
        $message_group_members_table = $this->db->prefixTable('message_group_members');

        $where = "";
        if ($user_ids) {
            $where .= " AND ($messages_table.to_user_id IN($user_ids) OR $messages_table.from_user_id IN($user_ids) OR $messages_table.to_group_id IN(SELECT $message_group_members_table.message_group_id FROM $message_group_members_table WHERE $message_group_members_table.user_id IN ($user_ids))) ";
        }

        $sql = "SELECT COUNT(DISTINCT COALESCE($messages_table.message_id, $messages_table.id)) as total
        FROM $messages_table
        WHERE 
            $messages_table.deleted=0 AND 
            $messages_table.from_user_id <> $user_id AND 
            ($messages_table.status='unread' OR FIND_IN_SET($user_id, $messages_table.read_by) = 0) AND 
            ($messages_table.to_user_id = $user_id OR $messages_table.to_group_id IN 
                (SELECT $message_group_members_table.message_group_id 
                    FROM $message_group_members_table WHERE $message_group_members_table.user_id = $user_id)
                ) 
            $where";
        return $this->db->query($sql)->getRow()->total;
    }

    function delete_messages_for_user($message_id = 0, $user_id = 0) {
        $messages_table = $this->db->prefixTable('messages');

        $sql = "UPDATE $messages_table SET $messages_table.deleted_by_users = CONCAT($messages_table.deleted_by_users,',',$user_id)
        WHERE $messages_table.id=$message_id OR $messages_table.message_id=$message_id";
        return $this->db->query($sql);
    }

    function delete_messages($message_id = 0) {
        $messages_table = $this->db->prefixTable('messages');

        $sql = "UPDATE $messages_table SET $messages_table.deleted = 1
        WHERE $messages_table.id=$message_id OR $messages_table.message_id=$message_id";
        return $this->db->query($sql);
    }

    function reactive_messages_for_user($message_id = 0, $user_id = 0) {
        $messages_table = $this->db->prefixTable('messages');

        $sql = "UPDATE $messages_table SET $messages_table.ended = 0
        WHERE $messages_table.id=$message_id OR $messages_table.message_id=$message_id";
        return $this->db->query($sql);
    }

    function end_messages_for_user($message_id = 0, $user_id = 0) {
        $messages_table = $this->db->prefixTable('messages');

        $sql = "UPDATE $messages_table SET $messages_table.ended = 1
        WHERE $messages_table.id=$message_id OR $messages_table.message_id=$message_id";
        return $this->db->query($sql);
    }

    function clear_deleted_status($message_id = 0) {
        $messages_table = $this->db->prefixTable('messages');

        $sql = "UPDATE $messages_table SET $messages_table.deleted_by_users = ''
        WHERE $messages_table.id=$message_id OR $messages_table.message_id=$message_id";
        return $this->db->query($sql);
    }

    function get_users_for_messaging($options = array()) {
        $users_table = $this->db->prefixTable('users');
        $clients_table = $this->db->prefixTable('clients');

        $where = "";

        $login_user_id = $this->_get_clean_value($options, "login_user_id");

        $all_members = $this->_get_clean_value($options, "all_members");
        $specific_members = $this->_get_clean_value($options, "specific_members");
        $member_to_clients = $this->_get_clean_value($options, "member_to_clients");
        if ($all_members) {
            if ($member_to_clients) {
                $where .= " AND ($users_table.user_type='staff' OR $users_table.user_type='client')";
            } else {
                $where .= " AND $users_table.user_type='staff'";
            }
        } else if ($specific_members) {
            if (is_array($specific_members) && count($specific_members)) {
                $specific_members = join(",", $specific_members);
            } else {
                $specific_members = '0';
            }

            if ($member_to_clients) {
                $where .= " AND ($users_table.id IN($specific_members) OR $users_table.user_type='client')";
            } else {
                $where .= " AND $users_table.id IN($specific_members)";
            }
        }

        if ($member_to_clients && !$all_members && !$specific_members) {
            $where .= " AND $users_table.user_type='client'";
        }

        $client_to_members = $this->_get_clean_value($options, "client_to_members");
        $client_id = $this->_get_clean_value($options, "client_id");
        if ($client_to_members) {
            if ($client_id) {
                $where .= " AND (FIND_IN_SET($users_table.id, '$client_to_members') OR $users_table.client_id=$client_id)";
            } else {
                $where .= " AND FIND_IN_SET($users_table.id, '$client_to_members')";
            }
        }

        if ($client_id && !$client_to_members) {
            $where .= " AND $users_table.client_id=$client_id";
        }

        $sql = "SELECT $users_table.id, $users_table.client_id, $users_table.user_type, $users_table.first_name, $users_table.last_name, $clients_table.company_name,
            $users_table.image,  $users_table.job_title, $users_table.last_online
        FROM $users_table
        LEFT JOIN $clients_table ON $clients_table.id = $users_table.client_id AND $clients_table.deleted=0
        WHERE $users_table.deleted=0 AND $users_table.status='active' AND $users_table.id!=$login_user_id $where
        ORDER BY $users_table.user_type, $users_table.first_name ASC";

        return $this->db->query($sql);
    }
}
