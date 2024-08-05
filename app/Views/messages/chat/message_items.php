<?php

foreach ($replies as $reply_info) {
    if(get_setting('module_message_group'))
    {
        echo view("messages/chat/single_message", array("reply_info" => $reply_info));
    }
    else
    {
        if($reply_info->group_name == "")
        {
            echo view("messages/chat/single_message", array("reply_info" => $reply_info));
        }
    }
}

if ($is_online) {
    ?>
    <script class="temp-script">
        $("#js-active-chat-online-icon").removeClass("hide");
        $(".temp-script").remove();
        $("#is_user_online").val("1");
    </script>
    <?php

} else {
    ?>
    <script class="temp-script">
        $("#js-active-chat-online-icon").addClass("hide");
        $(".temp-script").remove();
        $("#is_user_online").val("0");
    </script>
    <?php

}