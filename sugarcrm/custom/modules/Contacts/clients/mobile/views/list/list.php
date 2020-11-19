<?php
$viewdefs['Contacts']['mobile']['view']['list']['panels'] =
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
                'name' => 'phone_work',
                'label' => 'LBL_OFFICE_PHONE',
                'enabled' => true,
                'default' => false,
              ),
              3 => 
              array (
                'name' => 'phone_mobile',
                'enabled' => true,
                'default' => false,
              ),
              4 => 
              array (
                'name' => 'primary_address_street',
                'enabled' => true,
                'default' => false,
              ),
              5 => 
              array (
                'name' => 'primary_address_city',
                'enabled' => true,
                'default' => false,
              ),
              6 => 
              array (
                'name' => 'primary_address_country',
                'enabled' => true,
                'default' => false,
              ),
              7 => 
              array (
                'name' => 'email',
                'enabled' => true,
                'default' => false,
              ),
            ),
          ),
);
