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

require_once('tests/rest/RestTestBase.php');
//BEGIN SUGARCRM flav=pro ONLY
require_once('modules/SugarFavorites/SugarFavorites.php');
require_once('include/SugarSearchEngine/SugarSearchEngineAbstractBase.php');
//END SUGARCRM flav=pro ONLY

class RestListTest extends RestTestBase {
    public function setUp()
    {
        parent::setUp();
        $this->accounts = array();
        $this->opps = array();
        $this->contacts = array();
        $this->cases = array();
        $this->bugs = array();
        $this->files = array();
        $this->users = array();
        // set the FTS engine as down and make sure the config removes FTS

        //BEGIN SUGARCRM flav=pro ONLY
        SugarSearchEngineAbstractBase::markSearchEngineStatus();
        //END SUGARCRM flav=pro ONLY
        $this->config_file_override = '';
        if(file_exists('config_override.php'))
            $this->config_file_override = file_get_contents('config_override.php');
        else
            $this->config_file_override= '<?php' . "\r\n";
        $new_line= '$sugar_config[\'full_text_engine\'] = true;';
        SugarAutoLoader::put('config_override.php', $this->config_file_override . "\r\n" . $new_line, true);
    }

    public function tearDown()
    {
        // restore FTS and config override
        //BEGIN SUGARCRM flav=pro ONLY
        SugarSearchEngineAbstractBase::markSearchEngineStatus(false);
        //END SUGARCRM flav=pro ONLY

        SugarAutoLoader::put('config_override.php', $this->config_file_override);

        // Cleaning up after ourselves, but only if there is cleanup to do
        // Accounts clean up
        if (count($this->accounts)) {
            $accountIds = array();
            foreach ( $this->accounts as $account ) {
                $accountIds[] = $account->id;
            }
            $accountIds = "('".implode("','",$accountIds)."')";
            $GLOBALS['db']->query("DELETE FROM accounts WHERE id IN {$accountIds}");
            if ($GLOBALS['db']->tableExists('accounts_cstm')) {
                $GLOBALS['db']->query("DELETE FROM accounts_cstm WHERE id_c IN {$accountIds}");
            }
        }

        // Opportunities clean up
        if (count($this->opps)) {
            $oppIds = array();
            foreach ( $this->opps as $opp ) {
                $oppIds[] = $opp->id;
            }
            $oppIds = "('".implode("','",$oppIds)."')";
            $GLOBALS['db']->query("DELETE FROM opportunities WHERE id IN {$oppIds}");
            $GLOBALS['db']->query("DELETE FROM accounts_opportunities WHERE opportunity_id IN {$oppIds}");
            $GLOBALS['db']->query("DELETE FROM opportunities_contacts WHERE opportunity_id IN {$oppIds}");
            if ($GLOBALS['db']->tableExists('opportunities_cstm')) {
                $GLOBALS['db']->query("DELETE FROM opportunities_cstm WHERE id_c IN {$oppIds}");
            }
        }

        // Contacts cleanup
        if (count($this->contacts)) {
            $contactIds = array();
            foreach ( $this->contacts as $contact ) {
                $contactIds[] = $contact->id;
            }
            $contactIds = "('".implode("','",$contactIds)."')";

            $GLOBALS['db']->query("DELETE FROM contacts WHERE id IN {$contactIds}");
            $GLOBALS['db']->query("DELETE FROM accounts_contacts WHERE contact_id IN {$contactIds}");
            if ($GLOBALS['db']->tableExists('contacts_cstm')) {
                $GLOBALS['db']->query("DELETE FROM contacts_cstm WHERE id_c IN {$contactIds}");
            }
        }

        // Cases cleanup
        if (count($this->cases)) {
            $caseIds = array();
            foreach ( $this->cases as $aCase ) {
                $caseIds[] = $aCase->id;
            }
            $caseIds = "('".implode("','",$caseIds)."')";

            $GLOBALS['db']->query("DELETE FROM cases WHERE id IN {$caseIds}");
            $GLOBALS['db']->query("DELETE FROM accounts_cases WHERE case_id IN {$caseIds}");
            if ($GLOBALS['db']->tableExists('cases_cstm')) {
                $GLOBALS['db']->query("DELETE FROM cases_cstm WHERE id_c IN {$caseIds}");
            }
        }

        // Bugs cleanup
        if (count($this->bugs)) {
            $bugIds = array();
            foreach( $this->bugs AS $bug ) {
                $bugIds[] = $bug->id;
            }
            $bugIds = "('" . implode( "','", $bugIds) . "')";
            $GLOBALS['db']->query("DELETE FROM bugs WHERE id IN {$bugIds}");
            if ($GLOBALS['db']->tableExists('bugs_cstm')) {
                $GLOBALS['db']->query("DELETE FROM bugs_cstm WHERE id_c IN {$bugIds}");
            }
        }

        if(count($this->users)) {
            foreach($this->users AS $id) {
                $GLOBALS['db']->query("DELETE FROM users WHERE id = '{$id}'");
                if ($GLOBALS['db']->tableExists('users_cstm')) {
                    $GLOBALS['db']->query("DELETE FROM users_cstm WHERE id_c IN {$id}");
                }
            }
        }

        //BEGIN SUGARCRM flav=pro ONLY
        $GLOBALS['db']->query("DELETE FROM sugarfavorites WHERE created_by = '".$GLOBALS['current_user']->id."'");
        //END SUGARCRM flav=pro ONLY

        parent::tearDown();
        foreach($this->files AS $file) {
            SugarAutoLoader::unlink($file);
        }
        SugarAutoLoader::saveMap();
        $GLOBALS['db']->commit();
    }

