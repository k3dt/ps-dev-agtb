<?php
$module_name = 'gtb_contacts';
$viewdefs[$module_name]['mobile']['view']['detail'] = array (
  'templateMeta' =>
  array (
    'form' =>
    array (
      'buttons' =>
      array (
        0 => 'EDIT',
        1 => 'DUPLICATE',
        2 => 'DELETE',
      ),
    ),
    'maxColumns' => '1',
    'widths' =>
    array (
      0 =>
      array (
        'label' => '10',
        'field' => '30',
      ),
      1 =>
      array (
        'label' => '10',
        'field' => '30',
      ),
    ),
    'useTabs' => false,
  ),
  'panels' =>
  array (
    0 =>
    array (
      'label' => 'LBL_PANEL_DEFAULT',
      'newTab' => false,
      'panelDefault' => 'expanded',
      'name' => 'LBL_PANEL_DEFAULT',
      'columns' => '1',
      'placeholders' => 1,
      'fields' =>
      array (
        0 =>
        array (
          'name' => 'salutation',
          'comment' => 'Contact salutation (e.g., Mr, Ms)',
          'label' => 'LBL_SALUTATION',
        ),
        1 =>
        array (
          'name' => 'first_name',
          'comment' => 'First name of the contact',
          'label' => 'LBL_FIRST_NAME',
        ),
        2 =>
        array (
          'name' => 'last_name',
          'comment' => 'Last name of the contact',
          'label' => 'LBL_LAST_NAME',
        ),
        3 =>
        array (
          'name' => 'title',
          'comment' => 'The title of the contact',
          'label' => 'LBL_TITLE',
        ),
        4 =>
        array (
          'name' => 'department',
          'comment' => 'The department of the contact',
          'label' => 'LBL_DEPARTMENT',
        ),
        5 =>
        array (
          'name' => 'phone_mobile',
          'comment' => 'Mobile phone number of the contact',
          'label' => 'LBL_MOBILE_PHONE',
        ),
        6 =>
        array (
          'name' => 'phone_work',
        ),
        7 =>
        array (
          'name' => 'phone_fax',
          'comment' => 'Contact fax number',
          'label' => 'LBL_FAX_PHONE',
        ),
        8 =>
        array (
          'name' => 'primary_address_street',
          'comment' => 'The street address used for primary address',
          'label' => 'LBL_PRIMARY_ADDRESS_STREET',
        ),
        9 => 'tag',
        10 => 'assigned_user_name',
        11 => 'team_name',
        12 =>
        array (
          'name' => 'date_entered',
          'comment' => 'Date record created',
          'studio' =>
          array (
            'portaleditview' => false,
          ),
          'readonly' => true,
          'label' => 'LBL_DATE_ENTERED',
        ),
        13 =>
        array (
          'name' => 'date_modified',
          'comment' => 'Date record last modified',
          'studio' =>
          array (
            'portaleditview' => false,
          ),
          'readonly' => true,
          'label' => 'LBL_DATE_MODIFIED',
        ),
      ),
    ),
  ),
);
