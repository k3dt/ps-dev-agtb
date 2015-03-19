<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */


$dictionary['pmse_Inbox'] = array(
	'table'=>'pmse_inbox',
	'audited'=>false,
	'activity_enabled'=>true,
    'reassignable' => false,
		'duplicate_merge'=>true,
		'fields'=>array (
  'cas_id' => 
  array (
    'required' => true,
    'name' => 'cas_id',
    'vname' => 'LBL_CAS_ID',
    'type' => 'int',
    'auto_increment' => true,
    'massupdate' => false,
    'no_default' => false,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => false,
    'reportable' => true,
    'unified_search' => false,
    'merge_filter' => 'disabled',
    'full_text_search' => 
    array (
      'boost' => '0',
    ),
    'calculated' => false,
    'len' => '255',
    'size' => '20',
    'enable_range_search' => false,
    'disable_num_format' => '',
    'min' => false,
    'max' => false,
  ),
  'cas_parent' => 
  array (
    'required' => true,
    'name' => 'cas_parent',
    'vname' => 'LBL_CAS_PARENT',
    'type' => 'int',
    'massupdate' => false,
    'no_default' => false,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => false,
    'reportable' => true,
    'unified_search' => false,
    'merge_filter' => 'disabled',
    'full_text_search' => 
    array (
      'boost' => '0',
    ),
    'calculated' => false,
    'len' => '255',
    'size' => '20',
    'enable_range_search' => false,
    'disable_num_format' => '',
    'min' => false,
    'max' => false,
  ),
  'cas_status' => 
  array (
    'required' => true,
    'name' => 'cas_status',
    'vname' => 'LBL_CAS_STATUS',
    'type' => 'varchar',
    'massupdate' => false,
    'default' => 'IN PROGRESS',
    'no_default' => false,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => false,
    'reportable' => true,
    'unified_search' => false,
    'merge_filter' => 'disabled',
    'full_text_search' => 
    array (
      'boost' => '0',
    ),
    'calculated' => false,
    'len' => '32',
    'size' => '20',
  ),
  'pro_id' => 
  array (
    'required' => true,
    'name' => 'pro_id',
    'vname' => 'LBL_PRO_ID',
    'type' => 'varchar',
    'massupdate' => false,
    'no_default' => false,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => false,
    'reportable' => true,
    'unified_search' => false,
    'merge_filter' => 'disabled',
    'full_text_search' => 
    array (
      'boost' => '0',
    ),
    'calculated' => false,
    'len' => '36',
    'size' => '20',
  ),
  'cas_title' => 
  array (
    'required' => true,
    'name' => 'cas_title',
    'vname' => 'LBL_CAS_TITLE',
    'type' => 'varchar',
    'massupdate' => false,
    'no_default' => false,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => false,
    'reportable' => true,
    'unified_search' => false,
    'merge_filter' => 'disabled',
    'full_text_search' => 
    array (
      'boost' => '0',
    ),
    'calculated' => false,
    'len' => '255',
    'size' => '20',
  ),
  'pro_title' => 
  array (
    'required' => false,
    'name' => 'pro_title',
    'vname' => 'LBL_PRO_TITLE',
    'type' => 'varchar',
    'massupdate' => false,
    'no_default' => false,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => false,
    'reportable' => true,
    'unified_search' => false,
    'merge_filter' => 'disabled',
    'full_text_search' => 
    array (
      'boost' => '0',
    ),
    'calculated' => false,
    'len' => '255',
    'size' => '20',
  ),
  'cas_custom_status' => 
  array (
    'required' => false,
    'name' => 'cas_custom_status',
    'vname' => 'LBL_CAS_CUSTOM_STATUS',
    'type' => 'varchar',
    'massupdate' => false,
    'no_default' => false,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => false,
    'reportable' => true,
    'unified_search' => false,
    'merge_filter' => 'disabled',
    'full_text_search' => 
    array (
      'boost' => '0',
    ),
    'calculated' => false,
    'len' => '32',
    'size' => '20',
  ),
  'cas_init_user' => 
  array (
    'required' => false,
    'name' => 'cas_init_user',
    'vname' => 'LBL_CAS_INIT_USER',
    'type' => 'varchar',
    'massupdate' => false,
    'no_default' => false,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => false,
    'reportable' => true,
    'unified_search' => false,
    'merge_filter' => 'disabled',
    'full_text_search' => 
    array (
      'boost' => '0',
    ),
    'calculated' => false,
    'len' => '36',
    'size' => '20',
  ),
  'cas_create_date' => 
  array (
    'required' => false,
    'name' => 'cas_create_date',
    'vname' => 'LBL_CAS_CREATE_DATE',
    'type' => 'datetimecombo',
    'massupdate' => true,
    'no_default' => false,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => false,
    'reportable' => true,
    'unified_search' => false,
    'merge_filter' => 'disabled',
    'calculated' => false,
    'size' => '20',
    'enable_range_search' => false,
    'dbType' => 'datetime',
    'display_default' => 'now&12:00am',
  ),
  'cas_update_date' => 
  array (
    'required' => false,
    'name' => 'cas_update_date',
    'vname' => 'LBL_CAS_UPDATE_DATE',
    'type' => 'datetimecombo',
    'massupdate' => true,
    'no_default' => false,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => false,
    'reportable' => true,
    'unified_search' => false,
    'merge_filter' => 'disabled',
    'calculated' => false,
    'size' => '20',
    'enable_range_search' => false,
    'dbType' => 'datetime',
  ),
  'cas_finish_date' => 
  array (
    'required' => false,
    'name' => 'cas_finish_date',
    'vname' => 'LBL_CAS_FINISH_DATE',
    'type' => 'datetimecombo',
    'massupdate' => true,
    'no_default' => false,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => false,
    'reportable' => true,
    'unified_search' => false,
    'merge_filter' => 'disabled',
    'calculated' => false,
    'size' => '20',
    'enable_range_search' => false,
    'dbType' => 'datetime',
  ),
  'cas_pin' => 
  array (
    'required' => false,
    'name' => 'cas_pin',
    'vname' => 'LBL_CAS_PIN',
    'type' => 'varchar',
    'massupdate' => false,
    'default' => '0000',
    'no_default' => false,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => false,
    'reportable' => true,
    'unified_search' => false,
    'merge_filter' => 'disabled',
    'full_text_search' => 
    array (
      'boost' => '0',
    ),
    'calculated' => false,
    'len' => '10',
    'size' => '20',
  ),
  'cas_assigned_status' => 
  array (
    'required' => false,
    'name' => 'cas_assigned_status',
    'vname' => 'LBL_CAS_ASSIGNED_STATUS',
    'type' => 'varchar',
    'massupdate' => false,
    'default' => 'UNASSIGNED',
    'no_default' => false,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => false,
    'reportable' => true,
    'unified_search' => false,
    'merge_filter' => 'disabled',
    'full_text_search' => 
    array (
      'boost' => '0',
    ),
    'calculated' => false,
    'len' => '12',
    'size' => '20',
  ),
    'name' => array(
        'name' => 'name',
        'vname' => 'LBL_NAME',
        'type' => 'name',
        'link' => true, // bug 39288
        'dbType' => 'varchar',
        'len' => 255,
        'unified_search' => false,
        'full_text_search' => array('enabled' => true, 'boost' => 3),
        'required' => true,
        'importable' => 'required',
        'duplicate_merge' => 'enabled',
        'merge_filter' => 'selected',
        'duplicate_on_record_copy' => 'always',
    ),
    ),
	'relationships'=>array (
),
    'indices' => array(
    array(
      'name'   => 'idx_pmse_inbox_case_id',
      'type'   => 'index',
      'fields' => array('cas_id')
    ),
),
    'optimistic_locking' => true,
    'unified_search' => true,
    'acls' => array('SugarACLDeveloperOrAdmin' => array('aclModule' => 'pmse_Inbox', 'allowUserRead' => true)),
    'hidden_to_role_assignment' => true,
    // @TODO Fix the Default and Basic SugarObject templates so that Basic
    // implements Default. This would allow the application of various
    // implementations on Basic without forcing Default to have those so that
    // situations like this - implementing taggable - doesn't have to apply to
    // EVERYTHING. Since there is no distinction between basic and default for
    // sugar objects templates yet, we need to forecefully remove the taggable
    // implementation fields. Once there is a separation of default and basic
    // templates we can safely remove these as this module will implement
    // default instead of basic.
    'ignore_templates' => array(
        'taggable',
    ),
);
if (!class_exists('VardefManager')){
        require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef('pmse_Inbox','pmse_Inbox', array('basic','team_security','assignable'));
