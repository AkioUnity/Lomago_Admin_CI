<?php
/** @noinspection SqlNoDataSourceInspection */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Demo Controller with Swagger annotations
 * Reference: https://github.com/zircote/swagger-php/
 */
class Users extends API_Controller
{
    public $wabox_token = '51ed0669bea9c01cf3cf2144cd0049975c7a994025fa9';
    public $agent_token = 'a8c3a69806144c45b100f0fd0f06f232';
    public $wa_phone = '8562092175213';

//    http://www.lomago.io/whatsapp/api/users/sms
    public function sms_post()
    {
        $data = array('result' => 'ok');
        $this->response($data);
    }

    public function sms_get()
    {
        $data = array('result' => 'ok');
        $this->response($data);
    }

    //    https://admin.lomago.io/api/users/messengerpeople    //webhook
    // http://www.lomago.io/whatsapp/api/users/messengerpeople
    public function messengerpeople_post()
    {
        $payload = $this->post();
        log_message('error', json_encode($payload));
        $response = [
            "success" => true
        ];
        if (isset($payload['challenge']) && isset($payload['verification_token'])) {
            $verification_token = $payload['verification_token'];
            // If verification code does not match the value set by you, send 403 - forbidden.
            if ($verification_token !== "lomago_token") {
                http_response_code(403);
                log_message('error', "Wrong verification code.");
                die("Wrong verification code.");
            }

            // Add challenge to the response.
            $response['challenge'] = $payload['challenge'];
        }
//        http_response_code(200);
//        echo json_encode($response);
        $this->response($response);
    }

//report
    public function hook_post()
    {
//        $report_id=$this->post()['report_id'];
        $data = elements(array('event', 'token', 'uid', 'ack'), $this->post());

        $contact = $this->post()['contact'];
        $data['phone'] = $contact['uid'];
        $data['sender_name'] = $contact['name'];
        $message = $this->post()['message'];
        $data['time'] = date('Y-m-d H:i:s', $message['dtm']);
        $data['dir'] = $message['dir'];
        $data['text'] = $message['body']['text'];
        if ($data['event'] == "message") {
            $data['event'] = 'whatsapp';
            $send = array();
            $send['token'] = $data['token'];
            $send['uid'] = $data['uid'];  //connected phone
            $send['to'] = $data['phone'];  // sender
            $send['custom_uid'] = $message['uid'];
            $send['text'] = $data['text'];
            $send['name'] = $data['sender_name'];
            $send['time'] = $data['time'];
            $send['dir'] = $data['dir'];

//            $this->UpdateLHCDb($send);
            $res = "error";
            if ($message['dir'] == 'i') {  //input
//                $this->dialogflow($send);
                $phone = substr($send['to'], 2);
//                $LAMOGA_WAF = $this->getCustomerPhone($phone);  //from consultant to customer
//                if ($LAMOGA_WAF == null)
                $LAMOGA_WAF = $this->getConsultantPhone($phone);
                if ($LAMOGA_WAF != null) {
                    $send['to'] = $LAMOGA_WAF->phone;
                    $send['custom_uid'] = time();
                    $send['uid'] = $this->wa_phone;
//                    $res = $this->send_message($send);  // not sending to Consultant whatsapp
                    $res = $this->SendBillingServerWA($send, $LAMOGA_WAF);

                    $data['sender_id'] = $LAMOGA_WAF->sender_id;
                    $data['receiver_id'] = $LAMOGA_WAF->receiver_id;
                    $this->load->model('w_receive_message_model', 'receive_model');
                    $this->receive_model->insert($data);
                }
            }

            $this->response($res);
        }
    }

