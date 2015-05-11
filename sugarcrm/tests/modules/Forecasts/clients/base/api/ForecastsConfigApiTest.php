<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once 'include/api/RestService.php';
require_once 'modules/Forecasts/clients/base/api/ForecastsConfigApi.php';

/**
 * Class ForecastsConfigApiTest
 * @coversDefaultClass \ForecastsConfigApi
 */
class ForecastsConfigApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $createdBeans = array();

    public function setUp(){
        SugarTestHelper::setup('beanList');
        SugarTestHelper::setup('moduleList');
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        SugarTestForecastUtilities::setUpForecastConfig(
            array(
                'worksheet_columns' => array(),
            )
        );

        $GLOBALS['current_user']->is_admin = 1;
        parent::setUp();
    }

    public function tearDown()
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        $db = DBManagerFactory::getInstance();
        $db->query("DELETE FROM config where name = 'testSetting' and category = 'Forecasts'");
        $db->commit();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * test the create api
     *
     * @group forecasts
     * @covers ::forecastsConfigSave
     */
    public function testCreateConfig() {
        // Get the real data that is in the system, not the partial data we have saved

        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];


        $args = array(
            "module" => "Forecasts",
            "testSetting" => "testValue",
            'worksheet_columns' => array(),
            'show_worksheet_best' => 1,
            'show_worksheet_worst' => 0
        );
        /* @var ForecastsConfigApi $apiClass */
        $apiClass = $this->getMock('ForecastsConfigApi', array('timePeriodSettingsChanged'));
        $apiClass->expects($this->once())
            ->method('timePeriodSettingsChanged')
            ->will($this->returnValue(false));
        $result = $apiClass->forecastsConfigSave($api, $args);
        $this->assertArrayHasKey("testSetting", $result);
        $this->assertEquals($result['testSetting'], "testValue");

        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');

        $results = $admin->getConfigForModule('Forecasts', 'base');

        $this->assertArrayHasKey("testSetting", $results);
        $this->assertEquals($results['testSetting'], "testValue");
    }

    /**
     * test the get config
     * @group forecasts
     * @covers ::config
     */
    public function testReadConfig() {
        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');
        $admin->saveSetting('Forecasts', 'testSetting', 'testValue', 'base');

        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];

        $args = array(
            "module" => "Forecasts",
        );
        $apiClass = new ForecastsConfigApi();
        $result = $apiClass->config($api, $args);
        $this->assertArrayHasKey("testSetting", $result);
        $this->assertEquals($result['testSetting'], "testValue");
    }

    /**
     * test the update config
     * @group forecasts
     * @covers ::forecastsConfigSave
     */
    public function testUpdateConfig() {
        $testSetting = 'testValue';
        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');
        $admin->saveSetting('Forecasts', 'testSetting', $testSetting, 'base');

        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];

        $args = array(
            "module" => "Forecasts",
            "testSetting" => strrev($testSetting),
            'worksheet_columns' => array(),
            'show_worksheet_best' => 1,
            'show_worksheet_worst' => 0
        );
        $apiClass = $this->getMock('ForecastsConfigApi', array('timePeriodSettingsChanged'));
        $apiClass->expects($this->once())
            ->method('timePeriodSettingsChanged')
            ->will($this->returnValue(false));
        $result = $apiClass->forecastsConfigSave($api, $args);
        $this->assertArrayHasKey("testSetting", $result);
        $this->assertEquals($result['testSetting'], strrev($testSetting));

        $results = $admin->getConfigForModule('Forecasts', 'base');

        $this->assertArrayHasKey("testSetting", $results);
        $this->assertNotEquals($results['testSetting'], $testSetting);
        $this->assertEquals($results['testSetting'], strrev($testSetting));
    }

    /**
     * @covers ::forecastsConfigSave
     */
    public function testSetConfigWithEmptyWorksheetColumns()
    {
        $testSetting = 'testValue';
        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');
        $admin->saveSetting('Forecasts', 'testSetting', $testSetting, 'base');

        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];

        $args = array(
            "module" => "Forecasts",
            'worksheet_columns' => array(),
            'show_worksheet_best' => 1,
            'show_worksheet_worst' => 0
        );
        $apiClass = $this->getMock('ForecastsConfigApi', array('timePeriodSettingsChanged', 'setWorksheetColumns'));
        $apiClass->expects($this->once())
            ->method('timePeriodSettingsChanged')
            ->will($this->returnValue(false));
        $apiClass->expects($this->once())
            ->method('setWorksheetColumns')
            ->will($this->returnValue(true));
        $apiClass->forecastsConfigSave($api, $args);

        $results = $admin->getConfigForModule('Forecasts', 'base');

        $this->assertNotEmpty($results['worksheet_columns']);
    }

    /**
     * test the create api using bad credentials, should receive a failure
     *
     * @group forecasts
     * @expectedException SugarApiExceptionNotAuthorized
     * @covers ::forecastsConfigSave
     */
    public function testCreateBadCredentialsConfig() {
        $GLOBALS['current_user']->is_admin = 0;

        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];


        $args = array(
            "module" => "Forecasts",
            "testSetting" => "testValue",
        );
        $apiClass = new ForecastsConfigApi();
        $result = $apiClass->forecastsConfigSave($api, $args);

        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');

        $results = $admin->getConfigForModule('Forecasts', 'base');

        $this->assertArrayNotHasKey("testSetting", $results);
    }

    /**
     * test the save config calls TimePeriodSettingsChanged
     * @group forecasts
     * @covers ::forecastsConfigSave
     */
    public function testSaveConfigTimePeriodSettingsChangedCalled() {
        $testSetting = 'testValue';
        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');
        $admin->saveSetting('Forecasts', 'testSetting', $testSetting, 'base');

        $priorSettings = $admin->getConfigForModule('Forecasts', 'base');
        $currentSettings = $admin->getConfigForModule('Forecasts', 'base');

        $currentSettings['worksheet_columns'] = array(
            0 => 'commit_stage',
            1 => 'parent_name',
            2 => 'likely_case',
            3 => 'best_case'
        );

        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];

        $args = array(
            "module" => "Forecasts",
        );

        $args = array_merge($args, $priorSettings);

        $apiClass = $this->getMock('ForecastsConfigApi', array('timePeriodSettingsChanged'));

        if(empty($priorSettings['is_setup'])) {
            $priorSettings['timeperiod_shown_forward'] = 0;
            $priorSettings['timeperiod_shown_backward'] = 0;
        }

        $apiClass->expects($this->once())
                                ->method('timePeriodSettingsChanged')
                                ->with($priorSettings, $currentSettings);

        $apiClass->forecastsConfigSave($api, $args);
    }

    /**
   	 * @return array asserting data with the key data points changed to test each conditional
   	 */
   	public function getTimePeriodSettingsData()
   	{
   		return array(
               array(
                   array(
                  ),
                  false
               ),
               array(
                   array(
                      'timeperiod_shown_backward' => '3',
                  ),
                  true
               ),
               array(
                   array(
                      'timeperiod_shown_forward' => '3',
                  ),
                  true
               ),
               array(
                   array(
                      'timeperiod_start_date' => '2013-03-01',
                  ),
                  true
               ),
               array(
                   array(
                      'timeperiod_interval' => TimePeriod::QUARTER_TYPE,
                  ),
                  true
               ),
               array(
                   array(
                      'timeperiod_leaf_interval' => TimePeriod::MONTH_TYPE,
                  ),
                  true
               ),
               array(
                   array(
                      'timeperiod_type' => 'fiscal',
                  ),
                  true
               ),
   		);
   	}

    /**
     * check the conditionals and that they return expected values for the timePeriodSettingsChanged function
     *
     * @dataProvider getTimePeriodSettingsData
     * @param $changedSettings
     * @param $expectedResult
     * @group forecasts
     * @covers ::timePeriodSettingsChanged
     */
    public function testTimePeriodSettingsChagned($changedSettings, $expectedResult)
   	{
        $priorSettings = array(
                           'timeperiod_shown_backward' => '2',
                           'timeperiod_shown_forward' => '2',
                           'timeperiod_start_date' => '2013-01-01',
                           'timeperiod_interval' => TimePeriod::ANNUAL_TYPE,
                           'timeperiod_leaf_interval' => TimePeriod::QUARTER_TYPE,
                           'timeperiod_type' => 'chronological',
                       );

        $currentSettings = array_merge($priorSettings, $changedSettings);

        $apiClass = new ForecastsConfigApi();
        $result = $apiClass->timePeriodSettingsChanged($priorSettings, $currentSettings);

        $this->assertEquals($expectedResult, $result, "TimePeriod Setting check failed for given parameters. Prior Settings: " . print_r($priorSettings,1) . " Current Settings: " . print_r($currentSettings, 1) . " result: " . print_r($result,1));
    }

}
