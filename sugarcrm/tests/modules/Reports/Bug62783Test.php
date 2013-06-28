<?php
//FILE SUGARCRM flav=pro ONLY
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

require_once('modules/Forecasts/ForecastsDefaults.php');
require_once('include/generic/LayoutManager.php');
require_once('modules/Reports/Report.php');

/**
 * Test Fiscal Filters and Fiscal Group By for report date/time fields
 *
 * @author avucinic@sugarcrm.com
 */
class Bug62783Test extends Sugar_PHPUnit_Framework_TestCase
{

    private static $reportDef = array(
        'display_columns' => array(),
        'module' => 'Opportunities',
        'assigned_user_id' => '1',
        'report_type' => 'summary',
        'full_table_list' => array(
            'self' => array(
                'value' => 'Opportunities',
                'module' => 'Opportunities',
                'label' => 'Opportunities',
            ),
        ),
        'filters_def' => array(
            'Filter_1' => array(
                'operator' => 'AND',
                array(
                    'name' => 'id',
                    'table_key' => 'self',
                    'qualifier_name' => 'is',
                ),
            ),
        ),
    );

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        // Setup Forecast defaults
        SugarTestForecastUtilities::setUpForecastConfig();
        ForecastsDefaults::setupForecastSettings();
    }

    public function tearDown()
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestRevenueLineItemUtilities::removeAllCreatedRevenueLineItems();

        SugarTestHelper::tearDown();
    }

    /**
     * Test if fiscal query filters for DateTime type fields are working properly
     *
     * @param $qualifier - qualifier (year/quarter)
     * @param $startDate - Fiscal start date
     * @param $date - date for which to to find the fiscal quarter/year
     * @param $modifyStart - Modification to start date
     * @param $modifyEnd - Modification to end date
     * @param $expectedStart - Expected start date in query
     * @param $expectedEnd - Expected end date in query
     * @param $timezone - User timezone
     *
     * @dataProvider filterDataProvider
     */
    public function testDateTimeFiscalQueryFilter(
        $qualifier,
        $type,
        $startDate,
        $date,
        $modifyStart,
        $modifyEnd,
        $expectedStart,
        $expectedEnd,
        $timezone
    ) {
        // Setup Fiscal Start Date
        $admin = BeanFactory::getBean('Administration');
        $admin->saveSetting('Forecasts', 'timeperiod_start_date', json_encode($startDate), 'base');

        $GLOBALS['current_user']->setPreference('timezone', $timezone);

        $layoutManager = new LayoutManager();
        $layoutManager->setAttribute('reporter', new Report());
        $SWFDT = new SugarWidgetFielddatetime62783Test($layoutManager);
        $layoutDef = array(
            'qualifier_name' => $qualifier,
            'type' => $type
        );

        $result = $SWFDT->getFiscalYearFilter($layoutDef, $modifyStart, $modifyEnd, $date);

        $this->assertContains($expectedStart, $result, 'Greater than part of query generated incorrectly.');
        $this->assertContains($expectedEnd, $result, 'Lower than part of query generated incorrectly.');
    }

    /**
     * Test if groupBy query for fiscal year/quarter
     * on Date type fields is working properly
     *
     * @param $startDate - Fiscal start date
     * @param $timezone - User timezone
     * @param $expected - Expected result
     * @param $reportDef - Report def
     *
     * @dataProvider groupDateDataProvider
     */
    public function testDateFiscalQueryGroupBy($startDate, $timezone, $expected, $reportDef)
    {
        // Setup Fiscal Start Date
        $admin = BeanFactory::getBean('Administration');
        $admin->saveSetting('Forecasts', 'timeperiod_start_date', json_encode($startDate), 'base');

        $GLOBALS['current_user']->setPreference('timezone', $timezone);

        $id = create_guid();
        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $rli->date_closed = $startDate;
        $rli->opportunity_id = $id;
        $rli->save();
        $opportunity = SugarTestOpportunityUtilities::createOpportunity($id);
        $opportunity->date_closed = $startDate;
        $opportunity->save();

        $reportDef['filters_def']['Filter_1'][0]['input_name0'] = $opportunity->id;

        $report = new Report(json_encode($reportDef));

        $report->run_summary_query();
        $row = $report->get_summary_next_row();

        $this->assertEquals(1, $row['count'], 'Report count should be 1');
        $this->assertEquals($expected, $row['cells'][0], 'Wrong grouping result');
    }

    /**
     * Test if groupBy query for fiscal year/quarter
     * on DateTime type fields is working properly
     *
     * @param $startDate - Fiscal start date
     * @param $timezone - User timezone
     * @param $expected - Expected result
     * @param $reportDef - Report def
     *
     * @dataProvider groupDateTimeDataProvider
     */
    public function testDateTimeFiscalQueryGroupBy($startDate, $timezone, $expected, $reportDef)
    {
        // Setup Fiscal Start Date
        $admin = BeanFactory::getBean('Administration');
        $admin->saveSetting('Forecasts', 'timeperiod_start_date', json_encode($startDate), 'base');

        $GLOBALS['current_user']->setPreference('timezone', $timezone);

        $id = create_guid();
        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $rli->date_closed = $startDate . ' 00:00:00';
        $rli->opportunity_id = $id;
        $rli->save();
        $opportunity = SugarTestOpportunityUtilities::createOpportunity($id);
        $opportunity->date_modified = $startDate . ' 00:00:00';
        $opportunity->update_date_modified = false;
        $opportunity->save();

        $reportDef['filters_def']['Filter_1'][0]['input_name0'] = $opportunity->id;

        $report = new Report(json_encode($reportDef));

        $report->run_summary_query();
        $row = $report->get_summary_next_row();

        $this->assertEquals(1, $row['count'], 'Report count should be 1');
        $this->assertEquals($expected, $row['cells'][0], 'Wrong grouping result');
    }

    public static function groupDateTimeDataProvider()
    {
        $reportDefYear = Bug62783Test::$reportDef;
        $reportDefQuarter = Bug62783Test::$reportDef;

        $reportDefYear['group_defs'] = $reportDefYear['summary_columns'] =
            array(
                array(
                    'name' => 'date_modified',
                    'column_function' => 'fiscalYear',
                    'qualifier' => 'fiscalYear',
                    'table_key' => 'self',
                ),
            );

        $reportDefQuarter['group_defs'] = $reportDefQuarter['summary_columns'] =
            array(
                array(
                    'name' => 'date_modified',
                    'column_function' => 'fiscalQuarter',
                    'qualifier' => 'fiscalQuarter',
                    'table_key' => 'self',
                ),
            );

        return array(
            array(
                '2013-05-05',
                'America/Los_Angeles',
                '2012',
                $reportDefYear
            ),
            array(
                '2013-05-05',
                'UTC',
                '2013',
                $reportDefYear
            ),
            array(
                '2013-05-05',
                'Europe/Helsinki',
                '2013',
                $reportDefYear
            ),
            array(
                '2013-12-05',
                'America/Los_Angeles',
                'Q4 2012',
                $reportDefQuarter
            ),
            array(
                '2013-05-05',
                'UTC',
                'Q1 2013',
                $reportDefQuarter
            ),
            array(
                '2013-05-05',
                'Europe/Helsinki',
                'Q1 2013',
                $reportDefQuarter
            ),
        );
    }

    public static function groupDateDataProvider()
    {
        $reportDefYear = Bug62783Test::$reportDef;
        $reportDefQuarter = Bug62783Test::$reportDef;

        $reportDefYear['group_defs'] = $reportDefYear['summary_columns'] =
            array(
                array(
                    'name' => 'date_closed',
                    'column_function' => 'fiscalYear',
                    'qualifier' => 'fiscalYear',
                    'table_key' => 'self',
                ),
            );

        $reportDefQuarter['group_defs'] = $reportDefQuarter['summary_columns'] =
            array(
                array(
                    'name' => 'date_closed',
                    'column_function' => 'fiscalQuarter',
                    'qualifier' => 'fiscalQuarter',
                    'table_key' => 'self',
                ),
            );

        return array(
            array(
                '2013-05-05',
                'America/Los_Angeles',
                '2013',
                $reportDefYear
            ),
            array(
                '2013-05-05',
                'UTC',
                '2013',
                $reportDefYear
            ),
            array(
                '2013-05-05',
                'Europe/Helsinki',
                '2013',
                $reportDefYear
            ),
            array(
                '2013-05-05',
                'America/Los_Angeles',
                'Q1 2013',
                $reportDefQuarter
            ),
            array(
                '2013-01-05',
                'UTC',
                'Q1 2013',
                $reportDefQuarter
            ),
            array(
                '2013-01-05',
                'Europe/Helsinki',
                'Q1 2013',
                $reportDefQuarter
            ),
        );
    }

    public static function filterDataProvider()
    {
        return array(
            array(
                'quarter',
                'datetime',
                '1987-01-01',
                '2013-05-05',
                '',
                '+3 month',
                ">= '2013-04-01 07:00:00'",
                "< '2013-07-01 07:00:00'",
                'America/Los_Angeles'
            ),
            array(
                'year',
                'datetime',
                '1987-01-01',
                '2013-05-05',
                '+1 year',
                '+2 year',
                ">= '2013-12-31 22:00:00'",
                "< '2014-12-31 22:00:00'",
                'Europe/Helsinki'
            ),
            array(
                'quarter',
                'date',
                '1987-01-01',
                '2013-05-05',
                '',
                '+3 month',
                ">= '2013-04-01 00:00:00'",
                "< '2013-07-01 00:00:00'",
                'America/Los_Angeles'
            ),
            array(
                'year',
                'date',
                '1987-01-01',
                '2013-05-05',
                '+1 year',
                '+2 year',
                ">= '2014-01-01 00:00:00'",
                "< '2015-01-01 00:00:00'",
                'Europe/Helsinki'
            ),
            array(
                'quarter',
                'datetime',
                '1987-01-01',
                '2013-05-05',
                '-3 month',
                '',
                ">= '2013-01-01 00:00:00'",
                "< '2013-04-01 00:00:00'",
                'UTC'
            ),
            array(
                'year',
                'datetime',
                '1987-01-01',
                '2013-05-05',
                '+1 year',
                '+2 year',
                ">= '2014-01-01 00:00:00'",
                "< '2015-01-01 00:00:00'",
                'UTC'
            ),
            array(
                'quarter',
                'datetime',
                '2018-05-01',
                '2013-05-05',
                '',
                '+3 month',
                ">= '2013-05-01 07:00:00'",
                "< '2013-08-01 07:00:00'",
                'America/Los_Angeles'
            ),
            array('year',
                'datetime',
                '2018-05-01',
                '2013-05-05',
                '+1 year',
                '+2 year',
                ">= '2014-04-30 21:00:00'",
                "< '2015-04-30 21:00:00'",
                'Europe/Helsinki'
            ),
            array(
                'quarter',
                'date',
                '2018-05-01',
                '2013-05-05',
                '',
                '+3 month',
                ">= '2013-05-01 00:00:00'",
                "< '2013-08-01 00:00:00'",
                'America/Los_Angeles'
            ),
            array('year',
                'date',
                '2018-05-01',
                '2013-05-05',
                '+1 year',
                '+2 year',
                ">= '2014-05-01 00:00:00'",
                "< '2015-05-01 00:00:00'",
                'Europe/Helsinki'
            ),
            array(
                'quarter',
                'datetime',
                '2018-05-01',
                '2013-05-05',
                '-3 month',
                '',
                ">= '2013-02-01 00:00:00'",
                "< '2013-05-01 00:00:00'",
                'UTC'
            ),
            array(
                'year',
                'datetime',
                '2018-05-01',
                '2013-05-05',
                '+1 year',
                '+2 year',
                ">= '2014-05-01 00:00:00'",
                "< '2015-05-01 00:00:00'",
                'UTC'
            ),
        );
    }
}

/**
 * Helper class for testing getFiscalYearFilter() method
 */
class SugarWidgetFielddatetime62783Test extends SugarWidgetFielddatetime
{
    public function getFiscalYearFilter($layout_def, $modifyStart, $modifyEnd, $date = '')
    {
        return parent::getFiscalYearFilter($layout_def, $modifyStart, $modifyEnd, $date);
    }
}
