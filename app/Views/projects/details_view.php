<?php
if (!function_exists("make_project_tabs_data")) {

    function make_project_tabs_data($default_project_tabs = array(), $is_client = false, $is_ticket = 0) {
        $project_tab_order = get_setting("project_tab_order");
        $project_tab_order_of_clients = get_setting("project_tab_order_of_clients");
        $custom_project_tabs = array();

        if ($is_client && $project_tab_order_of_clients) {
            //user is client
            $custom_project_tabs = explode(',', $project_tab_order_of_clients);
        } else if (!$is_client && $project_tab_order) {
            //user is team member
            $custom_project_tabs = explode(',', $project_tab_order);
        }

        $final_projects_tabs = array();
        if ($custom_project_tabs) {
            foreach ($custom_project_tabs as $custom_project_tab) {
                if (array_key_exists($custom_project_tab, $default_project_tabs)) {
                    $final_projects_tabs[$custom_project_tab] = get_array_value($default_project_tabs, $custom_project_tab);
                }
            }
        }

        $final_projects_tabs = $final_projects_tabs ? $final_projects_tabs : $default_project_tabs;

        foreach ($final_projects_tabs as $key => $value) {
            $exibition_key = $key;
            if($is_ticket)
            {
                if($key == 'tasks_kanban')
                {
                    $exibition_key = 'ticket_kanban';
                }
                
                if($key == 'tasks_list')
                {
                    $exibition_key = 'ticket_list';
                }
            }
            echo "<li class='nav-item' role='presentation'><a class='nav-link' data-bs-toggle='tab' href='" . get_uri($value) . "' data-bs-target='#project-$key-section'>" . app_lang($exibition_key) . "</a></li>";
        }
    }

}
?>

