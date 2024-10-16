<?php

namespace App\Controllers;

class Help extends Security_Controller {

    protected $Help_categories_model;
    protected $Help_articles_model;

    function __construct() {
        parent::__construct();
        $this->access_only_team_members();
        $this->init_permission_checker("help_and_knowledge_base");

        $this->Help_categories_model = model('App\Models\Help_categories_model');
        $this->Help_articles_model = model('App\Models\Help_articles_model');
    }

    //show help page
    function index() {
        $this->check_module_availability("module_help");

        $type = "help";

        $view_data["categories"] = $this->Help_categories_model->get_details(array("type" => $type, "only_active_categories" => true))->getResult();
        $view_data["type"] = $type;
        return $this->template->rander("help_and_knowledge_base/index", $view_data);
    }

    //show article
    function view($id = 0) {
        if (!$id || !is_numeric($id)) {
            show_404();
        }

        $model_info = $this->Help_articles_model->get_details(array("id" => $id))->getRow();

        if (!$model_info) {
            show_404();
        }

        $this->Help_articles_model->increas_page_view($id);

        $view_data['selected_category_id'] = $model_info->category_id;
        $view_data['type'] = $model_info->type;
        $view_data['categories'] = $this->Help_categories_model->get_details(array("type" => $model_info->type))->getResult();
        $view_data['page_type'] = "article_view";

        $view_data['article_info'] = $model_info;

        return $this->template->rander('help_and_knowledge_base/articles/view_page', $view_data);
    }

    //get search suggestion for autocomplete
    function get_article_suggestion() {
        $search = $this->request->getPost("search");
        if ($search) {
            $result = $this->Help_articles_model->get_suggestions("help", $search);

            echo json_encode($result);
        }
    }

    //show help category
    function category($id) {
        if (!$id || !is_numeric($id)) {
            show_404();
        }

        $category_info = $this->Help_categories_model->get_one($id);
        if (!$category_info || !$category_info->id) {
            show_404();
        }

        $view_data['page_type'] = "articles_list_view";
        $view_data['type'] = $category_info->type;
        $view_data['selected_category_id'] = $category_info->id;

        $options = array();
        $options = $this->_prepare_access_options($options);
        $options['type'] = $category_info->type;

        $view_data['categories'] = $this->Help_categories_model->get_details( $options )->getResult();

        $options_article = array("id" => $id);
        $options_article = $this->_prepare_access_options($options_article);
        $options_article['id'] = $id;
        $options_article['login_user_id'] = $this->login_user->id;

        $view_data["articles"] = $this->Help_articles_model->get_articles_of_a_category( $options_article )->getResult();
        $view_data["category_info"] = $category_info;

        return $this->template->rander("help_and_knowledge_base/articles/view_page", $view_data);
    }

    //show help articles list
    function help_articles() {
        $this->access_only_allowed_members();

        $view_data["type"] = "help";
        return $this->template->rander("help_and_knowledge_base/articles/index", $view_data);
    }

    //show knowledge base articles list
    function knowledge_base_articles() {
        $this->access_only_allowed_members();

        $view_data["type"] = "knowledge_base";
        return $this->template->rander("help_and_knowledge_base/articles/index", $view_data);
    }

    //show help articles list
    function help_categories() {
        $this->access_only_allowed_members();

        $view_data["type"] = "help";
        return $this->template->rander("help_and_knowledge_base/categories/index", $view_data);
    }

    //show knowledge base articles list
    function knowledge_base_categories() {
        $this->access_only_allowed_members();

        $view_data["type"] = "knowledge_base";
        return $this->template->rander("help_and_knowledge_base/categories/index", $view_data);
    }

