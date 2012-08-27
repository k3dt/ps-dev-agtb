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

require_once('modules/TimePeriods/TimePeriod.php');

class TimePeriodTest extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        SugarTestHelper::setUp('app_strings');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     *
     */
    function testGetLastCurrentNextIds()
    {
        global $app_strings;

        $timeperiods = array();
        $timedate = TimeDate::getInstance();
        //get current timeperiod
        $db = DBManagerFactory::getInstance();
        $queryDate = $timedate->getNow();
        $date = $db->convert($db->quoted($queryDate->asDbDate()), 'date');
        $timeperiod = $db->getOne("SELECT id FROM timeperiods WHERE start_date < {$date} AND end_date > {$date} and is_fiscal_year = 0", false, string_format($app_strings['ERR_TIMEPERIOD_UNDEFINED_FOR_DATE'], array($queryDate->asDbDate())));

        if (!empty($timeperiod)) {
            $timeperiods[$timeperiod] = $app_strings['LBL_CURRENT_TIMEPERIOD'];
        }

        //previous timeperiod (3 months ago)
        $queryDate = $queryDate->modify('-3 month');
        $date = $db->convert($db->quoted($queryDate->asDbDate()), 'date');
        $timeperiod = $db->getOne("SELECT id FROM timeperiods WHERE start_date < {$date} AND end_date > {$date} and is_fiscal_year = 0", false, string_format($app_strings['ERR_TIMEPERIOD_UNDEFINED_FOR_DATE'], array($queryDate->asDbDate())));

        if (!empty($timeperiod)) {
            $timeperiods[$timeperiod] = $app_strings['LBL_PREVIOUS_TIMEPERIOD'];
        }

        //next timeperiod (3 months from today)
        $queryDate = $queryDate->modify('+6 month');
        $date = $db->convert($db->quoted($queryDate->asDbDate()), 'date');
        $timeperiod = $db->getOne("SELECT id FROM timeperiods WHERE start_date < {$date} AND end_date > {$date} and is_fiscal_year = 0", false, string_format($app_strings['ERR_TIMEPERIOD_UNDEFINED_FOR_DATE'], array($queryDate->asDbDate())));

        if (!empty($timeperiod)) {
            $timeperiods[$timeperiod] = $app_strings['LBL_NEXT_TIMEPERIOD'];
        }

        if (count($timeperiods) != 3) {
            $this->markTestSkipped('Incomplete default timeperiods data');
        }

        $result = TimePeriod::getLastCurrentNextIds();
        $this->assertSame($timeperiods, $result);

        $result = TimePeriod::getLastCurrentNextIds(TimeDate::getInstance());
        $this->assertSame($timeperiods, $result);
    }


}