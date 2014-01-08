<?php

/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

$viewdefs['Notifications']['base']['view']['list'] = array(
    'favorites' => false,
    'panels' => array(
        array(
            'label' => 'LBL_PANEL_1',
            'fields' => array(
                array(
                    'name' => 'severity',
                    'type' => 'severity',
                    'default' => true,
                    'enabled' => true,
                    'css_class' => 'full-width',
                ),
                array(
                    'name' => 'name',
                    'default' => true,
                    'enabled' => true,
                    'link' => true,
                ),
                array (
                    'name' => 'assigned_user_name',
                    'sortable' => false,
                    'enabled' => true,
                    'default' => false,
                ),
                array(
                    'name' => 'date_entered',
                    'default' => false,
                    'enabled' => true,
                ),
                array(
                    'name' => 'date_modified',
                    'default' => false,
                    'enabled' => true,
                ),
                array(
                    'name' => 'is_read',
                    'default' => true,
                    'enabled' => true,
                ),
            ),
        ),
    ),
    'orderBy' => array(
        'field' => 'date_entered',
        'direction' => 'desc',
    ),
);
