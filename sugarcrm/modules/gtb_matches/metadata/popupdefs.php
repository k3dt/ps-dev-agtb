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
  'STATUS' => 
  array (
    'type' => 'enum',
    'default' => true,
    'label' => 'LBL_STATUS',
    'width' => 10,
    'name' => 'status',
  ),
  'STAGE' => 
  array (
    'type' => 'enum',
    'default' => true,
    'label' => 'LBL_STAGE',
    'width' => 10,
    'name' => 'stage',
  ),
  'FULFILLMENT' => 
  array (
    'type' => 'enum',
    'default' => true,
    'label' => 'LBL_FULFILLMENT',
    'width' => 10,
    'name' => 'fulfillment',
  ),
),
);
