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

$viewdefs['KBContents']['portal']['layout']['list-dashboard'] = array(
    'components' =>
        array(
            array(
                'view' => array(
                    'type' => 'list-dashboard-toolbar',
                ),
            ),
            array(
                'view' =>
                    array(
                        'type' => 'dashlet-nestedset-list',
                        'label' => 'LBL_CATEGORIES',
                        'data_provider' => 'Categories',
                        'config_provider' => 'KBContents',
                        'root_name' => 'category_root',
                        'extra_provider' => array(
                            'module' => 'KBContents',
                            'field' => 'category_id',
                        ),
                    ),
            ),
        ),
    'css_class' => 'thumbnail dashlet'
);
