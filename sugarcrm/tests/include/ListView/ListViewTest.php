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
 
require_once 'include/ListView/ListView.php';

class ListViewTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->_lv = new ListView();
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
    }

    public function tearDown()
    {
        unset($this->_lv);
    	SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    	unset($GLOBALS['current_user']);
    	unset($GLOBALS['app_strings']);
    }

    public function sortOrderProvider()
    {
        // test data in order (request,session,subpaneldefs,default,expected return)
        return array (
            array('asc' ,'desc' ,'desc' ,'desc' ,'asc'),
            array('desc','asc'  ,'asc'  ,'asc'  ,'desc'),
            array(null  ,'asc'  ,'desc' ,'desc' ,'asc'),
            array(null  ,'desc' ,'asc'  ,'asc'  ,'desc'),
            array(null  ,null   ,'asc'  ,'desc' ,'asc'),
            array(null  ,null   ,'desc' ,'asc'  ,'desc'),
            array(null  ,null   ,null   ,'asc'  ,'asc'),
            array(null  ,null   ,null   ,'desc' ,'desc')
        ) ;
    }
    /**
     * @group bug48665
     * @dataProvider sortOrderProvider
     */
    public function testCalculateSortOrder($req,$sess,$subpdefs,$default,$expected)
    {
        $sortOrder = array(
            'request' => $req,
            'session' => $sess,
            'subpaneldefs' => $subpdefs,
            'default' => $default,
        );
        $actual = $this->_lv->calculateSortOrder($sortOrder);
        $this->assertEquals($expected, $actual, 'Sort order is wrong');
    }

}
