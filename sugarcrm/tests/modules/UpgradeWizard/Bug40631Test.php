<?php

require_once('modules/UpgradeWizard/uw_utils.php');

class Bug40631Test extends Sugar_PHPUnit_Framework_TestCase {

var $has_original_metadata_custom_directory;	
var $has_original_hoovers_custom_directory;

public function setUp() 
{
	if(is_dir('custom/modules/Connectors/connectors/sources/ext/soap/hoovers'))
	{
	   $this->has_original_hoovers_custom_directory = true;
	   //backup directory
	   mkdir_recursive('custom/modules/Connectors/connectors/sources/ext/soap/hoovers_bak');
	   copy_recursive('custom/modules/Connectors/connectors/sources/ext/soap/hoovers', 'custom/modules/Connectors/connectors/sources/ext/soap/hoovers_bak');
	} else {
	   mkdir_recursive('custom/modules/Connectors/connectors/sources/ext/soap/hoovers');
	}
	
//Now create the test files with the pre 3.3 version
//1) Create hoovers_custom_functions.php
$the_string = <<<EOQ
<?php

/**
 * get_country_value
 * 
 */
function get_country_value(\$bean, \$out_field, \$value) {
	if(file_exists('include/language/en_us.lang.php')) {
	   require('include/language/en_us.lang.php');
	   if(isset(\$app_list_strings['countries_dom'])) {
	   	  \$country = trim(strtoupper(\$value));
	   	  if(isset(\$app_list_strings['countries_dom'][\$country])) {
	   	  	 return \$app_list_strings['countries_dom'][\$country];
	   	  }
	   }
	}
	
    switch(\$country) {
     	case (preg_match('/U[\.]?S[\.]?A[\.]?/', \$country) || \$country == 'UNITED STATES' || \$country == 'AMERICA' || \$country == 'NORTH AMERICA') :
     	    return "USA";
     	case (\$country == "ENGLAND" || \$country == "UK" || \$country == "GREAT BRITAIN" || \$country == "BRITAIN") :
     		return "UNITED KINGDOM";
     	default : 
     		return \$value;
    }
}


/**
 * get_hoovers_finsales
 * 
 * @param \$value decimal number denoting annual sales in millions of dollars
 */
function get_hoovers_finsales(\$bean, \$out_field, \$value) {
	
	\$value = trim(\$value);
	if(empty(\$value) || !is_numeric(\$value) || \$value == '0'){
			return 'Unknown';
	}
	
	\$value = \$value * 1000000;	//Multiply by 1 million	
	\$value = intval(floor(\$value));
	
	switch(\$value) {
		case (\$value < 10000000):
			return 'under 10M';
		case (\$value < 25000000):
			return '10 - 25M';
		case (\$value < 100000000):
			return '25 - 99M';
		case (\$value < 250000000):
			return '100M - 249M';
		case (\$value < 500000000):
			return '250M - 499M';
		case (\$value < 1000000000):
			return '500M - 1B';
		case (\$value >= 1000000000):
			return 'more than 1B';
		default:
			return 'Unknown';
	}
}

function get_hoovers_employees(\$bean, \$out_field, \$value) {
	
	\$value = trim(\$value);
	if(empty(\$value) || !is_numeric(\$value)) {
	   return '';
	}
	
	switch(\$value) {
		case (\$value < 100):
			return 'under 100 employees';
		case (\$value < 400):
			return '100 - 399 employees';
		case (\$value < 1000):
			return '400 - 999 employees';
		default:
			return 'more than 1000 employees';
	}
}

?>
EOQ;

$fp = sugar_fopen('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/hoovers_custom_functions.php', "w" );
fwrite( $fp, $the_string );
fclose( $fp );	

//2) Create listviewdefs.php
$the_string = <<<EOQ
<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

\$listViewDefs['ext_soap_hoovers'] = array(
	'recname' => array(
		'width' => '25', 
		), 
	'locationtype' => array(
		'width' => '15',
		),
	'addrcity' => array(
		'width' => '15', 
		),
	'addrstateprov' => array(
		'width' => '15', 
		),
	'country' => array(
		'width' => '10', 
		),
	'hqphone' => array(
		'width' => '10',
		),
	'finsales' => array(
        'width' => '10',
		),
		
);
?>
EOQ;

$fp = sugar_fopen('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/listviewdefs.php', "w" );
fwrite( $fp, $the_string );
fclose( $fp );	

//3) Create mapping.php
$the_string = <<<EOQ
<?php
// created: 2010-11-10 10:12:43
\$mapping = array (
  'beans' => 
  array (
    'Touchpoints' => 
    array (
      'companyname' => 'company_name',
      'city' => 'primary_address_city',
      'address1' => 'primary_address_street',
      'address2' => 'primary_address_street_2',
      'stateorprovince' => 'primary_address_state',
      'country' => 'primary_address_country',
      'addrzip' => 'primary_address_postalcode',
      'sales' => 'annual_revenue',
      'employees' => 'employees',
      'id' => 'id',
    ),
    'Accounts' => 
    array (
      'companyname' => 'name',
      'address1' => 'billing_address_street',
      'address2' => 'billing_address_street_2',
      'city' => 'billing_address_city',
      'stateorprovince' => 'billing_address_state',
      'country' => 'billing_address_country',
      'addrzip' => 'billing_address_postalcode',
      'sales' => 'annual_revenue',
      'employees' => 'employees',
      'hqphone' => 'phone_office',
      'description' => 'description',
      'id' => 'id',
    ),
    'Contacts' => 
    array (
      'address1' => 'primary_address_street',
      'address2' => 'primary_address_street_2',
      'city' => 'primary_address_city',
      'stateorprovince' => 'primary_address_state',
      'country' => 'primary_address_country',
      'addrzip' => 'primary_address_postalcode',
      'hqphone' => 'phone_work',
      'id' => 'id',
    ),
  ),
);
?>

EOQ;

$fp = sugar_fopen('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/mapping.php', "w" );
fwrite( $fp, $the_string );
fclose( $fp );	

//4) Create vardefs.php
$the_string = <<<EOQ
<?php

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

\$dictionary['ext_soap_hoovers'] = array(

  'comment' => 'vardefs for hoovers connector',
  'fields' => array (
    'id' =>
	  array (
	    'name' => 'id',
	    'input' => 'uniqueId',
	    'vname' => 'LBL_ID',
	    'type' => 'id',
	    'hidden' => true,
	    'comment' => 'Unique identifier for records'
	),
    'companyname'=> array(
	    'name' => 'companyname',
		'input' => 'bal.specialtyCriteria.companyKeyword',
		'output' => 'companyname',
	    'vname' => 'LBL_NAME',
	    'type' => 'varchar',
	    'search' => true,
	    'comment' => 'The name of the company',
    ),
   'duns' => array (
	    'name' => 'duns',
    	'input' => 'bal.specialtyCriteria.duns',
		'output' => 'duns',
	    'vname' => 'LBL_DUNS',
	    'type' => 'varchar',
    	'hidden' => true,
	    'search' => true,
	    'comment' => 'The duns id used by Hoovers',
    ),
   'parent_duns' => array (
	    'name' => 'parent_duns',
		'output' => 'parent_duns',
	    'vname' => 'LBL_PARENT_DUNS',
	    'type' => 'varchar',
	    'comment' => 'The parent duns id used by Hoovers',
	    'search' => true,
    	'required' => true,
    	'hidden' => true,
    ),
   'city' => array (
	    'name' => 'city',
   		'input' => 'bal.location.city',
   		'output' => 'city',
	    'vname' => 'LBL_CITY',
	    'type' => 'varchar',
	    'search' => true,
	    'comment' => 'The city address for the company', 
   ),
   'address1' => array(
        'name' => 'address1',
        'search' => false,
        'vname' => 'LBL_ADDRESS_STREET1',
        'type' => 'varchar',
        'comment' => 'street address',
   ),
   'address2' => array(
        'name' => 'address2',
        'search' => false,
        'vname' => 'LBL_ADDRESS_STREET2',
        'type' => 'varchar',
        'comment' => 'street address (continued)',   
   ),
   'stateorprovince' => array(
        'name' => 'stateorprovince',
   		'input' => 'bal.location.state', //\$args['bal']['location']['state'] = 'California'
   		'output' => 'stateorprovince',
        'vname' => 'LBL_STATE',
        'type' => 'varchar',
        'search' => true,
        'options' => 'addrstateprov_dom',
        'comment' => 'The state address for the company',
   ),
   'country' => array(
        'name' => 'country',
        'input' => 'bal.location.country',
        'vname' => 'LBL_COUNTRY',
        'type' => 'varchar',
        'search' => true,
        'comment' => 'The country address for the company',
        'function' => array('name'=>'get_country_value', 'include'=>'custom/modules/Connectors/connectors/sources/ext/soap/hoovers/hoovers_custom_functions.php'),
   ),
   'addrzip' => array(
        'name' => 'addrzip',
   		'input' => 'bal.location.zip',
   		'output' => 'addrzip',
        'vname' => 'LBL_ZIP',
        'type' => 'varchar',
        'search' => true,
        'comment' => 'The postal code address for the company',
   ),
   'sales' => array(
        'name' => 'sales',
        'vname' => 'LBL_SALES',
        'type' => 'enum',
        'comment' => 'Annual sales (in millions)',
        'function' => array('name'=>'get_hoovers_finsales', 'include'=>'custom/modules/Connectors/connectors/sources/ext/soap/hoovers/hoovers_custom_functions.php'),
   ),
   'locationtype' => array(
        'name' => 'locationtype',
        'vname' => 'LBL_LOCATION_TYPE',
        'type' => 'varchar',
        'comment' => 'Location type such as headquarters or branch',   
   ),
   'companytype' => array(
        'name' => 'companytype',
        'vname' => 'LBL_COMPANY_TYPE',
        'type' => 'varchar',
        'comment' => 'Company type (public, private, etc.)',
   ),
   'hqphone' => array(
        'name' => 'hqphone',
        'vname' => 'LBL_HQPHONE',
        'type' => 'varchar',
        'comment' => 'Headquarters phone number',    
   ),
   'employees' => array(
        'name' => 'employees',
        'vname' => 'LBL_TOTAL_EMPLOYEES',
        'type' => 'decimal',
        'comment' => 'Total number of employees',
        'function' => array('name'=>'get_hoovers_employees', 'include'=>'custom/modules/Connectors/connectors/sources/ext/soap/hoovers/hoovers_custom_functions.php'),    
   ),
   )
);
?>
EOQ;

$fp = sugar_fopen('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/vardefs.php', "w" );
fwrite( $fp, $the_string );
fclose( $fp );	

//Create config.php
//5) Create config.php
$the_string = <<<EOQ
<?php
\$config['name'] = 'Hoovers&#169;';
\$config['order'] = 2;
\$config['properties']['hoovers_endpoint'] = 'http://hapi.hoovers.com/HooversAPI-33';
\$config['properties']['hoovers_wsdl'] = 'http://hapi.hoovers.com/HooversAPI-33/hooversAPI/hooversAPI.wsdl';
\$config['properties']['hoovers_api_key'] = 'abc';
?>
EOQ;

$fp = sugar_fopen('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/config.php', "w" );
fwrite( $fp, $the_string );
fclose( $fp );	


if(is_dir('custom/modules/Connectors/metadata'))
{
   $this->has_original_metadata_custom_directory = true;
   //backup directory
   mkdir_recursive('custom/modules/Connectors/metadata_bak');
   copy_recursive('custom/modules/Connectors/metadata', 'custom/modules/Connectors/metadata_bak');
} else {
   mkdir_recursive('custom/modules/Connectors/metadata');
}	


//1) Create mergeviewdefs.php
$the_string = <<<EOQ
<?php
\$viewdefs = array(
  'Connector'=> array('MergeView'=> 
     array('Touchpoints'=>
        array(
              'company_name',
              'primary_address_street',
              'primary_address_city',
              'primary_address_state',
              'primary_address_country',
              'primary_address_postalcode',
              'annual_revenue',
              'employees',
              'description',
        ),
     ),     
  ),
);

EOQ;

$fp = sugar_fopen('custom/modules/Connectors/metadata/mergeviewdefs.php', "w" );
fwrite( $fp, $the_string );
fclose( $fp );	

//2) Create searchdefs.php
$the_string = <<<EOQ
<?php
\$searchdefs = array (
  'ext_rest_zoominfocompany' => 
  array (
    'Touchpoints' => 
    array (
      0 => 'companyname',
      1 => 'countrycode',
      2 => 'zip',
    ),
  ),
  'ext_soap_jigsaw' => 
  array (
    'Touchpoints' => 
    array (
      0 => 'name',
    ),
  ),
  'ext_soap_hoovers' => 
  array (
    'Touchpoints' => 
    array (
      0 => 'companyname',
      1 => 'addrstateprov',
      2 => 'addrcountry',
    ),
    'Accounts' => 
    array (
      0 => 'companyname',
    ),
    'Opportunities' => 
    array (
      0 => 'companyname',
    ),
    'Contacts' => 
    array (
      0 => 'companyname',
    ),
  ),
  'ext_rest_linkedin' => 
  array (
    'Accounts' => 
    array (
    ),
    'Opportunities' => 
    array (
    ),
  ),
);
?>

EOQ;

$fp = sugar_fopen('custom/modules/Connectors/metadata/searchdefs.php', "w" );
fwrite( $fp, $the_string );
fclose( $fp );	

//3) Create connectors.php
$the_string = <<<EOQ

<?php
// created: 2010-10-01 11:49:49
\$connectors = array (
  'ext_rest_linkedin' => 
  array (
    'id' => 'ext_rest_linkedin',
    'name' => 'LinkedIn&#169;',
    'enabled' => true,
    'directory' => 'modules/Connectors/connectors/sources/ext/rest/linkedin',
    'modules' => 
    array (
      0 => 'Accounts',
      1 => 'Opportunities',
    ),
  ),
  'ext_soap_hoovers' => 
  array (
    'id' => 'ext_soap_hoovers',
    'name' => 'Hoovers&#169;',
    'enabled' => true,
    'directory' => 'custom/modules/Connectors/connectors/sources/ext/soap/hoovers',
    'modules' => 
    array (
      0 => 'Touchpoints',
      1 => 'Accounts',
      2 => 'Opportunities',
      3 => 'Contacts',
    ),
  ),
  'ext_rest_zoominfocompany' => 
  array (
    'id' => 'ext_rest_zoominfocompany',
    'name' => 'Zoominfo&#169; - Company',
    'enabled' => true,
    'directory' => 'custom/modules/Connectors/connectors/sources/ext/rest/zoominfocompany',
    'modules' => 
    array (
      0 => 'Touchpoints',
    ),
  ),
  'ext_rest_zoominfoperson' => 
  array (
    'id' => 'ext_rest_zoominfoperson',
    'name' => 'Zoominfo&#169; - Person',
    'enabled' => true,
    'directory' => 'custom/modules/Connectors/connectors/sources/ext/rest/zoominfoperson',
    'modules' => 
    array (
    ),
  ),
  'ext_rest_crunchbase' => 
  array (
    'id' => 'ext_rest_crunchbase',
    'name' => 'Crunchbase&#169;',
    'enabled' => true,
    'directory' => 'modules/Connectors/connectors/sources/ext/rest/crunchbase',
    'modules' => 
    array (
    ),
  ),
  'ext_soap_jigsaw' => 
  array (
    'id' => 'ext_soap_jigsaw',
    'name' => 'Jigsaw&#169;',
    'enabled' => true,
    'directory' => 'custom/modules/Connectors/connectors/sources/ext/soap/jigsaw',
    'modules' => 
    array (
      0 => 'Touchpoints',
    ),
  ),
);
?>
-ba

EOQ;

$fp = sugar_fopen('custom/modules/Connectors/metadata/connectors.php', "w" );
fwrite( $fp, $the_string );
fclose( $fp );	

//4) display_config.php
$the_string = <<<EOQ
<?php
// created: 2010-07-19 12:56:38
\$modules_sources = array (
  'Touchpoints' => 
  array (
    'ext_rest_zoominfocompany' => 'ext_rest_zoominfocompany',
    'ext_soap_jigsaw' => 'ext_soap_jigsaw',
    'ext_soap_hoovers' => 'ext_soap_hoovers',
  ),
  'Accounts' => 
  array (
    'ext_rest_linkedin' => 'ext_rest_linkedin',
    'ext_soap_hoovers' => 'ext_soap_hoovers',
  ),
  'Opportunities' => 
  array (
    'ext_rest_linkedin' => 'ext_rest_linkedin',
    'ext_soap_hoovers' => 'ext_soap_hoovers',
  ),
  'Contacts' => 
  array (
    'ext_soap_hoovers' => 'ext_soap_hoovers',
  ),
);
?>

EOQ;

$fp = sugar_fopen('custom/modules/Connectors/metadata/display_config.php', "w" );
fwrite( $fp, $the_string );
fclose( $fp );	

}

public function tearDown() 
{
	if($this->has_original_hoovers_custom_directory)
	{
	   //Remove custom directory
	   rmdir_recursive('custom/modules/Connectors/connectors/sources/ext/soap/hoovers');
	   //Re-create custom directory
	   mkdir_recursive('custom/modules/Connectors/connectors/sources/ext/soap/hoovers');
	   //Copy original contents back in
	   copy_recursive('custom/modules/Connectors/connectors/sources/ext/soap/hoovers_bak', 'custom/modules/Connectors/connectors/sources/ext/soap/hoovers');
	   //Remove the backup directory
	   rmdir_recursive('custom/modules/Connectors/connectors/sources/ext/soap/hoovers_bak');
	} else {
	   //Remove the custom directory
	   rmdir_recursive('custom/modules/Connectors/connectors/sources/ext/soap/hoovers');
	}
	
	if($this->has_original_metadata_custom_directory)
	{
	   //Remove custom directory
	   rmdir_recursive('custom/modules/Connectors/metadata');
	   //Re-create custom directory
	   mkdir_recursive('custom/modules/Connectors/metadata');
	   //Copy original contents back in
	   copy_recursive('custom/modules/Connectors/metadata_bak', 'custom/modules/Connectors/metadata');
	   //Remove the backup directory
	   rmdir_recursive('custom/modules/Connectors/metadata_bak');
	} else {
	   //Remove the custom directory
	   rmdir_recursive('custom/modules/Connectors/metadata');
	}	
}


public function testHooversCustomizationUpgrade()
{
    $this->assertTrue(file_exists('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/hoovers_custom_functions.php'));
    $this->assertTrue(file_exists('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/listviewdefs.php'));
    $this->assertTrue(file_exists('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/mapping.php'));
    $this->assertTrue(file_exists('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/vardefs.php'));
    $this->assertTrue(file_exists('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/config.php'));    
    $this->assertTrue(file_exists('custom/modules/Connectors/metadata/mergeviewdefs.php'));
    $this->assertTrue(file_exists('custom/modules/Connectors/metadata/searchdefs.php'));    
    $this->assertTrue(file_exists('custom/modules/Connectors/metadata/display_config.php'));
    $this->assertTrue(file_exists('custom/modules/Connectors/metadata/connectors.php'));  

    //Alright now let's call the code...
}

}

?>