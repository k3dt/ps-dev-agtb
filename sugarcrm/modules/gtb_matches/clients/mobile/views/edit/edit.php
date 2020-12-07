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
          'name' => 'contacts_gtb_matches_1_name',
          'label' => 'LBL_CONTACTS_GTB_MATCHES_1_FROM_CONTACTS_TITLE',
        ),
        2 => 
        array (
          'name' => 'gtb_positions_gtb_matches_1_name',
          'label' => 'LBL_GTB_POSITIONS_GTB_MATCHES_1_FROM_GTB_POSITIONS_TITLE',
        ),
        3 =>
        array (
          'name' => 'stage',
          'label' => 'LBL_STAGE',
        ),
        4 =>
        array (
          'name' => 'fulfillment',
          'label' => 'LBL_FULFILLMENT',
        ),
        5 =>
        array (
          'name' => 'description',
          'comment' => 'Full text of the note',
          'label' => 'LBL_DESCRIPTION',
        ),
        6 =>
        array (
          'name' => 'func_mobility_fulfilled',
          'label' => 'LBL_FUNC_MOBILITY_FULFILLED',
        ),
        7 =>
        array (
          'name' => 'geo_mobility_fulfilled',
          'label' => 'LBL_GEO_MOBILITY_FULFILLED',
        ),
        8 =>
        array (
          'name' => 'oe_mobility_fulfilled',
          'label' => 'LBL_OE_MOBILITY_FULFILLED',
        ),
        9 => 'assigned_user_name',
        10 => 'team_name',
      ),
    ),
  ),
);
