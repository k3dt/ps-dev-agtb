<?php

$viewdefs['Cases']['base']['filter']['default'] = array(
    'default_filter' => 'all_records',
    'fields' => array(
        'name' => array(),
        'account_name' => array(
            'dbFields' => array(
                'accounts.name',
            ),
            'type' => 'text',
            'vname' => 'LBL_ACCOUNT_NAME',
        ),
        'status' => array(),
        'priority' => array(),
        'case_number' => array(),
        'date_entered' => array(),
        'date_modified' => array(),
        'assigned_user_name' => array(),
        '$owner' => array(
            'predefined_filter' => true,
            'vname' => 'LBL_CURRENT_USER_FILTER',
        ),
        '$favorite' => array(
            'predefined_filter' => true,
            'vname' => 'LBL_FAVORITES_FILTER',
        ),
    ),
);
