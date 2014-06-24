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
 
require_once 'include/MassUpdate.php';
require_once 'include/dir_inc.php';

class MassUpdateTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
		$GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
		$GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
	}

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['app_strings']);
    }
    
    /**
     * @ticket 12300
     */
    public function testAdddateWorksWithMultiByteCharacters()
    {
        $mass = new MassUpdate();
        $displayname = "开始日期:";
        $varname = "date_start";
        
        $result = $mass->addDate($displayname , $varname);
        $pos_f = strrpos($result, $GLOBALS['app_strings']['LBL_MASSUPDATE_DATE']);
        $this->assertTrue((bool) $pos_f);
    }
    
    /**
     * @ticket 23900
     */
    public function testAddStatus() 
    {
        $mass = new MassUpdate();
        $options = array (
            '10' => 'ten',
            '20' => 'twenty',
            '30' => 'thirty',
            );
        $result = $mass->addStatus('test_dom', 'test_dom', $options);
        preg_match_all('/value=[\'\"].*?[\'\"]/si', $result, $matches);
       /* $this->assertTrue(isset($matches));
        $this->assertTrue($matches[0][0] == "value=''");
        $this->assertTrue($matches[0][2] == "value='10'");
        $this->assertTrue($matches[0][3] == "value='20'"); */
        $this->assertTrue($matches[0][0] == "value=''");
        $this->assertTrue($matches[0][1] == "value='__SugarMassUpdateClearField__'");
        $this->assertTrue($matches[0][2] == "value='10'");
        $this->assertTrue($matches[0][3] == "value='20'");
        $this->assertTrue($matches[0][4] == "value='30'");       	
    }
    
    /**
     * @ticket 23900
     */
    public function testAddStatusMulti() 
    {
        $mass = new MassUpdate();
        $options = array (
            '10' => 'ten',
            '20' => 'twenty',
            '30' => 'thirty',
            );
        
        $result = $mass->addStatusMulti('test_dom', 'test_dom', $options);
        preg_match_all('/value=[\'\"].*?[\'\"]/si', $result, $matches);
        $this->assertTrue(isset($matches));
        /*$this->assertTrue($matches[0][0] == "value=''");
        $this->assertTrue($matches[0][2] == "value='10'");
        $this->assertTrue($matches[0][3] == "value='20'"); */
        $this->assertTrue($matches[0][0] == "value=''");
        $this->assertTrue($matches[0][1] == "value='__SugarMassUpdateClearField__'");
        $this->assertTrue($matches[0][2] == "value='10'");
        $this->assertTrue($matches[0][3] == "value='20'");
        $this->assertTrue($matches[0][4] == "value='30'");       	
    }
}
