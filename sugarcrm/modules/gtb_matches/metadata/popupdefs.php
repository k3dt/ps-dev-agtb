<?php
$popupMeta = array (
    'moduleMain' => 'gtb_matches',
    'varName' => 'gtb_matches',
    'orderBy' => 'gtb_matches.name',
    'whereClauses' => array (
  'name' => 'gtb_matches.name',
),
    'searchInputs' => array (
  0 => 'gtb_matches_number',
  1 => 'name',
  2 => 'priority',
  3 => 'status',
),
    'listviewdefs' => array (
  'NAME' => 
  array (
    'type' => 'name',
    'default' => true,
    'label' => 'LBL_NAME',
    'width' => 10,
    'name' => 'name',
  ),
  'CONTACTS_GTB_MATCHES_1_NAME' => 
  array (
    'type' => 'relate',
    'link' => true,
    'label' => 'LBL_CONTACTS_GTB_MATCHES_1_FROM_CONTACTS_TITLE',
    'id' => 'CONTACTS_GTB_MATCHES_1CONTACTS_IDA',
    'width' => 10,
    'default' => true,
  ),
  'GTB_POSITIONS_GTB_MATCHES_1_NAME' => 
  array (
    'type' => 'relate',
    'link' => true,
    'label' => 'LBL_GTB_POSITIONS_GTB_MATCHES_1_FROM_GTB_POSITIONS_TITLE',
    'id' => 'GTB_POSITIONS_GTB_MATCHES_1GTB_POSITIONS_IDA',
    'width' => 10,
    'default' => true,
  ),
  'TEAM_NAME' => 
  array (
    'type' => 'relate',
    'link' => true,
    'studio' => 
    array (
      'portallistview' => false,
      'portalrecordview' => false,
    ),
    'label' => 'LBL_TEAMS',
    'id' => 'TEAM_ID',
    'width' => 10,
    'default' => true,
  ),
  'ASSIGNED_USER_NAME' => 
  array (
    'link' => true,
    'type' => 'relate',
    'related_fields' => 
    array (
      0 => 'assigned_user_id',
    ),
    'label' => 'LBL_ASSIGNED_TO',
    'id' => 'ASSIGNED_USER_ID',
    'width' => 10,
    'default' => true,
  ),
  'DATE_MODIFIED' => 
  array (
    'type' => 'datetime',
    'studio' => 
    array (
      'portaleditview' => false,
    ),
    'readonly' => true,
    'label' => 'LBL_DATE_MODIFIED',
    'width' => 10,
    'default' => true,
  ),
),
);
