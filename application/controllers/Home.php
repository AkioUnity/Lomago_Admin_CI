<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Home page
 */
class Home extends MY_Controller {

	public function index()
	{
        $this->db->update('modals', array("status" => 'closed'));
		$this->render('home', 'full_width');
	}
}
