<?php
//FILE SUGARCRM flav=ent ONLY

/*********************************************************************************
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
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once 'modules/ModuleBuilder/parsers/views/PortalGridLayoutMetaDataParser.php' ;



class PortalGridLayoutMetaDataParserTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_parser;

    public function setUp()
    {
        //echo "Setup";
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->is_admin = true;
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $GLOBALS['mod_strings'] = array();
        $this->_parser = new PortalGridLayoutMetaDataParserTestDerivative(MB_PORTALEDITVIEW, 'Leads') ;
    }

    public function tearDown()
    {
        //echo "TearDown";
        unset($GLOBALS['mod_strings']);
        unset($GLOBALS['app_list_strings']);
        unset($GLOBALS['current_user']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function testdoWeHasAParser()
    {
        $this->assertInstanceOf('PortalGridLayoutMetaDataParser',$this->_parser);
    }

    protected function getMockRequestArray() {
        return array(
            'PORTAL' => '1',
            'action' => 'saveLayout',
            'module' => 'ModuleBuilder',
            'panel-1-label' => '0',
            'panel-1-name' => 'Default',
            'panels_as_tabs',
            'slot-1-0-label' => 'Salutation',
            'slot-1-0-name' => 'salutation',
            'slot-1-1-name' => '(empty)',
            'slot-1-10-label' => 'Email Opt Out:',
            'slot-1-10-name' => 'email_opt_out',
            'slot-1-11-name' => '(filler)',
            'slot-1-12-label' => 'Title:',
            'slot-1-12-name' => 'title',
            'slot-1-13-label' => 'Department:',
            'slot-1-13-name' => 'department',
            'slot-1-14-label' => 'Account Name:',
            'slot-1-14-name' => 'account_name',
            'slot-1-15-name' => '(empty)',
            'slot-1-16-label' => 'Primary Address Street',
            'slot-1-16-name' => 'primary_address_street',
            'slot-1-17-name' => '(empty)',
            'slot-1-18-label' => 'Primary Address City',
            'slot-1-18-name' => 'primary_address_city',
            'slot-1-19-label' => 'Primary Address State',
            'slot-1-19-name' => 'primary_address_state',
            'slot-1-2-label' => 'First Name:',
            'slot-1-2-name' => 'first_name',
            'slot-1-20-label' => 'Primary Address Postalcode',
            'slot-1-20-name' => 'primary_address_postalcode',
            'slot-1-21-label' => 'Primary Address Country',
            'slot-1-21-name' => 'primary_address_country',
            'slot-1-22-label' => 'LBL_DATE_ENTERED',
            'slot-1-22-name' => 'date_entered',
            'slot-1-23-label' => 'LBL_DATE_MODIFIED',
            'slot-1-23-name' => 'date_modified',
            'slot-1-3-label' => 'Last Name:',
            'slot-1-3-name' => 'last_name',
            'slot-1-4-label' => 'Office Phone:',
            'slot-1-4-name' => 'phone_work',
            'slot-1-5-label' => 'Mobile:',
            'slot-1-5-name' => 'phone_mobile',
            'slot-1-6-label' => 'Home Phone:',
            'slot-1-6-name' => 'phone_home',
            'slot-1-7-label' => 'Do Not Call:',
            'slot-1-7-name' => 'do_not_call',
            'slot-1-8-label' => 'Email Address:',
            'slot-1-8-name' => 'email1',
            'slot-1-9-label' => 'Other Email:',
            'slot-1-9-name' => 'email2',
            'sync_detail_and_edit    ',
            'to_pdf' => '1',
            'view' => 'EditView',
            'view_module' => 'Leads',
        );
    }

//
//        // We use a derived class to aid in stubbing out test properties and functions
//        $parser = new SearchViewMetaDataParserTestDerivative("basic_search");
//
//        // Creating a mock object for the DeployedMetaDataImplementation
//
//        $impl = $this->getMock('DeployedMetaDataImplementation',
//                               array('getOriginalViewdefs'),
//                               array(),
//                               'DeployedMetaDataImplementation_Mock',
//                               FALSE);
//
//        // Making the getOriginalViewdefs function return the test viewdefs and verify that it is being called once
//        $impl->expects($this->once())
//                ->method('getOriginalViewdefs')
//                ->will($this->returnValue($orgViewDefs));
//
//        // Replace the protected implementation with our Mock object
//        $parser->setImpl($impl);
//
//        // Execute the method under test
//        $result = $parser->getOriginalViewDefs();
//
//        // Verify that the returned result matches our expectations
//        $this->assertEquals($result, $expectedResult);
//
//        //echo "Done";
//    }

//    public function testdoWeHasAParser()
//    {
//        $parser = new PortalGridLayoutMetaDataParser(MB_PORTALEDITVIEW, 'Leads');
//        $this->assertInstanceOf('PortalGridLayoutMetaDataParser',$parser);
//    }

//    public function testWTFDoesThisDo()
//    {
//        $postString = <<<EOL
//PORTAL	1
//action	saveLayout
//module	ModuleBuilder
//panel-1-label	0
//panel-1-name	Default
//panels_as_tabs
//slot-1-0-label	Salutation
//slot-1-0-name	salutation
//slot-1-1-name	(empty)
//slot-1-10-label	Email Opt Out:
//slot-1-10-name	email_opt_out
//slot-1-11-name	(filler)
//slot-1-12-label	Title:
//slot-1-12-name	title
//slot-1-13-label	Department:
//slot-1-13-name	department
//slot-1-14-label	Account Name:
//slot-1-14-name	account_name
//slot-1-15-name	(empty)
//slot-1-16-label	Primary Address Street
//slot-1-16-name	primary_address_street
//slot-1-17-name	(empty)
//slot-1-18-label	Primary Address City
//slot-1-18-name	primary_address_city
//slot-1-19-label	Primary Address State
//slot-1-19-name	primary_address_state
//slot-1-2-label	First Name:
//slot-1-2-name	first_name
//slot-1-20-label	Primary Address Postalcode
//slot-1-20-name	primary_address_postalcode
//slot-1-21-label	Primary Address Country
//slot-1-21-name	primary_address_country
//slot-1-22-label	LBL_DATE_ENTERED
//slot-1-22-name	date_entered
//slot-1-23-label	LBL_DATE_MODIFIED
//slot-1-23-name	date_modified
//slot-1-3-label	Last Name:
//slot-1-3-name	last_name
//slot-1-4-label	Office Phone:
//slot-1-4-name	phone_work
//slot-1-5-label	Mobile:
//slot-1-5-name	phone_mobile
//slot-1-6-label	Home Phone:
//slot-1-6-name	phone_home
//slot-1-7-label	Do Not Call:
//slot-1-7-name	do_not_call
//slot-1-8-label	Email Address:
//slot-1-8-name	email1
//slot-1-9-label	Other Email:
//slot-1-9-name	email2
//sync_detail_and_edit
//to_pdf	1
//view	EditView
//view_module	Leads
//EOL;
//
////        $_POST = $_REQUEST = $this->createRequestFromString($postString);
////        $oldParser = new ParserPortalLayoutView();
////        $oldParser->init('Leads', 'EditView');
//
//print_r($this->_parser->getAvailableFields());
//    echo '-----------------',PHP_EOL;
//print_r($this->_parser->getFieldDefs());
//    echo '-----------------',PHP_EOL;
//print_r($this->_parser->getLayout());
//    echo '-----------------',PHP_EOL;
//print_r($this->_parser->getCalculatedFields());
//    echo '-----------------',PHP_EOL;
//print_r($this->_parser->getMaxColumns());
////        $this->assertEquals($oldParser->getAvailableFields(), $this->_parser->getAvailableFields());
////        $this->assertEquals($oldParser->getFieldDefs(), $this->_parser->getFieldDefs());
////        $this->assertEquals($oldParser->getLayout(), $this->_parser->getLayout());
////        $this->assertEquals($oldParser->getCalculatedFields(), $this->_parser->getCalculatedFields());
////        $this->assertEquals($oldParser->getMaxColumns(), $this->_parser->getMaxColumns());
//    }


    /*
    * data provider for testing converting to and from canonical form
    */
    public function canonicalAndInternalForms() {
        // pull in our arrays
        require 'canonical_panel_test.php';
        require 'internal_panel_test.php';

        // this is php shorthand for returning an array( array($a[0],$b[0]), ...)
        return array_map(null,$canonicals,$internals);

    }

    /**
     * @dataProvider canonicalAndInternalForms
     * @param $input
     * @param $expected
     */
    public function testConvertFromCanonicalForm($input, $expected) {

        $output = $this->_parser->testConvertFromCanonicalForm($input, array());

        $this->assertEquals($expected, $output);
        //print_r($output);

    }

    public function canonicalAndInternalFieldList()
    {
        return array(
            array(
                // canonical panels
                array(array('label' => 'Default', 'fields' => array(
                    array(
                        'name' => 'name',
                        'label' => 'Name',
                    ),
                    array(
                        'name' => 'status',
                        'label' => 'Status',
                    ),
                    array(
                        'name' => 'description',
                        'label' => 'Description',
                    ),
                    ""
                ))),
                // internal fieldlist
                array(
                    'name' => array(
                        'name' => 'name',
                        'label' => 'Name',
                    ),
                    'status' => array(
                        'name' => 'status',
                        'label' => 'Status',
                    ),
                    'description' => array(
                        'name' => 'description',
                        'label' => 'Description',
                    ),
                    "" => null,

                )
            ),
            array(
                // internal panels
                array('Default' => array(
                    array( //row 1
                        array(
                            'name' => 'name',
                            'label' => 'Name',
                            'displayParams' => array('colspan' => 2)
                        ),
                        MBConstants::$EMPTY
                    ),
                    array( //row 2
                        array(
                            'name' => 'status',
                            'label' => 'Status',
                        ),
                        array(
                            'name' => 'description',
                            'label' => 'Description',
                        ),
                    ),
                )),
                // field list
                array(
                    'name' =>  array(
                        'name' => 'name',
                        'label' => 'Name',
                        'displayParams' => array('colspan' => 2)
                    ),
                    '(empty)' => MBConstants::$EMPTY,
                    'status' =>  array(
                        'name' => 'status',
                        'label' => 'Status',
                    ),
                    'description' => array(
                        'name' => 'description',
                        'label' => 'Description',
                    ),

                )
            ),

        );
    }

    /**
     * @dataProvider canonicalAndInternalFieldList
     * @param input
     * @param expected
     */
    public function testGetFieldsFromLayout($input, $expected)
    {
        $output = $this->_parser->testGetFieldsFromLayout(array('panels' => $input));
        $this->assertEquals($expected, $output);
    }

    /**
     * @dataProvider canonicalAndInternalFieldList
     * @param input
     * @param expected
     */
    public function testGetFieldsFromLayoutUsingFullViewdef($input, $expected)
    {
        // put on the additional array path on the input
        $canonical_input['portal']['view']['edit']['panels'] = $input;
        $output = $this->_parser->testGetFieldsFromLayout($canonical_input);
        $this->assertEquals($expected, $output);
    }


    /**
     * @dataProvider canonicalAndInternalForms
     * @param $expected
     * @param $input
     */
    public function testConvertToCanonicalForm($expected, $input)
    {
        // need this to prime our viewdefs
        $this->_parser->testInstallPreviousViewdefs(array('panels' => $expected));
        $output = $this->_parser->testConvertToCanonicalForm($input);
        //print_r($output);
        $this->assertEquals($expected, $output);

    }
}



