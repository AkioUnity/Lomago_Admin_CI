<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auto extends Admin_Controller {

    public function text($type)
    {
        $wpDb = $this->load->database('lamoga', TRUE);
        if (!empty($this->input->post())){
//            print_r($this->input->post());
            $text=$this->input->post('text');
            $ids=$this->input->post('id');
            foreach ($text as $key=>$element){
//                echo $key.' '.$element;
                $wpDb->where('id', $ids[$key]);
                $wpDb->update('auto_messages'.$this->wa_portal_id, array('text'=>$element));
            }
        }
        $query = $wpDb->select('*')->where('type', $type)->from('auto_messages'.$this->wa_portal_id)->get();
        $this->mViewData['results'] = $query->result();
        $this->mPageTitle = $type.' block message';
        $this->mViewData['type'] = $type;
        $this->render('lamoga/block_text');
    }
}
