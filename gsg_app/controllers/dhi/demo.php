<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* -----------------------------------------------------------------
 *	CLASS comments
*  @file: demo.php
*  @author: ctranel
*
*  @description: sets session herd to be the default herd and redirects to landing page when navigating to this page.
*
* -----------------------------------------------------------------
*/

class Demo extends CI_Controller {
	
	protected $arr_user_super_sections;
	protected $arr_user_sections;
	
	function index(){
		$this->load->library('herd', array('herd_code' => $this->config->item('default_herd')));
		$this->set_herd_session_data();
		redirect(site_url());
	}

	protected function set_herd_session_data(){
		$this->session->set_userdata('herd_code', $this->herd->getHerdCode());
		$this->session->set_userdata('arr_pstring', $this->herd_model->get_pstring_array($this->herd->getHerdCode(), FALSE));
		$this->session->set_userdata('herd_enroll_status_id', $this->herd->getHerdEnrollStatus($this->herd_model), $this->config->item('product_report_code'));
		$this->session->set_userdata('recent_test_date', $this->herd->getRecentTest($this->herd_model));
	}
}
