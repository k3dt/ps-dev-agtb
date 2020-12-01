<?php
// created: 2020-11-12 06:18:24
$dictionary["gtb_positions"]["fields"]["gtb_contacts_gtb_positions_1"] = array (
  'name' => 'gtb_contacts_gtb_positions_1',
  'type' => 'link',
  'relationship' => 'gtb_contacts_gtb_positions_1',
  'source' => 'non-db',
  'module' => 'gtb_contacts',
  'bean_name' => 'gtb_contacts',
  'side' => 'right',
  'vname' => 'LBL_GTB_CONTACTS_GTB_POSITIONS_1_FROM_GTB_POSITIONS_TITLE',
  'id_name' => 'gtb_contacts_gtb_positions_1gtb_contacts_ida',
  'link-type' => 'one',
);
$dictionary["gtb_positions"]["fields"]["gtb_contacts_gtb_positions_1_name"] = array (
  'name' => 'gtb_contacts_gtb_positions_1_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_GTB_CONTACTS_GTB_POSITIONS_1_FROM_GTB_CONTACTS_TITLE',
  'save' => true,
  'id_name' => 'gtb_contacts_gtb_positions_1gtb_contacts_ida',
  'link' => 'gtb_contacts_gtb_positions_1',
  'table' => 'gtb_contacts',
  'module' => 'gtb_contacts',
  'rname' => 'full_name',
  'db_concat_fields' => 
  array (
    0 => 'first_name',
    1 => 'last_name',
  ),
);
$dictionary["gtb_positions"]["fields"]["gtb_contacts_gtb_positions_1gtb_contacts_ida"] = array (
  'name' => 'gtb_contacts_gtb_positions_1gtb_contacts_ida',
  'type' => 'id',
  'source' => 'non-db',
  'vname' => 'LBL_GTB_CONTACTS_GTB_POSITIONS_1_FROM_GTB_POSITIONS_TITLE_ID',
  'id_name' => 'gtb_contacts_gtb_positions_1gtb_contacts_ida',
  'link' => 'gtb_contacts_gtb_positions_1',
  'table' => 'gtb_contacts',
  'module' => 'gtb_contacts',
  'rname' => 'id',
  'reportable' => false,
  'side' => 'right',
  'massupdate' => false,
  'duplicate_merge' => 'disabled',
  'hideacl' => true,
);
