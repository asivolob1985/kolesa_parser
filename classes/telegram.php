<?php

class telegram {

    protected $chatID = '874356888';
    protected $token = '2011696508:AAF4lrCgsrcRI45SfLn19MeOaNVlSeUHq34';

    public function message_to_telegram($text) {
        $this->sendMessage($text);

    }

    protected function sendMessage($text) {
        $ch = curl_init();
        curl_setopt_array(
            $ch,
            [
                CURLOPT_URL            => 'https://api.telegram.org/bot'.$this->token.'/sendMessage',
                CURLOPT_POST           => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_POSTFIELDS     => [
                    'chat_id'    => $this->chatID,
                    'text'       => $text,
                    'parse_mode' => "HTML",
                ],
            ]
        );
        $res = curl_exec($ch);
    }

}

 