    /**
     * @group rest
     */
    public function testModuleSearch() {
        // Make sure there is at least one page of accounts
        for ( $i = 0 ; $i < 40 ; $i++ ) {
            $account = new Account();
            $account->name = "UNIT TEST ".count($this->accounts)." - ".create_guid();
            $account->billing_address_postalcode = sprintf("%08d",count($this->accounts));
            if ( $i > 25 && $i < 36 ) {
                $account->assigned_user_id = $GLOBALS['current_user']->id;
            } else {
                // The rest are assigned to admin
                $account->assigned_user_id = '1';
            }
            $account->save();
            $this->accounts[] = $account;
            //BEGIN SUGARCRM flav=pro ONLY
            if ( $i > 33 ) {
                // Favorite the last six
                $fav = new SugarFavorites();
                $fav->id = SugarFavorites::generateGUID('Accounts',$account->id);
                $fav->new_with_id = true;
                $fav->module = 'Accounts';
                $fav->record_id = $account->id;
                $fav->created_by = $GLOBALS['current_user']->id;
                $fav->assigned_user_id = $GLOBALS['current_user']->id;
                $fav->deleted = 0;
                $fav->save();
            }
            //END SUGARCRM flav=pro ONLY
        }
        $GLOBALS['db']->commit();

        // Test searching for a lot of records
        $restReply = $this->_restCall("Accounts/?q=".rawurlencode("UNIT TEST")."&max_num=30");
        $this->assertEquals(30,$restReply['reply']['next_offset'],"Next offset was set incorrectly.");

        // Test Offset
        $restReply2 = $this->_restCall("Accounts?offset=".$restReply['reply']['next_offset']);

        $this->assertNotEquals($restReply['reply']['next_offset'],$restReply2['reply']['next_offset'],"Next offset was not set correctly on the second page.");

        // Test finding one record
        $restReply3 = $this->_restCall("Accounts/?q=".rawurlencode($this->accounts[17]->name));

        $this->assertTrue(is_array($restReply3['reply']['records']), "Reply3 Records is not an array");

        $tmp = array_keys($restReply3['reply']['records']);
        $firstRecord = $restReply3['reply']['records'][$tmp[0]];
        $this->assertEquals($this->accounts[17]->name,$firstRecord['name'],"The search failed for record: ".$this->accounts[17]->name);

        // Sorting descending
        $restReply4 = $this->_restCall("Accounts?q=".rawurlencode("UNIT TEST")."&order_by=id:DESC");

        $this->assertTrue(is_array($restReply4['reply']['records']), "Reply4 Records is not an array");

        $tmp = array_keys($restReply4['reply']['records']);
        $this->assertLessThan($restReply4['reply']['records'][$tmp[0]]['id'],
                              $restReply4['reply']['records'][$tmp[1]]['id'],
                              'Second record is not lower than the first, decending order failed.');

        // Sorting ascending
        $restReply5 = $this->_restCall("Accounts?q=".rawurlencode("UNIT TEST")."&order_by=id:ASC");

        $this->assertTrue(is_array($restReply5['reply']['records']), "Reply5 Records is not an array");

        $tmp = array_keys($restReply5['reply']['records']);
        $this->assertGreaterThan($restReply5['reply']['records'][$tmp[0]]['id'],
                                 $restReply5['reply']['records'][$tmp[1]]['id'],
                                 'Second record is not lower than the first, ascending order failed.');
        //BEGIN SUGARCRM flav=pro ONLY
        // Test Favorites
        $restReply = $this->_restCall("Accounts?favorites=1&max_num=10");
        $this->assertEquals(6,count($restReply['reply']['records']));
        //END SUGARCRM flav=pro ONLY

        // Test My Items
        $restReply = $this->_restCall("Accounts?my_items=1&max_num=20");
        $this->assertEquals(10,count($restReply['reply']['records']));

        // validate each is actually my item
        foreach($restReply['reply']['records'] AS $record) {
            $this->assertEquals($record['assigned_user_id'], $GLOBALS['current_user']->id, "A Record isn't assigned to me");
        }

        //BEGIN SUGARCRM flav=pro ONLY
        // Test Favorites & My Items
        $restReply = $this->_restCall("Accounts?favorites=1&my_items=1&max_num=10");
        $this->assertEquals(2,count($restReply['reply']['records']));
        //END SUGARCRM flav=pro ONLY

        // Get a list, no searching
        $restReply = $this->_restCall("Accounts?max_num=10");
        $this->assertEquals(10,count($restReply['reply']['records']));

        // Get 2 pages, verify the data is different [check guids]
        $restReply_page1 = $this->_restCall("Accounts?offset=0&max_num=5");
        $restReply_page2 = $this->_restCall("Accounts?offset=5&max_num=5");
        foreach($restReply_page1['reply']['records'] AS $page1_record) {
            foreach($restReply_page2['reply']['records'] AS $page2_record) {
                $this->assertNotEquals($page2_record['id'], $page1_record['id'], "ID's match, pagination may be broke");
            }
        }

    }

