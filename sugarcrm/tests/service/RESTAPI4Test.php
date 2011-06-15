<?php
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
 
require_once('service/v3/SugarWebServiceUtilv3.php');
require_once('tests/service/APIv3Helper.php');


class RESTAPI4Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_user;
    protected $_admin_user;
    protected $_lastRawResponse;

    private static $helperObject;
    
    protected $aclRole;
    protected $aclField;
    
    public function setUp()
    {
        $beanList = array();
		$beanFiles = array();
		require('include/modules.php');
		$GLOBALS['beanList'] = $beanList;
		$GLOBALS['beanFiles'] = $beanFiles;
		
        //Reload langauge strings
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'Accounts');
        //Create an anonymous user for login purposes/
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        
        $this->_admin_user = SugarTestUserUtilities::createAnonymousUser();
        $this->_admin_user->status = 'Active';
        $this->_admin_user->is_admin = 1;
        $this->_admin_user->save();
        $GLOBALS['current_user'] = $this->_user;

        self::$helperObject = new APIv3Helper();
        
        //Disable access to the website field.
        $this->aclRole = new ACLRole();
        $this->aclRole->name = "Unit Test";
        $this->aclRole->save();
        $this->aclRole->set_relationship('acl_roles_users', array('role_id'=>$this->aclRole->id ,'user_id'=> $this->_user->id), false);
        //BEGIN SUGARCRM flav=pro ONLY
        $this->aclField = new ACLField();
        $this->aclField->setAccessControl('Accounts', $this->aclRole->id, 'website', -99);
        $this->aclField->loadUserFields('Accounts', 'Account', $this->_user->id, true );
        //END SUGARCRM flav=pro ONLY
    }

    public function tearDown()
	{
	    //BEGIN SUGARCRM flav=pro ONLY
	    $GLOBALS['db']->query("DELETE FROM acl_fields WHERE role_id IN ( SELECT id FROM acl_roles WHERE id IN ( SELECT role_id FROM acl_user_roles WHERE user_id = '{$GLOBALS['current_user']->id}' ) )");
	    //END SUGARCRM flav=pro ONLY
	    $GLOBALS['db']->query("DELETE FROM acl_roles WHERE id IN ( SELECT role_id FROM acl_user_roles WHERE user_id = '{$GLOBALS['current_user']->id}' )");
	    $GLOBALS['db']->query("DELETE FROM acl_user_roles WHERE user_id = '{$GLOBALS['current_user']->id}'");
	    
	    if(isset($GLOBALS['listViewDefs'])) unset($GLOBALS['listViewDefs']);
	    if(isset($GLOBALS['viewdefs'])) unset($GLOBALS['viewdefs']);
	    unset($GLOBALS['beanList']);
		unset($GLOBALS['beanFiles']);
		unset($GLOBALS['app_list_strings']);
	    unset($GLOBALS['app_strings']);
	    unset($GLOBALS['mod_strings']);
	    unset($GLOBALS['current_user']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
	}

    protected function _makeRESTCall($method,$parameters)
    {
        // specify the REST web service to interact with
        $url = $GLOBALS['sugar_config']['site_url'].'/service/v4/rest.php';
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

    protected function _login($user = null)
    {
        if($user == null)
            $user = $this->_user;
        return $this->_makeRESTCall('login',
            array(
                'user_auth' =>
                    array(
                        'user_name' => $user->user_name,
                        'password' => $user->user_hash,
                        'version' => '.01',
                        ),
                'application_name' => 'mobile',
                'name_value_list' => array(),
                )
            );
    }
    //BEGIN SUGARCRM flav=pro ONLY
    /**
     * Test the login function to ensure it returns the available quotes layouts when application name
     * is mobile.
     *
     */
    public function testLoginForMobileWithQuotes()
    {
        $results = $this->_login($this->_admin_user);
        $this->assertTrue(isset($results['name_value_list']['avail_quotes_layouts']['Standard']) );
        $this->assertTrue(isset($results['name_value_list']['avail_quotes_layouts']['Invoice']) );
    }
    
    /**
     * Test the get_entry_list call with Export access disabled to ensure results are returned.
     *
     */
    public function testGetEntryListWithExportRole()
    {
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        
        //Set the Export Role to no access for user.
        $aclRole = new ACLRole();
        $aclRole->name = "Unit Test Export";
        $aclRole->save();
        $aclRole->set_relationship('acl_roles_users', array('role_id'=> $aclRole->id ,'user_id'=> $this->_user->id), false);
        $role_actions = $aclRole->getRoleActions($aclRole->id);
        $action_id = $role_actions['Accounts']['module']['export']['id'];
        $aclRole->setAction($aclRole->id, $action_id, -99);
        
        $result = $this->_login($this->_user);
        $session = $result['id'];

        $module = 'Accounts';
        $orderBy = 'name';
        $offset = 0;
        $returnFields = array('name');
        $linkNameFields = "";
        $maxResults = 2;
        $deleted = FALSE;
        $favorites = FALSE;
        $result = $this->_makeRESTCall('get_entry_list', array($session, $module, '', $orderBy,$offset, $returnFields,$linkNameFields, $maxResults, $deleted, $favorites));
        
        $this->assertFalse(isset($result['name']));
        if ( isset($result['name']) ) {
            $this->assertNotEquals('Access Denied',$result['name']);
        }
    }
    
    /**
     * Test the ability to retrieve quote PDFs
     *
     */
    public function testGetQuotesPDF()
    {
        $log_result = $this->_login($this->_admin_user);
        $session = $log_result['id'];
        
        //Retrieve a list of quote ids to work with
        $whereClause = "";
        $module = 'Quotes';
        $orderBy = 'name';
        $offset = 0;
        $returnFields = array('id');
        $linkNameFields = "";
        $maxResults = 2;
        $deleted = FALSE;
        $favorites = FALSE;
        $list_result = $this->_makeRESTCall('get_entry_list', array($session, $module, $whereClause, $orderBy,$offset, $returnFields,$linkNameFields, $maxResults, $deleted, $favorites));
        
        //Test for standard oob layouts
        foreach ($list_result['entry_list'] as $entry)
        {
            $quote_id = $entry['id'];
            $result = $this->_makeRESTCall('get_quotes_pdf', array($session, $quote_id, 'Standard' ));
            $this->assertTrue(!empty($result['file_contents']));
        }
        
        //Test for a fake pdf type.
        if( count($list_result['entry_list']) > 0 )
        {
            $quote_id = $list_result['entry_list'][0]['id'];
            $result = $this->_makeRESTCall('get_quotes_pdf', array($session, $quote_id, 'Fake' ));
            $this->assertTrue(!empty($result['file_contents']));
        }   
        
        //Test for a fake bean.
        $result = $this->_makeRESTCall('get_quotes_pdf', array($session, '-1', 'Standard' ));
        $this->assertTrue(!empty($result['file_contents']));     
    }
    //END SUGARCRM flav=pro ONLY
    /**
     * Ensure the ability to retrieve a module list of recrods that are favorites.
     *
     */
    public function testGetModuleFavoriteList()
    {
        $result = $this->_login($this->_admin_user);
        $session = $result['id'];

        $account = new Account();
        $account->id = uniqid();
        $account->new_with_id = TRUE;
        $account->name = "Test " . $account->id;
        $account->save();

        $this->_markBeanAsFavorite($session, "Accounts", $account->id);
        
        $whereClause = "accounts.name='{$account->name}'";
        $module = 'Accounts';
        $orderBy = 'name';
        $offset = 0;
        $returnFields = array('name');
        $linkNameFields = "";
        $maxResults = 50;
        $deleted = FALSE;
        $favorites = TRUE;
        $result = $this->_makeRESTCall('get_entry_list', array($session, $module, $whereClause, $orderBy,$offset, $returnFields,$linkNameFields, $maxResults, $deleted, $favorites));

        $this->assertEquals($account->id, $result['entry_list'][0]['id'],'Unable to retrieve account favorite list.');

        $GLOBALS['db']->query("DELETE FROM accounts WHERE id = '{$account->id}'");
        $GLOBALS['db']->query("DELETE FROM sugarfavorites WHERE record_id = '{$account->id}'");
    }
    
    /**
     * Test set entries call with name value list format key=>value.
     *
     */
    public function testSetEntriesCall()
    {
        $result = $this->_login();
        $session = $result['id'];
        $module = 'Contacts';
        $c1_uuid = uniqid();
        $c2_uuid = uniqid();
        $contacts = array( 
            array('first_name' => 'Unit Test', 'last_name' => $c1_uuid), 
            array('first_name' => 'Unit Test', 'last_name' => $c2_uuid)
        );
        $results = $this->_makeRESTCall('set_entries',
        array(
            'session' => $session,
            'module' => $module,
            'name_value_lists' => $contacts,
        ));
        $this->assertTrue(isset($results['ids']) && count($results['ids']) == 2);
        
        $actual_results = $this->_makeRESTCall('get_entries',
        array(
            'session' => $session,
            'module' => $module,
            'ids' => $results['ids'],
            'select_fields' => array('first_name','last_name')
        ));
        
        $this->assertTrue(isset($actual_results['entry_list']) && count($actual_results['entry_list']) == 2);
        $this->assertEquals($actual_results['entry_list'][0]['name_value_list']['last_name']['value'], $c1_uuid);
        $this->assertEquals($actual_results['entry_list'][1]['name_value_list']['last_name']['value'], $c2_uuid);
    }
    
    
    /**
     * Test search by module with favorites flag enabled.
     *
     */
    public function testSearchByModuleWithFavorites()
    {
        $result = $this->_login($this->_admin_user);
        $session = $result['id'];

        $account = new Account();
        $account->id = uniqid();
        $account->assigned_user_id = $this->_user->id;
        $account->team_id = 1;
        $account->new_with_id = TRUE;
        $account->name = "Unit Test Fav " . $account->id;
        $account->save();
        $this->_markBeanAsFavorite($session, "Accounts", $account->id);
        
        //Negative test.
        $account2 = new Account();
        $account2->id = uniqid();
        $account2->new_with_id = TRUE;
        $account2->name = "Unit Test Fav " . $account->id;
        $account->assigned_user_id = $this->_user->id;
        $account2->save();
        
        $searchModules = array('Accounts');
        $searchString = "Unit Test Fav ";
        $offSet = 0;
        $maxResults = 10;

        $results = $this->_makeRESTCall('search_by_module',
                        array(
                            'session' => $session,
                            'search_string'  => $searchString,
                            'modules' => $searchModules,
                            'offset'  => $offSet,
                            'max_results'     => $maxResults,
                            'assigned_user_id'    => $this->_user->id,
                            'select_fields' => array(),
                            'unified_search_only' => true,
                            'favorites' => true,                            
                            )
                        );
        
        $GLOBALS['db']->query("DELETE FROM accounts WHERE name like 'Unit Test %' ");
        $GLOBALS['db']->query("DELETE FROM sugarfavorites WHERE record_id = '{$account->id}'");
        $GLOBALS['db']->query("DELETE FROM sugarfavorites WHERE record_id = '{$account2->id}'");
        
        $this->assertTrue( self::$helperObject->findBeanIdFromEntryList($results['entry_list'],$account->id,'Accounts'), "Unable to find {$account->id} id in favorites search.");
        $this->assertFalse( self::$helperObject->findBeanIdFromEntryList($results['entry_list'],$account2->id,'Accounts'), "Account {$account2->id} id in favorites search should not be there.");
    }    
    //BEGIN SUGARCRM flav=pro ONLY
    public function _aclEditViewFieldProvider()
    {
        return array(       

            array('Accounts','wireless','edit', array( 'name'=> 99, 'website'=> -99, 'phone_office'=> 99, 'email1'=> 99, 'nofield'=> null ) ), 
            array('Contacts','wireless','edit', array('first_name'=> 99, 'last_name'=> 99 ) ),            
            array('Reports','wireless','edit', array('name'=> 99)),
            
            array('Accounts','wireless','detail', array('name'=>99, 'website'=> -99, 'phone_office'=> 99, 'email1'=> 99, 'nofield'=> null )),            
            array('Contacts','wireless','detail', array('first_name'=> 99, 'last_name'=> 99 )),
            array('Reports','wireless','detail', array('name'=> 99)),


            );
    }
    
    /**
     * @dataProvider _aclEditViewFieldProvider
     */
    public function testMetadataEditViewFieldLevelACLS($module, $view_type, $view, $expected_fields)
    {
        $result = $this->_login();
        $session = $result['id'];

        $results = $this->_makeRESTCall('get_module_layout',
        array(
            'session' => $session,
            'module' => array($module),
            'type' => array($view_type),
            'view' => array($view))
        );

        if($view == 'list')
            $fields = $results[$module][$view_type][$view];
        else
            $fields = $results[$module][$view_type][$view]['panels'];
            
        foreach ($fields as $field_row)
        {
            foreach ($field_row as $field_def)
            {
                if( isset($expected_fields[$field_def['name']]) )
                {
                    $this->assertEquals($expected_fields[$field_def['name']], $field_def['acl'] );
                    break;
                }
            }
        }
    }
    
    public function _aclListViewFieldProvider()
    {
        return array(       
            array('Accounts','wireless', array('name' => 99,  'website' => -99, 'phone_office' => 99, 'email1' => 99 )),
            array('Contacts','wireless', array('name' => 99,  'title' => 99 )),
            array('Reports','wireless', array('name' => 99 ) )
            
            );
    }
    
    /**
     * @dataProvider _aclListViewFieldProvider
     */
    public function testMetadataListViewFieldLevelACLS($module, $view_type, $expected_fields)
    {
        $result = $this->_login();
        $session = $result['id'];
        $results = $this->_makeRESTCall('get_module_layout',
        array(
            'session' => $session,
            'module' => array($module),
            'type' => array($view_type),
            'view' => array('list') )
        );

        $fields = $results[$module][$view_type]['list'];
  
        foreach ($fields as $field_row)
        {
            $tmpName = strtolower($field_row['name']);
            if( isset($expected_fields[$tmpName]) )
            {
                $this->assertEquals($expected_fields[$tmpName], $field_row['acl'] );
            }
        }
    }
    //END SUGARCRM flav=pro ONLY
    /**
     * Private helper function to mark a bean as a favorite item.
     *
     * @param string $session
     * @param string $moduleName
     * @param string $recordID
     */
    private function _markBeanAsFavorite($session, $moduleName, $recordID)
    {
        $result = $this->_makeRESTCall('set_entry',
            array(
                'session' => $session,
                'module' => 'SugarFavorites',
                'name_value_list' => array(
                    array('name' => 'record_id', 'value' => $recordID),
                    array('name' => 'module', 'value' => $moduleName),
                    ),
                )
            );
    }


    public function testRelateAccountToTwoContacts()
    {
        $result = $this->_login();
        $this->assertTrue(!empty($result['id']) && $result['id'] != -1,$this->_returnLastRawResponse());
        $session = $result['id'];

        $result = $this->_makeRESTCall('set_entry',
            array(
                'session' => $session,
                'module' => 'Accounts',
                'name_value_list' => array(
                    array('name' => 'name', 'value' => 'New Account'),
                    array('name' => 'description', 'value' => 'This is an account created from a REST web services call'),
                    ),
                )
            );

        $this->assertTrue(!empty($result['id']) && $result['id'] != -1,$this->_returnLastRawResponse());

        $accountId = $result['id'];

        $result = $this->_makeRESTCall('set_entry',
            array(
                'session' => $session,
                'module' => 'Contacts',
                'name_value_list' => array(
                    array('name' => 'last_name', 'value' => 'New Contact 1'),
                    array('name' => 'description', 'value' => 'This is a contact created from a REST web services call'),
                    ),
                )
            );

        $this->assertTrue(!empty($result['id']) && $result['id'] != -1,$this->_returnLastRawResponse());

        $contactId1 = $result['id'];

        $result = $this->_makeRESTCall('set_entry',
            array(
                'session' => $session,
                'module' => 'Contacts',
                'name_value_list' => array(
                    array('name' => 'last_name', 'value' => 'New Contact 2'),
                    array('name' => 'description', 'value' => 'This is a contact created from a REST web services call'),
                    ),
                )
            );

        $this->assertTrue(!empty($result['id']) && $result['id'] != -1,$this->_returnLastRawResponse());

        $contactId2 = $result['id'];

        // now relate them together
        $result = $this->_makeRESTCall('set_relationship',
            array(
                'session' => $session,
                'module' => 'Accounts',
                'module_id' => $accountId,
                'link_field_name' => 'contacts',
                'related_ids' => array($contactId1,$contactId2),
                )
            );

        $this->assertEquals($result['created'],1,$this->_returnLastRawResponse());

        // check the relationship
        $result = $this->_makeRESTCall('get_relationships',
            array(
                'session' => $session,
                'module' => 'Accounts',
                'module_id' => $accountId,
                'link_field_name' => 'contacts',
                'related_module_query' => '',
                'related_fields' => array('last_name','description'),
                'related_module_link_name_to_fields_array' => array(),
                'deleted' => false,
                )
            );

        $returnedValues = array();
        $returnedValues[] = $result['entry_list'][0]['name_value_list']['last_name']['value'];
        $returnedValues[] = $result['entry_list'][1]['name_value_list']['last_name']['value'];

        $GLOBALS['db']->query("DELETE FROM accounts WHERE id= '{$accountId}'");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$contactId1}'");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$contactId2}'");
        $GLOBALS['db']->query("DELETE FROM accounts_contacts WHERE account_id= '{$accountId}'");

        $this->assertContains('New Contact 1',$returnedValues,$this->_returnLastRawResponse());
        $this->assertContains('New Contact 2',$returnedValues,$this->_returnLastRawResponse());
    }
}