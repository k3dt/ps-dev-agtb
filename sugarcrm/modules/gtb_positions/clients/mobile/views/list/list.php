<?php
$module_name = 'gtb_positions';
$viewdefs[$module_name]['mobile']['view']['list'] = array (
  'panels' =>
  array (
    0 =>
    array (
      'label' => 'LBL_PANEL_DEFAULT',
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
          'default' => false,
        ),
        3 =>
        array (
          'name' => 'org_unit',
          'label' => 'LBL_ORG_UNIT',
          'enabled' => true,
          'default' => false,
        ),
        4 =>
        array (
          'name' => 'location',
          'label' => 'LBL_LOCATION',
          'enabled' => true,
          'default' => false,
        ),
        5 =>
        array (
          'name' => 'gtb_cluster',
          'label' => 'LBL_GTB_CLUSTER',
          'enabled' => true,
          'default' => false,
        ),
        6 =>
        array (
          'name' => 'status',
          'label' => 'LBL_STATUS',
          'enabled' => true,
          'default' => false,
        ),
        7 =>
        array (
          'name' => 'process_step',
          'label' => 'LBL_PROCESS_STEP',
          'enabled' => true,
          'default' => false,
        ),
      ),
    ),
  ),
);
