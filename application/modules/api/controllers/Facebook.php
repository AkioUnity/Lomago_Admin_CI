<?php
/** @noinspection SqlNoDataSourceInspection */
defined('BASEPATH') OR exit('No direct script access allowed');

class Facebook extends API_Controller
{

    public static $lomago_token = "Lomago_Token";

    public static $postData;
    public static $token = "EAAqZArcydXpUBAFQJbRF7zCv8t3djCt9VwX2MxZBKkcDG8wsIXABu0SZAbACGJ43phENsMIDysTFQVHZCZBl8tBxyPD7EZBE9qiE3upI61GmB7quFmLCZCio4ZAA2Nen5QZAn6JjjxZCbrVcL5Auuf2FbsZAMDcFEzVihXA2lmMVBkxDBf9EuuoV8ZCSKGr5W2olftAZD";

    public function __construct()
    {
        parent::__construct();
        self::$postData = file_get_contents('php://input');
    }

    //    https://www.lomago.io/whatsapp/api/facebook/webhook
    public function webhook_post(){

    }

    public function webhook_get(){
        if (isset($_GET['hub_verify_token']) && isset($_GET['hub_mode'])) {
            $verify_token =$_GET['hub_verify_token'];
            $challenge = $_GET['hub_challenge'];
            $mode=$_GET['hub_mode'];
            if ($verify_token ==self::$lomago_token && $mode=='subscribe') {
                echo $challenge;
            } elseif ($verify_token != self::$lomago_token) {
                $this->response(null, 403);
            }
        }
    }


    public static function getMessage() {
        $output = json_decode(self::$postData);
        return $output->entry[0]->messaging[0]->message->text;
    }

    public static function getSender() {
        $output = json_decode(preg_replace('/"id":(\d+)/', '"id":"$1"',self::$postData ));
        return $output->entry[0]->messaging[0]->sender->id;
    }

    public static function sendFormattedMessage($recipient, $elements) {
        $json = '{
                "recipient":{"id":"' . $recipient . '"},
                "message":{
                  "attachment":{
                    "type":"template",
                    "payload":{
                      "template_type":"generic",
                      "elements":' . json_encode($elements) . '
                    }
                  }
                  }}';

        $options = array(
            'http' => array(
                'method' => 'POST',
                'content' => $json,
                'header' => "Content-Type: application/json\r\n" .
                    "Accept: application/json\r\n"
            )
        );
        $url = 'https://graph.facebook.com/v2.6/me/messages?access_token=' . self::$token;
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return $json;
    }

    public static function sendMessage($recipient, $message) {
        $json = '{
                "recipient":{"id":"' . $recipient . '"},
                "message":{
                  "text":"' . $message . '"
                }
              }';
        $options = array(
            'http' => array(
                'method' => 'POST',
                'content' => $json,
                'header' => "Content-Type: application/json\r\n" .
                    "Accept: application/json\r\n"
            )
        );
        $url = 'https://graph.facebook.com/v2.6/me/messages?access_token=' . self::$token;
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return $json;
    }
}
