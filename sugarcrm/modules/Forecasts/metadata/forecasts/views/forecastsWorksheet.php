<?php
$viewdefs['Forecasts']['forecasts']['view']['forecastsWorksheet'] = array(
    'panels' =>
    array(
        0 =>
        array(
            'label' => 'LBL_PANEL_1',
            'fields' =>
            array(
                array(
                    'name' => 'forecast',
                    'type' => 'bool',
                    'label' => 'LBL_FORECAST',
                    'default' => true,
                    'enabled' => true,
                ),
                array(
                    'name' => 'commit_stage',
                    'type' => 'enum',
                    'options' => 'commit_stage_dom',
                    'label' => 'LBL_FORECAST',
                    'default' => true,
                    'enabled' => true,
                ),
                array(
                    'name' => 'name',
                    'label' => 'LBL_NAME',
                    'link' => true,
                    'route' =>
                    array(
                        'module'=>'Opportunities',
                        'action'=>'DetailView',
                        'recordID'=>'primaryid'
                    ),
                    'default' => true,
                    'enabled' => true,
                ),

                array(
                    'name' => 'date_closed',
                    'label' => 'LBL_DATE_CLOSED',
                    'default' => true,
                    'enabled' => true,
                ),

                array(
                    'name' => 'sales_stage',
                    'label' => 'LBL_SALES_STAGE',
                    'default' => true,
                    'enabled' => true,
                ),

                array(
                    'name' => 'probability',
                    'label' => 'LBL_PROBABILITY',
                    'default' => true,
                    'enabled' => true,
                    'clickToEdit' => true
                ),

                array(
                    'name' => 'amount',
                    'label' => 'LBL_AMOUNT',
                    'default' => true,
                    'enabled' => true
                ),

                array(
                    'name' => 'best_case',
                    'label' => 'LBL_BEST_CASE',
                    'type' => 'int',
                    'default' => true,
                    'enabled' => true,
                    'clickToEdit' => true
                ),

                array(
                    'name' => 'likely_case',
                    'label' => 'LBL_LIKELY_CASE',
                    'type' => 'int',
                    'default' => true,
                    'enabled' => true,
                    'clickToEdit' => true
                ),
            ),
        ),
    ),
);
