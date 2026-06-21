<?php

class SendData{
    
    public static $db = '';

    public $salesdrive_url = "https://textlexx.salesdrive.me/handler/";
    public $salesdrive_headers = [
        "Content-Type: application/json",
        "X-Api-Key: ".SALES_DRIVE_API_KEY
    ];

    public $tl_gram_send_url = 'https://api.telegram.org/bot'.TELEGRAM_API_TOKEN.'/sendMessage';
    
    public $uname = '';
    public $phone = '';

    

    public function run( $products = [] ){

        $dat = $this->check_data_return();
        if( ! $dat ) return false;

        $order_id = time();

        $products = [];
            
        $products[0]["id"] = "11111"; // id товару
        $products[0]["name"] = "Тестовий товар"; // назва товару
        $products[0]["costPerItem"] = "2000"; // ціна
        $products[0]["amount"] = "1"; // кількість
        $products[0]["description"] = "Тестовий опис"; // опис товарної позиції в заявці
        $products[0]["discount"] = "10%"; // знижка, задається в % або в абсолютній величині
        $products[0]["sku"] = "sku-11111"; // артикул (SKU) товару

        $shipping_method = 'novaposhta';
        $payment_method = 'Післяплата';
        $shipping_address = 'Місто тест, вулиця тест';
        $shipping_data = [
            "ServiceType" => "Warehouse", // можливі значення: Warehouse, Doors
            "payer" => "recipient", // можливі значення: "sender", "recipient"
            "area" => "Житомирська", // область російською або українською мовою, або Ref області в системі Нової пошти
            "region" => "Житомирський", // район російською або українською мовою (використовується тільки якщо cityNameFormat=settlement)
            "city" => "Коростишів", // назва міста російською чи українською мовою, або Ref міста у старій чи новій адресній системі системі Нової пошти. Під час передачі назви міста: у режимі cityNameFormat=short слід передавати лише назву міста; у режимі cityNameFormat=full слід передавати назву міста у старій адресній системі Нової Пошти.
            "cityNameFormat" => "short", // можливі значення: "full" (за замовчуванням), "short"
            "WarehouseNumber" => "Відділення №1", // відділення Нової Пошти в одному з форматів: номер, опис, Ref
            "Street" => "", // назва і тип вулиці, або Ref вулиці в системі Нової пошти
            "BuildingNumber" => "", // номер будинку
            "Flat" => "", // номер квартири
            "ttn" => "112233445566778" // ТТН
        ];

        $salesdrive_values = [
            "getResultData" => "1", // Отримувати дані створеної заявки (0 - не отримувати, 1 - отримувати)
            "products"=>$products, 
            "comment"=>"", 
            "externalId"=>$order_id, // Зовнішній номер заявки
            "fName"=>$dat->uname, 
            "lName"=>"",
            "mName"=>"",
            "phone"=>$dat->phone,
            "email"=>"",
            "con_comment"=>"",
            "counterparty"=> [
                "name" => "", // Назва контрагента 
                "code" => "", // Номер ЄДРПОУ контрагента 
            ],
            "shipping_method"=>$shipping_method,
            "payment_method"=>$payment_method,
            "shipping_address"=>$shipping_address,

            // Shipping method can be one from enum 
            // "novaposhta|ukrposhta|meest|rozetka_delivery"
            // and be setted by program what will need
            "$shipping_method"=> $shipping_data,
            
            "orderStock"=>"", // Склад
            "stockId"=>"", // id складу
        ];

        $salesdrive_ch = curl_init();

        curl_setopt($salesdrive_ch, CURLOPT_URL, $this->salesdrive_url);
        curl_setopt($salesdrive_ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($salesdrive_ch, CURLOPT_HTTPHEADER, $this->salesdrive_headers);
        //curl_setopt($salesdrive_ch, CURLOPT_SAFE_UPLOAD, true);
        curl_setopt($salesdrive_ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($salesdrive_ch, CURLOPT_POST, 1);
        curl_setopt($salesdrive_ch, CURLOPT_POSTFIELDS, json_encode($salesdrive_values));
        curl_setopt($salesdrive_ch, CURLOPT_TIMEOUT, 15);

        $salesdrive_res = curl_exec($salesdrive_ch);

        if( $salesdrive_res ){

            $salesdrive_res = json_decode($salesdrive_res);
        }

        $salesdrive_res = (object) $salesdrive_res;

        if(
            $salesdrive_res && is_object($salesdrive_res) && 
            property_exists($salesdrive_res, 'success')
        ){

            if($salesdrive_res->success) 
                Notifications::set_s('Дані успішно відправлені.');
            else 
                Notifications::set_e('Помилка відправки даних.');
        }else{

            Notifications::set_e('Помилка відправки даних.');
        }
    }

    public function check_data_return(){

        if(isset($_POST['name']) && isset($_POST['tel'])){

            $fName = $_POST['name'];
            
            // For first do clear no need symbols
            $fTel = preg_replace('#[\-\s+]+#', '', $_POST['tel']);

            // Allow cyr and eng symbs big or small
            if( ! preg_match('#^[a-zA-ZЙйЦцУуКкЕеНнГгШшЩщЗзХхЇїФфІіВвАаПпРрОоЛлДдЖжЄєЭэЯяЧчСсМмИиТтЬьБбЮюЁёЫыЪъ]{2,20}$#i', $fName)){

                Notifications::set_e('Помилка. Ви не правильно ввели "ім`я".');

                return false;
            }

            // First check if number in format without 380 for example: 0631234567
            if( ! preg_match('#^0[1-9]{1}[1-9]{1}[0-9]{7}$#', $fTel) ){

                if( ! preg_match('#^380[1-9]{1}[1-9]{1}[0-9]{7}$#', $fTel) ){

                    Notifications::set_e('Помилка. Ви не правильно ввели "номер телефону".');

                    return false;
                }
            }

            Notifications::set_s('Дані введено вірно.');

            return (object) [
                'uname' => $fName,
                'phone' => $fTel,
            ];
        }

        return false;
    }

    public function set_telegram_webhook(){

        if( !isset($_GET['set_hook']) ) return false;
        //--------------------------------------

        $serverUrl = 'https://creation.zt.ua/send-data/?start_chat';

        $responseSaveStatusFile = $_SERVER['DOCUMENT_ROOT'].'/send-data/config/response_save_status_file.txt';

        $secretTokenFile = $_SERVER['DOCUMENT_ROOT'].'/send-data/config/secret_token.txt';
        clearstatcache();
        if( ! file_exists($secretTokenFile) ) {

            $secretToken = bin2hex(random_bytes(32));
            file_put_contents($secretTokenFile, $secretToken, LOCK_EX);
        }else{

            $secretToken = file_get_contents($secretTokenFile);

            if( preg_match('#^[\s]*$#', $secretToken) ) return false;
        }        

        // Формируем URL для привязки вебхука
        $url = 
        // SET_TELEGRAM_WEBHOOK_URL = 'https://api.telegram.org/bot{$botToken}/setWebhook'
        SET_TELEGRAM_WEBHOOK_URL
        ."?url=" . urlencode($serverUrl) . "&secret_token=" . urlencode($secretToken);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        if(function_exists('curl_close')) curl_close($ch);

        file_put_contents($responseSaveStatusFile, $response, LOCK_EX);

        //echo $response;
    }

    public function del_telegram_webhook(){

        if( !isset($_GET['del_hook']) ) return false;
        //--------------------------------------

        $responseSaveStatusFile = $_SERVER['DOCUMENT_ROOT'].'/send-data/config/response_save_status_file.txt';

        // Формируем URL для удаления вебхука
        $url = 
        // SET_TELEGRAM_WEBHOOK_URL = 'https://api.telegram.org/bot{$botToken}/setWebhook'
        SET_TELEGRAM_WEBHOOK_URL;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        if(function_exists('curl_close')) curl_close($ch);

        file_put_contents($responseSaveStatusFile, $response, LOCK_EX);

        //echo $response;
    }

    public function get_response_from_telegram_bot(){

        if( !isset($_GET['start_chat']) ) return false;
        //--------------------------------------

        $responseSaveStatusFile = $_SERVER['DOCUMENT_ROOT'].'/send-data/config/response_save_status_file.txt';
        $errorResponseTelApiFile = $_SERVER['DOCUMENT_ROOT'].'/send-data/config/t_api_response_error.txt';

        $secretTokenFile = $_SERVER['DOCUMENT_ROOT'].'/send-data/config/secret_token.txt';
        clearstatcache();
        if( ! file_exists($secretTokenFile) ) {

            file_put_contents($errorResponseTelApiFile, 'secret token file not exists', LOCK_EX);

            return false;
        }

        $secretToken = file_get_contents($secretTokenFile);
        // If file empty
        if( preg_match('#^[\s]*$#', $secretToken) ) {
            
            file_put_contents($errorResponseTelApiFile, 'secret token file is empty', LOCK_EX);

            return false;
        }

        // We receive the data sent by Telegram       
        $content = file_get_contents("php://input");
        $update = json_decode($content, true);
        $headers = getallheaders();

        /*
        $receivedToken = isset($headers['X-Telegram-Bot-Api-Secret-Token']) 
            ? $headers['X-Telegram-Bot-Api-Secret-Token'] 
            : '';
        */

        // For check what data in response if need
        file_put_contents($responseSaveStatusFile, /*$receivedToken."\n\n".*/$content, LOCK_EX);
        //-------------------------------------
        
        if(isset($headers['X-Telegram-Bot-Api-Secret-Token'])){

            $secretTokenFromResponse = $headers['X-Telegram-Bot-Api-Secret-Token'];

            if($secretTokenFromResponse != $secretToken) {

                file_put_contents($errorResponseTelApiFile, 
                'secret token in FILE and secret token from RESPONSE not match', LOCK_EX);

                return false;
            }
        }
            

        //------------------------------
        // SAVE DATA OF USER CHAT_ID
        //------------------------------


        if (isset($update['message']) && $update['message']['text'] == '/start') {

            $chat_id = $update['message']['chat']['id'];
            /*
            $username = 
            isset($update['message']['chat']['username']) ? 
            $update['message']['chat']['username'] : '';
            */

            $message = "Повідомлення з боту телеграм.";

            $data = [
                'chat_id' => $chat_id,
                'text' => $message,
                'parse_mode' => 'HTML'
            ];

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $this->tl_gram_send_url);
            curl_setopt($ch, CURLOPT_POST, true);
            // You can also pass json_encode($data), changing the headers
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // for a local server (OpenServer, etc.)

            $response = curl_exec($ch);

            if(curl_errno($ch)){

                file_put_contents($errorResponseTelApiFile, curl_error($ch), LOCK_EX);
            }

            if(function_exists('curl_close')) curl_close($ch);

            $result = json_decode($response, true);

            if (isset($result['ok']) && $result['ok']) {

                //file_put_contents($responseSaveStatusFile, 'Status ok.', LOCK_EX);
            } else {

                if(isset($result['description']))
                file_put_contents($errorResponseTelApiFile, 'ERROR. '.$result['description'], LOCK_EX);
            }
        }
    }

    public static function dbConnection($db_extension_class = 'CDBMysqli', $charset = 'utf8', $collation = 'utf8_general_ci'){		
		
		if( ! SendData::$db = $db_extension_class::createDb() ){
			
            Notifications::set_e('Error. Not connection to db.');

			return false;
		}
		
		$db_extension_class::setDbCharset($charset, $collation);
		
		return true;
	}
}


// Example data from telegram bot:

/*
{
  "update_id": 11111111,
  "message": {
    "message_id": 9,
    "from": {
      "id": 11111111,
      "is_bot": false,
      "first_name": "Name",
      "last_name": "Surname",
      "username": "fffffff",
      "language_code": "ru"
    },
    "chat": {
      "id": 11111111,
      "first_name": "Name",
      "last_name": "Surname",
      "username": "fffffff",
      "type": "private"
    },
    "date": 1781979358,
    "text": "/start",
    "entities": [
      {
        "offset": 0,
        "length": 6,
        "type": "bot_command"
      }
    ]
  }
}
*/


// Example data from bot after the user exit from bot:

/*
{
  "update_id": 262419377,
  "my_chat_member": {
    "chat": {
      "id": 11111111,
      "first_name": "Name",
      "last_name": "Surname",
      "username": "name",
      "type": "private"
    },
    "from": {
      "id": 11111111,
      "is_bot": false,
      "first_name": "Name",
      "last_name": "Surname",
      "username": "name",
      "language_code": "ru"
    },
    "date": 1781978973,
    "old_chat_member": {
      "user": {
        "id": 11111111,
        "is_bot": true,
        "first_name": "Textlexx",
        "username": "textlexx_bot"
      },
      "status": "member"
    },
    "new_chat_member": {
      "user": {
        "id": 11111111,
        "is_bot": true,
        "first_name": "Textlexx",
        "username": "textlexx_bot"
      },
      "status": "kicked",
      "until_date": 0
    }
  }
}
*/
