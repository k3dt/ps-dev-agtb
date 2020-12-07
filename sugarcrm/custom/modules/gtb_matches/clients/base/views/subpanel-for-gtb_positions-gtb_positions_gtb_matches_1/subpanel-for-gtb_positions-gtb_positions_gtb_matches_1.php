<?php
// created: 2020-11-14 07:49:38
$viewdefs['gtb_matches']['base']['view']['subpanel-for-gtb_positions-gtb_positions_gtb_matches_1'] = array (
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
          'name' => 'contacts_gtb_matches_1_name',
          'label' => 'LBL_CONTACTS_GTB_MATCHES_1_FROM_CONTACTS_TITLE',
          'enabled' => true,
          'id' => 'CONTACTS_GTB_MATCHES_1CONTACTS_IDA',
          'link' => true,
          'sortable' => false,
          'default' => true,
        ),
        1 => 
        array (
          'name' => 'stage',
          'label' => 'LBL_STAGE',
          'enabled' => true,
          'default' => true,
        ),
        2 => 
        array (
          'name' => 'description',
          'label' => 'LBL_DESCRIPTION',
          'enabled' => true,
          'sortable' => false,
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