<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

use PHPUnit\Framework\TestCase;

class SavedReportTest extends TestCase
{
    protected $dbMock = null;

    protected function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');
        global $moduleList, $modListHeader, $app_list_strings;
        require 'config.php';
        require 'include/modules.php';
        require_once 'modules/Reports/config.php';
        $GLOBALS['report_modules'] = getAllowedReportModules($modListHeader);
        $this->dbMock = SugarTestHelper::setUp('mock_db');
    }

    protected function tearDown()
    {
        unset($GLOBALS['report_modules']);
        SugarTestHelper::tearDown('mock_db');
        SugarTestHelper::tearDown();
    }

    /**
     * Test of SavedReport's getLastRunDate
     */
    public function testGetLastRunDate()
    {
        $timedate = TimeDate::getInstance();
        $now = db_convert("'" . $timedate->nowDb() . "'", 'datetime');

        $report = new SavedReport();

        $mock = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->getMock();
        $mock->method('beansAreLoaded')->willReturn(true);
        $relBean = new SugarBean();
        $relBean->date_modified = $now;
        $mock->method('getBeans')->willReturn(array($relBean));
        $report->last_run_date_link = $mock;
        //Set values to other relate fields to ensure nothing tries to populate them
        $report->report_cache_id = "-1";

        $report->fill_in_relationship_fields();

        $this->assertEquals($now, $report->last_run_date, 'incorrect last_run_date');
    }

    /**
     * Make sure that the array returned is a subset of `GLOBALS['report_modules']`
     * and contain values from `$app_list_strings['moduleList']`
     */
    public function test_getModulesDropdown()
    {
        global $app_list_strings;
        $allowed_modules = getModulesDropdown();
        foreach ($allowed_modules as $key => $val) {
            $this->assertArrayHasKey($key, $GLOBALS['report_modules']);
            $this->assertEquals($val, $app_list_strings['moduleList'][$key]);
        }
    }

    /**
     * Checks if last run date is on the bean
     */
    public function testLastRunDate()
    {
        $bean = BeanFactory::newBean('Reports');
        $this->assertArrayHasKey('last_run_date', $bean->field_defs);
    }

    /**
     * to test and ensure the report object is an instance of basic
     */
    public function testReportBeanType()
    {
        $report = new SavedReport();
        $this->assertInstanceOf('Basic', $report, 'report should be a Basic instance');
    }

    /**
     * Data provider for testReportBeanVardefs()
     * @return array reset, type, name
     */
    public function reportBeanVardefsProvider()
    {
        return array(
            // basic
            array('fields', 'description'),
            array('indices' , 'id'),
            array('relationships' , 'reports_modified_user'),

            // assignable
            array('fields', 'assigned_user_id'),
            array('indices' , 'assigned_user_id'),
            array('relationships' , 'reports_assigned_user'),

            // reports
            array('fields', 'module'),
            array('indices' , 'idx_savedreport_module'),
            array('relationships' , 'reports_last_run_date'),

            // team_security
            array('fields', 'team_id'),
            array('indices' , 'team_set_saved_reports'),
            array('relationships' , 'reports_team_count_relationship'),
        );
    }

    /**
     * to test that the definitions from various templates exist in the report dictionary
     * @dataProvider reportBeanVardefsProvider
     */
    public function testReportBeanVardefs($type, $name)
    {
        $def = $this->getReportVardef();
        $this->assertArrayHasKey($name, $def[$type], 'Missing ' . $type . ':' . $name);
    }

    /**
     * To get vardefs of Reports
     * @return array
     */
    protected function getReportVardef()
    {
        static $def = null;
        if (empty($def)) {
            global $dictionary;
            unset($dictionary['SavedReport']);
            require 'modules/Reports/vardefs.php';
            $def = $dictionary['SavedReport'];
        }
        return $def;
    }

    //BEGIN SUGARCRM flav=ent ONLY
    /**
     * Tests that out of the box reports, by id, exist in the system
     */
    public function testStockReportsExistById()
    {
        $stockGuids = [
            'c2908254-7606-11e9-a121-f218983a1c3e' => 'New Cases by Business Center by Week',
            'c2908fc4-7606-11e9-a83a-f218983a1c3e' => 'Recently Created Cases',
            'c290929e-7606-11e9-a555-f218983a1c3e' => 'New Cases by Customer Tier by Week',
            'c290953c-7606-11e9-b083-f218983a1c3e' => 'Open Cases by Customer Tier and Priority',
            'c29097d0-7606-11e9-ac35-f218983a1c3e' => 'Total Cases Resolved this Month by Business Center',
            'c2909a50-7606-11e9-914a-f218983a1c3e' => 'Total Cases Resolved this Month by Agent',
            'c2909cd0-7606-11e9-9955-f218983a1c3e' => 'List of Recently Resolved Cases',
            'c2909f50-7606-11e9-b00e-f218983a1c3e' => 'My Cases Resolved this Month by Week',
            'c290a1da-7606-11e9-80e5-f218983a1c3e' => 'My Cases Due Today and Overdue',
            'c290a45a-7606-11e9-9663-f218983a1c3e' => 'All Cases Due Today and Overdue',
            'c290a6da-7606-11e9-a76d-f218983a1c3e' => 'My Open Cases by Followup Date',
            'c290a950-7606-11e9-a526-f218983a1c3e' => 'All Open Cases by Followup Date',
            'c290abda-7606-11e9-9f3e-f218983a1c3e' => 'My Open Cases by Status',
            'c290ae50-7606-11e9-9cb2-f218983a1c3e' => 'My Cases in the Last Week by Status',
            'c290b0da-7606-11e9-81f9-f218983a1c3e' => 'Status of Open Tasks Assigned by Me',
        ];

        $sql = sprintf(
            'SELECT id, name FROM saved_reports WHERE id in (%s)',
            "'" . implode("','", array_keys($stockGuids)) . "'"
        );

        $conn = DBManagerFactory::getConnection();
        $data = $conn->executeQuery($sql)->fetchAll();

        $this->assertCount(15, $data);

        foreach ($data as $row) {
            $this->assertArrayHasKey($row['id'], $stockGuids);
            $this->assertSame($row['name'], $stockGuids[$row['id']]);
        }
    }
    //END SUGARCRM flav=ent ONLY
}
