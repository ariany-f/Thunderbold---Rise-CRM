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
                {title: "<?php echo app_lang("member"); ?>", "class": "w15p"},
                {title: "<?php echo app_lang("task"); ?>", "class": "w15p"},
                {title: "<?php echo app_lang("duration"); ?>", "class": "w15p text-right"},
                {title: "<?php echo app_lang("hours"); ?>", "class": "w15p text-right"},
                {visible: projectAmount, title: "<?php echo app_lang('charge') ?>", "class": "text-center w50"},
                {title: "<?php echo app_lang("consultant"); ?>", "class": "text-center"},
                {title: "<?php echo app_lang("manager"); ?>", "class": "text-center"},
                {title: "<?php echo app_lang("manager_name"); ?>", "class": "text-center"},
                {visible: projectAmount, title: "<?php echo app_lang('liquid') ?>", "class": "text-center w50"}
            ],
            printColumns: [2, 3, 4, 5, 6],
            xlsColumns: [2, 3, 4, 5, 6],
            summation: [{column: 4, dataType: 'time'}, {column: 5, dataType: 'number'}, {column: 6, dataType: 'currency'}, {column: 7, dataType: 'currency'},  {column: 8, dataType: 'currency'},  {column: 10, dataType: 'currency'}],
            onRelaodCallback: function (tableInstance, filterParams) {

                //we'll show/hide the task/member column based on the group by status
                if (filterParams && filterParams.group_by === "member") {
                    showHideAppTableColumn(tableInstance, 2, true);
                    showHideAppTableColumn(tableInstance, 3, false);
                } else if (filterParams && filterParams.group_by === "task") {
                    showHideAppTableColumn(tableInstance, 2, false);
                    showHideAppTableColumn(tableInstance, 3, true);
                } else {
                    showHideAppTableColumn(tableInstance, 2, true);
                    showHideAppTableColumn(tableInstance, 3, true);
                }

                //clear this status for next time load
                clearAppTableState(tableInstance);
            }
        });
    });
</script>