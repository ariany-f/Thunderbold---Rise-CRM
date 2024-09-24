<div id="page-content" class="page-wrapper clearfix grid-button all-tasks-view">

    <ul class="nav nav-tabs bg-white title" role="tablist">
        <li class="title-tab my-tasks"><h4 class="pl15 pt10 pr15"><?php echo app_lang("tasks"); ?></h4></li>

        <?php echo view("projects/tasks/tabs", array("active_tab" => "tasks_list", "selected_tab" => $tab)); ?>

        <div class="tab-title clearfix no-border">
            <div class="title-button-group">
                <?php
                if ($login_user->user_type == "staff") {
                    echo modal_anchor("", "<i data-feather='edit-2' class='icon-16'></i> " . app_lang('batch_update'), array("class" => "btn btn-info text-white hide batch-update-btn", "title" => app_lang('batch_update')));
                    echo js_anchor("<i data-feather='check-square' class='icon-16 ml15'></i> " . app_lang("batch_update"), array("class" => "btn btn-default hide batch-active-btn"));
                    echo js_anchor("<i data-feather='x' class='icon-16'></i> " . app_lang("cancel_selection"), array("class" => "hide btn btn-default batch-cancel-btn"));
                }
                if ($can_create_tasks) {
                    echo modal_anchor(get_uri("projects/import_tasks_modal_form"), "<i data-feather='upload' class='icon-16'></i> " . app_lang('import_tasks'), array("class" => "btn btn-default", "title" => app_lang('import_tasks')));
                    echo modal_anchor(get_uri("projects/task_modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_multiple_tasks'), array("class" => "btn btn-default", "title" => app_lang('add_multiple_tasks'), "data-post-add_type" => "multiple"));
                    echo modal_anchor(get_uri("projects/task_modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_task'), array("class" => "btn btn-success", "title" => app_lang('add_task')));
                }
                ?>
            </div>
        </div>

    </ul>

    <div class="card">
        <div class="table-responsive">
            <table id="task-table" class="display" cellspacing="0" width="100%">            
            </table>
        </div>
    </div>
</div>

<?php
//if we get any task parameter, we'll show the task details modal automatically
$preview_task_id = get_array_value($_GET, 'task');
if ($preview_task_id) {
    echo modal_anchor(get_uri("projects/task_view"), "", array("id" => "preview_task_link", "title" => app_lang('task_info') . " #$preview_task_id", "data-post-id" => $preview_task_id));
}

$statuses = array();
foreach ($task_statuses as $status) {
    $is_selected = false;

    if (isset($selected_status_id) && $selected_status_id) {
        //if there is any specific status selected, select only the status.
        if ($selected_status_id == $status->id) {
            $is_selected = true;
        }
    } else if ($status->key_name != "done") {
        $is_selected = true;
    }

    $statuses[] = array("text" => ($status->key_name ? app_lang($status->key_name) : $status->title), "value" => $status->id, "isChecked" => $is_selected);
}
?>

<script type="text/javascript">
    $(document).ready(function () {

        var showOption = true,
                idColumnClass = "w5p",
                titleColumnClass = "project-title w25p";

        if (isMobile()) {
            showOption = false;
            idColumnClass = "w25p";
            titleColumnClass = "w75p";
        }

        $("#task-table").appTable({
            source: '<?php echo_uri("projects/my_tasks_list_data") ?>',
            serverSide: true,
            order: [[1, "desc"]],
            responsive: false, //hide responsive (+) icon
            filterDropdown: [
                {name: "specific_user_id", class: "w150", options: <?php echo $team_members_dropdown; ?>},
                {name: "milestone_id", class: "w150", options: [{id: "", text: "- <?php echo app_lang('milestone'); ?> -"}], dependency: ["project_id"], dataSource: '<?php echo_uri("projects/get_milestones_for_filter") ?>'}, //milestone is dependent on project
                {name: "priority_id", class: "w100", options: <?php echo $priorities_dropdown; ?>},
                {name: "is_ticket", class: "w100", options: <?php echo $project_type_dropdown; ?>},
                {name: "project_id", class: "w200", options: <?php echo $projects_dropdown; ?>, dependent: ["milestone_id"]}, //reset milestone on changing of project
                {name: "quick_filter", class: "w200", showHtml: true, options: <?php echo view("projects/tasks/quick_filters_dropdown"); ?>}
                , <?php echo $custom_field_filters; ?>
            ],
            singleDatepicker: [{name: "deadline", defaultText: "<?php echo app_lang('deadline') ?>",
                    options: [
                        {value: "expired", text: "<?php echo app_lang('expired') ?>"},
                        {value: moment().format("YYYY-MM-DD"), text: "<?php echo app_lang('today') ?>"},
                        {value: moment().add(1, 'days').format("YYYY-MM-DD"), text: "<?php echo app_lang('tomorrow') ?>"},
                        {value: moment().add(7, 'days').format("YYYY-MM-DD"), text: "<?php echo sprintf(app_lang('in_number_of_days'), 7); ?>"},
                        {value: moment().add(15, 'days').format("YYYY-MM-DD"), text: "<?php echo sprintf(app_lang('in_number_of_days'), 15); ?>"}
                    ]}],
            multiSelect: [
                {
                    name: "status_id",
                    text: "<?php echo app_lang('status'); ?>",
                    options: <?php echo json_encode($statuses); ?>
                }
            ],
            columns: [
                {visible: false, searchable: false},
                {title: '<?php echo app_lang("id") ?>', "class": idColumnClass, order_by: "id"},
                {title: '<?php echo app_lang("title") ?>', "class": titleColumnClass, order_by: "title"},
                {visible: false, searchable: false, order_by: "created_date"},
                {title: '<?php echo app_lang("included_date") ?>', "iDataSort": 3, visible: showOption, order_by: "created_date"},
                {visible: false, searchable: false, order_by: "start_date"},
                {title: '<?php echo app_lang("start_date") ?>', "iDataSort": 3, visible: showOption, order_by: "start_date"},
                {visible: false, searchable: false, order_by: "deadline"},
                {title: '<?php echo app_lang("deadline") ?>', "iDataSort": 5, visible: showOption, order_by: "deadline"},
                {visible: false, searchable: false, order_by: "deadline"},
                {title: '<?php echo app_lang("is_ticket") ?>', "class": 'w5p', order_by: "is_ticket"},
                {title: '<?php echo app_lang("project") ?>', visible: showOption, order_by: "project"},
                {title: '<?php echo app_lang("assigned_to") ?>', "class": "min-w150", visible: showOption, order_by: "assigned_to"},
                {title: '<?php echo app_lang("collaborators") ?>', visible: showOption},
                {title: '<?php echo app_lang("status") ?>', visible: showOption, order_by: "status"},
                {title: '<?php echo app_lang("timesheet_total") ?>', 'class': 'text-center', visible: showOption, order_by: "timesheet_total"}
                <?php echo $custom_field_headers; ?>,
                {visible: false, searchable: false}
            ],
            printColumns: combineCustomFieldsColumns([1, 2, 4, 6, 7, 8, 9, 10, 12], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([1, 2, 4, 6, 7, 8, 9, 10, 12], '<?php echo $custom_field_headers; ?>'),
            summation: [{column: 15, dataType: 'time'}, {column: 16, dataType: 'time'}],
            rowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                $('td:eq(0)', nRow).attr("style", "border-left:5px solid " + aData[0] + " !important;");

                //add activated sub task filter class
                setTimeout(function () {
                    var searchValue = $('#task-table').closest(".dataTables_wrapper").find("input[type=search]").val();
                    if (searchValue.substring(0, 1) === "#") {
                        $('#task-table').find("[main-task-id='" + searchValue + "']").removeClass("filter-sub-task-button").addClass("remove-filter-button sub-task-filter-active");
                    }
                }, 50);
            },
            onRelaodCallback: function () {
                hideBatchTasksBtn(true);
            },
            onInitComplete: function () {
                if (!showOption) {
                    window.scrollTo(0, 210); //scroll to the content for mobile devices
                }
            }
        });


        //open task details modal automatically 

        if ($("#preview_task_link").length) {
            $("#preview_task_link").trigger("click");
        }

        setTimeout(function () {
            var tab = "<?php echo $tab; ?>";
            if (tab === "tasks_list") {
                $("[data-tab='#tasks_list']").trigger("click");

                //save the selected tab in browser cookie
                setCookie("selected_tab_" + "<?php echo $login_user->id; ?>", "tasks_list");
            }
        }, 210);

    });
</script>

<?php echo view("projects/tasks/batch_update/batch_update_script"); ?>
<?php echo view("projects/tasks/update_task_script"); ?>
<?php echo view("projects/tasks/update_task_read_comments_status_script"); ?>
<?php echo view("projects/tasks/quick_filters_helper_js"); ?>