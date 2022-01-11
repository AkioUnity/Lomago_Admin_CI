<?php
/** @noinspection SqlNoDataSourceInspection */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Demo Controller with Swagger annotations
 * Demo Controller with Swagger annotations
 * yes. i will do the tesint myself and send you over what i see
 * yes. I will do the testing myself and send you over what i see
 * Reference: https://github.com/zircote/swagger-php/
 */
class Wp extends API_Controller
{
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
