<?php
//FILE SUGARCRM flav=pro ONLY

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

class RestFavoriteTest extends RestTestBase {
    public function setUp()
    {
        parent::setUp();
    }
    
    public function tearDown()
    {
        if ( isset($this->account_id) ) {
            $GLOBALS['db']->query("DELETE FROM accounts WHERE id = '{$this->account_id}'");
            $GLOBALS['db']->query("DELETE FROM accounts_cstm WHERE id = '{$this->account_id}'");
        }
        $GLOBALS['db']->query("DELETE FROM sugarfavorites WHERE created_by = '".$GLOBALS['current_user']->id."'");
        parent::tearDown();
    }

    public function testSetFavorite() {
        $restReply = $this->_restCall("Accounts/",
                                      json_encode(array('name'=>'UNIT TEST - AFTER', 'my_favorite' => true)),
                                      'POST');

        $this->assertTrue(isset($restReply['reply']['id']),
                          "An account was not created (or if it was, the ID was not returned)");

        $this->assertTrue(isset($restReply['reply']['team_name'][0]['name']), "A team name was not set.");

        $this->account_id = $restReply['reply']['id'];
        
        $account = new Account();
        $account->retrieve($this->account_id);

        $this->assertEquals("UNIT TEST - AFTER",
                            $account->name,
                            "Did not set the account name.");


        $restReply = $this->_restCall("Accounts/{$account->id}/favorite", array(), 'PUT');
        
        $is_fav = SugarFavorites::isUserFavorite('Accounts', $account->id, $this->_user->id);
        
        $this->assertEquals($is_fav, (bool) $restReply['reply']['my_favorite'], "The returned favorite was not the same.");


        $restReply = $this->_restCall("Accounts/{$account->id}/unfavorite", array(), 'PUT');

        
        $is_fav = SugarFavorites::isUserFavorite('Accounts', $account->id, $this->_user->id);
        
        $this->assertEquals($is_fav, (bool) $restReply['reply']['my_favorite'], "The returned favorite was not the same.");


    }

}