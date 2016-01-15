<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
//FILE SUGARCRM flav=int ONLY
$dictionary['Queue'] = array('table' => 'queues',
	'fields' => array (
		'id' => array (
			'name' => 'id',
			'vname' => 'LBL_ID',
			'type' => 'id',
			'dbType' => 'varchar',
			'len' => 36,
			'required' => true,
			'reportable'=>false,
		),
		'deleted' => array (
			'name' => 'deleted',
			'vname' => 'LBL_DELETED',
			'type' => 'bool',
			'required' => true,
			'default' => '0',
			'reportable'=>false,
		),
		'date_entered' => array (
			'name' => 'date_entered',
			'vname' => 'LBL_DATE_ENTERED',
			'type' => 'datetime',
			'required' => true,
		),
		'date_modified' => array (
			'name' => 'date_modified',
			'vname' => 'LBL_DATE_MODIFIED',
			'type' => 'datetime',
			'required' => true,
		),
		'modified_user_id' => array (
			'name' => 'modified_user_id',
			'rname' => 'user_name',
			'id_name' => 'modified_user_id',
			'vname' => 'LBL_MODIFIED_BY',
			'type' => 'modified_user_name',
			'table' => 'users',
			'isnull' => false,
			'dbType' => 'id',
			'reportable'=>true,
		),
		'modified_user_id_link' => array (
			'name' => 'modified_user_id_link',
			'type' => 'link',
			'relationship' => 'schedulers_modified_user_id',
			'vname' => 'LBL_MODIFIED_BY_USER',
			'link_type' => 'one',
			'module' => 'Users',
			'bean_name' => 'User',
			'source' => 'non-db',
		),
		'created_by' => array (
			'name' => 'created_by',
			'rname' => 'user_name',
			'id_name' => 'modified_user_id',
			'vname' => 'LBL_ASSIGNED_TO',
			'type' => 'assigned_user_name',
			'table' => 'users',
			'isnull' => false,
			'dbType' => 'id'
		),
		'created_by_link' => array (
			'name' => 'created_by_link',
			'type' => 'link',
			'relationship' => 'schedulers_created_by',
			'vname' => 'LBL_CREATED_BY_USER',
			'link_type' => 'one',
			'module' => 'Users',
			'bean_name' => 'User',
			'source' => 'non-db',
		),
		'name' => array (
			'name' => 'name',
			'vname' => 'LBL_NAME',
			'type' => 'varchar',
			'len' => '255',
			'required' => true,
			'reportable' => false,
		),
		'status' => array (
			'name' => 'status',
			'vname' => 'LBL_STATUS',
			'type' => 'varchar',
			'len' => 100,
			'default' => 'Active',
			'required' => true,
			'reportable' => false,
		),
		'owner_id' => array (
			'name' => 'owner_id',
			'vname' => 'LBL_OWNER',
			'isnull' => false,
			'type' => 'id',
			'reportable'=>true,
			'importable' => 'required',
		),
		'queue_type' => array (
			'name' => 'queue_type',
			'vname' => 'LBL_QUEUE_TYPE',
			'type' => 'varchar',
			'len' => '35',
			'required' => true,
			'reportable' => false,
			'importable' => 'required',
		),
		'workflows' => array (
			'name' => 'workflows',
			'vname' => 'LBL_WORKFLOWS_USED',
			'type' => 'varchar',
			'len' => 50,
		),
		'persistent_memory' => array (
			'name'	=> 'persistent_memory',
			'vname'	=> 'LBL_PERSISTENT_MEMORY',
			'type'	=> 'varchar',
			'len'	=> 255,
			'required' => false,
			'reportable' => false,
		),
		'queuedItems' => array (
			'name' => 'queuedItems',
			'vname' => 'LBL_QUEUED_ITEMS',
			'source' => 'non-db',
			'type' => 'non-db',
		),
		/* relationship definitions */
		'child_queues'	=> array (
			'name'			=> 'child_queues',
			'vname' 		=> 'LBL_CHILD_QUEUES_REL',
			'type' 			=> 'link',
			'relationship' 	=> 'child_queues_rel',
			'module' 		=> 'Queues',
			'bean_name' 	=> 'Queue',
			'source' 		=> 'non-db',
		),
		'parent_queues'	 => array (
			'name' 			=> 'parent_queues',
			'vname' 		=> 'LBL_PARENT_QUEUES_REL',
			'type' 			=> 'link',
			'relationship' 	=> 'parent_queues_rel',
			'module' 		=> 'Queues',
			'bean_name' 	=> 'Queue',
			'source' 		=> 'non-db',
		),
		'queues_emails'	=> array (
			'name'			=> 'queues_emails',
			'vname'			=> 'LBL_QUEUES_EMAILS_REL',
			'type'			=> 'link',
			'relationship'	=> 'queues_emails_rel',
			'module'		=> 'Queues',
			'bean_name'		=> 'Queue',
			'source'		=> 'non-db',
		),
		'queues_workflows'	=> array (
			'name'			=> 'queues_workflow',
			'vname'			=> 'LBL_QUEUES_WORKFLOW_REL',
			'type'			=> 'link',
			'relationship'	=> 'queues_workflow_rel',
			'module'		=> 'Queues',
			'bean_name'		=> 'Queue',
			'source'		=> 'non-db',
		),

	), /* end fields() */
	'indices' => array (
		array(
			'name' =>'queuespk',
			'type' =>'primary',
			'fields' => array(
				'id'
			)
		),
	), /* end indices */
//	'relationships' => array (
//		'queues_emails_rel' => array(
//			'lhs_module'					=> 'Queues',
//			'lhs_table'						=> 'queues',
//			'lhs_key' 						=> 'id',
//			'rhs_module'					=> 'Emails',
//			'rhs_table'						=> 'emails',
//			'rhs_key' 						=> 'id',
//			'relationship_type' 			=> 'one-to-many',
//			'join_table'					=> 'queues_beans',
//			'join_key_rhs'					=> 'object_id',
//			'join_key_lhs'					=> 'queue_id',
//			'relationship_role_column_value'=> 'Emails'
//		),
//	), /* end relationship definitions */
);

VardefManager::createVardef('Queues','Queue', array(
'team_security',
));
?>
