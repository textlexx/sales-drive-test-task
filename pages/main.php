<?php

//global $send;

$err = Notifications::get_t();
$scs = Notifications::get_s();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Sales Driver Test Task</title>
    
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>

    <main id="container">
        <div class="out-form-wrap">

            <form action="./" method="post">

                <?php
                if( ! $err || ! is_array($err) || count($err) < 1 ){

                    if( is_array($scs) && count($scs) > 0 ){
                ?>

                    <div class="message">
                        <?php
                        foreach( $scs as $v ){

                            echo $v.'<br>';
                        }
                        ?>
                    </div>

                    <?php
                    }else{
                    ?>

                    <div class="message">
                        Форма відправки заявки
                    </div>

                    <?php
                    }
                    ?>

                <?php
                }else{
                ?>

                <div class="message">
                <?php
                foreach( $err as $v ){

                    echo $v.'<br>';
                }
                ?>
                </div>

                <?php
                }
                ?>


                <div class="tgram">
                    <a target="_blank" href="https://t.me/textlexx_bot">
                        Щоб перестрахуватися спочатку добавте бот телеграма він буде повідомляти вас про збої.
                        Для переходу натисніть на це зелене посилання.
                    </a>
                </div>



                <label>
                    <div class="note">Ім'я:</div>
                    <input type="text" id="name" name="name">
                </label>

                <label>
                    <div class="note">Телефон (цей номер має збігатися з номером, який ви додаєте в бота телеграм):</div>
                    <input type="tel" id="tel" name="tel">
                </label>

                <input type="submit" id="send_btn" name="send_btn">
            </form>
        </div>
    </main>

</body>

</html>