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

class RestTestUpdate extends RestTestBase
{
    public function setUp()
    {
        //Create an anonymous user for login purposes/
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->_user;
        $this->_restLogin($this->_user->user_name, $this->_user->user_name);
    }

    public function tearDown()
    {
        if (isset($this->account->id)) {
            $GLOBALS['db']->query("DELETE FROM accounts WHERE id = '{$this->account->id}'");
            $GLOBALS['db']->query("DELETE FROM accounts_cstm WHERE id = '{$this->account->id}'");
        }
        if (isset($this->contact->id)) {
            $GLOBALS['db']->query("DELETE FROM contacts WHERE id = '{$this->contact->id}'");
            $GLOBALS['db']->query("DELETE FROM contacts_cstm WHERE id = '{$this->contact->id}'");
        }
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function testUpdate()
    {
        $this->account = new Account();
        $this->account->name = "UNIT TEST - BEFORE";
        $this->account->save();
        $restReply = $this->_restCall("Accounts/{$this->account->id}", json_encode(array('name' => 'UNIT TEST - AFTER')), "PUT");

        $this->assertEquals($this->account->id, $restReply['reply']['id'], "The returned account id was not the same.");

        $account2 = new Account();
        $account2->retrieve($this->account->id);

        $this->assertEquals("UNIT TEST - AFTER",
                            $account2->name,
                            "Did not set the account name.");

        $this->assertEquals($restReply['reply']['name'],
                            $account2->name,
                            "Rest Reply and Bean Do Not Match.");
    }


    public function testUpdateEmail()
    {
        $this->contact = new Contact();
        $this->contact->first_name = "UNIT TEST - BEFORE";
        $this->contact->save();
        $emails = array(
                        array(
                            'email_address'=>'test@test.com',
                            'opt_out'=>'0',
                            'invalid_email'=>'0',
                            'primary_address'=>'1'
                        ),
                        array(
                            'email_address'=>'asdf@test.com',
                            'opt_out'=>'0',
                            'invalid_email'=>'1',
                            'primary_address'=>'0'
                        ),
                    );
        $restReply = $this->_restCall("Contacts/{$this->contact->id}", json_encode(array(
            'first_name' => 'UNIT TEST - AFTER',
            'email' => $emails,
        )), "PUT");

        $this->assertEquals($this->contact->id, $restReply['reply']['id'], "The returned contact id was not the same.");

        $contact2 = new Contact();
        $contact2->retrieve($this->contact->id);
        $restReply = $this->_restCall("Contacts/{$this->contact->id}");

        $this->assertEquals($restReply['reply']['email'], $emails,"Returned emails don't match");

        $this->assertEquals("UNIT TEST - AFTER",
                            $contact2->name,
                            "Did not set the account name.");

        $this->assertEquals($restReply['reply']['name'],
                            $contact2->name,
                            "Rest Reply and Bean Do Not Match.");
    }
}