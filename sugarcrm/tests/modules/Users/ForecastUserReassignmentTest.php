<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


require_once('modules/Forecasts/ForecastsSeedData.php');
require_once('modules/Forecasts/WorksheetSeedData.php');

/**
 * nutmeg:sfa-219
 * Fix reassignment of records when user set to Inactive
 *
 * @ticket sfa-219
 */
class ForecastUserReassignmentTest extends  Sugar_PHPUnit_Framework_OutputTestCase
{
    private $_users;
    private $_users_ids;
    private $_users_opps;
    private $_users_worksheets_count;
    private $_timeperiod;

    /**
     * create user
     *
     * @param $user String id of the user to create
     * @param $report_user String id of the user the created user should report to (defaults to null)
     */
    private function _createUser($user, $report_user = null)
    {
        $this->_users[$user] = SugarTestUserUtilities::createAnonymousUser($save = false, $is_admin=0);
        $this->_users[$user]->id = create_guid();
        $this->_users[$user]->new_with_id = true;
        $this->_users[$user]->user_name = $user;
        $this->_users[$user]->first_name = $user;
        $this->_users[$user]->reports_to_id = $report_user && isset($this->_users[$report_user]) ?  $this->_users[$report_user]->id : null;
        $this->_users[$user]->save();
        $this->_users_ids[] = $this->_users[$user]->id;
    }

    /**
     * create opportunities for the user
     *
     * @param $user String id of the user to create opportunities for
     * @param $count int value for number of opportunities to create
     */
    private function _createOpportunityForUser($user, $count)
    {
        for ( $i = 0; $i < $count; $i++ )
        {
            $opp = SugarTestOpportunityUtilities::createOpportunity();
            $opp->assigned_user_id = $this->_users[$user]->id;
            $opp->save();
            $this->_users_opps[$user][] = $opp;
        }

    }

    /**
     * return count of opportunities for user
     *
     * @param $user
     * @return int
     */
    private function _getOpportunitiesCountForUser($user)
    {
        $db = DBManagerFactory::getInstance();
        $row = $db->fetchOne("SELECT count(*) as cnt FROM opportunities WHERE assigned_user_id = '".$this->_users[$user]->id."' and deleted = '0'");
        return $row['cnt'];
    }

    /**
     * return count of products for user
     *
     * @param $user
     * @return int
     */
    private function _getProductsCountForUser($user)
    {
        $db = DBManagerFactory::getInstance();
        $row = $db->fetchOne("SELECT count(*) as cnt FROM products WHERE assigned_user_id = '".$this->_users[$user]->id."' and deleted = '0'");
        return $row['cnt'];
    }

    /**
     * return count of worksheets for user
     *
     * @param $user
     * @return int
     */
    private function _getWorksheetsCountForUser($user)
    {
        $db = DBManagerFactory::getInstance();
        $row = $db->fetchOne("SELECT count(*) as cnt FROM worksheet WHERE user_id = '".$this->_users[$user]->id."' and deleted = '0'");
        return $row['cnt'];
    }

    /**
     * return count of forecasts for user
     *
     * @param $user
     * @return int
     */
    private function _getForecastsCountForUser($user)
    {
        $db = DBManagerFactory::getInstance();
        $row = $db->fetchOne("SELECT count(*) as cnt FROM forecasts WHERE user_id = '".$this->_users[$user]->id."' and deleted = '0'");
        return $row['cnt'];
    }

    /**
     * return count of forecast schedules for user
     *
     * @param $user
     * @return int
     */
    private function _getForecastScheduleCountForUser($user)
    {
        $db = DBManagerFactory::getInstance();
        $row = $db->fetchOne("SELECT count(*) as cnt FROM forecast_schedule WHERE user_id = '".$this->_users[$user]->id."' and deleted = '0'");
        return $row['cnt'];
    }

    /**
     * return count of quotas for user
     *
     * @param $user
     * @return int
     */
    private function _getQuotasCountForUser($user)
    {
        $db = DBManagerFactory::getInstance();
        $row = $db->fetchOne("SELECT count(*) as cnt FROM quotas WHERE user_id = '".$this->_users[$user]->id."' and deleted = '0'");
        return $row['cnt'];
    }

