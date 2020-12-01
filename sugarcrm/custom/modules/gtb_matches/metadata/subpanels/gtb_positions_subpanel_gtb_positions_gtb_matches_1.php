<?php
// created: 2020-11-12 18:33:39
$subpanel_layout['list_fields'] = array (
  'contacts_gtb_matches_1_name' => 
  array (
    'type' => 'relate',
    'link' => true,
    'vname' => 'LBL_CONTACTS_GTB_MATCHES_1_FROM_CONTACTS_TITLE',
    'id' => 'CONTACTS_GTB_MATCHES_1CONTACTS_IDA',
    'width' => 10,
    'default' => true,
    'widget_class' => 'SubPanelDetailViewLink',
    'target_module' => 'Contacts',
    'target_record_key' => 'contacts_gtb_matches_1contacts_ida',
  ),
  'status' => 
  array (
    'type' => 'enum',
    'default' => true,
    'vname' => 'LBL_STATUS',
    'width' => 10,
  ),
  'stage' => 
  array (
    'type' => 'enum',
    'default' => true,
    'vname' => 'LBL_STAGE',
    'width' => 10,
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