<?php

$viewdefs['base']['view']['candidates-matching-dashlet'] = array(
    'template' => 'list',
    'dashlets' => array(
        array(
            'label' => 'LBL_CANDIDATES_MATCHING_DASHLET',
            'description' => 'LBL_CANDIDATES_MATCHING_DASHLET_DESCRIPTION',
            'config' => array(
                'module' => 'Contacts',
                'limit' => '5',
                'display_columns' => array(
                    'name',
                    'title',
                    'org_unit_c',
                    'function_c',
                    'gtb_cluster_c',
                ),
                'filter_id' => 'all_records',
                'auto_refresh' => '5'
            ),
            'preview' => array(),
            'filter' => array(
                'module' => array(
                    'gtb_positions',
                ),
                'view' => array(
                    'record',
                ),
            )
        ),
    ),
    'panels' => array(
        array(
            'name' => 'dashlet_settings',
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => array(
                array(
                    'name' => 'module',
                    'label' => 'LBL_MODULE',
                    'type' => 'enum',
                    'span' => 12,
                    'sort_alpha' => true,
                ),
                array(
                    'name' => 'display_columns',
                    'label' => 'LBL_COLUMNS',
                    'type' => 'enum',
                    'isMultiSelect' => true,
                    'ordered' => true,
                    'span' => 12,
                    'hasBlank' => true,
                    'options' => array('' => ''),
                ),
                array(
                    'name' => 'limit',
                    'label' => 'LBL_DASHLET_CONFIGURE_DISPLAY_ROWS',
                    'type' => 'enum',
                    'options' => 'dashlet_limit_options',
                ),
                array(
                    'name' => 'auto_refresh',
                    'label' => 'Auto Refresh',
                    'type' => 'enum',
                    'options' => 'sugar7_dashlet_auto_refresh_options',
                ),
                array(
                    'name' => 'intelligent',
                    'label' => 'LBL_DASHLET_CONFIGURE_INTELLIGENT',
                    'type' => 'bool',
                ),
                array(
                    'name' => 'linked_fields',
                    'label' => 'LBL_DASHLET_CONFIGURE_LINKED',
                    'type' => 'enum',
                    'required' => true
                ),
            ),
        ),
    ),
);
