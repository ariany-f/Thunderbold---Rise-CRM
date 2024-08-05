<?php echo form_open(get_uri("messages/save_message_group_member"), array("id" => "message-group-member-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
        <input type="hidden" name="message_group_id" value="<?php echo $message_group_id; ?>" />

        <div class="form-group" style="min-height: 50px">
            <div class="row">
                <label for="user_id" class=" col-md-3"><?php echo app_lang('member'); ?></label>
                <div class="col-md-9">
                    <div class="select-member-field">
                        <div class="select-member-form clearfix pb10">
                            <?php echo form_dropdown("user_id[]", $users_dropdown, array($model_info->created_by), "class='user_select2 col-md-10 p0' id='user_id'"); ?>
                            <?php echo js_anchor("<i data-feather='x' class='icon-16'></i> ", array("class" => "remove-member delete ml20")); ?>
                        </div>                                
                    </div>
                    <?php echo js_anchor("<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_more'), array("class" => "add-member", "id" => "add-more-user")); ?>
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
        window.projectMemberForm = $("#message-group-member-form").appForm({
            closeModalOnSuccess: false,
            onSuccess: function (result) {
                if (result.id !== "exists") {
                    for (i = 0; i < result.data.length; i++) {
                        $("#message-group-member-table").appTable({newData: result.data[i], dataId: result.id[i]});
                    }

                }

                window.projectMemberForm.closeModal();
            }
        });

        var $wrapper = $('.select-member-field'),
                $field = $('.select-member-form:first-child', $wrapper).clone(); //keep a clone for future use.

        $(".add-member", $(this)).click(function (e) {
            var $newField = $field.clone();

            //remove used options
            $('.user_select2').each(function () {
                $newField.find("option[value='" + $(this).val() + "']").remove();
            });

            var $newObj = $newField.appendTo($wrapper);
            $newObj.find(".user_select2").select2();

            $newObj.find('.remove-member').click(function () {
                $(this).parent('.select-member-form').remove();
                showHideAddMore($field);
            });

            showHideAddMore($field);
        });

        showHideAddMore($field);

        $(".remove-member").hide();
        $(".user_select2").select2();

        function showHideAddMore($field) {
            //hide add more button if there are no options 
            if ($('.select-member-form').length < $field.find("option").length) {
                $("#add-more-user").show();
            } else {
                $("#add-more-user").hide();
            }
        }

        $("#save-and-continue-button").click(function () {
            $(this).trigger("submit");
        });
    });
</script>    