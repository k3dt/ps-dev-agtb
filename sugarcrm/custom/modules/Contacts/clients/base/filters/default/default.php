<?php

$viewdefs['Contacts']['base']['filter']['default'] = array (
  'default_filter' => 'all_records',
  'quicksearch_field' => 
  array (
    0 => 
    array (
      0 => 'first_name',
      1 => 'last_name',
    ),
    1 => 'email',
    3 => 'phone_work',
  ),
  'quicksearch_priority' => 2,
  'fields' => 
  array (
    'first_name' => 
    array (
    ),
    'last_name' => 
    array (
    ),
    'title' => 
    array (
    ),
    'lead_source' => 
    array (
    ),
    'address_country' => 
    array (
      'dbFields' => 
      array (
        0 => 'primary_address_country',
        1 => 'alt_address_country',
      ),
      'vname' => 'LBL_COUNTRY',
      'type' => 'text',
    ),
    'gender_c' => 
    array (
    ),
    'target_roles_c' => 
    array (
    ),
    'availability_c' => 
    array (
    ),
    'gtb_cluster_c' => 
    array (
    ),
    'career_discussion_c' => 
    array (
    ),
    'oe_mobility_c' => 
    array (
    ),
    'mobility_comments_c' => 
    array (
    ),
    'org_unit_c' => 
    array (
    ),
    'functional_mobility_c' => 
    array (
    ),
    'function_c' => 
    array (
    ),
      'gtb_country_match_c' =>
          array (
          ),
    'date_entered' => 
    array (
    ),
    'date_modified' => 
    array (
    ),
    'tag' => 
    array (
    ),
    '$owner' => 
    array (
      'predefined_filter' => true,
      'vname' => 'LBL_CURRENT_USER_FILTER',
    ),
    'assigned_user_name' => 
    array (
    ),
    '$favorite' => 
    array (
      'predefined_filter' => true,
      'vname' => 'LBL_FAVORITES_FILTER',
    ),
  ),
);
