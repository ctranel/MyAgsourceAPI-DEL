<?php

/**
 *
 * @author ctranel
 *        
 */
class Lactations_model extends CI_Model {
	protected $db_group_name; //name of database group
	protected $tables; //array of tables configured in ion_auth config file
	
	/**
	 */
	function __construct() {
		parent::__construct();
		$this->db_group_name = 'default';
		$this->{$this->db_group_name} = $this->load->database($this->db_group_name, TRUE);
	}
	
	public function getLactationsArray($herd_code, $serial_num){
		$arr_ret = $this->{$this->db_group_name}
		->select("lact_num, CONCAT(calving_age_yy, '-', calving_age_mm) AS age, FORMAT(calving_date, 'd') AS fresh_date, ltd_dim, ltd_milk_lbs, ltd_fat_lbs, ltd_pro_lbs, first_bred_dim, days_open, calving_int_days, avg_linear_score, d305_milk_lbs, d305_fat_lbs, d305_pro_lbs, me_milk_lbs, me_fat_lbs, me_pro_lbs, d365_milk_lbs, d365_fat_lbs, d365_pro_lbs, letter_grade_milk, letter_grade_fat, letter_grade_pro")
		->where('herd_code', $herd_code)
		->where('serial_num', $serial_num)
		->get('animal.dbo.vma_Cow_Lookup_Lactations')
		->result_array();
		
		if(is_array($arr_ret)){
			return $arr_ret;
		}
		return false;
	}
	
	public function getOffspringArray($herd_code, $serial_num){
		$arr_ret = $this->{$this->db_group_name}
		->select("calf_control_num, FORMAT(calving_date, 'd') AS calving_date, calf_name, calf_visible_id, sex_desc, twin_code, calving_ease_code, calf_sire_naab, calf_sire_name")
		->where('herd_code', $herd_code)
		->where('serial_num', $serial_num)
		->get('animal.dbo.vma_Cow_Lookup_Calving')
		->result_array();
		
		if(is_array($arr_ret)){
			return $arr_ret;
		}
		return false;
	}
}

?>