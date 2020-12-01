<?php

$dictionary["gtb_contacts_activities_1_notes"] = array (
  'relationships' => 
  array (
    'gtb_contacts_activities_1_notes' => 
    array (
      'lhs_module' => 'gtb_contacts',
      'lhs_table' => 'gtb_contacts',
      'lhs_key' => 'id',
      'rhs_module' => 'Notes',
      'rhs_table' => 'notes',
      'relationship_role_column_value' => 'gtb_contacts',
      'rhs_key' => 'parent_id',
      'relationship_type' => 'one-to-many',
      'relationship_role_column' => 'parent_type',
    ),
  ),
  'fields' => '',
  'indices' => '',
  'table' => '',
);