    public function UpdateLHCDb($send)
    {
        $query = $this->db->query("select id from lh_chat where (hash='" . $send['uid'] . "' and session_referrer='" . $send['to'] . "')");
        $record = $query->row();
        $chat_id = 0;
        if (!$record) {
            $sql = "insert into lh_chat (nick,hash,session_referrer,time,referrer) values('" . $send['name'] . "','" . $send['uid'] . "','" . $send['to'] . "'," . $send['time'] . ",'//web.whatsapp.com')";
            $this->db->query($sql);
            $chat_id = $this->db->insert_id();
        } else
            $chat_id = $record->id;
        $pos = strpos($send['text'], "(ChatID=");
        if ($pos) {
            $pos += 8;
            $web_chat_id = substr($send['text'], $pos, strpos($send['text'], ")", $pos) - $pos);
            $dataQ = array('chat_id' => $chat_id);
            $this->db->where('chat_id', $web_chat_id);
            $this->db->update('lh_msg', $dataQ);
        }

        $dataQ = array(
            'msg' => $send['text'],
            'time' => $send['time'],
            'chat_id' => $chat_id,
            'user_id' => ($send['dir'] == 'i') ? 0 : -3,
            'name_support' => $send['name']
        );
        $this->db->insert('lh_msg', $dataQ);

        $msg_id = $this->db->insert_id();

        $dataQ = array(
            'last_user_msg_time' => $send['time'],
            'lsync' => $send['time'],
            'last_msg_id' => $msg_id,
            'has_unread_messages' => 1,
            'unanswered_chat' => 1,
            'remarks' => $send['token'],
        );
        $this->db->where('id', $chat_id);
        $this->db->update('lh_chat', $dataQ);

    }

    public function dialogflow($obj)
    {
        $base_url = 'https://api.dialogflow.com/v1/';
        $version_date = '20170712'; //'20170712';
        $language = 'en';
        $session_id = 'somerandomthing';
        $url = $base_url . "query?v=" . $version_date;
        $data = array('query' => $obj['text'], 'lang' => $language, 'sessionId' => $session_id);

        $query = $this->db->query("select dialogflow_token,disable from w_dialogflows where phone='8562092175213'");
        $record = $query->row();

//        $this->load->model('w_dialogflow_model', 'agent');
//        $dialogflowToken = $this->agent->get(1);

//        $access_token='e5e28e0f35b845378ada5ef3edb49e57';  //dialogflowToken
        //'';//
        if ($record->disable)  //bot disable
            return;
        $access_token = $record->dialogflow_token;

        $response = json_decode($this->postCURL($url, $data, $access_token), true);
        $fulfillment = $response['result']['fulfillment'];
        $messages = $fulfillment['messages'];
        $res = "";
        for ($x = 0; $x < count($messages); $x++) {
            $message = $messages[$x];
            if ($message['type'] == 0) {
                $res = $res . $message['speech'];
                $obj['text'] = $message['speech'];
                $this->send_message($obj);
                //        $this->response($obj);
            }
        }
//        $this->response($obj);
    }

    public function wp_post()
    {
        $send = $this->post();
        $query = $this->db->query("SELECT * FROM w_message_steps WHERE step=" . $send['step']);
        $steps = $query->row();

        $send['token'] = $this->wabox_token;
        $send['uid'] = $this->wa_phone;
        $send['custom_uid'] = time();
        $send['text'] = str_replace('$username', $send['username'], $steps->message);
        $send['text'] = str_replace('$price', $send['price'], $send['text']);
        $res = $this->send_message($send);
//        sleep(5);
//        $send['custom_uid']=time()."10";
//        $send['text']=str_replace('$username',$send['username'],$steps[1]->message);
//        $res=$this->send_message($send);

//        $wpDb = $this->load->database('lamoga', TRUE);
//        $data=array('step'=>3);


        $this->response($res);
    }

    public function getWpUser($phone)
    {
        $phone = substr($phone, 4);
        $wpDb = $this->load->database('lamoga', TRUE);
        $query = $wpDb->select('ID,user_login')->like('telefon_mobil', $phone)->from('pts_useradressen'.$this->wa_portal_id)->get();
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        else{
            $log = array(
                'function' => 'getWpUser',
                'message' => $phone.' not found telefon_mobil or rufnummer_3 in pts_useradressen'
            );
            $this->db->insert('error_logs', $log);
        }
        return null;
    }

    public function getWpConsultant($phone)
    {
        $phone = substr($phone, 4);
        $wpDb = $this->load->database('lamoga', TRUE);
        $query = $wpDb->select('ID,bezeichnung')->like('mobilenumber_1', $phone)->from('pts_berater_profile'.$this->wa_portal_id)->get();
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        else{
            $log = array(
                'function' => 'getWpConsultant',
                'message' => $phone.' not found mobilenumber_1 in pts_berater_profile'
            );
            $this->db->insert('error_logs', $log);
        }
        return null;
    }

