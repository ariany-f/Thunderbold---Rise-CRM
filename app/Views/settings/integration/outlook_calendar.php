<div class="card no-border clearfix mb0">

    <?php echo form_open(get_uri("settings/save_outlook_calendar_settings"), array("id" => "outlook-calendar-form", "class" => "general-form dashed-row", "role" => "form")); ?>

    <div class="card-body">

        <div class="form-group">
            <div class="row">
                <label class=" col-md-12">
                    <?php echo app_lang("get_your_app_credentials_from_here") . " " . anchor("https://portal.azure.com/", "Microsoft Azure Portal", array("target" => "_blank")); ?>
                </label>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="outlook_calendar_client_id" class=" col-md-2"><?php echo app_lang('google_client_id'); ?></label>
                <div class=" col-md-10">
                    <?php
                    echo form_input(array(
                        "id" => "outlook_calendar_client_id",
                        "name" => "outlook_calendar_client_id",
                        "value" => get_setting("outlook_calendar_client_id"),
                        "class" => "form-control",
                        "placeholder" => app_lang('google_client_id'),
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required"),
                    ));
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="outlook_calendar_client_secret" class=" col-md-2"><?php echo app_lang('google_client_secret'); ?></label>
                <div class=" col-md-10">
                    <?php
                    echo form_input(array(
                        "id" => "outlook_calendar_client_secret",
                        "name" => "outlook_calendar_client_secret",
                        "value" => get_setting('outlook_calendar_client_secret'),
                        "class" => "form-control",
                        "placeholder" => app_lang('google_client_secret'),
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required"),
                    ));
                    ?>
                </div>
            </div>
        </div>
        <a class="btn btn-default" href="<?php echo $redirect_url ?>">Conceder permissões</a>
    </div>

    <div class="card-footer">
        <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
    </div>
    <?php echo form_close(); ?>
</div>


<script type="text/javascript">
    $(document).ready(function () {
        $("#outlook-calendar-form").appForm({
            isModal: false,
            onSuccess: function (result) {
                appAlert.success(result.message, {duration: 10000});
            }
        });

    });
</script>