<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
$dictionary['Message'] = [
    'table' => 'messages',
    'audited' => true,
    'activity_enabled' => false,
    'unified_search' => true,
    'full_text_search' => true,
    'unified_search_default_enabled' => true,
    'duplicate_merge' => false,
    'fields' => [
        'date_start' => [
            'name' => 'date_start',
            'vname' => 'LBL_START_DATE',
            'type' => 'datetimecombo',
            'dbType' => 'datetime',
            'group' => 'date_start',
            'validation' => [
                'type' => 'isbefore',
                'compareto' => 'date_end',
                'blank' => false,
            ],
            'studio' => [
                'required' => true,
                'no_duplicate' => true,
            ],
            'enable_range_search' => true,
            'options' => 'date_range_search_dom',
            'audited' => true,
        ],
        'date_end' => [
            'name' => 'date_end',
            'vname' => 'LBL_END_DATE',
            'type' => 'datetimecombo',
            'dbType' => 'datetime',
            'group' => 'date_end',
            'studio' => [
                'required' => true,
                'no_duplicate' => true,
            ],
            'enable_range_search' => true,
            'options' => 'date_range_search_dom',
            'full_text_search' => [
                'type' => 'datetime',
                'enabled' => true,
                'searchable' => false,
            ],
            'audited' => true,
        ],
        'parent_type' => [
            'name' => 'parent_type',
            'vname' => 'LBL_PARENT_TYPE',
            'type' => 'parent_type',
            'dbType' => 'varchar',
            'group' => 'parent_name',
            'options' => 'parent_type_display',
            'len' => '255',
            'studio' => [
                'wirelesslistview' => false,
            ],
            'comment' => 'Sugar module the Message is associated with',
        ],
        'parent_id' => [
            'name' => 'parent_id',
            'vname' => 'LBL_PARENT_ID',
            'type' => 'id',
            'required' => false,
            'reportable' => true,
            'comment' => 'The ID of the Sugar item specified in parent_type',
        ],
        'contact_id' => [
            'name' => 'contact_id',
            'vname' => 'LBL_CONTACT_ID',
            'type' => 'id',
            'required' => false,
            'reportable' => false,
            'audited' => true,
        ],
        'parent_name' => [
            'name' => 'parent_name',
            'parent_type' => 'record_type_display' ,
            'type_name' => 'parent_type',
            'id_name' => 'parent_id',
            'vname'=>'LBL_RELATED_TO',
            'type' => 'parent',
            'source' => 'non-db',
            'options' => 'record_type_display_notes',
            'studio' => true,
        ],
        'contact_name' => [
            'name' => 'contact_name',
            'rname' => 'name',
            'id_name' => 'contact_id',
            'vname' => 'LBL_CONTACT_NAME',
            'table' => 'contacts',
            'type' => 'relate',
            'link' => 'contact',
            'join_name' => 'contacts',
            'db_concat_fields' => [
                'first_name',
                'last_name',
            ],
            'isnull' => 'true',
            'module' => 'Contacts',
            'source' => 'non-db',
        ],
        'contact' => [
            'name' => 'contact',
            'type' => 'link',
            'relationship' => 'contact_messages',
            'vname' => 'LBL_LIST_CONTACT_NAME',
            'source' => 'non-db',
        ],
        'cases' => [
            'name' => 'cases',
            'type' => 'link',
            'relationship' => 'case_messages',
            'vname' => 'LBL_CASES',
            'source' => 'non-db',
        ],
        'accounts' => [
            'name' => 'accounts',
            'type' => 'link',
            'relationship' => 'account_messages',
            'source' => 'non-db',
            'vname' => 'LBL_ACCOUNTS',
        ],
        'opportunities' => [
            'name' => 'opportunities',
            'type' => 'link',
            'relationship' => 'opportunity_messages',
            'source' => 'non-db',
            'vname' => 'LBL_OPPORTUNITIES',
        ],
        'leads' => [
            'name' => 'leads',
            'type' => 'link',
            'relationship' => 'lead_messages',
            'source' => 'non-db',
            'vname' => 'LBL_LEADS',
        ],
        'products' => [
            'name' => 'products',
            'type' => 'link',
            'relationship' => 'product_messages',
            'source' => 'non-db',
            'vname' => 'LBL_PRODUCTS',
        ],
        'revenuelineitems' => [
            'name' => 'revenuelineitems',
            'type' => 'link',
            'relationship' => 'revenuelineitem_messages',
            'source' => 'non-db',
            'vname' => 'LBL_REVENUELINEITEMS',
            'workflow' => false,
        ],
        'quotes' => [
            'name' => 'quotes',
            'type' => 'link',
            'relationship' => 'quote_messages',
            'vname' => 'LBL_QUOTES',
            'source' => 'non-db',
        ],
        'contracts' => [
            'name' => 'contracts',
            'type' => 'link',
            'relationship' => 'contract_messages',
            'source' => 'non-db',
            'vname' => 'LBL_CONTRACTS',
        ],
        'prospects' => [
            'name' => 'prospects',
            'type' => 'link',
            'relationship' => 'prospect_messages',
            'source' => 'non-db',
            'vname' => 'LBL_PROSPECTS',
        ],
        'bugs' => [
            'name' => 'bugs',
            'type' => 'link',
            'relationship' => 'bug_messages',
            'source' => 'non-db',
            'vname' => 'LBL_BUGS',
        ],
        'kbcontents' => [
            'name' => 'kbcontents',
            'type' => 'link',
            'relationship' => 'kbcontent_messages',
            'source' => 'non-db',
            'vname' => 'LBL_KBDOCUMENTS',
        ],
        'emails' => [
            'name' => 'emails',
            'vname' => 'LBL_EMAILS',
            'type' => 'link',
            'relationship' => 'emails_messages_rel',
            'source' => 'non-db',
        ],
        'projects' => [
            'name' => 'projects',
            'type' => 'link',
            'relationship' => 'projects_messages',
            'source' => 'non-db',
            'vname' => 'LBL_PROJECTS',
        ],
        'project_tasks' => [
            'name' => 'project_tasks',
            'type' => 'link',
            'relationship' => 'project_tasks_messages',
            'source' => 'non-db',
            'vname' => 'LBL_PROJECT_TASKS',
        ],
        'meetings' => [
            'name' => 'meetings',
            'type' => 'link',
            'relationship' => 'meetings_messages',
            'source' => 'non-db',
            'vname' => 'LBL_MEETINGS',
        ],
        'calls' => [
            'name' => 'calls',
            'type' => 'link',
            'relationship' => 'calls_messages',
            'source' => 'non-db',
            'vname' => 'LBL_CALLS',
        ],
        'tasks' => [
            'name' => 'tasks',
            'type' => 'link',
            'relationship' => 'tasks_messages',
            'source' => 'non-db',
            'vname' => 'LBL_TASKS',
        ],
        'status' => [
            'name' => 'status',
            'vname' => 'LBL_STATUS',
            'type' => 'enum',
            'options' => 'message_status_dom',
            'len' => 50,
            'duplicate_on_record_copy' => 'always',
            'audited' => true,
        ],
        'direction' => [
            'name' => 'direction',
            'vname' => 'LBL_DIRECTION',
            'type' => 'enum',
            'options' => 'message_direction_dom',
            'len' => 50,
            'duplicate_on_record_copy' => 'always',
            'merge_filter' => 'enabled',
        ],
        'channel_type' => [
            'name' => 'channel_type',
            'vname' => 'LBL_CHANNEL_TYPE',
            'type' => 'enum',
            'options' => 'message_channel_type_dom',
            'len' => 50,
            'duplicate_on_record_copy' => 'always',
            'merge_filter' => 'enabled',
        ],
        'conversation_link' => [
            'name' => 'conversation_link',
            'vname' => 'LBL_CONVERSATION_LINK',
            'type' => 'varchar',
            'len' => 512,
            'duplicate_on_record_copy' => 'always',
            'audited' => true,
        ],
        'conversation' => [
            'name' => 'conversation',
            'vname' => 'LBL_CONVERSATION',
            'type' => 'text',
            'duplicate_on_record_copy' => 'always',
        ],
    ],
    'relationships' => [
    ],
    'indices' => [
        [
            'name' => 'idx_parent',
            'type' => 'index',
            'fields' => [
                'parent_id',
                'parent_type',
            ],
        ],
    ],
    'uses' => [
        'basic',
        'assignable',
        'team_security',
    ],
];

VardefManager::createVardef('Messages', 'Message');

$dictionary['Message']['fields']['name']['vname'] = 'LBL_MESSAGE_SUBJECT';