    /**
     * @group rest
     */
    public function testBugSearch() {
        $bug = new Bug();
        $bug->name = "UNIT TEST " . count($this->bugs) . " - " . create_guid();
        $bug->description = $bug->name;
        $bug->save();
        $this->bugs[] = $bug;
        $GLOBALS['db']->commit();
        $restReply = $this->_restCall("Bugs?q=" . rawurlencode("UNIT TEST"));
        $tmp = array_keys($restReply['reply']['records']);
        $this->assertTrue(!empty($restReply['reply']['records'][$tmp[0]]['description']), "Description not filled out");
    }

    /**
     * @group rest
     */
    public function testCaseSearch() {
        // This global only needs to be set for this test
        SugarTestHelper::setUp('mod_strings', array('Administration'));

        // Cases searches not only by fields in the module, but by the related account_name so it caused some extra problems so it gets some extra tests.
        // Make sure there is at least one page of cases
        for ( $i = 0 ; $i < 40 ; $i++ ) {
            $aCase = new aCase();
            $aCase->name = "UNIT TEST ".count($this->cases)." - ".create_guid();
            $aCase->billing_address_postalcode = sprintf("%08d",count($this->cases));
            if ( $i > 25 && $i < 36 ) {
                $aCase->assigned_user_id = $GLOBALS['current_user']->id;
            } else {
                // The rest are assigned to admin
                $aCase->assigned_user_id = '1';
            }
            $aCase->save();
            $this->cases[] = $aCase;
            //BEGIN SUGARCRM flav=pro ONLY
            if ( $i > 33 ) {
                // Favorite the last six
                $fav = new SugarFavorites();
                $fav->id = SugarFavorites::generateGUID('Cases',$aCase->id);
                $fav->new_with_id = true;
                $fav->module = 'Cases';
                $fav->record_id = $aCase->id;
                $fav->created_by = $GLOBALS['current_user']->id;
                $fav->assigned_user_id = $GLOBALS['current_user']->id;
                $fav->deleted = 0;
                $fav->save();
            }
            //END SUGARCRM flav=pro ONLY
        }
        $GLOBALS['db']->commit();
        // Test searching for a lot of records
        $restReply = $this->_restCall("Cases/?q=".rawurlencode("UNIT TEST")."&max_num=30");

        $this->assertEquals(30,$restReply['reply']['next_offset'],"Next offset was set incorrectly.");

        // Test Offset
        $restReply2 = $this->_restCall("Cases?offset=".$restReply['reply']['next_offset']);

        $this->assertNotEquals($restReply['reply']['next_offset'],$restReply2['reply']['next_offset'],"Next offset was not set correctly on the second page.");

        // Test finding one record
        $restReply3 = $this->_restCall("Cases/?q=".rawurlencode($this->cases[17]->name));

        $tmp = array_keys($restReply3['reply']['records']);
        $firstRecord = $restReply3['reply']['records'][$tmp[0]];
        $this->assertEquals($this->cases[17]->name,$firstRecord['name'],"The search failed for record: ".$this->cases[17]->name);

        // Here is where the problem came
        // First searching for specific fields broke
        $restReply = $this->_restCall("Cases/?q=".rawurlencode("UNIT TEST")."&fields=name,case_number");
        $this->assertGreaterThan(0,count($restReply['reply']['records']));

        // Then searching without specific fields broke
        $restReply = $this->_restCall("Cases/?q=".rawurlencode("UNIT TEST"));
        $this->assertGreaterThan(0,count($restReply['reply']['records']));

        // add a search field
        // create a new custom metadata vardef for unified search on status

        $metadata = '<?php $dictionary["Case"]["fields"]["status"]["unified_search"] = true; ?>';
        $metadata_dir = 'custom/Extension/modules/Cases/Ext/Vardefs';
        $metadata_file = 'case_status_unified_search.php';
        if(!is_dir($metadata_dir)) {
            mkdir("{$metadata_dir}", 0777, true);
        }

        SugarAutoLoader::put( $metadata_dir . '/' . $metadata_file, $metadata, true );
        $user = new User();

        // save old user
        $old_user = $GLOBALS['current_user'];
        $GLOBALS['current_user'] = $user->getSystemUser();
        $this->files[] = $metadata_dir . '/' . $metadata_file;

        // run repair and rebuild
        $_REQUEST['repair_silent']=1;
        $rc = new RepairAndClear();
        $rc->repairAndClearAll(array("rebuildExtensions", "clearVardefs"), array("Cases"),  false, false);

        // switch back to the user
        $GLBOALS['current_user'] = $old_user;

        $restReply = $this->_restCall("Cases/?q=New");

        foreach($restReply['reply']['records'] AS $record) {
            $status = trim($record['status']);
            $name = trim($record['name']);
            $status = substr($status, 0, 3);
            $name = substr($status, 0, 3);
            // this may not be the best way to do this but I can't figure out a better way right now
            $test = array( ucwords($status), ucwords($name) );
            $this->assertContains('New', $test, "New does not start either name or status");
        }

    }

