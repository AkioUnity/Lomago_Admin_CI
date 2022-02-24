<?php

class Whatsapp extends API_Controller
{
    public $isWabox = false;
    public $wabox_token = '51ed0669bea9c01cf3cf2144cd0049975c7a994025fa9';
    public $wa_phone = '8562092175213';

    //https://admin.lomago.io/api/whatsapp/sendMessage
    public function sendMessage_get(){
        $result= $this->sendWhatsappMessage_get($this->get('text','test_message'),$this->get('phone','8562092175213'));
        echo $result;
    }

//https://admin.lomago.io/api/whatsapp/sendWhatsappMessage
    public function sendWhatsappMessage_get($message = "Hello World",$recipient = "8562092175213")
    {
        // Make sure the recipient is inside the 24h window.
// If not, just send a message TO your channel to open the 24h window again.

// Put your bearer token hier. You can create it via API call: https://api.messengerpeople.dev/docs/authentication
// or open app.messengerpeople.dev and navigate to Settings - OAuth Apps, select the proper App (or create one) and click on "Create Token"
        $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjkyZjhjMzliZjgwMGQyZDg0NjJlYzJiZDZlZTY0NTI2ZGZjZGFjMTY5ZmFhZTM2MzAyODg4YzY0MmVlNWQ4Njc0MzU3NjMxYjcyM2RiZmM0In0.eyJhdWQiOiI0YmIyMGMyYzM0ODM0ODI0ZjMxZmQ0NDY5MjhkMzIxZiIsImp0aSI6IjkyZjhjMzliZjgwMGQyZDg0NjJlYzJiZDZlZTY0NTI2ZGZjZGFjMTY5ZmFhZTM2MzAyODg4YzY0MmVlNWQ4Njc0MzU3NjMxYjcyM2RiZmM0IiwiaWF0IjoxNjQzODgwNTkyLCJuYmYiOjE2NDM4ODA1OTIsImV4cCI6MTk1OTQxMzM5Miwic3ViIjoiIiwic2NvcGVzIjpbImNybTphZG1pbiIsImNybTpiaWxsaW5nIiwibWVkaWE6Y3JlYXRlIiwibWVkaWE6ZGVsZXRlIiwibWVkaWE6cmVhZCIsIm1lc3NhZ2VzOmRlbGV0ZSIsIm1lc3NhZ2VzOnJlYWQiLCJtZXNzYWdlczpzZW5kIiwibWVzc2VuZ2VyOnNldHRpbmdzIiwib2F1dGg6c2NvcGVzOnJlYWQiLCJvYXV0aDp1c2VyczptYW5hZ2UiLCJwYXJ0bmVyOnVzZXJzOm1hbmFnZSIsInN1YnNjcmlwdGlvbnM6Y3JlYXRlIiwic3Vic2NyaXB0aW9uczpkZWxldGUiLCJzdWJzY3JpcHRpb25zOnJlYWQiLCJzdWJzY3JpcHRpb25zOnVwZGF0ZSIsInRlbXBsYXRlczpjcmVhdGUiLCJ0ZW1wbGF0ZXM6ZGVsZXRlIiwidGVtcGxhdGVzOnJlYWQiLCJ0ZW1wbGF0ZXM6dXBkYXRlIiwidXNlcjpwcm9maWxlIiwidXNlcjpwcm9maWxlOnB1YmxpYyIsInVzZXI6c2NvcGVzIiwid2ViaG9va3M6Y3JlYXRlIiwid2ViaG9va3M6ZGVsZXRlIiwid2ViaG9va3M6cmVhZCIsIndlYmhvb2tzOnVwZGF0ZSJdLCJjdXN0b21lcl9pZCI6MTAyMjY2MX0.ZIFXuBQoAuYyICykHCbnbqM4ofUDF1a_mPURY_iY4S4LVq9emyNlFQLrmJbj2FRl62yjaC-i9YVf6MXJypemAQ3KcD9leDhrT7nQ4AxGmzdrpJnZ3P4iLbkJDssVMTE0huba0USFaPqKZofe7cOHeJBaqJ8mQR1TIwd_qG2dqncH49gIao-Qs6HfxGVYYk-6mtjm-qvaKsslYar5cZo_t04mjUIFK9ihBh-D0Bvob5Zh3NbeX1HXiKDQpE72nv0FzReYa3PeZhQuatX0TSqmTti6hM6mecixW017jPDI7kkbR2cz02YoNIHZ90qxOOdpyP7RAN08_LDwLiiRODvyZg";

        $channel_uuid = "ceccf9cc-d02c-4140-90e6-7f76ddde279d"; // Put the UUID of your channel here
        $curl = curl_init();

        $recipient=str_replace("0049","49",$recipient);

        $payload = [
            "sender" => $channel_uuid,
            "recipient" => $recipient,
            "payload" => [
                "type" => "text",
                "text" => $message
            ]
        ];

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.messengerpeople.dev/messages',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$token,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    public function send_message($send)
    {
        if (!$this->isWabox)
            $response = $this->sendWhatsappMessage_get($send['text'],$send['to']);
        else{
            $base_url = 'https://www.waboxapp.com/api/send/chat?';
            $url = $base_url . "token=" . $send['token'] . "&uid=" . $send['uid'] . "&to=" . $send['to'] . "&custom_uid=" . $send['custom_uid'] . "&text=" . urlencode($send['text']);
            $response = json_decode($this->getCURL($url), true);
        }
        return ($response);
    }


    public function getCURL($_url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
//        curl_setopt($ch, CURLOPT_HEADER, false);
//        curl_setopt($ch, CURLOPT_POST, count($postData));

        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
}