    public function getCustomerPhone($phone)  //from consultant to customer
    {
        $wpDb = $this->load->database('lamoga', TRUE);
        $query = $wpDb->select('id,customer_phone,sbid,consultant_id as sender_id,user_id as receiver_id')->like('consultant_phone', $phone)->from('LAMOGA_WAF_request'.$this->wa_portal_id)->get();

        if ($query->num_rows() > 0) {
            $res = $query->row();
            $res->phone = $res->customer_phone;
            return $res;
        }
        return null;
    }

    public function getConsultantPhone($phone)
    {
        $wpDb = $this->load->database('lamoga', TRUE);
        $query = $wpDb->select('id,consultant_phone,sbid,user_id as sender_id,consultant_id as receiver_id')->like('customer_phone', $phone)->from('LAMOGA_WAF_request'.$this->wa_portal_id)->get();
        if ($query->num_rows() > 0) {
            $res = $query->row();
            $res->phone = $res->consultant_phone;
            return $res;
        }
        return null;
    }

    public function sendLhc_post()
    {
        $send = $this->post();
        $query = $this->db->get_where('lh_chat', array('id' => $send['chat_id']));
        $res = $query->row();
        $send['token'] = $res->remarks;
        $send['uid'] = $res->hash;
        $send['to'] = $res->session_referrer;
        $send['custom_uid'] = time();

        $res = $this->send_message($send);
        $this->response($res);
    }

    //https://www.lomago.io/whatsapp/api/users/send
    // for Gerd  from webchat
    public function send_post()
    {
        $data = $this->post();
        $service_id=$data['service_id'];
        $profile_ids=explode("-",$data['profil_id']);
        $consultant_id=$profile_ids[0];
        $send=array();
        $send['text'] = $data['text'];

        $wpDb = $this->load->database('lamoga', TRUE);
        $query = $wpDb->select('value')->where('name', 'send_consultant_name')->from('settings')->get();
        $res = $query->row();
        if ($res->value=='true'){
            $query = $wpDb->select('consultant_name')->where('consultant_id', $consultant_id)->from('LAMOGA_WAF_request'.$this->wa_portal_id)->get();
            $res = $query->row();
            $send['text']=$res->consultant_name.': '.$send['text'];
        }

        if ($service_id=='WA'){
            $send['event'] = 'whatsapp';
            $send['token'] = $this->wabox_token;
            $send['uid'] = $this->wa_phone;
            $send['to'] = $data['phone'];
            $send['custom_uid'] = time();
            $res = $this->send_message($send);

            unset($send['to']);
            unset($send['custom_uid']);
        }
        else {
            if ($service_id=='FB')
                $type="facebook";
            else
                $type="telegram";
            $url = 'https://www.lomago.io:1337/send?';
            $url = $url . "page_id=" . $data['phone'] . "&text=" . urlencode($data['text']) . "&type=".$type;
            $log = array(
                'function' => 'send_post',
                'type' => 'data',
                'event' => $type,
                'message' => json_encode($data)
            );
            $this->db->insert('error_logs', $log);
            $log['message']=$url;
            $this->db->insert('error_logs', $log);
            $res = json_decode($this->getCURL($url), true);
            $send['event'] = $type;
        }
        $send['sender_id']=$consultant_id;
        $send['receiver_id']=$data['client_id'];

        $send['time'] = date('Y-m-d H:i:s', time());
        $this->load->model('w_receive_message_model', 'receive_model');
        $this->receive_model->insert($send);

        $this->response($res);
    }

//https://www.lomago.io/whatsapp/api/users/sendMessage
    public function sendMessage_post()  //sender_id, text ,receiver_id
    {
        $send = $this->post();
        $wpDb = $this->load->database('lamoga', TRUE);
        $query = $wpDb->select('customer_phone,status')->where('user_id', $send['receiver_id'])->from('LAMOGA_WAF_request'.$this->wa_portal_id)->get();
        $res = $query->row();
        $send['event'] = 'whatsapp';
        $send['token'] = $this->wabox_token;
        $send['uid'] = $this->wa_phone;
        $send['to'] = $res->customer_phone;
        $send['custom_uid'] = time();
        $res = $this->send_message($send);

        $data = array(
            'to' => $send['to'],
            'dir' => 'o',
            'text' => $send['text']
        );

        $phone = substr($send['to'], 2);

        unset($send['to']);
        unset($send['custom_uid']);
        unset($send['page_id']);
        unset($send['type']);

        $send['time'] = date('Y-m-d H:i:s', time());
        $this->load->model('w_receive_message_model', 'receive_model');
        $this->receive_model->insert($send);


        $LAMOGA_WAF = $this->getCustomerPhone($phone);
        if ($LAMOGA_WAF == null)
            $LAMOGA_WAF = $this->getConsultantPhone($phone);
        if ($LAMOGA_WAF != null) {
            $data['to'] = $LAMOGA_WAF->phone;
            $res = $this->SendBillingServerWA($data, $LAMOGA_WAF);
        }

        $this->response($res);
    }

