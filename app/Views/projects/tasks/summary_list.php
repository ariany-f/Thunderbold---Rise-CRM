<div class="table-responsive">
    <table id="timesheet-summary-table" class="display" cellspacing="0" width="100%">            
    </table>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $("#timesheet-summary-table").appTable({
            source: '<?php echo_uri("projects/timesheet_list_data/"); ?>',
            stateSave: false,
            filterParams: {project_id: "<?php echo $project_id; ?>", task_id: "<?php echo $model_info->id; ?>"},
            dateRangeType: "yearly",
            hideTools: true,
            columns: [
                {visible: false, title: "<?php echo app_lang('client') ?>", order_by: "client"},
                {visible: false, title: "<?php echo app_lang('project') ?>", order_by: "project"},
                {visible: false, title: "<?php echo app_lang('task') ?>", order_by: "task_title"},
                {visible: false, searchable: false, order_by: "start_time"},
                {title: "<?php echo app_lang('note')?>", "class": "text-center w200 limited-column"},
                {title: "<?php echo get_setting("users_can_input_only_total_hours_instead_of_period") ? app_lang("date") : app_lang('start_time') ?>", "iDataSort": 4, order_by: "start_time"},
                {visible: false, searchable: false, order_by: "end_time"},
                {title: "<?php echo app_lang('end_time') ?>", "iDataSort": 6, visible: true, order_by: "end_time"},
                {title: "<?php echo app_lang('duration') ?>", "class": "text-right"},
                {visible: false, title: "<?php echo app_lang('hours') ?>", "class": "text-right"},
                {visible: false, title: "<?php echo app_lang('charge'). ' (R$)' ?>", "class": "text-right w50"},
                {title: "<?php echo app_lang('consultant') ?>", order_by: "member_name"},
                {visible: false, title: "<?php echo app_lang('consultant'). ' (R$)' ?>", "class": "text-right"},
                {visible: false, title: "<?php echo app_lang('manager_name') ?>", "class": "text-right"},
                {visible: false, title: "<?php echo app_lang('comission'). ' (R$)' ?>", "class": "text-right"},
                {visible: false, title: "<?php echo app_lang('liquid'). ' (R$)' ?>", "class": "text-right w50"},
                {visible: false, title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100"}
            ],
            onRelaodCallback: function (tableInstance, filterParams) {
                //clear this status for next time load
                clearAppTableState(tableInstance);
            }
        });
    });
</script>