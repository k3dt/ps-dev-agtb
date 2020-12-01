<?php
$viewdefs['Contacts']['base']['view']['record'] =
      array (
        'buttons' => 
        array (
          0 => 
          array (
            'type' => 'button',
            'name' => 'cancel_button',
            'label' => 'LBL_CANCEL_BUTTON_LABEL',
            'css_class' => 'btn-invisible btn-link',
            'showOn' => 'edit',
            'events' => 
            array (
              'click' => 'button:cancel_button:click',
            ),
          ),
          1 => 
          array (
            'type' => 'rowaction',
            'event' => 'button:save_button:click',
            'name' => 'save_button',
            'label' => 'LBL_SAVE_BUTTON_LABEL',
            'css_class' => 'btn btn-primary',
            'showOn' => 'edit',
            'acl_action' => 'edit',
          ),
          2 => 
          array (
            'type' => 'actiondropdown',
            'name' => 'main_dropdown',
            'primary' => true,
            'showOn' => 'view',
            'buttons' => 
            array (
              0 => 
              array (
                'type' => 'rowaction',
                'event' => 'button:edit_button:click',
                'name' => 'edit_button',
                'label' => 'LBL_EDIT_BUTTON_LABEL',
                'acl_action' => 'edit',
              ),
              1 => 
              array (
                'type' => 'shareaction',
                'name' => 'share',
                'label' => 'LBL_RECORD_SHARE_BUTTON',
                'acl_action' => 'view',
              ),
              2 => 
              array (
                'type' => 'pdfaction',
                'name' => 'download-pdf',
                'label' => 'LBL_PDF_VIEW',
                'action' => 'download',
                'acl_action' => 'view',
              ),
              3 => 
              array (
                'type' => 'pdfaction',
                'name' => 'email-pdf',
                'label' => 'LBL_PDF_EMAIL',
                'action' => 'email',
                'acl_action' => 'view',
              ),
              4 => 
              array (
                'type' => 'divider',
              ),
              5 => 
              array (
                'type' => 'manage-subscription',
                'name' => 'manage_subscription_button',
                'label' => 'LBL_MANAGE_SUBSCRIPTIONS',
                'showOn' => 'view',
                'value' => 'edit',
              ),
              6 => 
              array (
                'type' => 'vcard',
                'name' => 'vcard_button',
                'label' => 'LBL_VCARD_DOWNLOAD',
                'acl_action' => 'edit',
              ),
              7 => 
              array (
                'type' => 'divider',
              ),
              8 => 
              array (
                'type' => 'rowaction',
                'event' => 'button:find_duplicates_button:click',
                'name' => 'find_duplicates',
                'label' => 'LBL_DUP_MERGE',
                'acl_action' => 'edit',
              ),
              9 => 
              array (
                'type' => 'rowaction',
                'event' => 'button:duplicate_button:click',
                'name' => 'duplicate_button',
                'label' => 'LBL_DUPLICATE_BUTTON_LABEL',
                'acl_module' => 'Contacts',
                'acl_action' => 'create',
              ),
              10 => 
              array (
                'type' => 'rowaction',
                'event' => 'button:historical_summary_button:click',
                'name' => 'historical_summary_button',
                'label' => 'LBL_HISTORICAL_SUMMARY',
                'acl_action' => 'view',
              ),
              11 => 
              array (
                'type' => 'rowaction',
                'event' => 'button:audit_button:click',
                'name' => 'audit_button',
                'label' => 'LNK_VIEW_CHANGE_LOG',
                'acl_action' => 'view',
              ),
              12 => 
              array (
                'type' => 'divider',
              ),
              13 => 
              array (
                'type' => 'rowaction',
                'event' => 'button:delete_button:click',
                'name' => 'delete_button',
                'label' => 'LBL_DELETE_BUTTON_LABEL',
                'acl_action' => 'delete',
              ),
            ),
          ),
          3 => 
          array (
            'name' => 'sidebar_toggle',
            'type' => 'sidebartoggle',
          ),
        ),
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
              2 => 
              array (
                'name' => 'favorite',
                'label' => 'LBL_FAVORITE',
                'type' => 'favorite',
                'dismiss_label' => true,
              ),
              3 => 
              array (
                'name' => 'follow',
                'label' => 'LBL_FOLLOW',
                'type' => 'follow',
                'readonly' => true,
                'dismiss_label' => true,
              ),
            ),
          ),
          1 => 
          array (
            'name' => 'panel_body',
            'label' => 'LBL_RECORD_BODY',
            'columns' => 2,
            'placeholders' => true,
            'newTab' => true,
            'panelDefault' => 'expanded',
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'assigned_user_name',
              ),
              1 => 
              array (
              ),
              2 => 'title',
              3 => 
              array (
                'name' => 'gender_c',
                'label' => 'LBL_GENDER_C',
              ),
              4 => 
              array (
                'name' => 'gtb_cluster_c',
                'label' => 'LBL_GTB_CLUSTER_C',
                'related_fields' => array(
                  'gtb_cluster_match_c',
                ),
              ),
              5 => 
              array (
                'name' => 'org_unit_c',
                'label' => 'LBL_ORG_UNIT_C',
              ),
              6 => 
              array (
                'name' => 'function_c',
                'label' => 'LBL_FUNCTION_C',
              ),
              7 => 
              array (
                'name' => 'primary_address_country',
                'label' => 'LBL_PRIMARY_ADDRESS_COUNTRY',
                'related_fields' => array(
                  'gtb_country_match_c',
                ),
              ),
              8 => 'phone_mobile',
              9 => 
              array (
                'name' => 'email',
              ),
              10 => 'lead_source',
              11 => 
              array (
                'name' => 'tag',
              ),
              12 => 
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
              13 => 
              array (
                'name' => 'functional_mobility_c',
                'label' => 'LBL_FUNCTIONAL_MOBILITY_C',
                'span' => 12,
                'related_fields' => array(
                  'gtb_function_match_c',
                ),
              ),
              14 => 
              array (
                'name' => 'oe_mobility_c',
                'label' => 'LBL_OE_MOBILITY_C',
                'span' => 12,
              ),
              15 => 
              array (
                'name' => 'mobility_comments_c',
                'label' => 'LBL_MOBILITY_COMMENTS_C',
                'span' => 12,
              ),
              16 => 
              array (
                'name' => 'target_roles_c',
                'label' => 'LBL_TARGET_ROLES_C',
                'span' => 12,
              ),
              17 => 
              array (
                'name' => 'career_discussion_c',
                'label' => 'LBL_CAREER_DISCUSSION_C',
              ),
              18 => 
              array (
                'name' => 'availability_c',
                'label' => 'LBL_AVAILABILITY_C',
              ),
            ),
          ),
          2 => 
          array (
            'newTab' => false,
            'panelDefault' => 'expanded',
            'name' => 'LBL_RECORDVIEW_PANEL3',
            'label' => 'LBL_RECORDVIEW_PANEL3',
            'columns' => 2,
            'placeholders' => 1,
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
                'label' => 'LBL_PROF_LEVEL_1',
              ),
              2 => 
              array (
                'name' => 'language_2_c',
                'label' => 'LBL_LANGUAGE_2_C',
              ),
              3 => 
              array (
                'name' => 'prof_level_2_c',
                'label' => 'LBL_PROF_LEVEL_2',
              ),
              4 => 
              array (
                'name' => 'language_3_c',
                'label' => 'LBL_LANGUAGE_3_C',
              ),
              5 => 
              array (
                'name' => 'prof_level_3_c',
                'label' => 'LBL_PROF_LEVEL_3',
              ),
              6 => 
              array (
                'name' => 'language_4_c',
                'label' => 'LBL_LANGUAGE_4_C',
              ),
              7 => 
              array (
                'name' => 'prof_level_4_c',
                'label' => 'LBL_PROF_LEVEL_4',
              ),
              8 => 
              array (
                'name' => 'language_5_c',
                'label' => 'LBL_LANGUAGE_5_C',
              ),
              9 => 
              array (
                'name' => 'prof_level_5_c',
                'label' => 'LBL_PROF_LEVEL_5',
              ),
              10 => 
              array (
                'name' => 'language_6_c',
                'label' => 'LBL_LANGUAGE_6_C',
              ),
              11 => 
              array (
                'name' => 'prof_level_6_c',
                'label' => 'LBL_PROF_LEVEL_6',
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
                'name' => 'sync_contact',
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
              ),
              5 => 
              array (
              ),
            ),
          ),
        ),
        'templateMeta' => 
        array (
          'useTabs' => true,
        ),
);
