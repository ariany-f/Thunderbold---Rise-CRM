<div id="page-content" class="page-wrapper clearfix grid-button">
    <div class="card">
        <ul id="project-all-timesheet-tabs" data-bs-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
            <li class="title-tab"><h4 class="pl15 pt10 pr15"><?php echo app_lang("timesheets"); ?></h4></li>

            <li><a id="timesheet-details-button"  role="presentation" data-bs-toggle="tab" href="javascript:;" data-bs-target="#timesheet-details"><?php echo app_lang("details"); ?></a></li>
            <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("clients/all_timesheet_summary/"); ?>" data-bs-target="#timesheet-summary"><?php echo app_lang('summary'); ?></a></li>
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane fade" id="timesheet-details">
                <div class="table-responsive">
                    <table id="all-project-timesheet-table" class="display" width="100%">  
                    </table>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane fade" id="timesheet-summary"></div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var endTimeVisibility = true;
        var optionVisibility = false;    
        var projectAmount = false;

        // Função para obter parâmetros da URL
        function getUrlParameter(name) {
            name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
            var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
            var results = regex.exec(location.search);
            return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
        }

        // Obtém os parâmetros da URL
        var urlProjectId = getUrlParameter('project_id');
        var urlStartDate = getUrlParameter('start_date');
        var urlEndDate = getUrlParameter('end_date');
        var tableInitialized = false;

        $("#all-project-timesheet-table").appTable({
            source: '<?php echo_uri("projects/timesheet_client_list_data/") ?>',
            filterDropdown: [
                {name: "project_id", class: "w200", options: <?php echo $projects_dropdown; ?>, dependency: ["client_id"], dataSource: '<?php echo_uri("projects/get_projects_of_selected_client_for_filter") ?>', selfDependency: true}
                , <?php echo $custom_field_filters; ?>
            ],
            rangeDatepicker: [{
                startDate: {
                    name: "start_date",
                    value: urlStartDate || ""
                },
                endDate: {
                    name: "end_date",
                    value: urlEndDate || ""
                },
                showClearButton: true
            }],
            columns: [
                {visible: false},
                {title: "<?php echo app_lang('project') ?>", order_by: "project"},
                {title: "<?php echo app_lang('task') ?>", order_by: "task_title"},
                {visible: false, searchable: false, order_by: "start_time"},
                {title: "<?php echo app_lang('note')?>", "class": "text-center w200 limited-column"},
                {title: "<?php echo get_setting("users_can_input_only_total_hours_instead_of_period") ? app_lang("date") : app_lang('start_time') ?>", "iDataSort": 4, order_by: "start_time"},
                {visible: false, searchable: false, order_by: "end_time"},
                {title: "<?php echo app_lang('end_time') ?>", "iDataSort": 6, visible: endTimeVisibility, order_by: "end_time"},
                {title: "<?php echo app_lang('duration') ?>", "class": "text-right"},
                {visible: false, title: "<?php echo app_lang('hours') ?>", "class": "text-right"},
                {visible: projectAmount, title: "<?php echo app_lang('amount'). ' (R$)' ?>", "class": "text-right w50"},
                {title: "<?php echo app_lang('consultant') ?>", order_by: "member_name"},
                {visible: false, title: "<?php echo app_lang('consultant'). ' (R$)' ?>", "class": "text-right"},
                {visible: false, title: "<?php echo app_lang('manager_name') ?>", "class": "text-right"},
                {visible:false, title: "<?php echo app_lang('comission'). ' (R$)' ?>", "class": "text-right"},
                {visible: false, title: "<?php echo app_lang('liquid'). ' (R$)' ?>", "class": "text-right w50"}
                <?php echo $custom_field_headers; ?>,
                {visible: optionVisibility, title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100"}
            ],
            printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 5, 7, 8, 9, 10], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 5, 7, 8, 9, 10], '<?php echo $custom_field_headers; ?>'),
            onRelaodCallback: function (tableInstance, filterParams) {
                showHideAppTableColumn(tableInstance, 3, false);
                showHideAppTableColumn(tableInstance, 6, false);
                showHideAppTableColumn(tableInstance, 9, false);
                clearAppTableState(tableInstance);
            },
            summation: [{column: 8, dataType: 'time'}, {column: 10, dataType: 'currency'}, {column: 12, dataType: 'currency'}, {column: 14, dataType: 'currency'},  {column: 15, dataType: 'currency'}],
            onInitComplete: function() {
                console.log("initComplete");
                if (!tableInitialized) {
                    console.log("Aplicando filtros");
                    
                    // Se tiver project_id, aguarda o carregamento dos projetos
                    if (urlProjectId) {
                        console.log("Aguardando para aplicar project_id:", urlProjectId);
                        setTimeout(function() {
                            $("select[name='project_id']").val(urlProjectId).trigger("change");
                        }, 2000);
                    }

                    if(urlProjectId || urlStartDate || urlEndDate) {
                        tableInitialized = true;
                        $("#timesheet-details-button").trigger("click");
                    }
                }
            }
        });
    });
</script>