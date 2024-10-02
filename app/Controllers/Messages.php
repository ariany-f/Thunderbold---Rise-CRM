<?php

namespace App\Controllers;

class Messages extends Security_Controller {

    function __construct() {
        parent::__construct();
        $this->init_permission_checker("message_permission");
    }

    private function is_my_message($message_info) {
        $options = ['user_id' => $this->login_user->id];
        $groups = $this->Message_groups_model->get_groups_for_messaging($options)->getResult();
        $groups = json_decode(json_encode($groups), true); //convert to array

        if(isset($message_info->from_user_id))
        {
            if ($message_info->from_user_id == $this->login_user->id || $message_info->to_user_id == $this->login_user->id || in_array($message_info->to_group_id, array_column($groups, "id"))) {
                return true;
            }
        }
    }

    private function check_message_user_permission() {
        if (!$this->check_access_on_messages_for_this_user()) {
            app_redirect("forbidden");
        }
    }

    private function check_validate_sending_message($to_user_id, $to_group_id) {
        if (!$this->validate_sending_message($to_user_id, $to_group_id)) {
            echo json_encode(array("success" => false, 'message' => app_lang("message_sending_error_message")));
            exit;
        }
    }

    function index() {
        $this->check_message_user_permission();
        app_redirect("messages/list_groups");
    }

    function message_group_member_modal_form($message_group_id = 0) {

        $users_dropdown[] = app_lang('select');
        
        if($message_group_id === 0) {
            $message_group_id = $this->request->getPost('id');
        }
        $view_data['model_info'] = $this->Message_groups_model->get_one($message_group_id );
      
        $message_group_id = $this->request->getPost('message_group_id') ? $this->request->getPost('message_group_id') : $view_data['model_info']->id;

        $view_data['message_group_id'] = $message_group_id;

        $view_data["view_type"] = $this->request->getPost("view_type") ?? 'groups';

        $add_user_type = $this->request->getPost("add_user_type");

        $users = $this->Message_group_members_model->get_rest_team_members_for_a_group($message_group_id)->getResult();
        
        foreach ($users as $user) {
            $users_dropdown[$user->id] = $user->member_name . " - " . app_lang($user->user_type);
        }

        $view_data["users_dropdown"] = $users_dropdown;
        $view_data["add_user_type"] = $add_user_type;
        return $this->template->view('messages/group_members/modal_form', $view_data);
    }

    /* list of group members, prepared for datatable  */

