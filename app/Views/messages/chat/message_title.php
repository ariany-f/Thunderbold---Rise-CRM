<div id="js-chat-messages-title">
    <?php
    $me = $login_user->id;
    if(get_setting('module_message_group'))
    {
        echo "<strong class='p10 block chat-message-title'>" . app_lang("subject") . ": " . $first_message->subject . "</strong>";
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
