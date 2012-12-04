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

require_once 'include/database/DBManagerFactory.php';
require_once 'modules/Contacts/Contact.php';
require_once 'tests/include/database/TestBean.php';
require_once 'include/SugarQuery/SugarQuery.php';

class ConditionTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var DBManager
     */
    private $_db;
    protected $created = array();

    protected $backupGlobals = FALSE;

    protected $contacts = array();
    protected $accounts = array();

    static public function setupBeforeClass()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    static public function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();

    }

    public function setUp()
    {
        if(empty($this->_db)){
            $this->_db = DBManagerFactory::getInstance();
        }

        $this->opportunity_bean = BeanFactory::newBean('Opportunities');

        for($x=100;$x<=300;$x++) {
            // create a new contact
            $opp = BeanFactory::newBean('Opportunities');
            $opp->name = "Test {$x}";
            $opp->amount = $x;

            $opp->save();
            $this->opportunities[] = $opp;
        }

        unset($opp);

    }

    public function tearDown()
    {
        if( !empty($this->opportunities) ) {
            $oppList = array();
            foreach($this->opportunities as $opp) {
                $oppList[] = $opp->id;
            }
            $this->_db->query("DELETE FROM opportunities WHERE id IN ('" . implode("','", $oppList) . "')");
            $this->_db->query("DELETE FROM opportunities_cstm WHERE id_c IN ('" . implode("','", $oppList) . "')");
        }
    }

    public function testEquals()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "amount"));
        $sq->from($this->opportunity_bean);
        $sq->where()->equals('amount', 200, $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 1, "Wrong row count, actually received: " . count($result) . " back.");

        foreach($result AS $opp) {
            $this->assertEquals(200,$opp['amount'], "The amount was not less than 2000 it was: {$opp['amount']}");
        }
    }

    public function testContains()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "amount"));
        $sq->from($this->opportunity_bean);
        $sq->where()->contains('name', 10, $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 12, "Wrong row count, actually received: " . count($result) . " back.");

        foreach($result AS $opp) {
            $test_string = strstr($opp['name'], '10');
            $this->assertTrue(!empty($test_string), "The name did not contain 10 it was: {$opp['name']}");
        }
    }

    public function testStartsWith()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "amount"));
        $sq->from($this->opportunity_bean);
        $sq->where()->starts('name', 'Test 10', $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 10, "Wrong row count, actually received: " . count($result) . " back.");

        foreach($result AS $opp) {
            $test_string = stristr($opp['name'], 'Test 10');
            $this->assertTrue(!empty($test_string), "The name did not start with Test 10 it was: {$opp['name']}");
        }
    }

    public function testLessThan()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "amount"));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->lt('amount',200, $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 100, "Wrong row count, actually received: " . count($result) . " back.");

        foreach($result AS $opp) {
            $this->assertLessThan(200,$opp['amount'], "The amount was not less than 2000 it was: {$opp['amount']}");
        }
    }

    public function testLessThanEquals()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "amount"));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->lte('amount',200, $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 101, "Wrong row count, actually received: " . count($result) . " back.");

        foreach($result AS $opp) {
            $this->assertLessThanOrEqual(200,$opp['amount'], "The amount was not less than 2000 it was: {$opp['amount']}");
        }
    }

    public function testGreaterThan()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "amount"));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->gt('amount',200, $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 100, "Wrong row count, actually received: " . count($result) . " back.");

        foreach($result AS $opp) {
            $this->assertGreaterThan(200,$opp['amount'], "The amount was not less than 2000 it was: {$opp['amount']}");
        }
    }

    public function testGreaterThanEquals()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "amount"));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->gte('amount',200, $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 101, "Wrong row count, actually received: " . count($result) . " back.");

        foreach($result AS $opp) {
            $this->assertGreaterThanOrEqual(200,$opp['amount'], "The amount was not less than 2000 it was: {$opp['amount']}");
        }
    }

    public function testIn()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "amount"));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->in('amount',array(100,101,102,103,104,105), $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 6, "Wrong row count, actually received: " . count($result) . " back.");
    }

    public function testBetween()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "amount"));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->between('amount',110, 120, $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 11, "Wrong row count, actually received: " . count($result) . " back.");
    }

    public function testNotNull() {
        $sq = new SugarQuery();

        $sq->select(array("name", "amount"));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->notNull('amount', $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 201, "Wrong row count, actually received: " . count($result) . " back.");

    }

    public function testNull() {
        $sq = new SugarQuery();

        $sq->select(array("name", "amount"));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->isNull('amount', $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 0, "Wrong row count, actually received: " . count($result) . " back.");

    }

    public function testRaw() {
        $sq = new SugarQuery();

        $sq->select(array("name", "amount"));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->addRaw("name = 'Test 131'");

        $result = $sq->execute();

        $this->assertEquals(count($result), 1, "Wrong row count, actually received: " . count($result) . " back.");

        $result = reset($result);

        $this->assertEquals($result['name'], "Test 131", "Wrong record returned, received: " . $result['name']);

    }

    public function testOrderByLimit() {
        $sq = new SugarQuery();
        $sq->select("name", "amount");
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->orderBy("amount", "ASC");
        $sq->limit(2);

        $result = $sq->execute();

        $this->assertEquals(count($result), 2, "Wrong row count, actually received: " . count($result) . " back.");

        $low = $result[0];
        $high = $result[1];

        $this->assertGreaterThan($low, $high, "{$high} is not greater than {$low}");

        $sq = new SugarQuery();
        $sq->select("name", "amount");
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->orderBy("amount", "ASC");
        $sq->limit(2);
        $sq->offset(1);

        $result = $sq->execute();

        $this->assertEquals(count($result), 2, "Wrong row count, actually received: " . count($result) . " back.");

        $low = $result[0];
        $high = $result[1];

        $this->assertGreaterThan($low, $high, "{$high} is not greater than {$low}");


    }

}