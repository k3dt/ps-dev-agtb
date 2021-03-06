<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
$viewdefs['Dashboards']['base']['view']['dashboard-headerpane'] = array(
    'buttons' => array(
        array(
            'type' => 'actiondropdown',
            'buttons' => array(
                array(
                    'name' => 'add_button',
                    'type' => 'rowaction',
                    'label' => 'LBL_CREATE_BUTTON_LABEL',
                ),
                array(
                    'name' => 'edit_button',
                    'type' => 'rowaction',
                    'label' => 'LBL_EDIT_BUTTON',
                    'acl_action' => 'edit',
                ),
                array(
                    'type' => 'rowaction',
                    'name' => 'duplicate_button',
                    'label' => 'LBL_DUPLICATE_BUTTON',
                    'acl_module' => 'Dashboards',
                    'acl_action' => 'create',
                ),
                [
                    'name' => 'delete_button',
                    'type' => 'rowaction',
                    'label' => 'LBL_DELETE_BUTTON_LABEL',
                    'acl_action' => 'delete',
                ],
                array(
                    'name' => 'collapse_button',
                    'type' => 'rowaction',
                    'label' => 'LBL_DASHLET_MINIMIZE_ALL',
                ),
                array(
                    'name' => 'expand_button',
                    'type' => 'rowaction',
                    'label' => 'LBL_DASHLET_MAXIMIZE_ALL',
                ),
                [
                    "name"      => "add_dashlet_button",
                    "type"      => "rowaction",
                    "label"     => "LBL_ADD_DASHLET_BUTTON",
                    'events' => [
                        'click' => 'button:add_dashlet_button:click',
                    ],
                    'acl_action' => 'edit',
                ],
            ),
            'showOn' => 'view',
        ),
        array(
            'name' => 'create_cancel_button',
            'type' => 'button',
            'label' => 'LBL_CANCEL_BUTTON_LABEL',
            'css_class' => 'btn-invisible btn-link',
            'showOn' => 'create',
        ),
        array(
            'name' => 'create_button',
            'type' => 'button',
            'events' => array(
                'click' => 'button:save_button:click',
            ),
            'label' => 'LBL_SAVE_BUTTON_LABEL',
            'css_class' => 'btn-primary',
            'showOn' => 'create',
        ),
    ),
    'panels' => array(
        array(
            'name' => 'header',
            'fields' => array(
                array(
                    'type' => 'dashboardtitle',
                    'name' => 'name',
                    'placeholder' => 'LBL_DASHBOARD_TITLE',
                ),
                array(
                    'name' => 'my_favorite',
                    'label' => 'LBL_FAVORITE',
                    'type' => 'favorite',
                    'dismiss_label' => true,
                ),
            ),
        ),
    ),
);
