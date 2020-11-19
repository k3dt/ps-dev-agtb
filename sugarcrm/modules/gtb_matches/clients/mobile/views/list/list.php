<?php
$module_name = 'gtb_matches';
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
          'name' => 'fulfillment',
          'label' => 'LBL_FULFILLMENT',
          'enabled' => true,
          'default' => true,
        ),
        2 =>
        array (
          'name' => 'stage',
          'label' => 'LBL_STAGE',
          'enabled' => true,
          'default' => false,
        ),
        3 =>
        array (
          'name' => 'status',
          'label' => 'LBL_STATUS',
          'enabled' => true,
          'default' => false,
        ),
      ),
    ),
  ),
);
