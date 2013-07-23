<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
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
$module_name = 'Styleguide';
$viewdefs[$module_name]['base']['menu']['header'] = array(
    array(
        'route'=>'#'.$module_name.'/docs/index',
        'label' =>'Core Elements',
        'acl_action'=>'list',
        'acl_module'=>'Accounts',
        'icon' => 'icon-book',
    ),
    array(
        'route'=>'#'.$module_name.'/layout/records',
        'label' =>'Default Module Layout',
        'acl_action'=>'list',
        'acl_module'=>'Accounts',
        'icon' => 'icon-columns',
    ),
    array(
        'route'=>'#'.$module_name.'/layout/list',
        'label' =>'Default List View',
        'acl_action'=>'list',
        'acl_module'=>'Accounts',
        'icon' => 'icon-reorder',
    ),
    array(
        'route'=>'#'.$module_name.'/create',
        'label' =>'Default Create View',
        'acl_action'=>'list',
        'acl_module'=>'Accounts',
        'icon' => 'icon-plus',
    ),
    array(
        'route'=>'#'.$module_name.'/field/all',
        'label' =>'Example Sugar7 Fields',
        'acl_action'=>'list',
        'acl_module'=>'Accounts',
        'icon' => 'icon-list-alt',
    ),
    array(
        'route'=>'styleguide/tests/global.html',
        'label' =>'Run Tests',
        'acl_action'=>'list',
        'acl_module'=>'Accounts',
        'icon' => 'icon-heart',
    ),
);
