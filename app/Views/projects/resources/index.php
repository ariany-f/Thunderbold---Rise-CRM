<div class="card">
    <div class="card-header">
        <h6 class="float-start"><?php echo app_lang('managers'); ?></h6>
        <?php
        if ($login_user->user_type == 'staff') {
            echo modal_anchor(get_uri("projects/project_resource_manager_modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_manager'), array("class" => "btn btn-outline-light float-end add-member-button", "title" => app_lang('add_manager'), "data-post-project_id" => $project_id));
        }
        ?>
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
                {title: 'Nome'},
                {title: 'Valor/Hora'},
                {title: '', "class": "text-center option w100"}
            ]
        });

        $("#project-resource-table").appTable({
            source: '<?php echo_uri("projects/project_resource_list_data/" . $project_id) ?>',
            hideTools: true,
            displayLength: 500,
            columns: [
                {title: 'Membro'},
                {title: 'Valor/Hora'},
                {title: '', "class": "text-center option w100"}
            ]
        });
    });
</script>