    /**
     * return information about user's reportees
     * @param $user
     * @return array
     */
    private function _getReporteesForUser($user)
    {
        $userID = $this->_users[$user]->id;

        require_once('include/SugarForecasting/ReportingUsers.php');
        $object = new SugarForecasting_ReportingUsers( array('user_id' => $this->_users['sarah']->id) );
        $return = $object->process();

        $children = array();
        if ( isset($return['children']) )
        {
            $children = $return['children'];
        }
        else
        {
            foreach ( $return as $reply )
            {
                if ( $reply['metadata'] && $reply['metadata']['id'] == $userID )
                {
                    $children = $reply['children'];
                }
            }
        }
        return $children;
    }

    /**
     * call action reassignUserRecords
     * @param $fromUser
     * @param $toUser
     */
    private function _doReassign($fromUser, $toUser)
    {
        $_SESSION['reassignRecords'] = array();
        $_SESSION['reassignRecords']['assignedModuleListCache'] = array('ForecastWorksheets' => 'ForecastWorksheet');
        $_SESSION['reassignRecords']['assignedModuleListCacheDisp'] = array ('ForecastWorksheets' => 'ForecastWorksheet');

        $_POST = $_GET = array();
        $_POST['module'] = 'Users';
        $_POST['action'] = 'reassignUserRecords';
        $_POST['fromuser'] = $this->_users[$fromUser]->id;
        $_POST['touser'] = $this->_users[$toUser]->id;
        $_POST['modules'] = array('ForecastWorksheet');
        $_POST['steponesubmit'] = 'Next';

        unset($_GET['execute']);

        global $app_list_strings, $beanFiles, $beanList, $current_user, $mod_strings, $app_strings;
        include('modules/Users/reassignUserRecords.php');

        $_GET['execute'] = true;
        include('modules/Users/reassignUserRecords.php');
    }

    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('app_strings');

        //This reporting structure mimics our seed data hierarchy
        $this->_createUser('jim');
        $this->_createUser('sarah', 'jim');
        $this->_createUser('will', 'jim');
        $this->_createUser('sally', 'sarah');
        $this->_createUser('max', 'sarah');
        $this->_createUser('chris', 'will');
        SugarTestHelper::setUp('current_user', array(true, 1));
        $this->_timeperiod = SugarTestTimePeriodUtilities::createTimePeriod();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestWorksheetUtilities::removeAllCreatedWorksheets();

        $db = DBManagerFactory::getInstance();
        $db->query("DELETE FROM forecasts WHERE timeperiod_id = '{$this->_timeperiod->id}'");
        $db->query("DELETE FROM forecast_schedule WHERE timeperiod_id = '{$this->_timeperiod->id}'");
        $db->query("DELETE FROM quotas WHERE timeperiod_id = '{$this->_timeperiod->id}'");

        unset($_SESSION['reassignRecords']);
        $postVars = array('module', 'action', 'fromuser', 'touser', 'modules', 'steponesubmit');
        foreach($postVars as $key)
        {
            unset($_POST[$key]);
        }

