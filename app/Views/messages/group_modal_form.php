<?php echo form_open(get_uri("messages/save_group"), array("id" => "message-group-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
        <div class="form-group">
            <div class="row">
                <label for="group_name" class=" col-md-3"><?php echo app_lang('group_name'); ?></label>
                <div class=" col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "group_name",
                        "name" => "group_name",
                        "value" => $model_info->group_name ?? "",
                        "class" => "form-control",
                        "placeholder" => app_lang('group_name'),
                        "autofocus" => true,
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
    <div id="link-of-add-message-group-member-modal" class="hide">
        <?php echo modal_anchor(get_uri("messages/message_group_member_modal_form"), "", array()); ?>
    </div>

    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
    <?php if (!$model_info->id) { ?>
        <button type="button" id="save-and-continue-button" class="btn btn-info text-white"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save_and_continue'); ?></button>
    <?php } ?>
    <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {
        window.projectForm = $("#message-group-form").appForm({
            closeModalOnSuccess: false,
            onSuccess: function (result) {
                if (typeof RELOAD_PROJECT_VIEW_AFTER_UPDATE !== "undefined" && RELOAD_PROJECT_VIEW_AFTER_UPDATE) {
                    location.reload();

                    window.projectForm.closeModal();
                } else if (typeof RELOAD_VIEW_AFTER_UPDATE !== "undefined" && RELOAD_VIEW_AFTER_UPDATE) {
                    RELOAD_VIEW_AFTER_UPDATE = false;
                    window.location = "<?php echo site_url('messages/view_group'); ?>/" + result.id;

                    window.projectForm.closeModal();
                } else if (window.showAddNewModal) {
                    var $addProjectMemberLink = $("#link-of-add-message-group-member-modal").find("a");

                    $addProjectMemberLink.attr("data-action-url", "<?php echo get_uri("messages/message_group_member_modal_form"); ?>");
                    $addProjectMemberLink.attr("data-group_name", "<?php echo app_lang("add_new_message_group_member"); ?>");
                    $addProjectMemberLink.attr("data-post-message_group_id", result.id);
                    $addProjectMemberLink.attr("data-post-view_type", "from_message_group_modal");

                    $addProjectMemberLink.trigger("click");

                    $("#message-group-table").appTable({newData: result.data, dataId: result.id});
                } else {
                    $("#message-group-table").appTable({newData: result.data, dataId: result.id});

                    window.projectForm.closeModal();
                }
            }
        });

        setTimeout(function () {
            $("#group_name").focus();
        }, 200);
        $("#message-group-form .select2").select2();

        setDatePicker("#start_date, #deadline");

        //save and open add new project member modal
        window.showAddNewModal = false;

        $("#save-and-continue-button").click(function () {
            window.showAddNewModal = true;
            $(this).trigger("submit");
        });
    });
</script>    