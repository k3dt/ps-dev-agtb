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

require_once 'include/database/DBManagerFactory.php';
require_once 'modules/Contacts/Contact.php';
require_once 'tests/include/database/TestBean.php';
require_once 'include/SugarQuery/SugarQuery.php';

class SimpleQueryTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var DBManager
     */
    private $_db;
    protected $created = array();

    protected $backupGlobals = FALSE;

    protected $contacts = array();
    protected $accounts = array();

    static public function setupBeforeClass()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    static public function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    public function setUp()
    {
        if(empty($this->_db)){
            $this->_db = DBManagerFactory::getInstance();
        }
    }

    public function tearDown()
    {
        if ( !empty($this->contacts) ) {
            $contactList = array();
            foreach ( $this->contacts as $contact ) {
                $contactList[] = $contact->id;
            }

            $this->_db->query("DELETE FROM contacts WHERE id IN ('".implode("','",$contactList)."')");
            $this->_db->query("DELETE FROM contacts_cstm WHERE id_c IN ('".implode("','",$contactList)."')");
        }
        if ( !empty($this->accounts) ) {
            $accountList = array();
            foreach ( $this->accounts as $account ) {
                $accountList[] = $account->id;
            }

            $this->_db->query("DELETE FROM accounts WHERE id IN ('".implode("','",$accountList)."')");
            $this->_db->query("DELETE FROM accounts_cstm WHERE id_c IN ('".implode("','",$accountList)."')");
        }

    }

    public function testSelect()
    {
    	// create a new contact
    	$contact = BeanFactory::newBean('Contacts');
    	$contact->first_name = 'Test';
    	$contact->last_name = 'McTester';
    	$contact->save();
        $this->contacts[] = $contact;
    	$id = $contact->id;
    	// don't need the contact bean anymore, get rid of it
    	unset($contact);
    	// get the new contact
    	
    	$sq = new SugarQuery();
    	$sq->select(array("first_name","last_name"));
    	$sq->from(BeanFactory::newBean('Contacts'));
    	$sq->where()->equals("id",$id);
        
    	$result = $sq->execute();
    	// only 1 record
    	$result = reset($result);

    	$this->assertEquals($result['first_name'], 'Test', 'The First Name Did Not Match');
    	$this->assertEquals($result['last_name'], 'McTester', 'The Last Name Did Not Match');


    }


    public function testSelectWithJoin()
    {
        // create a new contact
        $contact = BeanFactory::newBean('Contacts');
        $contact->first_name = 'Test';
        $contact->last_name = 'McTester';
        $contact->save();
        $contact_id = $contact->id;


        $account = BeanFactory::newBean('Accounts');
        $account->name = 'Awesome';
        $account->save();
        
        $account->load_relationship('contacts');
        $account->contacts->add($contact->id);

        $this->accounts[] = $account;
        $this->contacts[] = $contact;

        // don't need the contact bean anymore, get rid of it
        unset($contact);
        unset($account);
        // get the new contact



        $sq = new SugarQuery();
        $sq->select(array("first_name","last_name", array("accounts.name", 'aname')));
        $sq->from(BeanFactory::newBean('Contacts'));
        $sq->join('accounts');
        $sq->where()->equals("id",$contact_id);

        $result = $sq->execute();
        // only 1 record
        $result = reset($result);

        $this->assertEquals($result['first_name'], 'Test', 'The First Name Did Not Match');
        $this->assertEquals($result['last_name'], 'McTester', 'The Last Name Did Not Match');
        $this->assertEquals($result['aname'], 'Awesome', 'The Account Name Did Not Match');
    }

    public function testSelectWithJoinToSelf()
    {

        $account = BeanFactory::newBean('Accounts');
        $account->name = 'Awesome';
        $account->save();
        $account_id = $account->id;

        $account2 = BeanFactory::newBean('Accounts');
        $account2->name = 'Awesome 2';
        $account2->save();
        
        $account->load_relationship('members');
        $account->members->add($account2->id);

        $this->accounts[] = $account;
        $this->accounts[] = $account2;

        // don't need the accounts beans anymore, get rid of'em
        unset($account2);
        unset($account);
        


        // lets try a query
        $sq = new SugarQuery();
        $sq->select(array(array("accounts.name", 'aname')));
        $sq->from(BeanFactory::newBean('Accounts'));
        $sq->join('members');
        $sq->where()->equals("id",$account_id);
        
        $result = $sq->execute();
        // only 1 record
        $result = reset($result);

        $this->assertEquals('Awesome', $result['aname'], "Account doesn't match");

    }

}