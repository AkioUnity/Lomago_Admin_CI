<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ChatBot extends Admin_Controller {

	public function index()
	{
		redirect('chatBot/chatbot_history');
	}

    public function chatbot_history()
    {
        $crud = $this->generate_crud('lh_msg');
        $crud->set_subject("Chat Bot History");
//        $crud->columns('msg','name_support','time','chat_id','referrer');
        $crud->columns('msg','name_support','time','chat_id');
//        $crud->set_relation('chat_id','lh_chat','remarks');
        $crud->set_relation('chat_id','lh_chat','referrer');
        $crud->display_as('msg','Message');
        $crud->display_as('chat_id','Dialogflow ID');
        $crud->display_as('chat_id','Refer URL');
//        $crud->where('chat_id is NOT NULL',NULL,false);

        $crud->callback_column('time',array($this,'time_callback'));
        $crud->callback_column('referrer',array($this,'referrer_callback'));
//        $crud->callback_column('event',array($this,'type_callback'));

        $crud->order_by('id','desc');
        $crud->unset_add();
        $crud->unset_delete();
        $crud->unset_edit();
        $this->mPageTitle = 'Chat Bot History';
        $this->render_crud();
    }

    function time_callback($value, $row)
    {
        return date("Y-m-d H:i:s", $value);
    }

    function referrer_callback($value, $row)
    {
        return date("Y-m-d H:i:s", $value);
    }
}
