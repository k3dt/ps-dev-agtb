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

$dictionary['gtb_positions'] = array(
    'table' => 'gtb_positions',
    'audited' => true,
    'activity_enabled' => false,
    'duplicate_merge' => true,
    'fields' => array (
  'pos_function' => 
  array (
    'required' => true,
    'name' => 'pos_function',
    'vname' => 'LBL_POS_FUNCTION',
    'type' => 'enum',
    'massupdate' => true,
    'hidemassupdate' => false,
    'no_default' => false,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'enabled',
    'duplicate_merge_dom_value' => '1',
    'audited' => true,
    'reportable' => true,
    'unified_search' => false,
    'merge_filter' => 'disabled',
    'pii' => false,
    'default' => '',
    'calculated' => false,
    'len' => 100,
    'size' => '20',
    'options' => 'gtb_function_list',
    'dependency' => false,
  ),
  'name' => 
  array (
    'name' => 'name',
    'vname' => 'LBL_NAME',
    'type' => 'name',
    'dbType' => 'varchar',
    'len' => '255',
    'unified_search' => true,
    'full_text_search' => 
    array (
      'enabled' => true,
      'boost' => '1.55',
      'searchable' => true,
    ),
    'required' => true,
    'importable' => 'required',
    'duplicate_merge' => 'enabled',
    'merge_filter' => 'selected',
    'duplicate_on_record_copy' => 'always',
    'massupdate' => false,
    'hidemassupdate' => false,
    'no_default' => false,
    'comments' => '',
    'help' => '',
    'duplicate_merge_dom_value' => '3',
    'audited' => false,
    'reportable' => true,
    'pii' => false,
    'default' => '',
    'calculated' => false,
    'size' => '20',
  ),
  'description' => 
  array (
    'name' => 'description',
    'vname' => 'LBL_DESCRIPTION',
    'type' => 'text',
    'comment' => 'Full text of the note',
    'full_text_search' => 
    array (
      'enabled' => true,
      'boost' => '0.5',
      'searchable' => true,
    ),
    'rows' => '6',
    'cols' => '80',
    'duplicate_on_record_copy' => 'always',
    'required' => false,
    'massupdate' => false,
    'hidemassupdate' => false,
    'no_default' => false,
    'comments' => 'Full text of the note',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'enabled',
    'duplicate_merge_dom_value' => '1',
    'audited' => false,
    'reportable' => true,
    'unified_search' => true,
    'merge_filter' => 'disabled',
    'pii' => false,
    'default' => '',
    'calculated' => false,
    'size' => '20',
    'studio' => 'visible',
  ),
  'region' => 
  array (
    'required' => false,
    'name' => 'region',
    'vname' => 'LBL_REGION',
    'type' => 'enum',
    'massupdate' => true,
    'hidemassupdate' => false,
    'no_default' => false,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'enabled',
    'duplicate_merge_dom_value' => '1',
    'audited' => true,
    'reportable' => true,
    'unified_search' => false,
    'merge_filter' => 'disabled',
    'pii' => false,
    'default' => '',
    'calculated' => false,
    'len' => 100,
    'size' => '20',
    'options' => 'gtb_region_list',
    'dependency' => false,
  ),
        'country' =>
            array (
                'required' => false,
                'name' => 'country',
                'vname' => 'LBL_COUNTRY',
                'type' => 'enum',
                'massupdate' => true,
                'hidemassupdate' => false,
                'no_default' => false,
                'comments' => '',
                'help' => '',
                'importable' => 'true',
                'duplicate_merge' => 'enabled',
                'duplicate_merge_dom_value' => '1',
                'audited' => true,
                'reportable' => true,
                'unified_search' => false,
                'merge_filter' => 'disabled',
                'pii' => false,
                'default' => '',
                'calculated' => false,
                'len' => 100,
                'size' => '20',
                'options' => 'countries_dom',
                'dependency' => false,
            ),
  'org_unit' => 
  array (
    'required' => true,
    'name' => 'org_unit',
    'vname' => 'LBL_ORG_UNIT',
    'type' => 'enum',
    'massupdate' => false,
    'hidemassupdate' => false,
    'no_default' => false,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'enabled',
    'duplicate_merge_dom_value' => '1',
    'audited' => true,
    'reportable' => true,
    'unified_search' => true,
    'merge_filter' => 'disabled',
    'pii' => false,
    'default' => '',
    'calculated' => false,
    'len' => 100,
    'size' => '20',
    'options' => 'gtb_oe_mobility_list',
    'dependency' => false,
  ),
  'location' => 
  array (
    'required' => false,
    'name' => 'location',
    'vname' => 'LBL_LOCATION',
    'type' => 'varchar',
    'massupdate' => false,
    'hidemassupdate' => false,
    'no_default' => false,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'enabled',
    'duplicate_merge_dom_value' => '1',
    'audited' => true,
    'reportable' => true,
    'unified_search' => true,
    'merge_filter' => 'disabled',
    'pii' => false,
    'default' => '',
    'full_text_search' => 
    array (
      'enabled' => true,
      'boost' => '1',
      'searchable' => true,
    ),
    'calculated' => false,
    'len' => '255',
    'size' => '20',
  ),
  'gtb_cluster' => 
  array (
    'required' => true,
    'name' => 'gtb_cluster',
    'vname' => 'LBL_GTB_CLUSTER',
    'type' => 'enum',
    'massupdate' => true,
    'hidemassupdate' => false,
    'no_default' => false,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'enabled',
    'duplicate_merge_dom_value' => '1',
    'audited' => true,
    'reportable' => true,
    'unified_search' => false,
    'merge_filter' => 'disabled',
    'pii' => false,
    'default' => '',
    'calculated' => false,
    'len' => 100,
    'size' => '20',
    'options' => 'gtb_cluster_list',
    'dependency' => false,
  ),
  'gtb_source' => 
  array (
    'required' => true,
    'name' => 'gtb_source',
    'vname' => 'LBL_GTB_SOURCE',
    'type' => 'enum',
    'massupdate' => true,
    'hidemassupdate' => false,
    'no_default' => false,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => true,
    'reportable' => true,
    'unified_search' => false,
    'merge_filter' => 'disabled',
    'pii' => false,
    'default' => '',
    'calculated' => false,
    'len' => 100,
    'size' => '20',
    'options' => 'gtb_pos_source_list',
    'dependency' => false,
  ),
  'status' => 
  array (
    'required' => false,
    'name' => 'status',
    'vname' => 'LBL_STATUS',
    'type' => 'enum',
    'massupdate' => true,
    'hidemassupdate' => false,
    'no_default' => false,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => true,
    'reportable' => true,
    'unified_search' => false,
    'merge_filter' => 'disabled',
    'pii' => false,
    'default' => 'New',
    'calculated' => false,
    'len' => 100,
    'size' => '20',
    'options' => 'gtb_pos_status_list',
    'dependency' => false,
    'visibility_grid' => 
    array (
      'trigger' => 'process_step',
      'values' => 
      array (
        'Contacted_Recruiter' => 
        array (
          0 => 'In_Progress',
        ),
        'Contacted_Manager' => 
        array (
          0 => 'In_Progress',
        ),
        'Identify_Candidates' => 
        array (
          0 => 'In_Progress',
        ),
        'Profiles_Sent' => 
        array (
          0 => 'In_Progress',
        ),
        'Refined_Search' => 
        array (
          0 => 'In_Progress',
        ),
        'On_Hold' => 
        array (
          0 => 'In_Progress',
        ),
        'Filled_by_GTB' => 
        array (
          0 => 'Closed',
        ),
        'Not_Filled_by_GTB' => 
        array (
          0 => 'Closed',
        ),
        'Out_of_Scope' => 
        array (
          0 => 'Closed',
        ),
        'Not_Truly_Vacant' => 
        array (
          0 => 'Closed',
        ),
        '' => 
        array (
          0 => 'New',
        ),
      ),
    ),
  ),
  'process_step' => 
  array (
    'required' => true,
    'name' => 'process_step',
    'vname' => 'LBL_PROCESS_STEP',
    'type' => 'enum',
    'massupdate' => true,
    'hidemassupdate' => false,
    'no_default' => false,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => true,
    'reportable' => true,
    'unified_search' => false,
    'merge_filter' => 'disabled',
    'pii' => false,
    'default' => '',
    'calculated' => false,
    'len' => 100,
    'size' => '20',
    'options' => 'gtb_pos_process_step_list',
    'dependency' => false,
  ),
  'real_position' => 
  array (
    'required' => false,
    'name' => 'real_position',
    'vname' => 'LBL_REAL_POSITION',
    'type' => 'enum',
    'massupdate' => true,
    'hidemassupdate' => false,
    'no_default' => false,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => true,
    'reportable' => true,
    'unified_search' => false,
    'merge_filter' => 'disabled',
    'pii' => false,
    'default' => '',
    'calculated' => false,
    'len' => 100,
    'size' => '20',
    'options' => 'gtb_real_position_list',
    'dependency' => false,
  ),
  'reason_not_filled' => 
  array (
    'required' => false,
    'name' => 'reason_not_filled',
    'vname' => 'LBL_REASON_NOT_FILLED',
    'type' => 'enum',
    'massupdate' => true,
    'hidemassupdate' => false,
    'no_default' => false,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => true,
    'reportable' => true,
    'unified_search' => false,
    'merge_filter' => 'disabled',
    'pii' => false,
    'default' => '',
    'calculated' => false,
    'dependency' => 'or(equal($process_step,"Not_Filled_by_GTB"),equal($process_step,"Out_of_Scope"),equal($process_step,"Not_Truly_Vacant"))',
    'len' => 100,
    'size' => '20',
    'options' => 'gtb_lost_reason_list',
  ),
),
    'relationships' => array (
),
    'optimistic_locking' => true,
    'unified_search' => true,
    'full_text_search' => true,
);

if (!class_exists('VardefManager')){
}
VardefManager::createVardef('gtb_positions','gtb_positions', array('basic','team_security','assignable','taggable'));
