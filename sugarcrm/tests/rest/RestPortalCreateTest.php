<?php
//FILE SUGARCRM flav=ent ONLY
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

require_once('tests/rest/RestTestPortalBase.php');

class RestPortalCreateTest extends RestTestPortalBase
{

    /**
     * We need to associate an Account with the Contact in order to create Cases
     */
    public function setUp(){
        parent::setUp();
        // create account
        $this->account = new Account();
        $this->account->name = "UNIT TEST account - " . create_guid();
        $this->account->billing_address_postalcode = sprintf("%08d", 1);
        $this->account->save();
        $this->accounts[] = $this->account;

        $this->contact->load_relationship('accounts');
        // relate
        $this->contact->accounts->add($this->account->id);
        $GLOBALS['db']->commit();
    }

    /**
     * Make sure the relationship is removed, parent should clean everything else
     */
    public function tearDown(){
        if (isset($this->account->id)) {
            $this->contact->accounts->delete($this->account->id);
        }
        // Parent will remove account
        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testCreate()
    {
        // we need to be an admin to get at the relationship data
        $GLOBALS['current_user']->is_admin = 1;
        $args = array(
            'grant_type' => 'password',
            'username' => $this->contact->portal_name,
            'password' => 'unittest',
            'client_id' => 'support_portal',
            'client_secret' => '',
            'platform' => 'portal',
        );
        $GLOBALS['db']->commit();

        // create case
        $caseReply = $this->_restCall("Cases/",
                                      json_encode(array('name' => 'UNIT TEST Case','portal_visible'=>true)),
            'POST');
        $this->assertEquals(200,$caseReply['info']['http_code'],"HTTP Code was not a 200 - #1");
        $this->assertEquals($caseReply['reply']['account_id'], $this->account->id, "Case create did not contain account id of creator");
        $GLOBALS['db']->commit();
        $this->case = new aCase();

        $this->case->retrieve($caseReply['reply']['id']);
        $relates = $this->case->get_linked_beans('contacts', 'Contact');
        $this->assertEquals($relates[0]->id, $this->contact->id, "The contact id does not match the first related contact for the created case");
        // Make sure new case is cleaned up
        $this->cases[] = $this->case;
        // create bug
        $bugReply = $this->_restCall("Bugs/",
            json_encode(array('name' => 'UNIT TEST Bug')),
            'POST');
        $this->assertEquals(200,$bugReply['info']['http_code'],"HTTP Code was not a 200 - #2");
        $GLOBALS['db']->commit();
        $this->bug = new Bug();
        $this->bug->retrieve($bugReply['reply']['id']);
        // Make sure new bug is cleaned up
        $this->bugs[] = $this->bug;
        $relates = $this->bug->get_linked_beans('contacts', 'Contact');
        $this->assertEquals($relates[0]->id, $this->contact->id, "The contact id does not match the first related contact for the created case - #2");

        $relatesAccounts = $this->bug->get_linked_beans('accounts', 'Account');
        $this->assertEquals($relatesAccounts[0]->id, $this->account->id, "The account id does not match the first related account for the created case");
    }

    /**
     * @group bug56143
     * @group rest
     */
    public function testCreateErrorBug56143()
    {
        // we need to be an admin to get at the relationship data
        $GLOBALS['current_user']->is_admin = 1;
        $GLOBALS['db']->commit();
        $this->_restLogin($this->contact->portal_name,'unittest');

        // Remove the Account from Contact so this Contact can no longer create Cases
        if (isset($this->account->id)) {
            $this->contact->accounts->delete($this->account->id);
        }

        // create case
        $GLOBALS['db']->commit();
        $caseReply = $this->_restCall("Cases/",
            json_encode(array('name' => 'UNIT TEST Case')),
            'POST');

        $this->assertEquals("not_authorized",$caseReply['reply']['error']);
        $this->assertEquals(403,$caseReply['info']['http_code'],"HTTP Status");
        // Error message should mention the module name
        $this->assertContains('Cases',$caseReply['reply']['error_message'], "The error message should mention the module name.");

    }
}