<?php

class Bug40658Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setup()
    {
    	$this->useOutputBuffering = false;
    }
    
    public function testGetRelateJoin()
    {
		require_once('modules/DynamicFields/DynamicField.php');
		$dynamicField = new DynamicField();
		$account = new Account();
		$dynamicField->bean = $account;
		
		$field_def = array(
			'dependency' => '',
		    'required' => '',
		    'source' => 'non-db',
		    'name' => 'm1_related_c',
		    'vname' => 'LBL_M1_RELATED',
		    'type' => 'relate',
		    'massupdate' => 0,
		    'default' => '',
		    'comments' => '',
		    'help' => '',
		    'importable' => true,
		    'duplicate_merge' => 'disabled',
		    'duplicate_merge_dom_value' => 0,
		    'audited' => '',
		    'reportable' => 1,
		    'calculated' => '',
		    'len' => 255,
		    'size' => 20,
		    'id_name' => 'def_m1_id_c',
		    'ext2' => 'Accounts',
		    'module' => 'Accounts',
		    'rname' => 'name',
		    'quicksearch' => 'enabled',
		    'studio' => 'visible',
		    'id' => 'def_M1m1_related_c',
		    'custom_module' => 'Accounts',
	    );
		
	    $joinTableAlias = 'jt1';
	    $relatedJoinInfo = $dynamicField->getRelateJoin($field_def, $joinTableAlias);
	    $this->assertEquals(', accounts_cstm.def_m1_id_c, jt1.name m1_related_c ', $relatedJoinInfo['select']);
    }
    
    public function testSubpanelMetaDataParser()
    {
    	$subpanelMetaDataParser = new SubpanelMetaDataParserMock('Bug40658Test', 'Accounts');
        $defs = array('m1_related_c' => 
        			array (
					  'type' => 'relate',
					  'default' => true,
					  'studio' => 'visible',
					  'vname' => 'LBL_M2_RELATED',
					  'width' => '10%',
					)
				);
    	$result = $subpanelMetaDataParser->makeRelateFieldsAsLink($defs);
		$this->assertEquals('SubPanelDetailViewLink', $result['m1_related_c']['widget_class']);
		$this->assertEquals('def_M1', $result['m1_related_c']['target_module']);
		$this->assertEquals('def_m1_id_c', $result['m1_related_c']['target_record_key']);        	
    }
}

require_once('modules/ModuleBuilder/parsers/views/SubpanelMetaDataParser.php');

class SubpanelMetaDataParserMock extends SubpanelMetaDataParser
{
	//Override constructor... don't do anything
	function __construct ($subpanelName , $moduleName , $packageName = '')
	{
		
	}
		
	public function makeRelateFieldsAsLink($defs)
	{
		$this->_moduleName = 'def_M1';
		$this->_fielddefs = array('m1_related_c' => 
			array (
			  'dependency' => '',
			  'required' => false,
			  'source' => 'non-db',
			  'name' => 'm1_related_c',
			  'vname' => 'LBL_M1_RELATED',
			  'type' => 'relate',
			  'massupdate' => '0',
			  'default' => true,
			  'comments' => '',
			  'help' => '',
			  'importable' => 'true',
			  'duplicate_merge' => 'disabled',
			  'duplicate_merge_dom_value' => '0',
			  'audited' => false,
			  'reportable' => true,
			  'calculated' => false,
			  'len' => '255',
			  'size' => '20',
			  'id_name' => 'def_m1_id_c',
			  'ext2' => 'def_M1',
			  'module' => 'def_M1',
			  'rname' => 'name',
			  'quicksearch' => 'enabled',
			  'studio' => 'visible',
			  'id' => 'def_M1m1_related_c',
			  'custom_module' => 'def_M1',
			  'label' => 'm1_related_c',
			  'width' => '10%',
			  'widget_class' => 'SubPanelDetailViewLink',
			  'target_module' => 'def_M1',
			  'target_record_key' => 'def_m1_id_c',
			)
	    );
		

	       
		return parent::makeRelateFieldsAsLink($defs);
				
	}
}