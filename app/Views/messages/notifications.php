<div class="card mb0">
    <div class="page-title clearfix notificatio-plate-title-area">
        <span class="float-start"><strong><?php echo app_lang('messages'); ?></strong></span>
    </div>

    <div class="list-group" id="messages-popup-list">
        <?php
        if (count($notifications)) {
            foreach ($notifications as $notification) {

                //check read/unread class
                $notification_class = "";
                if ($notification->status === "unread") {
                    $notification_class = "unread-notification";
                }
                ?>
                <a class="list-group-item d-flex <?php echo $notification_class; ?>" href="<?php echo get_uri("messages/".($notification->group_name ? 'list_groups' : 'inbox')."/" . $notification->main_message_id); ?>">
                    <div class="flex-shrink-0">
                        <span class="avatar avatar-xs">
                            <img src="<?php echo get_avatar($notification->user_image); ?>" alt="..." />
                        </span>
                    </div>
                    <div class="w-100 ps-2 text-wrap-ellipsis">
                        <div class="mb5">
                            <strong><?php echo $notification->user_name; ?></strong>
                            <span class="text-off float-end"><small><?php echo format_to_relative_time($notification->created_at); ?></small></span>
                        </div>
                        <div class="text-wrap-ellipsis">
                            <?php
                            $subject = $notification->subject;
                            if ($notification->reply_subject) {
                                $subject = $notification->reply_subject;
                            }

                            $group_name = "";
                            if($notification->group_name) {
                                $ticket_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-tag icon"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>';
                                $project_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-grid icon"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>';
                                $group_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-coffee icon-18 me-2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>';
                                
                                if($notification->project_id)
                                {
                                    if($notification->is_ticket)
                                    {
                                        $group_name = $ticket_icon . $notification->group_name . "<br/>";
                                    }
                                    else
                                    {
                                        $group_name = $project_icon . $notification->group_name . "<br/>";
                                    }
                                }
                                else
                                {
                                    $group_name = $group_icon . $notification->group_name . "<br/>";
                                }
                            } 

                            echo ($group_name) . $subject;
                            ?>
                        </div>
                    </div>
                </a>
                <?php
            }
        } else {
            ?>
            <span class="list-group-item"><?php echo app_lang("no_new_messages"); ?></span>               
        <?php } ?>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        //don't apply scrollbar for mobile devices
        if ($(window).width() > 640) {
            if ($('#messages-popup-list').height() >= 400) {
                initScrollbar('#messages-popup-list', {
                    setHeight: 400
                });
            } else {
                $('#messages-popup-list').css({"overflow-y": "auto"});
            }
        }
    });
</script>