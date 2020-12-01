<?php
$viewdefs['Contacts']['base']['view']['preview'] = array (
        'panels' => 
        array (
          0 => 
          array (
            'name' => 'panel_header',
            'header' => true,
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'picture',
                'type' => 'avatar',
                'size' => 'large',
                'dismiss_label' => true,
              ),
              1 => 
              array (
                'name' => 'name',
                'label' => 'LBL_NAME',
                'dismiss_label' => true,
                'type' => 'fullname',
                'fields' => 
                array (
                  0 => 'salutation',
                  1 => 'first_name',
                  2 => 'last_name',
                ),
              ),
            ),
          ),
          1 => 
          array (
            'name' => 'panel_body',
            'label' => 'LBL_RECORD_BODY',
            'columns' => 2,
            'placeholders' => true,
            'newTab' => false,
            'panelDefault' => 'expanded',
            'fields' => 
            array (
              0 => 'title',
              1 => 
              array (
                'name' => 'gender_c',
                'label' => 'LBL_GENDER_C',
              ),
              2 => 
              array (
                'name' => 'gtb_cluster_c',
                'label' => 'LBL_GTB_CLUSTER_C',
              ),
              3 => 
              array (
                'name' => 'org_unit_c',
                'label' => 'LBL_ORG_UNIT_C',
              ),
              4 => 
              array (
                'name' => 'function_c',
                'label' => 'LBL_FUNCTION_C',
              ),
              5 => 
              array (
                'name' => 'primary_address_country',
                'label' => 'LBL_PRIMARY_ADDRESS_COUNTRY',
              ),
              6 => 'phone_mobile',
              7 => 
              array (
                'name' => 'email',
              ),
              8 => 'lead_source',
              9 => 
              array (
                'name' => 'tag',
              ),
              10 => 
              array (
                'name' => 'commentlog',
                'displayParams' => 
                array (
                  'type' => 'commentlog',
                  'fields' => 
                  array (
                    0 => 'entry',
                    1 => 'date_entered',
                    2 => 'created_by_name',
                  ),
                  'max_num' => 100,
                ),
                'studio' => 
                array (
                  'listview' => false,
                  'recordview' => true,
                  'wirelesseditview' => false,
                  'wirelessdetailview' => true,
                  'wirelesslistview' => false,
                  'wireless_basic_search' => false,
                  'wireless_advanced_search' => false,
                ),
                'label' => 'LBL_COMMENTLOG',
                'span' => 12,
              ),
              11 => 
              array (
                'name' => 'functional_mobility_c',
                'label' => 'LBL_FUNCTIONAL_MOBILITY_C',
                'span' => 12,
              ),
              12 => 
              array (
                'name' => 'oe_mobility_c',
                'label' => 'LBL_OE_MOBILITY_C',
                'span' => 12,
              ),
              13 => 
              array (
                'name' => 'mobility_comments_c',
                'label' => 'LBL_MOBILITY_COMMENTS_C',
                'span' => 12,
              ),
              14 => 
              array (
                'name' => 'target_roles_c',
                'label' => 'LBL_TARGET_ROLES_C',
                'span' => 12,
              ),
              15 => 
              array (
                'name' => 'career_discussion_c',
                'label' => 'LBL_CAREER_DISCUSSION_C',
              ),
              16 => 
              array (
                'name' => 'availability_c',
                'label' => 'LBL_AVAILABILITY_C',
              ),
            ),
          ),
          2 => 
          array (
            'columns' => 2,
            'name' => 'LBL_RECORDVIEW_PANEL3',
            'label' => 'LBL_RECORDVIEW_PANEL3',
            'hide' => false,
            'placeholders' => true,
            'newTab' => false,
            'panelDefault' => 'expanded',
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'geo_mobility_region_1_c',
                'label' => 'LBL_GEO_MOBILITY_REGION_1_C',
              ),
              1 => 
              array (
                'name' => 'geo_mobility_country_1_c',
                'label' => 'LBL_GEO_MOBILITY_COUNTRY_1_C',
              ),
              2 => 
              array (
                'name' => 'geo_mobility_region_2_c',
                'label' => 'LBL_GEO_MOBILITY_REGION_2_C',
              ),
              3 => 
              array (
                'name' => 'geo_mobility_country_2_c',
                'label' => 'LBL_GEO_MOBILITY_COUNTRY_2_C',
              ),
              4 => 
              array (
                'name' => 'geo_mobility_region_3_c',
                'label' => 'LBL_GEO_MOBILITY_REGION_3_C',
              ),
              5 => 
              array (
                'name' => 'geo_mobility_country_3_c',
                'label' => 'LBL_GEO_MOBILITY_COUNTRY_3_C',
              ),
              6 => 
              array (
                'name' => 'geo_mobility_region_4_c',
                'label' => 'LBL_GEO_MOBILITY_REGION_4_C',
              ),
              7 => 
              array (
                'name' => 'geo_mobility_country_4_c',
                'label' => 'LBL_GEO_MOBILITY_COUNTRY_4_C',
              ),
              8 => 
              array (
                'name' => 'geo_mobility_region_5_c',
                'label' => 'LBL_GEO_MOBILITY_REGION_5_C',
              ),
              9 => 
              array (
                'name' => 'geo_mobility_country_5_c',
                'label' => 'LBL_GEO_MOBILITY_COUNTRY_5_C',
              ),
              10 => 
              array (
                'name' => 'geo_mobility_region_6_c',
                'label' => 'LBL_GEO_MOBILITY_REGION_6_C',
              ),
              11 => 
              array (
                'name' => 'geo_mobility_country_6_c',
                'label' => 'LBL_GEO_MOBILITY_COUNTRY_6_C',
              ),
            ),
          ),
          3 => 
          array (
            'newTab' => false,
            'panelDefault' => 'expanded',
            'name' => 'LBL_RECORDVIEW_PANEL2',
            'label' => 'LBL_RECORDVIEW_PANEL2',
            'columns' => 2,
            'placeholders' => 1,
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'language_1_c',
                'label' => 'LBL_LANGUAGE_1_C',
              ),
              1 => 
              array (
                'name' => 'prof_level_1_c',
                'label' => 'LBL_PROF_LEVEL',
              ),
              2 => 
              array (
                'name' => 'language_2_c',
                'label' => 'LBL_LANGUAGE_2_C',
              ),
              3 => 
              array (
                'name' => 'prof_level_2_c',
                'label' => 'LBL_PROF_LEVEL',
              ),
              4 => 
              array (
                'name' => 'language_3_c',
                'label' => 'LBL_LANGUAGE_3_C',
              ),
              5 => 
              array (
                'name' => 'prof_level_3_c',
                'label' => 'LBL_PROF_LEVEL',
              ),
              6 => 
              array (
                'name' => 'language_4_c',
                'label' => 'LBL_LANGUAGE_4_C',
              ),
              7 => 
              array (
                'name' => 'prof_level_4_c',
                'label' => 'LBL_PROF_LEVEL',
              ),
              8 => 
              array (
                'name' => 'language_5_c',
                'label' => 'LBL_LANGUAGE_5_C',
              ),
              9 => 
              array (
                'name' => 'prof_level_5_c',
                'label' => 'LBL_PROF_LEVEL',
              ),
              10 => 
              array (
                'name' => 'language_6_c',
                'label' => 'LBL_LANGUAGE_6_C',
              ),
              11 => 
              array (
                'name' => 'prof_level_6_c',
                'label' => 'LBL_PROF_LEVEL',
              ),
            ),
          ),
          4 => 
          array (
            'newTab' => true,
            'panelDefault' => 'expanded',
            'name' => 'LBL_RECORDVIEW_PANEL1',
            'label' => 'LBL_RECORDVIEW_PANEL1',
            'columns' => 2,
            'placeholders' => 1,
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'assigned_user_name',
              ),
              1 => 
              array (
                'name' => 'date_entered_by',
                'readonly' => true,
                'inline' => true,
                'type' => 'fieldset',
                'label' => 'LBL_DATE_ENTERED',
                'fields' => 
                array (
                  0 => 
                  array (
                    'name' => 'date_entered',
                  ),
                  1 => 
                  array (
                    'type' => 'label',
                    'default_value' => 'LBL_BY',
                  ),
                  2 => 
                  array (
                    'name' => 'created_by_name',
                  ),
                ),
              ),
              2 => 'team_name',
              3 => 
              array (
                'name' => 'date_modified_by',
                'readonly' => true,
                'inline' => true,
                'type' => 'fieldset',
                'label' => 'LBL_DATE_MODIFIED',
                'fields' => 
                array (
                  0 => 
                  array (
                    'name' => 'date_modified',
                  ),
                  1 => 
                  array (
                    'type' => 'label',
                    'default_value' => 'LBL_BY',
                  ),
                  2 => 
                  array (
                    'name' => 'modified_by_name',
                  ),
                ),
              ),
              4 => 
              array (
                'name' => 'sync_contact',
              ),
              5 => 
              array (
              ),
            ),
          ),
        ),
        'templateMeta' => 
        array (
          'maxColumns' => 1,
          'useTabs' => false,
        ),
);
