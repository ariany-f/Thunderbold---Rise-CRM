<div id="page-content" class="page-wrapper clearfix">
    <div class="card grid-button">
        <div class="page-title clearfix projects-page">
            <h1><?php echo app_lang('tickets'); ?></h1>
            <div class="title-button-group">
                <?php
                if ($can_create_projects) {
                    if ($can_edit_projects) {
                        echo modal_anchor(get_uri("labels/modal_form"), "<i data-feather='tag' class='icon-16'></i> " . app_lang('manage_labels'), array("class" => "btn btn-default", "title" => app_lang('manage_labels'), "data-post-type" => "project"));
                    }

                    echo modal_anchor(get_uri("projects/ticket_modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_category_ticket'), array("class" => "btn btn-success", "title" => app_lang('add_category_ticket')));
                }
                ?>
            </div>
        </div>
        <div class="table-responsive">
            <table id="project-table" class="display" cellspacing="0" width="100%">            
            </table>
        </div>
    </div>
</div>
<?php
    // Adicione o seguinte PHP para gerar as colunas de status dinamicamente
    $status_columns = "";
    foreach ($status_rows as $status_row) {
        $status_id = $status_row->id;
        $status_title = $status_row->title;
        $status_columns .= "{title: '". app_lang(strtolower(str_replace(" ", "_", $status_title)))."', \"class\": \"w10p\"},";
    }
?>
<script type="text/javascript">
    $(document).ready(function () {
        var optionVisibility = false;
        if ("<?php echo ($can_edit_projects || $can_delete_projects); ?>") {
            optionVisibility = true;
        }

        var selectOpenStatus = true, selectCompletedStatus = false, selectHoldStatus = false;
<?php if (isset($status) && $status == "completed") { ?>
            selectOpenStatus = false;
            selectCompletedStatus = true;
            selectHoldStatus = false;
<?php } else if (isset($status) && $status == "hold") { ?>
            selectOpenStatus = false;
            selectCompletedStatus = false;
            selectHoldStatus = true;
<?php } ?>

        $("#project-table").appTable({
            source: '<?php echo_uri("projects/list_tickets") ?>',
            multiSelect: [
                {
                    name: "status",
                    text: "<?php echo app_lang('status'); ?>",
                    options: [
                        {text: '<?php echo app_lang("open") ?>', value: "open", isChecked: selectOpenStatus},
                        {text: '<?php echo app_lang("completed") ?>', value: "completed", isChecked: selectCompletedStatus},
                        {text: '<?php echo app_lang("hold") ?>', value: "hold", isChecked: selectHoldStatus},
                        {text: '<?php echo app_lang("canceled") ?>', value: "canceled"}
                    ]
                }
            ],
            filterDropdown: [{name: "project_label", class: "w200", options: <?php echo $project_labels_dropdown; ?>}, <?php echo $custom_field_filters; ?>],
            columns: [
                {title: '<?php echo app_lang("id") ?>', "class": "all w50"},
                {title: '<?php echo app_lang("title") ?>', "class": "all w300"},
                <?php echo $status_columns; ?> // Colunas de status dinamicamente geradas
                {title: '<?php echo app_lang("members") ?>', "class": "w10p"},
                {visible: false, title: '<?php echo app_lang("progress") ?>', "class": "w10p"},
                {title: '<?php echo app_lang("status") ?>', "class": "w10p"}
                <?php echo $custom_field_headers; ?>,
                {visible: optionVisibility, title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100"}
            ],
            order: [[1, "desc"]],
            printColumns: combineCustomFieldsColumns([0, 1, 3, 5], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([0, 1, 3, 5], '<?php echo $custom_field_headers; ?>')
        });
    });
</script>