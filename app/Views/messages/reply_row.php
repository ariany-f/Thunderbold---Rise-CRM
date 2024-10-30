<div class="b-b p15 m0 bg-white js-message-reply" data-message_id="<?php echo $reply_info->id; ?>" href="#reply-<?php echo $reply_info->id; ?>" >
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex">
                <div class="flex-shrink-0">
                    <span class="avatar avatar-sm">
                        <img src="<?php echo get_avatar($reply_info->user_image, $reply_info->user_name); ?>" alt="..." />
                    </span>
                </div>
                <div class="w-100 ps-2">
                    <div>
                        <strong><?php
                            if ($reply_info->from_user_id === $login_user->id) {
                                echo app_lang("me");
                            } else {
                                if ($reply_info->user_type == "client") {
                                    echo get_client_contact_profile_link($reply_info->from_user_id, $reply_info->user_name, array("class" => "dark strong"));
                                } else {
                                    echo get_team_member_profile_link($reply_info->from_user_id, $reply_info->user_name, array("class" => "dark strong"));
                                }
                            }
                            ?>
                        </strong>
                        <span class="text-off float-end"><?php echo format_to_relative_time($reply_info->created_at); ?></span>
                        <!-- Permitir responder uma mensagem diretamente -->
                        <?php if(isset($ended) && (!$ended)) { ?>
                            <span class="float-end dropdown">
                                <div class="text-off dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="true" >
                                    <i data-feather="chevron-down" class="icon"></i>
                                </div>
                                <ul class="dropdown-menu" role="menu">
                                    <?php if($reply_info->from_user_id === $login_user->id) { ?>
                                        <li role="presentation"><?php echo modal_anchor(get_uri("messages/edit_message_modal_form/$reply_info->id"), "<i data-feather='edit-2' class='icon-16'></i> " . app_lang('edit'), array("class" => "dropdown-item", "title" => app_lang('edit'), "data-post-id" => $reply_info->id)); ?> </li>
                                    <?php } else { ?>
                                        <li role="presentation"><?php //echo ajax_anchor(get_uri("messages/reply_reply_message/$reply_info->id"), "<i data-feather='corner-down-left' class='icon-16'></i> " . app_lang('reply'), array("class" => "dropdown-item", "title" => app_lang('reply'))); ?> </li>
                                    <?php } ?>
                                </ul>
                            </span>
                        <?php } ?>
                        <!-- Permitir responder uma mensagem diretamente -->
                    </div>
                    <p><?php echo convert_mentions(nl2br(link_it(process_images_from_content($reply_info->message)))); ?></p>

                    <div class="comment-image-box clearfix">
                        <?php
                        $files = unserialize($reply_info->files);
                        $total_files = count($files);

                        if ($total_files) {
                            echo view("includes/timeline_preview", array("files" => $files));

                            $download_caption = app_lang('download');
                            if ($total_files > 1) {
                                $download_caption = sprintf(app_lang('download_files'), $total_files);
                            }
                            echo "<i data-feather='paperclip' class='icon-16'></i>";
                            echo anchor(get_uri("messages/download_message_files/" . $reply_info->id), $download_caption, array("class" => "float-end", "title" => $download_caption));
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
