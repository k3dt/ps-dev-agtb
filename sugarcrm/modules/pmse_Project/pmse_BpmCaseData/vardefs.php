<?php
$dictionary['pmse_BpmCaseData'] = array(
	'table'=>'pmse_bpm_case_data',
	'audited'=>false,
	'activity_enabled'=>false,
    'reassignable' => false,
		'duplicate_merge'=>true,
		'fields'=>array ('cas_id' =>
  array (
      'required' => true,
      'name' => 'cas_id',
      'vname' => 'Unique Identifier for this Case',
      'type' => 'int',
      'massupdate' => false,
      'default' => '0',
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
      'len' => '4',
      'size' => '20',
      'enable_range_search' => false,
      'disable_num_format' => '',
      'min' => false,
      'max' => false,
  ),
'cas_data' =>
  array (
    'required' => true,
    'name' => 'cas_data',
    'vname' => 'internal variables for each case',
    'type' => 'text',
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
    'size' => '20',
    'rows' => '4',
    'cols' => '20',
  ),
),
	'relationships'=>array (
),
	'optimistic_locking'=>true,
		'unified_search'=>true,
	);
if (!class_exists('VardefManager')){
        require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef('pmse_BpmCaseData','pmse_BpmCaseData', array('basic','assignable'));