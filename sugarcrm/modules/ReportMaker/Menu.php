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
 * $Id: Menu.php 55254 2010-03-12 16:57:56Z roger $
 * Description:  
 ********************************************************************************/

global $mod_strings;
global $current_user;

if (!is_admin($current_user))
{
   $module_menu = Array(
   
	Array("index.php?module=ReportMaker&action=EditView&return_module=ReportMaker&return_action=DetailView", $mod_strings['LNK_NEW_REPORTMAKER'],"CreateReport"),
	Array("index.php?module=ReportMaker&action=index&return_module=ReportMaker&return_action=index", $mod_strings['LNK_LIST_REPORTMAKER'],"ReportMaker"),
//BEGIN SUGARCRM flav=int ONLY
	Array("index.php?module=QueryBuilder&action=EditView&return_module=QueryBuilder&return_action=DetailView", $mod_strings['LNK_NEW_QUERYBUILDER'],"CreateQuery"),
	Array("index.php?module=QueryBuilder&action=index&return_module=QueryBuilder&return_action=DetailView", $mod_strings['LNK_QUERYBUILDER'],"QueryBuilder"),
//END SUGARCRM flav=int ONLY
	Array("index.php?module=DataSets&action=EditView&return_module=DataSets&return_action=DetailView", $mod_strings['LNK_NEW_DATASET'],"CreateDataSet"),
	Array("index.php?module=DataSets&action=index&return_module=DataSets&return_action=index", $mod_strings['LNK_LIST_DATASET'],"DataSets"),
	Array("index.php?module=Reports&action=index", $mod_strings['LBL_ALL_REPORTS'],"Reports", 'Reports'),

	);
} else {
	
	$module_menu = Array(
	
	Array("index.php?module=ReportMaker&action=EditView&return_module=ReportMaker&return_action=DetailView", $mod_strings['LNK_NEW_REPORTMAKER'],"CreateReport"),
	Array("index.php?module=ReportMaker&action=index&return_module=ReportMaker&return_action=index", $mod_strings['LNK_LIST_REPORTMAKER'],"ReportMaker"),
	Array("index.php?module=CustomQueries&action=EditView&return_module=CustomQueries&return_action=DetailView", $mod_strings['LNK_NEW_CUSTOMQUERY'],"CreateCustomQuery"),
	Array("index.php?module=CustomQueries&action=index&return_module=CustomQueries&return_action=DetailView", $mod_strings['LNK_CUSTOMQUERIES'],"CustomQueries"),
//BEGIN SUGARCRM flav=int ONLY
	Array("index.php?module=QueryBuilder&action=EditView&return_module=QueryBuilder&return_action=DetailView", $mod_strings['LNK_NEW_QUERYBUILDER'],"CreateQuery"),
	Array("index.php?module=QueryBuilder&action=index&return_module=QueryBuilder&return_action=DetailView", $mod_strings['LNK_QUERYBUILDER'],"QueryBuilder"),
//END SUGARCRM flav=int ONLY
	Array("index.php?module=DataSets&action=EditView&return_module=DataSets&return_action=DetailView", $mod_strings['LNK_NEW_DATASET'],"CreateDataSet"),
	Array("index.php?module=DataSets&action=index&return_module=DataSets&return_action=index", $mod_strings['LNK_LIST_DATASET'],"DataSets"),
	Array("index.php?module=Reports&action=index", $mod_strings['LBL_ALL_REPORTS'],"Reports", 'Reports'),

	);
}	



?>
