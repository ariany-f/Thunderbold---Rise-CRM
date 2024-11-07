<div class="table-responsive">
    <table id="all-timesheet-summary-table" class="display" cellspacing="0" width="100%">            
    </table>
</div>
<script type="text/javascript">
    var projectAmount = false;
    <?php if ($login_user->is_admin) { ?>
            projectAmount = true;
    <?php } ?>

    $(document).ready(function () {
        $("#all-timesheet-summary-table").appTable({
            source: '<?php echo_uri("projects/timesheet_client_summary_list_data/"); ?>',
            stateSave:true,
            filterDropdown: [
                {name: "project_id", class: "w200", options: <?php echo $projects_dropdown; ?>, dependency: ["client_id"], dataSource: '<?php echo_uri("projects/get_projects_of_selected_client_for_filter") ?>', selfDependency: true}, //projects are dependent on client. but we have to show all projects, if there is no selected client
                {name: "group_by", class: "w200", options: <?php echo $group_by_dropdown; ?>},
                <?php echo $custom_field_filters; ?>
            ],
            //rangeDatepicker: [{startDate: {name: "start_date", value: moment().format("YYYY-MM-DD")}, endDate: {name: "end_date", value: moment().format("YYYY-MM-DD")}, showClearButton: true}],
            dateRangeType: "monthly",
            columns: [
                {visible: false},
                {title: "<?php echo app_lang("project"); ?>"},
                {title: "<?php echo app_lang("task"); ?>"},
                {title: "<?php echo app_lang("duration"); ?>"},
                {visible: false, title: "<?php echo app_lang("hours"); ?>"},
                {visible: projectAmount, title: "<?php echo app_lang('amount'). ' (R$)' ?>", "class": "text-right"},
                {title: "<?php echo app_lang("consultant"); ?>"},
                {visible: false, title: "<?php echo app_lang('consultant'). ' (R$)' ?>", "class": "text-right"},
                {title: "<?php echo app_lang('manager_name') ?>", "class": "text-right"},
                {visible: false, title: "<?php echo app_lang('comission'). ' (R$)' ?>", "class": "text-right"},
                {visible: false, title: "<?php echo app_lang('liquid') . ' (R$)'?>", "class": "text-right"},
                {visible:false, title: "<i data-feather='menu' class='icon-16'></i>", "class": "text-center w150"}
            ],
            printColumns: [0, 1, 2, 3, 4, 5],
            xlsColumns: [0, 1, 2, 3, 4, 5],
            summation: [{column: 3, dataType: 'time'}, {column: 5, dataType: 'currency'}, {column: 7, dataType: 'currency'},  {column: 9, dataType: 'currency'},  {column: 10, dataType: 'currency'}],
            onRelaodCallback: function (tableInstance, filterParams) {

                //we'll show/hide the task/member column based on the group by status

                if (filterParams && filterParams.group_by === "member") {
                    //show member
                    showHideAppTableColumn(tableInstance, 1, false);
                    showHideAppTableColumn(tableInstance, 2, false);
                    showHideAppTableColumn(tableInstance, 4, false);

                    showHideAppTableColumn(tableInstance, 3, true);
                    showHideAppTableColumn(tableInstance, 6, true);

                } else if (filterParams && filterParams.group_by === "project") {
                    //show project
                    showHideAppTableColumn(tableInstance, 1, true);
                    showHideAppTableColumn(tableInstance, 2, false);
                    showHideAppTableColumn(tableInstance, 3, true);
                    showHideAppTableColumn(tableInstance, 4, false);
                    showHideAppTableColumn(tableInstance, 5, true);
                    showHideAppTableColumn(tableInstance, 6, false);

                } else if (filterParams && filterParams.group_by === "task") {
                    //show task
                    showHideAppTableColumn(tableInstance, 1, true);
                    showHideAppTableColumn(tableInstance, 2, true);
                    showHideAppTableColumn(tableInstance, 3, true);
                    showHideAppTableColumn(tableInstance, 4, false);
                    showHideAppTableColumn(tableInstance, 5, true);
                    showHideAppTableColumn(tableInstance, 6, true);

                } else {
                    //show all
                    showHideAppTableColumn(tableInstance, 1, true);
                    showHideAppTableColumn(tableInstance, 2, false);
                    showHideAppTableColumn(tableInstance, 3, true);
                    showHideAppTableColumn(tableInstance, 4, false);
                    showHideAppTableColumn(tableInstance, 5, true);
                    showHideAppTableColumn(tableInstance, 6, true);
                }

                //clear this status for next time load
                clearAppTableState(tableInstance);
            }
        });
    });
</script>