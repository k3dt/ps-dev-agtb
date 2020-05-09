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

$viewdefs['Opportunities']['base']['view']['preview'] = array(
    'templateMeta' => array(
        'maxColumns' => 1,
    ),
    'panels' => array(
        array(
            'name' => 'panel_header',
            'header' => true,
            'fields' => array(
                array(
                    'name' => 'picture',
                    'type' => 'avatar',
                    'size' => 'large',
                    'dismiss_label' => true,
                    'readonly' => true,
                ),
                array(
                    'name' => 'name',
                    //BEGIN SUGARCRM flav=ent ONLY
                    'related_fields' => array(
                        'total_revenue_line_items',
                        'closed_revenue_line_items',
                        'included_revenue_line_items',
                    ),
                    //END SUGARCRM flav=ent ONLY
                ),
                array(
                    'name' => 'favorite',
                    'label' => 'LBL_FAVORITE',
                    'type' => 'favorite',
                    'dismiss_label' => true,
                ),
                array(
                    'name' => 'follow',
                    'label' => 'LBL_FOLLOW',
                    'type' => 'follow',
                    'readonly' => true,
                    'dismiss_label' => true,
                ),
                // BEGIN SUGARCRM flav=ent ONLY
                [
                    'name' => 'renewal',
                    'type' => 'renewal',
                    'dismiss_label' => true,
                ],
                // END SUGARCRM flav=ent ONLY
            ),
        ),
        array(
            'name' => 'panel_body',
            'label' => 'LBL_RECORD_BODY',
            'columns' => 2,
            'labels' => true,
            'placeholders' => true,
            'fields' => array(
                array(
                    'name' => 'account_name',
                    'related_fields' => array(
                        'account_id',
                    ),
                ),
                // BEGIN SUGARCRM flav!=ent ONLY
                array(
                    'name' => 'date_closed',
                    'related_fields' => array(
                        'date_closed_timestamp',
                    ),
                ),
                // END SUGARCRM flav!=ent ONLY
                // BEGIN SUGARCRM flav=ent ONLY
                array(
                    'name' => 'date_closed',
                    'type' => 'date-cascade',
                    'label' => 'LBL_LIST_DATE_CLOSED',
                    'disable_field' => array(
                        'total_revenue_line_items',
                        'closed_revenue_line_items',
                    ),
                ),
                [
                    'name' => 'service_start_date',
                    'type' => 'date-cascade',
                    'label' => 'LBL_SERVICE_START_DATE',
                    'disable_field' => 'service_open_revenue_line_items',
                    'related_fields' => [
                        'service_open_revenue_line_items',
                    ],
                ],
                // END SUGARCRM flav=ent ONLY
                'probability',
                array(
                    'name' => 'commit_stage',
                    'span' => 6,
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
                    'span' => 6,
                    'currency_field' => 'currency_id',
                    'base_rate_field' => 'base_rate',
                ),
                array(
                    'name' => 'best_case',
                    'type' => 'currency',
                    'label' => 'LBL_BEST',
                    'related_fields' => array(
                        'best_case',
                        'currency_id',
                        'base_rate',
                    ),
                    'currency_field' => 'currency_id',
                    'base_rate_field' => 'base_rate',
                ),
                array(
                    'name' => 'worst_case',
                    'type' => 'currency',
                    'label' => 'LBL_WORST',
                    'related_fields' => array(
                        'worst_case',
                        'currency_id',
                        'base_rate',
                    ),
                    'currency_field' => 'currency_id',
                    'base_rate_field' => 'base_rate',
                ),
                array(
                    'name' => 'tag',
                    'span' => 12,
                ),
                [
                    'name' => 'sales_status',
                    'label' => 'LBL_SALES_STATUS',
                    'default' => true,
                    'enabled' => true,
                    'type' => 'enum',
                ],
                // BEGIN SUGARCRM flav!=ent ONLY
                array(
                    'name' => 'sales_stage',
                ),
                // END SUGARCRM flav!=ent ONLY
                // BEGIN SUGARCRM flav=ent ONLY
                [
                    'name' => 'sales_stage',
                    'type' => 'enum-cascade',
                    'label' => 'LBL_SALES_STAGE',
                    'disable_field' => array(
                        'total_revenue_line_items',
                        'closed_revenue_line_items',
                    ),
                ],
                // END SUGARCRM flav=ent ONLY
            ),
        ),
        array(
            'name' => 'panel_hidden',
            'label' => 'LBL_RECORD_SHOWMORE',
            'hide' => true,
            'placeholders' => true,
            'columns' => 2,
            'fields' => array(
                'next_step',
                'opportunity_type',
                // BEGIN SUGARCRM flav=ent ONLY
                'renewal_parent_name',
                // END SUGARCRM flav=ent ONLY
                'lead_source',
                'campaign_name',
                array(
                    'name' => 'description',
                    'span' => 12,
                ),
                'assigned_user_name',
                'team_name',
                array(
                    'name' => 'date_entered_by',
                    'readonly' => true,
                    'type' => 'fieldset',
                    'label' => 'LBL_DATE_ENTERED',
                    'fields' => array(
                        array(
                            'name' => 'date_entered',
                        ),
                        array(
                            'type' => 'label',
                            'default_value' => 'LBL_BY',
                        ),
                        array(
                            'name' => 'created_by_name',
                        ),
                    ),
                ),
                array(
                    'name' => 'date_modified_by',
                    'readonly' => true,
                    'type' => 'fieldset',
                    'label' => 'LBL_DATE_MODIFIED',
                    'fields' => array(
                        array(
                            'name' => 'date_modified',
                        ),
                        array(
                            'type' => 'label',
                            'default_value' => 'LBL_BY',
                        ),
                        array(
                            'name' => 'modified_by_name',
                        ),
                    ),
                ),
            ),
        ),
    ),
);
