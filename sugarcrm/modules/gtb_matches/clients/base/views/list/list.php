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
          'name' => 'contacts_gtb_matches_1_name',
          'label' => 'LBL_CONTACTS_GTB_MATCHES_1_FROM_CONTACTS_TITLE',
          'enabled' => true,
          'id' => 'CONTACTS_GTB_MATCHES_1CONTACTS_IDA',
          'link' => true,
          'sortable' => false,
          'default' => true,
        ),
        2 => 
        array (
          'name' => 'gtb_positions_gtb_matches_1_name',
          'label' => 'LBL_GTB_POSITIONS_GTB_MATCHES_1_FROM_GTB_POSITIONS_TITLE',
          'enabled' => true,
          'id' => 'GTB_POSITIONS_GTB_MATCHES_1GTB_POSITIONS_IDA',
          'link' => true,
          'sortable' => false,
          'default' => true,
        ),
        3 =>
        array (
          'name' => 'stage',
          'label' => 'LBL_STAGE',
          'enabled' => true,
          'default' => true,
        ),
        4 =>
        array (
          'name' => 'fulfillment',
          'label' => 'LBL_FULFILLMENT',
          'enabled' => true,
          'default' => true,
        ),
        5 =>
        array (
          'name' => 'func_mobility_fulfilled',
          'label' => 'LBL_FUNC_MOBILITY_FULFILLED',
          'enabled' => true,
          'default' => false,
        ),
        6 =>
        array (
          'name' => 'geo_mobility_fulfilled',
          'label' => 'LBL_GEO_MOBILITY_FULFILLED',
          'enabled' => true,
          'default' => false,
        ),
        7 =>
        array (
          'name' => 'oe_mobility_fulfilled',
          'label' => 'LBL_OE_MOBILITY_FULFILLED',
          'enabled' => true,
          'default' => false,
        ),
        8 =>
        array (
          'name' => 'assigned_user_name',
          'label' => 'LBL_ASSIGNED_TO_NAME',
          'default' => false,
          'enabled' => true,
          'link' => true,
        ),
        9 =>
        array (
          'name' => 'date_modified',
          'enabled' => true,
          'default' => false,
        ),
        10 =>
        array (
          'name' => 'date_entered',
          'enabled' => true,
          'default' => false,
        ),
        11 =>
        array (
          'name' => 'team_name',
          'label' => 'LBL_TEAM',
          'default' => false,
          'enabled' => true,
        ),
        12 =>
        array (
          'name' => 'description',
          'label' => 'LBL_DESCRIPTION',
          'enabled' => true,
          'sortable' => false,
          'default' => false,
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
