<?php
// created: 2020-11-20 14:48:32
$dictionary["gtb_matches"]["fields"]["contacts_gtb_matches_1"] = array (
  'name' => 'contacts_gtb_matches_1',
  'type' => 'link',
  'relationship' => 'contacts_gtb_matches_1',
  'source' => 'non-db',
  'module' => 'Contacts',
  'bean_name' => 'Contact',
  'side' => 'right',
  'vname' => 'LBL_CONTACTS_GTB_MATCHES_1_FROM_GTB_MATCHES_TITLE',
  'id_name' => 'contacts_gtb_matches_1contacts_ida',
  'link-type' => 'one',
);
$dictionary["gtb_matches"]["fields"]["contacts_gtb_matches_1_name"] = array (
  'name' => 'contacts_gtb_matches_1_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_CONTACTS_GTB_MATCHES_1_FROM_CONTACTS_TITLE',
  'save' => true,
  'id_name' => 'contacts_gtb_matches_1contacts_ida',
  'link' => 'contacts_gtb_matches_1',
  'table' => 'contacts',
  'module' => 'Contacts',
  'rname' => 'full_name',
  'db_concat_fields' => 
  array (
    0 => 'first_name',
    1 => 'last_name',
  ),
);
$dictionary["gtb_matches"]["fields"]["contacts_gtb_matches_1contacts_ida"] = array (
  'name' => 'contacts_gtb_matches_1contacts_ida',
  'type' => 'id',
  'source' => 'non-db',
  'vname' => 'LBL_CONTACTS_GTB_MATCHES_1_FROM_GTB_MATCHES_TITLE_ID',
  'id_name' => 'contacts_gtb_matches_1contacts_ida',
  'link' => 'contacts_gtb_matches_1',
  'table' => 'contacts',
  'module' => 'Contacts',
  'rname' => 'id',
  'reportable' => false,
  'side' => 'right',
  'massupdate' => false,
  'duplicate_merge' => 'disabled',
  'hideacl' => true,
);
