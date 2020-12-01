<?php
// created: 2020-11-12 18:34:37
$viewdefs['gtb_matches']['base']['view']['subpanel-for-contacts-contacts_gtb_matches_1'] = array (
  'panels' => 
  array (
    0 => 
    array (
      'name' => 'panel_header',
      'label' => 'LBL_PANEL_1',
      'fields' => 
      array (
        0 => 
        array (
          'name' => 'gtb_positions_gtb_matches_1_name',
          'label' => 'LBL_GTB_POSITIONS_GTB_MATCHES_1_FROM_GTB_POSITIONS_TITLE',
          'enabled' => true,
          'id' => 'GTB_POSITIONS_GTB_MATCHES_1GTB_POSITIONS_IDA',
          'link' => true,
          'sortable' => false,
          'default' => true,
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
          'label' => 'LBL_DATE_MODIFIED',
          'enabled' => true,
          'default' => true,
          'name' => 'date_modified',
        ),
      ),
    ),
  ),
  'orderBy' => 
  array (
    'field' => 'date_modified',
    'direction' => 'desc',
  ),
  'type' => 'subpanel-list',
);