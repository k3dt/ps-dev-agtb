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

require_once('service/v3/SugarWebServiceUtilv3.php');
require_once('tests/service/APIv3Helper.php');
require_once 'tests/service/SOAPTestCase.php';

class Bug48748Test extends SOAPTestCase
{

    public function setUp()
    {
        $this->markTestIncomplete('Incomplete test');
    	$this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/service/v3/soap.php';
        parent::setUp();
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->status = 'Active';
        $GLOBALS['current_user']->is_admin = 1;
        $GLOBALS['current_user']->save();
        $this->useOutputBuffering = false;
    }

    public function tearDown()
    {
        parent::tearDown();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['beanList']);
    }


    public function testGetRelationshipsWithCustomFields()
    {
        $this->_login();
        $account = new Account();
        //$result = $this->_soapClient->get_module_layout($this->_sessionId, array($account), 'wireless', 'subpanel');
        //echo var_export($result, true);
        //$total = $GLOBALS['db']->getOne("SELECT count(id) AS total FROM users WHERE portal_only=0 AND deleted=0");
        //$this->assertArrayHasKey('list', $result, 'Assert that we have a list of results and that the get_data_list query on Employees does not cause an error');
    }

}