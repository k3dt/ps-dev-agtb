<?php
// created: 2020-11-12 06:22:02
$viewdefs['gtb_positions']['base']['view']['subpanel-for-gtb_contacts-gtb_contacts_gtb_positions_1'] = array (
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
          'label' => 'LBL_NAME',
          'enabled' => true,
          'default' => true,
          'name' => 'name',
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
          'name' => 'status',
          'label' => 'LBL_STATUS',
          'enabled' => true,
          'default' => true,
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