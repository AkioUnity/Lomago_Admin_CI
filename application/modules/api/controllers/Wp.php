<?php
/** @noinspection SqlNoDataSourceInspection */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Demo Controller with Swagger annotations
 * Reference: https://github.com/zircote/swagger-php/
 */
class Wp extends API_Controller
{
    public $wabox_token = '51ed0669bea9c01cf3cf2144cd0049975c7a994025fa9';
    public $agent_token = 'a8c3a69806144c45b100f0fd0f06f232';
    public $wa_phone = '8562092175213';

    public function check_get()
    {
        $domain = parse_url($_SERVER['HTTP_HOST']);
//        $domain0 = parse_url($_SERVER['SERVER_NAME']);
        $this->load->model('service_model', 'services');
        $service=$this->services->get_by('domain','lomago.de');
        if ($service){
            $this->response($service);
        }
        else{
            $this->response($domain);
        }

    }
}