    //show add/edit category modal
    function category_modal_form($type) {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "numeric"
        ));

        $id = $this->request->getPost('id');
        $view_data['model_info'] = $this->Help_categories_model->get_one($id);
        $view_data['share_with'] = $id ? ($view_data['model_info']->share_with ? explode(",", $view_data['model_info']->share_with) : array("all_members")) : array("all_members");
        $view_data['groups_dropdown'] = json_encode($this->_get_client_groups_dropdown_select2_data());
        $view_data['type'] = clean_data($type);
        return $this->template->view('help_and_knowledge_base/categories/modal_form', $view_data);
    }

    //save category
    function save_category() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "numeric",
            "title" => "required",
            "type" => "required"
        ));

        $id = $this->request->getPost('id');
        $share_with = array();
        $share_with_all_members = $this->request->getPost('share_with_all_members');
        $share_with_all_clients = $this->request->getPost('share_with_all_clients');
        $share_with_specific_checkbox = $this->request->getPost('share_with_specific_checkbox');
        $share_with_specific_client_groups = $this->request->getPost('share_with_specific_client_groups');

        if ($share_with_all_members) {
            array_push($share_with, $share_with_all_members);
        }

        if ($share_with_all_clients) {
            array_push($share_with, $share_with_all_clients);
        }

        if ($share_with_specific_checkbox && $share_with_specific_client_groups && !$share_with_all_clients) {
            array_push($share_with, $share_with_specific_client_groups);
        }
        $data = array(
            "title" => $this->request->getPost('title'),
            "description" => $this->request->getPost('description'),
            "type" => $this->request->getPost('type'),
            "sort" => $this->request->getPost('sort'),
            "status" => $this->request->getPost('status'),
            "share_with" => $share_with ? implode(",", $share_with) : ""
        );
        $save_id = $this->Help_categories_model->ci_save($data, $id);
        if ($save_id) {
            echo json_encode(array("success" => true, "data" => $this->_category_row_data($save_id), 'id' => $save_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    //delete/undo a category 
    function delete_category() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');
        if ($this->request->getPost('undo')) {
            if ($this->Help_categories_model->delete($id, true)) {
                echo json_encode(array("success" => true, "data" => $this->_category_row_data($id), "message" => app_lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, app_lang('error_occurred')));
            }
        } else {
            if ($this->Help_categories_model->delete($id)) {
                echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
            }
        }
    }

    private function _prepare_access_options($options = array()) {
        if ($this->access_type === "all") {
            return $options;
        }

        $options["user_type"] = $this->login_user->user_type;

        if ($this->login_user->user_type === "client") {
            $group_ids = $this->Clients_model->get_one($this->login_user->client_id)->group_ids;
            if ($group_ids) {
                $options["client_group_ids"] = $group_ids;
            }
        }

        return $options;
    }

    //prepare categories list data
    function categories_list_data($type) {
        $this->access_only_allowed_members();

        $list_data = $this->Help_categories_model->get_details(array("type" => $type))->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_category_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    //get a row of category row
    private function _category_row_data($id) {
        $options = array("id" => $id);
        $data = $this->Help_categories_model->get_details($options)->getRow();
        return $this->_make_category_row($data);
    }

    //make a row of category row
    private function _make_category_row($data) {

        $share_with = "";
        if ($data->client_groups) {
            $groups = explode(",", $data->client_groups);
            foreach ($groups as $group) {
                if ($group) {
                    $share_with .= "<li>" . $group . "</li>";
                }
            }
        }

        if ($share_with) {
            $share_with = "<ul class='pl15'>" . $share_with . "</ul>";
        }
        else
        {
            if ($data->share_with && (strpos($data->share_with, "all_members") !== false || strpos($data->share_with, "all_clients") !== false)) {
                $share_with_data = explode(",", $data->share_with);
                foreach ($share_with_data as $dt) {
                    if ($dt) {
                        $share_with .= "<li>" . app_lang($dt) . "</li>";
                    }
                }
                if ($share_with) {
                    $share_with = "<ul class='pl15'>" . $share_with . "</ul>";
                }
            }
        }
        return array(
            $data->title,
            $data->description ? $data->description : "-",
            app_lang($data->status),
            $share_with,
            $data->sort,
            modal_anchor(get_uri("help/category_modal_form/" . $data->type), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_category'), "data-post-id" => $data->id))
            . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete_category'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("help/delete_category"), "data-action" => "delete"))
        );
    }

    //show add/edit article form
    function article_form($type, $id = 0) {
        $this->access_only_allowed_members();

        $view_data['model_info'] = $this->Help_articles_model->get_one($id);
        $view_data['share_with'] = $id ? explode(",", $view_data['model_info']->share_with) : array("all_members");
        $view_data['groups_dropdown'] = json_encode($this->_get_client_groups_dropdown_select2_data());
        $view_data['type'] = clean_data($type);
        $view_data['categories_dropdown'] = $this->Help_categories_model->get_dropdown_list(array("title"), "id", array("type" => $type));
        return $this->template->rander('help_and_knowledge_base/articles/form', $view_data);
    }   
    
    private function _get_client_groups_dropdown_select2_data() {
        $client_groups = $this->Client_groups_model->get_all()->getResult();
        $groups_dropdown = array();

        foreach ($client_groups as $group) {
            $groups_dropdown[] = array("id" => "cg:" . $group->id, "text" => $group->title);
        }

        return $groups_dropdown;
    }

    //save article
    function save_article() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "numeric",
            "title" => "required",
            "category_id" => "numeric|required"
        ));

        $id = $this->request->getPost('id');
        $type = $this->request->getPost('type');

        $target_path = get_setting("timeline_file_path");
        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "help");
        $new_files = unserialize($files_data);

        $data = array(
            "title" => $this->request->getPost('title'),
            "description" => $this->request->getPost('description'),
            "category_id" => $this->request->getPost('category_id'),
            "sort" => $this->request->getPost('sort'),
            "status" => $this->request->getPost('status')
        );

        //is editing? update the files if required
        if ($id) {
            $expense_info = $this->Help_articles_model->get_one($id);
            $timeline_file_path = get_setting("timeline_file_path");

            $new_files = update_saved_files($timeline_file_path, $expense_info->files, $new_files);
        }

        $data["files"] = serialize($new_files);

        if (!$id) {
            $data["created_by"] = $this->login_user->id;
            $data["created_at"] = get_my_local_time();
        }


        $save_id = $this->Help_articles_model->ci_save($data, $id);
        if ($save_id) {

            //send log notification
            $category_info = $this->Help_categories_model->get_one($data["category_id"]);
            $data_announcement = array(
                "title" => $this->request->getPost('title'),
                "description" => decode_ajax_post_data($this->request->getPost('description')),
                "start_date" => get_current_utc_time(),
                "end_date" => get_current_utc_time(),
                "created_by" => $this->login_user->id,
                "created_at" => get_current_utc_time(),
                "share_with" => $category_info->share_with ? $category_info->share_with : ""
            );
    
            $data_announcement["files"] = serialize($new_files);
            $data_announcement["read_by"] = 0; //set default value
    
            $save_announcement_id = $this->Announcements_model->ci_save($data_announcement);
    
            if ($save_announcement_id) {
                //send log notification
                if ($data_announcement["share_with"]) {
                    if (!$id) {
                        log_notification("new_announcement_created", array("announcement_id" => $save_announcement_id));
                    }
                }
            } 
            // send log notification

            $this->session->setFlashdata("success_message", app_lang('record_saved'));
            app_redirect("help/article_form/" . $type . "/" . $save_id);
        } else {
            $this->session->setFlashdata("error_message", app_lang('error_occurred'));
            app_redirect("help/article_form/" . $type);
        }
    }

    //delete/undo an article 
    function delete_article() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');
        if ($this->request->getPost('undo')) {
            if ($this->Help_articles_model->delete($id, true)) {
                echo json_encode(array("success" => true, "data" => $this->_article_row_data($id), "message" => app_lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, app_lang('error_occurred')));
            }
        } else {
            if ($this->Help_articles_model->delete($id)) {
                echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
            }
        }
    }

    //prepare article list data
    function articles_list_data($type) {
        $this->access_only_allowed_members();

        $options = array();
        $options = $this->_prepare_access_options($options);
        $options['type'] = $type;
        $options['login_user_id'] = $this->login_user->id;
        $list_data = $this->Help_articles_model->get_details($options)->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_article_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    //get a row of article row
    private function _article_row_data($id) {
        $options = array("id" => $id, "login_user_id" => $this->login_user->id);
        $data = $this->Help_articles_model->get_details($options)->getRow();
        return $this->_make_article_row($data);
    }

    //make a row of article row
    private function _make_article_row($data) {
        $title = anchor(get_uri("help/view/" . $data->id), $data->title);

        $feedback = "";

        if ($data->type == "knowledge_base") {
            $title = anchor(get_uri("knowledge_base/view/" . $data->id), $data->title);
            $feedback = "<span class='badge bg-success mt0'>" . $data->helpful_status_yes . " " . app_lang("yes") . "</span> <span class='badge bg-danger mt0'>" . $data->helpful_status_no . " " . app_lang("no") . "</span>";
        }

        return array(
            $title,
            $data->category_title,
            app_lang($data->status),
            $data->total_views,
            $feedback,
            $data->sort,
            anchor(get_uri("help/article_form/" . $data->type . "/" . $data->id), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_article')))
            . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete_article'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("help/delete_article"), "data-action" => "delete"))
        );
    }

    // upload a file 
    function upload_file() {
        $this->access_only_allowed_members();

        upload_file_to_temp();
    }

    // check valid file for ticket 

    function validate_file() {
        return validate_post_file($this->request->getPost("file_name"));
    }

    // download files 
    function download_files($id = 0) {
        $info = $this->Help_articles_model->get_one($id);
        return $this->download_app_files(get_setting("timeline_file_path"), $info->files);
    }

}

/* End of file help.php */
/* Location: ./app/controllers/help.php */