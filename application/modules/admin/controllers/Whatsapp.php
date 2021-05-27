<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Whatsapp extends Admin_Controller {

	public function index()
	{
		redirect('whatsapp/agent');
	}

    public function setting()
    {
        $this->load->library('system_message');

        $wpDb = $this->load->database('lamoga', TRUE);
        $query = $wpDb->select('value')->where('name', 'send_consultant_name')->from('settings')->get();
        $setting=$query->row();

        if ($this->input->get('value')!=null){
            $setting->value=$this->input->get('value');
            $wpDb->where('name', 'send_consultant_name');
            $wpDb->update('settings', array('value'=>$setting->value));
        }
        if ($setting->value=='true'){
            $this->session->set_flashdata('success', 'Send Consultant Name was Enabled.');
        }
        else{
            $this->session->set_flashdata('success', '');
            $this->session->set_flashdata('info', 'Send Consultant Name was Disabled.');
        }
        $this->mViewData = array(
            'setting'=>$setting->value
        );
        $this->render('setting');
    }

    public function agent()
    {
        $crud = $this->generate_crud('w_dialogflows');
        $this->mPageTitle = 'Dialogflow Agent';
        $this->render_crud();
    }

    public function step()
    {
        $crud = $this->generate_crud('w_message_steps');
        $crud->unset_texteditor('message');
        $this->mPageTitle = 'Whatsapp Bot Message Steps';
        $this->render_crud();
    }

    public function received()
    {
        $crud = $this->generate_crud('w_receive_messages');
        $crud->set_subject("Chat History");
        $crud->columns('event','sender_id','receiver_id','text', 'phone', 'time');
        $crud->display_as('text','Message Content');

        $crud->display_as('sender_id','Sender');
        $crud->display_as('receiver_id','Receiver');
        $crud->callback_column('sender_id',array($this,'customer_callback'));
        $crud->callback_column('receiver_id',array($this,'customer_callback'));
        $crud->callback_column('event',array($this,'type_callback'));

        $crud->order_by('id','desc');
        $crud->unset_add();
        $crud->unset_delete();
        $crud->unset_edit();
        $this->mPageTitle = 'Chat History';
        $this->render_crud();
    }

    function customer_callback($value, $row)
    {
        $wpDb = $this->load->database('lamoga', TRUE);
        $query = $wpDb->select('user_login')->where('ID', $value)->from('pts_useradressen'.$this->wa_portal_id)->get();
        if ($query->num_rows() > 0) {
            $customer=$query->row();
            return "<b>".$customer->user_login."</b>";
        }
    }

    function type_callback($value, $row)
    {
        return "<img src=\"".image_url($value).".png"."\" width='25' /> ".$value;
    }

    public function agent1()
    {
        $crud = $this->generate_crud('blog_posts');
        $crud->columns('author_id', 'category_id', 'title', 'image_url', 'tags', 'publish_time', 'status');
        $crud->set_field_upload('image_url', UPLOAD_BLOG_POST);
        $crud->set_relation('category_id', 'blog_categories', 'title');
        $crud->set_relation_n_n('tags', 'blog_posts_tags', 'blog_tags', 'post_id', 'tag_id', 'title');

        $state = $crud->getState();
        if ($state==='add')
        {
            $crud->field_type('author_id', 'hidden', $this->mUser->id);
            $this->unset_crud_fields('status');
        }
        else
        {
            $crud->set_relation('author_id', 'admin_users', '{first_name} {last_name}');
        }

        $this->mPageTitle = 'Blog Posts';
        $this->render_crud();
    }

    // Grocery CRUD - Blog Categories
	public function category()
	{
		$crud = $this->generate_crud('blog_categories');
		$crud->columns('title');
		$this->mPageTitle = 'Blog Categories';
		$this->mViewData['crud_note'] = modules::run('adminlte/widget/btn', 'Sort Order', 'blog/category_sortable');
		$this->render_crud();
	}
	
	// Sortable - Blog Categories
	public function category_sortable()
	{
		$this->load->library('sortable');
		$this->sortable->init('blog_category_model');
		$this->mViewData['content'] = $this->sortable->render('{title}', 'blog/category');
		$this->mPageTitle = 'Blog Categories';
		$this->render('general');
	}

	// Grocery CRUD - Blog Tags
	public function tag()
	{
		$crud = $this->generate_crud('blog_tags');
		$this->mPageTitle = 'Blog Tags';
		$this->render_crud();
	}
}
