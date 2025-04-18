<div id="page-content" class="page-wrapper clearfix grid-button">
    <div class="card clearfix">
        <ul id="expenses-tabs" data-bs-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
            <li class="title-tab"><h4 class="pl15 pt10 pr15"><?php echo app_lang("expenses"); ?></h4></li>
            <li><a id="monthly-expenses-button" role="presentation" data-bs-toggle="tab" href="javascript:;" data-bs-target="#monthly-expenses"><?php echo app_lang("monthly"); ?></a></li>
            <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("expenses/yearly/"); ?>" data-bs-target="#yearly-expenses"><?php echo app_lang('yearly'); ?></a></li>
            <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("expenses/custom/"); ?>" data-bs-target="#custom-expenses"><?php echo app_lang('custom'); ?></a></li>
            <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("expenses/recurring/"); ?>" data-bs-target="#recurring-expenses"><?php echo app_lang('recurring'); ?></a></li>
            <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("expenses/summary/"); ?>" data-bs-target="#expenses-summary"><?php echo app_lang('summary'); ?></a></li>
            <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("expenses/yearly_chart/"); ?>" data-bs-target="#yearly-chart"><?php echo app_lang('chart'); ?></a></li>
            <div class="tab-title clearfix no-border expenses-page-title">
                <div class="title-button-group">
                    <?php echo modal_anchor(get_uri("expenses/import_expenses_modal_form"), "<i data-feather='upload' class='icon-16'></i> " . app_lang('import_expense'), array("class" => "btn btn-default mb0", "title" => app_lang('import_expense'))); ?>
                    <?php echo modal_anchor(get_uri("expenses/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_expense'), array("class" => "btn btn-default mb0", "title" => app_lang('add_expense'))); ?>
                </div>
            </div>
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane fade" id="monthly-expenses">
                <div class="table-responsive">
                    <table id="monthly-expense-table" class="display" cellspacing="0" width="100%">
                    </table>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane fade" id="yearly-expenses"></div>
            <div role="tabpanel" class="tab-pane fade" id="custom-expenses"></div>
            <div role="tabpanel" class="tab-pane fade" id="recurring-expenses"></div>
            <div role="tabpanel" class="tab-pane fade" id="expenses-summary"></div>
            <div role="tabpanel" class="tab-pane fade" id="yearly-chart"></div>
        </div>
    </div>
</div>

<script type="text/javascript">
    loadExpensesTable = function (selector, dateRange) {
    var customDatePicker = "", recurring = "0";
    if (dateRange === "custom" || dateRange === "recurring") {
    customDatePicker = [{startDate: {name: "start_date", value: moment().format("YYYY-MM-DD")}, endDate: {name: "end_date", value: moment().format("YYYY-MM-DD")}, showClearButton: true}];
    if (dateRange === "recurring"){
    recurring = "1";
    }

    dateRange = "";
    }

    $(selector).appTable({
    source: '<?php echo_uri("expenses/list_data") ?>/' + recurring,
            dateRangeType: dateRange,
            filterDropdown: [
            {name: "category_id", class: "w200", options: <?php echo $categories_dropdown; ?>},
            {name: "user_id", class: "w200", options: <?php echo $members_dropdown; ?>},
<?php if ($projects_dropdown) { ?>
                 {name: "project_id", class: "w200", options: <?php echo $projects_dropdown; ?>},
<?php } ?>
            {name: "group_by", class: "w200", options: <?php echo $group_by_dropdown; ?>}
            ,<?php echo $custom_field_filters; ?>
            ],
            order: [[0, "asc"]],
            rangeDatepicker: customDatePicker,
            columns: [
            {title: '<?php echo app_lang("id") ?>', "class": "w50"},
            {visible: false, searchable: false},
            {title: '<?php echo app_lang("date") ?>', "iDataSort": 0, "class": "all"},
            {title: '<?php echo app_lang("member") ?>'},
            {title: '<?php echo app_lang("category") ?>'},
            {title: '<?php echo app_lang("title") ?>', "class": "all"},
            {title: '<?php echo app_lang("description") ?>'},
            {title: '<?php echo app_lang("files") ?>'},
            {title: '<?php echo app_lang("amount") ?>', "class": "text-right"},
            {visible: false, title: '<?php echo app_lang("tax") ?>', "class": "text-right"},
            {visible: false, title: '<?php echo app_lang("second_tax") ?>', "class": "text-right"},
            {title: '<?php echo app_lang("total") ?>', "class": "text-right"},
            {title: "<?php echo app_lang("start_timesheet_filter") ?>"},
            {title: "<?php echo app_lang("end_timesheet_filter") ?>"}
<?php echo $custom_field_headers; ?>,
            {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100"}
            ],
            printColumns: combineCustomFieldsColumns([1, 2, 3, 4, 6, 7, 8, 9], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([1, 2, 3, 4, 6, 7, 8, 9], '<?php echo $custom_field_headers; ?>'),
            summation: [{column: 8, dataType: 'currency'}, {column: 11, dataType: 'currency'}],
            onRelaodCallback: function (tableInstance, filterParams) {

            //we'll show/hide the task/member column based on the group by status
            if (filterParams && filterParams.group_by === "member") {
                showHideAppTableColumn(tableInstance, 3, true);
                showHideAppTableColumn(tableInstance, 5, false);
                showHideAppTableColumn(tableInstance, 6, false);
                showHideAppTableColumn(tableInstance, 7, false);
            } else if (filterParams && filterParams.group_by === "project") {
                showHideAppTableColumn(tableInstance, 3, false);
                showHideAppTableColumn(tableInstance, 5, true);
                showHideAppTableColumn(tableInstance, 6, true);
                showHideAppTableColumn(tableInstance, 7, true);
            } else if (filterParams && filterParams.group_by === "member/project") {
                showHideAppTableColumn(tableInstance, 3, true);
                showHideAppTableColumn(tableInstance, 5, true);
                showHideAppTableColumn(tableInstance, 6, true);
                showHideAppTableColumn(tableInstance, 7, true);
            } else {
                showHideAppTableColumn(tableInstance, 3, true);
                showHideAppTableColumn(tableInstance, 5, true);
                showHideAppTableColumn(tableInstance, 6, true);
                showHideAppTableColumn(tableInstance, 7, true);
            }
            //clear this status for next time load
            clearAppTableState(tableInstance);
            }
    });
    };
    $(document).ready(function () {
    loadExpensesTable("#monthly-expense-table", "monthly");
    });
</script>
