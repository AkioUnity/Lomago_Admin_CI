<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Modal extends API_Controller {
//   https://admin.lomago.io/api/modal
	public function index_get()
	{
        $data=$this->db->get_where('modals', array('token' => '1234'))->row();
		$this->response($data);
	}
//   https://admin.lomago.io/api/modal/open
    public function open_get()
    {
        $this->db->update('modals', array("status" => 'opened'));
        $this->response("OK");
    }
}
