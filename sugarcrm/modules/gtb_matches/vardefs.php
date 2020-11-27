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

$dictionary['gtb_matches'] = array(
    'table' => 'gtb_matches',
    'audited' => true,
    'activity_enabled' => true,
    'duplicate_merge' => true,
    'fields' => array (
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
    'importable' => 'false',
    'duplicate_merge' => 'disabled',
    'merge_filter' => 'disabled',
    'duplicate_on_record_copy' => 'always',
    'massupdate' => false,
    'hidemassupdate' => false,
    'no_default' => false,
    'comments' => '',
    'help' => '',
    'duplicate_merge_dom_value' => '0',
    'audited' => false,
    'reportable' => true,
    'pii' => false,
    'default' => '',
    'calculated' => false,
    'size' => '20',
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
    'duplicate_merge' => 'enabled',
    'duplicate_merge_dom_value' => '1',
    'audited' => true,
    'reportable' => true,
    'unified_search' => false,
    'merge_filter' => 'disabled',
    'pii' => false,
    'default' => 'Open',
    'calculated' => false,
    'len' => 100,
    'size' => '20',
    'options' => 'gtb_matches_status_list',
    'dependency' => false,
  ),
  'stage' => 
  array (
    'required' => false,
    'name' => 'stage',
    'vname' => 'LBL_STAGE',
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
    'options' => 'gtb_matches_stage_list',
    'dependency' => false,
  ),
  'fulfillment' => 
  array (
    'required' => false,
    'name' => 'fulfillment',
    'vname' => 'LBL_FULFILLMENT',
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
    'options' => 'gtb_fulfillment_list',
    'dependency' => false,
  ),
  'func_mobility_fulfilled' => 
  array (
    'required' => false,
    'name' => 'func_mobility_fulfilled',
    'vname' => 'LBL_FUNC_MOBILITY_FULFILLED',
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
    'options' => 'gtb_fulfillment_list',
    'dependency' => false,
  ),
  'geo_mobility_fulfilled' => 
  array (
    'required' => false,
    'name' => 'geo_mobility_fulfilled',
    'vname' => 'LBL_GEO_MOBILITY_FULFILLED',
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
    'options' => 'gtb_fulfillment_list',
    'dependency' => false,
  ),
  'oe_mobility_fulfilled' => 
  array (
    'required' => false,
    'name' => 'oe_mobility_fulfilled',
    'vname' => 'LBL_OE_MOBILITY_FULFILLED',
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
    'options' => 'gtb_fulfillment_list',
    'dependency' => false,
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
VardefManager::createVardef('gtb_matches','gtb_matches', array('basic','team_security','assignable','taggable'));