<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/*********************************************************************************
 * $Id: GroupBySave.php 13782 2006-06-06 17:58:55Z majed $
 * Description:
 ********************************************************************************/

require_once('modules/QueryBuilder/QueryGroupBy.php');
require_once('modules/QueryBuilder/QueryColumn.php');
require_once('include/controller/Controller.php');




$focus = new QueryGroupBy();


if(!empty($_POST['groupby_record'])){
	$focus->retrieve($_POST['groupby_record']);
}



foreach($focus->column_fields as $field)
{
	if(isset($_POST[$field]))
	{
		$focus->$field = $_POST[$field];

	}
}

foreach($focus->additional_column_fields as $field)
{
	if(isset($_POST[$field]))
	{
		$value = $_POST[$field];
		$focus->$field = $value;

	}
}


//Conduct a column data save

echo "<BR>GROUP_BY MODULE:".$focus->groupby_module;
echo "<BR>GROUP_BY FIELD:".$focus->groupby_field;
echo "<BR>GROUP_BY CALC MODULE:".$focus->groupby_calc_module;
echo "<BR>GROUP_BY CALC FIELD:".$focus->groupby_calc_field;
echo "<BR>GROUP_BY TYPE:".$focus->groupby_type;
echo "<BR>GROUP_BY CALC TYPE:".$focus->groupby_calc_type;
echo "<BR>GROUP_BY AXIS:".$focus->groupby_calc_type;
echo "<BR>GROUP_BY QUAL:".$focus->groupby_qualifier;
echo "<BR>GROUP_BY QUAL QTY:".$focus->groupby_qualifier_qty;
echo "<BR>GROUP_BY QUAL START:".$focus->groupby_qualifier_start;
echo "<BR>ACTION:".$_REQUEST['action'];


//exit;

//process the column if this is a x axis group by
if(!empty($focus->groupby_axis) && $focus->groupby_axis=="Columns"){


	$column_object = new QueryColumn();
	if(!empty($_POST['parent_id'])){
	$column_object->retrieve($_POST['parent_id']);
	}

		$column_object->column_module = $focus->groupby_module;
		$column_object->column_name =$focus->groupby_field;
		$column_object->column_type = "Group By";
		$column_object->parent_id = $_POST['record'];

	$column_object->save();


	$focus->parent_id = $column_object->id;

} else {

	$focus->parent_id = $_REQUEST['record'];


	$controller = new Controller();

/////////Handle the list_order changes and information regarding this
	//run through change order if needed
	if(!empty($_REQUEST['change_order']) && $_REQUEST['change_order']=="Y"){


	///This is a hack, fix this. Maybe create a separate save file for when you change order
		$focus->retrieve($_POST['groupby_record']);

		$magnitude = 1;
		$direction = $_REQUEST['direction'];

		$controller->init($focus, "Save");
		$controller->change_component_order($magnitude, $direction, $focus->parent_id);


	}

	//run the order graber if this is new
	if(empty($focus->id)){
		$controller->init($focus, "New");
		$controller->change_component_order("", "", $focus->parent_id);
	}
//End list order handling


}


//Dump out unnecessary post data (group by type or calc type based)
$focus->check_groupby_type();

$focus->save();

$return_id = $focus->id;

//echo "RETURN ID".$_POST['return_id']."ACTION".$_POST['return_action']."moudle".$_POST['return_module'];
//exit;

if(isset($_POST['return_module']) && $_POST['return_module'] != "") $return_module = $_POST['return_module'];
else $return_module = "QueryBuilder";
if(isset($_POST['return_action']) && $_POST['return_action'] != "") $return_action = $_POST['return_action'];
else $return_action = "DetailView";
if(isset($_POST['return_id']) && $_POST['return_id'] != "") $return_id = $_POST['return_id'];

$GLOBALS['log']->debug("Saved record with id of ".$return_id);

header("Location: index.php?action=$return_action&module=$return_module&record=$return_id");
?>
