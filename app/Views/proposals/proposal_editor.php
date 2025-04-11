<div class="card no-border clearfix mb0">
    <?php
    load_css(array(
        "assets/css/invoice.css",
    ));
    ?>

    <?php echo form_open(get_uri("proposals/save_view"), array("id" => "proposal-editor-form", "class" => "general-form", "role" => "form")); ?>
    <div class="bg-all-white pt15">

        <input type="hidden" name="id" id="proposal_id" value="<?php echo $proposal_info->id; ?>" />

        <div class="form-group mb15 pl15 pr15">
            <div class="invoice-preview proposal-preview">
                <div class="proposal-preview-container pt0 pb0">
                    <div class="clearfix pl5 pr5 pb10">
                        <?php if(!$proposal_info->lock_change): ?>
                            <?php echo modal_anchor(get_uri("proposal_templates/insert_template_modal_form/$proposal_info->id"), "<i data-feather='rotate-ccw' class='icon-16'></i> " . app_lang('change_template'), array("class" => "btn btn-default float-start", "title" => app_lang('change_template'))); ?>
                        <?php else: ?>
                            <?php echo ajax_anchor(get_uri("proposals/unlock/$proposal_info->id"), "<i data-feather='unlock' class='icon-16'></i> " . app_lang('unlock'), array("disabled" => $proposal_info->lock_change, "class" => "btn btn-default float-start", "title" => app_lang('unlock'), "data-reload-on-success" => "1")); ?>
                        <?php endif; ?>
                        <button type="button" class="btn btn-primary ml10 float-end" id="proposal-save-and-show-btn"><span data-feather='check-circle' class="icon-16"></span> <?php echo app_lang('save_and_show'); ?></button>
                        <button type="submit" class="btn btn-primary float-end"><span data-feather='check-circle' class="icon-16"></span> <?php echo app_lang('save'); ?></button>
                    </div>
                    <div class="d-flex pl5 pr5 pb10">
                        <div class="col-md-6 text-left">
                            <label style="color: #2471a3;" for="lock_change"><b><?php echo app_lang('lock_change'); ?></b></label>
                            <?php
                                echo form_checkbox("lock_change", "1", $proposal_info->lock_change ? true : false, "id='lock_change' class='form-check-input'");
                            ?>
                        </div>
                        <div class=" col-md-6 text-right">
                            <label style="color: #2471a3;" for="gp_apart"><b><?php echo app_lang('gp_apart'); ?></b></label>
                            <?php
                                echo form_checkbox("gp_apart", "1", $proposal_info->gp_apart ? true : false, "id='gp_apart' class='form-check-input'");
                            ?>
                            
                            <label style="color: #2471a3;" for="qa_apart"><b><?php echo app_lang('qa_apart'); ?></b></label>
                            <?php
                                echo form_checkbox("qa_apart", "1", $proposal_info->qa_apart ? true : false, "id='qa_apart' class='form-check-input'");
                            ?>
                        </div>
                    </div>
                    <div class=" col-md-12">
                        <?php
                        echo form_textarea(array(
                            "id" => "proposal-view",
                            "name" => "view",
                            "value" => process_images_from_content($proposal_info->content, false),
                            "placeholder" => app_lang('view'),
                            "class" => "form-control"
                        ));
                        ?>
                    </div>

                    <div class=" col-md-12">
                        <?php
                        echo form_input(array(
                            "id" => "proposal-temnplate-id",
                            "name" => "template_id",
                            "value" => process_images_from_content($proposal_info->template_id, false),
                            "placeholder" => app_lang('template_id'),
                            "class" => "form-control hide"
                        ));
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="p15 pt0"><strong><?php echo app_lang("avilable_variables"); ?></strong>: <?php
                $avilable_variables = get_available_proposal_variables();
                foreach ($avilable_variables as $variable) {
                    echo "{" . $variable . "}, ";
                }
                ?></div>

    </div>
    <?php echo form_close(); ?>

</div>

<script>
    $(document).ready(function () {
        $("#proposal-editor-form").appForm({
            isModal: false,
            beforeAjaxSubmit: function (data) {
                var view = encodeAjaxPostData(getWYSIWYGEditorHTML("#proposal-view"));
                $.each(data, function (index, obj) {
                    if (obj.name === "view") {
                        data[index]["value"] = view;
                    }
                });
            },
            onSuccess: function (response) {
                appAlert.success(response.message, {duration: 10000});
            }
        });

        initWYSIWYGEditor("#proposal-view", {
            height: 600,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
                ['table', ['table']],
                ['insert', ['hr', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview']]
            ],
            lang: "<?php echo app_lang('language_locale_long'); ?>"
        });

        //insert proposal template
        $("body").on("click", "#proposal-template-table tr", function () {
            var id = $(this).find(".proposal_template-row").attr("data-id");
            var lock_change = $("#lock_change").val();
            appLoader.show({container: "#insert-template-section", css: "left:0;"});

            $.ajax({
                url: "<?php echo get_uri('proposal_templates/get_template_data') ?>" + "/" + id,
                type: 'POST',
                dataType: 'json',
                success: function (result) {
                    if (result.success) {
                        $("#proposal-view").summernote("code", result.template);
                        $("#proposal-temnplate-id").val(id);

                        //close the modal
                        $("#close-template-modal-btn").trigger("click");
                    } else {
                        appAlert.error(result.message);
                    }
                }
            });

        });
    });
</script>