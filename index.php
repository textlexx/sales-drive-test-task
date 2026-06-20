<?php

require_once('./require_classes.php');

//global $send;

$send = new SendData;

if(
    !isset($_GET['start_chat']) && 
    !isset($_GET['set_hook']) &&
    !isset($_GET['del_hook'])
) {

    $send->run();

    require_once('./pages/main.php');
}
elseif(isset($_GET['set_hook'])){

    $send->set_telegram_webhook();
}
elseif(isset($_GET['del_hook'])){

    $send->del_telegram_webhook();
}
elseif(isset($_GET['start_chat'])){

    $send->get_response_from_telegram_bot();
}