<?php
$popupMeta = array (
    'moduleMain' => 'gtb_positions',
    'varName' => 'gtb_positions',
    'orderBy' => 'gtb_positions.name',
    'whereClauses' => array (
  'name' => 'gtb_positions.name',
),
    'searchInputs' => array (
  0 => 'gtb_positions_number',
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
  'POS_FUNCTION' =>
  array (
    'type' => 'enum',
    'default' => true,
    'label' => 'LBL_POS_FUNCTION',
    'width' => 10,
    'name' => 'pos_function',
  ),
  'REGION' =>
  array (
    'type' => 'enum',
    'default' => true,
    'label' => 'LBL_REGION',
    'width' => 10,
    'name' => 'region',
  ),
  'ORG_UNIT' =>
  array (
    'type' => 'varchar',
    'default' => true,
    'label' => 'LBL_ORG_UNIT',
    'width' => 10,
    'name' => 'org_unit',
  ),
  'LOCATION' =>
  array (
    'type' => 'varchar',
    'default' => true,
    'label' => 'LBL_LOCATION',
    'width' => 10,
    'name' => 'location',
  ),
  'PROCESS_STEP' =>
  array (
    'type' => 'enum',
    'default' => true,
    'label' => 'LBL_PROCESS_STEP',
    'width' => 10,
    'name' => 'process_step',
  ),
),
);
