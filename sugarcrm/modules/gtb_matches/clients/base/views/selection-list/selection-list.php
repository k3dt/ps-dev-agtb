<?php
$module_name = 'gtb_matches';
$viewdefs[$module_name]['base']['view']['selection-list'] = array (
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
          'name' => 'team_name',
          'label' => 'LBL_TEAM',
          'default' => true,
          'enabled' => true,
        ),
        4 => 
        array (
          'name' => 'assigned_user_name',
          'label' => 'LBL_ASSIGNED_TO_NAME',
          'default' => true,
          'enabled' => true,
          'link' => true,
        ),
        5 => 
        array (
          'label' => 'LBL_DATE_MODIFIED',
          'enabled' => true,
          'default' => true,
          'name' => 'date_modified',
          'readonly' => true,
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
