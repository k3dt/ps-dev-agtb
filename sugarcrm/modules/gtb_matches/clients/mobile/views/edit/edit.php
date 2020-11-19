<?php
$module_name = 'gtb_matches';
$viewdefs[$module_name]['mobile']['view']['edit'] = array (
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
          'name' => 'status',
          'label' => 'LBL_STATUS',
        ),
        2 =>
        array (
          'name' => 'stage',
          'label' => 'LBL_STAGE',
        ),
        3 =>
        array (
          'name' => 'fulfillment',
          'label' => 'LBL_FULFILLMENT',
        ),
        4 =>
        array (
          'name' => 'func_mobility_fulfilled',
          'label' => 'LBL_FUNC_MOBILITY_FULFILLED',
        ),
        5 =>
        array (
          'name' => 'geo_mobility_fulfilled',
          'label' => 'LBL_GEO_MOBILITY_FULFILLED',
        ),
        6 =>
        array (
          'name' => 'oe_mobility_fulfilled',
          'label' => 'LBL_OE_MOBILITY_FULFILLED',
        ),
        7 => 'assigned_user_name',
        8 => 'team_name',
      ),
    ),
  ),
);
