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

 * Description:  
 ********************************************************************************/

require_once('modules/QueryBuilder/QueryColumn.php');
global $mod_strings;



$focus = new QueryColumn();

if(!isset($_REQUEST['column_record']))
	sugar_die($mod_strings['ERR_DELETE_RECORD']);

$focus->retrieve($_REQUEST['column_record']);	
	
$focus->clear_deleted();


if(!empty($focus->column_type) && $focus->column_type=="Calculation"){
	
	$focus->clear_child_calc_info();
	
}	

header("Location: index.php?module=".$_REQUEST['return_module']."&action=".$_REQUEST['return_action']."&record=".$_REQUEST['return_id']);
?>
