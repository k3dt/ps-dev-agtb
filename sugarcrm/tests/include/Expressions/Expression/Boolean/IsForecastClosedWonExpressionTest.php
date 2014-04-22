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
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 */

class IsForecastClosedWonExpressionTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    public static function dataProviderCheckStatus()
    {
        return array(
            array('test stage 1', 'false'),
            array('Closed Won', 'true'),
            array('Closed Lost', 'false'),
        );
    }

    /**
     * @dataProvider dataProviderCheckStatus
     *
     * @param $status
     * @param $expected
     * @throws PHPUnit_Framework_Exception
     * @throws Exception
     */
    public function testIsForecastClosedWonEvaluate($status, $expected)
    {
        $forecast = $this->getMockBuilder('Forecast')
            ->setMethods(array('getSettings'))
            ->getMock();

        $settings_return = array(
            'is_setup' => 1,
            'sales_stage_won' => array('Closed Won'),
            'sales_stage_lost' => array('Closed Lost'),
        );

        $forecast->staticExpects($this->any())
            ->method('getSettings')
            ->will($this->returnValue($settings_return));

        /* @var $rli RevenueLineItem */
        $rli = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(array('save'))
            ->getMock();

        $rli->sales_stage = $status;

        $expr = 'isForecastClosedWon($sales_stage)';
        $result = Parser::evaluate($expr, $rli)->evaluate();

        $this->assertSame($expected, $result);
    }
}
