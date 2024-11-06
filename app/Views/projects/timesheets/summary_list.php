<div class="table-responsive">
    <table id="timesheet-summary-table" class="display" cellspacing="0" width="100%">            
    </table>
</div>
<script type="text/javascript">
    var projectAmount = false;
    <?php if ($login_user->is_admin) { ?>
            projectAmount = true;
    <?php } ?>

    $(document).ready(function () {
        $("#timesheet-summary-table").appTable({
            source: '<?php echo_uri("projects/timesheet_summary_list_data/"); ?>',
            stateSave:true,
            filterParams: {project_id: "<?php echo $project_id; ?>"},
            filterDropdown: [
                {name: "user_id", class: "w200", options: <?php echo $project_members_dropdown; ?>},
                {name: "task_id", class: "w200", options: <?php echo $tasks_dropdown; ?>},
                {name: "group_by", class: "w200", options: <?php echo $group_by_dropdown; ?>},
                <?php echo $custom_field_filters; ?>
            ],
            //rangeDatepicker: [{startDate: {name: "start_date", value: ""}, endDate: {name: "end_date", value: ""}, showClearButton: true}],
            dateRangeType: "monthly",
            columns: [
                {visible: false, searchable: false},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("task"); ?>", "class": "text-left"},
                {title: "<?php echo app_lang("duration"); ?>", "class": "text-left"},
                {visible: false, title: "<?php echo app_lang("hours"); ?>"},
                {visible: projectAmount, title: "<?php echo app_lang('charge'). ' (R$)' ?>", "class": "text-right"},
                {title: "<?php echo app_lang("consultant"); ?>", "class": "w15p"},
                {visible: projectAmount, title: "<?php echo app_lang("consultant") . ' (R$)'; ?>", "class": "text-right"},
                {title: "<?php echo app_lang("manager_name"); ?>", "class": "text-center"},
                {visible:projectAmount, title: "<?php echo app_lang("comission"). ' (R$)'; ?>", "class": "text-right"},
                {visible: projectAmount, title: "<?php echo app_lang('liquid'). ' (R$)' ?>", "class": "text-right"},
                {visible: false, searchable: false}
            ],
            printColumns: [2, 3, 4, 5, 6, 7, 8, 9, 10],
            xlsColumns: [2, 3, 4, 5, 6, 7, 8, 9, 10],
            summation: [{column: 3, dataType: 'time'}, {column: 5, dataType: 'currency'}, {column: 7, dataType: 'currency'},  {column: 9, dataType: 'currency'},  {column: 10, dataType: 'currency'}],
            onRelaodCallback: function (tableInstance, filterParams) {

                //we'll show/hide the task/member column based on the group by status
                if (filterParams && filterParams.group_by === "member") {
                    showHideAppTableColumn(tableInstance, 2, false);
                    showHideAppTableColumn(tableInstance, 3, true);
                    // showHideAppTableColumn(tableInstance, 2, false);
                    // showHideAppTableColumn(tableInstance, 6, true);
                    showHideAppTableColumn(tableInstance, 8, false);
                    showHideAppTableColumn(tableInstance, 9, false);
                } else if (filterParams && filterParams.group_by === "task") {
                    showHideAppTableColumn(tableInstance, 2, true);
                    showHideAppTableColumn(tableInstance, 3, true);
                    showHideAppTableColumn(tableInstance, 6, false);
                    // showHideAppTableColumn(tableInstance, 2, false);
                } else {
                    showHideAppTableColumn(tableInstance, 3, true);
                    showHideAppTableColumn(tableInstance, 2, true);
                    // showHideAppTableColumn(tableInstance, 2, false);
                    // showHideAppTableColumn(tableInstance, 6, true);
                }

                //clear this status for next time load
                clearAppTableState(tableInstance);
            }
        });
    });
</script>