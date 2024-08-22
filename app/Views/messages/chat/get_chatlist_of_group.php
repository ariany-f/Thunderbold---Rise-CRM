<div class="rise-chat-header box">
    <div class="box-content chat-back" id="js-back-to-groups-tab">
        <i data-feather="chevron-left" class="icon-16"></i>
    </div>
    <div class="box-content chat-title">
        <div>
            <?php
             if(!empty($project_info)) {
                $ticket_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-tag icon"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>';
                $project_icon = ' <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-grid icon"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>';
                if($project_info->is_ticket)
                {
                    $link =  anchor(get_uri("projects/view/" . $group_info->project_id . "/ticket"), $ticket_icon . $group_info->group_name);
                }
                else
                {
                    $link = anchor(get_uri("projects/view/" . $group_info->project_id), $project_icon . $group_info->group_name);
                }
            } else {
                $group_icon = ' <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-coffee icon-18 me-2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>';
                $link = $group_icon . $group_info->group_name;
            } 
            
            ?>
            <?php echo $link; ?>
        </div>
    </div>
</div>
<div id="js-single-group-chat-list" class="rise-chat-body full-height">

    <div class='clearfix p10 b-b'>
        <?php
        if (get_setting("module_chat")) {
            echo modal_anchor(get_uri("messages/to_group_modal_form/" . $group_id), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang("new_conversation"), array("class" => "btn btn-default col-md-12 col-sm-12 col-xs-12", "title" => app_lang('send_message')));
            echo modal_anchor(get_uri("messages/message_group_member_modal_form/" . $group_id), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang("add_member"), array("class" => "btn btn-default col-md-12 col-sm-12 col-xs-12", "title" => app_lang('add_member')));
        }
        ?>
    </div>
    <div id="chatlist-of-user">
        <?php
        foreach ($messages as $message) {
            $online = "";
            if ($message->last_online && is_online_user($message->last_online)) {
                $online = "<i class='online'></i>";
            }

            $status = "";
            $last_message_from = $message->from_user_id;
            if ($message->last_from_user_id) {
                $last_message_from = $message->last_from_user_id;
            }

            if ($message->status === "unread" && $last_message_from != $login_user->id) {
                $status = "unread";
            }
            ?>
            <?php if(!$message->ended) { ?>
            <div class='js-message-row message-row <?php echo $status; ?>' data-id='<?php echo $message->id; ?>' data-index='<?php echo $message->id; ?>'>
                <div class="d-flex">
                    <div class='flex-shrink-0'>
                        <span class='avatar avatar-xs'>
                            <img src='<?php echo get_avatar($message->user_image); ?>' />
                            <?php echo $online; ?>
                        </span>
                    </div>
                    <div class='w-100 ps-2'>
                        <div class='mb5'>
                            <strong><?php echo $message->user_name; ?></strong>
                            <span class='text-off float-end time'><?php echo format_to_relative_time($message->message_time); ?></span>
                        </div>
                        <?php echo $message->subject; ?>
                    </div>
                </div>
            </div>
            <?php } ?>
            <?php
        }
        ?>
    </div>
</div>

<script>
    $("#js-back-to-groups-tab").click(function () {
        loadChatTabs("<?php echo $tab_type; ?>");
    });
</script>