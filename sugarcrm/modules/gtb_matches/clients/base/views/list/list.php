<?php
$module_name = 'gtb_matches';
$viewdefs[$module_name]['base']['view']['list'] = array (
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
          'name' => 'status',
          'label' => 'LBL_STATUS',
          'enabled' => true,
          'default' => true,
        ),
        2 =>
        array (
          'name' => 'stage',
          'label' => 'LBL_STAGE',
          'enabled' => true,
          'default' => true,
        ),
        3 =>
        array (
          'name' => 'fulfillment',
          'label' => 'LBL_FULFILLMENT',
          'enabled' => true,
          'default' => true,
        ),
        4 =>
        array (
          'name' => 'func_mobility_fulfilled',
          'label' => 'LBL_FUNC_MOBILITY_FULFILLED',
          'enabled' => true,
          'default' => false,
        ),
        5 =>
        array (
          'name' => 'geo_mobility_fulfilled',
          'label' => 'LBL_GEO_MOBILITY_FULFILLED',
          'enabled' => true,
          'default' => false,
        ),
        6 =>
        array (
          'name' => 'oe_mobility_fulfilled',
          'label' => 'LBL_OE_MOBILITY_FULFILLED',
          'enabled' => true,
          'default' => false,
        ),
        7 =>
        array (
          'name' => 'assigned_user_name',
          'label' => 'LBL_ASSIGNED_TO_NAME',
          'default' => false,
          'enabled' => true,
          'link' => true,
        ),
        8 =>
        array (
          'name' => 'date_modified',
          'enabled' => true,
          'default' => false,
        ),
        9 =>
        array (
          'name' => 'date_entered',
          'enabled' => true,
          'default' => false,
        ),
        10 =>
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
