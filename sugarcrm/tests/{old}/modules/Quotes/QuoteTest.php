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

/**
 * @coversDefaultClass Quote
 */
class QuoteTest extends TestCase
{
    protected function setUp() : void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        SugarTestCurrencyUtilities::createCurrency('MonkeyDollars', '$', 'MOD', 2.0);
    }

    protected function tearDown() : void
    {
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        SugarTestQuoteUtilities::removeAllCreatedQuotes();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestProductBundleUtilities::removeAllCreatedProductBundles();
        SugarTestHelper::tearDown();
    }

    /**
     * test get related product bundles
     */
    public function testGetProductBundles()
    {
        $quote = SugarTestQuoteUtilities::createQuote();
        $pblist = $quote->get_product_bundles();
        $this->assertEquals(0, count($pblist));
        $expected = SugarTestProductBundleUtilities::createProductBundle();
        SugarTestQuoteUtilities::relateQuoteToProductBundle($quote->id, $expected->id);
        $pblist = $quote->get_product_bundles();
        $actual = $pblist[0];
        $this->assertEquals($expected->id, $actual->id, "Unable to get quote related product bundle.");
    }

    /**
     * Test that the base_rate field is populated with rate
     * of currency_id
     */
    public function testQuoteRate()
    {
        $quote = SugarTestQuoteUtilities::createQuote();
        $currency = SugarTestCurrencyUtilities::getCurrencyByISO('MOD');
        $quote->currency_id = $currency->id;
        $quote->save();
        $this->assertEquals(
            sprintf('%.6f', $quote->base_rate),
            sprintf('%.6f', $currency->conversion_rate)
        );
    }

    /**
     * test related opportunity count
     */
    public function testGetRelatedOpportunityCount()
    {
        $quote = SugarTestQuoteUtilities::createQuote();
        $this->assertEquals(0, $quote->getRelatedOpportunityCount());
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        SugarTestQuoteUtilities::relateQuoteToOpportunity($quote->id, $opp->id);
        $this->assertEquals(1, $quote->getRelatedOpportunityCount());
    }

    /**
     * test get related opportunities
     */
    public function testGetRelatedOpportunities()
    {
        $quote = SugarTestQuoteUtilities::createQuote();
        $this->assertEquals(0, $quote->getRelatedOpportunityCount());
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        SugarTestQuoteUtilities::relateQuoteToOpportunity($quote->id, $opp->id);
        $relopp = $quote->getRelatedOpportunities();
        $this->assertEquals($opp->id, $relopp[0]['opportunity_id']);
    }

    public function testMarkDeleted()
    {
        $quote = $this->getMockBuilder('Quote')
            ->setMethods(array('save', 'retrieve', 'load_relationship'))
            ->getMock();

        $link2 = $this->getMockBuilder('Link2')
            ->setMethods(array('getBeans'))
            ->disableOriginalConstructor()
            ->getMock();

        $product_bundle = $this->getMockBuilder('ProductBundle')
            ->setMethods(array('mark_deleted'))
            ->getMock();

        $product_bundle->id = 'pb_unittest';

        $product_bundle->expects($this->once())
            ->method('mark_deleted')
            ->with('pb_unittest');

        $link2->expects($this->once())
            ->method('getBeans')
            ->will($this->returnValue(array($product_bundle)));

        $quote->product_bundles = $link2;


        $quote->expects($this->once())
            ->method('retrieve')
            ->with('quote_unittest');

        /* @var $quote Quote */
        $quote->mark_deleted('quote_unittest');
    }

    /**
     * @dataProvider dataProviderQuoteIsClosed
     * @param string $stage
     * @param boolean $expected
     */
    public function testQuoteIsClosed($stage, $expected)
    {
        $quote = $this->getMockBuilder('Quote')
            ->setMethods(array('save', 'retrieve', 'load_relationship'))
            ->getMock();

        $quote->quote_stage = $stage;

        $this->assertEquals($expected, $quote->isClosed());
    }

    /**
     * Data Provider for testQuoteIsClosed
     *
     * @return array
     */
    public function dataProviderQuoteIsClosed()
    {
        return array(
            array('Draft', false),
            array('Negotiation', false),
            array('Delivered', false),
            array('On Hold', false),
            array('Confirmed', false),
            array('Closed Accepted', true),
            array('Closed Lost', true),
            array('Closed Dead', true)
        );
    }

    /**
     * @dataProvider dataProviderQuoteUpdateCurrencyBaseRate
     * @param string $stage
     * @param boolean $expected
     */
    public function testQuoteUpdateBaseRate($stage, $expected)
    {
        $quote = $this->getMockBuilder('Quote')
            ->setMethods(array('save', 'retrieve', 'load_relationship'))
            ->getMock();

        $quote->quote_stage = $stage;

        $this->assertEquals($expected, $quote->updateCurrencyBaseRate());
    }

    /**
     * Data Provider for testQuoteUpdateCurrencyBaseRate
     *
     * @return array
     */
    public function dataProviderQuoteUpdateCurrencyBaseRate()
    {
        return array(
            array('Draft', true),
            array('Negotiation', true),
            array('Delivered', true),
            array('On Hold', true),
            array('Confirmed', true),
            array('Closed Accepted', false),
            array('Closed Lost', false),
            array('Closed Dead', false)
        );
    }

    /**
     * @covers ::updateProductsAccountId
     */
    public function testUpdateProductsAccountId()
    {
        $quote = $this->getMockBuilder('Quote')
        ->setMethods(array('load_relationship'))
        ->getMock();
        $quote->expects($this->once())
        ->method('load_relationship')
        ->with('product_bundles')
        ->willReturn(true);
        $quote->billing_account_id = 'new';
        $quote->account_id = 'old';
        $product = $this->getMockBuilder('Product')
        ->setMethods(array('save'))
        ->getMock();
        $product->account_id = 'old';
        $product->expects($this->once())
        ->method('save');
        $link2 = $this->getMockBuilder('Link2')
        ->setMethods(array('getBeans'))
        ->disableOriginalConstructor()
        ->getMock();
        $link2->expects($this->once())
        ->method('getBeans')
        ->will($this->returnValue(array($product)));
        $bundle = $this->getMockBuilder('ProductBundle')
        ->setMethods(array('load_relationship'))
        ->getMock();
        $bundle->expects($this->once())
        ->method('load_relationship')
        ->with('products')
        ->willReturn(true);
        $bundle->products = $link2;
        $link2 = $this->getMockBuilder('Link2')
        ->setMethods(array('getBeans'))
        ->disableOriginalConstructor()
        ->getMock();
        $link2->expects($this->once())
        ->method('getBeans')
        ->will($this->returnValue(array($bundle)));
        $quote->product_bundles = $link2;
        $quote->updateProductsAccountId();
        $this->assertEquals($quote->billing_account_id, $product->account_id, 'Product account_id should have been updated');
    }
}
