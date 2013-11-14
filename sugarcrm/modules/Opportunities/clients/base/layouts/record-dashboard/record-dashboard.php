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
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

$viewdefs['Opportunities']['base']['layout']['record-dashboard'] = array(
    'metadata' =>
    array(
        'components' =>
        array(
            array(
                'rows' =>
                array(
                    //BEGIN SUGARCRM flav=ent ONLY
                    array(
                        array(
                            'view' =>
                            array(
                                'type' => 'forecastdetails-record',
                                'label' => 'LBL_DASHLET_FORECAST_NAME',
                            ),
                            'context' => array(
                                'module' => 'Forecasts',
                            ),
                            'width' => 12,
                        ),
                    ),
                    //END SUGARCRM flav=ent ONLY
                    array(
                        array(
                            'view' =>
                            array(
                                'type' => 'forecast-pareto',
                                'label' => 'LBL_DASHLET_FORECAST_PARETO_CHART_NAME',
                            ),
                            'width' => 12,
                        ),
                    ),
                    array(
                        array(
                            'view' => array(
                                'type' => 'planned-activities',
                                'label' => 'LBL_PLANNED_ACTIVITIES_DASHLET',
                            ),
                            'width' => 12,
                        ),
                    ),
                    array(
                        array(
                            'view' =>
                                array(
                                    'name' => 'active-tasks',
                                    'label' => 'LBL_ACTIVE_TASKS_DASHLET',
                                ),
                            'width' => 12,
                        ),
                    ),
                    array(
                        array(
                            'view' => array(
                                'type' => 'history',
                                'label' => 'LBL_HISTORY_DASHLET',
                            ),
                            'width' => 12,
                        ),
                    ),
                    array(
                        array(
                            'view' =>
                            array(
                                'type' => 'attachments',
                                'label' => 'LBL_DASHLET_ATTACHMENTS_NAME',
                                'limit' => '5',
                                'auto_refresh' => '0',
                            ),
                            'context' =>
                            array(
                                'module' => 'Notes',
                                'link' => 'notes',
                            ),
                            'width' => 12,
                        ),
                    ),
                ),
                'width' => 12,
            ),
        ),
    ),
    'name' => 'LBL_DEFAULT_DASHBOARD_TITLE',
);

