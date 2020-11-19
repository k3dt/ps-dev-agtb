<?php
$viewdefs['Contacts']['base']['view']['list']['panels'] =
        array (
          0 => 
          array (
            'label' => 'LBL_PANEL_1',
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
                'enabled' => true,
                'default' => true,
              ),
              2 => 
              array (
                'name' => 'department',
                'label' => 'LBL_DEPARTMENT',
                'enabled' => true,
                'default' => true,
              ),
              3 => 
              array (
                'name' => 'phone_mobile',
                'label' => 'LBL_MOBILE_PHONE',
                'enabled' => true,
                'default' => true,
              ),
              4 => 
              array (
                'name' => 'email',
                'enabled' => true,
                'default' => true,
              ),
              5 => 
              array (
                'name' => 'assigned_user_name',
                'label' => 'LBL_LIST_ASSIGNED_USER',
                'id' => 'ASSIGNED_USER_ID',
                'enabled' => true,
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
                'name' => 'date_entered',
                'enabled' => true,
                'default' => false,
                'readonly' => true,
              ),
              8 => 
              array (
                'name' => 'phone_fax',
                'label' => 'LBL_FAX_PHONE',
                'enabled' => true,
                'default' => false,
              ),
              9 => 
              array (
                'name' => 'primary_address_street',
                'label' => 'LBL_PRIMARY_ADDRESS_STREET',
                'enabled' => true,
                'sortable' => false,
                'default' => false,
              ),
              10 => 
              array (
                'name' => 'primary_address_city',
                'label' => 'LBL_PRIMARY_ADDRESS_CITY',
                'enabled' => true,
                'default' => false,
              ),
              11 => 
              array (
                'name' => 'primary_address_postalcode',
                'label' => 'LBL_PRIMARY_ADDRESS_POSTALCODE',
                'enabled' => true,
                'default' => false,
              ),
              12 => 
              array (
                'name' => 'primary_address_country',
                'label' => 'LBL_PRIMARY_ADDRESS_COUNTRY',
                'enabled' => true,
                'default' => false,
              ),
              13 => 
              array (
                'name' => 'phone_work',
                'enabled' => true,
                'default' => false,
              ),
            ),
          ),
);
