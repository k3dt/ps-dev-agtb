<?php

$dictionary["gtb_matches_activities_1_calls"] = array (
  'relationships' => 
  array (
    'gtb_matches_activities_1_calls' => 
    array (
      'lhs_module' => 'gtb_matches',
      'lhs_table' => 'gtb_matches',
      'lhs_key' => 'id',
      'rhs_module' => 'Calls',
      'rhs_table' => 'calls',
      'relationship_role_column_value' => 'gtb_matches',
      'rhs_key' => 'parent_id',
      'relationship_type' => 'one-to-many',
      'relationship_role_column' => 'parent_type',
    ),
  ),
  'fields' => '',
  'indices' => '',
  'table' => '',
);