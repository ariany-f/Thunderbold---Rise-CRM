
<?php
if(!$event) : ?>
    <?php if (count($notifications)) {
    
    // Definir categorias de notificações
    $categories = [
        'projetos' => [],
        'mensagens' => [],
        'propostas' => []
    ];
    
    // Classificar notificações nas categorias correspondentes
    foreach ($notifications as $notification) {
        if (strpos($notification->event, 'project') !== false) {
            $categories['projetos'][] = $notification;
        } elseif (strpos($notification->event, 'message') !== false) {
            $categories['mensagens'][] = $notification;
        } elseif (strpos($notification->event, 'proposal') !== false) {
            $categories['propostas'][] = $notification;
        } else {
            $categories['outros'][] = $notification;
        }
    }
    ?>
    
    <ul class="nav nav-tabs" id="notificationTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="projetos-tab" data-bs-toggle="tab" href="#projetos" role="tab" onclick="event.stopPropagation();">Projetos</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="mensagens-tab" data-bs-toggle="tab" href="#mensagens" role="tab" onclick="event.stopPropagation();">Mensagens</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="propostas-tab" data-bs-toggle="tab" href="#propostas" role="tab" onclick="event.stopPropagation();">Propostas</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="outros-tab" data-bs-toggle="tab" href="#outros" role="tab" onclick="event.stopPropagation();">Outros</a>
        </li>
    </ul>
    
    
    <div class="tab-content mt-3" id="notificationTabsContent">
        <?php foreach ($categories as $key => $notifs) : ?>
            <div class="tab-pane fade <?php echo $key === 'projetos' ? 'show active' : ''; ?>" id="<?php echo $key; ?>" role="tabpanel">
                <?php if (count($notifs)) : ?>
                    <?php foreach ($notifs as $notification) {
    
                        //get url attributes
                        $url_attributes_array = get_notification_url_attributes($notification);
                        $url_attributes = get_array_value($url_attributes_array, "url_attributes");
                        $url = get_array_value($url_attributes_array, "url");
    
                        //check read/unread class
                        $notification_class = "";
                        if (!$notification->is_read) {
                            $notification_class = "unread-notification";
                        }
    
                        if ((!$url || $url == "#") && $url_attributes == "href='$url'") {
                            $notification_class .= " not-clickable";
                        } else {
                            $notification_class .= " clickable";
                        }
    
                        $avatar = get_avatar("system_bot", "System Bot");
                        $title = get_setting("app_title");
                        if ($notification->user_id) {
                            if ($notification->user_id == "999999998") {
                                //check if it's bitbucket commit notification
                                $avatar = get_avatar("bitbucket");
                                $title = "Bitbucket";
                            } else if ($notification->user_id == "999999997") {
                                //check if it's github commit notification
                                $avatar = get_avatar("github");
                                $title = "GitHub";
                            } else if ($notification->user_id == "999999996") {
                                //check if it's public notification of contract/estimate/proposal
                                $signer_info = $notification->contract_meta_data;
                                if ($notification->estimate_id) {
                                    $signer_info = $notification->estimate_meta_data;
                                } else if ($notification->proposal_id) {
                                    $signer_info = $notification->proposal_meta_data;
                                }
    
                                $signer_info = @unserialize($signer_info);
                                if (!($signer_info && is_array($signer_info))) {
                                    $signer_info = array();
                                }
    
                                $signer_name = get_array_value($signer_info, "name");
                                if ($signer_name) {
                                    $title = $signer_name;
                                } else {
                                    $title = app_lang("unknown_user");
                                }
    
                                $avatar = get_avatar(); //show default user image
                            } else {
                                $avatar = get_avatar($notification->user_image, ($notification->user_id ? $notification->user_name : ""));
                                $title = $notification->user_id ? $notification->user_name : get_setting("app_title");
                            }
                        }
    
                        //for custom field changes, we've to check if the field has any restrictions 
                        //like 'visible to admins only' or 'hide from clients'
                        $changes_array = array();
                        if ($notification->activity_log_changes !== "") {
                            if ($notification->event === "bitbucket_push_received" || $notification->event === "github_push_received") {
                                $changes_array = get_change_logs_array($notification->activity_log_changes, $notification->activity_log_type, $notification->event, true);
                            } else {
                                $changes_array = get_change_logs_array($notification->activity_log_changes, $notification->activity_log_type, "all");
                            }
                        }
    
                        if ($notification->activity_log_changes == "" || ($notification->activity_log_changes !== "" && count($changes_array))) {
                            ?>
    
                            <a class="list-group-item border-bottom dropdown-item <?php echo $notification_class; ?>" data-notification-id="<?php echo $notification->id; ?>" <?php echo $url_attributes; ?> >
                                <div class="d-flex text-wrap">
                                    <div class="flex-shrink-0 me-2">
                                        <span class="avatar avatar-xs">
                                            <img src="<?php echo $avatar; ?>" alt="..." />
                                            <!--  if user name is not present then -->
                                        </span>
                                    </div>
                                    <div class="w100p">
                                        <div class="mb5">
                                            <strong><?php echo $title; ?></strong>
                                            <span class="text-off float-end"><small><?php echo format_to_relative_time($notification->created_at); ?></small></span>
                                        </div>
                                        <div class="m0 text-break">
                                            <?php
                                            echo sprintf(app_lang("notification_" . $notification->event), "<strong>" . $notification->to_user_name . "</strong>");
    
                                            //replace anchor tags with text to fix tagging error
                                            echo preg_replace('#<a.*?>(.*?)</a>#i', '\1', view("notifications/notification_description", array("notification" => $notification, "changes_array" => $changes_array)));
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </a>
                            <?php
                        }
                    }
    
                    if ($result_remaining) {
                        $next_container_id = "load" . $next_page_offset . '-' . $key;
                        ?>
                        <div id="<?php echo $next_container_id; ?>-<?php echo $key?>">
    
                        </div>
    
                        <div id="loader-<?php echo $next_container_id; ?>-<?php echo $key?>" >
                            <div class="text-center p20 clearfix margin-top-5">
                                <?php
                                echo ajax_anchor(get_uri("notifications/load_more/" . $next_page_offset . '/' . $key), app_lang("load_more"), array("class" => "btn btn-default load-more mt15 p10 spinning-btn pr0", "data-remove-on-success" => "#loader-" . $next_container_id . "-" . $key, "title" => app_lang("load_more"), "data-inline-loader" => "1", "data-real-target" => "#" . $next_container_id . '-' . $key));
                                ?>
                            </div>
                        </div>
                        <?php
                    } ?>
                <?php else : ?>
                    <span class="list-group-item"><?php echo app_lang("no_new_notifications"); ?></span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php } else { ?>
        <span class="list-group-item"><?php echo app_lang("no_new_notifications"); ?></span>               
    <?php } ?>
    
    
    <script type="text/javascript">
        $(document).ready(function () {
            // Evita que o menu feche ao clicar dentro dele
            /* $(".dropdown-menu").on("click", function (e) {
                e.stopPropagation();
            }); */
    
            // Mantém a aba ativa ao clicar
            $(".nav-link").on("click", function (e) {
                e.preventDefault();
                $(this).tab("show");
            });
    
            // Marca notificações como lidas ao clicar
            $(".unread-notification").click(function () {
                $.ajax({
                    url: '<?php echo get_uri("notifications/set_notification_status_as_read") ?>/' + $(this).attr("data-notification-id")
                });
                $(this).removeClass("unread-notification");
            });
        });
    </script>
