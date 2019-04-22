<?php
// FILE SUGARCRM flav=ent ONLY
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
$viewdefs['VisualPipeline']['base']['view']['pipeline-modules'] = array(
    'label' => 'LBL_VISUAL_PIPELINE_CONFIG_TITLE',
    'panels' => array(
        array(
            'fields' => array(
                array(
                    'name' => 'enabled_modules',
                    'type' => 'modules-list',
                    'label' => 'LBL_PIPELINE_MODULES_LIST',
                    'span' => 11,
                    'view' => 'edit',
                    'isMultiSelect' => true,
                    'ordered' => true,
                ),
            ),
        ),
    ),
);
