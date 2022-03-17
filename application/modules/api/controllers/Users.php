<?php
/** @noinspection SqlNoDataSourceInspection */
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__) . "/Whatsapp.php");
/**
 * Demo Controller with Swagger annotations
 * Reference: https://github.com/zircote/swagger-php/
 */
class Users extends Whatsapp
{

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
//        log_message('error', json_encode($payload));
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
        // At this point everything is fine, the challenge was set, the secret header was checked and you can process the message.
        //at this point everything is fine,
        if (isset($payload['payload'])) {
            $data = [];
            $data['phone'] = $payload['sender'];
            $payload=$payload['payload'];
            $data['sender_name'] = $payload['user']['name'];
            $data['time'] = date('Y-m-d H:i:s', $payload['timestamp']);
            $data['dir'] = 'i';
            $data['text'] = $payload['text'];
            $this->processWhatsappMessage($data);
        }
        $this->response($response);
    }

    function processWhatsappMessage($data) {
        $phone = substr($data['phone'], 2);
//                $LAMOGA_WAF = $this->getCustomerPhone($phone);  //from consultant to customer
//                if ($LAMOGA_WAF == null)
        $LAMOGA_WAF = $this->getConsultantPhone($phone);
        if ($LAMOGA_WAF != null) {
            $send = [];
//            $send['token'] = $data['token'];
            $send['to'] = $LAMOGA_WAF->phone;
            $send['custom_uid'] = time();
            $send['uid'] = $this->wa_phone;
            $send['text'] = $data['text'];
            $send['name'] = $data['sender_name'];
            $send['time'] = $data['time'];
            $send['dir'] = $data['dir'];
//                    $res = $this->send_message($send);  // not sending to Consultant whatsapp
            if ($LAMOGA_WAF->status==1) //connected
                $res = $this->SendBillingServerWA($send, $LAMOGA_WAF);
            else if ($LAMOGA_WAF->status==0){ //waiting{
                $wpDb = $this->load->database('lamoga', TRUE);
                $query = $wpDb->select('text')->where('name', 'whatsapp_first_reply')->from('auto_messages'.$this->wa_portal_id)->get();
                $replayText = $query->row()->text;
                $replayText = str_replace('$customer', $data['sender_name'], $replayText);
                $this->sendWhatsappMessage_get($replayText,$data['phone']);
            }

            $data['sender_id'] = $LAMOGA_WAF->sender_id;
            $data['receiver_id'] = $LAMOGA_WAF->receiver_id;
        }
        $data['event'] = 'whatsapp';
        $this->load->model('w_receive_message_model', 'receive_model');
        $this->receive_model->insert($data);
        log_message('error', json_encode($res));
        return $res;
    }

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
//            $this->UpdateLHCDb($send);
            $res = "error";
            if ($message['dir'] == 'i') {  //input
//                $this->dialogflow($send);
                $res = $this->processWhatsappMessage($data);
            }
            $this->response($res);
        }
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
        if (isset($send['price']))
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
        $query = $wpDb->select('id,consultant_phone as phone,sbid,user_id as sender_id,consultant_id as receiver_id,status')->like('customer_phone', $phone)->from('LAMOGA_WAF_request'.$this->wa_portal_id)->get();
        if ($query->num_rows() > 0) {
            return $query->row();
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
}
