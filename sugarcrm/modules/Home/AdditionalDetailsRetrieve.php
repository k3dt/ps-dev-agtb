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
 ********************************************************************************/
/*********************************************************************************
 * $Id: AdditionalDetailsRetrieve.php 51719 2009-10-22 17:18:00Z mitani $
 * Description:  Target for ajax calls to retrieve AdditionalDetails
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

global $beanList, $beanFiles, $current_user;

$moduleDir = empty($_REQUEST['bean']) ? '' : $_REQUEST['bean'];
$beanName = empty($beanList[$moduleDir]) ? '' : $beanList[$moduleDir];
$id = empty($_REQUEST['id']) ? '' : $_REQUEST['id'];


if(empty($beanFiles[$beanName]) || 
    empty($id) || !is_file(substr($beanFiles[$beanName], 0, strrpos($beanFiles[$beanName], '/')) . '/metadata/additionalDetails.php')) {
        echo 'bad data';
        die();
} 

require_once($beanFiles[$beanName]);
require_once(substr($beanFiles[$beanName], 0, strrpos($beanFiles[$beanName], '/')) . '/metadata/additionalDetails.php');
$adFunction = 'additionalDetails' . $beanName;

if(function_exists($adFunction)) { // does the additional details function exist
    global $theme;
    $json = getJSONobj();
    $bean = new $beanName();
    $bean->retrieve($id);
    //BEGIN SUGARCRM flav=pro ONLY
    $bean->ACLFilterFields();
    //END SUGARCRM flav=pro ONLY
    $arr = array_change_key_case($bean->toArray(), CASE_UPPER);

    $results = $adFunction($arr);
    $retArray['body'] = str_replace(array("\rn", "\r", "\n"), array('','','<br />'), $results['string']);
    if(!$bean->ACLAccess('EditView')) $results['editLink'] = '';
    
$retArray['caption'] = "<div style='float:left'>{$app_strings['LBL_ADDITIONAL_DETAILS']}</div><div style='float: right'>" . (!empty($results['editLink']) ? "<a title='{$app_strings['LBL_EDIT_BUTTON']}' href={$results['editLink']}><img border=0 src=".SugarThemeRegistry::current()->getImageURL('edit_inline.gif')."></a>" : '');
    $retArray['caption'] .= (!empty($results['viewLink']) ? "<a title='{$app_strings['LBL_VIEW_BUTTON']}' href={$results['viewLink']}><img style='margin-left: 2px;' border=0 src=".SugarThemeRegistry::current()->getImageURL('view_inline.gif')."></a>" : '') . ""; 
    $retArray['width'] = (empty($results['width']) ? '300' : $results['width']);              
    $retArray['theme'] = $theme;
    echo 'result = ' . $json->encode($retArray);
}

?>