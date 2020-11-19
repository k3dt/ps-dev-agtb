<?php
$module_name = 'gtb_positions';
$viewdefs[$module_name]['base']['view']['selection-list'] = array (
  'panels' =>
  array (
    0 =>
    array (
      'label' => 'LBL_PANEL_1',
      'fields' =>
      array (
        0 =>
        array (
          'name' => 'name',
          'label' => 'LBL_NAME',
          'default' => true,
          'enabled' => true,
          'link' => true,
        ),
        1 =>
        array (
          'name' => 'pos_function',
          'label' => 'LBL_POS_FUNCTION',
          'enabled' => true,
          'default' => true,
        ),
        2 =>
        array (
          'name' => 'region',
          'label' => 'LBL_REGION',
          'enabled' => true,
          'default' => true,
        ),
        3 =>
        array (
          'name' => 'org_unit',
          'label' => 'LBL_ORG_UNIT',
          'enabled' => true,
          'default' => true,
        ),
        4 =>
        array (
          'name' => 'location',
          'label' => 'LBL_LOCATION',
          'enabled' => true,
          'default' => true,
        ),
        5 =>
        array (
          'name' => 'process_step',
          'label' => 'LBL_PROCESS_STEP',
          'enabled' => true,
          'default' => true,
        ),
        6 =>
        array (
          'name' => 'assigned_user_name',
          'label' => 'LBL_ASSIGNED_TO_NAME',
          'default' => false,
          'enabled' => true,
          'link' => true,
        ),
        7 =>
        array (
          'label' => 'LBL_DATE_MODIFIED',
          'enabled' => true,
          'default' => false,
          'name' => 'date_modified',
          'readonly' => true,
        ),
        8 =>
        array (
          'name' => 'team_name',
          'label' => 'LBL_TEAM',
          'default' => false,
          'enabled' => true,
        ),
      ),
    ),
  ),
  'orderBy' =>
  array (
    'field' => 'date_modified',
    'direction' => 'desc',
  ),
);
