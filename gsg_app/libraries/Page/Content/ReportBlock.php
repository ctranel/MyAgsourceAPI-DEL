<?php
namespace myagsource\Page\Content;

require_once APPPATH . 'libraries/Site/iBlock.php';
require_once APPPATH . 'libraries/Page/iReportBlock.php';
require_once APPPATH . 'libraries/Page/Content/Sort.php';
require_once APPPATH . 'libraries/Page/Content/WhereGroup.php';
require_once APPPATH . 'libraries/Datasource/DbObjects/DbField.php';

use \myagsource\Page\iReportBlock;
use \myagsource\Datasource\DbObjects\DbField;
use \myagsource\Datasource\DbObjects\DbTableFactory;
use \myagsource\DataHandler;
use \myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\Datasource\iDataField;
use \myagsource\Filters\ReportFilters;
use \myagsource\Site\WebContent\Block as SiteBlock;

/**
* Name:  Block
*
* Author: ctranel
*  
* Created:  02-02-2015
*
* Description:  Contains properties and methods specific to displaying blocks of the website.
*
*/

abstract class ReportBlock implements iReportBlock {
    /**
     * SiteBlock object which contains properties and methods related to the block context within the site
     * @var SiteBlock
     **/
    protected $site_block;

    /**
	 * array of ReportField objects
	 * @var ReportField[]
	 **/
	protected $report_fields;
	
	/**
	 * array of field names to be added to the field objects
	 * @var array of strings
	 **/
	protected $addl_select_field_names;

	/**
	 * array of WhereGroup objects
	 * @var WhereGroup[]
	 **/
	protected $where_groups;
	
	/**
	 * array of Sort objects
	 * @var Sort[]
	 **/
	protected $default_sorts;
	
	/**
	 * array of Sort objects
	 * @var Sort[]
	 **/
	protected $sorts;
	
	/**
	 * iDataField object
	 * @var iDataField
	 **/
	protected $pivot_field;
	
	/**
	 * max_rows
	 * @var int
	 **/
	protected $max_rows;

	/**
	 * cnt_row
	 * @var boolean
	 **/
	protected $cnt_row;
	
	/**
	 * sum_row
	 * @var boolean
	 **/
	protected $sum_row;
	
		/**
	 * avg_row
	 * @var boolean
	 **/
	protected $avg_row;
	
	/**
	 * bench_row
	 * @var boolean
	 **/
	protected $bench_row;
	
	/**
	 * is_summary
	 * @var boolean
	 **/
	protected $is_summary;
	
	/**
	 * has_aggregate
	 * @var boolean
	 **/
	protected $has_aggregate;
	
	/**
	 * filters
	 * 
	 * @var ReportFilters
	 **/
	protected $filters;
	
	/**
	 * primary_table_name
	 * @var string
	 **/
	protected $primary_table_name;
	
	/**
	 * joins
	 * @var Joins
	 **/
	protected $joins;
	
	/**
	 * supp_param_fieldnames
	 * @var array
	 **/
	protected $supp_param_fieldnames;
	
	/**
	 * field_groups
	 * @var numerically keyed array
	 **/
	protected $field_groups;

    /**
     * dataset
     * @var array
     **/
    protected $dataset;

    /**
     * data_handler
     * @var DataHandler
     **/
   protected $data_handler;

    /**
     * supplemental
     * @var Supplemental
     **/
    protected $supplemental;

    /**
     * supplemental_factory
     * @var SupplementalFactory
     **/
    protected $supplemental_factory;

    /**
     * db_table_factory
     * @var DbTableFactory
     **/
    protected $db_table_factory;