    public function SendBillingServerWA($send, $LAMOGA_WAF)
    {
        $user = $this->getWpUser($send['to']);
        $response = "Error";
        if ($user == null) {
            $user = $this->getWpConsultant($send['to']);
        }
        if ($user != null) {
            $send['type']="WA";
            $send['sender_id']=$user->ID;
            return $this->SendBillingServer($send, $LAMOGA_WAF);
        } else{
            $log = array(
                'function' => 'SendBillingServerWA',
                'message' => $send['to'] . ' not find ' . json_encode($send)
            );
            $this->db->insert('error_logs', $log);
        }
        return ($response);
    }

    public function SendBillingServer($send, $LAMOGA_WAF)
    {
        $url = 'https://www.audiotex-0900.de/whatsapp/whatsapp_send.php/?';
        $data = array(
            'phone' => $send['to'],
            'direction' => $send['dir'],
            'text' => urlencode($send['text'])
        , 'price' => 120, 'url' => 'lamoga.de', 'client_id' => $send['sender_id']
        , 'auth' => "3d7fhezeTuZfkiedopdWq-12\$S", 'hash' => md5("lamoga.de" . $send['to'])
        , 'timestamp' => time()
        , 'dialog_id' => $LAMOGA_WAF->id, 'profil_id' => $LAMOGA_WAF->sbid
        , 'service_id' => $send['type']
        );
        $log = array(
            'function' => 'SendBillingServer post',
            'type'=>'data',
            'message' => json_encode($send),
            'event'=>$send['type']
        );
        $this->db->insert('error_logs', $log);
        $response = json_decode($this->postCURL($url, $data));
        $log = array(
            'function' => 'SendBillingServer response',
            'type'=>'data',
            'message' => json_encode($response),
            'event'=>$send['type']
        );
        $this->db->insert('error_logs', $log);
        return ($response);
    }

//sender_id:sender_id,
//receiver_id:receiver_id,
//text: message
    public function billingServer_post()  //from facebook,telegram of node.js
    {
        $send = $this->post();
        $LAMOGA_WAF = $this->getLAMOGA_WAF($send);
        $send['type']=($send['type']=='facebook')?"FB":"TG";

        $send['to']=$send['receiver_id'];
        $send['dir']=($send['sender_id']==$LAMOGA_WAF->user_id)?'i':'o';
        $res = $this->SendBillingServer($send, $LAMOGA_WAF);
        $this->response($res);
    }

    public function getLAMOGA_WAF($send)  //to get facebook info
    {
        $wpDb = $this->load->database('lamoga', TRUE);
        $where='(user_id='.$send['sender_id'].' and consultant_id='.$send['receiver_id'].') or (user_id='.$send['receiver_id'].' and consultant_id='.$send['sender_id'].')';
        $query = $wpDb->select('id,user_id,sbid')->where($where)->from('LAMOGA_WAF_request'.$this->wa_portal_id)->get();

        if ($query->num_rows() > 0) {
            $res = $query->row();
            return $res;
        }
        return null;
    }

    public function send_message($send)
    {
        $base_url = 'https://www.waboxapp.com/api/send/chat?';
        $url = $base_url . "token=" . $send['token'] . "&uid=" . $send['uid'] . "&to=" . $send['to'] . "&custom_uid=" . $send['custom_uid'] . "&text=" . urlencode($send['text']);
        $response = json_decode($this->getCURL($url), true);
//        $this->response($response);
//        $this->response($url);
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

    public function dialogflow_post()
    {
        $message = $this->post();
        $this->dialogflow($message);
    }
}