    /**
     * @group rest
     */
    public function testUserEmployeeActiveOnlySearch()
    {
        $users = $GLOBALS['db']->getOne("SELECT count(id) as cnt FROM users WHERE deleted=0 AND status='Active'");
        $emp = $GLOBALS['db']->getOne("SELECT count(id) as cnt FROM users WHERE deleted=0 AND status='Active' AND employee_status='Active'");
        for($x=0;$x<10;$x++) {
            $user = BeanFactory::newBean('Users');
            $user->first_name = 'Test';
            $user->last_name = create_guid();
            $user->status = 'Active';
            $user->employee_status = 'Active';
            $user->save();
            $this->users[] = $user->id;
        }
        $emp += 10;
        $users += 10;
        $restReply = $this->_restCall('Employees');
        $this->assertEquals($emp, count($restReply['reply']['records']), "Number of employees incorrect.  Should be $emp but is " . count($restReply['reply']['records']));
        $restReply = $this->_restCall('Users');
        $this->assertEquals($users, count($restReply['reply']['records']), "Number of users incorrect.  Should be $users but is " . count($restReply['reply']['records']));

        // set a user employee_status as terminated
        $user = BeanFactory::getBean('Users', $this->users[0]);
        $user->employee_status = 'Terminated';
        $user->save();
        $emp--;
        $restReply = $this->_restCall('Employees');
        $this->assertEquals($emp, count($restReply['reply']['records']), "Number of employees incorrect.  Should be $emp but is " . count($restReply['reply']['records']));

        $restReply = $this->_restCall('Users');
        $this->assertEquals($users, count($restReply['reply']['records']), "Number of users incorrect.  Should be $users but is " . count($restReply['reply']['records']));

        // set a users status as inactive
        $user = BeanFactory::getBean('Users', $this->users[1]);
        $user->status = 'Inactive';
        $user->save();
        $emp--; $users--;
        $restReply = $this->_restCall('Employees');
        $this->assertEquals($emp, count($restReply['reply']['records']), "Number of employees incorrect.  Should be $emp but is " . count($restReply['reply']['records']));

        $restReply = $this->_restCall('Users');
        $this->assertEquals($users, count($restReply['reply']['records']), "Number of users incorrect.  Should be $users but is " . count($restReply['reply']['records']));

    }

