<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-professional-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2005 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: Save.php 56115 2010-04-26 17:08:09Z kjing $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/


global $current_user;

if (!is_admin($current_user)) sugar_die("Unauthorized access to administration.");

$focus = BeanFactory::getBean('Administration');

// filter for relevant POST data and update config table
foreach ($_POST as $key => $val) {
	$prefix = $focus->get_config_prefix($key);
	if (in_array($prefix[0], $focus->config_categories)) {
        if ( $prefix[0] == "license" )
        {
        	if ( $prefix[1] == "expire_date" )
        	{
        		global $timedate;
            	$val = $timedate->swap_formats( $val, $timedate->get_date_format(), $timedate->dbDayFormat );
        	}
        	else
        	if ( $prefix[1] == "key" )
        	{
        		$val = trim($val); // bug 16860 tyoung - trim whitespace from the start and end of the licence key value	
        	}
        }

        $focus->saveSetting($prefix[0], $prefix[1], $val); 
	}
}

  //BEGIN SUGARCRM lic=sub ONLY

if(isset($_POST['license_key'])){
	
	
	loadLicense(true);
	check_now(get_sugarbeat());
	
}

  //END SUGARCRM lic=sub ONLY




  //BEGIN SUGARCRM lic=sub ONLY

unset($_SESSION['license_seats_needed']);
unset($_SESSION['LICENSE_EXPIRES_IN']);
unset($_SESSION['VALIDATION_EXPIRES_IN']);
unset($_SESSION['HomeOnly']);

  //END SUGARCRM lic=sub ONLY

header("Location: index.php?action={$_POST['return_action']}&module={$_POST['return_module']}");
?>
