<?php
$viewdefs['Forecasts']['base']['layout']['forecasts'] = array(
    'type' => 'forecasts',
    'components' => array(
        array(
            'view' => 'forecastsFilter',
        ),
        array(
            'view' => 'forecastsChart',
        ),
        array(
            'view' => 'forecastsProgress',
        ),
        array(
            'view' => 'forecastsCommitted',
            'collection' => array(
                'name' => 'Committed'
            ),
        ),
        array(
            'view' => 'forecastsWorksheet',

            'contextCollection' => array(
                'module' => 'ForecastWorksheets',
                'name' => 'Worksheet'
            ),
        ),
        array(
            'view' => 'forecastSchedule',
            'contextCollection' => array(
                'module' => 'ForecastSchedule',
                'name' => 'ForecastSchedule',
            )
        ),
        array(
            'view' => 'forecastsWorksheetManager',

            'contextCollection' => array(
                'module' => 'ForecastManagerWorksheets',
                'name' => 'WorksheetManager'
            ),

        ),
        array(
            'layout' => array(
                'type' => 'modal',
                'showEvent' => 'modal:forecastsConfig:open',
            ),
        ),
        array(
            'layout' => 'forecastsHeader'
        ),
    ),
);