<?php else: ?>
    <?php if (count($notifications)) : ?>
        <?php foreach ($notifications as $notification) {

            //get url attributes
            $url_attributes_array = get_notification_url_attributes($notification);
            $url_attributes = get_array_value($url_attributes_array, "url_attributes");
            $url = get_array_value($url_attributes_array, "url");

            //check read/unread class
            $notification_class = "";
            if (!$notification->is_read) {
                $notification_class = "unread-notification";
            }

            if ((!$url || $url == "#") && $url_attributes == "href='$url'") {
                $notification_class .= " not-clickable";
            } else {
                $notification_class .= " clickable";
            }

            $avatar = get_avatar("system_bot", "System Bot");
            $title = get_setting("app_title");
            if ($notification->user_id) {
                if ($notification->user_id == "999999998") {
                    //check if it's bitbucket commit notification
                    $avatar = get_avatar("bitbucket");
                    $title = "Bitbucket";
                } else if ($notification->user_id == "999999997") {
                    //check if it's github commit notification
                    $avatar = get_avatar("github");
                    $title = "GitHub";
                } else if ($notification->user_id == "999999996") {
                    //check if it's public notification of contract/estimate/proposal
                    $signer_info = $notification->contract_meta_data;
                    if ($notification->estimate_id) {
                        $signer_info = $notification->estimate_meta_data;
                    } else if ($notification->proposal_id) {
                        $signer_info = $notification->proposal_meta_data;
                    }

                    $signer_info = @unserialize($signer_info);
                    if (!($signer_info && is_array($signer_info))) {
                        $signer_info = array();
                    }

                    $signer_name = get_array_value($signer_info, "name");
                    if ($signer_name) {
                        $title = $signer_name;
                    } else {
                        $title = app_lang("unknown_user");
                    }

                    $avatar = get_avatar(); //show default user image
                } else {
                    $avatar = get_avatar($notification->user_image, ($notification->user_id ? $notification->user_name : ""));
                    $title = $notification->user_id ? $notification->user_name : get_setting("app_title");
                }
            }

            //for custom field changes, we've to check if the field has any restrictions 
            //like 'visible to admins only' or 'hide from clients'
            $changes_array = array();
            if ($notification->activity_log_changes !== "") {
                if ($notification->event === "bitbucket_push_received" || $notification->event === "github_push_received") {
                    $changes_array = get_change_logs_array($notification->activity_log_changes, $notification->activity_log_type, $notification->event, true);
                } else {
                    $changes_array = get_change_logs_array($notification->activity_log_changes, $notification->activity_log_type, "all");
                }
            }

            if ($notification->activity_log_changes == "" || ($notification->activity_log_changes !== "" && count($changes_array))) {
                ?>

                <a class="list-group-item border-bottom dropdown-item <?php echo $notification_class; ?>" data-notification-id="<?php echo $notification->id; ?>" <?php echo $url_attributes; ?> >
                    <div class="d-flex text-wrap">
                        <div class="flex-shrink-0 me-2">
                            <span class="avatar avatar-xs">
                                <img src="<?php echo $avatar; ?>" alt="..." />
                                <!--  if user name is not present then -->
                            </span>
                        </div>
                        <div class="w100p">
                            <div class="mb5">
                                <strong><?php echo $title; ?></strong>
                                <span class="text-off float-end"><small><?php echo format_to_relative_time($notification->created_at); ?></small></span>
                            </div>
                            <div class="m0 text-break">
                                <?php
                                echo sprintf(app_lang("notification_" . $notification->event), "<strong>" . $notification->to_user_name . "</strong>");

                                //replace anchor tags with text to fix tagging error
                                echo preg_replace('#<a.*?>(.*?)</a>#i', '\1', view("notifications/notification_description", array("notification" => $notification, "changes_array" => $changes_array)));
                                ?>
                            </div>
                        </div>
                    </div>
                </a>
                <?php
            }
        }

        if ($result_remaining) {
            $next_container_id = "load" . $next_page_offset . '-' . $event;
            ?>
            <div id="<?php echo $next_container_id; ?>-<?php echo $event?>">

            </div>

            <div id="loader-<?php echo $next_container_id; ?>-<?php echo $event?>" >
                <div class="text-center p20 clearfix margin-top-5">
                    <?php
                    echo ajax_anchor(get_uri("notifications/load_more/" . $next_page_offset . '/' . $event), app_lang("load_more"), array("class" => "btn btn-default load-more mt15 p10 spinning-btn pr0", "data-remove-on-success" => "#loader-" . $next_container_id . "-" . $event, "title" => app_lang("load_more"), "data-inline-loader" => "1", "data-real-target" => "#" . $next_container_id . '-' . $event));
                    ?>
                </div>
            </div>
            <?php
        } ?>
        
        <script type="text/javascript">
            $(document).ready(function () {
                // Evita que o menu feche ao clicar dentro dele
                /* $(".dropdown-menu").on("click", function (e) {
                    e.stopPropagation();
                }); */
        
                // Mantém a aba ativa ao clicar
                $(".nav-link").on("click", function (e) {
                    e.preventDefault();
                    $(this).tab("show");
                });
        
                // Marca notificações como lidas ao clicar
                $(".unread-notification").click(function () {
                    $.ajax({
                        url: '<?php echo get_uri("notifications/set_notification_status_as_read") ?>/' + $(this).attr("data-notification-id")
                    });
                    $(this).removeClass("unread-notification");
                });
            });
        </script>
    <?php else : ?>
        <span class="list-group-item"><?php echo app_lang("no_new_notifications"); ?></span>
    <?php endif; ?>
<?php endif; ?>
