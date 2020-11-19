<?php
$module_name = 'gtb_contacts';
$viewdefs[$module_name]['base']['view']['list'] = array (
  'panels' =>
  array (
    0 =>
    array (
      'label' => 'LBL_PANEL_DEFAULT',
      'fields' =>
      array (
        0 =>
        array (
          'name' => 'name',
          'type' => 'fullname',
          'fields' =>
          array (
            0 => 'salutation',
            1 => 'first_name',
            2 => 'last_name',
          ),
          'link' => true,
          'label' => 'LBL_LIST_NAME',
          'enabled' => true,
          'default' => true,
        ),
        1 =>
        array (
          'name' => 'title',
          'label' => 'LBL_TITLE',
          'default' => true,
          'enabled' => true,
        ),
        2 =>
        array (
          'name' => 'phone_mobile',
          'label' => 'LBL_MOBILE_PHONE',
          'default' => true,
          'enabled' => true,
        ),
        3 =>
        array (
          'name' => 'department',
          'label' => 'LBL_DEPARTMENT',
          'enabled' => true,
          'default' => true,
        ),
        4 =>
        array (
          'name' => 'email',
          'label' => 'LBL_EMAIL_ADDRESS',
          'link' => true,
          'default' => true,
          'enabled' => true,
        ),
        5 =>
        array (
          'name' => 'assigned_user_name',
          'label' => 'LBL_ASSIGNED_TO',
          'enabled' => true,
          'related_fields' =>
          array (
            0 => 'assigned_user_id',
          ),
          'id' => 'ASSIGNED_USER_ID',
          'link' => true,
          'default' => true,
        ),
        6 =>
        array (
          'name' => 'date_modified',
          'enabled' => true,
          'default' => true,
        ),
        7 =>
        array (
          'name' => 'phone_work',
          'label' => 'LBL_OFFICE_PHONE',
          'default' => false,
          'enabled' => true,
        ),
        8 =>
        array (
          'name' => 'phone_fax',
          'label' => 'LBL_FAX_PHONE',
          'default' => false,
          'enabled' => true,
        ),
        9 =>
        array (
          'name' => 'date_entered',
          'enabled' => true,
          'default' => false,
        ),
        10 =>
        array (
          'name' => 'primary_address_street',
          'label' => 'LBL_PRIMARY_ADDRESS_STREET',
          'default' => false,
          'enabled' => true,
        ),
      ),
    ),
  ),
);
