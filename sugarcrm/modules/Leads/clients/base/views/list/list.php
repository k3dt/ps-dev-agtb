<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

$viewdefs['Leads']['base']['view']['list'] = array(
    'panels' => array(
        array(
            'name' => 'panel_header',
            'label' => 'LBL_PANEL_1',
            'fields' => array(
                array (
                    'name' => 'full_name',
                    'type' => 'fullname',
                    'fields' => array(
                        'salutation',
                        'first_name',
                        'last_name',
                    ),
                    'link' => true,
                    'css_class' => 'full-name',
                    'label' => 'LBL_LIST_NAME',
                    'enabled' => true,
                    'default' => true,
                ),
                array (
                    'name' => 'status',
                    'label' => 'LBL_LIST_STATUS',
                    'enabled' => true,
                    'default' => true,
                ),
                array (
                    'name' => 'account_name',
                    'label' => 'LBL_LIST_ACCOUNT_NAME',
                    'enabled' => true,
                    'default' => true,
                    'related_fields' => array(
                        'account_id',
                        'converted',
                    ),
                ),
                array (
                    'name'  => 'phone_work',
                    'label' => 'LBL_LIST_PHONE',
                    'enabled' => true,
                    'default' => true,
                ),
                array (
                    'name'  => 'email',
                    'label' => 'LBL_LIST_EMAIL_ADDRESS',
                    'enabled' => true,
                    'default' => true,
                ),
                array (
                    'name'  => 'assigned_user_name',
                    'label' => 'LBL_LIST_ASSIGNED_USER',
                    'enabled' => true,
                    'default' => true,
                ),
                array (
                    'name'  => 'date_entered',
                    'label' => 'LBL_DATE_ENTERED',
                    'enabled' => true,
                    'default' => true,
                    'readonly' => true,
                ),
            ),
        ),
    ),
);
