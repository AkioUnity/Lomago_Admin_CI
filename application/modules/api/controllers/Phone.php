<?php
/** @noinspection SqlNoDataSourceInspection */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Demo Controller with Swagger annotations
 * Reference: https://github.com/zircote/swagger-php/
 */
class Phone extends API_Controller
{
//    http://www.lomago.io/whatsapp/api/phone/chats/103
//    http://www.lomago.io/whatsapp/api/phone/chats/115  for TEST-BERATER
    public function chats_get($user_id)
    {
        $wpDb = $this->load->database('lamoga', TRUE);
        $sql = "SELECT LAMOGA_WAF_request".$this->wa_portal_id.".user_id,LAMOGA_WAF_request".$this->wa_portal_id.".type,LAMOGA_WAF_request".$this->wa_portal_id.".customer_phone,LAMOGA_WAF_request".$this->wa_portal_id.".status,pts_useradressen".$this->wa_portal_id.".user_login as name FROM LAMOGA_WAF_request".$this->wa_portal_id." INNER JOIN pts_useradressen".$this->wa_portal_id." on LAMOGA_WAF_request".$this->wa_portal_id.".user_id=pts_useradressen".$this->wa_portal_id.".ID WHERE consultant_id=" . $user_id;
        $res = $wpDb->query($sql)->result();
        foreach ($res as $row) {
            $sql = "select text,time from w_receive_messages where (((sender_id=" . $user_id . " and receiver_id=" . $row->user_id . ") or (sender_id=" . $row->user_id . " and receiver_id=" . $user_id . "))  and event='".$row->type."' ) order by id desc limit 1";
            $message = $this->db->query($sql)->row();
            if (isset($message)) {
                $row->lastMessage = $message->text;
                $row->time = $message->time;
            }
            $row->balance=$this->GetBalance($row->user_id)->credit_available;
            $row->profileImage = "https://bootdey.com/img/Content/avatar/avatar5.png";
        }
        $this->response($res);
    }

    //    http://www.lomago.io/whatsapp/api/phone/messages/104/9/facebook
    // http://www.lomago.io/whatsapp/api/phone/messages/115/114/whatsapp
    // http://www.lomago.io/whatsapp/api/phone/messages/105/9/telegram
    // sometimes.. error  (sender_id=whatsapp and receiver_id=114) and event='undefined')
    public function messages_get($user1, $user2,$type)
    {
        $sql = "SELECT * FROM (SELECT id,text,time,sender_id from w_receive_messages where (((sender_id=" . $user1 . " and receiver_id=" . $user2 . ") or (sender_id=" . $user2 . " and receiver_id=" . $user1 . ")) and event='".$type."')  ORDER BY id DESC limit 14) sub ORDER BY id";
        $messages = $this->db->query($sql)->result();
        $this->response($messages);
    }
//    http://www.lomago.io/whatsapp/api/phone/messages/104/9/facebook
    public function GetBalance($user_id)
    {
        $url = 'https://www.audiotex-0900.de/whatsapp/whatsapp_send.php/?';
        $data = array(
            'url' => 'lamoga.de', 'client_id' => $user_id
        , 'auth' => "3d7fhezeTuZfkiedopdWq-12\$S", 'hash' => md5("lamoga.de")
        );
        $response = json_decode($this->postCURL($url, $data));
        return ($response);
    }
}
