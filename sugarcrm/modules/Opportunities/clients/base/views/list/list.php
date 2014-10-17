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

$viewdefs['Opportunities']['base']['view']['list'] = array(
    'panels' => array(
        array(
            'name' => 'panel_header',
            'label' => 'LBL_PANEL_1',
            'fields' => array(
                array(
                    'name' => 'name',
                    'width' => 30,
                    'link' => true,
                    'label' => 'LBL_LIST_OPPORTUNITY_NAME',
                    'enabled' => true,
                    'default' => true,
                    //BEGIN SUGARCRM flav=ent ONLY
                    'related_fields' => array(
                        'total_revenue_line_items',
                        'closed_revenue_line_items'
                    )
                    //END SUGARCRM flav=ent ONLY
                ),
                array(
                    'name' => 'account_name',
                    'width' => 20,
                    'link' => true,
                    'label' => 'LBL_LIST_ACCOUNT_NAME',
                    'enabled' => true,
                    'default' => true,
                ),
                array(
                    'name' => 'sales_stage',
                    'width' => 10,
                    'label' => 'LBL_LIST_SALES_STAGE',
                    'enabled' => true,
                    'default' => true,
                ),
                array(
                    'name' => 'amount',
                    'type' => 'currency',
                    'label' => 'LBL_LIKELY',
                    'related_fields' => array(
                        'amount',
                        'currency_id',
                        'base_rate',
                    ),
                    'currency_field' => 'currency_id',
                    'base_rate_field' => 'base_rate',
                    'width' => 10,
                    'enabled' => true,
                    'default' => true,
                ),
                array(
                    'name' => 'opportunity_type',
                    'width' => 15,
                    'label' => 'LBL_TYPE',
                    'enabled' => true,
                    'default' => true,
                ),
                array(
                    'name' => 'lead_source',
                    'width' => 15,
                    'label' => 'LBL_LEAD_SOURCE',
                    'enabled' => true,
                    'default' => true,
                ),
                array(
                    'name' => 'next_step',
                    'width' => 10,
                    'label' => 'LBL_NEXT_STEP',
                    'enabled' => true,
                    'default' => true,
                ),
                array(
                    'name' => 'probability',
                    'width' => 10,
                    'label' => 'LBL_PROBABILITY',
                    'enabled' => true,
                    'default' => true,
                ),
                array(
                    'name' => 'date_closed',
                    'width' => 10,
                    'label' => 'LBL_DATE_CLOSED',
                    'enabled' => true,
                    'default' => true,
                ),
                array(
                    'name' => 'created_by_name',
                    'width' => 10,
                    'label' => 'LBL_CREATED',
                    'enabled' => true,
                    'default' => true,
                    'readonly' => true
                ),
                array(
                    'name' => 'team_name',
                    'type' => 'teamset',
                    'width' => 5,
                    'label' => 'LBL_LIST_TEAM',
                    'enabled' => true,
                    'default' => false,
                ),
                array(
                    'name' => 'assigned_user_name',
                    'width' => 5,
                    'label' => 'LBL_LIST_ASSIGNED_USER',
                    'id' => 'ASSIGNED_USER_ID',
                    'enabled' => true,
                    'default' => true,
                ),
                array(
                    'name' => 'modified_by_name',
                    'width' => 5,
                    'label' => 'LBL_MODIFIED',
                    'enabled' => true,
                    'default' => true,
                    'readonly' => true,
                ),
                array(
                    'name' => 'date_entered',
                    'width' => 10,
                    'label' => 'LBL_DATE_ENTERED',
                    'enabled' => true,
                    'default' => true,
                    'readonly' => true,
                ),
            ),
        ),
    ),
);
