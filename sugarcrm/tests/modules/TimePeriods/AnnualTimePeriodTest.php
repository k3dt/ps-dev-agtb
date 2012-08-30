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

        //SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
    }

    /**
     *
     */
    function testGetNextTimePeriod()
    {
        global $app_strings;
        $tp = SugarTestTimePeriodUtilities::createAnnualTimePeriod();

        $timedate = TimeDate::getInstance();
        //get current timeperiod
        $db = DBManagerFactory::getInstance();
        $queryDate = $timedate->getNow();
        $date = $db->convert($db->quoted($queryDate->asDbDate()), 'date');
        $timeperiod = $db->getOne("SELECT id FROM timeperiods WHERE start_date < {$date} AND end_date > {$date} and is_fiscal_year = 0", false, string_format($app_strings['ERR_TIMEPERIOD_UNDEFINED_FOR_DATE'], array($queryDate->asDbDate())));

        //error_log(print_r($timeperiod,1));
        $baseTimePeriod = BeanFactory::getBean('AnnualTimePeriods', $timeperiod);
        //error_log(print_r($baseTimePeriod,1));
        $nextTimePeriod = $baseTimePeriod->createNextTimePeriod();
        $nextTimePeriod->name = "SugarTestNextAnnualTimePeriod";
        $nextTimePeriod->save();
        SugarTestTimePeriodUtilities::addTimePeriod($nextTimePeriod);
        $nextTimePeriod = BeanFactory::getBean('AnnualTimePeriods', $nextTimePeriod->id);

        //next timeperiod (1 year from today)
        $nextStartDate = $timedate->fromUserDate($baseTimePeriod->start_date);
        $nextStartDate = $nextStartDate->modify("+1 year");
        $nextEndDate = $timedate->fromUserDate($baseTimePeriod->end_date);
        $nextEndDate = $nextEndDate->modify("+1 year");

        $this->assertEquals($timedate->fromUserDate($nextTimePeriod->start_date), $nextStartDate);

        $this->assertEquals($timedate->fromUserDate($nextTimePeriod->end_date), $nextEndDate);

        $dayLength = 364;
        if(($nextEndDate->year % 4) == 0) {
            $dayLength = 365;
        }

        $this->assertEquals($dayLength, $nextTimePeriod->getLengthInDays());
    }


}