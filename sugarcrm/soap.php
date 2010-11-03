<?php
 if(!defined('sugarEntry'))define('sugarEntry', true);
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
 *
 ********************************************************************************/

require_once('include/entryPoint.php');
require_once('include/utils/file_utils.php');
ob_start();

require_once('soap/SoapError.php');
require_once('include/nusoap/nusoap.php');
require_once('modules/Contacts/Contact.php');
require_once('modules/Accounts/Account.php');
require_once('modules/Opportunities/Opportunity.php');
//BEGIN SUGARCRM flav!=sales ONLY
require_once('modules/Cases/Case.php');
//END SUGARCRM flav!=sales ONLY
//ignore notices
error_reporting(E_ALL ^ E_NOTICE);

//BEGIN SUGARCRM flav=pro ONLY
checkSystemLicenseStatus();
checkSystemState();
//END SUGARCRM flav=pro ONLY

global $HTTP_RAW_POST_DATA;

$administrator = new Administration();
$administrator->retrieveSettings();

$NAMESPACE = 'http://www.sugarcrm.com/sugarcrm';
$server = new soap_server;
$server->configureWSDL('sugarsoap', $NAMESPACE, $sugar_config['site_url'].'/soap.php');

//New API is in these files
if(!empty($administrator->settings['portal_on'])) {
	require_once('soap/SoapPortalUsers.php');
}

// BEGIN: SUGARINTERNAL CUSTOMIZATION - Sadek - added custom soap file
require_once('custom/si_custom_files/SoapCustomFunctions.php');
// END: SUGARINTERNAL CUSTOMIZATION - Sadek


/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 57
 * Include Moofcart specific soap functions
*/
require_once('custom/si_custom_files/SoapMoofcartFunctions.php');

/********** END BARTEK CUSTOMIZATION FOR MOOFCART ****/

require_once('soap/SoapSugarUsers.php');
//require_once('soap/SoapSugarUsers_version2.php');
require_once('soap/SoapData.php');
require_once('soap/SoapDeprecated.php');

//BEGIN SUGARCRM flav=int ONLY
require_once('soap/SoapStudio.php');
//END SUGARCRM flav=int ONLY

//BEGIN SUGARCRM flav=pro ONLY
require_once('soap/SoapSync.php');
require_once('soap/SoapUpgradeUtils.php');
//END SUGARCRM flav=pro ONLY

/* Begin the HTTP listener service and exit. */
ob_clean();

if (!isset($HTTP_RAW_POST_DATA)){
    $HTTP_RAW_POST_DATA = file_get_contents('php://input');
}

require_once('include/resource/ResourceManager.php');
$resourceManager = ResourceManager::getInstance();
$resourceManager->setup('Soap');
$observers = $resourceManager->getObservers();
//Call set_soap_server for SoapResourceObserver instance(s)
foreach($observers as $observer) {
   if(method_exists($observer, 'set_soap_server')) {
   	  $observer->set_soap_server($server);
   }
}

$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
global $soap_server_object;
$soap_server_object = $server;
$server->service($HTTP_RAW_POST_DATA);
//BEGIN: SUGARINTERNAL CUSTOMIZATION
//Log SOAP calls
if(!empty($server->methodname)){
	$fp = fopen('/var/www/sugarinternal/logs/soap_calls.log', 'a');
	$params = '';
	if (!empty($server->methodparams) && is_array($server->methodparams)){
		foreach($server->methodparams as $k => $v){
			$params .= "$k::$v,";
		}
		$params = substr($params, 0, -1);
	}
	$the_session_id = session_id();
	$ip_addr = $_SERVER['REMOTE_ADDR'];
	$msg = "\"".date('Y-m-d H:i:s')."\",\"{$server->methodname}\",\"$params\"" . (!empty($the_session_id) ? ",\"$the_session_id\"" : ",\"\"") . "\"" . $ip_addr . "\"";
	fwrite($fp, $msg."\n");
	fclose($fp);	
}
//END: SUGARINTERNAL CUSTOMIZATION
ob_end_flush();
flush();
sugar_cleanup();
exit();
?>
