<?php
 if(!defined('sugarEntry'))define('sugarEntry', true);
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
//change directories to where this file is located.
//this is to make sure it can find dce_config.php
chdir(dirname(__FILE__));

require_once('include/entryPoint.php');

//Bug 27991 . Redirect to index.php if the request is not come from CLI.
//$sapi_type = php_sapi_name();
//if (substr($sapi_type, 0, 3) != 'cgi') {
//    global $sugar_config;
//	if(!empty($sugar_config['site_url'])){
//		header("Location: ".$sugar_config['site_url'] . "/index.php");
//	}else{
//		sugar_die("Didn't find site url in your sugarcrm config file");
//	}
//}
//End of #27991
$sapi_type = php_sapi_name();
if (substr($sapi_type, 0, 3) != 'cli') {
    sugar_die("cron.php is CLI only.");
}

if(empty($current_language)) {
	$current_language = $sugar_config['default_language'];
}

$app_list_strings = return_app_list_strings_language($current_language);
$app_strings = return_application_language($current_language);

global $current_user;
$current_user = new User();
$current_user->getSystemUser();

///////////////////////////////////////////////////////////////////////////////
////	PREP FOR SCHEDULER PID
$GLOBALS['log']->debug('--------------------------------------------> at cron.php <--------------------------------------------');
require_once 'include/SugarQueue/SugarCronJobs.php';
$jobq = new SugarCronJobs();
$jobq->runCycle();

$exit_on_cleanup = true;
//BEGIN SUGARCRM flav=dce ONLY
if(!empty($GLOBALS['DCE_CALL']) && $GLOBALS['DCE_CALL'])
	$exit_on_cleanup = false;
//END SUGARCRM flav=dce ONLY

sugar_cleanup(false);
// some jobs have annoying habit of calling sugar_cleanup(), and it can be called only once
// but job results can be written to DB after job is finished, so we have to disconnect here again
// just in case we couldn't call cleanup
if(class_exists('DBManagerFactory')) {
	$db = DBManagerFactory::getInstance();
	$db->disconnect();
}

if($exit_on_cleanup) exit;
