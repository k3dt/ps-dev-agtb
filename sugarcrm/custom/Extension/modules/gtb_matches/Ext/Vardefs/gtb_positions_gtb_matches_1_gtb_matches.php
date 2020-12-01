<?php
// created: 2020-11-20 14:49:11
$dictionary["gtb_matches"]["fields"]["gtb_positions_gtb_matches_1"] = array (
  'name' => 'gtb_positions_gtb_matches_1',
  'type' => 'link',
  'relationship' => 'gtb_positions_gtb_matches_1',
  'source' => 'non-db',
  'module' => 'gtb_positions',
  'bean_name' => 'gtb_positions',
  'side' => 'right',
  'vname' => 'LBL_GTB_POSITIONS_GTB_MATCHES_1_FROM_GTB_MATCHES_TITLE',
  'id_name' => 'gtb_positions_gtb_matches_1gtb_positions_ida',
  'link-type' => 'one',
);
$dictionary["gtb_matches"]["fields"]["gtb_positions_gtb_matches_1_name"] = array (
  'name' => 'gtb_positions_gtb_matches_1_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_GTB_POSITIONS_GTB_MATCHES_1_FROM_GTB_POSITIONS_TITLE',
  'save' => true,
  'id_name' => 'gtb_positions_gtb_matches_1gtb_positions_ida',
  'link' => 'gtb_positions_gtb_matches_1',
  'table' => 'gtb_positions',
  'module' => 'gtb_positions',
  'rname' => 'name',
);
$dictionary["gtb_matches"]["fields"]["gtb_positions_gtb_matches_1gtb_positions_ida"] = array (
  'name' => 'gtb_positions_gtb_matches_1gtb_positions_ida',
  'type' => 'id',
  'source' => 'non-db',
  'vname' => 'LBL_GTB_POSITIONS_GTB_MATCHES_1_FROM_GTB_MATCHES_TITLE_ID',
  'id_name' => 'gtb_positions_gtb_matches_1gtb_positions_ida',
  'link' => 'gtb_positions_gtb_matches_1',
  'table' => 'gtb_positions',
  'module' => 'gtb_positions',
  'rname' => 'id',
  'reportable' => false,
  'side' => 'right',
  'massupdate' => false,
  'duplicate_merge' => 'disabled',
  'hideacl' => true,
);
