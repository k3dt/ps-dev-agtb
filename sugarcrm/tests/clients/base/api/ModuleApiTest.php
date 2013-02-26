<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


require_once ("clients/base/api/ModuleApi.php");
require_once ("tests/SugarTestRestUtilities.php");
/**
 * @group ApiTests
 */
class ModuleApiTest extends Sugar_PHPUnit_Framework_TestCase {

    public $accounts;
    public $roles;
    public $moduleApi;
    public $serviceMock;

    public function setUp() {
        SugarTestHelper::setUp("current_user");        
        // load up the unifiedSearchApi for good times ahead
        $this->moduleApi = new ModuleApi();
        $account = BeanFactory::newBean('Accounts');
        $account->name = "ModulaApiTest setUp Account";
        $account->save();
        $this->accounts[] = $account;
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    public function tearDown() {
        $GLOBALS['current_user']->is_admin = 1;        
        // delete the bunch of accounts crated
        foreach($this->accounts AS $account) {
            $account->mark_deleted($account->id);
        }
        unset($_SESSION['ACL']);
        SugarTestHelper::tearDown();
        parent::tearDown();        
    }

    // test set favorite
    public function testSetFavorite() {
        $result = $this->moduleApi->setFavorite($this->serviceMock, array('module' => 'Accounts','record' => $this->accounts[0]->id));
        $this->assertTrue((bool) $result['my_favorite'], "Was not set to true");
    }
    // test remove favorite
    public function testRemoveFavorite() {
        $result = $this->moduleApi->setFavorite($this->serviceMock, array('module' => 'Accounts','record' => $this->accounts[0]->id));
        $this->assertTrue((bool) $result['my_favorite'], "Was not set to true");

        $result = $this->moduleApi->unsetFavorite($this->serviceMock, array('module' => 'Accounts','record' => $this->accounts[0]->id));
        $this->assertFalse((bool) $result['my_favorite'], "Was not set to false");
    }
    // test set favorite of deleted record
    public function testSetFavoriteDeleted() {
        $this->accounts[0]->mark_deleted($this->accounts[0]->id);
        $this->setExpectedException(
          'SugarApiExceptionNotFound', "Could not find record: {$this->accounts[0]->id} in module: Accounts"
        );
        $result = $this->moduleApi->setFavorite($this->serviceMock, array('module' => 'Accounts','record' => $this->accounts[0]->id));
        
    }
    // test remove favorite of deleted record
    public function testRemoveFavoriteDeleted() {
        $result = $this->moduleApi->setFavorite($this->serviceMock, array('module' => 'Accounts','record' => $this->accounts[0]->id));
        $this->assertTrue((bool) $result['my_favorite'], "Was not set to true");

        $this->accounts[0]->deleted = 1;
        $this->accounts[0]->save();
        $this->setExpectedException(
          'SugarApiExceptionNotFound', "Could not find record: {$this->accounts[0]->id} in module: Accounts"
        );

        $result = $this->moduleApi->setFavorite($this->serviceMock, array('module' => 'Accounts','record' => $this->accounts[0]->id));
    }
    // test set my_favorite on bean
    public function testSetFavoriteOnBean() {
        $result = $this->moduleApi->updateRecord($this->serviceMock, array('module' => 'Accounts','record' => $this->accounts[0]->id, "my_favorite" => true));
        $this->assertTrue((bool) $result['my_favorite'], "Was not set to true");
    }
    // test remove my_favorite on bean
    public function testRemoveFavoriteOnBean() {
        $result = $this->moduleApi->updateRecord($this->serviceMock, array('module' => 'Accounts','record' => $this->accounts[0]->id, "my_favorite" => true));
        $this->assertTrue((bool) $result['my_favorite'], "Was not set to true");

        $result = $this->moduleApi->updateRecord($this->serviceMock, array('module' => 'Accounts','record' => $this->accounts[0]->id, "my_favorite" => false));
        $this->assertFalse((bool) $result['my_favorite'], "Was not set to False");        
    }

    public function testViewNoneCreate() {
        // setup ACL
        $_SESSION['ACL'][$GLOBALS['current_user']->id]['Accounts']['module']['view']['aclaccess'] = -99;
        // create a record
        $result = $this->moduleApi->createRecord(new ModuleApiServiceMockUp, array('module' => 'Accounts','name' => 'Test Account'));
        // verify only id returns
        $this->assertNotEmpty($result);
        $this->assertEquals(count($result), 1);
        // delete the record
        $result = $this->moduleApi->deleteRecord(new ModuleApiServiceMockUp, array('module' => 'Accounts','record'=>$result['id']));
    }

}

class ModuleApiServiceMockUp extends RestService
{
    public function execute() {}
    protected function handleException(Exception $exception) {}
}
