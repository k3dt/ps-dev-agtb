<?php
// created: 2020-11-14 07:49:25
$subpanel_layout['list_fields'] = array (
  'gtb_positions_gtb_matches_1_name' => 
  array (
    'type' => 'relate',
    'link' => true,
    'vname' => 'LBL_GTB_POSITIONS_GTB_MATCHES_1_FROM_GTB_POSITIONS_TITLE',
    'id' => 'GTB_POSITIONS_GTB_MATCHES_1GTB_POSITIONS_IDA',
    'width' => 10,
    'default' => true,
    'widget_class' => 'SubPanelDetailViewLink',
    'target_module' => 'gtb_positions',
    'target_record_key' => 'gtb_positions_gtb_matches_1gtb_positions_ida',
  ),
  'stage' => 
  array (
    'type' => 'enum',
    'default' => true,
    'vname' => 'LBL_STAGE',
    'width' => 10,
  ),
  'description' => 
  array (
    'type' => 'text',
    'vname' => 'LBL_DESCRIPTION',
    'sortable' => false,
    'width' => 10,
    'default' => true,
  ),
  'fulfillment' => 
  array (
    'type' => 'enum',
    'default' => true,
    'vname' => 'LBL_FULFILLMENT',
    'width' => 10,
  ),
  'date_modified' => 
  array (
    'vname' => 'LBL_DATE_MODIFIED',
    'width' => 10,
    'default' => true,
  ),
);