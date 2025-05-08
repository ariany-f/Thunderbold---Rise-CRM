<div id="page-content" class="page-wrapper clearfix grid-button">
    <div class="card">
        <ul id="project-all-timesheet-tabs" data-bs-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
            <li class="title-tab"><h4 class="pl15 pt10 pr15"><?php echo app_lang("timesheets"); ?></h4></li>

            <li><a id="timesheet-details-button"  role="presentation" data-bs-toggle="tab" href="javascript:;" data-bs-target="#timesheet-details"><?php echo app_lang("details"); ?></a></li>
            <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("projects/all_timesheet_summary/"); ?>" data-bs-target="#timesheet-summary"><?php echo app_lang('summary'); ?></a></li>
            <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("projects/timesheet_chart/"); ?>" data-bs-target="#timesheet-chart"><?php echo app_lang('chart'); ?></a></li>
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane fade" id="timesheet-details">
                <div class="table-responsive">
                    <table id="all-project-timesheet-table" class="display" width="100%">  
                    </table>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane fade" id="timesheet-summary"></div>
            <div role="tabpanel" class="tab-pane fade" id="timesheet-chart"></div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var endTimeVisibility = true;
<?php if (get_setting("users_can_input_only_total_hours_instead_of_period")) { ?>
            endTimeVisibility = false;
<?php } ?>
    
        var optionVisibility = false;
        <?php if ($login_user->user_type === "staff" && ($login_user->is_admin || get_array_value($login_user->permissions, "timesheet_manage_permission"))) { ?>
                    optionVisibility = true;
        <?php } ?>
        
        var projectAmount = false;
        <?php if ($login_user->is_admin) { ?>
                projectAmount = true;
        <?php } ?>

        // Função para obter parâmetros da URL
        function getUrlParameter(name) {
            name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
            var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
            var results = regex.exec(location.search);
            return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
        }

        // Obtém os parâmetros da URL
        var urlClientId = getUrlParameter('client_id');
        var urlProjectId = getUrlParameter('project_id');
        var urlStartDate = getUrlParameter('start_date');
        var urlEndDate = getUrlParameter('end_date');
        var tableInitialized = false;

        var table = $("#all-project-timesheet-table").appTable({
            source: '<?php echo_uri("projects/timesheet_list_data/") ?>',
            stateSave:true,
            filterDropdown: [
                {name: "user_id", class: "w200", options: <?php echo $members_dropdown; ?>},
                {name: "manager_id", class: "w200", options: <?php echo $managers_dropdown; ?>},
                {name: "project_id", class: "w200", options: <?php echo $projects_dropdown; ?>, dependency: ["client_id"], dataSource: '<?php echo_uri("projects/get_projects_of_selected_client_for_filter") ?>', selfDependency: true} //projects are dependent on client. but we have to show all projects, if there is no selected client
<?php if ($login_user->is_admin || get_array_value($login_user->permissions, "client")) { ?>
                    , {name: "client_id", class: "w200", options: <?php echo $clients_dropdown; ?>, dependent: ["project_id"]} //reset projects on changing of client
<?php } ?>
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
            //rangeDatepicker: [{startDate: {name: "start_date", value: moment().format("YYYY-MM-DD")}, endDate: {name: "end_date", value: moment().format("YYYY-MM-DD")}, showClearButton: true}],
            dateRangeType: "monthly",
            columns: [
                {title: "<?php echo app_lang('client') ?>", order_by: "client"},
                {title: "<?php echo app_lang('project') ?>", order_by: "project"},
                {title: "<?php echo app_lang('task') ?>", order_by: "task_title"},
                {visible: false, searchable: false, order_by: "start_time"},
                {title: "<?php echo app_lang('note')?>", "class": "text-center w200 limited-column"},
                {title: "<?php echo get_setting("users_can_input_only_total_hours_instead_of_period") ? app_lang("date") : app_lang('start_time') ?>", "iDataSort": 4, order_by: "start_time"},
                {visible: false, searchable: false, order_by: "end_time"},
                {title: "<?php echo app_lang('end_time') ?>", "iDataSort": 6, visible: endTimeVisibility, order_by: "end_time"},
                {title: "<?php echo app_lang('duration') ?>", "class": "text-right"},
                {visible: false, title: "<?php echo app_lang('hours') ?>", "class": "text-right"},
                {visible: projectAmount, title: "<?php echo app_lang('charge'). ' (R$)' ?>", "class": "text-right w50"},
                {title: "<?php echo app_lang('consultant') ?>", order_by: "member_name"},
                {visible: projectAmount, title: "<?php echo app_lang('consultant'). ' (R$)' ?>", "class": "text-right"},
                {title: "<?php echo app_lang('manager_name') ?>", "class": "text-right"},
                {visible:projectAmount, title: "<?php echo app_lang('comission'). ' (R$)' ?>", "class": "text-right"},
                {visible: projectAmount, title: "<?php echo app_lang('liquid'). ' (R$)' ?>", "class": "text-right w50"}
                <?php echo $custom_field_headers; ?>,
                {visible: optionVisibility, title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100"}
            ],
            printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 7, 8, 9, 10], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 7, 8, 9, 10], '<?php echo $custom_field_headers; ?>'),
            onRelaodCallback: function (tableInstance, filterParams) {
                showHideAppTableColumn(tableInstance, 3, false);
                showHideAppTableColumn(tableInstance, 6, false);
                showHideAppTableColumn(tableInstance, 9, false);
                clearAppTableState(tableInstance);
            },
            summation: [{column: 8, dataType: 'time'}, {column: 10, dataType: 'currency'}, {column: 12, dataType: 'currency'}, {column: 14, dataType: 'currency'},  {column: 15, dataType: 'currency'}],
            onInitComplete: function() {
                if (!tableInitialized) {

                    // Aplica o filtro do cliente se existir
                    if (urlClientId) {
                        $("select[name='client_id']").val(urlClientId).trigger("change");
                        
                        // Se tiver project_id, aguarda o carregamento dos projetos
                        if (urlProjectId) {
                           
                            // Aguarda o carregamento dos projetos após o client_id
                            setTimeout(function() {
                                $("select[name='project_id']").val(urlProjectId).trigger("change");
                            }, 2000);
                        }
                    }

                    // Aplica os filtros de data se existirem
                    if (urlStartDate && urlEndDate) {
                       
                        
                        var datepickerr = $(".datepicker");
                        // Função para tentar aplicar as datas
                        function tryApplyDates() {
                            var $datepicker = $(".datepicker");
                            if ($datepicker.length && $datepicker.data('daterangepicker')) {
                                console.log("Datepicker encontrado, aplicando datas");
                                $datepicker.data('daterangepicker').setStartDate(moment(urlStartDate));
                                $datepicker.data('daterangepicker').setEndDate(moment(urlEndDate));
                                $datepicker.trigger("change");
                            } else {
                                console.log("Datepicker não encontrado, tentando novamente...");
                                // setTimeout(tryApplyDates, 200);
                            }
                        }

                        // Inicia a tentativa de aplicar as datas
                        // setTimeout(tryApplyDates, 100);
                    }

                    
                    if(urlClientId || urlProjectId || urlStartDate || urlEndDate) {
                    
                        tableInitialized = true;

                        $("#timesheet-details-button").trigger("click");
                    }
                }
            }
        });
    });
</script>