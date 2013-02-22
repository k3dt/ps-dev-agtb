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

require_once('modules/Products/clients/base/api/ProductToQuoteConvertApi.php');
class ProductToQuoteConvertApiTests extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Opportunity
     */
    protected $opp;

    /**
     * @var Product
     */
    protected $product;

    public function setUp()
    {
        $this->opp = SugarTestOpportunityUtilities::createOpportunity();

        $this->product = array_shift($this->opp->getProducts());
    }

    public function tearDown()
    {
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestProductBundleUtilities::removeAllCreatedProductBundles();
        SugarTestQuoteUtilities::removeAllCreatedQuotes();
    }

    /**
     * @group products
     * @group quotes
     */
    public function testCreateQuoteFromProductApi()
    {
        /* @var $restService RestService */
        $restService = $this->getMock('ServiceBase');
        $restService->user = $GLOBALS['current_user'];

        $api = new ProductToQuoteConvertApi();
        $return = $api->convertToQuote($restService, array('module' => 'Products', 'record' => $this->product->id));

        $this->assertNotEmpty($return['id']);

        SugarTestQuoteUtilities::setCreatedQuote(array($return['id']));

        // now pull up the quote to make sure it matches the stuff from the opp
        /* @var $quote Quote */
        $quote = BeanFactory::getBean('Quotes', $return['id']);

        $this->assertEquals($this->opp->id, $quote->opportunity_id);

        // get the product bundle to make sure it contains the product id
        $bundle = array_shift($quote->get_product_bundles());
        $product = array_shift($bundle->get_products());

        SugarTestProductBundleUtilities::setCreatedProductBundle(array($bundle->id));

        var_dump($product);

        $this->assertEquals($product->id, $bundle->product_id);
    }

}
