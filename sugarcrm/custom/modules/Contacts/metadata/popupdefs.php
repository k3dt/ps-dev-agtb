<?php
$popupMeta = array (
    'moduleMain' => 'Contact',
    'varName' => 'CONTACT',
    'orderBy' => 'contacts.first_name, contacts.last_name',
    'whereClauses' => array (
  'first_name' => 'contacts.first_name',
  'last_name' => 'contacts.last_name',
  'functional_mobility_c' => 'contacts_cstm.functional_mobility_c',
  'target_roles_c' => 'contacts_cstm.target_roles_c',
  'oe_mobility_c' => 'contacts_cstm.oe_mobility_c',
  'org_unit_c' => 'contacts_cstm.org_unit_c',
  'availability_c' => 'contacts_cstm.availability_c',
  'gtb_cluster_c' => 'contacts_cstm.gtb_cluster_c',
  'function_c' => 'contacts_cstm.function_c',
  'primary_address_country' => 'contacts.primary_address_country',
  'title' => 'contacts.title',
  'assigned_user_id' => 'contacts.assigned_user_id',
),
    'searchInputs' => array (
  0 => 'first_name',
  1 => 'last_name',
  4 => 'functional_mobility_c',
  5 => 'target_roles_c',
  6 => 'oe_mobility_c',
  7 => 'org_unit_c',
  8 => 'availability_c',
  9 => 'gtb_cluster_c',
  11 => 'function_c',
  12 => 'primary_address_country',
  13 => 'title',
  14 => 'assigned_user_id',
),
    'create' => array (
  'formBase' => 'ContactFormBase.php',
  'formBaseClass' => 'ContactFormBase',
  'getFormBodyParams' => 
  array (
    0 => '',
    1 => '',
    2 => 'ContactSave',
  ),
  'createButton' => 'LNK_NEW_CONTACT',
),
    'searchdefs' => array (
  'first_name' => 
  array (
    'name' => 'first_name',
    'width' => 10,
  ),
  'last_name' => 
  array (
    'name' => 'last_name',
    'width' => 10,
  ),
  'functional_mobility_c' => 
  array (
    'type' => 'multienum',
    'label' => 'LBL_FUNCTIONAL_MOBILITY_C',
    'width' => 10,
    'name' => 'functional_mobility_c',
  ),
  'target_roles_c' => 
  array (
    'type' => 'text',
    'label' => 'LBL_TARGET_ROLES_C',
    'sortable' => false,
    'width' => 10,
    'name' => 'target_roles_c',
  ),
  'oe_mobility_c' => 
  array (
    'type' => 'text',
    'label' => 'LBL_OE_MOBILITY_C',
    'sortable' => false,
    'width' => 10,
    'name' => 'oe_mobility_c',
  ),
  'org_unit_c' => 
  array (
    'type' => 'varchar',
    'label' => 'LBL_ORG_UNIT_C',
    'width' => 10,
    'name' => 'org_unit_c',
  ),
  'availability_c' => 
  array (
    'type' => 'varchar',
    'label' => 'LBL_AVAILABILITY_C',
    'width' => 10,
    'name' => 'availability_c',
  ),
  'gtb_cluster_c' => 
  array (
    'type' => 'enum',
    'label' => 'LBL_GTB_CLUSTER_C',
    'width' => 10,
    'name' => 'gtb_cluster_c',
  ),
  'function_c' => 
  array (
    'type' => 'enum',
    'label' => 'LBL_FUNCTION_C',
    'width' => 10,
    'name' => 'function_c',
  ),
  'primary_address_country' => 
  array (
    'type' => 'varchar',
    'label' => 'LBL_PRIMARY_ADDRESS_COUNTRY',
    'width' => 10,
    'name' => 'primary_address_country',
  ),
  'title' => 
  array (
    'name' => 'title',
    'width' => 10,
  ),
  'assigned_user_id' => 
  array (
    'name' => 'assigned_user_id',
    'type' => 'enum',
    'label' => 'LBL_ASSIGNED_TO',
    'function' => 
    array (
      'name' => 'get_user_array',
      'params' => 
      array (
        0 => false,
      ),
    ),
    'width' => 10,
  ),
),
    'listviewdefs' => array (
  'NAME' => 
  array (
    'width' => 10,
    'label' => 'LBL_LIST_NAME',
    'link' => true,
    'default' => true,
    'related_fields' => 
    array (
      0 => 'first_name',
      1 => 'last_name',
      2 => 'salutation',
      3 => 'account_name',
      4 => 'account_id',
    ),
    'name' => 'name',
  ),
  'TITLE' => 
  array (
    'width' => 10,
    'label' => 'LBL_LIST_TITLE',
    'default' => true,
    'name' => 'title',
  ),
  'GTB_CLUSTER_C' => 
  array (
    'type' => 'enum',
    'default' => true,
    'label' => 'LBL_GTB_CLUSTER_C',
    'width' => 10,
    'name' => 'gtb_cluster_c',
  ),
  'ORG_UNIT_C' => 
  array (
    'type' => 'varchar',
    'label' => 'LBL_ORG_UNIT_C',
    'width' => 10,
    'default' => true,
    'name' => 'org_unit_c',
  ),
  'FUNCTION_C' => 
  array (
    'type' => 'enum',
    'default' => true,
    'label' => 'LBL_FUNCTION_C',
    'width' => 10,
    'name' => 'function_c',
  ),
),
);
