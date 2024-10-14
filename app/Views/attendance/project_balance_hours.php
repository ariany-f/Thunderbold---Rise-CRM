<div class="card">
    <div class="card-body">
        <div class="d-flex">
            <div class="widget-icon bg-info">
                <i data-feather="clock" class="icon"></i>
            </div>
            <div class="w-100 text-end">
                <?php if($balance == 0) { ?>
                    <h3><?php echo app_lang('not_set'); ?></h3>
                <?php } else { ?>
                    <h1><?php echo $balance; ?></h1>
                <?php } ?>
                <span class="bg-transparent-white"><?php echo app_lang('project_balance_hours'); ?></span>
            </div>
        </div>
        <div class="text-off">
            <span><?php echo app_lang('project_balance_hours_help_message'); ?></span>
        </div>
    </div>
</div>