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
		$section_path = $this->router->fetch_class(); //this should match the name of this file (minus ".php".  Also used as base for css and js file names and model directory name
		if($this->uri->segment(1) != $section_path){
			$super_section_id = $this->ion_auth_model->get_super_section_id_by_path($this->uri->segment(1));
		}
		
		$this->session->set_userdata('herd_code', $this->config->item('default_herd', 'ion_auth'));
		$this->session->set_userdata('arr_pstring', $this->herd_model->get_pstring_array($this->config->item('default_herd', 'ion_auth'), FALSE));
		//$this->session->set_userdata('active_group_id', 2);
		$arr_scope = array('subscription','public','unmanaged');
		//the first parameter is group id--use producer (2) if no one is logged in, second param is user id. 
		$this->arr_user_super_sections = $this->as_ion_auth->get_super_sections_array(2, $this->session->userdata('user_id'), $this->session->userdata('herd_code'), $arr_scope);
		$this->arr_user_sections = $this->as_ion_auth->get_sections_array(2, $this->session->userdata('user_id'), $this->session->userdata('herd_code'), $super_section_id, $arr_scope);
		redirect(site_url());
	}
}