    //The count of rows appended to the end of a dataset's rows; the number of these rows: sum_row,avg_row,cnt_row,bench_row
	protected $appended_rows_count;
	
/**
	 * __construct
	 *
	 * @return void
	 * @author ctranel
	 **/
	public function __construct($block_datasource, SiteBlock $site_block, $max_rows, $cnt_row, $sum_row, $avg_row, $bench_row,
            $is_summary, $display_type, ReportFilters $filters, SupplementalFactory $supp_factory, DataHandler $data_handler, DbTableFactory $db_table_factory, $field_groups = null) {//$id, $page_id, $name, $description, $scope, $active, $path, 
		$this->datasource = $block_datasource;
        $this->site_block = $site_block;
		$this->max_rows = $max_rows;
		$this->cnt_row = $cnt_row;
		$this->sum_row = $sum_row;
		$this->avg_row = $avg_row;
		$this->bench_row = $bench_row;
		$this->is_summary = $is_summary;
		$this->field_groups = $field_groups;
		//$this->group_by_fields = $group_by_fields;
		//$this->where_fields = $group_by_fields;
		$this->display_type = $display_type;
        $this->filters = $filters;
        $this->data_handler = $data_handler;
        $this->db_table_factory = $db_table_factory;

        /*
         * myagsource special case: if PAGE filters or params contain only a pstring of 0, and the block is not a summary
        * Needed for pages that contain both cow level and summary reports.
         * @todo: should extend base class with MyAgSource-specific class
        */

        if($this->filters->criteriaExists('pstring') && !$this->isSummary()){
            $p_value = $this->filters->getCriteriaValueByKey('pstring');
            if(count($p_value) === 1 && $p_value[0] === 0){
                $this->filters->removeCriteria('pstring');
            }
        }
        /* end special case */

        $this->supplemental_factory = $supp_factory;
        $this->supplemental = $supp_factory->getBlockSupplemental($this->site_block->id());
		$this->supp_param_fieldnames = [];
		
		//load data for remaining fields
		$this->setWhereGroups();
		$this->setDefaultSort();
		//@todo: joins

        $this->appended_rows_count = 0;
		if ($cnt_row) {
		    $this->appended_rows_count++;
		}
		if ($sum_row) {
		    $this->appended_rows_count++;
		}
		if ($avg_row) {
		    $this->appended_rows_count++;
		}
		if ($bench_row) {
		    $this->appended_rows_count++;
		}
	}
	
	/*
	 * a slew of getters
	 */
	
	public function id(){
		return $this->site_block->id();
	}

	public function path(){
		return $this->site_block->path();
	}

	public function title(){
		return $this->site_block->name();
	}
	public function maxRows(){
		return $this->max_rows;
	}
	public function pivotFieldName(){
		return $this->pivot_field->dbFieldName();
	}

	public function primaryTableName(){
		return $this->primary_table_name;
	}

	public function sorts(){
		return $this->sorts;
	}

/*
	public function joins(){
		return $this->joins;
	}
*/

    public function name(){
        return $this->site_block->name();
    }

    public function description(){
        return $this->site_block->description();
    }

    public function displayType(){
		return $this->site_block->displayType();
	}

	public function subtitle(){
		return $this->filters->get_filter_text();
	}

	public function hasBenchmark(){
		return $this->bench_row;
	}

	public function isSummary(){
		return $this->is_summary;
	}

	public function fieldGroups(){
		return $this->field_groups;
	}

	public function hasCntRow(){
		return $this->cnt_row;
	}

	public function hasAvgRow(){
		return $this->avg_row;
	}

	public function hasSumRow(){
		return $this->sum_row;
	}
	
	public function hasPivot(){
		return isset($this->pivot_field);
	}
	
	public function reportFields(){
		return $this->report_fields;
	}
	
	public function numFields(){
		return count($this->report_fields);
	}

    public function filterKeysValues(){
        return $this->filters->criteriaKeyValue();
    }
    
	public function getFieldlistArray(){
		if(!isset($this->report_fields) || count($this->report_fields) === 0){
			return false;
		}
		$ret = [];
		foreach($this->report_fields as $f){
			$ret[] = $f->dbFieldName();
		}
		return $ret;
	}

	public function getDisplayedFieldArray(){
		if(!isset($this->report_fields) || count($this->report_fields) === 0){
			return false;
		}
		$ret = [];
		foreach($this->report_fields as $f){
			if($f->isDisplayed()){
				$ret[] = $f->displayName();
			}
		}
		return $ret;
	}

	public function getAppendedRowsCount(){
	    return $this->appended_rows_count;
	}
	
	/**
	 * addFieldName
	 * 
	 * Add a field name to be included in the data query.  Will not create a new series
	 * 
	 * @return void
	 * @access public
	 *
	 **/
	public function addFieldName($name){
		$this->addl_select_field_names[] = $name;
	}

	/**
	 * @method getOutputData
     * 
     * Returns data needed by original non-API version of site
     * 
	 * @return array of output data for block
	 * @access public
	 *
	 **/
	public function getOutputData(){
		return [
			'name' => $this->site_block->name(),
			'description' => $this->site_block->name(),
			'filter_text' => $this->filters->get_filter_text(),
			'block' => $this->site_block->path(), //original program included sort_by, sort_order, graph_order but couldn't find anywhere it was used
			'block_id' => $this->site_block->id(),
		];
	}

