<?php
// created: 2020-11-20 14:49:11
$dictionary["gtb_positions_gtb_matches_1"] = array (
  'true_relationship_type' => 'one-to-many',
  'from_studio' => true,
  'relationships' => 
  array (
    'gtb_positions_gtb_matches_1' => 
    array (
      'lhs_module' => 'gtb_positions',
      'lhs_table' => 'gtb_positions',
      'lhs_key' => 'id',
      'rhs_module' => 'gtb_matches',
      'rhs_table' => 'gtb_matches',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'gtb_positions_gtb_matches_1_c',
      'join_key_lhs' => 'gtb_positions_gtb_matches_1gtb_positions_ida',
      'join_key_rhs' => 'gtb_positions_gtb_matches_1gtb_matches_idb',
    ),
  ),
  'table' => 'gtb_positions_gtb_matches_1_c',
  'fields' => 
  array (
    'id' => 
    array (
      'name' => 'id',
      'type' => 'id',
    ),
    'date_modified' => 
    array (
      'name' => 'date_modified',
      'type' => 'datetime',
    ),
    'deleted' => 
    array (
      'name' => 'deleted',
      'type' => 'bool',
      'default' => 0,
    ),
    'gtb_positions_gtb_matches_1gtb_positions_ida' => 
    array (
      'name' => 'gtb_positions_gtb_matches_1gtb_positions_ida',
      'type' => 'id',
    ),
    'gtb_positions_gtb_matches_1gtb_matches_idb' => 
    array (
      'name' => 'gtb_positions_gtb_matches_1gtb_matches_idb',
      'type' => 'id',
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'idx_gtb_positions_gtb_matches_1_pk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'idx_gtb_positions_gtb_matches_1_ida1_deleted',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'gtb_positions_gtb_matches_1gtb_positions_ida',
        1 => 'deleted',
      ),
    ),
    2 => 
    array (
      'name' => 'idx_gtb_positions_gtb_matches_1_idb2_deleted',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'gtb_positions_gtb_matches_1gtb_matches_idb',
        1 => 'deleted',
      ),
    ),
    3 => 
    array (
      'name' => 'gtb_positions_gtb_matches_1_alt',
      'type' => 'alternate_key',
      'fields' => 
      array (
        0 => 'gtb_positions_gtb_matches_1gtb_matches_idb',
      ),
    ),
  ),
);