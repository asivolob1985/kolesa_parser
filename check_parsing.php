<?php

 $mess = 'Парсинг выполнился.';
$cur_date = date('Y-m-d');
$log_file = $path = __DIR__.'/classes/debug_logs/'.date('Y-m-d').'/log.txt';
if(!is_file($log_file)){
    $mess = 'Парсинг не был запущен.';
}else{
    $size = filesize($log_file);
    if($size < 1000 ){
        $mess = 'Парсинг был запущен, но не прошел.';
    }
}

define('TELEGRAM_TOKEN', '2011696508:AAF4lrCgsrcRI45SfLn19MeOaNVlSeUHq34');
// сюда нужно вписать ваш внутренний айдишник
define('TELEGRAM_CHATID', '-251543225');

message_to_telegram($mess);

function message_to_telegram($text)
{
    $ch = curl_init();
    curl_setopt_array(
        $ch,
        array(
            CURLOPT_URL => 'https://api.telegram.org/bot' . TELEGRAM_TOKEN . '/sendMessage',
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_POSTFIELDS => array(
                'chat_id' => TELEGRAM_CHATID,
                'text' => $text,
                'parse_mode' => "HTML",
            ),
        )
    );
  // $res = curl_exec($ch);

}
