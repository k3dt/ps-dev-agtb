<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
* Agreement ("License") which can be viewed at
* http://www.sugarcrm.com/crm/master-subscription-agreement
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
* by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
********************************************************************************/

require_once 'clients/base/api/ListApi.php';
require_once('modules/Audit/Audit.php');
require_once 'data/BeanFactory.php';

class AuditApi extends ListApi
{
    public function registerApiRest()
    {
        return array(
            'view_change_log' => array(
                'reqType' => 'GET',
                'path' => array('Audit'),
                'pathVars' => array(''),
                'method' => 'viewChangeLog',
                'shortHelp' => 'View change log in record view',
                'longHelp' => '',
            ),
        );
    }

    public function viewChangeLog($api, $args) {
        global $focus; 
        
        if(empty($_REQUEST['module']) || empty($_REQUEST['record'])) {
            return false;
        } 
       
        $focus = BeanFactory::getBean($_REQUEST['module']);
        
        if(empty($focus)) {
            return false;
        }
        
        $records = Audit::get_audit_list();        
        return array('next_offset'=>-1,'records'=>$records);
    }
}