        unset($this->_users, $this->_users_ids, $this->_users_opps, $this->_users_worksheets_count, $this->_timeperiod);
        SugarTestHelper::tearDown();
    }

    /**
     * test reassignment rep to rep
     * @group forecast
     * @outputBuffering enabled
     */
    public function testReassignRepToRep()
    {
        //Create 10 opportunities for sally
        $this->_createOpportunityForUser('sally', 10);
        $this->_created_items = ForecastsSeedData::populateSeedData( array($this->_timeperiod->id => $this->_timeperiod) );

        //Create worksheet entries using WorksheetSeedData class
        $worksheets_ids = WorksheetSeedData::populateSeedData();
        SugarTestWorksheetUtilities::setCreatedWorksheet($worksheets_ids);

        //Assert that 10 opportunities and 10 products are created for sally
        $count = $this->_getOpportunitiesCountForUser('sally');
        $this->assertEquals(10, $count);
        $count = $this->_getProductsCountForUser('sally');
        $this->assertEquals(10, $count);

        $expected['worksheets'] = $this->_getWorksheetsCountForUser('sally');
        $expected['opportunities'] = sizeof($this->_users_opps['sally']);

        //Now simulate the reassignment from sally to chris
        $this->_doReassign('sally', 'chris');

        // from sally
        $count = $this->_getOpportunitiesCountForUser('sally');
        $this->assertEquals(0, $count, 'Opportunities are not reassigned.');
        $count = $this->_getProductsCountForUser('sally');
        $this->assertEquals(0, $count, 'Products are not reassigned.');
        $count = $this->_getWorksheetsCountForUser('sally');
        $this->assertEquals(0, $count, 'Worksheets are not reassigned.');
        $count = $this->_getForecastsCountForUser('sally');
        $this->assertEquals(0, $count, 'Forecasts are not deleted.');
        $count = $this->_getForecastScheduleCountForUser('sally');
        $this->assertEquals(0, $count, 'ForecastSchedule are not deleted.');
        $count = $this->_getQuotasCountForUser('sally');
        $this->assertEquals(0, $count, 'Quotas are not deleted.');

        // check that the opportunities, products and worksheet entries have been assigned to chris
        $count = $this->_getOpportunitiesCountForUser('chris');
        $this->assertEquals($expected['opportunities'], $count, 'Opportunities are not reassigned.');
        $count = $this->_getProductsCountForUser('chris');
        $this->assertEquals($expected['opportunities'], $count, 'Products are not reassigned.');
        $count = $this->_getWorksheetsCountForUser('chris');
        $this->assertEquals($expected['worksheets'], $count, 'Worksheets are not reassigned.');
    }

    /**
     * test reassignment manager to manager
     * @group forecasts
     * @outputBuffering enabled
     */
    public function testReassignManagerToManager()
    {
        $this->_createOpportunityForUser('sarah', 10);
        $this->_created_items = ForecastsSeedData::populateSeedData( array($this->_timeperiod->id => $this->_timeperiod) );
        $worksheets_ids = WorksheetSeedData::populateSeedData( array($this->_timeperiod->id => $this->_timeperiod) );
        SugarTestWorksheetUtilities::setCreatedWorksheet($worksheets_ids);

        $count = $this->_getOpportunitiesCountForUser('sarah');
        $this->assertEquals(10, $count);
        $count = $this->_getProductsCountForUser('sarah');
        $this->assertEquals(10, $count);

        // subtract 1 from the total count because of the rollup entry created for sarah's manager (jim)
        $expected['worksheets'] = $this->_getWorksheetsCountForUser('sarah') - 1; 
        $expected['opportunities'] = sizeof($this->_users_opps['sarah']);

        $this->_doReassign('sarah', 'will');

        // from sarah
        $count = $this->_getOpportunitiesCountForUser('sarah');
        $this->assertEquals(0, $count, 'Opportunities are not reassigned.');
        $count = $this->_getProductsCountForUser('sarah');
        $this->assertEquals(0, $count, 'Products are not reassigned.');
        $count = $this->_getWorksheetsCountForUser('sarah');
        $this->assertEquals(0, $count, 'Worksheets are not reassigned.');
        $count = $this->_getForecastsCountForUser('sarah');
        $this->assertEquals(0, $count, 'Forecasts are not deleted.');
        $count = $this->_getForecastScheduleCountForUser('sarah');
        $this->assertEquals(0, $count, 'ForecastSchedule are not deleted.');
        $count = $this->_getQuotasCountForUser('sarah');
        $this->assertEquals(0, $count, 'Quotas are not deleted.');

         // check that the opportunities, products and worksheet entries have been assigned to will
        $count = $this->_getOpportunitiesCountForUser('will');
        $this->assertEquals($expected['opportunities'], $count, 'Opportunities are not reassigned.');
        $count = $this->_getProductsCountForUser('will');
        $this->assertEquals($expected['opportunities'], $count, 'Products are not reassigned.');
        $count = $this->_getWorksheetsCountForUser('will');
        $this->assertEquals($expected['worksheets'], $count, 'Worksheets are not reassigned.');
    }

    /**
     * test reassignment manager to rep
     * @group forecast
     * @outputBuffering enabled
     */
    public function testReassignManagerToRep()
    {
        $this->_createOpportunityForUser('sarah', 10);
        $this->_created_items = ForecastsSeedData::populateSeedData( array($this->_timeperiod->id => $this->_timeperiod) );
        $worksheets_ids = WorksheetSeedData::populateSeedData( array($this->_timeperiod->id => $this->_timeperiod) );
        SugarTestWorksheetUtilities::setCreatedWorksheet($worksheets_ids);

        $count = $this->_getOpportunitiesCountForUser('sarah');
        $this->assertEquals(10, $count);
        $count = $this->_getProductsCountForUser('sarah');
        $this->assertEquals(10, $count);

        // subtract 1 from the total count because of the rollup entry created for sarah's manager (jim)
        $expected['worksheets'] = $this->_getWorksheetsCountForUser('sarah') - 1;
        $expected['opportunities'] = sizeof($this->_users_opps['sarah']);

        $this->_doReassign('sarah', 'sally');

        // from sarah
        $count = $this->_getOpportunitiesCountForUser('sarah');
        $this->assertEquals(0, $count, 'Opportunities are not reassigned.');
        $count = $this->_getProductsCountForUser('sarah');
        $this->assertEquals(0, $count, 'Products are not reassigned.');
        $count = $this->_getWorksheetsCountForUser('sarah');
        $this->assertEquals(0, $count, 'Worksheets are not reassigned.');
        $count = $this->_getForecastsCountForUser('sarah');
        $this->assertEquals(0, $count, 'Forecasts are not deleted.');
        $count = $this->_getForecastScheduleCountForUser('sarah');
        $this->assertEquals(0, $count, 'ForecastSchedule are not deleted.');
        $count = $this->_getQuotasCountForUser('sarah');
        $this->assertEquals(0, $count, 'Quotas are not deleted.');

        // to will
        $count = $this->_getOpportunitiesCountForUser('sally');
        $this->assertEquals($expected['opportunities'], $count, 'Opportunities are not reassigned.');
        $count = $this->_getProductsCountForUser('sally');
        $this->assertEquals($expected['opportunities'], $count, 'Products are not reassigned.');
        $count = $this->_getWorksheetsCountForUser('sally');
        $this->assertEquals($expected['worksheets'], $count, 'Worksheets are not reassigned.');

        $objSally = new User();
        $objSally->retrieve($this->_users['sally']->id);
        $this->assertEquals($this->_users['sarah']->reports_to_id, $objSally->reports_to_id );
    }


    /**
     * Placeholder for now.  Not sure if we need to support Opportunities filtering and pass along filters
    public function testReassignRepToRepUsingFilters()
    {
        //Create 10 opportunities for sally
        $this->_createOpportunityForUser('sally', 10);
        $this->_created_items = ForecastsSeedData::populateSeedData( array($this->_timeperiod->id => $this->_timeperiod) );

        //Create worksheet entries using WorksheetSeedData class
        $worksheets_ids = WorksheetSeedData::populateSeedData();
        SugarTestWorksheetUtilities::setCreatedWorksheet($worksheets_ids);

        $count = 0;
        $newWonCount = 0;
        foreach($this->_users_opps['sally'] as $opportunity)
        {
            $even = ($count++ % 2) == 0;
            if($even) {
                $newWonCount++;
            }
            $opportunity->opportunity_type = $even ? 'New Business' : 'Existing Business';
            $opportunity->sales_stage = $even ? 'Closed Won' : 'Closed Lost';
            $opportunity->save();
        }

        $expected['worksheets'] = $this->_getWorksheetsCountForUser('sally');
        $expected['opportunities'] = sizeof($this->_users_opps['sally']);

        $additionalParams = array(
            'modules' => array ( 'Opportunity' => array ( 'query' => 'select id from opportunities where opportunities.deleted=0 and opportunities.assigned_user_id = \'seed_chris_id\' and (opportunities.sales_stage in (\'Qualification\') ) and (opportunities.opportunity_type in (\'Existing Business\') )', 'update' => 'update opportunities set assigned_user_id = \'seed_sally_id\', date_modified = \'2012-10-17 19:43:24\', modified_user_id = \'1\' , team_id = \'1\', team_set_id = \'1\' where opportunities.deleted=0 and opportunities.assigned_user_id = \'seed_chris_id\' and (opportunities.sales_stage in (\'Qualification\') ) and (opportunities.opportunity_type in (\'Existing Business\') )', ), ), )
        );


        $_SESSION['reassignRecords'] ...
        $_SESSION['reassignRecords'] ...
        //Now simulate the reassignment from sally to chris
        $this->_doReassign('sally', 'chris');
    }
    */

    /**
     * test user's reportees if some user became inactive
     * @group forecast
     */
    public function testInactiveChildren()
    {
        global $current_user;
        $db = DBManagerFactory::getInstance();
        if ( !$db->supports('recursive_query') )
        {
            // @see SugarForecasting_ReportingUsers::getReportees()
            $this->markTestSkipped('DBManager does not support recursive query');
        }

        $this->_createOpportunityForUser('sarah', 10);

        $children = $this->_getReporteesForUser('sarah');
        $this->assertEquals(3, sizeof($children)); // sally, max and opportunities

        $this->_users['sally']->status = 'Inactive';
        $this->_users['sally']->save();
        $children = $this->_getReporteesForUser('sarah');
        $this->assertEquals(2, sizeof($children)); // max and opportunities

        $this->_users['max']->status = 'Inactive';
        $this->_users['max']->save();
        $children = $this->_getReporteesForUser('sarah');
        $this->assertEquals(0, sizeof($children));
    }

    /**
     * test a user's reportees if some user is set to be deleted
     * @group forecast
     */
    public function testDeletedChildren()
    {
        global $current_user;
        $db = DBManagerFactory::getInstance();
        if ( !$db->supports('recursive_query') )
        {
            // @see SugarForecasting_ReportingUsers::getReportees()
            $this->markTestSkipped('DBManager does not support recursive query');
        }

        $this->_createOpportunityForUser('sarah', 10);

        $children = $this->_getReporteesForUser('sarah');
        $this->assertEquals(3, sizeof($children)); // sally, max and opportunities

        $this->_users['sally']->deleted = 1;
        $this->_users['sally']->save();
        $children = $this->_getReporteesForUser('sarah');
        $this->assertEquals(2, sizeof($children)); // max and opportunities

        $this->_users['max']->deleted = 1;
        $this->_users['max']->save();
        $children = $this->_getReporteesForUser('sarah');
        $this->assertEquals(0, sizeof($children));
    }

    /**
     * test worksheets after reassignment rep to rep
     *
     * @group forecasts
     */
    public function testWorksheetRepToRep()
    {
        $this->_createOpportunityForUser('sally', 10);
        $this->_created_items = ForecastsSeedData::populateSeedData( array($this->_timeperiod->id => $this->_timeperiod) );
        $worksheets_ids = WorksheetSeedData::populateSeedData( array($this->_timeperiod->id => $this->_timeperiod) );
        SugarTestWorksheetUtilities::setCreatedWorksheet($worksheets_ids);

        require_once('include/SugarForecasting/Individual.php');

        global $current_user;
        $this->_users['sally']->is_admin = true;
        $this->_users['chris']->is_admin = true;
        $current_user = $this->_users['sally'];
        $api = new SugarForecasting_Individual( array('timeperiod_id' => $this->_timeperiod->id, 'user_id' => $this->_users['sally']->id) );
        $result = $api->process();
        $GLOBALS['log']->fatal(var_export($result, true));
        $this->assertEquals(10, sizeof($result));

        $this->_doReassign('sally', 'chris');

        $current_user = $this->_users['chris'];
        $api = new SugarForecasting_Individual( array('timeperiod_id' => $this->_timeperiod->id, 'user_id' => $this->_users['chris']->id) );
        $result = $api->process();
        $this->assertEquals(10, sizeof($result));

        $current_user = $this->_users['sally'];
        $api = new SugarForecasting_Individual( array('timeperiod_id' => $this->_timeperiod->id, 'user_id' => $this->_users['sally']->id) );
        $result = $api->process();
        $this->assertEquals(0, sizeof($result));

    }

    /**
     * test worksheets after reassignment manager to manager
     * @outputBuffering enabled
     * @group forecasts
     */
    public function testWorksheetManagerToManager()
    {
        $db = DBManagerFactory::getInstance();
        if ( !$db->supports('recursive_query') )
        {
            // @see SugarForecasting_Manager::process() -> loadUsers()
            $this->markTestSkipped('DBManager does not support recursive query');
        }

        $this->_createOpportunityForUser('sarah', 10);
        $this->_created_items = ForecastsSeedData::populateSeedData( array($this->_timeperiod->id => $this->_timeperiod) );
        $worksheets_ids = WorksheetSeedData::populateSeedData();
        SugarTestWorksheetUtilities::setCreatedWorksheet($worksheets_ids);

        require_once('include/SugarForecasting/Manager.php');

        $api = new SugarForecasting_Manager( array('timeperiod_id' => $this->_timeperiod->id, 'user_id' => $this->_users['sarah']->id) );
        $result = $api->process();
        $this->assertEquals(3, sizeof($result)); // 3 sarah's opps + sally + max

        $this->_doReassign('sarah', 'will');

        $api = new SugarForecasting_Manager( array('timeperiod_id' => $this->_timeperiod->id, 'user_id' => $this->_users['will']->id) );
        $result = $api->process();
        $this->assertEquals(4, sizeof($result)); // 4 = will's opps + chris + (sally + max)

        $api = new SugarForecasting_Manager( array('timeperiod_id' => $this->_timeperiod->id, 'user_id' => $this->_users['sarah']->id) );
        $result = $api->process();
        $this->assertEquals(1, sizeof($result)); // sarah opps only
    }

    /**
     * This is a test to move a manager's data (sarah) to a reportees (sally)
     * @outputBuffering enabled
     * @group forecasts
     */
    public function testReportsToSarahToSally()
    {
        $this->_doReassign('sarah', 'sally');

        $objJim = BeanFactory::getBean('Users');
        $objJim->retrieve($this->_users['jim']->id);
        $this->assertEmpty($objJim->reports_to_id, 'Jim report_to_id is not empty');

        $objSarah = BeanFactory::getBean('Users');
        $objSarah->retrieve($this->_users['sarah']->id);
        $this->assertEmpty($objSarah->reports_to_id, 'Sarah report_to_id is not empty');

        $objSally = BeanFactory::getBean('Users');
        $objSally->retrieve($this->_users['sally']->id);
        $this->assertEquals($this->_users['jim']->id, $objSally->reports_to_id, 'Sally does not report to Jim');

        $objMax = BeanFactory::getBean('Users');
        $objMax->retrieve($this->_users['max']->id);
        $this->assertEquals($this->_users['sally']->id, $objMax->reports_to_id, 'Max does not report to Sally');

        $objWill = BeanFactory::getBean('Users');
        $objWill->retrieve($this->_users['will']->id);
        $this->assertEquals($this->_users['jim']->id, $objWill->reports_to_id, 'Will does not report to Jim');

        $objChris = BeanFactory::getBean('Users');
        $objChris->retrieve($this->_users['chris']->id);
        $this->assertEquals($this->_users['will']->id, $objChris->reports_to_id, 'Chris does not report to Will');

    }

    /**
     * This is a test to check the reporting structure changes when jim (top level manager) has his data reassigned to
     * sally (a reportee)
     *
     * @outputBuffering enabled
     * @group forecasts
     */
    public function testReportsToJimToSally()
    {
        $this->_doReassign('jim', 'sally');

        $objJim = BeanFactory::getBean('Users');
        $objJim->retrieve($this->_users['jim']->id);
        $this->assertEmpty($objJim->reports_to_id, 'Jim report_to_id is not empty');

        $objSally = BeanFactory::getBean('Users');
        $objSally->retrieve($this->_users['sally']->id);
        $this->assertEmpty($objSally->reports_to_id, 'Sally report_to_id is not empty');

        $objSarah = BeanFactory::getBean('Users');
        $objSarah->retrieve($this->_users['sarah']->id);
        $this->assertEquals($this->_users['sally']->id, $objSarah->reports_to_id, 'Sarah does not report to Sally');

        $objMax = BeanFactory::getBean('Users');
        $objMax->retrieve($this->_users['max']->id);
        $this->assertEquals($this->_users['sarah']->id, $objMax->reports_to_id, 'Max does not report to Sarah');

        $objWill = BeanFactory::getBean('Users');
        $objWill->retrieve($this->_users['will']->id);
        $this->assertEquals($this->_users['sally']->id, $objWill->reports_to_id, 'Will does not report to Sally');

        $objChris = BeanFactory::getBean('Users');
        $objChris->retrieve($this->_users['chris']->id);
        $this->assertEquals($this->_users['will']->id, $objChris->reports_to_id, 'Chris does not report to Will');

    }

    /**
     * This is a test to check the reporting structure when sally (a reportee) has her data reassigned to chris (another reportee)
     * @outputBuffering enabled
     * @group forecasts
     */
    public function testReportsToSallyToChris()
    {
        $this->_doReassign('sally', 'chris');

        $objSally = BeanFactory::getBean('Users');
        $objSally->retrieve($this->_users['sally']->id);
        $this->assertEmpty($objSally->reports_to_id, 'Sally report_to_id is not empty');

        $objChris = BeanFactory::getBean('Users');
        $objChris->retrieve($this->_users['chris']->id);
        $this->assertEquals($this->_users['will']->id, $objChris->reports_to_id, 'Chris does not report to Will');

        $objWill = BeanFactory::getBean('Users');
        $objWill->retrieve($this->_users['will']->id);
        $this->assertEquals($this->_users['jim']->id, $objWill->reports_to_id, 'Will does not report to Jim');

        $objMax = BeanFactory::getBean('Users');
        $objMax->retrieve($this->_users['max']->id);
        $this->assertEquals($this->_users['sarah']->id, $objMax->reports_to_id, 'Max does not report to Sarah');

        $objSarah = BeanFactory::getBean('Users');
        $objSarah->retrieve($this->_users['sarah']->id);
        $this->assertEquals($this->_users['jim']->id, $objSarah->reports_to_id, 'Sarah does not report to Jim');
    }

    /**
     * This is a test to move two manager's data (sarah and jim) to a reportee (sally)
     * @outputBuffering enabled
     * @group forecasts
     */
    public function testReportsToSarahToSallyAndThenJimToSally()
    {
        $this->_doReassign('sarah', 'sally');
        $this->_doReassign('jim', 'sally');

        $objSarah = BeanFactory::getBean('Users');
        $objSarah->retrieve($this->_users['sarah']->id);
        $this->assertEmpty($objSarah->reports_to_id, 'Sarah report_to_id is not empty');

        $objJim = BeanFactory::getBean('Users');
        $objJim->retrieve($this->_users['jim']->id);
        $this->assertEmpty($objJim->reports_to_id, 'Jim report_to_id is not empty');

        $objSally = BeanFactory::getBean('Users');
        $objSally->retrieve($this->_users['sally']->id);
        $this->assertEmpty($objSally->reports_to_id, 'Sally report_to_id is not empty');

        $objMax = BeanFactory::getBean('Users');
        $objMax->retrieve($this->_users['max']->id);
        $this->assertEquals($this->_users['sally']->id, $objMax->reports_to_id, 'Max does not report to Sally');
    }


    /**
     * This is a test to move data between managers (sarah and will)
     * @outputBuffering enabled
     * @group forecasts
     */
    public function testReportsToSarahToWill()
    {
        $this->_doReassign('sarah', 'will');

        $objSarah = BeanFactory::getBean('Users');
        $objSarah->retrieve($this->_users['sarah']->id);
        $this->assertEmpty($objSarah->reports_to_id, 'Sarah report_to_id is not empty');

        $objSally = BeanFactory::getBean('Users');
        $objSally->retrieve($this->_users['sally']->id);
        $this->assertEquals($this->_users['will']->id, $objSally->reports_to_id, 'Sally does not report to Will');

        $objMax = BeanFactory::getBean('Users');
        $objMax->retrieve($this->_users['max']->id);
        $this->assertEquals($this->_users['will']->id, $objMax->reports_to_id, 'Sally does not report to Will');

        $objChris = BeanFactory::getBean('Users');
        $objChris->retrieve($this->_users['chris']->id);
        $this->assertEquals($this->_users['will']->id, $objChris->reports_to_id, 'Chris does not report to Will');

        $objWill = BeanFactory::getBean('Users');
        $objWill->retrieve($this->_users['will']->id);
        $this->assertEquals($this->_users['jim']->id, $objWill->reports_to_id, 'Will does not report to Jim');

    }
}
