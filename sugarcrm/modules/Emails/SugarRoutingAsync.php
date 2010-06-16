<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
/*********************************************************************************
 * $Id: Delete.php,v 1.22 2006/01/17 22:50:52 majed Exp $
 * Description:
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc. All Rights
 * Reserved. Contributor(s): ______________________________________..
 *********************************************************************************/
require_once("include/SugarRouting/SugarRouting.php");

$ie = new InboundEmail();
$json = getJSONobj();
$rules = new SugarRouting($ie, $current_user);

switch($_REQUEST['routingAction']) {
	case "setRuleStatus":
		$rules->setRuleStatus($_REQUEST['rule_id'], $_REQUEST['status']);
	break;
	
	case "saveRule":
		$rules->save($_REQUEST);
	break;
	
	case "deleteRule":
		$rules->deleteRule($_REQUEST['rule_id']);
	break;
	
	/* returns metadata to construct actions */
	case "getActions":
		require_once("include/SugarDependentDropdown/SugarDependentDropdown.php");
		
		$sdd = new SugarDependentDropdown();
		$sdd->init("include/SugarDependentDropdown/metadata/dependentDropdown.php");
		$out = $json->encode($sdd->metadata, true);
		echo $out;
	break;
	
	/* returns metadata to construct a rule */
	case "getRule":
		$ret = '';
		if(isset($_REQUEST['rule_id']) && !empty($_REQUEST['rule_id']) && isset($_REQUEST['bean']) && !empty($_REQUEST['bean'])) {
			if(!isset($beanList))
				include("include/modules.php");
			
			$class = $beanList[$_REQUEST['bean']];
			//$beanList['Groups'] = 'Group';
			if(isset($beanList[$_REQUEST['bean']])) {
				require_once("modules/{$_REQUEST['bean']}/{$class}.php");
				$bean = new $class();
				
				$rule = $rules->getRule($_REQUEST['rule_id'], $bean);
				
				$ret = array(
					'bean' => $_REQUEST['bean'],
					'rule' => $rule
				);
			}
		} else {
			$bean = new SugarBean();
			$rule = $rules->getRule('', $bean);
			
			$ret = array(
				'bean' => $_REQUEST['bean'],
				'rule' => $rule
			);
		}
		
		//_ppd($ret);
		
		$out = $json->encode($ret, true);
		echo $out;
	break;
	
	case "getStrings":
		$ret = $rules->getStrings();
		$out = $json->encode($ret, true);
		echo $out;
	break;

	//BEGIN SUGARCRM flav=int ONLY
	case "test":
		require_once("include/SugarDependentDropdown/SugarDependentDropdown.php");
		
		$sdd = new SugarDependentDropdown();
		$sdd->debugMode = true;
		$sdd->init("include/SugarDependentDropdown/metadata/dependentDropdown.php");
		
		_pp($sdd);
	break;
	//END SUGARCRM flav=int ONLY
	
	default:
		echo "NOOP";
}