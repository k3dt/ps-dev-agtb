<?php
//FILE SUGARCRM flav=pro ONLY
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once 'tests/SugarTestDatabaseMock.php';
require_once 'modules/RevenueLineItems/jobs/RevenueLineItemsCurrencyRateUpdate.php';

class RevenueLineItemsCurrencyRateUpdateTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $db;
    private $mock;

    public function setUp()
    {
        $this->db = new SugarTestDatabaseMock();
        $this->db->setUp();
        $this->setupMockClass();
        parent::setUp();
    }

    public function tearDown()
    {
        $this->tearDownMockClass();
        $this->db->tearDown();
        parent::tearDown();
    }

    /**
     * setup the mock class and override getClosedStages to return a static array for the test
     */
    public function setupMockClass()
    {
        $this->mock = $this->getMock('RevenueLineItemsCurrencyRateUpdate', array('getClosedStages'));
        // we want to use our mock database for these tests, so replace it
        SugarTestReflection::setProtectedValue($this->mock, 'db', $this->db);
    }

    /**
     * tear down mock class
     */
    public function tearDownMockClass()
    {
        unset($this->mock);
    }

    /**
     * @group opportunities
     */
    public function testDoCustomUpdateRate()
    {
        $this->mock->expects($this->once())
            ->method('getClosedStages')
            ->will($this->returnValue(array('Closed Won', 'Closed Lost')));

        // setup the query strings we are expecting and what they should return
        $this->db->queries['get_rate'] = array(
            'match' => "/SELECT conversion_rate FROM currencies WHERE id = 'abc'/",
            'rows' => array(array('1.234')),
        );
        $this->db->queries['rate_update'] = array(
            'match' => "/UPDATE mytable SET mycolumn = '1\.234'/",
            'rows' => array(array(1)),
        );

        // run our tests with mockup data
        $result = $this->mock->doCustomUpdateRate('mytable', 'mycolumn', 'abc');
        // make sure we get the expected result and the expected run counts
        $this->assertEquals(true, $result);
        $this->assertEquals(1, $this->db->queries['get_rate']['runCount']);
        $this->assertEquals(1, $this->db->queries['rate_update']['runCount']);
    }

    /**
     * @group opportunities
     */
    public function testDoCustomUpdateUsDollarRate()
    {
        $this->mock->expects($this->once())
            ->method('getClosedStages')
            ->will($this->returnValue(array('Closed Won', 'Closed Lost')));

        // setup the query strings we are expecting and what they should return
        $this->db->queries['rate_update'] = array(
            'match' => "/UPDATE mytable SET amount_usdollar = 1\.234 \/ base_rate/",
            'rows' => array(array(1)),
        );

        // run our tests with mockup data
        $result = $this->mock->doCustomUpdateUsDollarRate('mytable', 'amount_usdollar', '1.234', 'abc');
        // make sure we get the expected result and the expected run counts
        $this->assertEquals(true, $result);
        $this->assertEquals(1, $this->db->queries['rate_update']['runCount']);
    }

    /**
     * @group opportunities
     */
    public function testDoPostUpdateAction()
    {
        //BEGIN SUGARCRM flav=ent ONLY
        $this->mock->expects($this->once())
            ->method('getClosedStages')
            ->will($this->returnValue(array('Closed Won', 'Closed Lost')));
        //END SUGARCRM flav=ent ONLY

        // setup the query strings we are expecting and what they should return
        $this->db->queries['post_select'] = array(
            'match' => "/SELECT opportunity_id/",
            'rows' => array(
                array('likely'=>'1000', 'best'=>'1000', 'worst'=>'1000', 'opp_id'=>'abc123'),
                array('likely'=>'2000', 'best'=>'2000', 'worst'=>'2000', 'opp_id'=>'abc123'),
            )
        );
        $this->db->queries['post_update'] = array(
            'match' => "/UPDATE opportunities/",
        );

        // run our tests with mockup data
        $result = $this->mock->doPostUpdateAction();
        // make sure we get the expected result and the expected run counts
        $this->assertEquals(true, $result);
        //BEGIN SUGARCRM flav=ent ONLY
        $this->assertEquals(1, $this->db->queries['post_select']['runCount']);
        $this->assertGreaterThan(0, $this->db->queries['post_update']['runCount']);
        //END SUGARCRM flav=ent ONLY
    }


}
