<?php echo form_open(get_uri("projects/transfer_timelog"), array("id" => "timelog-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
        <input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
        <input type="hidden" name="client_id" value="<?php echo $client_id; ?>" />

        
        <div class="form-group">
            <div class="row">
                <label for="task_id" class=" col-md-3"><?php echo app_lang('task'); ?></label>
                <div class="col-md-9" id="dropdown-apploader-section">
                    <?php
                    echo form_input(array(
                        "id" => "task_id",
                        "name" => "task_id",
                        "value" => $task_id ?? $model_info->task_id,
                        "class" => "form-control",
                        "placeholder" => app_lang('task'),
                        "required" => true,
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required"),
                    ));
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
    <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#timelog-form").appForm({
            onSuccess: function (result) {
                var table = $(".dataTable:visible").attr("id");
                if (table === "project-timesheet-table" || table === "all-project-timesheet-table") {
                    $("#" + table).appTable({newData: result.data, dataId: result.id});
                }
            }
        });

        $("#timelog-form .select2").select2();

        // //load all related data of the selected project
        // $("#project_id").select2().on("change", function () {
        //     var projectId = $(this).val();
        //     if (projectId) {
        //         $('#user_id').select2("destroy");
        //         $("#user_id").hide();
        //         $('#task_id').select2("destroy");
        //         $("#task_id").hide();
        //         appLoader.show({container: "#dropdown-apploader-section"});
        //         $.ajax({
        //             url: "<?php //echo get_uri('projects/get_all_related_data_of_selected_project_for_transfer_timelog') ?>" + "/" + projectId,
        //             dataType: "json",
        //             success: function (result) {
        //                 $('#task_id').select2({data: result.tasks_dropdown});
        //                 appLoader.hide();
        //             }
        //         });
        //     }
        // });

        $("#task_id").select2({data: <?php echo $tasks_dropdown; ?>});
    });
</script>