/**
 * Using derived helper class from PortalGridLayoutMetaDataParser to test canonical/internal
 * format conversions without saving the file.
 *
 * lifted from SearchViewMDPTest
 */
class PortalGridLayoutMetaDataParserTestDerivative extends PortalGridLayoutMetaDataParser
{
    // dummy constructor for now
    public function __construct($view, $moduleName) {
        $view = strtolower ( $view ) ;

        $this->FILLER = array ( 'name' => MBConstants::$FILLER['name'] , 'label' => translate ( MBConstants::$FILLER['label'] ) ) ;

        $this->_moduleName = $moduleName ;
        $this->_view = $view ;

        $module = StudioModuleFactory::getStudioModule( $moduleName ) ;
        $this->module_dir = $module->seed->module_dir;
        $this->_fielddefs = $module->getFields();
        $this->_standardizeFieldLabels( $this->_fielddefs );
    }

    public function testInstallPreviousViewdefs($viewdefs) {
        $this->_originalViewDef = $this->getFieldsFromLayout($viewdefs);
    }

    public function testConvertFromCanonicalForm($panels , $fielddefs) {

       return $this->_convertFromCanonicalForm($panels, $fielddefs);
    }

    public function testConvertToCanonicalForm($panels, $fielddefs=null) {
        if ($fielddefs==null)
        {
            $fielddefs = $this->_fielddefs;
        }

        // spoof our internal viewdefs


        return $this->_convertToCanonicalForm($panels, $fielddefs);
    }

    public function testPopulateFromRequest(&$fielddefs) {
        // ??
    }

    public function testGetFieldsFromLayout($viewdef) {
        return $this->getFieldsFromLayout($viewdef);
    }
}