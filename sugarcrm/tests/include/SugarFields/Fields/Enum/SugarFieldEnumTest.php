<?php 
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
 
require_once('include/SugarFields/Fields/Relate/SugarFieldRelate.php');

class SugarFieldEnumTest extends Sugar_PHPUnit_Framework_TestCase
{
	public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
	}

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }
    
     /**
     * @ticket 36744
     */
	public function testFormatEnumField()
	{
	    $langpack = new SugarTestLangPackCreator();
	    $langpack->setAppListString('case_priority_dom',
	        array (
                'P1' => 'High',
                'P2' => 'Medium',
                'P3' => 'Low',
                )
            );
        $langpack->save();
        
		$GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
		$fieldDef = array (
					    'name' => 'priority',
					    'vname' => 'LBL_PRIORITY',
					    'type' => 'enum',
					    'options' => 'case_priority_dom',
					    'len'=>25,
					    'audited'=>true,
					    'comment' => 'The priority of the case',
					);
		$field_value = "P2";
		
        require_once('include/SugarFields/SugarFieldHandler.php');
   		$sfr = SugarFieldHandler::getSugarField('enum');
    	
   	 	$this->assertEquals(trim($sfr->formatField($field_value,$fieldDef)),'Medium');
    }
}