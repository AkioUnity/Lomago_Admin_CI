<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Service extends Admin_Controller {

    public function index()
    {
        $crud = $this->generate_crud('services');
//        $crud->columns('text', 'phone', 'sender_name', 'dir', 'time','uid');
//        $crud->display_as('text','Message Content')->display_as( 'dir','Direction');
//        $crud->order_by('id','desc');
//        $crud->unset_add();
        $crud->callback_column('whatsapp',array($this,'_callback_status'));
        $crud->callback_column('facebook',array($this,'_callback_status'));
        $crud->callback_column('telegram',array($this,'_callback_status'));
        $this->mPageTitle = 'Service List';
        $this->render_crud();
    }

    public function _callback_status($value, $row)
    {
        if ($value)
            return "<a href='".site_url('admin/service/index/edit/'.$row->id)."' style='color:#7ac943' >Active</a>";
        else
            return "<a href='".site_url('admin/service/index/edit/'.$row->id)."' style='color:#ff7bac' >InActive</a>";
    }
}
