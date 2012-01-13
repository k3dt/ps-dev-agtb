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
 
require_once('include/nusoap/nusoap.php');
require_once('modules/Cases/Case.php');
require_once('modules/Accounts/Account.php');


/**
 * @group bug39234
 */
class Bug39855Test extends Sugar_PHPUnit_Framework_TestCase
{
	public $_user = null;
	public $_case1 = null;
	public $_case2 = null;
	public $_acc = null;
	public $_soapClient = null;
	public $_session = null;
	public $_sessionId = '';
    /**
     * Create test user
     *
     */
	public function setUp() 
    {
    	
        $this->markTestIncomplete('Skipping for now while investigating');    	
        //setup test portal user
    	$this->_setupTestUser();
    	$this->_soapClient = new nusoapclient($GLOBALS['sugar_config']['site_url'].'/soap.php',false,false,false,false,false,600,600);
    	$this->_login();
    	
    	//setup test account
		$account = new Account();
        $account->name = 'test account for bug 39855';
        $account->assigned_user_id = 'SugarUser';
        $account->save();
        $this->_acc = $account;
    	
    	//setup test cases
		$case1 = new aCase();
        $case1->name = 'test case for bug 39855 ASDF';
        $case1->account_id = $this->_acc->id;
        $case1->status = 'New';
        $case1->save();
        $this->_case1 = $case1;

        $case2 = new aCase();
		//$account->id = 'a_'.$unid;
        $case2->name = 'test case for bug 39855 QWER';
        $case2->account_id = $this->_acc->id;
        $case2->status = 'Rejected';
        $case2->save();
        $this->_case2 = $case2;
        
        
    }

    /**
     * Remove anything that was used during this test
     *
     */
    public function tearDown() {
    	global $soap_version_test_accountId, $soap_version_test_opportunityId, $soap_version_test_contactId;
        $this->_tearDownTestUser();
        $this->_user = null;
        $this->_sessionId = '';
        $GLOBALS['db']->query("DELETE FROM cases WHERE name like 'test case for bug 39855%'");
        $GLOBALS['db']->query("DELETE FROM accounts WHERE name like 'test account for bug 39855%'");
        
        unset($this->_case1);
        unset($this->_case2);
        unset($this->_acc1);
        
    }	
    
    public function testGetEntry() {
    	//test retrieving a case by id
    	$result =  $this->_soapClient->call('portal_get_entry',array('session'=>$this->_sessionId,'module_name'=>'Cases','id'=>$this->_case1->id ,'select_field'=>array('case_number','status', 'name','description')));
		$this->assertTrue($result['entry_list'][0]['id'] == $this->_case1->id,'portal_get_entry was not able to retrieve a case record by id');

    	$result =  $this->_soapClient->call('portal_logout',array('session' => $this->_sessionId));
    }
    
     public function testGetEntryList() {
    	$w = " name LIKE 'test case for bug 39855 %' ";
    	
    	$result =  $this->_soapClient->call('portal_get_entry_list',array('session'=>$this->_sessionId,'module_name'=>'Cases','where'=>$w ,'', 'select_field'=>array('case_number','status', 'name','description')));
		$this->assertTrue($result['result_count'] > 1,'portal_get_entry_list was not able to retrieve both cases using the following where clause: '.$w);

    } 
    
	/**********************************
     * HELPER PUBLIC FUNCTIONS
     **********************************/
    
    /**
     * Attempt to login to the soap server
     *
     * @return $set_entry_result - this should contain an id and error.  The id corresponds
     * to the session_id.
     */
    public function _login(){
		global $current_user;  	
    	$result = $this->_soapClient->call('portal_login',
            array('user_auth' => 
                array('user_name' => $this->_user->user_name,
                    'password' => $this->_user->user_hash, 
                    'version' => '.01'), 
                	'user_name' =>'portal',
                'application_name' => 'SoapTestPortal')
            );
        $this->_sessionId = $result['id'];
		return $result;
		
    }
    
 /**
     * Create a test portal user
     *
     */
	public function _setupTestUser() {
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $this->_user->status = 'Active';
        $this->_user->portal_only = 1;
        $this->_user->save();
    }
    

        
    /**
     * Remove user created for test
     *
     */
	public function _tearDownTestUser() {
       SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }
	
}
?>