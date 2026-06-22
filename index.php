<?php

require_once('./require_classes.php');

//global $send;

if( SendData::dbConnection() ){

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

        $send->set_or_del_tgram_webhook(1);
    }
    elseif(isset($_GET['del_hook'])){

        $send->set_or_del_tgram_webhook(0);
    }
    elseif(isset($_GET['start_chat'])){

        $send->get_response_from_telegram_bot();
    }
}