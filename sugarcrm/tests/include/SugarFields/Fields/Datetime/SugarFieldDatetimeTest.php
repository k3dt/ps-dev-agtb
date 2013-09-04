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
require_once('include/SugarFields/SugarFieldHandler.php');

class SugarFieldDatetimeTest extends Sugar_PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @group export
     */
    public function testExportSanitize()
    {
        $timedate = TimeDate::getInstance();
        $db = DBManagerFactory::getInstance();

        $now = $timedate->getNow();
        $isoDate = $timedate->asIso($now);
        $dbDatetime = $timedate->asDb($now);

        $expectedTime = $timedate->to_display_date_time($db->fromConvert($dbDatetime, 'datetime'));
        $expectedTime = preg_replace('/([pm|PM|am|AM]+)/', ' \1', $expectedTime);

        $obj = BeanFactory::getBean('Opportunities');
        $obj->date_modified = $isoDate;

        $vardef = $obj->field_defs['date_modified'];

        $field = SugarFieldHandler::getSugarField('datetime');
        $value = $field->exportSanitize($obj->date_modified, $vardef, $obj);
        $this->assertEquals($expectedTime, $value);

        $obj->date_modified = $dbDatetime;
        $value = $field->exportSanitize($obj->date_modified, $vardef, $obj);
        $this->assertEquals($expectedTime, $value);
    }

    public function unformatDataProvider()
    {
        return array(
            array('Europe/Helsinki', '2013-08-05T08:15:30+02:00', '2013-08-05 06:15:30'),
            array('America/Boise', '2013-08-05T08:15:30-07:00', '2013-08-05 15:15:30'),
            array('America/NewYork','2013-08-05T08:15:30','2013-08-05 05:15:30'),
            array('Europe/Minsk','2013-08-05T08:15:30+03:00','2013-08-05 05:15:30'),
            array('Antarctica/Vostok','2013-08-05T08:15:30','2013-08-05 05:15:30'),
        );
    }

    /**
     * @dataProvider unformatDataProvider
     **/
    public function testApiUnformat($timeZone, $isoDate, $gmtResult)
    {
        $GLOBALS['current_user']->setPreference('timezone', $timeZone);
        $GLOBALS['current_user']->savePreferencesToDB();
        $GLOBALS['current_user']->reloadPreferences();

        $field = SugarFieldHandler::getSugarField('datetime');
        $this->assertEquals($gmtResult, $field->apiUnformat($isoDate));
    }

    public function fixForFilterDataProvider()
    {
        return array(
            array('2013-08-29', '$equals', array('2013-08-29T00:00:00', '2013-08-29T23:59:59')),
            array('2013-08-29', '$lt', '2013-08-29T23:59:59'),
            array('2013-08-29', '$gt', '2013-08-29T00:00:00'),
            array(array('2013-08-19', '2013-08-29'), '$between', array('2013-08-19T00:00:00', '2013-08-29T23:59:59')),
            array('2013-08-29', '$daterange', '2013-08-29'),
        );
    }

    /**
     * @dataProvider fixForFilterDataProvider
     */
    public function testFixForFilter($date, $op, $fixedDate)
    {
        $field = SugarFieldHandler::getSugarField('datetime');
        $field->fixForFilter($date, 'date_entered', BeanFactory::getBean('Accounts'), new SugarQuery, new SugarQuery_Builder_AndWhere, $op);
        $this->assertEquals($fixedDate, $date);
    }
}
