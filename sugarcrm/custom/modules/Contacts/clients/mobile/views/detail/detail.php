<?php
$viewdefs['Contacts']['mobile']['view']['detail'] =
      array (
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
              0 => 'full_name',
              1 => 'title',
              2 => 
              array (
                'name' => 'department',
                'comment' => 'The department of the contact',
                'label' => 'LBL_DEPARTMENT',
              ),
              3 => 'phone_work',
              4 => 'phone_mobile',
              5 => 'email',
              6 => 'tag',
              7 => 'primary_address_street',
              8 => 'primary_address_city',
              9 => 'primary_address_state',
              10 => 'primary_address_postalcode',
              11 => 'primary_address_country',
              12 => 'assigned_user_name',
              13 => 'team_name',
              14 => 
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
              15 => 
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
            ),
          ),
        ),
);
