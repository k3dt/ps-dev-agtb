<?php
$module_name = 'gtb_positions';
$viewdefs[$module_name]['base']['view']['preview'] =
      array (
        'panels' => 
        array (
          0 => 
          array (
            'name' => 'panel_header',
            'label' => 'LBL_RECORD_HEADER',
            'header' => true,
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'picture',
                'type' => 'avatar',
                'width' => 42,
                'height' => 42,
                'dismiss_label' => true,
                'readonly' => true,
                'size' => 'large',
              ),
              1 => 'name',
            ),
          ),
          1 => 
          array (
            'name' => 'panel_body',
            'label' => 'LBL_RECORD_BODY',
            'columns' => 2,
            'placeholders' => true,
            'newTab' => true,
            'panelDefault' => 'expanded',
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'pos_function',
                'label' => 'LBL_POS_FUNCTION',
              ),
              1 => 
              array (
                'name' => 'region',
                'label' => 'LBL_REGION',
              ),
              2 => 
              array (
                'name' => 'country',
                'label' => 'LBL_COUNTRY',
              ),
              3 => 
              array (
                'name' => 'location',
                'label' => 'LBL_LOCATION',
              ),
              4 => 
              array (
                'name' => 'org_unit',
                'label' => 'LBL_ORG_UNIT',
              ),
              5 => 
              array (
                'name' => 'gtb_cluster',
                'label' => 'LBL_GTB_CLUSTER',
              ),
              6 => 
              array (
                'name' => 'gtb_source',
                'label' => 'LBL_GTB_SOURCE',
              ),
              7 => 
              array (
                'name' => 'gtb_contacts_gtb_positions_1_name',
              ),
              8 => 
              array (
                'name' => 'tag',
              ),
              9 => 
              array (
                'name' => 'status',
                'label' => 'LBL_STATUS',
              ),
              10 => 
              array (
                'name' => 'process_step',
                'label' => 'LBL_PROCESS_STEP',
              ),
              11 => 
              array (
                'name' => 'real_position',
                'label' => 'LBL_REAL_POSITION',
              ),
              12 => 
              array (
                'name' => 'reason_not_filled',
                'label' => 'LBL_REASON_NOT_FILLED',
              ),
              13 => 
              array (
                'name' => 'description',
              ),
            ),
          ),
          2 => 
          array (
            'name' => 'panel_hidden',
            'label' => 'LBL_SHOW_MORE',
            'hide' => true,
            'columns' => 2,
            'placeholders' => true,
            'newTab' => true,
            'panelDefault' => 'expanded',
            'fields' => 
            array (
              0 => 'assigned_user_name',
              1 => 
              array (
                'name' => 'date_entered_by',
                'readonly' => true,
                'inline' => true,
                'type' => 'fieldset',
                'label' => 'LBL_DATE_ENTERED',
                'fields' => 
                array (
                  0 => 
                  array (
                    'name' => 'date_entered',
                  ),
                  1 => 
                  array (
                    'type' => 'label',
                    'default_value' => 'LBL_BY',
                  ),
                  2 => 
                  array (
                    'name' => 'created_by_name',
                  ),
                ),
              ),
              2 => 'team_name',
              3 => 
              array (
                'name' => 'date_modified_by',
                'readonly' => true,
                'inline' => true,
                'type' => 'fieldset',
                'label' => 'LBL_DATE_MODIFIED',
                'fields' => 
                array (
                  0 => 
                  array (
                    'name' => 'date_modified',
                  ),
                  1 => 
                  array (
                    'type' => 'label',
                    'default_value' => 'LBL_BY',
                  ),
                  2 => 
                  array (
                    'name' => 'modified_by_name',
                  ),
                ),
              ),
            ),
          ),
        ),
        'templateMeta' => 
        array (
          'maxColumns' => 1,
          'useTabs' => true,
        ),
);