    /**
     * toArray
     *
     * @return array representation of object
     *
     **/
    public function toArray(){
        $ret = $this->site_block->toArray();
        $ret['pivot_field'] = $this->pivot_field;
        $ret['is_summary'] = $this->is_summary;
        $ret['display_type'] = $this->display_type;
//        $ret['appended_rows_count'] = $this->appended_rows_count;
//            'max_rows' => $this->max_rows,
        $ret['cnt_row'] = $this->cnt_row;
        $ret['sum_row'] = $this->sum_row;
        $ret['avg_row'] = $this->avg_row;
        $ret['bench_row'] = $this->bench_row;
//            'field_groups' => $this->field_groups,

//if(strpos($ret['display_type'], 'chart') > 0 || true) var_dump('block name', $this->site_block->name(), $this->dataset);
        if(is_array($this->dataset) && !empty($this->dataset)){
            $ret['dataset'] = $this->dataset;
        }

        $tmp = array_filter($this->supplemental->toArray());
        if(is_array($tmp) && !empty($tmp)){
            $ret['supplemental'] = $tmp;
        }
        unset($tmp);

        if(is_array($this->default_sorts) && !empty($this->default_sorts)){
            $dsorts = [];
            foreach($this->default_sorts as $s){
                $dsorts[] = $s->toArray();
            }
            $ret['default_sorts'] = $dsorts;
        }

        return $ret;
    }
	/**
	 * setWhereGroups()
	 * 
	 * @return void
	 * @author ctranel
	 * @todo: implement child/nested groups
	 **/
	protected function setWhereGroups(){
		$this->where_groups = [];
		$criteria = [];
		$arr_ret = [];
		$arr_res = $this->datasource->getWhereData($this->site_block->id());
		if(!is_array($arr_res) || empty($arr_res)){
			return;
		}
		$prev_group = $arr_res[0]['where_group_id'];
		$prev_op = $arr_res[0]['operator'];
		if(is_array($arr_res)){
			foreach($arr_res as $s){
				if($prev_group != $s['where_group_id']){
					$this->where_groups[] = new WhereGroup($prev_op, $criteria);
					$criteria = [];
				}
				$criteria_datafield = new DbField($s['db_field_id'], $s['db_table_id'], $s['db_field_name'], $s['name'], $s['description'], $s['pdf_width'], $s['default_sort_order'],
						 $s['datatype'], $s['max_length'], $s['decimal_scale'], $s['unit_of_measure'], $s['is_timespan'], $s['is_foreign_key'], $s['is_nullable'], $s['is_natural_sort']);
				$criteria[] = new WhereCriteria($criteria_datafield, $s['condition']);

				$prev_group = $s['where_group_id'];
				$prev_op = $s['operator'];
			}
			//add the last item
			$this->where_groups[] = new WhereGroup($s['operator'], $criteria);
		}
	}
	
	/**
	 * getWhereGroupArray()
	 * 
	 * @return array of (nested) where groups
	 * @author ctranel
	 **/
	public function getWhereGroupArray(){
		$ret = [];
		if(!is_array($this->where_groups) || empty($this->where_groups)){
			$this->where_groups = $this->setWhereGroups();
		}
		if(count($this->where_groups) === 0){
			return;
		}

		foreach($this->where_groups as $wg){
			$ret[] = $wg->criteria();
		}
		return $ret;
	}
	
	/**
	 * @method sortText()
	 * @return string sort text
	 * @access public
	* */
	public function sortText($is_verbose = false){
		$ret = '';
		$is_first = true;
		if(isset($this->sorts) && count($this->sorts) > 0){
			foreach($this->sorts as $s){
				$ret .= $is_verbose ? $s->sortText($is_first) : $s->sortTextBrief($is_first);
				$is_first = false;
			}
		}
		
		return $ret;
	}

	/**
	 * getSortDateFieldName()
	 *
	 * returns field-name-keyed array of sort orders
	 *
	 * @return string field name
	 * @access public
	 * */
	public function getSortDateFieldName(){
		if(isset($this->sorts) && count($this->sorts) > 0){
			foreach($this->sorts as $s){
				if($s->isDate()){
					return $s->fieldName();
				}
			}
		}
		return null;
	}

	/**
	 * getSortArray()
	 * 
	 * returns field-name-keyed array of sort orders
	 * 
	 * @return string sort text
	 * @access public
	* */
	public function getSortArray(){
		$ret = [];
		if(isset($this->sorts) && count($this->sorts) > 0){
			foreach($this->sorts as $s){
				$ret[$s->fieldName()] = $s->order();
			}
		}
		return $ret;
	}
	
	/**
	 * @method resetSort()
	 * @return void
	 * @access public
	* */
	public function resetSort(){
		if(isset($this->sorts) && count($this->sorts) > 0){
			$this->sorts->removeAll($this->sorts);
		}
	}
	
	/**
	 * @method addSort()
	 * @param array of Sort objects
	 * @return void
	 * @access public
	* */
	public function addSort(Sort $sort){
		$this->sorts[] = $sort;
	}
	
