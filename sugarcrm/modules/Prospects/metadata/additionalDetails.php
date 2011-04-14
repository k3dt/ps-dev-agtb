<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement 
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.  
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may 
 *not use this file except in compliance with the License. Under the terms of the license, You 
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or 
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or 
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit 
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the 
 *Software without first paying applicable fees is strictly prohibited.  You do not have the 
 *right to remove SugarCRM copyrights from the source code or user interface. 
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and 
 * (ii) the SugarCRM copyright notice 
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer 
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.  
 * $Id: additionalDetails.php 25128 2007-08-05 20:45:17Z chris $
 *********************************************************************************/

function additionalDetailsProspect($fields) {
	static $mod_strings;
	if(empty($mod_strings)) {
		global $current_language;
		$mod_strings = return_module_language($current_language, 'Prospects');
	}
		
	$overlib_string = '';
	
	if(!empty($fields['ACCOUNT_NAME'])) $overlib_string .= '<b>'. $mod_strings['LBL_EDIT_ACCOUNT_NAME'] . '</b> ' . $fields['ACCOUNT_NAME'] . '<br>';
	if(!empty($fields['PRIMARY_ADDRESS_STREET']) || !empty($fields['PRIMARY_ADDRESS_CITY']) ||
		!empty($fields['PRIMARY_ADDRESS_STATE']) || !empty($fields['PRIMARY_ADDRESS_POSTALCODE']) ||
		!empty($fields['PRIMARY_ADDRESS_COUNTRY']))
			$overlib_string .= '<b>' . $mod_strings['LBL_PRIMARY_ADDRESS'] . '</b><br>';
	if(!empty($fields['PRIMARY_ADDRESS_STREET'])) $overlib_string .= $fields['PRIMARY_ADDRESS_STREET'] . '<br>';
	if(!empty($fields['PRIMARY_ADDRESS_STREET_2'])) $overlib_string .= $fields['PRIMARY_ADDRESS_STREET_2'] . '<br>';
	if(!empty($fields['PRIMARY_ADDRESS_STREET_3'])) $overlib_string .= $fields['PRIMARY_ADDRESS_STREET_3'] . '<br>';
	if(!empty($fields['PRIMARY_ADDRESS_CITY'])) $overlib_string .= $fields['PRIMARY_ADDRESS_CITY'] . ', ';
	if(!empty($fields['PRIMARY_ADDRESS_STATE'])) $overlib_string .= $fields['PRIMARY_ADDRESS_STATE'] . ' ';
	if(!empty($fields['PRIMARY_ADDRESS_POSTALCODE'])) $overlib_string .= $fields['PRIMARY_ADDRESS_POSTALCODE'] . ' ';
	if(!empty($fields['PRIMARY_ADDRESS_COUNTRY'])) $overlib_string .= $fields['PRIMARY_ADDRESS_COUNTRY'] . '<br>';
	if(strlen($overlib_string) > 0 && !(strrpos($overlib_string, '<br>') == strlen($overlib_string) - 4)) 
		$overlib_string .= '<br>';  
	if(!empty($fields['PHONE_MOBILE'])) $overlib_string .= '<b>'. $mod_strings['LBL_MOBILE_PHONE'] . '</b> ' . $fields['PHONE_MOBILE'] . '<br>';
	if(!empty($fields['PHONE_HOME'])) $overlib_string .= '<b>'. $mod_strings['LBL_HOME_PHONE'] . '</b> ' . $fields['PHONE_HOME'] . '<br>';
	if(!empty($fields['PHONE_OTHER'])) $overlib_string .= '<b>'. $mod_strings['LBL_OTHER_PHONE'] . '</b> ' . $fields['PHONE_OTHER'] . '<br>';

	if(!empty($fields['EMAIL1'])) 
		$overlib_string .= '<b>'. $mod_strings['LBL_EMAIL_ADDRESS'] . '</b> ' . 
								 "<a href=index.php?module=Emails&action=Compose&contact_id={$fields['ID']}&" .
								 "parent_type=Contacts&parent_id={$fields['ID']}&to_addrs_ids={$fields['ID']}&to_addrs_names" .
								 "={$fields['FIRST_NAME']}&nbsp;{$fields['LAST_NAME']}&to_addrs_emails={$fields['EMAIL1']}&" .
								 "to_email_addrs=" . urlencode("{$fields['FIRST_NAME']} {$fields['LAST_NAME']} <{$fields['EMAIL1']}>") .
								 "&return_module=Contacts&return_action=ListView'>{$fields['EMAIL1']}</a><br>";
	if(!empty($fields['EMAIL2'])) 
		$overlib_string .= '<b>'. $mod_strings['LBL_OTHER_EMAIL_ADDRESS'] . '</b> ' . 
								 "<a href=index.php?module=Emails&action=Compose&contact_id={$fields['ID']}&" .
								 "parent_type=Contacts&parent_id={$fields['ID']}&to_addrs_ids={$fields['ID']}&to_addrs_names" .
								 "={$fields['FIRST_NAME']}&nbsp;{$fields['LAST_NAME']}&to_addrs_emails={$fields['EMAIL2']}&" .
								 "to_email_addrs=" . urlencode("{$fields['FIRST_NAME']} {$fields['LAST_NAME']} <{$fields['EMAIL2']}>") .
								 "&return_module=Contacts&return_action=ListView'>{$fields['EMAIL2']}</a><br>";
	
	if(!empty($fields['DESCRIPTION'])) { 
		$overlib_string .= '<b>'. $mod_strings['LBL_DESCRIPTION'] . '</b> ' . substr($fields['DESCRIPTION'], 0, 300);
		if(strlen($fields['DESCRIPTION']) > 300) $overlib_string .= '...';
	}	
	
	return array('fieldToAddTo' => 'FULL_NAME', 
				 'string' => $overlib_string, 
				 'editLink' => "index.php?action=EditView&module=Prospects&return_module=Prospects&record={$fields['ID']}", 
				 'viewLink' => "index.php?action=DetailView&module=Prospects&return_module=Prospects&record={$fields['ID']}");
	
}
 
 ?>
 
