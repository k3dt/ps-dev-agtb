<?php
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

require_once('modules/Opportunities/OpportunityHooks.php');

class OpportunityHooksTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function dataProviderSetOpportunitySalesStatus()
    {
        // utility method to to return an array
        $count_to_array = function ($count) {
            return array_pad(array(), $count, '-');
        };

        // # of won, # of lost, #total, #status
        return array(
            // all closed_won
            array($count_to_array(2), $count_to_array(0), $count_to_array(2), Opportunity::STATUS_CLOSED_WON),
            // closed won and closed lost
            array($count_to_array(2), $count_to_array(2), $count_to_array(4), Opportunity::STATUS_CLOSED_WON),
            // all closed log
            array($count_to_array(0), $count_to_array(2), $count_to_array(2), Opportunity::STAGE_CLOSED_LOST),
            // only closed lost but higher total
            array($count_to_array(0), $count_to_array(2), $count_to_array(4), Opportunity::STATUS_IN_PROGRESS),
            // only cosed won but higher total
            array($count_to_array(2), $count_to_array(0), $count_to_array(4), Opportunity::STATUS_IN_PROGRESS),
            // no closed won or lost but still a total
            array($count_to_array(0), $count_to_array(0), $count_to_array(4), Opportunity::STATUS_IN_PROGRESS),
            // no closed won, closed lost and total
            array($count_to_array(0), $count_to_array(0), $count_to_array(0), Opportunity::STATUS_IN_PROGRESS),
        );
    }

    /**
     * @group opportunities
     */
    public function testSetOpportunitySalesStatusOnNewOpp()
    {
        $oppMock = $this->getMock('Opportunity', array('get_linked_beans', 'save', 'retrieve'));

        /* @var $hookMock OpportunityHooks */
        $hookMock = new MockOpportunityHooks();

        $hookMock::setSalesStatus($oppMock, 'before_save', array());

        // assert the status is what it should be
        $this->assertEquals($oppMock->sales_status, Opportunity::STATUS_NEW);
    }

    /**
     * @dataProvider dataProviderSetOpportunitySalesStatus
     * @group opportunities
     * @group revenuelineitems
     */
    public function testSetOpportunitySalesStatusWithAccess($won_count, $lost_count, $total_count, $status)
    {
        $oppMock = $this->getMock('Opportunity', array('get_linked_beans', 'save', 'retrieve', 'ACLFieldAccess'));
        $oppMock->id = 'test';
        $oppMock->fetched_row['id'] = 'test';

        /* @var $hookMock OpportunityHooks */
        $hookMock = new MockOpportunityHooks();

        $closed_won = array('won');
        $closed_lost = array('lost');

        $hr = new ReflectionClass($hookMock);
        $hr->setStaticPropertyValue(
            'settings',
            array(
                'is_setup' => 1,
                'sales_stage_won' => $closed_won,
                'sales_stage_lost' => $closed_lost
            )
        );

        // generate a map for the get_linked_beans call, the first 7 params are for the method call
        // the final param, it what gets returned  this is used below
        $map = array(
            array(
                'revenuelineitems',
                'RevenueLineItems',
                array(),
                0,
                -1,
                0,
                "sales_stage in ('" . join("', '", $closed_won) . "')",
                $won_count
            ),
            array(
                'revenuelineitems',
                'RevenueLineItems',
                array(),
                0,
                -1,
                0,
                "sales_stage in ('" . join("', '", $closed_lost) . "')",
                $lost_count
            ),
            array(
                'revenuelineitems',
                'RevenueLineItems',
                array(),
                0,
                -1,
                0,
                '',
                $total_count
            )
        );

        // we want to run get_linked_bean 3 times. each time will iterate though the $map and return the lats param
        // this is the magic of ->will($this->returnValueMap($map));
        $oppMock->expects($this->exactly(3))
            ->method('get_linked_beans')
            ->will($this->returnValueMap($map));

        $oppMock->expects($this->any())
            ->method('ACLFieldAccess')
            ->will($this->returnValue(true));

        $hookMock::setSalesStatus($oppMock, 'before_save', array());

        // assert the status is what it should be
        $this->assertEquals($oppMock->sales_status, $status);
    }

    public function testSetOpportunitySalesStatusWithoutAccess()
    {
        $oppMock = $this->getMock('Opportunity', array('get_linked_beans', 'save', 'retrieve', 'ACLFieldAccess'));

        /* @var $hookMock OpportunityHooks */
        $hookMock = new MockOpportunityHooks();

        $closed_won = array('won');
        $closed_lost = array('lost');

        $hr = new ReflectionClass($hookMock);
        $hr->setStaticPropertyValue(
            'settings',
            array(
                'is_setup' => 1,
                'sales_stage_won' => $closed_won,
                'sales_stage_lost' => $closed_lost
            )
        );

        $oppMock->expects($this->any())
            ->method('ACLFieldAccess')
            ->will($this->returnValue(false));

        $oppMock->sales_status = 'testing1';

        $hookMock::setSalesStatus($oppMock, 'before_save', array());

        // assert the status is what it should be
        $this->assertEquals('testing1', $oppMock->sales_status);
    }

    public function createHiddenRevenueLineItem()
    {
        $oppMock = $this->getMock('Opportunity', array('get_linked_beans', 'save', 'retrieve', 'ACLFieldAccess'));
        $rliMock = $this->getMock('RevenueLineItem', array('save', 'retrieve_by_string_fields'));

        // we want to test that this gets called once
        $rliMock->expects($this->once())
            ->method('save');

        BeanFactory::setBeanClass('RevenueLineItems', get_class($rliMock));

        $oppMock->name = 'Test';
        $oppMock->id = 'test_id';

        /* @var $hookMock OpportunityHooks */
        $hookMock = new MockOpportunityHooks();

        $hr = new ReflectionClass($hookMock);
        $hr->setStaticPropertyValue(
            'settings',
            array(
                'is_setup' => 1,
                'forecast_by' => 'Opportunities'
            )
        );

        $return = $hookMock->processHiddenRevenueLineItem($oppMock, 'after_save', array());

        $this->assertTrue($return);

        // unset it here
        BeanFactory::setBeanClass('RevenueLineItems');
    }
}

class MockOpportunityHooks extends OpportunityHooks
{
    public static function isForecastSetup()
    {
        return true;
    }
}
