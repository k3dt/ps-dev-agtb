<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
$viewdefs['Calls']['base']['view']['selection-list'] = array(
    'panels' => array(
        array(
            'name' => 'panel_header',
            'label' => 'LBL_PANEL_1',
            'fields' => array(
                array(
                    'label' => 'LBL_LIST_SUBJECT',
                    'enabled' => true,
                    'default' => true,
                    'link' => true,
                    'name' => 'name',
                ),
                array(
                    'label' => 'LBL_STATUS',
                    'enabled' => true,
                    'default' => true,
                    'name' => 'status',
                    'type' => 'event-status',
                    'css_class' => 'full-width',
                ),
                array(
                    'name' => 'parent_name',
                    'width' => '20%',
                    'label' => 'LBL_LIST_RELATED_TO',
                    'dynamic_module' => 'PARENT_TYPE',
                    'id' => 'PARENT_ID',
                    'link' => true,
                    'enabled' => true,
                    'default' => true,
                    'sortable' => false,
                    'ACLTag' => 'PARENT',
                    'related_fields' =>
                        array(
                            'parent_id',
                            'parent_type',
                        ),
                ),
                array(
                    'label' => 'LBL_LIST_DATE',
                    'enabled' => true,
                    'default' => true,
                    'name' => 'date_start',
                ),
                array(
                    'label' => 'LBL_DATE_END',
                    'enabled' => true,
                    'default' => false,
                    'name' => 'date_end',
                ),
                array(
                    'name' => 'assigned_user_name',
                    'target_record_key' => 'assigned_user_id',
                    'target_module' => 'Employees',
                    'label' => 'LBL_LIST_ASSIGNED_TO_NAME',
                    'enabled' => true,
                    'default' => false,
                    'sortable' => false,
                ),
            ),
        ),
    ),
);
