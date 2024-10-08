<div class="card">
    <div class="card-body">
        <div class="d-flex">
            <div class="widget-icon bg-warning">
                <i data-feather="clock" class="icon"></i>
            </div>
            <div class="w-100 text-end">
                <?php if($limit == 0) { ?>
                    <h3><?php echo app_lang('not_set'); ?></h3>
                <?php } else { ?>
                    <h1><?php echo $limit; ?></h1>
                <?php } ?>
                <span class="bg-transparent-white"><?php echo app_lang('project_limit_hours'); ?></span>
            </div>
        </div>
        <div class="text-off">
            <span><?php echo app_lang('project_limit_hours_help_message'); ?></span>
        </div>
    </div>
</div>