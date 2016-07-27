<?php
namespace myagsource\Page;

/**
 * Name:  iWhereCriteria
 *
 * Author: ctranel
 *
 * Created:  06-04-2015
 *
 * Description:  Interface for report where criteria.
 *
 */
interface iWhereCriteria {
	/**
	 */
	function __construct(\myagsource\Datasource\iDataField $datafield, $order);
	function fieldName();
//	function operator();
	function criteria();
}

?>