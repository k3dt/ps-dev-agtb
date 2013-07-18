<?php
//FILE SUGARCRM flav=pro || flav=sales ONLY

/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

$viewdefs['Cases']['portal']['view']['record'] = array(
    'buttons' => array(
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
            ),
        ),
        array(
            'name' => 'panel_body',
            'label' => 'LBL_PANEL_2',
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' =>
            array(
                array(
                    'name' => 'case_number',
                    'span' => 12,
                ),
                array(
                    'name' => 'date_entered',
                    'readonly' => true,
                ),
                'status',
                'priority',
                'type',
                array(
                    'name' => 'date_modified',
                    'readonly' => true,
                ),
                array(
                    'name' => 'modified_by_name',
                    'readonly' => true,
                ),
                array(
                    'name' => 'created_by_name',
                    'readonly' => true,
                ),
                'assigned_user_name',
                array(
                    'name' => 'description',
                    'span' => 12,
                ),
            ),
        ),
    ),
);
