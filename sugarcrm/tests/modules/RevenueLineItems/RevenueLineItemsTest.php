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

class RevenueLineItemsTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var RevenueLineItem
     */
    private $revenuelineitem;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', array('RevenueLineItems'));
        SugarTestForecastUtilities::setUpForecastConfig();
    }

    public function setUp()
    {
        parent::setUp();
        SugarTestForecastUtilities::setUpForecastConfig();
        $this->revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
    }

    public function tearDown()
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        SugarTestRevenueLineItemUtilities::removeAllCreatedRevenueLineItems();
        SugarTestWorksheetUtilities::removeAllCreatedWorksheets();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestProductTemplatesUtilities::removeAllCreatedProductTemplate();
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();

        parent::tearDown();
    }

    public static function tearDownAfterClass()
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    /**
     * This test checks to see that we can save a revenuelineitem where date_closed is set to null
     *
     * @group revenuelineitems
     */
    public function testCreateRevenueLineItemWithoutDateClosed()
    {
        $this->revenuelineitem->date_closed = null;
        $this->revenuelineitem->save();
        $this->assertEmpty($this->revenuelineitem->date_closed);
    }

    /**
     * @group revenuelineitems
     *
     * Test that the account_id in RevenueLineItem instance is properly set for a given Opportunity id.  I am
     * currently creating Opportunities with new Opportunity() because the test helper for Opportunities
     * creates accounts automatically.
     */
    public function testSetAccountForOpportunity()
    {
        //creating Opportunities with BeanFactory because the test helper for Opportunities
        // creates accounts automatically.
        $opp = BeanFactory::newBean("Opportunities");
        $opp->name = "opp1";
        $opp->date_closed = date('Y-m-d');
        $opp->save();
        $opp->load_relationship('accounts');
        SugarTestOpportunityUtilities::setCreatedOpportunity(array($opp->id));
        $account = SugarTestAccountUtilities::createAccount();
        $opp->accounts->add($account);
        $revenuelineitem = new MockRevenueLineItem();
        $this->assertTrue($revenuelineitem->setAccountIdForOpportunity($opp->id));

        //creating Opportunities with BeanFactory because the test helper for Opportunities
        // creates accounts automatically.
        $opp2 = BeanFactory::newBean("Opportunities");
        $opp2->name = "opp2";
        $opp2->date_closed = date('Y-m-d');
        $opp2->save();
        SugarTestOpportunityUtilities::setCreatedOpportunity(array($opp2->id));
        $revenuelineitem2 = new MockRevenueLineItem();
        $this->assertFalse($revenuelineitem2->setAccountIdForOpportunity($opp2->id));
    }

    //BEGIN SUGARCRM flav=pro && flav!=ent ONLY
    /**
     * @group revenuelineitems
     * @ticket SFA-567
     */
    public function testRevenueLineItemCreatedFromOpportunityContainsSalesStage()
    {
        $this->markTestIncomplete("This is just a bad test.  How can there be a revenuelineitem on an new opp?");
        $opp = SugarTestOpportunityUtilities::createOpportunity();

        $opp->load_relationship('revenuelineitems');

        $revenuelineitems = $opp->revenuelineitems->getBeans();

        $this->assertEquals(1, count($revenuelineitems));
        /* @var $revenuelineitem RevenueLineItem */
        $revenuelineitem = array_shift($revenuelineitems);

        SugarTestRevenueLineItemUtilities::setCreatedRevenueLineItem(array($revenuelineitem->id));

        $this->assertNotNull($opp->sales_stage); // make sure it's not set to null
        $this->assertEquals($opp->sales_stage, $revenuelineitem->sales_stage);
    }
    //end SUGARCRM flav=pro && flav!=ent ONLY

    //BEGIN SUGARCRM flav=ent ONLY
    /**
     * @group revenuelineitems
     */
    public function testSaveRevenueLineItemWorksheetReturnsFalseWhenForecastNotSetup()
    {
        /* @var $admin Administration */
        // get the current settings and set is_setup to 0
        $admin = BeanFactory::getBean('Administration');
        $settings = $admin->getConfigForModule('Forecasts');
        $admin->saveSetting('Forecasts', 'is_setup', 0, 'base');

        /* @var $revenuelineitem RevenueLineItem */
        $revenuelineitem = BeanFactory::getBean('RevenueLineItems');
        $ret = SugarTestReflection::callProtectedMethod($revenuelineitem, "saveProductWorksheet", array());

        $this->assertFalse($ret);

        // resave the settings to put it back like it was
        $admin->saveSetting('Forecasts', 'is_setup', intval($settings['is_setup']), 'base');
    }

    /**
     * @group revenuelineitems
     */
    public function testCreateRevenueLineItemCreatesForecastWorksheet()
    {
        /* @var $admin Administration */
        // get the current settings and set is_setup to 1
        $admin = BeanFactory::getBean('Administration');
        $settings = $admin->getConfigForModule('Forecasts');
        $admin->saveSetting('Forecasts', 'is_setup', 1, 'base');

        $revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();

        /* @var $worksheet ForecastWorksheet */
        $worksheet = BeanFactory::getBean('ForecastWorksheets');
        $worksheet->retrieve_by_string_fields(
            array(
                'parent_type' => $revenuelineitem->module_name,
                'parent_id' => $revenuelineitem->id,
                'draft' => 1,
                'deleted' => 0
            )
        );

        $this->assertNotEmpty($worksheet->id);
        $this->assertEquals($revenuelineitem->id, $worksheet->parent_id);
        // get the worksheet
        SugarTestWorksheetUtilities::setCreatedWorksheet(array($worksheet->id));

        // resave the settings to put it back like it was
        $admin->saveSetting('Forecasts', 'is_setup', intval($settings['is_setup']), 'base');
    }

    //END SUGARCRM flav=ent ONLY

    /**
     * @group revenuelineitems
     */
    public function testRevenueLineItemTemplateSetsRevenueLineItemFields()
    {

        $pt_values = array(
            'mft_part_num' => 'unittest',
            'list_price' => '800',
            'cost_price' => '400',
            'discount_price' => '700',
            'list_usdollar' => '800',
            'cost_usdollar' => '400',
            'discount_usdollar' => '700',
            'tax_class' => 'Taxable',
            'weight' => '100'
        );

        $pt = SugarTestProductTemplatesUtilities::createProductTemplate('', $pt_values);

        $revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $revenuelineitem->product_template_id = $pt->id;

        SugarTestReflection::callProtectedMethod($revenuelineitem, 'mapFieldsFromProductTemplate');

        foreach ($pt_values as $field => $value) {
            $this->assertEquals($value, $revenuelineitem->$field);
        }

    }

    /**
     * @group revenuelineitems
     */
    public function testRevenueLineItemTemplateSetsRevenueLineItemFieldsWithCurrencyConversion()
    {
        SugarTestCurrencyUtilities::createCurrency('Yen','¥','YEN',78.87,'currency-yen');
        $pt_values = array(
            'mft_part_num' => 'unittest',
            'list_price' => '800',
            'cost_price' => '400',
            'discount_price' => '700',
            'list_usdollar' => '800',
            'cost_usdollar' => '400',
            'discount_usdollar' => '700',
            'tax_class' => 'Taxable',
            'weight' => '100',
            'currency_id' => '-99'
        );

        $pt = SugarTestProductTemplatesUtilities::createProductTemplate('', $pt_values);

        $revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $revenuelineitem->product_template_id = $pt->id;
        $revenuelineitem->currency_id = 'currency-yen';

        SugarTestReflection::callProtectedMethod($revenuelineitem, 'mapFieldsFromProductTemplate');

        $this->assertEquals(SugarCurrency::convertAmount(800, '-99', 'currency-yen'), $revenuelineitem->list_price);
        $this->assertEquals(SugarCurrency::convertAmount(400, '-99', 'currency-yen'), $revenuelineitem->cost_price);
        $this->assertEquals(SugarCurrency::convertAmount(700, '-99', 'currency-yen'), $revenuelineitem->discount_price);

        // remove test currencies
    }

    /**
     * @group revenuelineitems
     */
    public function testBestCaseAutofillEmpty()
    {
        $revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $revenuelineitem->likely_case = 10000;
        $revenuelineitem->best_case = '';
        $revenuelineitem->save();

        $this->assertEquals($revenuelineitem->likely_case, $revenuelineitem->best_case);
    }

    /**
     * @group revenuelineitems
     */
    public function testBestCaseAutofillNull()
    {
        $revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $revenuelineitem->likely_case = 10000;
        $revenuelineitem->best_case = null;
        $revenuelineitem->save();

        $this->assertEquals($revenuelineitem->likely_case, $revenuelineitem->best_case);
    }

    /**
     * @group revenuelineitems
     */
    public function testBestCaseAutoRegression()
    {
        $revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $revenuelineitem->likely_case = 10000;
        $revenuelineitem->best_case = 42;
        $revenuelineitem->save();

        $this->assertEquals(42, $revenuelineitem->best_case);
    }

    /**
     * @group revenuelineitems
     */
    public function testWorstCaseAutofillEmpty()
    {
        $revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $revenuelineitem->likely_case = 10000;
        $revenuelineitem->worst_case = '';
        $revenuelineitem->save();

        $this->assertEquals($revenuelineitem->likely_case, $revenuelineitem->worst_case);
    }

    /**
     * @group revenuelineitems
     */
    public function testWorstCaseAutofillNull()
    {
        $revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $revenuelineitem->likely_case = 10000;
        $revenuelineitem->worst_case = null;
        $revenuelineitem->save();

        $this->assertEquals($revenuelineitem->likely_case, $revenuelineitem->worst_case);
    }

    /**
     * @group revenuelineitems
     */
    public function testWorstCaseAutofillRegression()
    {
        $revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $revenuelineitem->likely_case = 10000;
        $revenuelineitem->worst_case = 42;
        $revenuelineitem->save();

        $this->assertEquals(42, $revenuelineitem->worst_case);
    }

    /**
     * @group revenuelineitems
     */
    public function testEmptyQuantityDefaulted()
    {
        $revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();

        $revenuelineitem->quantity = "";
        $revenuelineitem->save();
        $this->assertEquals(1, $revenuelineitem->quantity, "Empty string not converted to 1");
    }

    /**
     * @group revenuelineitems
     */
    public function testNullQuantityDefaulted()
    {
        $revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();

        $revenuelineitem->quantity = null;
        $revenuelineitem->save();
        $this->assertEquals(1, $revenuelineitem->quantity, "Null not converted to 1");
    }

    /**
     * @group revenuelineitems
     */
    public function testQuantityNotDefaulted()
    {
        $revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();

        $revenuelineitem->quantity = 42;
        $revenuelineitem->save();
        $this->assertEquals(42, $revenuelineitem->quantity, "Null not converted to 1");
    }

    /**
     * @dataProvider dataProviderMapProbabilityFromSalesStage
     * @group revenuelineitems
     */
    public function testProbabilityNotOverwrittenBySaleStageIfGiven($salesStage)
    {
        $revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();

        $revenuelineitem->sales_stage = $salesStage;
        $revenuelineitem->probability = 22;
        $revenuelineitem->save();
        $this->assertEquals(22, $revenuelineitem->probability);
    }

    // BEGIN SUGARCRM flav=ent ONLY
    /**
     * @group revenuelineitems
     * @group forecasts
     * @ticket SFA-716
     * @dataProvider dataProviderCreateRevenueLineItemWithSalesStageCreatesForecastWorksheetWithSameSalesStage
     */
    public function testCreateRevenueLineItemWithSalesStageCreatesForecastWorksheetWithSameSalesStage($sales_stage)
    {
        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');
        $settings = $admin->getConfigForModule('Forecasts');
        $admin->saveSetting('Forecasts', 'is_setup', 1, 'base');


        $revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $revenuelineitem->sales_stage = $sales_stage;
        $revenuelineitem->save();

        // reset the flag before we run any assertions just to make sure it gets set back if we have a fatal error
        $admin->saveSetting('Forecasts', 'is_setup', $settings['is_setup'], 'base');
        // load up the draft worksheet
        $worksheet = SugarTestWorksheetUtilities::loadWorksheetForBean($revenuelineitem);

        $this->assertEquals($sales_stage, $revenuelineitem->sales_stage);
        $this->assertInstanceOf('ForecastWorksheet', $worksheet);
        $this->assertEquals($sales_stage, $worksheet->sales_stage);
    }

    /**
     * Data Provider
     *
     * @return array
     */
    public function dataProviderCreateRevenueLineItemWithSalesStageCreatesForecastWorksheetWithSameSalesStage()
    {
        return array(
            array('Prospecting'),
            array('Qualification'),
            array('Needs Analysis'),
            array('Value Proposition'),
            array('Id. Decision Makers'),
            array('Perception Analysis'),
            array('Proposal/Price Quote'),
            array('Negotiation/Review'),
        );
    }
    // END SUGARCRM flav=ent ONLY
    
    /**
     * @dataProvider dataProviderMapProbabilityFromSalesStage
     * @group revenuelineitems
     */
    public function testMapProbabilityFromSalesStage($sales_stage, $probability)
    {
        $revenuelineitem = new MockRevenueLineItem();
        $revenuelineitem->sales_stage = $sales_stage;
        // use the Reflection Helper to call the Protected Method
        SugarTestReflection::callProtectedMethod($revenuelineitem, 'mapProbabilityFromSalesStage');

        $this->assertEquals($probability, $revenuelineitem->probability);
    }

    public static function dataProviderMapProbabilityFromSalesStage()
    {
        return array(
            array('Prospecting', '10'),
            array('Qualification', '20'),
            array('Needs Analysis', '25'),
            array('Value Proposition', '30'),
            array('Id. Decision Makers', '40'),
            array('Perception Analysis', '50'),
            array('Proposal/Price Quote', '65'),
            array('Negotiation/Review', '80'),
            array('Closed Won', '100'),
            array('Closed Lost', '0')
        );
    }

    //BEGIN SUGARCRM flav=ent ONLY
    /**
     * @group revenuelineitems
     * @ticket SFA-814
     */
    public function testRevenueLineItemMarkDeletedAlsoDeletesWorksheet()
    {
        SugarTestTimePeriodUtilities::createTimePeriod('2013-01-01', '2013-03-31');

        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->date_closed = '2013-01-01';
        $opp->save();

        $revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $revenuelineitem->opportunity_id = $opp->id;
        $revenuelineitem->date_closed = '2013-01-01';
        $revenuelineitem->save();

        $worksheet = SugarTestWorksheetUtilities::loadWorksheetForBean($revenuelineitem);

        // assert that worksheet is not deleted
        $this->assertEquals(0, $worksheet->deleted);

        $revenuelineitem->mark_deleted($revenuelineitem->id);

        $this->assertEquals(1, $revenuelineitem->deleted);

        // fetch the worksheet again
        unset($worksheet);
        $worksheet = SugarTestWorksheetUtilities::loadWorksheetForBean($revenuelineitem, false, true);
        $this->assertEquals(1, $worksheet->deleted);
    }
    //END SUGARCRM flav=ent ONLY

    /**
     * @group revenuelineitems
     * @group currency
     * @ticket SFA-745
     */
    public function testRevenueLineItemSaveSetsCurrencyBaseRate()
    {
        $currency = SugarTestCurrencyUtilities::createCurrency('Philippines', '₱', 'PHP', 41.82982, 'currency-php');

        $revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $revenuelineitem->currency_id = $currency->id;
        $revenuelineitem->save();

        $this->assertEquals($currency->id, $revenuelineitem->currency_id);
        $this->assertEquals($currency->conversion_rate, $revenuelineitem->base_rate);

    }

    /**
     * @group revenuelineitems
     * @ticket SFA-511
     */
    public function testMapFieldsFromOpportunity()
    {
        $revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $revenuelineitem->opportunity_id = $opp->id;
        $opp->opportunity_type = 'new';
        $revenuelineitem->save();
        $this->assertEquals('new', $revenuelineitem->product_type);
    }

    /**
     * @group revenuelineitems
     *
     * Test that RLI converted to quote uses product name.
     */
    public function testRevenueLineItemQuoteName()
    {

        $pt_values = array(
            'name' => 'foobar',
            'mft_part_num' => 'unittest',
            'list_price' => '800',
            'cost_price' => '400',
            'discount_price' => '700',
            'list_usdollar' => '800',
            'cost_usdollar' => '400',
            'discount_usdollar' => '700',
            'tax_class' => 'Taxable',
            'weight' => '100'
        );

        $pt = SugarTestProductTemplatesUtilities::createProductTemplate('', $pt_values);

        $revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $revenuelineitem->product_template_id = $pt->id;

        $product = $revenuelineitem->convertToQuotedLineItem();

        $this->assertEquals($product->name, $pt->name);

    }

}

class MockRevenueLineItem extends RevenueLineItem
{
    //BEGIN SUGARCRM flav=ent ONLY
    private $handleOppSalesStatusCalled = false;
    
    public function handleOppSalesStatus()
    {
        $this->handleOppSalesStatusCalled = true;
        parent::handleOppSalesStatus();
    }

    public function handleOppSalesStatusCalled()
    {
        return $this->handleOppSalesStatusCalled;
    }
    //END SUGARCRM flav=ent ONLY

    public function setAccountIdForOpportunity($oppId)
    {
        return parent::setAccountIdForOpportunity($oppId);
    }
}
