<?php
namespace myagsource\Report\Content;

require_once APPPATH . 'libraries/Datasource/DbObjects/DbField.php';

use \myagsource\Datasource\DbObjects\DbField;
use myagsource\Supplemental\Content\Supplemental;

/**
 * Name:  BlockField
 *
 * Author: ctranel
 *
 * Created:  02-03-2015
 *
 * Description:  Metadata typically associated with data storage for data fields..
 *
 */
abstract class BlockField {
	/**
	 * id
	 * @var int
	 **/
	protected $id;
	
	/**
	 * data_field
	 * @var iDataField
	 **/
	protected $data_field;

	/**
	 * block name
	 * @var string
	 **/
	protected $name;
		
	/**
	 * display_format
	 * @var string
	 **/
	protected $display_format;
		
	/**
	 * aggregate
	 * @var string
	 **/
	protected $aggregate;
		
	/**
	 * is_sortable
	 * @var boolean
	 **/
	protected $is_sortable;
		
	/**
	 * displayed
	 * @var boolean
	 **/
	protected $is_displayed;
	
	/**
	 * header_supp
	 * @var Supplemental
	 **/
	protected $header_supp;
	
	/**
	 * data_supp
	 * @var Supplemental
	 **/
	protected $data_supp;
	
	
	/**
	 */
	public function __construct($id, $name, DbField $data_field, $is_displayed, $display_format, $aggregate, $is_sortable, $header_supp, $data_supp) {
		$this->id = $id;
		$this->name = $name;
		$this->data_field = $data_field;
		$this->is_displayed = $is_displayed;
		$this->display_format = $display_format;
		$this->aggregate = $aggregate;
		$this->is_sortable = $is_sortable;
		$this->header_supp = $header_supp;
		$this->data_supp = $data_supp;
	}
	
	/* debugging
	public function id() {
		return $this->id;
	}
  */
	public function dbFieldName() {
		return $this->data_field->dbFieldName();
	}

	public function displayName() {
		return $this->name;
	}

	public function decimalScale() {
		return $this->data_field->decimalScale();
	}

	public function isSortable() {
		return $this->is_sortable;
	}

	public function isDisplayed() {
		return $this->is_displayed;
	}

	public function isNumeric() {
		return $this->data_field->isNumeric();
	}

	public function isAggregate() {
		return (isset($this->aggregate) && !empty($this->aggregate));
	}

	public function defaultSortOrder() {
		return $this->data_field->defaultSortOrder();
	}
	
	public function unitOfMeasure() {
		return $this->data_field->unitOfMeasure();
	}
	
	public function dbTableName() {
		return $this->data_field->dbTableName();
	}
	
	public function pdfWidth() {
		return $this->data_field->pdfWidth();
	}

	public function isNaturalSort() {
		return $this->data_field->isNaturalSort();
	}

	public function dataSupplemental() {
		if(isset($this->data_supp)){
			return $this->data_supp->getContent();
		}
	}

	public function headerSupplemental() {
		if(isset($this->header_supp)){
			return $this->header_supp;
		}
	}

	public function sort() {
		return null;
	}

	public function selectFieldText() {
		if(isset($this->display_format) && !empty($this->display_format)){
			return "FORMAT(" . $this->data_field->dbTableName() . "." . $this->data_field->dbFieldName() . ", '" . $this->display_format . "', 'en-US') AS " . $this->data_field->dbFieldName();
		}
		if(isset($this->aggregate) && !empty($this->aggregate)){
			$alias_field_name = strtolower($this->aggregate) . '_' . $this->data_field->dbFieldName();
			$ret_val = $this->aggregate . '(' . $this->data_field->dbTableName() . '.' . $this->data_field->dbFieldName() . ') AS ' . $alias_field_name;
			$this->data_field->setDbFieldName($alias_field_name);
			return $ret_val;
		}
		return $this->data_field->dbFieldName();
	}
}

?>