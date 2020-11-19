<?php
$popupMeta = array (
    'moduleMain' => 'gtb_contacts',
    'varName' => 'gtb_contacts',
    'orderBy' => 'gtb_contacts.first_name, gtb_contacts.last_name',
    'whereClauses' => array (
  'first_name' => 'gtb_contacts.first_name',
  'last_name' => 'gtb_contacts.last_name',
),
    'searchInputs' => array (
  0 => 'first_name',
  1 => 'last_name',
),
    'listviewdefs' => array (
  'NAME' => 
  array (
    'type' => 'fullname',
    'label' => 'LBL_NAME',
    'width' => 10,
    'default' => true,
    'name' => 'name',
  ),
  'TITLE' => 
  array (
    'type' => 'varchar',
    'label' => 'LBL_TITLE',
    'width' => 10,
    'default' => true,
    'name' => 'title',
  ),
  'PHONE_MOBILE' => 
  array (
    'type' => 'phone',
    'label' => 'LBL_MOBILE_PHONE',
    'width' => 10,
    'default' => true,
    'name' => 'phone_mobile',
  ),
  'DEPARTMENT' => 
  array (
    'type' => 'varchar',
    'label' => 'LBL_DEPARTMENT',
    'width' => 10,
    'default' => true,
    'name' => 'department',
  ),
  'EMAIL' => 
  array (
    'type' => 'email',
    'studio' => 
    array (
      'visible' => true,
      'searchview' => true,
      'editview' => true,
      'editField' => true,
    ),
    'link' => 'email_addresses_primary',
    'label' => 'LBL_EMAIL_ADDRESS',
    'sortable' => false,
    'width' => 10,
    'default' => true,
    'name' => 'email',
  ),
),
);
