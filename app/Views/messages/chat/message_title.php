<div id="js-chat-messages-title">
    <?php
    $me = $login_user->id;
    $task_id = $first_message->task_id;
    if(get_setting('module_message_group'))
    {
        if(!$task_id) {
            echo "<strong class='p10 block chat-message-title'>" . app_lang("subject") . ": " . $first_message->subject . "</strong>";
            if($login_user->user_type === 'staff')
            {
                echo "<div class='text-right mb15'>";
                echo ajax_anchor(get_uri("messages/create_task/" . $first_message->id . ""), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('convert_task'), array("class" => "btn btn-warning", "id" => "convert_task", "title" => app_lang('create_task'), "data-reload-on-success" => "1"));
                echo "</div>";
            }
        }
        else
        {
            echo "<strong class='p10 block chat-message-title'>" . app_lang("subject") . ": " . $first_message->subject . "</strong>";
        }
        echo view("messages/chat/single_message", array("reply_info" => $first_message));
    }
    else
    {
        if($first_message->group_name == "")
        {
            echo "<strong class='p10 block chat-message-title'>" . app_lang("subject") . ": " . $first_message->subject . "</strong>";
            echo view("messages/chat/single_message", array("reply_info" => $first_message));
        }
    }
    ?>

</div>
<div id="js-chat-old-messages">

</div>
