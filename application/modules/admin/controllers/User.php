<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends Admin_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->library('form_builder');
	}

    public function online(){
        $crud = $this->generate_crud('fake_onlines');
        $crud->set_subject('Fake Online Customer');
        $crud->newDb=$this->load->database('lamoga', TRUE);
        $crud->field_type('is_weekend','dropdown',array('0' => 'No', '1' => 'Yes'));
        $crud->field_type('start_time','integer'); //same result
//        $crud->field_type('end_time','datetime');
//        $crud->columns('type','user_id','consultant_name','requested_time', 'status', 'step', 'consultant_phone', 'customer_phone');
//        $crud->display_as('consultant_name','Consultant');
//        $crud->display_as('consultant_phone','Consultant phone or PIN code');
//        $crud->display_as('user_id','Customer');

//        $crud->add_action('Commissions', '', 'admin/commission/export', 'fa fa-file-excel-o');
//        $crud->callback_column('user_id',array($this,'customer_callback'));
//        $crud->callback_column('type',array($this,'type_callback'));
//        $crud->callback_column('status',array($this,'status_callback'));
//        $crud->unset_add();
        $this->mPageTitle = 'Fake Online Customer Count';
        $this->render_crud();
    }

    public function connection(){
        $crud = $this->generate_crud('LAMOGA_WAF_request'.$this->wa_portal_id);
        $crud->set_subject('Connection Status');
        $crud->newDb=$this->load->database('lamoga', TRUE);
        $crud->columns('type','user_id','consultant_name','requested_time', 'status', 'step', 'consultant_phone', 'customer_phone');
        $crud->display_as('consultant_name','Consultant');
        $crud->display_as('consultant_phone','Consultant phone or PIN code');
        $crud->display_as('user_id','Customer');

//        $crud->add_action('Commissions', '', 'admin/commission/export', 'fa fa-file-excel-o');
        $crud->callback_column('user_id',array($this,'customer_callback'));
        $crud->callback_column('type',array($this,'type_callback'));
        $crud->callback_column('status',array($this,'status_callback'));
        $crud->unset_add();
        $this->mPageTitle = 'Connection between Consultant and Customers';
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

    function status_callback($value, $row)
    {
        if ($value==-1)
            return "<span class=\"label label-danger\"> Offline </span> ";
        else if ($value==0)
            return "<span class=\"label label-warning\"> Request </span> ";
        else
            return "<span class=\"label label-success\"> Connected </span> ";
    }


    public function customers(){
        $crud = $this->generate_crud('pts_useradressen');
        $crud->newDb=$this->load->database('lamoga_hack', TRUE);
        $crud->columns('ID','user_login','mentor_id', 'telefon_mobil', 'vorwahl_3', 'rufnummer_3', 'berater_status');
        $crud->add_action('Commissions', '', 'admin/commission/export', 'fa fa-file-excel-o');

        $crud->unset_add();
        $crud->unset_delete();
        $crud->unset_edit();
        $crud->unset_view();

        $this->mPageTitle = 'Customers';
        $this->render_crud();
    }


    public function consultants(){
        $crud = $this->generate_crud('pts_berater_profile');
        $crud->newDb=$this->load->database('lamoga_hack', TRUE);
        $crud->columns('bezeichnung','ID','mobilenumber_1', 'chatpreis', 'chatpreis_1', 'chatpreis_2', 'chatpreis_3');
        $crud->set_relation('ID','pts_useradressen','mentor_id');
        $crud->display_as('ID','Mentor');
        $crud->unset_add();
        $crud->unset_delete();
        $crud->unset_edit();
        $crud->unset_view();

        $this->mPageTitle = 'Consultants';
        $this->render_crud();
    }

	// Frontend User CRUD
	public function index()
	{
		$crud = $this->generate_crud('users');
		$crud->columns('groups', 'username', 'email', 'first_name', 'last_name', 'active');
		$this->unset_crud_fields('ip_address', 'last_login');

		// only webmaster and admin can change member groups
		if ($crud->getState()=='list' || $this->ion_auth->in_group(array('webmaster', 'admin')))
		{
			$crud->set_relation_n_n('groups', 'users_groups', 'groups', 'user_id', 'group_id', 'name');
		}

		// only webmaster and admin can reset user password
		if ($this->ion_auth->in_group(array('webmaster', 'admin')))
		{
			$crud->add_action('Reset Password', '', 'admin/user/reset_password', 'fa fa-repeat');
		}

		// disable direct create / delete Frontend User
		$crud->unset_add();
		$crud->unset_delete();

		$this->mPageTitle = 'Users';
		$this->render_crud();
	}

	// Create Frontend User
	public function create()
	{
		$form = $this->form_builder->create_form();

		if ($form->validate())
		{
			// passed validation
			$username = $this->input->post('username');
			$email = $this->input->post('email');
			$password = $this->input->post('password');
			$identity = empty($username) ? $email : $username;
			$additional_data = array(
				'first_name'	=> $this->input->post('first_name'),
				'last_name'		=> $this->input->post('last_name'),
			);
			$groups = $this->input->post('groups');

			// [IMPORTANT] override database tables to update Frontend Users instead of Admin Users
			$this->ion_auth_model->tables = array(
				'users'				=> 'users',
				'groups'			=> 'groups',
				'users_groups'		=> 'users_groups',
				'login_attempts'	=> 'login_attempts',
			);

			// proceed to create user
			$user_id = $this->ion_auth->register($identity, $password, $email, $additional_data, $groups);			
			if ($user_id)
			{
				// success
				$messages = $this->ion_auth->messages();
				$this->system_message->set_success($messages);

				// directly activate user
				$this->ion_auth->activate($user_id);
			}
			else
			{
				// failed
				$errors = $this->ion_auth->errors();
				$this->system_message->set_error($errors);
			}
			refresh();
		}

		// get list of Frontend user groups
		$this->load->model('group_model', 'groups');
		$this->mViewData['groups'] = $this->groups->get_all();
		$this->mPageTitle = 'Create User';

		$this->mViewData['form'] = $form;
		$this->render('user/create');
	}

	// User Groups CRUD
	public function group()
	{
		$crud = $this->generate_crud('groups');
		$this->mPageTitle = 'User Groups';
		$this->render_crud();
	}

	// Frontend User Reset Password
	public function reset_password($user_id)
	{
		// only top-level users can reset user passwords
		$this->verify_auth(array('webmaster', 'admin'));

		$form = $this->form_builder->create_form();
		if ($form->validate())
		{
			// pass validation
			$data = array('password' => $this->input->post('new_password'));
			
			// [IMPORTANT] override database tables to update Frontend Users instead of Admin Users
			$this->ion_auth_model->tables = array(
				'users'				=> 'users',
				'groups'			=> 'groups',
				'users_groups'		=> 'users_groups',
				'login_attempts'	=> 'login_attempts',
			);

			// proceed to change user password
			if ($this->ion_auth->update($user_id, $data))
			{
				$messages = $this->ion_auth->messages();
				$this->system_message->set_success($messages);
			}
			else
			{
				$errors = $this->ion_auth->errors();
				$this->system_message->set_error($errors);
			}
			refresh();
		}

		$this->load->model('user_model', 'users');
		$target = $this->users->get($user_id);
		$this->mViewData['target'] = $target;

		$this->mViewData['form'] = $form;
		$this->mPageTitle = 'Reset User Password';
		$this->render('user/reset_password');
	}
}
