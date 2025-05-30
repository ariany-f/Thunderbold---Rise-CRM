<?php echo form_open(get_uri("projects/save_project_resource_manager"), array("id" => "project-resource-form", "class" => "general-form", "role" => "form")); ?>


<div class="modal-body clearfix">
    <div class="container-fluid">
        <div class="form-group">
            <small class="">Ao adicionar como gerente um usuário que não for membro deste projeto, esse usuário automáticamente será adicionado aos membros do projeto</small>
        </div>
        <input type="hidden" name="project_id" value="<?php echo ((isset($model_info->project_id) && $model_info->project_id != '') ? $model_info->project_id : $project_id); ?>" />
        <input type="hidden" name="is_leader" value="<?php echo ((isset($model_info->is_leader) && $model_info->is_leader != '') ? $model_info->is_leader : $is_leader); ?>" />
        <input type="hidden" name="id" value="<?php echo ((isset($model_info->id) && $model_info->id != '') ? $model_info->id : ''); ?>" />

        <div class="form-group" style="min-height: 50px">
            <div class="row">
                <label for="user_id" class=" col-md-3"><?php echo app_lang('manager'); ?></label>
                <div class="col-md-9">
                    <div class="select-resource-field">
                        <div class="select-member-form clearfix pb10">
                            <?php echo form_dropdown("user_id", $users_dropdown, (isset($model_info->user_id) ? $model_info->user_id : ''), "class='select2 col-md-10 p0' id='user_id'"); ?>
                            <?php echo js_anchor("<i data-feather='x' class='icon-16'></i> ", array("class" => "remove-member delete ml20")); ?>
                        </div>                                
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="hour_amount" class=" col-md-3"><?php echo app_lang('hour_amount'); ?></label>
                <div class="col-md-9">
                    <?php
                        echo form_input(array(
                            "id" => "hour_amount",
                            "name" => "hour_amount",
                            "value" => (isset($model_info->hour_amount) ? $model_info->hour_amount : ""),
                            "class" => "form-control",
                            "placeholder" => app_lang('amount'),
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
        window.projectMemberForm = $("#project-resource-form").appForm({
            closeModalOnSuccess: false,
            onSuccess: function (result) {
                if(<?php echo ($model_info->id ? 0 : 1)?>)
                {
                    $("#manager-table").appTable({newData: result.data[0], dataId: result.id});
                }
                if(result.name !== "<?php echo (isset($model_info->resource_name) ? $model_info->resource_name : ''); ?>")
                {
                    location.reload();
                }
                window.projectMemberForm.closeModal();
            }
        });

        $(".remove-member").hide();
        $(".select2").select2();

    });
</script>    