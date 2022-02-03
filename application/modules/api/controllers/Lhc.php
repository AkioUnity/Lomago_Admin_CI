<?php
/** @noinspection SqlNoDataSourceInspection */
defined('BASEPATH') OR exit('No direct script access allowed');

class Lhc extends API_Controller
{
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

    public function dialogflow_post()
    {
        $message = $this->post();
        $this->dialogflow($message);
    }
}