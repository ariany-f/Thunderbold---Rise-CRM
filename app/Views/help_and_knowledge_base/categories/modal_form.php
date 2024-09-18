<?php echo form_open(get_uri("help/save_category"), array("id" => "category-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
        <input type="hidden" name="type" value="<?php echo $type; ?>" />

        <div class="form-group">
            <div class="row">
                <label for="title" class=" col-md-3"><?php echo app_lang('title'); ?></label>
                <div class=" col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "title",
                        "name" => "title",
                        "value" => $model_info->title,
                        "class" => "form-control",
                        "placeholder" => app_lang('title'),
                        "autofocus" => true,
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required"),
                    ));
                    ?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="description" class=" col-md-3"><?php echo app_lang('description'); ?></label>
                <div class=" col-md-9">
                    <?php
                    echo form_textarea(array(
                        "id" => "description",
                        "name" => "description",
                        "value" => process_images_from_content($model_info->description, false),
                        "class" => "form-control",
                        "placeholder" => app_lang('description'),
                        "data-rich-text-editor" => true
                    ));
                    ?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="sort" class=" col-md-3"><?php echo app_lang('sort'); ?></label>
                <div class=" col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "sort",
                        "name" => "sort",
                        "value" => $model_info->sort,
                        "class" => "form-control",
                        "placeholder" => app_lang('sort'),
                        "type" => "number",
                        "min" => "0"
                    ));
                    ?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="status" class=" col-md-3"><?php echo app_lang('status'); ?></label>
                <div class=" col-md-9">
                    <?php
                    echo form_radio(array(
                        "id" => "status_active",
                        "name" => "status",
                        "class" => "form-check-input",
                        "data-msg-required" => app_lang("field_required"),
                            ), "active", ($model_info->status === "active") ? true : (($model_info->status !== "inactive") ? true : false));
                    ?>
                    <label for="status_active" class="mr15"><?php echo app_lang('active'); ?></label>
                    <?php
                    echo form_radio(array(
                        "id" => "status_inactive",
                        "name" => "status",
                        "class" => "form-check-input",
                        "data-msg-required" => app_lang("field_required"),
                            ), "inactive", ($model_info->status === "inactive") ? true : false);
                    ?>
                    <label for="status_inactive" class=""><?php echo app_lang('inactive'); ?></label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="share_with" class=" col-md-3"><?php echo app_lang('share_with'); ?></label>
                <div class="col-md-9">
                    <div>
                        <?php
                        echo form_checkbox(array(
                            "id" => "share_with_members",
                            "name" => "share_with_all_members",
                            "value" => "all_members",
                            "class" => "form-check-input",
                                ), $model_info->share_with, (in_array("all_members", $share_with)) ? true : false);
                        ?>
                        <label for="share_with_members"><?php echo app_lang("all_team_members"); ?> </label>
                    </div>

                    <?php
                    $has_client_group = false;
                    $client_groups_value = "";

                    foreach ($share_with as $share) {
                        if (strpos($share, 'cg') !== false) {
                            $has_client_group = true;

                            if ($client_groups_value) {
                                $client_groups_value .= ",";
                            }

                            $client_groups_value .= $share;
                        }
                    }
                    ?>

                    <div id="share_with_clients_area" class="<?php echo $has_client_group ? "hide" : ""; ?>">
                        <div>
                            <?php
                            echo form_checkbox(array(
                                "id" => "share_with_clients",
                                "name" => "share_with_all_clients",
                                "value" => "all_clients",
                                "class" => "form-check-input"
                                    ), $model_info->share_with, (in_array("all_clients", $share_with)) ? true : false);
                            ?>

                            <label for="share_with_clients"><?php echo app_lang("all_team_clients"); ?></label>

                        </div>

                    </div>


                    <div id="share_with_specific_area" class="form-group <?php echo (in_array("all_clients", $share_with)) ? "hide" : ""; ?>">
                        <div>
                            <?php
                            echo form_checkbox("share_with_specific_checkbox", "1", $has_client_group ? true : false, "id='share_with_specific_checkbox' class='form-check-input'");
                            ?>
                            <label for="share_with_specific_checkbox"><?php echo app_lang("specific_client_groups"); ?></label>

                            <div class="specific_dropdown">
                                <input type="text" value="<?php echo $client_groups_value; ?>" name="share_with_specific_client_groups" id="share_with_specific_dropdown" class="w100p validate-hidden"  data-rule-required="true" data-msg-required="<?php echo app_lang('field_required'); ?>" placeholder="<?php echo app_lang('choose_client_groups'); ?>"  />    

                            </div>
                        </div>
                    </div>
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
        $("#category-form").appForm({
            onSuccess: function (result) {
                $("#category-table").appTable({newData: result.data, dataId: result.id});
            }
        });
        $("#share_with_specific_dropdown").select2({
            multiple: true,
            data: <?php echo ($groups_dropdown); ?>
        });

        toggle_specific_dropdown();

        function toggle_specific_dropdown() {
            var $element = $("#share_with_specific_checkbox:checked");
            if ($element.is(":checked") && !$("#share_with_specific_area").hasClass("hide")) {
                $("#share_with_specific_checkbox").closest("div.form-group").find(".specific_dropdown").show().find("input").addClass("validate-hidden");
            } else {
                $("#share_with_specific_checkbox").closest("div.form-group").find(".specific_dropdown").hide().find("input").removeClass("validate-hidden");
            }
        }

        //show/hide client groups area
        $("#share_with_clients").click(function () {
            if ($(this).is(":checked")) {
                $("#share_with_specific_area").addClass("hide");
            } else {
                $("#share_with_specific_area").removeClass("hide");
            }

            toggle_specific_dropdown();
        });


        //show/hide clients area
        $("#share_with_specific_checkbox").click(function () {
            if ($(this).is(":checked")) {
                $("#share_with_clients_area").addClass("hide");
            } else {
                $("#share_with_clients_area").removeClass("hide");
            }

            toggle_specific_dropdown();
        });
    });
</script>    