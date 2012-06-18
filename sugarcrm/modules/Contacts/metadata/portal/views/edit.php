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

$viewdefs ['Contacts']['portal']['view']['edit'] = array(
    'buttons' =>
        array(
            array(
                'name' => 'save_button',
                'type' => 'button',
                'label' => 'Save',
                'value' => 'save',
                'class' => 'save-profile',
                'primary' => true,
            ),
            array(
                'name' => 'cancel_button',
                'type' => 'button',
                'label' => 'Cancel',
                'value' => 'cancel',
                'events' =>
                array(
                    'click' => 'function(){ window.history.back(); }',
                ),
                'primary' => false,
            ),
        ),
    'templateMeta' =>
        array(
            'maxColumns' => '2',
            'widths' =>
            array(
                array(
                    'label' => '10',
                    'field' => '30',
                ),
                array(
                    'label' => '10',
                    'field' => '30',
                ),
            ),
            'formId' => 'ContactsEditView',
            'formName' => 'ContactsEditView',
            'hiddenInputs' =>
            array(
                'module' => 'Contacts',
                'returnmodule' => 'Contacts',
                'returnaction' => 'DetailView',
                'action' => 'Save',
            ),
            'hiddenFields' =>
            array(
                array(
                    'name' => 'portal_viewable',
                    'operator' => '=',
                    'value' => '1',
                ),
            ),
            'useTabs' => false,
        ),
    'panels' =>
        array(
            array(
                'label' => 'default',
                'fields' =>
                array(
                    array(
                        'name' => 'first_name',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'last_name',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'title',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'email',
                        'type' => 'email',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'phone_work',
                        'type' => 'text',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'primary_address_street',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'primary_address_city',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'primary_address_state',
                        'type' => 'enum',
                        'options' => 'state_dom',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'primary_address_postalcode',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array (
                        'name' => 'primary_address_country',
                        'type' => 'enum',
                        'options' => 'countries_dom',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                ),
            ),
        ),
);
