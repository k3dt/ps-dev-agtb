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

class ForecastTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var Currency
     */
    protected $currency;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        SugarTestForecastUtilities::setUpForecastConfig();

        $this->currency = SugarTestCurrencyUtilities::createCurrency('MonkeyDollars','$','MOD',2.0);

        $GLOBALS['current_user']->setPreference('currency', $this->currency->id);
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        SugarTestForecastUtilities::removeAllCreatedForecasts();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestHelper::tearDown();
    }

    /**
     * Test that the base_rate field is populated with rate
     * of currency_id
     *
     * @group forecasts
     */
    public function testForecastRate() {
        $timeperiod = SugarTestTimePeriodUtilities::createTimePeriod();
        $forecast = SugarTestForecastUtilities::createForecast($timeperiod, $GLOBALS['current_user']);
        $currency = SugarTestCurrencyUtilities::getCurrencyByISO('MOD');
        $forecast->currency_id = $currency->id;
        $forecast->save();
        $this->assertEquals(
            sprintf('%.6f',$forecast->base_rate),
            sprintf('%.6f',$currency->conversion_rate)
        );
    }
}
