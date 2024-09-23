<div class="message-container-<?php echo $message_info->id; ?>" data-total_messages="<?php echo $found_rows ?>">
    <?php
    if ($mode === "inbox") {
        if ($is_reply) {
            $user_image = $login_user->image;
            $user_name = $login_user->first_name . " " . $login_user->last_name;
        } else {
            $user_image = $message_info->user_image;
            $user_name = $message_info->user_name;
        }
    } if ($mode === "sent_items") {
        if ($is_reply) {
            $user_image = $message_info->user_image;
            $user_name = $message_info->user_name;
        } else {
            $user_image = $login_user->image;
            $user_name = $login_user->first_name . " " . $login_user->last_name;
        }
    }
    if ($mode === "list_groups") {
        if ($is_reply) {
            $user_image = $login_user->image;
            $user_name = $login_user->first_name . " " . $login_user->last_name;
        } else {
            if(isset($message_info->another_user_image))
            {

                $user_image = $message_info->another_user_image;
                $user_name = $message_info->another_user_name;
            }
            else
            {
                $user_image = $message_info->user_image;
                $user_name = $message_info->user_name;
            }
        }
    }
    ?>

    <div class="b-b p15 m0 bg-white">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex flex-column">
                    <?php 
                     $ticket_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-tag icon"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>';
                     $project_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-grid icon"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>';
                     $group_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-coffee icon-18 me-2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>';
             
             
                     $link = null;
                     $group_name = "";
                     if($message_info->project_id)
                     {
                         if($project_info->is_ticket)
                         {
                             $link =  anchor(get_uri("projects/view/" . $message_info->project_id . "/ticket"), "<div style='color: initial;'>" . $ticket_icon . '&nbsp;' . $message_info->group_name . "</div>");
                         }
                         else
                         {
                             $link = anchor(get_uri("projects/view/" . $message_info->project_id), "<div style='color: initial;'>" . $project_icon . '&nbsp;' . $message_info->group_name . "</div>");
                         }
                     }
                     
                     if($link)
                     {
                         $group_name = $link;
                     }
                     else
                     {
                         if($message_info->group_name)
                         {
                             $group_name = $group_icon . $message_info->group_name;
                         }
                     }
                     
                    ?>
                    <div class="d-flex justify-content-between b-b pt-15 mb-3">
                        <b><?php echo $group_name; ?>
                        <?php if($message_info->ended) { ?>
                            <span class="badge bg-danger">CONVERSA ENCERRADA</span>
                        <?php } ?></b>
                        <div class="title-button-group">
                            <div class="d-flex align-items-center">
                            <?php if(isset($message_users_result )) { ?>
                                <div class="avatar-group pt-3">
                                    <?php
                                        foreach ($message_users_result AS $user) {
                                        ?>
                                        <div class="user-avatar avatar-30 avatar-circle" data-bs-toggle='tooltip' title='<?php echo $user->user_name; ?>'>
                                            <img alt="" src="<?php echo get_avatar($user->user_avatar, $user->user_name); ?>">
                                        </div>
                                    <?php
                                        }
                                    ?>
                                </div>
                            <?php } ?>
                            
                            <?php if($message_info->group_name)
                                {
                                    echo modal_anchor(get_uri("messages/message_group_member_modal_form/" . $message_info->group_id), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang("manage_members"), array("class" => "btn bg-success d-flex align-items-center", "title" => app_lang('manage_members')));
                                }   
                            ?>
                        </div>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="flex-shrink-0 pe-2"> 
                            <span class="avatar avatar-sm">
                                <img src="<?php echo get_avatar($user_image, $user_name); ?>" alt="..." />
                            </span>
                        </div>
                        <div class="w-100">
                            <div class="clearfix">
                                <?php
                                $message_user_id = $message_info->from_user_id;
                                if ($mode === "list_groups" && $is_reply != "1" || $mode === "sent_items" && $is_reply != "1" || $mode === "inbox" && $is_reply == "1") {
                                    if(!empty($message_info->to_user_id)) {
                                        
                                        $message_user_id = $message_info->to_user_id;
                                    }
                                    ?>
                                    <label class="badge bg-success"><?php echo app_lang("to"); ?></label>
                                <?php } ?>
                                <?php
                                if ($message_info->user_type == "client") {
                                    echo get_client_contact_profile_link($message_user_id, ($message_info->user_name ?? $message_info->another_user_name), array("class" => "dark strong"));
                                } else {
                                    echo get_team_member_profile_link($message_user_id, ($message_info->user_name ?? $message_info->another_user_name), array("class" => "dark strong"));
                                }
                                ?>
                                <span class="text-off float-end"><?php echo format_to_relative_time($message_info->created_at); ?></span>

                                <span class="float-end dropdown">
                                    <div class="text-off dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="true" >
                                        <i data-feather="chevron-down" class="icon"></i>
                                    </div>
                                    <ul class="dropdown-menu" role="menu">
                                        <li role="presentation"><?php echo ajax_anchor(get_uri("messages/delete_my_messages/$message_info->id"), "<i data-feather='x' class='icon-16'></i> " . app_lang('delete'), array("class" => "dropdown-item", "title" => app_lang('delete'), "data-fade-out-on-success" => ".message-container-$message_info->id")); ?> </li>
                                        <?php if($login_user->user_type === 'staff') { ?>
                                            <?php if($message_info->ended) { ?>
                                                <li role="presentation"><?php echo ajax_anchor(get_uri("messages/reactive_my_messages/$message_info->id"), "<i data-feather='corner-down-left' class='icon-16'></i> " . app_lang('reactive_conversation'), array("class" => "dropdown-item", "title" => app_lang('reactive_conversation'), "data-reload-on-success" => "1")); ?> </li>
                                            <?php } else { ?>
                                                <li role="presentation"><?php echo ajax_anchor(get_uri("messages/end_my_messages/$message_info->id"), "<i data-feather='x' class='icon-16'></i> " . app_lang('end_conversation'), array("class" => "dropdown-item", "title" => app_lang('end_conversation'), "data-reload-on-success" => "1")); ?> </li>
                                            <?php } ?>
                                            <?php if((!$message_info->task_id) and $message_info->group_name != "") { ?>
                                                <li role="presentation"><?php echo ajax_anchor(get_uri("messages/create_task/" . $message_info->id . ""), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('convert_task'), array("class" => "dropdown-item", "id" => "convert_task", "title" => app_lang('create_task'), "data-reload-on-success" => "1"));?></li>
                                            <?php } ?>
                                        <?php } ?>
                                    </ul>
                                </span>
                            </div>
                            <p class="pt10 pb10 b-b">
                                <?php if($message_info->task_id && $message_info->task_id != 0) : ?>
                                    <?php echo modal_anchor(get_uri("projects/task_view"), 'Tarefa: #' . $message_info->task_id . ' ' . $message_info->subject, array("title" => app_lang('task_info') . " #$message_info->task_id", "data-post-id" => $message_info->task_id, "data-modal-lg" => "1"))?>
                                <?php else : ?>
                                    <?php echo app_lang("subject"); ?>:  
                                    <?php echo $message_info->subject; ?>
                                <?php endif; ?>
                            </p>

                            <p>
                                <?php echo nl2br(link_it(process_images_from_content($message_info->message))); ?>
                            </p>

                            <div class="comment-image-box clearfix">
                                <?php
                                $files = unserialize($message_info->files);
                                $total_files = count($files);

                                if ($total_files) {
                                    echo view("includes/timeline_preview", array("files" => $files));
                                    $download_caption = app_lang('download');
                                    if ($total_files > 1) {
                                        $download_caption = sprintf(app_lang('download_files'), $total_files);
                                    }
                                    echo "<i data-feather='paperclip' class='icon-16'></i>";
                                    echo anchor(get_uri("messages/download_message_files/" . $message_info->id), $download_caption, array("class" => "float-end", "title" => $download_caption));
                                }
                                ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <?php
    //if there are more then 5 messages, we'll show load more option.

    if ($found_rows > 5) {
        ?>    
        <div id="load-messages" class="b-b">
            <?php
            echo js_anchor(app_lang("load_more"), array("class" => "btn btn-default w-100 mt15 spinning-btn", "title" => app_lang("load_more"), "id" => "load-more-messages-link"));
            ?>
        </div>
        <div id="load-more-messages-container"></div>
        <?php
    }




    foreach ($replies as $reply_info) {
        ?>
        <?php echo view("messages/reply_row", array("reply_info" => $reply_info)); ?>
    <?php } ?>

    <?php if(!$message_info->ended) { ?>
    <div id="reply-form-container">
        <div id="reply-form-dropzone" class="post-dropzone">
            <?php echo form_open(get_uri("messages/reply"), array("id" => "message-reply-form", "class" => "general-form", "role" => "form")); ?>
            <div class="p15 box b-b">
                    <div class="box-content avatar avatar-md pr15 d-table-cell">
                        <img src="<?php echo get_avatar($login_user->image, ($login_user->first_name . ' ' . $login_user->last_name)); ?>" alt="..." />
                    </div>
                
                    <div class="box-content mb-3 form-group">
                        <input type="hidden" name="message_id" value="<?php echo $message_info->id; ?>">
                        <?php
                            echo form_textarea(array(
                                "id" => "reply_message",
                                "name" => "reply_message",
                                "class" => "form-control",
                                "placeholder" => app_lang('write_a_reply'),
                                "data-rule-required" => true,
                                "data-msg-required" => app_lang("field_required"),
                                "data-rich-text-editor" => true,
                                "style" => "height: 6rem;"
                            ));
                        ?>
                            <?php echo view("includes/dropzone_preview"); ?>
                            <footer class="card-footer b-a clearfix">
                                <button class="btn btn-default upload-file-button float-start me-auto btn-sm round" type="button" style="color:#7988a2"><i data-feather="camera" class='icon-16'></i> <?php echo app_lang("upload_file"); ?></button>
                                <button class="btn btn-primary float-end btn-sm " type="submit"><i data-feather="send" class='icon-16'></i> <?php echo app_lang("reply"); ?></button>
                            </footer>
                    </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
    <?php } ?> 
    <script type="text/javascript">
        $(document).ready(function () {
            var uploadUrl = "<?php echo get_uri("messages/upload_file"); ?>";
            var validationUrl = "<?php echo get_uri("messages/validate_message_file"); ?>";

            <?php if(!$message_info->ended) { ?>
                var dropzone = attachDropzoneWithForm("#reply-form-dropzone", uploadUrl, validationUrl);

                $("#message-reply-form").appForm({
                    isModal: false,
                    onSuccess: function (result) {
                        $("#reply_message").val("");
                        $(result.data).insertBefore("#reply-form-container");
                        appAlert.success(result.message, {duration: 10000});
                        if (dropzone) {
                            dropzone.removeAllFiles();
                        }
                    }
                });
            <?php } ?> 

            $("#load-more-messages-link").click(function () {
                loadMoreMessages();
            });

        });

        function loadMoreMessages(callback) {
            $("#load-more-messages-link").addClass("spinning");
            var $topMessageDiv = $(".js-message-reply").first();

            $.ajax({
                url: "<?php echo get_uri('messages/view_messages'); ?>",
                type: "POST",
                data: {
                    message_id: "<?php echo $message_info->id; ?>",
                    top_message_id: $topMessageDiv.attr("data-message_id")
                },
                success: function (response) {
                    if (response) {
                        $("#load-more-messages-container").prepend(response);
                        $("#load-more-messages-link").removeClass("spinning");

                        if (callback) {
                            callback(); //has more data?
                        }
                    } else {
                        $("#load-more-messages-link").remove(); //no more messages left
                    }

                }
            });
        }
    </script>
</div>