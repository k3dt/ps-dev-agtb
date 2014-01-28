<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
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
    private static $db;
    protected static $opportunities = array();
    protected static $oppIds = array();

    protected $created = array();

    protected $backupGlobals = false;

    protected $contacts = array();
    protected $accounts = array();

    static public function setupBeforeClass()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');

        if (empty(self::$db)) {
            self::$db = DBManagerFactory::getInstance();
        }


        // "Delete" all the opportunities that may currently exist
        $sql = "SELECT id FROM opportunities WHERE deleted = 0";
        $res = self::$db->query($sql);
        while ($row = self::$db->fetchRow($res)) {
            self::$oppIds[] = $row['id'];
        }

        if (self::$oppIds) {
            $sql = "UPDATE opportunities SET deleted = 1 WHERE id IN ('" . implode("','", self::$oppIds) . "')";
            self::$db->query($sql);
        }

        for ($x = 100; $x <= 300; $x++) {
            // create a new contact
            $id = create_guid();
            $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
            $opportunity = SugarTestOpportunityUtilities::createOpportunity($id);
            $opportunity->revenuelineitems->add($rli);
            $opportunity->name = "SugarQuery Unit Test {$x}";
            $opportunity->probability = $x;
            $opportunity->date_modified = $opportunity->date_entered = date('Y-m-d');
            $opportunity->date_closed = $rli->date_closed = date('Y-m-d');

            $rli->opportunity_id = $id;

            $rli->save();
            $opportunity->save();
            self::$opportunities[] = $opportunity;
        }

        unset($opportunity);
    }

    static public function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
        if (!empty(self::$opportunities)) {
            $oppList = array();
            foreach (self::$opportunities as $opp) {
                $oppList[] = $opp->id;
            }

            self::$db->query("DELETE FROM opportunities WHERE id IN ('" . implode("','", $oppList) . "')");

            if (self::$db->tableExists('opportunities_cstm')) {
                self::$db->query("DELETE FROM opportunities_cstm WHERE id_c IN ('" . implode("','", $oppList) . "')");
            }
        }

        if (self::$oppIds) {
            $sql = "UPDATE opportunities SET deleted = 0 WHERE id IN ('" . implode("','", self::$oppIds) . "')";
            self::$db->query($sql);
        }
    }

    public function setUp()
    {
        $this->opportunity_bean = BeanFactory::newBean('Opportunities');
    }

    public function testEquals()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "probability"));
        $sq->from($this->opportunity_bean);
        $sq->where()->equals('probability', 200, $this->opportunity_bean);

        $result = $sq->execute();
        $this->assertEquals(count($result), 1, "Wrong row count, actually received: " . count($result) . " back.");

        foreach ($result AS $opp) {
            $this->assertEquals(200, $opp['probability'], "The amount does not equal to 200 it was: {$opp['probability']}");
        }
    }

    public function testContains()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "probability"));
        $sq->from($this->opportunity_bean);
        $sq->where()->contains('name', 'Query Unit Test 10', $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 10, "Wrong row count, actually received: " . count($result) . " back.");

        foreach ($result AS $opp) {
            $test_string = strstr($opp['name'], '10');
            $this->assertTrue(!empty($test_string), "The name did not contain 10 it was: {$opp['name']}");
        }
    }

    public function testStartsWith()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "amount"));
        $sq->from($this->opportunity_bean);
        $sq->where()->starts('name', 'SugarQuery Unit Test 10', $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 10, "Wrong row count, actually received: " . count($result) . " back.");

        foreach ($result AS $opp) {
            $test_string = stristr($opp['name'], 'SugarQuery Unit Test 10');
            $this->assertTrue(
                !empty($test_string),
                "The name did not start with SugarQuery Unit Test 10 it was: {$opp['name']}"
            );
        }
    }

    public function testLessThan()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "probability"));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->lt('probability', 200, $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 100, "Wrong row count, actually received: " . count($result) . " back.");

        foreach ($result AS $opp) {
            $this->assertLessThan(200, $opp['probability'], "The amount was not less than 2000 it was: {$opp['probability']}");
        }
    }

    public function testLessThanEquals()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "probability"));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->lte('probability', 200, $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 101, "Wrong row count, actually received: " . count($result) . " back.");

        foreach ($result AS $opp) {
            $this->assertLessThanOrEqual(
                200,
                $opp['probability'],
                "The amount was not less than 2000 it was: {$opp['probability']}"
            );
        }
    }

    public function testGreaterThan()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "probability"));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->gt('probability', 200, $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 100, "Wrong row count, actually received: " . count($result) . " back.");

        foreach ($result AS $opp) {
            $this->assertGreaterThan(200, $opp['probability'], "The amount was not less than 2000 it was: {$opp['probability']}");
        }
    }

    public function testGreaterThanEquals()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "probability"));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->gte('probability', 200, $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 101, "Wrong row count, actually received: " . count($result) . " back.");

        foreach ($result AS $opp) {
            $this->assertGreaterThanOrEqual(200, $opp['probability'], "Wrong amount value detected.");
        }
    }

    public function testDateRange()
    {
        $sq = new SugarQuery();

        $sq->select(array('name', 'date_modified'));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->dateRange('date_entered', 'last_7_days', $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertGreaterThanOrEqual(
            1,
            count($result),
            'Wrong row count, actually received: ' . count($result) . ' back.'
        );

        foreach ($result AS $opp) {
            $this->assertGreaterThanOrEqual(
                gmdate("Y-m-d H:i:s", gmmktime(0, 0, 0, gmdate('m'), gmdate('d') - 7, gmdate('Y'))),
                $opp['date_modified'],
                'Wrong date detected.'
            );
            $this->assertLessThanOrEqual(
                gmdate("Y-m-d H:i:s", gmmktime(23, 59, 59, gmdate('m'), gmdate('d'), gmdate('Y'))),
                $opp['date_modified'],
                'Wrong date detected.'
            );
        }
    }

    public function testDateBetween()
    {
        $sq = new SugarQuery();

        $sq->select(array('name', 'date_modified'));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $params = array(gmdate('Y-m-d', gmmktime(0, 0, 0, gmdate('m'), gmdate('d') - 1, gmdate('Y'))), gmdate('Y-m-d'));
        $sq->where()->dateBetween('date_entered', $params, $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertGreaterThanOrEqual(
            1,
            count($result),
            'Wrong row count, actually received: ' . count($result) . ' back.'
        );

        foreach ($result AS $opp) {
            $this->assertGreaterThanOrEqual(
                gmdate("Y-m-d H:i:s", gmmktime(0, 0, 0, gmdate('m'), gmdate('d') - 1, gmdate('Y'))),
                $opp['date_modified'],
                'Wrong date detected.'
            );
            $this->assertLessThanOrEqual(
                gmdate("Y-m-d H:i:s", gmmktime(23, 59, 59, gmdate('m'), gmdate('d'), gmdate('Y'))),
                $opp['date_modified'],
                'Wrong date detected.'
            );
        }
    }

    public function testIn()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "probability"));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->in('probability', array(100, 101, 102, 103, 104, 105), $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 6, "Wrong row count, actually received: " . count($result) . " back.");


        //With a null value
        $sq = new SugarQuery();

        $sq->select(array("name", "probability"));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->in('probability', array('', 100, 101, 102, 103, 104, 105), $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 6, "Wrong row count, actually received: " . count($result) . " back.");


        //With only a null value
        $sq = new SugarQuery();

        $sq->select(array("name", "probability"));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->in('probability', array(''), $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 0, "Wrong row count, actually received: " . count($result) . " back.");
    }

    public function testNotIn()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "probability"));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->notIn('probability', array(100, 101, 102, 103, 104, 105));

        $result = $sq->execute();

        $this->assertEquals(195, count($result), "Wrong row count, actually received: " . count($result) . " back.");


        //With a null value
        $sq = new SugarQuery();

        $sq->select(array("name", "probability"));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->notIn('probability', array('', 100, 101, 102, 103, 104, 105));

        $result = $sq->execute();

        $this->assertEquals(195, count($result), "Wrong row count, actually received: " . count($result) . " back.");


        //With only a null value
        $sq = new SugarQuery();

        $sq->select(array("name", "probability"));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->notIn('probability', array(''));

        $result = $sq->execute();

        $this->assertEquals(201, count($result), "Wrong row count, actually received: " . count($result) . " back.");
    }

    public function testBetween()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "probability"));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->between('probability', 110, 120, $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 11, "Wrong row count, actually received: " . count($result) . " back.");
    }

    public function testNotNull()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "probability"));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->notNull('probability', $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 201, "Wrong row count, actually received: " . count($result) . " back.");

    }

    public function testNull()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "probability"));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->isNull('probability', $this->opportunity_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 0, "Wrong row count, actually received: " . count($result) . " back.");

    }

    public function testRaw()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "amount"));
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->where()->addRaw("name = 'SugarQuery Unit Test 131'");

        $result = $sq->execute();

        $this->assertEquals(count($result), 1, "Wrong row count, actually received: " . count($result) . " back.");

        $result = reset($result);

        $this->assertEquals(
            $result['name'],
            "SugarQuery Unit Test 131",
            "Wrong record returned, received: " . $result['name']
        );

    }

    public function testOrderByLimit()
    {
        $sq = new SugarQuery();
        $sq->select("name", "probability");
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->orderBy("probability", "ASC");
        $sq->limit(2);

        $result = $sq->execute();

        $this->assertEquals(count($result), 2, "Wrong row count, actually received: " . count($result) . " back.");

        $low = $result[0]['probability'];
        $high = $result[1]['probability'];

        $this->assertGreaterThan($low, $high, "{$high} is not greater than {$low}");

        $sq = new SugarQuery();
        $sq->select("name", "probability");
        $sq->from(BeanFactory::newBean('Opportunities'));
        $sq->orderBy("probability", "ASC");
        $sq->limit(2);
        $sq->offset(1);

        $result = $sq->execute();

        $this->assertEquals(count($result), 2, "Wrong row count, actually received: " . count($result) . " back.");

        $low = $result[0]['probability'];
        $high = $result[1]['probability'];

        $this->assertGreaterThan($low, $high, "{$high} is not greater than {$low}");


    }

}
