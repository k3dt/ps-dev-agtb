<?php
$module_name = 'gtb_contacts';
$viewdefs[$module_name]['mobile']['view']['edit'] = array (
  'templateMeta' =>
  array (
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
          'customCode' => '{html_options name="salutation" options=$fields.salutation.options selected=$fields.salutation.value}&nbsp;<input name="first_name" size="25" maxlength="25" type="text" value="{$fields.first_name.value}">',
          'displayParams' =>
          array (
            'wireless_edit_only' => true,
          ),
        ),
        2 =>
        array (
          'name' => 'last_name',
          'displayParams' =>
          array (
            'wireless_edit_only' => true,
          ),
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
      ),
    ),
  ),
);
