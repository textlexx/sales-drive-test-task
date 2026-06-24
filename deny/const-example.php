<?php

/*
return [
    'server' => '',
    'user' => '',
    'pass' => '',
    'db' => '',
    'port' => '',
];
*/
$db1 = require_once('./deny/db1_access.php');

define('DB_CONNECTIONS', [
    0 => $db1
]);

define('EMPTY_OBJ', (object) ['empty' => 1]);

define('SALES_DRIVE_API_KEY', '111111');
define('TELEGRAM_API_TOKEN', '1111111:1111111');
// If send webhook url by this link the telegram api added it, 
// else if send empty string so webhook added early will deleted.
define(
    'SET_TELEGRAM_WEBHOOK_URL', 
    'https://api.telegram.org/bot'.TELEGRAM_API_TOKEN.'/setWebhook'
    // Example of your webhook link
    //.'?url=' . urlencode($serverUrl) . '&secret_token=' . urlencode($secretToken)
);
define('DILOVOD_API_KEY', '1111111');