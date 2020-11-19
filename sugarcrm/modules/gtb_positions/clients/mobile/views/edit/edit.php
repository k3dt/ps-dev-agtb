<?php
$module_name = 'gtb_positions';
$viewdefs[$module_name] = 
array (
  'mobile' => 
  array (
    'view' => 
    array (
      'edit' => 
      array (
        'templateMeta' => 
        array (
          'maxColumns' => '1',
          'widths' => 
          array (
            0 => 
            array (
              'label' => '10',
              'field' => '30',
            ),
            1 => 
            array (
              'label' => '10',
              'field' => '30',
            ),
          ),
          'useTabs' => false,
        ),
        'panels' => 
        array (
          0 => 
          array (
            'label' => 'LBL_PANEL_DEFAULT',
            'newTab' => false,
            'panelDefault' => 'expanded',
            'name' => 'LBL_PANEL_DEFAULT',
            'columns' => '1',
            'placeholders' => 1,
            'fields' => 
            array (
              0 => 'name',
              1 => 
              array (
                'name' => 'pos_function',
                'label' => 'LBL_POS_FUNCTION',
              ),
              2 => 
              array (
                'name' => 'region',
                'label' => 'LBL_REGION',
              ),
              3 => 
              array (
                'name' => 'org_unit',
                'label' => 'LBL_ORG_UNIT',
              ),
              4 => 
              array (
                'name' => 'location',
                'label' => 'LBL_LOCATION',
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
              7 => 'tag',
              8 => 
              array (
                'name' => 'process_step',
                'label' => 'LBL_PROCESS_STEP',
              ),
              9 => 
              array (
                'name' => 'real_position',
                'label' => 'LBL_REAL_POSITION',
              ),
              10 => 
              array (
                'name' => 'description',
                'comment' => 'Full text of the note',
                'studio' => 'visible',
                'label' => 'LBL_DESCRIPTION',
              ),
              11 => 'assigned_user_name',
              12 => 'team_name',
            ),
          ),
        ),
      ),
    ),
  ),
);
