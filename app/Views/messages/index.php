<div id="page-content" class="page-wrapper clearfix">

    <div class="row">

        <div class="box">
            <div class="box-content message-button-list">
                <ul class="list-group ">

                    <?php echo modal_anchor(get_uri("messages/modal_form"), app_lang('compose'), array("class" => "list-group-item", "title" => app_lang('send_message'))); ?> 
                   
                    <?php if(get_setting('module_message_group')) { ?>

                    <?php $count_group = count_unread_group_message(); ?>

                    <?php echo anchor(get_uri("messages/list_groups"), app_lang('groups') . ' <span class="badge '. ($count_group > 0 ? "bg-danger" : "badge-light") .'">' . $count_group . '</span>', array("class" => "list-group-item", "style" => "flex-direction: row;display: flex;align-items: center;justify-content: space-between;")); ?>

                    <?php } ?>

                    <?php $count_inbox = count_unread_inbox_message(); ?>
                    <?php echo anchor(get_uri("messages/inbox"), app_lang('inbox'). ' <span class="badge '.($count_inbox > 0 ? "bg-danger" : "badge-light") .'">' . $count_inbox . '</span>', array("class" => "list-group-item", "style" => "flex-direction: row;display: flex;align-items: center;justify-content: space-between;")); ?>

                    <?php echo anchor(get_uri("messages/sent_items"), app_lang('sent_items'), array("class" => "list-group-item")); ?>

                </ul>
            </div>


            <div class="box-content message-view ps-3" >
                <div class="row">
                    <div class="col-sm-12 col-md-5">
                        <div id="message-list-box" class="card">
                            <div class="card-header p10 clearfix no-border">
                                <div class="float-start p5">
                                    <?php
                                    if ($mode === "inbox") {
                                        echo "<i data-feather='inbox' class='icon-16'></i> " . app_lang('inbox');
                                    } else if ($mode === "sent_items") {
                                        echo "<i data-feather='send' class='icon-16'></i> " . app_lang('sent_items');
                                    } else if ($mode === "list_groups") {
                                        echo "<i data-feather='users' class='icon-16'></i> " . app_lang('groups');
                                    }
                                    ?>
                                </div>
                                <div class="tab-title clearfix no-border">
                                    <?php if ($mode === "inbox" || $mode === "sent_items") { ?>
                                        <input type="text" id="search-messages" class="datatable-search" placeholder="<?php echo app_lang('search') ?>">
                                    <?php } else if ($mode === "list_groups") { ?>
                                        <div class="title-button-group ">
                                            <input type="text" id="search-messages" class="datatable-search" placeholder="<?php echo app_lang('search') ?>">
                                            <?php echo modal_anchor(get_uri("messages/groups_modal_form/"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang("new_group"), array("class" => "btn btn-default", "title" => app_lang('new_group')));?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table id="message-table" class="display no-thead no-padding clickable" cellspacing="0" width="100%">            
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-7">
                        <div id="message-details-section" class="card"> 
                            <div id="empty-message" class="text-center mb15 box">
                                <div class="box-content" style="vertical-align: middle; height: 100%"> 
                                    <div><?php echo app_lang("select_a_message"); ?></div>
                                    <i data-feather="mail" width="10rem" height="10rem" style="color:rgba(128, 128, 128, 0.1)"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>
</div>
<style type="text/css">
    .datatable-tools:first-child {
        display:  none;
    }
</style>

<script type="text/javascript">
    $(document).ready(function () {
        var autoSelectIndex = "<?php echo $auto_select_index; ?>";
        $("#message-table").appTable({
            source: '<?php echo_uri("messages/list_data/" . $mode) ?>',
            order: [[1, "desc"]],
            columns: [
                {title: '<?php echo app_lang("message") ?>'},
                {targets: [1], visible: false},
                {targets: [2], visible: false}
            ],
            onInitComplete: function () {
                if (autoSelectIndex) {
                    //automatically select the message
                    var $tr = $("[data-index=" + autoSelectIndex + "]").closest("tr");
                    if ($tr.length)
                        $tr.trigger("click");
                }

                var $message_list = $("#message-list-box"),
                        $empty_message = $("#empty-message");
                if ($empty_message.length && $message_list.length) {
                    $empty_message.height($message_list.height());
                }
            }
        });

        var messagesTable = $('#message-table').DataTable();
        $('#search-messages').keyup(function () {
            messagesTable.search($(this).val()).draw();
        });


        /*load a message details*/
        $("body").on("click", "tr", function () {
            //remove unread class
            $(this).find(".unread").removeClass("unread");

            //don't load this message if already has selected.
            if (!$(this).hasClass("active")) {
                var message_id = $(this).find(".message-row").attr("data-id"),
                        reply = $(this).find(".message-row").attr("data-reply");
                if (message_id) {
                    $("tr.active").removeClass("active");
                    $(this).addClass("active");
                    $.ajax({
                        url: "<?php echo get_uri("messages/view"); ?>/" + message_id + "/<?php echo $mode ?>/" + reply,
                        dataType: "json",
                        success: function (result) {
                            if (result.success) {
                                $("#message-details-section").html(result.data);
                            } else {
                                appAlert.error(result.message);
                            }
                        }
                    });
                }

                //add index with tr for dlete the message
                $(this).addClass("message-container-" + $(this).find(".message-row").attr("data-index"));

            }
        });

    });
</script>