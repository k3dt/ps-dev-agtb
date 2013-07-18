<?php
//FILE SUGARCRM flav=pro ONLY
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
$viewdefs['Users']['mobile']['view']['list'] = array(
    'panels' => array(
        array(
            'label' => 'LBL_PANEL_DEFAULT',
            'fields' => array(
                array(
                    'name' => 'name',
                    'width' => '20%',   
                    'label' => 'LBL_NAME', 
                    'link' => true,
                    'orderBy' => 'last_name',
                    'default' => true,
                    'enabled' => true,
                    'related_fields' => array('first_name', 'last_name', 'salutation'),
                ),
                array(
                    'name' => 'title',
                    'width' => '15%', 
                    'label' => 'LBL_TITLE',
                    'default' => true,
                    'enabled' => true,
                ),
                array(
                    'name' => 'email1',
                    'width' => '15%', 
                    'label' => 'LBL_EMAIL',
                    'sortable' => false,
                    'link' => true,
                    'customCode' => '{$EMAIL1_LINK}{$EMAIL1}</a>',
                    'default' => true,
                    'enabled' => true,
                ),
                array(
                    'name' => 'phone_work',
                    'width' => '15%', 
                    'label' => 'LBL_OFFICE_PHONE',
                    'default' => true,
                    'enabled' => true,
                ),
                array(
                    'name' => 'phone_home',
                    'width' => '10',
                    'label' => 'LBL_HOME_PHONE',
                    'default' => false,
                ),
                array(
                    'name' => 'phone_mobile',
                    'width' => '10',
                    'label' => 'LBL_MOBILE_PHONE',
                    'default' => false,
                ),
                array(
                    'name' => 'phone_other',
                    'width' => '10',
                    'label' => 'LBL_WORK_PHONE',
                    'default' => false,
                ),
                array(
                    'name' => 'phone_fax',
                    'width' => '10',
                    'label' => 'LBL_FAX_PHONE',
                    'default' => false,
                ),
                array(
                    'name' => 'address_street',
                    'width' => '10',
                    'label' => 'LBL_PRIMARY_ADDRESS_STREET',
                    'default' => false,
                ),
                array(
                    'name' => 'address_city',
                    'width' => '10',
                    'label' => 'LBL_PRIMARY_ADDRESS_CITY',
                    'default' => false,
                ),
                array(
                    'name' => 'address_state',
                    'width' => '10',
                    'label' => 'LBL_PRIMARY_ADDRESS_STATE',
                    'default' => false,
                ),
                array(
                    'name' => 'address_postalcode',
                    'width' => '10',
                    'label' => 'LBL_PRIMARY_ADDRESS_POSTALCODE',
                    'default' => false,
                ),
                array(
                    'name' => 'date_entered',
                    'width' => '10',
                    'label' => 'LBL_DATE_ENTERED',
                    'default' => false,
                    'readonly' => true,
                ),
            ),
        ),
    ),
);