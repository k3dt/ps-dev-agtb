<?php

/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

$dictionary['Contact'] = array(
    'table' => 'contacts',
    'audited' => true,
    'activity_enabled' => true,
    'unified_search' => true,
    'full_text_search' => true,
    'unified_search_default_enabled' => true,
    'duplicate_merge' => true,
    'fields' => array(

        'email_and_name1' => array(
            'name' => 'email_and_name1',
            'vname' => 'LBL_NAME',
            'type' => 'varchar',
            'source' => 'non-db',
            'len' => '510',
            'importable' => 'false',
            'studio' => array('formula' => false),
        ),
        'lead_source' => array(
            'name' => 'lead_source',
            'vname' => 'LBL_LEAD_SOURCE',
            'type' => 'enum',
            'options' => 'lead_source_dom',
            'len' => '255',
            'comment' => 'How did the contact come about',
            //BEGIN SUGARCRM flav=pro ONLY
            'merge_filter' => 'enabled',
            //END SUGARCRM flav=pro ONLY
        ),
        'account_name' => array(
            'name' => 'account_name',
            'rname' => 'name',
            'id_name' => 'account_id',
            'vname' => 'LBL_ACCOUNT_NAME',
            'join_name' => 'accounts',
            'type' => 'relate',
            'link' => 'accounts',
            'table' => 'accounts',
            'isnull' => 'true',
            'module' => 'Accounts',
            'dbType' => 'varchar',
            'len' => '255',
            'source' => 'non-db',
            'unified_search' => true,
            'populate_list' => array(
                'billing_address_street' => 'primary_address_street',
                'billing_address_city' => 'primary_address_city',
                'billing_address_state' => 'primary_address_state',
                'billing_address_postalcode' => 'primary_address_postalcode',
                'billing_address_country' => 'primary_address_country',
                'phone_office' => 'phone_work',
            ),
            'populate_confirm_label' => 'TPL_OVERWRITE_POPULATED_DATA_CONFIRM_WITH_MODULE_SINGULAR',
            'importable' => 'true',
        ),
        'account_id' => array(
            'name' => 'account_id',
            'rname' => 'id',
            'id_name' => 'account_id',
            'vname' => 'LBL_ACCOUNT_ID',
            'type' => 'relate',
            'table' => 'accounts',
            'isnull' => 'true',
            'module' => 'Accounts',
            'dbType' => 'id',
            'reportable' => false,
            'source' => 'non-db',
            'massupdate' => false,
            'duplicate_merge' => 'disabled',
            'hideacl' => true,
            'link' => 'accounts',
        ),
        //d&b principal id num
        'dnb_principal_id' =>
          array (
            'name' => 'dnb_principal_id',
            'vname' => 'LBL_DNB_PRINCIPAL_ID',
            'type' => 'varchar',
            'len' => 30,
            'comment' => 'Unique Id For D&B Contact',
        ),
        // Deprecated, use rname_link instead
        'opportunity_role_fields' => array(
            'name' => 'opportunity_role_fields',
            'rname' => 'id',
            'relationship_fields' => array(
                'id' => 'opportunity_role_id',
                'contact_role' => 'opportunity_role'
            ),
            'vname' => 'LBL_ACCOUNT_NAME',
            'type' => 'relate',
            'link' => 'opportunities',
            'link_type' => 'relationship_info',
            'join_link_name' => 'opportunities_contacts',
            'source' => 'non-db',
            'importable' => 'false',
            'duplicate_merge' => 'disabled',
            'studio' => false,
        ),
        // Deprecated, use rname_link instead
        'opportunity_role_id' => array(
            'name' => 'opportunity_role_id',
            'type' => 'varchar',
            'source' => 'non-db',
            'vname' => 'LBL_OPPORTUNITY_ROLE_ID',
            'studio' => array('listview' => false),
        ),
        'opportunity_role' => array(
            'name' => 'opportunity_role',
            'type' => 'enum',
            'source' => 'non-db',
            'vname' => 'LBL_OPPORTUNITY_ROLE',
            'options' => 'opportunity_relationship_type_dom',
            'link' => 'opportunities',
            'rname_link' => 'contact_role',
        ),
        'reports_to_id' => array(
            'name' => 'reports_to_id',
            'vname' => 'LBL_REPORTS_TO_ID',
            'type' => 'id',
            'required' => false,
            'reportable' => false,
            'comment' => 'The contact this contact reports to'
        ),
        'report_to_name' => array(
            'name' => 'report_to_name',
            'rname' => 'name',
            'id_name' => 'reports_to_id',
            'vname' => 'LBL_REPORTS_TO',
            'type' => 'relate',
            'link' => 'reports_to_link',
            'table' => 'contacts',
            'isnull' => 'true',
            'module' => 'Contacts',
            'dbType' => 'varchar',
            'len' => 'id',
            'reportable' => false,
            'source' => 'non-db',
            'populate_list' => array(
                'account_id' => 'account_id',
                'account_name' => 'account_name',
            ),
        ),
        'birthdate' => array(
            'name' => 'birthdate',
            'vname' => 'LBL_BIRTHDATE',
            'massupdate' => false,
            'type' => 'date',
            'comment' => 'The birthdate of the contact'
        ),
        //BEGIN SUGARCRM flav=ent ONLY
        'portal_name' => array(
            'name' => 'portal_name',
            'vname' => 'LBL_PORTAL_NAME',
            'type' => 'varchar',
            'len' => '255',
            'group' => 'portal',
            'comment' => 'Name as it appears in the portal',
            'studio' => array(
                'portalrecordview' => false,
                'portallistview' => false,
            ),
        ),
        'portal_active' => array(
            'name' => 'portal_active',
            'vname' => 'LBL_PORTAL_ACTIVE',
            'type' => 'bool',
            'default' => '0',
            'group' => 'portal',
            'comment' => 'Indicator whether this contact is a portal user'
        ),
        'portal_password' => array(
            'name' => 'portal_password',
            'vname' => 'LBL_USER_PASSWORD',
            'type' => 'password',
            'dbType' => 'varchar',
            'len' => '255',
            'group' => 'portal',
            'reportable' => false,
            'studio' => array(
                'listview' => false,
                'portalrecordview' => false,
                'portallistview' => false,
            ),
        ),
        'portal_password1' => array(
            'name' => 'portal_password1',
            'vname' => 'LBL_USER_PASSWORD',
            'type' => 'password',
            'source' => 'non-db',
            'len' => '255',
            'group' => 'portal',
            'reportable' => false,
            'importable' => 'false',
            'studio' => array(
                'listview' => false,
                'portalrecordview' => false,
                'portallistview' => false,
            ),
        ),
        'portal_app' => array(
            'name' => 'portal_app',
            'vname' => 'LBL_PORTAL_APP',
            'type' => 'varchar',
            'group' => 'portal',
            'len' => '255',
            'comment' => 'Reference to the portal',
        ),
        'preferred_language' => array(
            'name' => 'preferred_language',
            'type' => 'enum',
            'vname' => 'LBL_PREFERRED_LANGUAGE',
            'options' => 'available_language_dom',
            'popupHelp' => 'LBL_LANG_PREF_TOOLTIP',
        ),
        //END SUGARCRM flav=ent ONLY
        'accounts' => array(
            'name' => 'accounts',
            'type' => 'link',
            'relationship' => 'accounts_contacts',
            'link_type' => 'one',
            'source' => 'non-db',
            'vname' => 'LBL_ACCOUNT',
            'duplicate_merge' => 'disabled',
            'primary_only' => true,
        ),
        'reports_to_link' => array(
            'name' => 'reports_to_link',
            'type' => 'link',
            'relationship' => 'contact_direct_reports',
            'link_type' => 'one',
            'side' => 'right',
            'source' => 'non-db',
            'vname' => 'LBL_REPORTS_TO',
        ),
        'opportunities' => array(
            'name' => 'opportunities',
            'type' => 'link',
            'relationship' => 'opportunities_contacts',
            'source' => 'non-db',
            'module' => 'Opportunities',
            'bean_name' => 'Opportunity',
            'vname' => 'LBL_OPPORTUNITIES',
            'populate_list' => array(
                'account_id' => 'account_id',
                'account_name' => 'account_name',
            )
        ),
        'bugs' => array(
            'name' => 'bugs',
            'type' => 'link',
            'relationship' => 'contacts_bugs',
            'source' => 'non-db',
            'vname' => 'LBL_BUGS',
        ),
        'calls' => array(
            'name' => 'calls',
            'type' => 'link',
            'relationship' => 'calls_contacts',
            'source' => 'non-db',
            'vname' => 'LBL_CALLS',
        ),
        'cases' => array(
            'name' => 'cases',
            'type' => 'link',
            'relationship' => 'contacts_cases',
            'source' => 'non-db',
            'vname' => 'LBL_CASES',
            'populate_list' => array(
                'account_id',
                'account_name'
            )
        ),
        'direct_reports' => array(
            'name' => 'direct_reports',
            'type' => 'link',
            'relationship' => 'contact_direct_reports',
            'source' => 'non-db',
            'vname' => 'LBL_DIRECT_REPORTS',
        ),
        'emails' => array(
            'name' => 'emails',
            'type' => 'link',
            'relationship' => 'emails_contacts_rel',
            'source' => 'non-db',
            'vname' => 'LBL_EMAILS',
        ),
        'archived_emails' => array(
            'name' => 'archived_emails',
            'type' => 'link',
            'link_file' => 'modules/Emails/ArchivedEmailsLink.php',
            'link_class' => 'ArchivedEmailsLink',
            'source' => 'non-db',
            'vname' => 'LBL_EMAILS',
            'module' => 'Emails',
            'link_type' => 'many',
            'relationship' => '',
            'readonly' => true,
        ),
        'documents' => array(
            'name' => 'documents',
            'type' => 'link',
            'relationship' => 'documents_contacts',
            'source' => 'non-db',
            'vname' => 'LBL_DOCUMENTS_SUBPANEL_TITLE',
        ),
        'leads' => array(
            'name' => 'leads',
            'type' => 'link',
            'relationship' => 'contact_leads',
            'source' => 'non-db',
            'vname' => 'LBL_LEADS',
        ),
        //BEGIN SUGARCRM flav=pro ONLY
        'products' => array(
            'name' => 'products',
            'type' => 'link',
            'link_file' => 'modules/Products/AccountLink.php',
            'link_class' => 'AccountLink',
            'relationship' => 'contact_products',
            'source' => 'non-db',
            'vname' => 'LBL_PRODUCTS_TITLE',
        ),
        'contracts' => array(
            'name' => 'contracts',
            'type' => 'link',
            'vname' => 'LBL_CONTRACTS',
            'relationship' => 'contracts_contacts',
            'source' => 'non-db',
        ),
        //END SUGARCRM flav=pro ONLY
        'meetings' => array(
            'name' => 'meetings',
            'type' => 'link',
            'relationship' => 'meetings_contacts',
            'source' => 'non-db',
            'vname' => 'LBL_MEETINGS',
        ),
        'notes' => array(
            'name' => 'notes',
            'type' => 'link',
            'relationship' => 'contact_notes',
            'source' => 'non-db',
            'vname' => 'LBL_NOTES',
        ),
        'project' => array(
            'name' => 'project',
            'type' => 'link',
            'relationship' => 'projects_contacts',
            'source' => 'non-db',
            'vname' => 'LBL_PROJECTS',
        ),
        //BEGIN SUGARCRM flav=pro ONLY
        'project_resource' => array(
            'name' => 'project_resource',
            'type' => 'link',
            'relationship' => 'projects_contacts_resources',
            'source' => 'non-db',
            'vname' => 'LBL_PROJECTS_RESOURCES',
        ),
        'quotes' => array(
            'name' => 'quotes',
            'type' => 'link',
            'relationship' => 'quotes_contacts_shipto',
            'source' => 'non-db',
            'ignore_role' => 'true',
            'module' => 'Quotes',
            'bean_name' => 'Quote',
            'vname' => 'LBL_QUOTES_SHIP_TO',
        ),
        'billing_quotes' => array(
            'name' => 'billing_quotes',
            'type' => 'link',
            'relationship' => 'quotes_contacts_billto',
            'source' => 'non-db',
            'ignore_role' => 'true',
            'module' => 'Quotes',
            'bean_name' => 'Quote',
            'vname' => 'LBL_QUOTES_BILL_TO',
        ),
        //END SUGARCRM flav=pro ONLY
        'tasks' => array(
            'name' => 'tasks',
            'type' => 'link',
            'relationship' => 'contact_tasks',
            'source' => 'non-db',
            'vname' => 'LBL_TASKS',
        ),
        'tasks_parent' => array(
            'name' => 'tasks_parent',
            'type' => 'link',
            'relationship' => 'contact_tasks_parent',
            'source' => 'non-db',
            'vname' => 'LBL_TASKS',
            'reportable' => false,
        ),
        'all_tasks' => array(
            'name' => 'all_tasks',
            'type' => 'link',
            'link_file' => 'data/Link/FlexRelateChildrenLink.php',
            'link_class' => 'FlexRelateChildrenLink',
            'relationship' => 'contact_tasks',
            'source' => 'non-db',
            'vname' => 'LBL_TASKS',
        ),
        'user_sync' => array(
            'name' => 'user_sync',
            'type' => 'link',
            'relationship' => 'contacts_users',
            'source' => 'non-db',
            'vname' => 'LBL_USER_SYNC',
        ),
        'created_by_link' => array(
            'name' => 'created_by_link',
            'type' => 'link',
            'relationship' => 'contacts_created_by',
            'vname' => 'LBL_CREATED_BY_USER',
            'link_type' => 'one',
            'module' => 'Users',
            'bean_name' => 'User',
            'source' => 'non-db',
        ),
        'modified_user_link' => array(
            'name' => 'modified_user_link',
            'type' => 'link',
            'relationship' => 'contacts_modified_user',
            'vname' => 'LBL_MODIFIED_BY_USER',
            'link_type' => 'one',
            'module' => 'Users',
            'bean_name' => 'User',
            'source' => 'non-db',
        ),
        'assigned_user_link' => array(
            'name' => 'assigned_user_link',
            'type' => 'link',
            'relationship' => 'contacts_assigned_user',
            'vname' => 'LBL_ASSIGNED_TO_USER',
            'link_type' => 'one',
            'module' => 'Users',
            'bean_name' => 'User',
            'source' => 'non-db',
            'id_name' => 'assigned_user_id',
            'table' => 'users',
            'duplicate_merge' => 'enabled',
        ),
        'campaign_id' => array(
            'name' => 'campaign_id',
            'comment' => 'Campaign that generated lead',
            'vname' => 'LBL_CAMPAIGN_ID',
            'rname' => 'id',
            'id_name' => 'campaign_id',
            'type' => 'id',
            'table' => 'campaigns',
            'isnull' => 'true',
            'module' => 'Campaigns',
            'massupdate' => false,
            'duplicate_merge' => 'disabled',
        ),
        'campaign_name' => array(
            'name' => 'campaign_name',
            'rname' => 'name',
            'vname' => 'LBL_CAMPAIGN',
            'type' => 'relate',
            'link' => 'campaign_contacts',
            'isnull' => 'true',
            'reportable' => false,
            'source' => 'non-db',
            'table' => 'campaigns',
            'id_name' => 'campaign_id',
            'module' => 'Campaigns',
            'duplicate_merge' => 'disabled',
            'comment' => 'The first campaign name for Contact (Meta-data only)',
        ),
        'campaigns' => array(
            'name' => 'campaigns',
            'type' => 'link',
            'relationship' => 'contact_campaign_log',
            'module' => 'CampaignLog',
            'bean_name' => 'CampaignLog',
            'source' => 'non-db',
            'vname' => 'LBL_CAMPAIGNLOG',
        ),
        'campaign_contacts' => array(
            'name' => 'campaign_contacts',
            'type' => 'link',
            'vname' => 'LBL_CAMPAIGN_CONTACT',
            'relationship' => 'campaign_contacts',
            'source' => 'non-db',
        ),
        // Deprecated: Use rname_link instead
        'c_accept_status_fields' => array(
            'name' => 'c_accept_status_fields',
            'rname' => 'id',
            'relationship_fields' => array(
                'id' => 'accept_status_id',
                'accept_status' => 'accept_status_name'
            ),
            'vname' => 'LBL_LIST_ACCEPT_STATUS',
            'type' => 'relate',
            'link' => 'calls',
            'link_type' => 'relationship_info',
            'source' => 'non-db',
            'importable' => 'false',
            'duplicate_merge' => 'disabled',
            'studio' => false,
        ),
        // Deprecated: Use rname_link instead
        'm_accept_status_fields' => array(
            'name' => 'm_accept_status_fields',
            'rname' => 'id',
            'relationship_fields' => array(
                'id' => 'accept_status_id',
                'accept_status' => 'accept_status_name',
            ),
            'vname' => 'LBL_LIST_ACCEPT_STATUS',
            'type' => 'relate',
            'link' => 'meetings',
            'link_type' => 'relationship_info',
            'source' => 'non-db',
            'importable' => 'false',
            'hideacl' => true,
            'duplicate_merge' => 'disabled',
            'studio' => false,
        ),
        // Deprecated: Use rname_link instead
        'accept_status_id' => array(
            'name' => 'accept_status_id',
            'type' => 'varchar',
            'source' => 'non-db',
            'vname' => 'LBL_LIST_ACCEPT_STATUS',
            'studio' => array('listview' => false),
        ),
        // Deprecated: Use rname_link instead
        'accept_status_name' => array(
            'massupdate' => false,
            'name' => 'accept_status_name',
            'type' => 'enum',
            'studio' => 'false',
            'source' => 'non-db',
            'vname' => 'LBL_LIST_ACCEPT_STATUS',
            'options' => 'dom_meeting_accept_status',
            'importable' => 'false',
        ),
        'accept_status_calls' => array(
            'massupdate' => false,
            'name' => 'accept_status_calls',
            'type' => 'enum',
            'studio' => 'false',
            'source' => 'non-db',
            'vname' => 'LBL_LIST_ACCEPT_STATUS',
            'options' => 'dom_meeting_accept_status',
            'importable' => 'false',
            'link' => 'calls',
            'rname_link' => 'accept_status',
        ),
        'accept_status_meetings' => array(
            'massupdate' => false,
            'name' => 'accept_status_meetings',
            'type' => 'enum',
            'studio' => 'false',
            'source' => 'non-db',
            'vname' => 'LBL_LIST_ACCEPT_STATUS',
            'options' => 'dom_meeting_accept_status',
            'importable' => 'false',
            'link' => 'meetings',
            'rname_link' => 'accept_status',
        ),
        'prospect_lists' => array(
            'name' => 'prospect_lists',
            'type' => 'link',
            'relationship' => 'prospect_list_contacts',
            'module' => 'ProspectLists',
            'source' => 'non-db',
            'vname' => 'LBL_PROSPECT_LIST',
        ),
        'sync_contact' => array(
            'massupdate' => false,
            'name' => 'sync_contact',
            'vname' => 'LBL_SYNC_CONTACT',
            'type' => 'bool',
            'source' => 'non-db',
            'comment' => 'Synch to outlook?  (Meta-Data only)',
            'studio' => 'true',
            'link' => 'user_sync',
            'rname' => 'id',
            'rname_exists' => true,
        ),

        // Marketo Fields
        'mkto_sync' =>
            array(
                'name' => 'mkto_sync',
                'vname' => 'LBL_MKTO_SYNC',
                'type' => 'bool',
                'default' => '0',
                'comment' => 'Should the Lead be synced to Marketo',
                'massupdate' => true,
                'audited' => true,
                'duplicate_merge' => true,
                'reportable' => true,
                'importable' => 'true',
            ),
        'mkto_id' =>
            array(
                'name' => 'mkto_id',
                'vname' => 'LBL_MKTO_ID',
                'comment' => 'Associated Marketo Lead ID',
                'type' => 'int',
                'default' => null,
                'audited' => true,
                'mass_update' => false,
                'duplicate_merge' => true,
                'reportable' => true,
                'importable' => 'false',
            ),
        'mkto_lead_score' =>
            array(
                'name' => 'mkto_lead_score',
                'vname' => 'LBL_MKTO_LEAD_SCORE',
                'comment' => null,
                'type' => 'int',
                'default_value' => null,
                'audited' => true,
                'mass_update' => false,
                'duplicate_merge' => true,
                'reportable' => true,
                'importable' => 'true',
            ),
    ),
    'indices' => array(
        array(
            'name' => 'idx_cont_last_first',
            'type' => 'index',
            'fields' => array('last_name', 'first_name', 'deleted'),
        ),
        array(
            'name' => 'idx_contacts_del_last',
            'type' => 'index',
            'fields' => array('deleted', 'last_name'),
        ),
        array(
            'name' => 'idx_cont_del_reports',
            'type' => 'index',
            'fields' => array('deleted', 'reports_to_id', 'last_name'),
        ),
        array(
            'name' => 'idx_reports_to_id',
            'type' => 'index',
            'fields' => array('reports_to_id'),
        ),
        array(
            'name' => 'idx_del_id_user',
            'type' => 'index',
            'fields' => array('deleted', 'id', 'assigned_user_id'),
        ),
        array(
            'name' => 'idx_cont_assigned',
            'type' => 'index',
            'fields' => array('assigned_user_id'),
        ),
        array('name' => 'idx_contact_title', 'type' => 'index', 'fields' => array('title')),
        array('name' => 'idx_contact_date_entered', 'type' => 'index', 'fields' => array('date_entered')),
        array(
            'name' => 'idx_contact_mkto_id',
            'type' => 'index',
            'fields' => array('mkto_id')
        ),

    ),
    'relationships' => array(
        'contact_direct_reports' => array(
            'lhs_module' => 'Contacts',
            'lhs_table' => 'contacts',
            'lhs_key' => 'id',
            'rhs_module' => 'Contacts',
            'rhs_table' => 'contacts',
            'rhs_key' => 'reports_to_id',
            'relationship_type' => 'one-to-many',
        ),
        'contact_leads' => array(
            'lhs_module' => 'Contacts',
            'lhs_table' => 'contacts',
            'lhs_key' => 'id',
            'rhs_module' => 'Leads',
            'rhs_table' => 'leads',
            'rhs_key' => 'contact_id',
            'relationship_type' => 'one-to-many',
        ),
        'contact_notes' => array(
            'lhs_module' => 'Contacts',
            'lhs_table' => 'contacts',
            'lhs_key' => 'id',
            'rhs_module' => 'Notes',
            'rhs_table' => 'notes',
            'rhs_key' => 'contact_id',
            'relationship_type' => 'one-to-many',
        ),
        'contact_tasks' => array(
            'lhs_module' => 'Contacts',
            'lhs_table' => 'contacts',
            'lhs_key' => 'id',
            'rhs_module' => 'Tasks',
            'rhs_table' => 'tasks',
            'rhs_key' => 'contact_id',
            'relationship_type' => 'one-to-many',
        ),
        'contact_tasks_parent' => array(
            'lhs_module' => 'Contacts',
            'lhs_table' => 'contacts',
            'lhs_key' => 'id',
            'rhs_module' => 'Tasks',
            'rhs_table' => 'tasks',
            'rhs_key' => 'parent_id',
            'relationship_type' => 'one-to-many',
            'relationship_role_column' => 'parent_type',
            'relationship_role_column_value' => 'Contacts',
        ),
        'contacts_assigned_user' => array(
            'lhs_module' => 'Users',
            'lhs_table' => 'users',
            'lhs_key' => 'id',
            'rhs_module' => 'Contacts',
            'rhs_table' => 'contacts',
            'rhs_key' => 'assigned_user_id',
            'relationship_type' => 'one-to-many',
        ),
        'contacts_modified_user' => array(
            'lhs_module' => 'Users',
            'lhs_table' => 'users',
            'lhs_key' => 'id',
            'rhs_module' => 'Contacts',
            'rhs_table' => 'contacts',
            'rhs_key' => 'modified_user_id',
            'relationship_type' => 'one-to-many',
        ),
        'contacts_created_by' => array(
            'lhs_module' => 'Users',
            'lhs_table' => 'users',
            'lhs_key' => 'id',
            'rhs_module' => 'Contacts',
            'rhs_table' => 'contacts',
            'rhs_key' => 'created_by',
            'relationship_type' => 'one-to-many',
        ),
        //BEGIN SUGARCRM flav=pro ONLY
        'contact_products' => array(
            'lhs_module' => 'Contacts',
            'lhs_table' => 'contacts',
            'lhs_key' => 'id',
            'rhs_module' => 'Products',
            'rhs_table' => 'products',
            'rhs_key' => 'contact_id',
            'relationship_type' => 'one-to-many',
        ),
        //END SUGARCRM flav=pro ONLY
        'contact_campaign_log' => array(
            'lhs_module' => 'Contacts',
            'lhs_table' => 'contacts',
            'lhs_key' => 'id',
            'rhs_module' => 'CampaignLog',
            'rhs_table' => 'campaign_log',
            'rhs_key' => 'target_id',
            'relationship_type' => 'one-to-many',
        ),
    ),
    'duplicate_check' => array(
        'enabled' => true,
        'FilterDuplicateCheck' => array(
            'filter_template' => array(
                array(
                    '$and' => array(
                        array('first_name' => array('$starts' => '$first_name')),
                        array('last_name' => array('$starts' => '$last_name')),
                        array('accounts.id' => array('$equals' => '$account_id')),
                        array('dnb_principal_id' => array('$equals' => '$dnb_principal_id')),
                    )
                ),
            ),
            'ranking_fields' => array(
                array(
                    'in_field_name' => 'account_id',
                    'dupe_field_name' => 'account_id',
                ),
                array(
                    'in_field_name' => 'last_name',
                    'dupe_field_name' => 'last_name',
                ),
                array(
                    'in_field_name' => 'first_name',
                    'dupe_field_name' => 'first_name',
                ),
            ),
        ),
    ),
    // This enables optimistic locking for Saves From EditView
    'optimistic_locking' => true,
    'uses' => array(
        'default',
        'assignable',
//BEGIN SUGARCRM flav=pro ONLY
        'team_security',
//END SUGARCRM flav=pro ONLY
        'person',
    ),
);

VardefManager::createVardef(
    'Contacts',
    'Contact'
);