    function group_member_list_data($group_id = 0, $user_type = "") {
        validate_numeric_value($group_id);
        $this->access_only_team_members();

        //show the message icon to client contacts list only if the user can send message to client. 
        $can_send_message_to_client = false;
        $client_message_users = get_setting("client_message_users");
        $client_message_users_array = explode(",", $client_message_users);
        if (in_array($this->login_user->id, $client_message_users_array)) {

            $can_send_message_to_client = true;
        }

        $options = array("message_group_id" => $group_id, "user_type" => $user_type, "show_user_wise" => false);
        $list_data = $this->Message_group_members_model->get_details($options)->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_group_member_row($data, $can_send_message_to_client);
        }
        echo json_encode(array("data" => $result));
    }

    /* return a row of group member list */

    private function _group_member_row_data($id) {
        $options = array("id" => $id);
        $data = $this->Message_group_members_model->get_details($options)->getRow();
        return $this->_make_group_member_row($data);
    }

    /* prepare a row of group member list */
    private function _make_group_member_row($data, $can_send_message_to_client = false) {
        $member_image = "<span class='avatar avatar-sm'><img src='" . get_avatar($data->member_image, $data->member_name) . "' alt='...'></span> ";

        if ($data->user_type == "staff") {
            $member = get_team_member_profile_link($data->user_id, $member_image);
            $member_name = get_team_member_profile_link($data->user_id, $data->member_name, array("class" => "dark strong"));
        } else {
            $member = get_client_contact_profile_link($data->user_id, $member_image);
            $member_name = get_client_contact_profile_link($data->user_id, $data->member_name, array("class" => "dark strong"));
        }

        $link = "";

        $delete_link = js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete_member'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("messages/delete_group_member"), "data-action" => "delete"));

        $link .= $delete_link;

        $member = '<div class="d-flex"><div class="p-2 flex-shrink-1">' . $member . '</div><div class="p-2 w-100"><div>' . $member_name . '</div><label class="text-off">' . $data->job_title . '</label></div></div>';

        return array($member, $link);
    }

    /* delete/undo a group members  */
    function delete_group_member() {
        $id = $this->request->getPost('id');
        $group_member_info = $this->Message_group_members_model->get_one($id);

      
        if ($this->request->getPost('undo')) {
            if ($this->Message_group_members_model->delete($id, true)) {
                echo json_encode(array("success" => true, "data" => $this->_group_member_row_data($id), "message" => app_lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, app_lang('error_occurred')));
            }
        } else {
            if ($this->Message_group_members_model->delete($id)) {

                $group_member_info = $this->Message_group_members_model->get_one($id);

                log_notification("group_member_deleted", array("message_group_id" => $group_member_info->message_group_id, "to_user_id" => $group_member_info->user_id));
                echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
            }
        }
    }

    /* add a message group members  */
    function save_message_group_member() {
        $message_group_id = $this->request->getPost('message_group_id');

        $this->validate_submitted_data(array(
            "user_id.*" => "required"
        ));

        $user_ids = $this->request->getPost('user_id');

        $save_ids = array();
        $already_exists = false;

        if ($user_ids) {
            foreach ($user_ids as $user_id) {
                if ($user_id) {
                    $data = array(
                        "message_group_id" => $message_group_id,
                        "user_id" => $user_id
                    );

                    $save_id = $this->Message_group_members_model->save_member($data);
                    if ($save_id && $save_id != "exists") {
                        $save_ids[] = $save_id;
                        log_notification("message_group_member_added", array("message_group_id" => $message_group_id, "to_user_id" => $user_id));
                    } else if ($save_id === "exists") {
                        $already_exists = true;
                    }
                }
            }
        }


        if (!count($save_ids) && $already_exists) {
            //this member already exists.
            echo json_encode(array("success" => true, 'id' => "exists"));
        } else if (count($save_ids)) {
            $project_member_row = array();
            foreach ($save_ids as $id) {
                $project_member_row[] = $this->_message_group_member_row_data($id);
            }
            echo json_encode(array("success" => true, "data" => $project_member_row, 'id' => $save_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    
    private function _message_group_member_row_data($id) {
        $options = array("id" => $id);
        $data = $this->Message_group_members_model->get_details($options)->getRow();
        return $this->_make_message_group_member_row($data);
    }

    private function _make_message_group_member_row($data) {
        $member_image = "<span class='avatar avatar-sm'><img src='" . get_avatar($data->member_image, $data->member_name) . "' alt='...'></span> ";

        if ($data->user_type == "staff") {
            $member = get_team_member_profile_link($data->user_id, $member_image);
            $member_name = get_team_member_profile_link($data->user_id, $data->member_name, array("class" => "dark strong"));
        } else {
            $member = get_client_contact_profile_link($data->user_id, $member_image);
            $member_name = get_client_contact_profile_link($data->user_id, $data->member_name, array("class" => "dark strong"));
        }

        $link = "";

        if ($this->can_add_remove_message_group_members()) {
            $delete_link = js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete_member'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("projects/delete_project_member"), "data-action" => "delete"));

            if (!$this->can_manage_all_projects() && ($this->login_user->id === $data->user_id)) {
                $delete_link = "";
            }
            $link .= $delete_link;
        }

        $member = '<div class="d-flex"><div class="p-2 flex-shrink-1">' . $member . '</div><div class="p-2 w-100"><div>' . $member_name . '</div><label class="text-off">' . $data->job_title . '</label></div></div>';

        return array($member, $link);
    }

    
    private function can_add_remove_message_group_members() {
        if ($this->login_user->user_type == "staff") {
            if ($this->login_user->is_admin) {
                return true;
            } else {
                if (get_array_value($this->login_user->permissions, "show_assigned_tasks_only") !== "1") {
                    if ($this->can_manage_all_projects()) {
                        return true;
                    } else if (get_array_value($this->login_user->permissions, "can_add_remove_message_group_members") == "1") {
                        return true;
                    }
                }
            }
        }
    }

    /* return a row of message group list  table */

    private function _group_row_data($id) {
      
        $options = array(
            "id" => $id
        );

        $data = $this->Message_groups_model->get_details($options)->getRow();
        return $this->_group_make_row($data);
    }

      /* prepare a row of project list table */

      private function _group_make_row($data) {

        $optoins = "";
        if ($this->can_edit_projects($data->id)) {
            $optoins .= modal_anchor(get_uri("messages/groups_modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_project'), "data-post-id" => $data->id));
        }

        $row_data = array(
            $data->group_name,
        );

        $row_data[] = $optoins;

        return $row_data;
    }

    function save_group_client_message() {

        $id = $this->request->getPost('to_group_id');
        $group_name = $this->request->getPost('group_name');
        $message_id = 0;
        $data = array();

       $this->validate_submitted_data(array(
           "subject" => "required",
            "message" => "required",
            "to_group_id" => "required"
       ));


       if (!$id) {
            $data = array(
                "group_name" => $this->login_user->first_name . " - Cliente",
            );

           $data["created_date"] = get_current_utc_time();
           $data["created_by"] = $this->login_user->id;

           $data = clean_data($data);
    
           $save_id = $this->Message_groups_model->ci_save($data, $id);
       }
       else
       {
            if($group_name)
            {
                $data = array(
                    "group_name" => $group_name
                );

                $data = clean_data($data);
    
                $save_id = $this->Message_groups_model->ci_save($data, $id);
            }
            else
            {
                $save_id = $id;
            }
       }

       if ($save_id) {
           if (!$id) {
               if ($this->login_user->user_type === "client") {
                   //this is a new project and created by team members
                   //add default project member after project creation
                   $data = array(
                       "message_group_id" => $save_id,
                       "user_id" => $this->login_user->id
                   );
                   $this->Message_group_members_model->save_member($data);
            
                    // Adicionar o usuário role 3 (admin) ao grupo
                    $admin_user = $this->Users_model->get_staff_member();
                    if ($admin_user) {
                        $admin_data = array(
                            "message_group_id" => $save_id,
                            "user_id" => $admin_user->id
                        );
                        $this->Message_group_members_model->save_member($admin_data);
                    }
               }

               log_notification("message_group_created", array("message_group_id" => $save_id));

                // Criar a mensagem "GRUPO CRIADO" no grupo
                $message_data = array(
                    "message" => $this->request->getPost('message'), // Mensagem que será enviada
                    "subject" => $this->request->getPost('subject'), // Assunto da mensagem
                    "from_user_id" => $this->login_user->id, // Quem criou a mensagem
                    "to_group_id" => $save_id, // Grupo recém-criado
                    "to_user_id" => 0,
                    "created_at" => get_current_utc_time(),
                    "status" => "unread", // Definir como não lida inicialmente
                    "deleted" => 0
                );
                
                $target_path = get_setting("timeline_file_path");
                $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "message");

                $message_data = clean_data($message_data);
                $message_data["files"] = $files_data; //don't clean serilized data

                $this->Messages_model->ci_save($message_data); // Salvar a mensagem no grupo
           }
           else
           {
                $message_data = array(
                    "message" => $this->request->getPost('message'), // Mensagem que será enviada
                    "subject" => $this->request->getPost('subject'), // Assunto da mensagem
                    "from_user_id" => $this->login_user->id, // Quem criou a mensagem
                    "to_group_id" => $save_id, // Grupo recém-criado
                    "to_user_id" => 0,
                    "created_at" => get_current_utc_time(),
                    "status" => "unread", // Definir como não lida inicialmente
                    "deleted" => 0
                );
                
                $target_path = get_setting("timeline_file_path");
                $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "message");

                $message_data = clean_data($message_data);
                $message_data["files"] = $files_data; //don't clean serilized data

                $message_id = $this->Messages_model->ci_save($message_data); // Salvar a mensagem no grupo
           }

           if($message_id !== 0)
           {
                $options = array("message_id" => $message_id, "group_id" => $save_id, "user_id" => $this->login_user->id, "mode" => "list_groups", "user_ids" => $this->get_allowed_user_ids());
           }
           else
           {
                $options = array("group_id" => $save_id, "user_id" => $this->login_user->id, "mode" => "list_groups", "user_ids" => $this->get_allowed_user_ids());
           }

           $list_data = $this->Messages_model->get_list($options)->getResult();
   
           $result = array();
   
           foreach ($list_data as $data) {
               $result[] = $this->_make_row($data, "list_groups");
           }

           echo json_encode(array("success" => true, "data" => $result, 'id' => $save_id, 'message' => app_lang('record_saved')));
       } else {
           echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
       }
   }
    
    function save_group() {

        $id = $this->request->getPost('id');

       $this->validate_submitted_data(array(
           "group_name" => "required"
       ));

      
       $data = array(
           "group_name" => $this->request->getPost('group_name')
       );

       if (!$id) {
           $data["created_date"] = get_current_utc_time();
           $data["created_by"] = $this->login_user->id;
       }

       $data = clean_data($data);
  
       $save_id = $this->Message_groups_model->ci_save($data, $id);

       if ($save_id) {
           if (!$id) {
               if ($this->login_user->user_type === "staff") {
                   //this is a new project and created by team members
                   //add default project member after project creation
                   $data = array(
                       "message_group_id" => $save_id,
                       "user_id" => $this->login_user->id
                   );
                   $this->Message_group_members_model->save_member($data);
               }

               log_notification("message_group_created", array("message_group_id" => $save_id));

                // Criar a mensagem "GRUPO CRIADO" no grupo
                $message_data = array(
                    "message" => "Mensagem automática de criação de grupo", // Mensagem que será enviada
                    "subject" => "Grupo criado", // Assunto da mensagem
                    "from_user_id" => $this->login_user->id, // Quem criou a mensagem
                    "to_group_id" => $save_id, // Grupo recém-criado
                    "created_at" => get_current_utc_time(),
                    "status" => "unread", // Definir como não lida inicialmente
                    "deleted" => 0
                );
                
                $target_path = get_setting("timeline_file_path");
                $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "message");

                $message_data = clean_data($message_data);
                $message_data["files"] = $files_data; //don't clean serilized data

                $this->Messages_model->ci_save($message_data); // Salvar a mensagem no grupo
           }

           
           $options = array("group_id" => $save_id, "user_id" => $this->login_user->id, "mode" => "list_groups", "user_ids" => $this->get_allowed_user_ids());
           $list_data = $this->Messages_model->get_list($options)->getResult();
   
           $result = array();
           if (!$id) {
                foreach ($list_data as $data) {
                    $result[] = $this->_make_row($data, "list_groups");
                }
            }

           echo json_encode(array("success" => true, "data" => $result, 'id' => $save_id, 'message' => app_lang('record_saved')));
       } else {
           echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
       }
   }

    function groups_modal_form($id = 0) {
        $message_group_id = $this->request->getPost('id') ?? ($id != 0 ? $id : "");

        $view_data['model_info'] = $this->Message_groups_model->get_one($message_group_id);

        return $this->template->view('messages/group_modal_form', $view_data);
    }

    function client_groups_modal_form() {
        
        $message_group_id = $this->request->getPost('id');

        $view_data['model_info'] = $this->Message_groups_model->get_one($message_group_id);

        $groups_dropdown[] = app_lang('select_group');

        $groups = $this->Message_groups_model->get_groups_for_member_messaging($this->login_user->id)->getResult();
        
        foreach ($groups as $group) {
            $groups_dropdown[$group->id] = $group->group_name;
        }

        $view_data["groups_dropdown"] = $groups_dropdown;

        return $this->template->view('messages/client_groups_modal_form', $view_data);
    }

    /* show new message modal */
    function modal_form($user_id = 0) {
        validate_numeric_value($user_id);
        $this->check_message_user_permission();
       
        $users_dropdown[] = app_lang('select');

        if ($user_id) {
            $view_data['message_user_info'] = $this->Users_model->get_one($user_id);
        } else {
            $users = $this->Messages_model->get_users_for_messaging($this->get_user_options_for_query())->getResult();

            foreach ($users as $user) {
                $user_name = $user->first_name . " " . $user->last_name;

                if ($user->user_type === "client" && $user->company_name) { //user is a client contact
                    if ($this->login_user->user_type == "staff") {
                        $user_name .= " - " . app_lang("client") . ": " . $user->company_name . "";
                    } else {
                        $user_name = app_lang("contact") . ": " . $user_name;
                    }
                }

                $users_dropdown[$user->id] = $user_name;
            }
        }

        $view_data['users_dropdown'] = $users_dropdown;

        return $this->template->view('messages/modal_form', $view_data);
    }

    /* show new message modal for group message*/
    function to_group_modal_form($group_id = 0, $task_id = 0) {
        validate_numeric_value($group_id);
        $this->check_message_user_permission();

        if ($group_id) {
            $view_data['model_info'] = $this->Message_groups_model->get_one($group_id);
        }

        if($task_id != 0)
        {
            $view_data['task_info'] = $this->Tasks_model->get_one($task_id);

        }

        return $this->template->view('messages/to_group_modal_form', $view_data);
    }

    /* show inbox */
    function inbox($auto_select_index = "") {
        $this->check_message_user_permission();
        $this->check_module_availability("module_message");

        $view_data['mode'] = "inbox";
        $view_data['auto_select_index'] = clean_data($auto_select_index);
        return $this->template->rander("messages/index", $view_data);
    }

    
    /* show group items */

    function list_groups($auto_select_index = "") {
        $this->check_message_user_permission();
        $this->check_module_availability("module_message_group");

        $view_data['mode'] = "list_groups";
        $view_data['auto_select_index'] = clean_data($auto_select_index);
        return $this->template->rander("messages/index", $view_data);
    }

    /* show sent items */

    function sent_items($auto_select_index = "") {
        $this->check_message_user_permission();
        $this->check_module_availability("module_message");

        $view_data['mode'] = "sent_items";
        $view_data['auto_select_index'] = clean_data($auto_select_index);
        return $this->template->rander("messages/index", $view_data);
    }

    /* list of messages, prepared for datatable  */

    function list_data($mode = "inbox") {
        $this->check_message_user_permission();
        if ($mode !== "inbox" and $mode !== "list_groups") {
            $mode = "sent_items";
        }

        $options = array("user_id" => $this->login_user->id, "mode" => $mode, "user_ids" => $this->get_allowed_user_ids());
        $list_data = $this->Messages_model->get_list($options)->getResult();

        $result = array();

        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data, $mode);
        }

        echo json_encode(array("data" => $result));
    }

    /* return a message details */

    function view($message_id = 0, $mode = "", $reply = 0) {
        validate_numeric_value($message_id);
        $this->check_message_user_permission();

        $message_mode = $mode;
        if ($reply == 1 && $mode == "inbox") {
            $message_mode = "sent_items";
        } else if ($reply == 1 && $mode == "sent_items") {
            $message_mode = "inbox";
        }

        $options = array("id" => $message_id, "user_id" => $this->login_user->id, "mode" => $message_mode);
        $view_data["message_info"] = $this->Messages_model->get_details($options)->row;

        if (!$this->is_my_message($view_data["message_info"])) {
            app_redirect("forbidden");
        }

        if($view_data["message_info"]->project_id)
        {
            $view_data["project_info"] = $this->Projects_model->get_one($view_data["message_info"]->project_id);
        }

        if($view_data["message_info"]->to_group_id)
        {
            $options = [
                "group_id" => $view_data["message_info"]->to_group_id
            ];
            $view_data["message_users_result"] = $this->Message_group_members_model->get_message_statistics($options)->group_users_data;
        }

        //change message status to read
        $this->Messages_model->set_message_status_as_read($view_data["message_info"]->id, $this->login_user->id);

        $replies_options = array("message_id" => $message_id, "user_id" => $this->login_user->id, "limit" => 4);
        $messages = $this->Messages_model->get_details($replies_options);

        $view_data["replies"] = $messages->result;
        $view_data["found_rows"] = $messages->found_rows;

        $view_data["mode"] = clean_data($mode);
        $view_data["is_reply"] = clean_data($reply);
        echo json_encode(array("success" => true, "data" => $this->template->view("messages/view", $view_data), "message_id" => $message_id));
    }

    /* prepare a row of message list table */

    private function _make_row($data, $mode = "", $return_only_message = false, $online_status = false) {
        $image = (isset($data->another_user_name) ? $data->another_user_image : $data->user_image);
        $image_url = get_avatar($image, (isset($data->another_user_name) ? $data->another_user_name : $data->user_name));
        $created_at = format_to_relative_time($data->created_at);
        $message_id = $data->main_message_id;
        $label = "";
        $reply = "";
        $status = "";
        $attachment_icon = "";
        $subject = $data->subject;
        if ($mode == "inbox") {
            $status = $data->status;
        } else if($mode === "list_groups") {

            $array = explode(",", $data->read_by);

            // Verificar se o usuário logado está presente no array
            if (in_array($this->login_user->id, $array)) {
                $status = 'read';
            } else {
                $status = 'unread';
            }
        }

        if ($data->reply_subject) {
            $label = " <label class='badge bg-success d-inline-block'>" . app_lang('reply') . "</label>";
            $reply = "1";
            $subject = $data->reply_subject;
        }

        if (isset($data->task_id) && $data->task_id) {
            $subject = '#' . $data->task_id . ' - ' . $data->subject;
        }

        if ($data->files && is_array(unserialize($data->files)) && count(unserialize($data->files))) {
            $attachment_icon = "<i data-feather='paperclip' class='icon-14 mr15'></i>";
        }


        //prepare online status
        $online = "";
        if(isset($data->another_user_last_online))
        {
            if ($online_status && is_online_user($data->another_user_last_online)) {
                $online = "<i class='online'></i>";
            }
        }
        else
        {
            if ($online_status && is_online_user($data->last_online)) {
                $online = "<i class='online'></i>";
            }
        }


        $ticket_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-tag icon"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>';
        $project_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-grid icon"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>';
        $group_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-coffee icon-18 me-2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>';


        $link = null;
        $group_name = "";

        if($data->group_name)
        {
            if($data->project_id)
            {
                if($data->is_ticket)
                {
                    $group_name = $ticket_icon . $data->group_name;
                }
                else
                {
                    $group_name = $project_icon . $data->group_name;
                }
            }
            else
            {
                $group_name = $group_icon . $data->group_name;
            }
        }

        $members = $last_message = "";
        $line_name = "<b> " . (isset($data->another_user_name) ? $data->another_user_name : $data->user_name) ."</b>";

        if($data->group_name)
        {
            $line_name = "";
            $members = "<span class='badge badge-light mt-0'><b>" .($data->count_members ?? 0) . " membros</b></span>";
            $last_message = "<i><b>" . (isset($data->another_user_name) ? $data->another_user_name : $data->user_name) ."</b> em </i>";
        }

        $classe = "";
        if($data->ended)
        {
            $classe = "inactive";
            $members .= "<span class='badge bg-danger mt-0' style='opacity: 1;'><b>Conversa Encerrada</b></span>";
        }

        $message = "<div class='message-row $status $classe' data-id='$message_id' data-index='$data->main_message_id' data-reply='$reply'><div class='d-flex'><div class='flex-shrink-0'>
                        <span class='avatar avatar-xs'>
                            <img src='$image_url' />
                                $online
                        </span>
                    </div>
                    <div class='w-100 ps-3'>
                        <div class='mb5'>
                                <div style='display: flex;align-items: center;gap: 1rem;'>
                                    <strong>" . $group_name . "</strong> 
                                    " . $members  . "
                                </div>
                                " .  $line_name . "
                                <span class='text-off float-end time'>$attachment_icon $last_message $created_at</span>
                        </div>
                        $label $subject
                    </div></div></div>
                  
                ";
        if ($return_only_message) {
            return $message;
        } else {
            return array(
                $message,
                $data->created_at,
                $status
            );
        }
    }

    function create_task($message_id)
    {
        $message_info = $this->Messages_model->get_one($message_id);

        $other_messages = $this->Messages_model->get_all_where(array("message_id" => $message_id))->getResult();

        $group_id = $message_info->to_group_id;

        $group_info = $this->Message_groups_model->get_one($group_id);
        
        $group_members_info = $this->Message_group_members_model->get_all_where(array("message_group_id" => $group_id))->getResult();
        
        $member_ids = array_column($group_members_info, 'user_id'); // Extrai os IDs dos membros para um array

        // Passo 2: Converter os IDs em uma string separada por vírgulas
        $collaborators = implode(',', $member_ids);

        $data = array(
            "title" => $message_info->subject,
            "project_id" => $group_info->project_id,
            "milestone_id" => 0,
            "parent_task_id" => 0,
            "collaborators" => $collaborators,
            "status_id" => 1,
            "created_date" => get_current_utc_time()
        );

        //don't get assign to id if login user is client
        $data["assigned_to"] = 1;

        $data = clean_data($data);
       
        $data["sort"] = $this->Tasks_model->get_next_sort_value($group_info->project_id, $data['status_id']);
        
        $save_id = $this->Tasks_model->ci_save($data);

        if ($save_id) {

            log_notification("project_task_created", array("project_id" => $group_info->project_id, "task_id" => $save_id));

            $target_path = get_setting("timeline_file_path");
            $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "project_comment");
            $data = array(
                "created_by" => $message_info->from_user_id,
                "created_at" => get_current_utc_time(),
                "project_id" => $group_info->project_id,
                "file_id" => 0,
                "task_id" => $save_id ? $save_id : 0,
                "customer_feedback_id" => 0,
                "comment_id" => 0,
                "description" => $message_info->message
            );
    
            $data = clean_data($data);
    
            $data["files"] = $files_data; //don't clean serilized data
    
            $save_comment_id = $this->Project_comments_model->save_comment($data, $save_id);
            if ($save_comment_id) {
                $response_data = "";
                $options = array("id" => $save_comment_id, "login_user_id" => $this->login_user->id);
    
    
                $comment_info = $this->Project_comments_model->get_one($save_comment_id);
    
                $notification_options = array("project_id" => $comment_info->project_id, "project_comment_id" => $save_comment_id);
    
                if ($comment_info->file_id) { //file comment
                    $notification_options["project_file_id"] = $comment_info->file_id;
                    log_notification("project_file_commented", $notification_options);
                } else if ($comment_info->task_id) { //task comment
                    $notification_options["task_id"] = $comment_info->task_id;
                    log_notification("project_task_commented", $notification_options);
                } else if ($comment_info->customer_feedback_id) {  //customer feedback comment
                    if ($comment_id) {
                        log_notification("project_customer_feedback_replied", $notification_options);
                    } else {
                        log_notification("project_customer_feedback_added", $notification_options);
                    }
                } else {  //project comment
                    if ($comment_id) {
                        log_notification("project_comment_replied", $notification_options);
                    } else {
                        log_notification("project_comment_added", $notification_options);
                    }
                }
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
            }

            foreach($other_messages as $message)
            {
                $target_path = get_setting("timeline_file_path");
                $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "project_comment");
                $data = array(
                    "created_by" => $message->from_user_id,
                    "created_at" => get_current_utc_time(),
                    "project_id" => $group_info->project_id,
                    "file_id" => 0,
                    "task_id" => $save_id ? $save_id : 0,
                    "customer_feedback_id" => 0,
                    "comment_id" => 0,
                    "description" => $message->message
                );
        
                $data = clean_data($data);
        
                $data["files"] = $files_data; //don't clean serilized data
        
                $save_comment_id = $this->Project_comments_model->save_comment($data, $save_id);
                if ($save_comment_id) {
                    $response_data = "";
                    $options = array("id" => $save_comment_id, "login_user_id" => $this->login_user->id);
        
        
                    $comment_info = $this->Project_comments_model->get_one($save_comment_id);
        
                    $notification_options = array("project_id" => $comment_info->project_id, "project_comment_id" => $save_comment_id);
        
                    if ($comment_info->file_id) { //file comment
                        $notification_options["project_file_id"] = $comment_info->file_id;
                        log_notification("project_file_commented", $notification_options);
                    } else if ($comment_info->task_id) { //task comment
                        $notification_options["task_id"] = $comment_info->task_id;
                        log_notification("project_task_commented", $notification_options);
                    } else if ($comment_info->customer_feedback_id) {  //customer feedback comment
                        if ($comment_id) {
                            log_notification("project_customer_feedback_replied", $notification_options);
                        } else {
                            log_notification("project_customer_feedback_added", $notification_options);
                        }
                    } else {  //project comment
                        if ($comment_id) {
                            log_notification("project_comment_replied", $notification_options);
                        } else {
                            log_notification("project_comment_added", $notification_options);
                        }
                    }
                } else {
                    echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
                }
            }

            $options = array(
                'task_id' => $save_id
            );
            $salvar_message_com_task = $this->Messages_model->ci_save($options, $message_id);
           

            echo json_encode(array("success" => true, 'id' => $save_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    /* send new message */

    function send_message() {
        $this->check_message_user_permission();

        $this->validate_submitted_data(array(
            "message" => "required",
            "to_user_id" => "required|numeric"
        ));

        $to_user_id = $this->request->getPost('to_user_id');

        //team member can send message to any team member
        //client can send messages to only allowed members

        $this->check_validate_sending_message($to_user_id, 0);

        $target_path = get_setting("timeline_file_path");
        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "message");

        $message_data = array(
            "from_user_id" => $this->login_user->id,
            "to_user_id" => $to_user_id,
            "subject" => $this->request->getPost('subject'),
            "message" => $this->request->getPost('message'),
            "created_at" => get_current_utc_time(),
            "deleted_by_users" => "",
        );

        $message_data = clean_data($message_data);

        $message_data["files"] = $files_data; //don't clean serilized data

        $save_id = $this->Messages_model->ci_save($message_data);

        if ($save_id) {
            log_notification("new_message_sent", array("actual_message_id" => $save_id));
            echo json_encode(array("success" => true, 'message' => app_lang('message_sent'), "id" => $save_id));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    /* send new message to group*/

    function send_message_to_group() {
    
        $this->validate_submitted_data(array(
            "message" => "required",
            "to_group_id" => "required|numeric"
        ));

        $to_group_id = $this->request->getPost('to_group_id');


        $target_path = get_setting("timeline_file_path");
        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "message");

        $message_data = array(
            "from_user_id" => $this->login_user->id,
            "to_group_id" => $to_group_id,
            "subject" => $this->request->getPost('subject'),
            "task_id" => $this->request->getPost('task_id'),
            "message" => $this->request->getPost('message'),
            "created_at" => get_current_utc_time(),
            "deleted_by_users" => "",
        );

        $message_data = clean_data($message_data);

        $message_data["files"] = $files_data; //don't clean serilized data

        $save_id = $this->Messages_model->ci_save($message_data);

        if ($save_id) {
            log_notification("new_message_sent_to_group", array("actual_message_id" => $save_id));
            echo json_encode(array("success" => true, 'message' => app_lang('message_sent'), "id" => $save_id));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    function edit_message_modal_form($id) {

        $this->check_message_user_permission();

        $view_data['model_info'] = $this->Messages_model->get_one($id);

        return $this->template->view('messages/edit_message_modal_form', $view_data);
    }


     /* send new message*/

     function edit_message() {
    
        $this->validate_submitted_data(array(
            "message" => "required",
        ));

        $id = $this->request->getPost('id');

        $target_path = get_setting("timeline_file_path");
        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "message");

        $message_data = array(
            "message" => $this->request->getPost('message'),
        );

        $message_data = clean_data($message_data);

        $message_data["files"] = $files_data; //don't clean serilized data

        $save_id = $this->Messages_model->ci_save($message_data, $id);

        if ($save_id) {
            echo json_encode(array("success" => true, 'message' => app_lang('message_sent'), "id" => $save_id));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }


    /* reply to an existing message */

    function reply($is_chat = 0) {
        $this->check_message_user_permission();
        $message_id = $this->request->getPost('message_id');

        $this->validate_submitted_data(array(
            "reply_message" => "required",
            "message_id" => "required|numeric"
        ));

        $message_info = $this->Messages_model->get_one($message_id);

        if (!$this->is_my_message($message_info)) {
            app_redirect("forbidden");
        }


        if ($message_info->id) {
            //check, where we have to send this message
            $to_user_id = 0;
            $to_group_id = 0;
            if($message_info->to_group_id)
            {
                $to_group_id = $message_info->to_group_id;
            }
            else
            {
                if ($message_info->from_user_id === $this->login_user->id) {
                    $to_user_id = $message_info->to_user_id;
                } else {
                    $to_user_id = $message_info->from_user_id;
                }
            }

            $this->check_validate_sending_message($to_user_id, $to_group_id);

            $target_path = get_setting("timeline_file_path");
            $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "message");

            $message = $this->request->getPost('reply_message');

            $message_data = array(
                "from_user_id" => $this->login_user->id,
                "to_user_id" => $to_user_id,
                "to_group_id" => $to_group_id,
                "message_id" => $message_id,
                "subject" => "",
                "message" => $message,
                "created_at" => get_current_utc_time(),
                "deleted_by_users" => "",
            );

            $message_data = clean_data($message_data);
            $message_data["files"] = $files_data; //don't clean serilized data


            $save_id = $this->Messages_model->ci_save($message_data);

            if ($save_id) {

                if($to_group_id !== 0)
                {
                    $group_members_info = $this->Message_group_members_model->get_details(array('message_group_id' => $to_group_id));
                    foreach ($group_members_info->getResult() as $member) {
                        $pusher_to_user_id = $member->user_id;
    
                        if (get_setting('enable_chat_via_pusher') && get_setting("enable_push_notification")) {
                            send_message_via_pusher($pusher_to_user_id, $message_data, $message_id);
                        }
                    }

                    log_notification("message_reply_sent_to_group", array("actual_message_id" => $save_id, "parent_message_id" => $message_id));
                }
                else
                {
                    //if chat via pusher is enabled, then send message data to pusher
                    if (get_setting('enable_chat_via_pusher') && get_setting("enable_push_notification")) {
                        send_message_via_pusher($to_user_id, $message_data, $message_id);
                    }
                    
                    //we'll not send notification, if the user is online

                    if ($this->request->getPost("is_user_online") !== "1") {
                        log_notification("message_reply_sent", array("actual_message_id" => $save_id, "parent_message_id" => $message_id));
                    }
    
                }

                //clear the delete status, if the mail deleted
                $this->Messages_model->clear_deleted_status($message_id);

                if ($is_chat) {
                    echo json_encode(array("success" => true, 'data' => $this->_load_messages($message_id, $this->request->getPost("last_message_id"), 0, $to_user_id)));
                } else {
                    $options = array("id" => $save_id, "user_id" => $this->login_user->id);
                    $view_data['reply_info'] = $this->Messages_model->get_details($options)->row;
                    echo json_encode(array("success" => true, 'message' => app_lang('message_sent'), 'data' => $this->template->view("messages/reply_row", $view_data)));
                }

                return;
            }
        }
        echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
    }

    //load messages right panel when clicking load more button
    function view_messages() {

        $this->check_message_user_permission();
        $this->validate_submitted_data(array(
            "message_id" => "required|numeric",
            "last_message_id" => "numeric",
            "top_message_id" => "numeric"
        ));

        $message_id = $this->request->getPost("message_id");

        echo $this->_load_more_messages($message_id, $this->request->getPost("last_message_id"), $this->request->getPost("top_message_id"));
    }

    //prepare the chat box messages 
    private function _load_more_messages($message_id, $last_message_id, $top_message_id) {

        $replies_options = array("message_id" => $message_id, "last_message_id" => $last_message_id, "top_message_id" => $top_message_id, "user_id" => $this->login_user->id, "limit" => 10);

        $view_data["replies"] = $this->Messages_model->get_details($replies_options)->result;
        $view_data["message_id"] = $message_id;

        $this->Messages_model->set_message_status_as_read($message_id, $this->login_user->id);

        return $this->template->view("messages/reply_rows", $view_data);
    }

    function count_notifications() {
        $this->validate_submitted_data(array(
            "active_message_id" => "numeric"
        ));

        $notifiations = $this->Messages_model->count_notifications($this->login_user->id, $this->login_user->message_checked_at, $this->request->getPost("active_message_id"), $this->get_allowed_user_ids());
        echo json_encode(array("success" => true, "active_message_id" => $this->request->getPost("active_message_id"), 'total_notifications' => $notifiations));
    }

    /* prepare notifications */

    function get_notifications() {
        $options = array("user_id" => $this->login_user->id, "mode" => "inbox", "user_ids" => $this->get_allowed_user_ids(), "is_notification" => true);
        $view_data['notifications'] = $this->Messages_model->get_list($options)->getResult();
        $options = array("user_id" => $this->login_user->id, "mode" => "list_groups", "user_ids" => $this->get_allowed_user_ids(), "is_notification" => true);
        $view_data['notifications'] = array_merge($view_data['notifications'], $this->Messages_model->get_list($options)->getResult());


        // Ordenando o array mesclado por created_at
        usort($view_data['notifications'], function($a, $b) {
            return strtotime($b->created_at) - strtotime($a->created_at); // Ordenação decrescente
        });
        echo json_encode(array("success" => true, 'notification_list' => $this->template->view("messages/notifications", $view_data)));
    }

    function update_notification_checking_status() {
        $now = get_current_utc_time();
        $user_data = array("message_checked_at" => $now);
        $this->Users_model->ci_save($user_data, $this->login_user->id);
    }

    /* upload a file */

    function upload_file() {
        upload_file_to_temp();
    }

    /* check valid file for message */

    function validate_message_file() {
        return validate_post_file($this->request->getPost("file_name"));
    }

    /* download files by zip */

    function download_message_files($message_id = "") {
        validate_numeric_value($message_id);
        $model_info = $this->Messages_model->get_one($message_id);
        if (!$this->is_my_message($model_info)) {
            app_redirect("forbidden");
        }

        $files = $model_info->files;

        $timeline_file_path = get_setting("timeline_file_path");
        return $this->download_app_files($timeline_file_path, $files);
    }

    function delete_my_messages($id = 0) {

        if (!$id) {
            exit();
        }

        validate_numeric_value($id);

        //delete messages for current user.
        $this->Messages_model->delete_messages_for_user($id, $this->login_user->id);
    }

    function end_my_messages($id = 0) {

        if (!$id) {
            exit();
        }

        validate_numeric_value($id);

        //delete messages for current user.
        $this->Messages_model->end_messages_for_user($id, $this->login_user->id);
    }

    function reactive_my_messages($id = 0) {

        if (!$id) {
            exit();
        }

        validate_numeric_value($id);

        //delete messages for current user.
        $this->Messages_model->reactive_messages_for_user($id, $this->login_user->id);
    }

    //prepare chat inbox list
    function chat_list() {
        $this->check_message_user_permission();

        $view_data['show_groups_list'] = false;
        $view_data['show_users_list'] = false;
        $view_data['show_clients_list'] = false;

        $client_message_users = get_setting("client_message_users");
        if ($this->login_user->user_type === "staff") {
            //user is team member
            $client_message_users_array = explode(",", $client_message_users);
            if (in_array($this->login_user->id, $client_message_users_array)) {
                //user can send message to clients
                $view_data['show_clients_list'] = true;
            }

            if (get_array_value($this->login_user->permissions, "message_permission") !== "no") {
                //user can send message to team members
                $view_data['show_users_list'] = true;
            }

             if (get_setting("module_message_group")) {
                $view_data['show_groups_list'] = true;
            }
        } else {
            //user is a client contact and can send messages
            if ($client_message_users) {
                $view_data['show_users_list'] = true;
            }

            //user can send message to own client contacts
            if (get_setting("client_message_own_contacts")) {
                $view_data['show_clients_list'] = true;
            }
        }

        $options = array("login_user_id" => $this->login_user->id, "user_ids" => $this->get_allowed_user_ids());

        $view_data['messages'] = $this->Messages_model->get_chat_list($options)->getResult();

        return $this->template->view("messages/chat/tabs", $view_data);
    }

    function users_list($type) {
        $view_data["users"] = $this->Messages_model->get_users_for_messaging($this->get_user_options_for_query($type))->getResult();

        $page_type = "";
        if ($type === "staff") {
            $page_type = "team-members-tab";
        } else {
            $page_type = "clients-tab";
        }

        $view_data["page_type"] = $page_type;

        return $this->template->view("messages/chat/team_members", $view_data);
    }

    function groups_list() {
        $options = ['user_id' => $this->login_user->id];
        $view_data["groups"] = $this->Message_groups_model->get_groups_for_messaging($options)->getResult();
        $view_data["without_message_groups"] = $this->Message_groups_model->get_groups_without_messages($options)->getResult();
        $view_data["page_type"] = "groups-tab";
        return $this->template->view("messages/chat/groups", $view_data);
    }

    //load messages in chat view
    function view_chat() {

        $this->check_message_user_permission();

        $this->validate_submitted_data(array(
            "message_id" => "required|numeric",
            "last_message_id" => "numeric",
            "top_message_id" => "numeric",
            "another_user_id" => "numeric"
        ));

        $message_id = $this->request->getPost("message_id");

        $another_user_id = $this->request->getPost("another_user_id");

        if ($this->request->getPost("is_first_load") == "1") {
            $view_data["first_message"] = $this->Messages_model->get_details(array("id" => $message_id, "user_id" => $this->login_user->id))->row;
            echo $this->template->view("messages/chat/message_title", $view_data);
        }

        echo $this->_load_messages($message_id, $this->request->getPost("last_message_id"), $this->request->getPost("top_message_id"), $another_user_id);
    }

    //prepare the chat box messages 
    private function _load_messages($message_id, $last_message_id, $top_message_id, $another_user_id = "") {

        $replies_options = array("message_id" => $message_id, "last_message_id" => $last_message_id, "top_message_id" => $top_message_id, "user_id" => $this->login_user->id);

        $view_data["replies"] = $this->Messages_model->get_details($replies_options)->result;
        $view_data["message_id"] = $message_id;

        $this->Messages_model->set_message_status_as_read($message_id, $this->login_user->id);

        $is_online = false;
        if ($another_user_id) {
            $last_online = $this->Users_model->get_one($another_user_id)->last_online;
            if ($last_online) {
                $is_online = is_online_user($last_online);
            }
        }

        $view_data['is_online'] = $is_online;

        return $this->template->view("messages/chat/message_items", $view_data);
    }

    function get_active_chat() {

        $this->validate_submitted_data(array(
            "message_id" => "required|numeric"
        ));

        $message_id = $this->request->getPost("message_id");

        $options = array("id" => $message_id, "user_id" => $this->login_user->id);

        $view_data["message_info"] = $this->Messages_model->get_details($options)->row;

        if (!$this->is_my_message($view_data["message_info"])) {
            app_redirect("forbidden");
        }

        $project_id = $view_data["message_info"]->project_id;
        $project_info = array();
        if($project_id)
        {
            $project_info = $this->Projects_model->get_one_where(array("id" => $project_id, "deleted" => "0"));
        }

        if($view_data["message_info"]->id)
        {
            $this->Messages_model->set_message_status_as_read($view_data["message_info"]->id, $this->login_user->id);
        }

        $view_data["tab_type"] = ((!empty($view_data["message_info"]->group_name)) ? 'groups' : '');

        $view_data["project_info"] = $project_info;
        $view_data["message_id"] = $message_id;
        return $this->template->view("messages/chat/active_chat", $view_data);
    }

    function get_chatlist_of_user() {

        $this->check_message_user_permission();

        $this->validate_submitted_data(array(
            "user_id" => "required|numeric"
        ));

        $user_id = $this->request->getPost("user_id");

        $options = array("user_id" => $user_id, "login_user_id" => $this->login_user->id);
        $view_data["messages"] = $this->Messages_model->get_chat_list($options)->getResult();

        $user_info = $this->Users_model->get_one_where(array("id" => $user_id, "status" => "active", "deleted" => "0"));
        $view_data["user_name"] = $user_info->first_name . " " . $user_info->last_name;

        $view_data["user_id"] = $user_id;
        $view_data["tab_type"] = $this->request->getPost("tab_type");

        return $this->template->view("messages/chat/get_chatlist_of_user", $view_data);
    }

    function get_chatlist_of_group() {

        $this->check_message_user_permission();

        $this->validate_submitted_data(array(
            "group_id" => "required|numeric"
        ));

        $group_id = $this->request->getPost("group_id");

        $options = array("group_id" => $group_id, "login_user_id" => $this->login_user->id, "ended" => 0);
        $view_data["messages"] = $this->Messages_model->get_chat_list($options)->getResult();

        $group_info = $this->Message_groups_model->get_one_where(array("id" => $group_id, "deleted" => "0"));

        $project_id = $group_info->project_id;
        $project_info = array();
        if($project_id)
        {
            $project_info = $this->Projects_model->get_one_where(array("id" => $project_id, "deleted" => "0"));
        }

        $view_data["group_name"] = $group_info->group_name;
        $view_data["group_info"] = $group_info;
        $view_data["project_info"] = $project_info;
        $view_data["group_id"] = $group_info->id;
        $view_data["tab_type"] = $this->request->getPost("tab_type");

        return $this->template->view("messages/chat/get_chatlist_of_group", $view_data);
    }


    function send_typing_indicator_to_pusher() {
        $message_id = $this->request->getPost("message_id");
        if (!$message_id) {
            show_404();
        }

        $message_info = $this->Messages_model->get_one($message_id);
        if (!$this->is_my_message($message_info)) {
            app_redirect("forbidden");
        }

        if ($message_info->id) {
            //check, where we have to send this message
            $to_user_id = 0;
            $to_group_id = 0;
            if($message_info->to_group_id)
            {
                $to_group_id = $message_info->to_group_id;
                $group_members_info = $this->Message_group_members_model->get_details(array('message_group_id' => $to_group_id));
                foreach ($group_members_info->getResult() as $member) {
                    $pusher_to_user_id = $member->user_id;
                    $this->check_validate_sending_message($pusher_to_user_id, $to_group_id);

                    if (get_setting('enable_chat_via_pusher') && get_setting("enable_push_notification")) {
                        send_message_via_pusher($pusher_to_user_id, "", $message_id, "typing");
                    }
                }
            }
            else
            {
                if ($message_info->from_user_id === $this->login_user->id) {
                    $to_user_id = $message_info->to_user_id;
                } else {
                    $to_user_id = $message_info->from_user_id;
                }

                $this->check_validate_sending_message($to_user_id, $to_group_id);
    
                if (get_setting('enable_chat_via_pusher') && get_setting("enable_push_notification")) {
                    send_message_via_pusher($to_user_id, "", $message_id, "typing");
                }
            }
        } else {
            show_404();
        }
    }

}

/* End of file messages.php */
    /* Location: ./app/controllers/messages.php */    