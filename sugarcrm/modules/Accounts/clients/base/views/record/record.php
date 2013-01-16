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

$viewdefs['Accounts']['base']['view']['record'] = array(
    'buttons' => array(
        array(
            'type' => 'button',
            'name' => 'cancel_button',
            'label' => 'LBL_CANCEL_BUTTON_LABEL',
            'css_class' => 'btn-invisible btn-link',
            'mode' => 'edit',
        ),
        array(
            'type' => 'buttondropdown',
            'name' => 'edit_dropdown',
            'default' => array(
                'name' => 'edit_button',
                'label' => 'LBL_EDIT_BUTTON_LABEL',
            ),
            'dropdown' => array(
                array(
                    'name' => 'delete_button',
                    'label' => 'LBL_DELETE_BUTTON_LABEL',
                ),
                array(
                    'name' => 'duplicate_button',
                    'label' => 'LBL_DUPLICATE_BUTTON_LABEL',
                ),
            ),
            'mode' => 'view',
        ),
        array(
            'type' => 'buttondropdown',
            'name' => 'save_dropdown',
            'default' => array(
                'name' => 'save_button',
                'label' => 'LBL_SAVE_BUTTON_LABEL',
            ),
            'dropdown' => array(
                array(
                    'name' => 'delete_button',
                    'label' => 'LBL_DELETE_BUTTON_LABEL',
                ),
            ),
            'mode' => 'edit',
        ),
        array(
            'name' => 'sidebar_toggle',
            'type' => 'sidebartoggle',
        ),
    ),
    'panels' => array(
        array(
            'name' => 'panel_header',
            'header' => true,
            'fields' => array(
                'name',
                array(
                    'type' => 'favorite',
                    'noedit' => true,
                ),
            )
        ),
        array(
            'name' => 'panel_body',
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => array(
                'website',
                array(
                    "name" => "phone_office",
                    "label" => "Work Phone"
                ),
                'email',
                'phone_fax',
                'assigned_user_name',
            ),
        ),
        array(
            'name' => 'panel_shipping',
            'columns' => '2',
            'labelsOnTop' => true,
            'fields' => array(
                array(
                    'name' => 'fieldset_address',
                    'type' => 'fieldset',
                    'label' => 'Billing Address',
                    'fields' => array(
                        'billing_address_street',
                        'billing_address_city',
                        'billing_address_state',
                        'billing_address_postalcode',
                    ),
                ),
                array(
                    'name' => 'fieldset_shipping_address',
                    'type' => 'fieldset',
                    'label' => 'Shipping Address',
                    'fields' => array(
                        'shipping_address_street',
                        'shipping_address_city',
                        'shipping_address_state',
                        'shipping_address_postalcode',
                        array(
                            'name' => 'copy',
                            'type' => 'copy',
                            'mapping' => array(
                                'billing_address_street' => 'shipping_address_street',
                                'billing_address_city' => 'shipping_address_city',
                                'billing_address_state' => 'shipping_address_state',
                                'billing_address_postalcode' => 'shipping_address_postalcode',
                            ),
                        ),
                    ),
                ),
            ),
        ),
        array(
            'name' => 'panel_hidden',
            'hide' => true,
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => array(
                array(
                    'name' => 'description',
                    'span' => 12
                ),
                'account_type',
                'industry',
                'annual_revenue',
                'employees',
                'sic_code',
                'ticker_symbol',
                'member_of',
                'ownership',
                'campaign_name',
                'rating',
                'date_modified',
                'team_name',
                'date_entered'
            )
        )
    ),
);
