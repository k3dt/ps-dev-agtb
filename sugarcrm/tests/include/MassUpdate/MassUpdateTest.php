<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
 
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
