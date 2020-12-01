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
          'name' => 'contacts_gtb_matches_1_name',
          'label' => 'LBL_CONTACTS_GTB_MATCHES_1_FROM_CONTACTS_TITLE',
          'enabled' => true,
          'id' => 'CONTACTS_GTB_MATCHES_1CONTACTS_IDA',
          'link' => true,
          'sortable' => false,
          'default' => false,
        ),
        3 => 
        array (
          'name' => 'gtb_positions_gtb_matches_1_name',
          'label' => 'LBL_GTB_POSITIONS_GTB_MATCHES_1_FROM_GTB_POSITIONS_TITLE',
          'enabled' => true,
          'id' => 'GTB_POSITIONS_GTB_MATCHES_1GTB_POSITIONS_IDA',
          'link' => true,
          'sortable' => false,
          'default' => false,
        ),
        4 => 
        array (
          'name' => 'stage',
          'label' => 'LBL_STAGE',
          'enabled' => true,
          'default' => false,
        ),
        5 => 
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
