<?php
$progress = 0;
if ($total_sub_tasks) {
    $progress = round($completed_sub_tasks / $total_sub_tasks * 100);
}
?>

<div class="row">
    <div class="col-lg-4 order-lg-last">
        <div class="clearfix">
            <div class="container-fluid">
                <div class="row">
                    <div  class="col-md-12 mb15 task-title-right d-none">
                        <strong><?php echo $model_info->title; ?></strong>
                    </div>

                    <div class="d-flex m0">
                        <div class="flex-shrink-0">
                            <span class="avatar avatar-sm">
                                <img id="task-assigned-to-avatar" src="<?php echo get_avatar($model_info->assigned_to_avatar, $model_info->assigned_to_user); ?>" alt="..." />
                            </span>
                        </div>
                        <div class="w-100 ps-2 pt5">
                            <div>
                                <?php echo get_update_task_info_anchor_data($model_info, "user", $can_edit_tasks, "", $show_assign_to_dropdown); ?>
                            </div>
                            <p> 
                                <span class='badge badge-light mr5' title='Point'><?php echo get_update_task_info_anchor_data($model_info, "points", $can_edit_tasks); ?></span>

                                <?php
                                echo "<span class='badge' style='background:$model_info->status_color; '>" . get_update_task_info_anchor_data($model_info, "status", $can_edit_tasks) . "</span>";
                                ?>
                            </p>
                        </div>
                    </div>

                    <?php if ($total_sub_tasks) { ?>
                        <div class="col-md-12 mb15 mt15">
                            <span class=""><?php echo $completed_sub_tasks . "/" . $total_sub_tasks; ?> <?php echo app_lang("sub_tasks_completed"); ?></span>
                            <div class="progress mt5" style="height: 6px;" title="<?php echo $progress; ?>%">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $progress; ?>%;" aria-valuenow="<?php echo $progress; ?>%" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    <?php } ?>

                    <div class="col-md-12 mb15 mt15">
                        <strong><?php echo app_lang('milestone') . ": "; ?></strong> <?php echo get_update_task_info_anchor_data($model_info, "milestone", $can_edit_tasks); ?>
                    </div>

                    <div class="col-md-12 mb15">
                        <strong><?php echo app_lang('start_date') . ": "; ?></strong> <?php echo get_update_task_info_anchor_data($model_info, "start_date", $can_edit_tasks); ?>
                    </div>

                    <div class="col-md-12 mb15">
                        <strong><?php echo app_lang('deadline') . ": "; ?></strong> <?php echo get_update_task_info_anchor_data($model_info, "deadline", $can_edit_tasks); ?>
                        <?php if ($model_info->deadline_milestone_title) { ?>
                            <span class="help task-deadline-milestone-tooltip" data-bs-toggle="tooltip" title="<?php echo app_lang('milestone') . ": " . $model_info->deadline_milestone_title; ?>"><i data-feather="help-circle" class="icon-16"></i></span>
                        <?php } ?>
                    </div>

                    <div class="col-md-12 mb15">
                        <strong><?php echo app_lang('priority') . ": "; ?></strong> <?php echo get_update_task_info_anchor_data($model_info, "priority", $can_edit_tasks); ?>
                    </div>

                    <div class="col-md-12 mb15">
                        <strong><?php echo app_lang('label') . ": " ?></strong><?php echo get_update_task_info_anchor_data($model_info, "labels", $can_edit_tasks, $labels); ?>
                    </div>

                    <div class="col-md-12 mb15">
                        <strong><?php echo app_lang('collaborators') . ": "; ?> </strong>
                        <div class="mt5">
                            <?php echo get_update_task_info_anchor_data($model_info, "collaborators", $can_edit_tasks, $collaborators); ?>
                        </div>
                    </div>

                    <?php if ($model_info->ticket_id != "0") { ?>
                        <div class="col-md-12 mb15">
                            <strong><?php echo app_lang("ticket") . ": "; ?> </strong> <?php echo anchor(get_uri("tickets/view/" . $model_info->ticket_id), get_ticket_id($model_info->ticket_id) . " - " . $model_info->ticket_title); ?>
                        </div>
                    <?php } ?>

                    <?php if ($model_info->recurring_task_id) { ?>
                        <div class="col-md-12 mb15">
                            <strong><?php echo app_lang('created_from') . ": "; ?> </strong> 
                            <?php
                            echo modal_anchor(get_uri("projects/task_view"), app_lang("task") . " " . $model_info->recurring_task_id, array("title" => app_lang('task_info') . " #$model_info->recurring_task_id", "data-post-id" => $model_info->recurring_task_id, "data-modal-lg" => "1"));
                            ?>
                        </div>
                    <?php } ?>

                    <!--recurring info-->
                    <?php if ($model_info->recurring) { ?>

                        <?php
                        $recurring_stopped = false;
                        $recurring_cycle_class = "";
                        if ($model_info->no_of_cycles_completed > 0 && $model_info->no_of_cycles_completed == $model_info->no_of_cycles) {
                            $recurring_stopped = true;
                            $recurring_cycle_class = "text-danger";
                        }
                        ?>

                        <?php
                        $cycles = $model_info->no_of_cycles_completed . "/" . $model_info->no_of_cycles;
                        if (!$model_info->no_of_cycles) { //if not no of cycles, so it's infinity
                            $cycles = $model_info->no_of_cycles_completed . "/&#8734;";
                        }
                        ?>

                        <div class="col-md-12 mb15">
                            <strong><?php echo app_lang("repeat_every") . ": "; ?> </strong> <?php echo $model_info->repeat_every . " " . app_lang("interval_" . $model_info->repeat_type); ?>
                        </div>

                        <div class="col-md-12 mb15">
                            <strong><?php echo app_lang("cycles") . ": "; ?> </strong> <span class="<?php echo $recurring_cycle_class; ?>"><?php echo $cycles; ?></span>
                        </div>

                        <?php if (!$recurring_stopped && (int) $model_info->next_recurring_date) { ?>
                            <div class="col-md-12 mb15">
                                <strong><?php echo app_lang("next_recurring_date") . ": "; ?> </strong> <?php echo format_to_date($model_info->next_recurring_date, false); ?>
                            </div>
                        <?php } ?>

                    <?php } ?>
                    <div class="col-md-12 mb15">
                        <?php
                            if ($show_timer) {
                                echo modal_anchor(get_uri("projects/timelog_modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('log_time'), array("class" => "btn btn-success", "title" => app_lang('log_time'), "data-post-project_id" => $model_info->project_id, "data-post-task_id" => $model_info->id));
                              //  echo view("projects/tasks/task_timer");
                            }
                            if($model_info->status_id == "1" && $login_user->user_type === 'staff') {
                                echo ajax_anchor(get_uri("projects/start_task_dev/" . $model_info->id), "<i data-feather='play' class='icon-16'></i> " . app_lang('start_development'), array("class" => "btn btn-info color-white mt-4", "title" => app_lang('start_development'), "data-reload-on-success" => "1"));
                            }
                        ?>
                    </div>

                    <?php if (get_setting("module_project_timesheet") == "1" && $show_timesheet_info) { ?>
                        <div class="col-md-12 mb15">
                            <strong><?php echo app_lang("total_time_logged") . ": "; ?></strong>
                            <?php
                            echo ajax_anchor(get_uri("projects/task_timesheet/" . $model_info->id . "/" . $model_info->project_id), $total_task_hours, array("data-real-target" => "#task-timesheet", "class" => "strong"));
                            ?>
                        </div>
                        <div class="col-md-12 mb15">
                            <div id="task-timesheet"></div>
                        </div>

                    <?php } ?>

                    <?php
                    $pinned_status = "hide";
                    if (count($pinned_comments)) {
                        $pinned_status = "";
                    }
                    ?>
                    <div class="col-md-12 mb15 <?php echo $pinned_status; ?>" id="pinned-comment">
                        <div class="mb5">
                            <strong><?php echo app_lang("pinned_comments") . ": "; ?> </strong>
                        </div>
                        <?php echo view("projects/comments/pinned_comments"); ?>
                    </div>

                    <?php if (can_access_reminders_module()) { ?>
                        <div class="col-md-12 mb15" id="task-reminders">
                            <div class="mb15"><strong><?php echo app_lang("reminders") . " (" . app_lang('private') . ")" . ": "; ?> </strong></div>
                            <?php echo view("reminders/reminders_view_data", array("task_id" => $model_info->id, "hide_form" => true)); ?>
                        </div>
                    <?php } ?>

                    <?php app_hooks()->do_action('app_hook_task_view_right_panel_extension'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8 mb15">
        <div class="clearfix">
            <div class="container-fluid">
                <div class="row">
                    <div  class="col-md-12 mb15 task-title-left">
                        <strong><?php echo $model_info->title; ?></strong>
                    </div>

                    <?php if ($model_info->parent_task_id) { ?>
                        <div class="col-md-12 mb15">
                            <strong><?php echo app_lang("main_task") . ": "; ?></strong><?php echo modal_anchor(get_uri("projects/task_view"), $parent_task_title, array("title" => app_lang('task_info') . " #$model_info->parent_task_id", "data-post-id" => $model_info->parent_task_id, "data-modal-lg" => "1")); ?>
                        </div>
                    <?php } ?>

                    <?php if ($model_info->description) { ?>
                        <div class="col-md-12 mb15 text-wrap">
                            <?php echo $model_info->description ? nl2br(link_it(process_images_from_content($model_info->description))) : ""; ?>
                        </div>
                    <?php } ?>

                    <?php if($login_user->user_type === 'staff' && get_setting("module_message_group")) { ?>
                        <div class="col-md-6 mb15">
                            <strong><?php echo app_lang('project') . ": "; ?> </strong> <?php echo anchor(get_uri("projects/view/" . $model_info->project_id), $model_info->project_title); ?>
                        </div>

                        <div class="col-md-6 mb15">
                            <?php if((!empty($message_group)) && $message_group->id) { ?>
                                <?php if((!empty($messages)) && $messages->id) { ?>
                                    <?php if($messages->ended == 0) { ?>
                                        <div class="enter_chat_group_message btn btn-primary btn-sm" data-id="<?= $messages->id?>"><i data-feather="mail" class="icon-16"></i> <?= app_lang('enter_conversation') ?></div>
                                    <?php }else{ ?>  

                                    <?php } ?>
                                <?php }else{ ?>
                                    <?php 
                                        echo modal_anchor(get_uri("messages/to_group_modal_form/" . $message_group->id . "/" . $model_info->id), "<i data-feather='mail' class='icon-16'></i> " . app_lang('create_conversation'), array("class" => "btn btn-primary btn-sm", "title" => app_lang('create_conversation')));
                                    ?>
                                <?php } ?>
                            <?php } ?>   
                        </div>
                    <?php } else { ?>
                        <div class="col-md-12 mb15">
                            <strong><?php echo app_lang('project') . ": "; ?> </strong> <?php echo anchor(get_uri("projects/view/" . $model_info->project_id), $model_info->project_title); ?>
                        </div>
                    <?php } ?>
                   

                    <?php
                    if (count($custom_fields_list)) {
                        foreach ($custom_fields_list as $data) {
                            if ($data->value) {
                                ?>
                                <div class="col-md-12 mb15">
                                    <strong><?php echo $data->title . ": "; ?> </strong> <?php echo view("custom_fields/output_" . $data->field_type, array("value" => $data->value)); ?>
                                </div>
                                <?php
                            }
                        }
                    }
                    ?>

                    <!--checklist-->
                    <?php echo form_open(get_uri("projects/save_checklist_item"), array("id" => "checklist_form", "class" => "general-form", "role" => "form")); ?>
                    <div class="col-md-12 mb15 b-t">
                        <div class="pb10 pt10">
                            <strong class="float-start mr10"><?php echo app_lang("checklist"); ?></strong><span class="chcklists_status_count">0</span><span>/</span><span class="chcklists_count"></span>
                        </div>
                        <input type="hidden" name="task_id" value="<?php echo $task_id; ?>" />
                        <input type="hidden" id="is_checklist_group" name="is_checklist_group" value="" />

                        <div class="checklist-items" id="checklist-items">

                        </div>
                        <?php if ($can_edit_tasks) { ?>
                            <div class="mb5 mt5 btn-group checklist-options-panel hide" role="group">
                                <button id="type-new-item-button" type="button" class="btn btn-default checklist_button active"> <?php echo app_lang('type_new_item'); ?></button>
                                <button id="select-from-template-button" type="button" class="btn btn-default checklist_button"> <?php echo app_lang('select_from_template'); ?></button>
                                <button id="select-from-checklist-group-button" type="button" class="btn btn-default checklist_button"> <?php echo app_lang('select_from_checklist_group'); ?></button>
                            </div>
                            <div class="form-group">
                                <div class="mt5 p0">
                                    <?php
                                    echo form_input(array(
                                        "id" => "checklist-add-item",
                                        "name" => "checklist-add-item",
                                        "class" => "form-control",
                                        "placeholder" => app_lang('add_item'),
                                        "data-rule-required" => true,
                                        "data-msg-required" => app_lang("field_required"),
                                        "autocomplete" => "off"
                                    ));
                                    ?>
                                </div>
                            </div>
                            <div class="mb10 p0 checklist-options-panel hide">
                                <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('add'); ?></button>
                                <button id="checklist-options-panel-close" type="button" class="btn btn-default ms-2"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('cancel'); ?></button>
                            </div>
                        <?php } ?>
                    </div>
                    <?php echo form_close(); ?>

                    <!--Sub tasks-->
                    <?php echo form_open(get_uri("projects/save_sub_task"), array("id" => "sub_task_form", "class" => "general-form", "role" => "form")); ?>
                    <div class="col-md-12 mb15 b-t">
                        <div class="pb10 pt10">
                            <strong><?php echo app_lang("sub_tasks"); ?></strong>
                        </div>
                        <input type="hidden" name="project_id" value="<?php echo $model_info->project_id; ?>" />
                        <input type="hidden" name="parent_task_id" value="<?php echo $task_id; ?>" />
                        <input type="hidden" name="milestone_id" value="<?php echo $model_info->milestone_id; ?>" />

                        <div class="checklist-items" id="sub-tasks">

                        </div>
                        <?php if ($can_create_tasks) { ?>
                            <div class="form-group">
                                <div class="mt5 col-md-12 p0">
                                    <?php
                                    echo form_input(array(
                                        "id" => "sub-task-title",
                                        "name" => "sub-task-title",
                                        "class" => "form-control",
                                        "placeholder" => app_lang('create_a_sub_task'),
                                        "data-rule-required" => true,
                                        "data-msg-required" => app_lang("field_required")
                                    ));
                                    ?>
                                </div>
                            </div>
                            <div id="sub-task-options-panel" class="col-md-12 mb15 p0 hide">
                                <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('create'); ?></button> 
                                <button id="sub-task-options-panel-close" type="button" class="btn btn-default"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('cancel'); ?></button>
                            </div>
                        <?php } ?>
                    </div>
                    <?php echo form_close(); ?>

                    <!--Task dependency-->
                    <?php if ($can_edit_tasks) { ?>
                        <div class="col-md-12 mb15">
                            <span class="dropdown">
                                <button class="btn btn-default dropdown-toggle btn-border" type="button" data-bs-toggle="dropdown" aria-expanded="true">
                                    <i data-feather="shuffle" class="icon-16"></i> <?php echo app_lang('add_dependency'); ?>
                                </button>
                                <ul class="dropdown-menu" role="menu">
                                    <li role="presentation"><?php echo js_anchor(app_lang("this_task_blocked_by"), array("class" => "add-dependency-btn dropdown-item", "data-dependency_type" => "blocked_by")); ?></li>
                                    <li role="presentation"><?php echo js_anchor(app_lang("this_task_blocking"), array("class" => "add-dependency-btn dropdown-item", "data-dependency_type" => "blocking")); ?></li>
                                </ul>
                            </span>
                        </div>
                    <?php } ?>

                    <div class="col-md-12 mb15 <?php echo ($blocked_by || $blocking) ? "" : "hide"; ?>" id="dependency-area">
                        <div class="pb10 pt10">
                            <strong><?php echo app_lang("dependency"); ?></strong>
                        </div>

                        <div class="p10 list-group-item mb15 dependency-section <?php echo $blocked_by ? "" : "hide"; ?>" id="blocked-by-area">
                            <div class="pb10"><strong><?php echo app_lang("blocked_by"); ?></strong></div>
                            <div id="blocked-by-tasks"><?php echo $blocked_by; ?></div>
                        </div>

                        <div class="p10 list-group-item mb15 dependency-section <?php echo $blocking ? "" : "hide"; ?>" id="blocking-area">
                            <div class="pb10"><strong><?php echo app_lang("blocking"); ?></strong></div>
                            <div id="blocking-tasks"><?php echo $blocking; ?></div>
                        </div>

                        <?php echo form_open(get_uri("projects/save_dependency_tasks"), array("id" => "dependency_tasks_form", "class" => "general-form hide", "role" => "form")); ?>

                        <input type="hidden" name="task_id" value="<?php echo $task_id; ?>" />

                        <div class="form-group mb0">
                            <div class="mt5 col-md-12 p0">
                                <?php
                                echo form_input(array(
                                    "id" => "dependency_task",
                                    "name" => "dependency_task",
                                    "class" => "form-control validate-hidden",
                                    "placeholder" => app_lang('tasks'),
                                    "data-rule-required" => true,
                                    "data-msg-required" => app_lang("field_required"),
                                ));
                                ?>
                            </div>
                        </div>

                        <div class="p0 mt10">
                            <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('add'); ?></button> 
                            <button type="button" class="dependency-tasks-close btn btn-default"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('cancel'); ?></button>
                        </div>

                        <?php echo form_close(); ?>
                    </div>
                    <div class="clearfix">
                        <strong><?php echo app_lang('select_to_see')  . ' ' .app_lang('comments')  . ' ' . app_lang('or')  . ' ' . app_lang('timesheets')  . '/' . app_lang('notes'); ?></strong>
                        <ul id="task-tabs" data-bs-toggle="ajax-tab" class="nav nav-tabs scrollable-tabs" role="tablist">
                            <li><a  role="presentation" data-bs-toggle="tab" data-bs-target="#task-comments"><?php echo app_lang('comments'); ?></a></li>
                            <li><a  role="presentation" data-bs-toggle="tab" data-bs-target="#task-timesheets"><?php echo app_lang('timesheets'); ?></a></li>
                        </ul>
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane fade" id="task-comments">
                                <div class="b-t pt10 list-container">
                                    <?php if ($can_comment_on_tasks) { ?>
                                        <?php echo view("projects/comments/comment_form"); ?>
                                    <?php } ?>
                                    <?php echo view("projects/comments/comment_list"); ?>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane fade" id="task-timesheets">
                                <div class="b-t pt10 list-container">
                                    <?php echo view("projects/tasks/summary_list"); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($login_user->user_type === "staff") { ?>
    <div class="box-title"><span ><?php echo app_lang("activity"); ?></span></div>
    <div class="pl15 pr15 mt15 list-container project-activity-logs-container">
        <?php echo activity_logs_widget(array("limit" => 20, "offset" => 0, "log_type" => "task", "log_type_id" => $model_info->id)); ?>
    </div>0
<?php } ?>

<script>
     $('body').on('click', '.enter_chat_group_message', function () {
        getActiveChat($(this).attr("data-id"));
    });

    setTimeout(function () {
        var tab = "<?php echo ($tab ? $tab : "comments"); ?>";
        if (tab === "comments") {
            $("[data-bs-target='#task-comments']").trigger("click");
        } else if (tab === "timesheets") {
            $("[data-bs-target='#task-timesheets']").trigger("click");
        }
    }, 210);

</script>