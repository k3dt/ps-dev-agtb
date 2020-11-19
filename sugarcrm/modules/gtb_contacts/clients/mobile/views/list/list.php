<?php
$module_name = 'gtb_contacts';
$viewdefs[$module_name]['mobile']['view']['list'] = array (
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
          'label' => 'LBL_NAME',
          'default' => true,
          'enabled' => true,
          'link' => true,
          'related_fields' =>
          array (
            0 => 'first_name',
            1 => 'last_name',
            2 => 'salutation',
          ),
        ),
        1 =>
        array (
          'name' => 'title',
          'label' => 'LBL_TITLE',
          'enabled' => true,
          'default' => true,
        ),
        2 =>
        array (
          'name' => 'department',
          'label' => 'LBL_DEPARTMENT',
          'enabled' => true,
          'default' => false,
        ),
        3 =>
        array (
          'name' => 'phone_mobile',
          'label' => 'LBL_MOBILE_PHONE',
          'enabled' => true,
          'default' => false,
        ),
        4 =>
        array (
          'name' => 'phone_work',
          'label' => 'LBL_OFFICE_PHONE',
          'enabled' => true,
          'default' => false,
        ),
      ),
    ),
  ),
);
