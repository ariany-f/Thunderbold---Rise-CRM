<?php echo form_open(get_uri("messages/save_group_client_message"), array("id" => "message-group-client-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
        <div class="form-group">
            <div class="row">
                <label for="to_group_id" class=" col-md-2"><?php echo app_lang('group'); ?></label>
                <div class="col-md-10">
                    <?php
                        echo form_dropdown("to_group_id", $groups_dropdown, "", "class='select2 validate-hidden' id='to_user_id' data-rule-required='true', data-msg-required='" . app_lang('field_required') . "'");
                    ?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="subject" class=" col-md-2"><?php echo app_lang('subject'); ?></label>
                <div class=" col-md-10">
                    <?php
                    echo form_input(array(
                        "id" => "subject",
                        "name" => "subject",
                        "value" => $model_info->subject ?? "",
                        "class" => "form-control",
                        "placeholder" => app_lang('subject'),
                        "autofocus" => true,
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required"),
                    ));
                    ?>
                </div> 
            </div>
        </div>
        <div class="form-group">
                <div class="col-md-12">
                    <?php
                    echo form_textarea(array(
                        "id" => "message",
                        "name" => "message",
                        "class" => "form-control",
                        "placeholder" => app_lang('write_a_message'),
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required"),
                        "style" => "min-height:200px;",
                        "data-rich-text-editor" => true
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-12">
                    <?php echo view("includes/dropzone_preview"); ?> 
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
        window.projectForm = $("#message-group-client-form").appForm({
            closeModalOnSuccess: false,
            onSuccess: function (result) {
                
                $("#message-table").appTable({newData: result.data[0], dataId: result.id});

                window.projectForm.closeModal();
            }
        });

        $("#message-group-client-form .select2").select2();
        
        var uploadUrl = "<?php echo get_uri("messages/upload_file"); ?>";
        var validationUrl = "<?php echo get_uri("messages/validate_message_file"); ?>";

        var dropzone = attachDropzoneWithForm("#new-message-dropzone", uploadUrl, validationUrl);


        setTimeout(function () {
            $("#group_name").focus();
        }, 200);
    });
</script>    