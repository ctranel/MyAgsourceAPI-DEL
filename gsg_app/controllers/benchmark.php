<?php
require_once(APPPATH . 'libraries/Benchmarks/Benchmarks.php');

use \myagsource\Benchmarks\Benchmarks;


if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Benchmark extends CI_Controller {	
	function __construct(){
		parent::__construct();
		
/*		if(!isset($this->as_ion_auth)){
			redirect('auth/login', 'refresh');
		}
		if((!$this->as_ion_auth->logged_in())){
			$this->session->keep_flashdata('redirect_url');
			$redirect_url = set_redirect_url($this->uri->uri_string(), $this->session->flashdata('redirect_url'), $this->as_ion_auth->referrer);
			$this->session->set_flashdata('redirect_url', $redirect_url);
			if(strpos($this->session->flashdata('message'), 'Please log in.') === FALSE){
				$this->session->set_flashdata('message',  $this->session->flashdata('message') . 'Please log in.');
			}
			else{
				$this->session->keep_flashdata('message');
			}
			redirect(site_url('auth/login'));
		} */
	}

/**
 * @method index()
 * 
 * @description updates benchmark session data and optionally modifies benchmark defaults
 * 
 * @param string serialized form parameters 
 * @access	public
 * @return	void
 * @todo: sending confirmation to client???
 */
	function ajax_set($ser_form_data){
		//for ajax pages
		$this->session->keep_flashdata('message');
		$this->session->keep_flashdata('redirect_url');
		//make sure previous page remains as the redirect url
		$redirect_url = set_redirect_url($this->uri->uri_string(), $this->session->flashdata('redirect_url'), $this->as_ion_auth->referrer);
		$this->session->set_flashdata('redirect_url', $redirect_url);
		
		if((!isset($this->as_ion_auth) || !$this->as_ion_auth->logged_in()) && $this->session->userdata('herd_code') != $this->config->item('default_herd')){
			$this->load->view('session_expired', array('url'=>$this->session->flashdata('redirect_url')));
			exit;
		}
		
		//do we have any data?
		if(!isset($ser_form_data)){
			return false;
		}
		
		//HANDLE DATA
		$arr_params = json_decode(urldecode($ser_form_data), true);
		//verify csrf
		if(isset($arr_params['csrf_test_name']) && $arr_params['csrf_test_name'] != $this->security->get_csrf_hash()) die("I don't recognize your browser session, your session may have expired, or you may have cookies turned off.");
		unset($arr_params['csrf_test_name']);

		$this->load->model('setting_model');
		$make_default = $arr_params['make_default'];
		unset($arr_params['make_default']);
		
		$formatted_form_data = Benchmarks::parseFormData($arr_params);

		//set session benchmarks
		//$benchmarks->setSessionValues($formatted_form_data);
		$this->session->set_userdata('benchmarks', $formatted_form_data);
		
		//if set default, write to database
		if($make_default){
			$this->load->model('benchmark_model');
			$benchmarks = new Benchmarks($this->session->userdata('user_id'), $this->session->userdata('herd_code'), $this->herd_model->header_info($this->session->userdata('herd_code')), $this->setting_model, $this->benchmark_model, $this->session->userdata('benchmarks'));
			$benchmarks->save_as_default($formatted_form_data);
		}
			
		$this->session->keep_flashdata('redirect_url');
		exit;
	}
}
