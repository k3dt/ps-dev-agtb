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

$viewdefs['Leads']['base']['view']['record'] = array(
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
                    'name' => 'fieldset_name',
                    'type' => 'fieldset',
                    'fields' => array('salutation', 'first_name', 'last_name'),
                ),
            )
        ),
        array(
            'name' => 'panel_body',
            'label' => 'LBL_PANEL_2',
            'columns' => 1,
            'labels' => true,
            'labelsOnTop' => false,
            'placeholders' => true,
            'fields' => array(
                'title',
                'department',
                'account_name',
                'phone_work',
                'phone_mobile',

            )
        ),
        array(
            'name' => 'panel_hidden',
            'hide' => true,
            'labelsOnTop' => false,
            'placeholders' => true,
            'fields' => array(
                'phone_fax',
                'website',
                array(
                    'name' => 'fieldset_primaryaddress',
                    'type' => 'fieldset',
                    'label' => 'Billing Address',
                    'fields' => array(
                        'primary_address_street',
                        'primary_address_city',
                        'primary_address_state',
                        'primary_address_postalcode',
                        'primary_address_country'
                    )
                ),
                array(
                    'name' => 'fieldset_altaddress',
                    'type' => 'fieldset',
                    'label' => 'Alternate Address',
                    'fields' => array(
                        'alt_address_street',
                        'alt_address_city',
                        'alt_address_state',
                        'alt_address_postalcode',
                        'alt_address_country'
                    )
                ),
                array(
                    'name' => 'email',
                    'label' => 'LBL_EMAIL_ADDRESSES',
                ),
                'description',
                'status',
                'status_description',
                'opportunity_amount',
                'campaign_name',
                'lead_source',
                'lead_source_description',
                'refered_by',
                'do_not_call',
                'assigned_user_name',
                //BEGIN SUGARCRM flav=pro ONLY
                'team_name',
                //END SUGARCRM flav=pro ONLY
            )
        )
    ),
);
