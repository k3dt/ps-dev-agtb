<?php
$module_name = 'gtb_positions';
$viewdefs[$module_name]['base']['view']['list'] =
      array (
        'panels' => 
        array (
          0 => 
          array (
            'label' => 'LBL_PANEL_1',
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'name',
                'label' => 'LBL_NAME',
                'default' => true,
                'enabled' => true,
                'link' => true,
              ),
              1 => 
              array (
                'name' => 'status',
                'label' => 'LBL_STATUS',
                'enabled' => true,
                'default' => true,
              ),
              2 => 
              array (
                'name' => 'process_step',
                'label' => 'LBL_PROCESS_STEP',
                'enabled' => true,
                'default' => true,
              ),
              3 => 
              array (
                'name' => 'pos_function',
                'label' => 'LBL_POS_FUNCTION',
                'enabled' => true,
                'default' => true,
              ),
              4 => 
              array (
                'name' => 'region',
                'label' => 'LBL_REGION',
                'enabled' => true,
                'default' => true,
              ),
              5 => 
              array (
                'name' => 'org_unit',
                'label' => 'LBL_ORG_UNIT',
                'enabled' => true,
                'default' => true,
              ),
              6 => 
              array (
                'name' => 'gtb_cluster',
                'label' => 'LBL_GTB_CLUSTER',
                'enabled' => true,
                'default' => true,
              ),
              7 => 
              array (
                'name' => 'gtb_source',
                'label' => 'LBL_GTB_SOURCE',
                'enabled' => true,
                'default' => false,
              ),
              8 => 
              array (
                'name' => 'assigned_user_name',
                'label' => 'LBL_ASSIGNED_TO_NAME',
                'default' => false,
                'enabled' => true,
                'link' => true,
              ),
              9 => 
              array (
                'name' => 'date_modified',
                'enabled' => true,
                'default' => false,
              ),
              10 => 
              array (
                'name' => 'date_entered',
                'enabled' => true,
                'default' => false,
              ),
              11 => 
              array (
                'name' => 'real_position',
                'label' => 'LBL_REAL_POSITION',
                'enabled' => true,
                'default' => false,
              ),
              12 => 
              array (
                'name' => 'location',
                'label' => 'LBL_LOCATION',
                'enabled' => true,
                'default' => false,
              ),
              13 => 
              array (
                'name' => 'gtb_contacts_gtb_positions_1_name',
                'label' => 'LBL_GTB_CONTACTS_GTB_POSITIONS_1_FROM_GTB_CONTACTS_TITLE',
                'enabled' => true,
                'id' => 'GTB_CONTACTS_GTB_POSITIONS_1GTB_CONTACTS_IDA',
                'link' => true,
                'sortable' => false,
                'default' => false,
              ),
              14 => 
              array (
                'name' => 'country',
                'label' => 'LBL_COUNTRY',
                'enabled' => true,
                'default' => false,
              ),
            ),
          ),
        ),
        'orderBy' => 
        array (
          'field' => 'date_modified',
          'direction' => 'desc',
        ),
);