<div class="page-content project-details-view clearfix">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="project-title-section">
                    <div class="page-title no-bg clearfix mb5 no-border">
                        <div>
                            <h1 class="pl0">
                            <?php if (!$project_info->is_ticket) { ?>
                                <?php if ($project_info->status == "open") { ?>
                                    <span title="<?php echo app_lang("open"); ?>"><i data-feather="grid" class='icon'></i></span>
                                <?php } else if ($project_info->status == "completed") { ?>
                                    <span title="<?php echo app_lang("completed"); ?>"><i data-feather="check-circle" class='icon'></i></span>
                                <?php } else if ($project_info->status == "hold") { ?>
                                    <span title="<?php echo app_lang("hold"); ?>"><i data-feather="pause-circle" class='icon'></i></span>
                                <?php } else if ($project_info->status == "canceled") { ?>
                                    <span title="<?php echo app_lang("canceled"); ?>"><i data-feather="x-circle" class='icon'></i></span>
                                <?php } ?>
                                <?php } else { ?>
                                    <span title="<?php echo app_lang("ticket"); ?>"><i data-feather="tag" class='icon'></i></span>
                                <?php } ?>

                                <?php echo "#$project_info->id - $project_info->title"; ?>
                                <?php if($is_user_a_project_member) { ?>
                                    <?php if((!empty($message_group)) && $message_group->id) { ?>
                                        <div class="btn btn-primary btn-sm js-message-row-of-groups" data-id="<?= $message_group->id ?>"><i data-feather="list" class="icon-16"></i> <?= app_lang('enter_group') ?></div>
                                    <?php } else { ?>
                                        <?php if($login_user->user_type === 'staff' && get_setting("module_message_group")) { ?>
                                            <?php echo ajax_anchor(get_uri("projects/create_group/" . $project_info->id . ""), "<i data-feather='plus' class='icon-16'></i> " . app_lang('create_group'), array("class" => "btn btn-primary", "id" => "create_group", "title" => app_lang('create_group'), "data-reload-on-success" => "1")); ?>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>

                                <?php if (!(get_setting("disable_access_favorite_project_option_for_clients") && $login_user->user_type == "client")) { ?>
                                    <span id="star-mark">
                                        <?php
                                        if ($is_starred) {
                                            echo view('projects/star/starred', array("project_id" => $project_info->id));
                                        } else {
                                            echo view('projects/star/not_starred', array("project_id" => $project_info->id));
                                        }
                                        ?>
                                    </span>
                                <?php } ?>
                            </h1>
                        </div>

                        <div class="project-title-button-group-section">
                            <div class="title-button-group mr0" id="project-timer-box">
                                <?php echo view("projects/project_title_buttons"); ?>
                            </div>
                        </div>
                    </div>
                    <ul id="project-tabs" data-bs-toggle="ajax-tab" class="nav nav-tabs rounded classic mb20 scrollable-tabs border-white" role="tablist">
                        <?php
                        if ($login_user->user_type === "staff") {
                            //default tab order
                            $project_tabs = array(
                                "overview" => "projects/overview/" . $project_info->id,
                                "tasks_list" => "projects/tasks/" . $project_info->id,
                                "tasks_kanban" => "projects/tasks_kanban/" . $project_info->id,
                            );

                            if ($show_milestone_info && !$project_info->is_ticket) {
                                $project_tabs["milestones"] = "projects/milestones/" . $project_info->id;
                            }

                            if ($show_gantt_info && !$project_info->is_ticket) {
                                $project_tabs["gantt"] = "projects/gantt/" . $project_info->id;
                            }

                            if ($show_note_info) {
                                $project_tabs["notes"] = "projects/notes/" . $project_info->id;
                            }

                            $project_tabs["files"] = "projects/files/" . $project_info->id;
                            if(!$project_info->is_ticket)
                            {
                                $project_tabs["comments"] = "projects/comments/" . $project_info->id;
                            }

                            if ($project_info->project_type === "client_project" && ($login_user->is_admin || get_array_value($login_user->permissions, "client_feedback_access_permission")) && !$project_info->is_ticket) {
                                $project_tabs["customer_feedback"] = "projects/customer_feedback/" . $project_info->id;
                            }

                            if ($show_timesheet_info) {
                                $project_tabs["timesheets"] = "projects/timesheets/" . $project_info->id;
                            }

                            if ($show_invoice_info && $project_info->project_type === "client_project" && !$project_info->is_ticket) {
                                $project_tabs["invoices"] = "projects/invoices/" . $project_info->id;
                                $project_tabs["payments"] = "projects/payments/" . $project_info->id;
                            }

                            if ($show_expense_info && !$project_info->is_ticket) {
                                $project_tabs["expenses"] = "projects/expenses/" . $project_info->id;
                            }

                            if ($show_contract_info && $project_info->project_type === "client_project" && !$project_info->is_ticket) {
                                $project_tabs["contracts"] = "projects/contracts/" . $project_info->id;
                            }

                            if ($show_ticket_info && $project_info->project_type === "client_project" && !$project_info->is_ticket) {
                                $project_tabs["tickets"] = "projects/tickets/" . $project_info->id;
                            }

                            if ($show_timesheet_info and $login_user->is_admin) {
                                $project_tabs["resources"] = "projects/resources/" . $project_info->id;
                            }

                            $project_tabs_of_hook_of_staff = array();
                            $project_tabs_of_hook_of_staff = app_hooks()->apply_filters('app_filter_team_members_project_details_tab', $project_tabs_of_hook_of_staff, $project_info->id);
                            $project_tabs_of_hook_of_staff = is_array($project_tabs_of_hook_of_staff) ? $project_tabs_of_hook_of_staff : array();
                            $project_tabs = array_merge($project_tabs, $project_tabs_of_hook_of_staff);

                            make_project_tabs_data($project_tabs, false, $project_info->is_ticket);
                        } else {
                            //default tab order
                            $project_tabs = array(
                                "overview" => "projects/overview_for_client/" . $project_info->id
                            );

                            if ($show_tasks) {
                                $project_tabs["tasks_list"] = "projects/tasks/" . $project_info->id;
                                $project_tabs["tasks_kanban"] = "projects/tasks_kanban/" . $project_info->id;
                            }

                            if ($show_files) {
                                $project_tabs["files"] = "projects/files/" . $project_info->id;
                            }

                            $project_tabs["comments"] = "projects/customer_feedback/" . $project_info->id;

                            if ($show_milestone_info) {
                                $project_tabs["milestones"] = "projects/milestones/" . $project_info->id;
                            }

                            if ($show_gantt_info) {
                                $project_tabs["gantt"] = "projects/gantt/" . $project_info->id;
                            }

                            if ($show_timesheet_info) {
                                $project_tabs["timesheets"] = "projects/timesheets/" . $project_info->id;
                            }
                            
                            if ($show_timesheet_info) {
                                $project_tabs["resources"] = "projects/resources/" . $project_info->id;
                            }


                            if (get_setting("module_invoice") && !$project_info->is_ticket) {
                                //check left menu settings
                                $left_menu = get_setting("user_" . $login_user->id . "_left_menu") ? get_setting("user_" . $login_user->id . "_left_menu") : get_setting("default_client_left_menu");
                                $left_menu = $left_menu ? json_decode(json_encode(@unserialize($left_menu)), true) : false;
                                if (!$left_menu || in_array("invoices", array_column($left_menu, "name"))) {
                                    $project_tabs["invoices"] = "projects/invoices/" . $project_info->id . "/" . $login_user->client_id;
                                }
                            }

                            $project_tabs_of_hook_of_client = array();
                            $project_tabs_of_hook_of_client = app_hooks()->apply_filters('app_filter_clients_project_details_tab', $project_tabs_of_hook_of_client, $project_info->id);
                            $project_tabs_of_hook_of_client = is_array($project_tabs_of_hook_of_client) ? $project_tabs_of_hook_of_client : array();
                            $project_tabs = array_merge($project_tabs, $project_tabs_of_hook_of_client);

                            make_project_tabs_data($project_tabs, true, $project_info->is_ticket);
                        }
                        ?>

                    </ul>
                </div>
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane fade active" id="project-overview-section"></div>
                    <div role="tabpanel" class="tab-pane fade grid-button" id="project-tasks_list-section"></div>
                    <div role="tabpanel" class="tab-pane fade grid-button" id="project-tasks_kanban-section"></div>
                    <div role="tabpanel" class="tab-pane fade" id="project-milestones-section"></div>
                    <div role="tabpanel" class="tab-pane fade grid-button" id="project-gantt-section"></div>
                    <div role="tabpanel" class="tab-pane fade" id="project-files-section"></div>
                    <div role="tabpanel" class="tab-pane fade" id="project-comments-section"></div>
                    <div role="tabpanel" class="tab-pane fade" id="project-customer_feedback-section"></div>
                    <div role="tabpanel" class="tab-pane fade" id="project-notes-section"></div>
                    <div role="tabpanel" class="tab-pane fade" id="project-timesheets-section"></div>
                    <div role="tabpanel" class="tab-pane fade" id="project-invoices-section"></div>
                    <div role="tabpanel" class="tab-pane fade" id="project-payments-section"></div>
                    <div role="tabpanel" class="tab-pane fade" id="project-expenses-section"></div>
                    <div role="tabpanel" class="tab-pane fade" id="project-contracts-section"></div>
                    <div role="tabpanel" class="tab-pane fade" id="project-tickets-section"></div>
                    <div role="tabpanel" class="tab-pane fade" id="project-resources-section"></div>


                    <?php
                    if ($login_user->user_type === "staff") {
                        $project_tabs_of_hook_targets = $project_tabs_of_hook_of_staff;
                    } else {
                        $project_tabs_of_hook_targets = $project_tabs_of_hook_of_client;
                    }

                    foreach ($project_tabs_of_hook_targets as $key => $value) {
                        ?>
                        <div role="tabpanel" class="tab-pane fade" id="project-<?php echo $key; ?>-section"></div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="project-footer-button-section">
    <?php echo view("projects/project_title_buttons"); ?>
</div>

<?php
//if we get any task parameter, we'll show the task details modal automatically
$preview_task_id = get_array_value($_GET, 'task');
if ($preview_task_id) {
    echo modal_anchor(get_uri("projects/task_view"), "", array("id" => "preview_task_link", "title" => app_lang('task_info') . " #$preview_task_id", "data-post-id" => $preview_task_id, "data-modal-lg" => "1"));
}
?>

<?php
load_css(array(
    "assets/js/gantt-chart/frappe-gantt.css",
));
load_js(array(
    "assets/js/gantt-chart/frappe-gantt.js",
));
?>

<script type="text/javascript">
    RELOAD_PROJECT_VIEW_AFTER_UPDATE = true;

    $(document).ready(function () {
        var reload_onclick = false;
        setTimeout(function () {
            var tab = "<?php echo $tab; ?>";
            if (tab === "comment") {
                $("[data-bs-target='#project-comments-section']").trigger("click");
            } else if (tab === "customer_feedback") {
                $("[data-bs-target='#project-customer_feedback-section']").trigger("click");
            } else if (tab === "files") {
                $("[data-bs-target='#project-files-section']").trigger("click");
            } else if (tab === "gantt") {
                $("[data-bs-target='#project-gantt-section']").trigger("click");
            } else if (tab === "tasks") {
                $("[data-bs-target='#project-tasks_list-section']").trigger("click");
            } else if (tab === "tasks_kanban") {
                $("[data-bs-target='#project-tasks_kanban-section']").trigger("click");
            } else if (tab === "milestones") {
                $("[data-bs-target='#project-milestones-section']").trigger("click");
            }
            reload_onclick = true;
        }, 210);
        
        $("#project-tabs .nav-link").on('click', function() {
            if(reload_onclick)
            {
                location.reload();
            }
        })


        //open task details modal automatically 

        if ($("#preview_task_link").length) {
            $("#preview_task_link").trigger("click");
        }

    });

    
  
    $('body').on('click', '.js-message-row-of-groups', function () {
        getChatListOfGroup($(this).attr("data-id"), 'groups');
    });

  
</script>

<?php echo view("projects/tasks/batch_update/batch_update_script"); ?>
<?php echo view("projects/tasks/sub_tasks_helper_js"); ?>