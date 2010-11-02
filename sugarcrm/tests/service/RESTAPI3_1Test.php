<?php

require_once('service/v3/SugarWebServiceUtilv3.php');
require_once('tests/service/APIv3Helper.php');


class RESTAPI3_1Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_user;
    
    protected $_lastRawResponse;
    
    private static $helperObject;
    
    public function setUp()
    {
        //Reload langauge strings 
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'Accounts');
        //Create an anonymous user for login purposes/
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $this->_user->status = 'Active';
        $this->_user->is_admin = 1;
        $this->_user->save();
        $GLOBALS['current_user'] = $this->_user;
        
        self::$helperObject = new APIv3Helper();
    }
    
    public function tearDown() 
	{
	    if(isset($GLOBALS['listViewDefs'])) unset($GLOBALS['listViewDefs']); 
	    if(isset($GLOBALS['viewdefs'])) unset($GLOBALS['viewdefs']); 
	}
	
    protected function _makeRESTCall($method,$parameters)
    {
        // specify the REST web service to interact with 
        $url = $GLOBALS['sugar_config']['site_url'].'/service/v3_1/rest.php'; 
        // Open a curl session for making the call 
        $curl = curl_init($url); 
        // set URL and other appropriate options
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0 );
        // build the request URL
        $json = json_encode($parameters); 
        $postArgs = "method=$method&input_type=JSON&response_type=JSON&rest_data=$json"; 
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postArgs); 
        // Make the REST call, returning the result 
        $response = curl_exec($curl); 
        // Close the connection 
        curl_close($curl);  
        
        $this->_lastRawResponse = $response;
        
        // Convert the result from JSON format to a PHP array 
        return json_decode($response,true); 
    }
    
    protected function _returnLastRawResponse()
    {
        return "Error in web services call. Response was: {$this->_lastRawResponse}";
    }
    
    protected function _login()
    {
        return $this->_makeRESTCall('login',
            array(
                'user_auth' => 
                    array( 
                        'user_name' => $this->_user->user_name, 
                        'password' => $this->_user->user_hash, 
                        'version' => '.01',
                        ), 
                'application_name' => 'mobile', 
                'name_value_list' => array(),
                )
            ); 
    }
    
    public function testLogin()
    {
        $result = $this->_login();
        $this->assertTrue( isset($result['name_value_list']['available_modules']) );
        $this->assertTrue( isset($result['name_value_list']['vardefs_md5']) );
        $this->assertTrue(!empty($result['id']) && $result['id'] != -1,$this->_returnLastRawResponse());
    }
   
    /**
     * Test the available modules returned from the login call to make sure they are correct.
     *
     */
    public function testLoginAvailableModulesResults()
    {
        $result = $this->_login();
        $this->assertTrue( isset($result['name_value_list']['available_modules']) );
        
        $actualModuleList= $result['name_value_list']['available_modules'];
        $sh = new SugarWebServiceUtilv3();
        $availModules = array_keys($sh->get_user_module_list($this->_user));
        $expectedModuleList = $sh->get_visible_mobile_modules($availModules);
        
        $this->assertEquals(count($actualModuleList), count($expectedModuleList), "Could not get available modules during login" );
    }
    
    public function testGetSingleModuleLanguage()
    {
        $result = $this->_login();
        $session = $result['id'];
        
        $results = $this->_makeRESTCall('get_language_definition',
                        array(
                            'session' => $session,
                            'modules'  => 'Accounts',
                            'md5'   => false,
                        ));
        $this->assertTrue( isset($results['Accounts']['LBL_NAME']) );
    }
    
     public function testGetSingleModuleLanguageMD5()
    {
        $result = $this->_login();
        $session = $result['id'];
        
        $results = $this->_makeRESTCall('get_language_definition',
                        array(
                            'session' => $session,
                            'modules'  => 'Accounts',
                            'md5'   => true,
                        ));

        $this->assertTrue( isset($results['Accounts']) );
        $this->assertTrue( !empty($results['Accounts']) );
    }
    
    public function testGetMultipleModuleLanguage()
    {
        $result = $this->_login();
        $session = $result['id'];
        
        $results = $this->_makeRESTCall('get_language_definition',
                        array(
                            'session' => $session,
                            'modules'  => array('Accounts','Contacts','Leads'),
                            'md5'   => false,
                        ));
        $this->assertTrue( isset($results['Accounts']['LBL_NAME']) );
        $this->assertTrue( isset($results['Contacts']['LBL_NAME']) );
        $this->assertTrue( isset($results['Leads']['LBL_ID']) );
    }
    
    public function testGetMultipleModuleLanguageAndAppStrings()
    {
        $result = $this->_login();
        $session = $result['id'];
        
        $results = $this->_makeRESTCall('get_language_definition',
                        array(
                            'session' => $session,
                            'modules'  => array('Accounts','Contacts','Leads','app_strings','app_list_strings'),
                            'md5'   => false,
                        ));        
                                
        $this->assertTrue( isset($results['app_strings']['LBL_NO_ACTION']) );
        $this->assertTrue( isset($results['app_strings']['LBL_EMAIL_YES']) );
        $this->assertTrue( isset($results['app_list_strings']['account_type_dom']) );
        $this->assertTrue( isset($results['app_list_strings']['moduleList']) );
        $this->assertTrue( isset($results['Contacts']['LBL_NAME']) );
        $this->assertTrue( isset($results['Leads']['LBL_ID']) );
    }
    
    public function testGetQuotesPDFContents()
    {
        $result = $this->_login();
        $session = $result['id'];
        
        $quote = new Quote();
        $quote->name = "Test " . uniqid();
        $quote->save(FALSE);
        
        $results = $this->_makeRESTCall('get_quotes_pdf',
                        array(
                            'session' => $session,
                            'quote_id' => $quote->id,
                            'pdf_format'   => 'Standard',
                        )); 
        
        $this->assertTrue( !empty($results['file_contents']) );          
    }
     /**
     * Test the available modules returned from the login call to make sure they are correct.
     *
     */
    public function testLoginVardefsMD5Results()
    {
        $this->markTestSkipped('Vardef results are still dirty even with reload global set, need to investigate further.');
        
        $GLOBALS['reload_vardefs'] = TRUE;
        global  $beanList, $beanFiles;
        $result = $this->_login();
        $this->assertTrue( isset($result['name_value_list']['vardefs_md5']) );
        
        $a_actualMD5= $result['name_value_list']['vardefs_md5'];
        
        $sh = new SugarWebServiceUtilv3();
        $availModules = array_keys($sh->get_user_module_list($this->_user));
        $expectedModuleList = $sh->get_visible_mobile_modules($availModules);
        $soapHelper = new SugarWebServiceUtilv3();
        foreach ($expectedModuleList as $mod)
        {
            $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], $mod);
            $actualMD5 = $a_actualMD5[$mod];
            
            $class_name = $beanList[$mod];
            require_once($beanFiles[$class_name]);
            $seed = new $class_name();
            $actualVardef = $soapHelper->get_return_module_fields($seed,$mod,'');
            $expectedMD5 = md5(serialize($actualVardef));
            $this->assertEquals($expectedMD5, $actualMD5); 
        }
        $this->assertEquals(count($actualModuleList), count($expectedModuleList), "Could not get available modules during login" );
    }
}
