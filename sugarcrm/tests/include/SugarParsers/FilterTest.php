<?php

/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
require_once("include/SugarParsers/Filter.php");
class SugarParsers_FilterTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var SugarParsers_Filter
     */
    protected $obj;

    public function setUp()
    {
        $this->obj = new SugarParsers_Filter();
    }

    public function tearDown()
    {
        $filterDict = new FilterDictionary();
        $filterDict->resetCache();
        unset($this->obj);
    }

    /**
     * @group SugarParser
     */
    public function testParseJsonEqual()
    {
        $this->obj->parseJson('{"billing_postalcode":"90210"}');
        $pFilter = $this->obj->getParsedFilter();
        $this->assertInstanceOf("SugarParsers_Filter_Equal", $pFilter['billing_postalcode']);
    }

    /**
     * @group SugarParser
     */
    public function testConverterRuns()
    {
        $this->obj->parseJson('{"billing_postalcode":"90210"}');
        $actual = $this->obj->convert(new MockConverter());
        $this->assertEquals('convertSuccessful', $actual);
    }

    /**
     * @group SugarParser
     */
    public function testParseEqual()
    {
        $obj = json_decode('{"billing_postalcode":"90210"}');
        $this->obj->parse($obj);
        $pFilter = $this->obj->getParsedFilter();
        $this->assertInstanceOf("SugarParsers_Filter_Equal", $pFilter['billing_postalcode']);
    }

    /**
     * @group SugarParser
     */
    public function testParseNotEqual()
    {
        $obj = json_decode('{"billing_postalcode": { "$not" : "90210" } }');
        $this->obj->parse($obj);
        $pFilter = $this->obj->getParsedFilter();
        $this->assertInstanceOf("SugarParsers_Filter_Not", $pFilter['billing_postalcode']);
        $equalObject = $pFilter['billing_postalcode']->getValue();
        $this->assertInstanceOf("SugarParsers_Filter_Equal", $equalObject);
        $this->assertSame("90210", $equalObject->getValue());

    }

    /**
     * @group SugarParser
     */
    public function testParseEmpty()
    {
        $obj = json_decode('{"billing_postalcode" : "$empty" }');
        $this->obj->parse($obj);
        $pFilter = $this->obj->getParsedFilter();
        $this->assertInstanceOf("SugarParsers_Filter_Empty", $pFilter['billing_postalcode']);
    }

    /**
     * @group SugarParser
     */
    public function testParseNotEmpty()
    {
        $obj = json_decode('{ "billing_postalcode" : { "$not" :  "$empty" } }');
        $this->obj->parse($obj);
        $pFilter = $this->obj->getParsedFilter();
        $this->assertInstanceOf("SugarParsers_Filter_Not", $pFilter['billing_postalcode']);
        $this->assertInstanceOf("SugarParsers_Filter_Empty", $pFilter['billing_postalcode']->getValue());
    }

    /**
     * @group SugarParser
     */
    public function testInvalidFilterVariableName()
    {
        $obj = json_decode('{"billing_postalcode": { "$doesnotexit" : ["90210", "46052"] }}');
        $this->obj->parse($obj);
        $pFilter = $this->obj->getParsedFilter();
        $this->assertEmpty($pFilter);
    }


    /**
     * @group SugarParser
     */
    public function testInFilterParse()
    {

        $obj = json_decode('{"billing_postalcode": { "$in" : ["90210", "46052"] }}');

        $this->obj->parse($obj);
        $pFilter = $this->obj->getParsedFilter();
        $this->assertInstanceOf("SugarParsers_Filter_In", $pFilter['billing_postalcode']);
        $inValue = $pFilter['billing_postalcode']->getValue();
        $this->assertSame(array('90210', '46052'), $inValue);

    }

    /**
     * @group SugarParser
     */
    public function testInFilterParseValueNotArray()
    {

        $obj = json_decode('{"billing_postalcode": { "$in" : "90210" }}');

        $this->obj->parse($obj);
        $pFilter = $this->obj->getParsedFilter();
        $this->assertInstanceOf("SugarParsers_Filter_In", $pFilter['billing_postalcode']);
        $inValue = $pFilter['billing_postalcode']->getValue();
        $this->assertSame(array('90210'), $inValue);

    }

    /**
     * @group SugarParser
     */
    public function testNotInFilterParse()
    {

        $obj = json_decode('{"billing_postalcode": { "$not" : { "$in" : ["90210", "46052"] }}}');

        $this->obj->parse($obj);
        $pFilter = $this->obj->getParsedFilter();
        $this->assertInstanceOf("SugarParsers_Filter_Not", $pFilter['billing_postalcode']);
        $inFilterObj = $pFilter['billing_postalcode']->getValue();
        $this->assertInstanceOf("SugarParsers_Filter_In", $inFilterObj);
        $inValue = $inFilterObj->getValue();
        $this->assertSame(array('90210', '46052'), $inValue);

    }

    /**
     * @group SugarParser
     */
    public function testParseWithAnd()
    {
        $obj = json_decode('{"$and" : [{"first_name":"William"},{"last_name":"Williamson"}] }');

        $this->obj->parse($obj);

        $pFilter = $this->obj->getParsedFilter();
        $this->assertInstanceOf("SugarParsers_Filter_And", $pFilter['0']);
        $andFilterObject = $pFilter['0']->getValue();
        $this->assertInstanceOf("SugarParsers_Filter_Equal", $andFilterObject['first_name']);
        $this->assertEquals("William", $andFilterObject['first_name']->getValue());
        $this->assertInstanceOf("SugarParsers_Filter_Equal", $andFilterObject['last_name']);
        $this->assertEquals("Williamson", $andFilterObject['last_name']->getValue());
    }

    /**
     * @group SugarParser
     */
    public function testParseWithSameFilterName()
    {
        $obj = json_decode('{"$and" : [{"first_name":"William"},{"first_name":"Williamson"}] }');

        $this->obj->parse($obj);

        $pFilter = $this->obj->getParsedFilter();
        $this->assertEquals(2, count($pFilter['0']->getValue()));
    }

    /**
     * @group SugarParser
     */
    public function testLinkFilter()
    {
        $obj = json_decode('{"member_of":{"$and":[{"state":"UT"},{"country":"USA"}]}}');

        $this->obj->parse($obj);
        $pFilter = $this->obj->getParsedFilter();

        $this->assertInstanceOf("SugarParsers_Filter_And", $pFilter['member_of']);
        $andFilterObject = $pFilter['member_of']->getValue();
        $this->assertInstanceOf("SugarParsers_Filter_Equal", $andFilterObject['state']);
        $this->assertEquals("UT", $andFilterObject['state']->getValue());
        $this->assertInstanceOf("SugarParsers_Filter_Equal", $andFilterObject['country']);
        $this->assertEquals("USA", $andFilterObject['country']->getValue());

    }
}

require_once('include/SugarParsers/Converter/AbstractConverter.php');
class MockConverter extends SugarParsers_Converter_AbstractConverter
{
    public function convert($value)
    {
        return 'convertSuccessful';
    }
}