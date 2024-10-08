
<div class="card">
    <div class="row">
        <?php if (get_setting('module_project_timesheet')) { ?>
            <div class="col-md-6 col-sm-12">
                <?php echo view("projects/widgets/total_hours_worked_widget"); ?>
            </div>
            <div class="col-md-6 col-sm-12">
                <?php echo view("attendance/project_limit_hours"); ?>
            </div>
        <?php } ?>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="float-start"><?php echo app_lang('managers'); ?></h6>
        <?php
        if ($login_user->user_type == 'staff') {
            echo modal_anchor(get_uri("projects/project_resource_manager_modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_manager'), array("class" => "btn btn-outline-light float-end add-member-button", "title" => app_lang('add_manager'), "data-post-project_id" => $project_id));
        }
        ?>
    </div>
    <div class="card-header">
        <div class="text-off">
            <span>Este apontamento de horas reflete nas horas registradas pelos recursos nesse projeto/chamado </span>
        </div>
    </div>
    <div class="table-responsive">
        <table id="manager-table" class="b-b-only" width="100%">
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="float-start"><?php echo app_lang('resources'); ?></h6>
    </div>

    <div class="table-responsive">
        <table id="project-resource-table" class="b-b-only" width="100%">
        </table>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {

        $("#manager-table").appTable({
            source: '<?php echo_uri("projects/project_resource_list_data/" . $project_id . "/manager") ?>',
            hideTools: true,
            columns: [
                {title: 'Nome', "class":"text-left w400"},
                {title: 'Valor/Hora'},
                {title: '', "class": "text-center option w100"}
            ]
        });

        $("#project-resource-table").appTable({
            source: '<?php echo_uri("projects/project_resource_list_data/" . $project_id) ?>',
            hideTools: true,
            displayLength: 500,
            columns: [
                {title: 'Membro', "class":"text-left w400"},
                {title: 'Valor/Hora'},
                {title: 'Horas Trabalhadas'},
                {title: 'Total'},
                {title: '', "class": "text-center option w100"}
            ]
        });
    });
</script>