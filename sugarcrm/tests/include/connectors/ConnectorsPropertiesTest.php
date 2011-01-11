<?php
//FILE SUGARCRM flav=pro ONLY
require_once('include/connectors/utils/ConnectorUtils.php');
require_once('include/connectors/ConnectorsTestUtility.php');

class ConnectorsPropertiesTest extends Sugar_PHPUnit_Framework_TestCase {

    var $original_modules_sources;
	var $original_searchdefs;

	public static function setUpBeforeClass() {
        // this is so that Hoovers connector won't SOAP for the huge lookup file
	    if(!file_exists(HOOVERS_LOOKUP_MAPPING_FILE)) {
	         copy(dirname(__FILE__)."/lookup_mapping_stub", HOOVERS_LOOKUP_MAPPING_FILE);
	         self::$drop_lookup_mapping = true;
	     }
	}

	public static function tearDownAfterClass()
	{
	    if(self::$drop_lookup_mapping) {
	        @unlink(HOOVERS_LOOKUP_MAPPING_FILE);
	    }
	}

	function setUp() {
        if(!file_exists(CONNECTOR_DISPLAY_CONFIG_FILE)) {
    	   ConnectorUtils::getDisplayConfig();
    	}
    	require(CONNECTOR_DISPLAY_CONFIG_FILE);
    	$this->original_modules_sources = $modules_sources;

    	//Remove the current file and rebuild with default
    	unlink(CONNECTOR_DISPLAY_CONFIG_FILE);
    	$this->original_searchdefs = ConnectorUtils::getSearchDefs();

    	if(file_exists('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/config.php')) {
    	   mkdir_recursive('custom/modules/Connectors/backup/connectors/sources/ext/soap/hoovers');
    	   copy_recursive('custom/modules/Connectors/connectors/sources/ext/soap/hoovers', 'custom/modules/Connectors/backup/connectors/sources/ext/soap/hoovers');
    	} else {
    	   mkdir_recursive('custom/modules/Connectors/connectors/sources/ext/soap/hoovers');
    	}
    }

    function tearDown() {
    	write_array_to_file('modules_sources', $this->original_modules_sources, CONNECTOR_DISPLAY_CONFIG_FILE);
        write_array_to_file('searchdefs', $this->original_searchdefs, 'custom/modules/Connectors/metadata/searchdefs.php');
        if(file_exists('custom/modules/Connectors/backup/connectors/sources/ext/soap/hoovers')) {
    	   copy_recursive('custom/modules/Connectors/backup/connectors/sources/ext/soap/hoovers', 'custom/modules/Connectors/connectors/sources/ext/soap/hoovers');
           ConnectorsTestUtility::rmdirr('custom/modules/Connectors/backup/connectors/sources/ext/soap/hoovers');
        }

        if(file_exists('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/config.php')) {
           require('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/config.php');
           if(empty($config['properties']['hoovers_api_key'])) {
				$config = array (
				  'name' => 'Hoovers&#169;',
				  'properties' =>
				  array (
				    'hoovers_endpoint' => 'http://hapi.hoovers.com/HooversAPI-33',
    				'hoovers_wsdl' => 'http://hapi.hoovers.com/HooversAPI-33/hooversAPI/hooversAPI.wsdl',
				  ),
				);
				write_array_to_file('config', $config, 'custom/modules/Connectors/connectors/sources/ext/soap/hoovers/config.php');
           }
        }

    }

    function test_get_data_button_without_api_key() {

		$config = array (
		  'name' => 'Hoovers&#169;',
		  'properties' =>
		  array (
		    'hoovers_endpoint' => 'http://hapi.hoovers.com/HooversAPI-33',
   			'hoovers_wsdl' => 'http://hapi.hoovers.com/HooversAPI-33/hooversAPI/hooversAPI.wsdl',
		    'hoovers_api_key' => '',
		  ),
		);

		write_array_to_file('config', $config, "custom/modules/Connectors/connectors/sources/ext/soap/hoovers/config.php");

        require_once('modules/Connectors/controller.php');
    	require_once('include/MVC/Controller/SugarController.php');
    	$controller = new ConnectorsController();
    	$_REQUEST['display_values'] = "ext_soap_hoovers:Leads";
    	$_REQUEST['display_sources'] =  'ext_soap_hoovers,ext_soap_hoovers,ext_rest_linkedin';
    	$_REQUEST['action'] = 'SaveModifyDisplay';
    	$_REQUEST['module'] = 'Connectors';
    	$_REQUEST['from_unit_test'] = true;
    	$controller->action_SaveModifyDisplay();

    	require('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/config.php');
    	require('custom/modules/Leads/metadata/detailviewdefs.php');
    	$hasConnectorButton = false;
    	//_pp($viewdefs['Leads']['DetailView']['templateMeta']['form']['buttons']);
    	foreach($viewdefs['Leads']['DetailView']['templateMeta']['form']['buttons'] as $button) {
    	        if(!is_array($button) && $button == 'CONNECTOR') {
                   $hasConnectorButton = true;
                }
    	}
    	$this->assertTrue($hasConnectorButton);
    }

    function test_get_data_button_with_api_key() {

		$config = array (
		  'name' => 'Hoovers&#169;',
		  'properties' =>
		  array (
   			'hoovers_endpoint' => 'http://hapi.hoovers.com/HooversAPI-33',
    		'hoovers_wsdl' => 'http://hapi.hoovers.com/HooversAPI-33/hooversAPI/hooversAPI.wsdl',
		    'hoovers_api_key' => '',
		  ),
		);

		write_array_to_file('config', $config, "custom/modules/Connectors/connectors/sources/ext/soap/hoovers/config.php");

        require_once('modules/Connectors/controller.php');
    	require_once('include/MVC/Controller/SugarController.php');
    	$controller = new ConnectorsController();
    	$_REQUEST['display_values'] = "ext_soap_hoovers:Leads";
    	$_REQUEST['display_sources'] =  'ext_soap_hoovers,ext_soap_hoovers,ext_rest_linkedin';
    	$_REQUEST['action'] = 'SaveModifyDisplay';
    	$_REQUEST['module'] = 'Connectors';
    	$_REQUEST['from_unit_test'] = true;
    	$controller->action_SaveModifyDisplay();

    	require('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/config.php');
    	require('custom/modules/Leads/metadata/detailviewdefs.php');
    	$hasConnectorButton = false;
    	foreach($viewdefs['Leads']['DetailView']['templateMeta']['form']['buttons'] as $button) {
    	        if(!is_array($button) && $button == 'CONNECTOR') {
                   $hasConnectorButton = true;
                }
    	}
    	$this->assertTrue($hasConnectorButton);
    }

}
?>