	/**
	 * @method setDefaultSort()
	 * @return void
	 * @author ctranel
	 **/
	public function setDefaultSort(){
		$this->default_sorts = [];
		$arr_ret = [];
		$arr_res = $this->datasource->getSortData($this->site_block->id());
		if(is_array($arr_res)){
			foreach($arr_res as $s){
				$datafield = new DbField($s['db_field_id'], $s['db_table_id'], $s['db_field_name'], $s['name'], $s['description'], $s['pdf_width'], $s['default_sort_order'],
						 $s['datatype'], $s['max_length'], $s['decimal_scale'], $s['unit_of_measure'], $s['is_timespan'], $s['is_foreign_key'], $s['is_nullable'], $s['is_natural_sort']);
				$this->default_sorts[] = new Sort($datafield, $s['sort_order']);
			}
		}
		$this->sorts = $this->default_sorts;
	}
	
	/**
	 * @method sortFieldNames()
	 * @return ordered array of field names
	 * @access public
	public function sortFieldNames(){
		$ret = [];
		if(isset($this->sorts) && count($this->sorts) > 0){
			foreach($this->sorts as $s){
				$ret[] = $s->fieldName();
			}
		}
		
		return $ret;
	}
	* */
	/**
	 * sortOrders
	 *
	 * @method sortOrders()
	 * @return ordered array of sort orders
	 * @access public
	 public function sortOrders(){
	 $ret = [];
	 if(isset($this->sorts) && count($this->sorts) > 0){
	 foreach($this->sorts as $s){
	 $ret[] = $s->order();
	 }
	 }
	
	 return $ret;
	 }
	 * */
	
	/**
	 * @method setFilters()
	 * @param Filter object
	 * @return void
	 * @access public
	public function setFilters(Filters $filters){
		$this->filters = $filters;
	}
* */
	
	/**
	 * @method getFieldTable()
	 * @param field name
	 * @return string table name
	 * @access public
	 * 
	 * @todo: change this to return tables for all fields and iterate where it is called?
	* */
	public function getFieldTable($field_name){
		if(isset($this->report_fields) && count($this->report_fields) > 0){
			foreach($this->report_fields as $f){
				if($f->dbFieldName() === $field_name){
					return $f->dbTableName();
				}
			}
		}
		return null;
	}
	
	/**
	 * @method isNaturalSort()
	 * @param field name
	 * @return boolean
	 * @access public
	 *
	 * */
	public function isNaturalSort($field_name){
		if(isset($this->report_fields) && count($this->report_fields) > 0){
			foreach($this->report_fields as $f){
				if($f->dbFieldName() === $field_name){
					return $f->isNaturalSort();
				}
			}
		}
		return false;
	}
	
	/**
	 * @method isNaturalSort()
	 * @param field name
	 * @return boolean
	 * @access public
	 *
	 * */
	public function isSortable($field_name){
		if(isset($this->report_fields) && count($this->report_fields) > 0){
			foreach($this->report_fields as $f){
				if($f->dbFieldName() === $field_name){
					return $f->isSortable();
				}
			}
		}
		return false;
	}

	/**
	 * @method getSelectFields()
	 * 
	 * Retrieves fields designated as select, supplemental params (if set), 
	 * 
	 * @return string table name
	 * @access public
	 * */
	public function getSelectFields(){
		$ret = [];
		if(isset($this->report_fields) && count($this->report_fields) > 0){
			foreach($this->report_fields as $f){
				$ret[] = $f->selectFieldText();
			}
		}
		//supplemental params
		if(isset($this->supp_param_fieldnames) && count($this->supp_param_fieldnames) > 0){
			foreach($this->supp_param_fieldnames as $f){
				$ret[] = $f;
			}
		}

		return $ret;
	}
	
	/**
	 * @method getGroupBy()
	 * //@param array of GroupBy objects
	 * @return void
	 * @access public
	* */
	public function getGroupBy(){
		$ret = [];
		
		//@todo: pull in group by fields from database
		if($this->has_aggregate && isset($this->report_fields) && count($this->report_fields) > 0){
			foreach($this->report_fields as $f){
				if(!$f->isAggregate()){
					$ret[] = $f->dbFieldName();
				}
			}
		}
		return $ret;
	}
	
	public function defaultSort(){
		return $this->default_sort;
	}

	public function displayBenchRow(){
		return $this->bench_row;
	}
	
	/**
	 * @method setPivot()
	 * @param iDataField pivot field
	 * @return void
	 * @access public
	* */
	public function setPivot(iDataField $pivot_field){
		$this->pivot_field = $pivot_field;
	}

    /**
     * setDataset
     *
     * sets the objects dataset property with report data
     * @access public
     * */
    protected function setDataset(){
        $db_table = $this->db_table_factory->getTable($this->primary_table_name);
        $tmp_path = 'libraries/DataHandlers/' . $this->path() . '.php';
        $block_data_handler = $this->data_handler->load($this, $tmp_path, $db_table);
        $this->dataset = $block_data_handler->getData();
    }
}


