<?php
//FILE SUGARCRM flav=pro || flav=sales ONLY
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
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
/*********************************************************************************
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

$viewdefs['Contacts']['base']['view']['record'] = array(
    'buttons' => array(
        array(
            'type'    => 'button',
            'label'   => 'LBL_SAVE_BUTTON_LABEL',
            'css_class' => 'hide btn-primary record-save',
        ),
        array(
            'type'    => 'button',
            'label'   => 'LBL_CANCEL_BUTTON_LABEL',
            'css_class' => 'hide record-cancel',
        ),
        array(
            'type'    => 'button',
            'label'   => 'LBL_EDIT_BUTTON_LABEL',
            'css_class' => 'record-edit',
        ),
        array(
            'type'    => 'button',
            'label'   => 'LBL_DELETE_BUTTON_LABEL',
            'css_class' => 'record-delete',
        ),
    ),
    'panels' => array(
        array(
            'name' => 'panel_header',
            'header' => true,
            'fields' => array(
                array(
                    'name' => 'img',
                    'noedit' => true,
                ),
                array(
                    'name' => 'fieldset_full_name',
                    'type' => 'fieldset',
                    'fields' => array('salutation','first_name', 'last_name')
                ),
            )
        ),
        array(
            'name' => 'panel_body',
            'columns' => 2,
            'labels' => false,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => array(
                'title',
                'phone_mobile',
                'department',
                'phone_work',
                'account_name',
                'phone_fax',
                array(
                    'name' => 'fieldset_address',
                    'type' => 'fieldset',
                    'label' => 'Primay Address',
                    'fields' => array('primary_address_street','primary_address_city', 'primary_address_state', 'primary_address_postalcode')
                ),
                'email'

            ),
        ),
        array(
            'columns'=>2,
            'name' => 'panel_hidden',
            'hide' => true,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => array(
                'description',
                'report_to_name',
                'sync_contact',
                'lead_source',
                'do_not_call',
                array(
                  'name'=>'campaign_name',
                   'span' => 8
                ),
                'portal_name',
                'portal_active',
                'preferred_language',
                'assigned_user_id',
                'date_modified',
                'date_created',
                array(
                    'name' => 'fieldset_alt_address',
                    'type' => 'fieldset',
                    'label' => 'Alternate Address',
                    'fields' => array('alt_address_street','alt_address_city', 'alt_address_state', 'alt_address_postalcode')
                ),

            )
        )
    ),
);