    /**
     * @group rest
     */
    public function testGlobalSearch() {
        // Make sure there is at least one page of accounts
        for ( $i = 0 ; $i < 40 ; $i++ ) {
            $account = new Account();
            $account->name = "UNIT TEST ".count($this->accounts)." - ".create_guid();
            $account->billing_address_postalcode = sprintf("%08d",count($this->accounts));
            if ( $i > 25 && $i < 36 ) {
                $account->assigned_user_id = $GLOBALS['current_user']->id;
            } else {
                // The rest are assigned to admin
                $account->assigned_user_id = '1';
            }
            $account->save();
            $this->accounts[] = $account;
            //BEGIN SUGARCRM flav=pro ONLY
            if ( $i > 33 ) {
                // Favorite the last six
                $fav = new SugarFavorites();
                $fav->id = SugarFavorites::generateGUID('Accounts',$account->id);
                $fav->new_with_id = true;
                $fav->module = 'Accounts';
                $fav->record_id = $account->id;
                $fav->created_by = $GLOBALS['current_user']->id;
                $fav->assigned_user_id = $GLOBALS['current_user']->id;
                $fav->deleted = 0;
                $fav->save();
            }
            //END SUGARCRM flav=pro ONLY
        }

        for ( $i = 0 ; $i < 30 ; $i++ ) {
            $contact = new Contact();
            $contact->first_name = "UNIT";
            $contact->last_name = "TEST ".create_guid();
            if ( $i > 15 && $i < 26 ) {
                $contact->assigned_user_id = $GLOBALS['current_user']->id;
            } else {
                // The rest are assigned to admin
                $contact->assigned_user_id = '1';
            }
            $contact->save();
            $this->contacts[] = $contact;
            //BEGIN SUGARCRM flav=pro ONLY
            if ( $i > 23 ) {
                // Favorite the last six
                $fav = new SugarFavorites();
                $fav->id = SugarFavorites::generateGUID('Contacts',$contact->id);
                $fav->new_with_id = true;
                $fav->module = 'Contacts';
                $fav->record_id = $contact->id;
                $fav->created_by = $GLOBALS['current_user']->id;
                $fav->assigned_user_id = $GLOBALS['current_user']->id;
                $fav->deleted = 0;
                $fav->save();
            }
            //END SUGARCRM flav=pro ONLY
        }

        for ( $i = 0 ; $i < 30 ; $i++ ) {
            $opportunity = new Opportunity();
            $opportunity->name = "UNIT TEST ".create_guid();

            if ( $i > 15 && $i < 26 ) {
                $opportunity->assigned_user_id = $GLOBALS['current_user']->id;
            } else {
                // The rest are assigned to admin
                $opportunity->assigned_user_id = '1';
            }
            $opportunity->date_closed = TimeDate::getInstance()->getNow()->asDbDate();
            $opportunity->save();
            $this->opps[] = $opportunity;
            //BEGIN SUGARCRM flav=pro ONLY
            if ( $i > 23 ) {
                // Favorite the last six
                $fav = new SugarFavorites();
                $fav->id = SugarFavorites::generateGUID('Opportunities',$opportunity->id);
                $fav->new_with_id = true;
                $fav->module = 'Opportunities';
                $fav->record_id = $opportunity->id;
                $fav->created_by = $GLOBALS['current_user']->id;
                $fav->assigned_user_id = $GLOBALS['current_user']->id;
                $fav->deleted = 0;
                $fav->save();
            }
            //END SUGARCRM flav=pro ONLY
        }

        $GLOBALS['db']->commit();

        // Test searching for a lot of records
        $restReply = $this->_restCall("search?q=".rawurlencode("UNIT TEST")."&max_num=5");
        $this->assertEquals(5,$restReply['reply']['next_offset'],"Next offset was set incorrectly.");

        // Test Offset
        $restReply2 = $this->_restCall("search/?offset=".$restReply['reply']['next_offset']);

        $this->assertNotEquals($restReply['reply']['next_offset'],$restReply2['reply']['next_offset'],"Next offset was not set correctly on the second page.");

        // Test finding one record
        $restReply3 = $this->_restCall("search/?q=".rawurlencode($this->opps[17]->name));

        $tmp = array_keys($restReply3['reply']['records']);
        $firstRecord = $restReply3['reply']['records'][$tmp[0]];
        $this->assertEquals($this->opps[17]->name,$firstRecord['name'],"The search failed for record: ".$this->opps[17]->name);

        // Sorting descending
        $restReply4 = $this->_restCall("search?q=".rawurlencode("UNIT TEST")."&order_by=id:DESC");

        $tmp = array_keys($restReply4['reply']['records']);
        $this->assertLessThan($restReply4['reply']['records'][$tmp[0]]['id'],
                              $restReply4['reply']['records'][$tmp[1]]['id'],
                              'Second record is not lower than the first, decending order failed.');

        // Sorting ascending
        $restReply5 = $this->_restCall("search?q=".rawurlencode("UNIT TEST")."&order_by=id:ASC");

        $tmp = array_keys($restReply5['reply']['records']);
        $this->assertGreaterThan($restReply5['reply']['records'][$tmp[0]]['id'],
                                 $restReply5['reply']['records'][$tmp[1]]['id'],
                                 'Second record is not lower than the first, ascending order failed.');
        //BEGIN SUGARCRM flav=pro ONLY
        // Test Favorites
        $restReply = $this->_restCall("search?favorites=1&max_num=30&max_num_module=10&fields=name");
        $this->assertEquals(18,count($restReply['reply']['records']));
        //END SUGARCRM flav=pro ONLY

        // Test My Items
        $restReply = $this->_restCall("search?my_items=1&max_num=50&max_num_module=20");
        $this->assertEquals(30,count($restReply['reply']['records']));

        //BEGIN SUGARCRM flav=pro ONLY
        // Test Favorites & My Items
        $restReply = $this->_restCall("search?favorites=1&my_items=1&max_num=10");
        $this->assertEquals(6,count($restReply['reply']['records']));
        //END SUGARCRM flav=pro ONLY

        // Get a list, no searching
        $restReply = $this->_restCall("search?max_num=10");
        $this->assertEquals(10,count($restReply['reply']['records']));

    }

}

