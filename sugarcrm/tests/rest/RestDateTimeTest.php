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

class RestDateTimeTest extends RestTestBase {
    public function tearDown()
    {
        if (isset($this->account->id)) {
            $GLOBALS['db']->query("DELETE FROM accounts WHERE id = '{$this->account->id}'");
            $GLOBALS['db']->query("DELETE FROM accounts_cstm WHERE id = '{$this->account->id}'");
        }
        if (isset($this->opp->id)) {
            $GLOBALS['db']->query("DELETE FROM opportunities WHERE id = '{$this->opp->id}'");
            $GLOBALS['db']->query("DELETE FROM opportunities_cstm WHERE id = '{$this->opp->id}'");
        }
        if (isset($this->meeting->id)) {
            $GLOBALS['db']->query("DELETE FROM meetings WHERE id = '{$this->meeting->id}'");
            $GLOBALS['db']->query("DELETE FROM meetings_contacts WHERE meeting_id = '{$this->meeting->id}'");
            $GLOBALS['db']->query("DELETE FROM meetings_leads WHERE meeting_id = '{$this->meeting->id}'");
            $GLOBALS['db']->query("DELETE FROM meetings_users WHERE meeting_id = '{$this->meeting->id}'");
        }
        
        $GLOBALS['db']->query("DELETE FROM sugarfavorites WHERE created_by = '".$GLOBALS['current_user']->id."'");

        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testRestDate()
    {
        $this->account = new Account();
        $this->account->name = "UNIT TEST - Account";
        $this->account->assigned_user_id = $GLOBALS['current_user']->id;
        $this->account->team_id = '1';
        $this->account->team_set_id = '1';
        $this->account->save();

        $this->opp = new Opportunity();
        $this->opp->name = "UNIT TEST - Opportunity";
        $this->opp->amount = 25000.1;
        $this->opp->date_closed = '2012-11-10';
        $this->opp->account_id = $this->account->id;
        $this->opp->assigned_user_id = $GLOBALS['current_user']->id;
        $this->opp->team_id = '1';
        $this->opp->team_set_id = '1';
        $this->opp->save();
        
        $GLOBALS['db']->commit();
        
        $restReply = $this->_restCall("Opportunities/{$this->opp->id}");
        $this->assertEquals('2012-11-10',$restReply['reply']['date_closed']);

        $restReply = $this->_restCall("Opportunities/{$this->opp->id}",
                                      json_encode(array('date_closed'=>'2012-10-11')),
                                      'PUT');
        $this->assertEquals('2012-10-11',$restReply['reply']['date_closed']);

        $ret = $GLOBALS['db']->query("SELECT date_closed FROM opportunities WHERE id = '{$this->opp->id}'",true);
        $row = $GLOBALS['db']->fetchByAssoc($ret);
        $this->assertEquals('2012-10-11',$row['date_closed']);
    }

    /**
     * @group rest
     */
    public function testRestDateTime()
    {
        $this->meeting = new Meeting();
        $this->meeting->name = "UNIT TEST - Meeting";
        $this->meeting->date_start = "2012-12-13 17:00:00";
        $this->meeting->date_end = "2012-12-13 17:15:00";
        $this->meeting->duration_hours = 0;
        $this->meeting->duration_minutes = 15;
        $this->meeting->assigned_user_id = $GLOBALS['current_user']->id;
        $this->meeting->team_id = '1';
        $this->meeting->team_set_id = '1';
        $this->meeting->save();
        
        $GLOBALS['db']->commit();
        
        $GLOBALS['current_user']->setPreference('timezone','America/Boise');
        $GLOBALS['current_user']->savePreferencesToDB();

        $restReply = $this->_restCall("Meetings/{$this->meeting->id}");
        $this->assertEquals($restReply['reply']['date_start'],'2012-12-13T10:00:00-0700');
        $this->assertEquals($restReply['reply']['date_end'],'2012-12-13T10:15:00-0700');

        // Check saving without offset
        $restReply = $this->_restCall("Meetings/{$this->meeting->id}",
                                      json_encode(array('date_end'=>'2012-12-13T10:15:00')),
                                      'PUT');
        $ret = $GLOBALS['db']->query("SELECT date_end FROM meetings WHERE id = '{$this->meeting->id}'",true);
        $row = $GLOBALS['db']->fetchByAssoc($ret);
        $this->assertEquals('2012-12-13 17:15:00',$row['date_end']);


        // Check saving with the user's offset
        $restReply = $this->_restCall("Meetings/{$this->meeting->id}",
                                      json_encode(array('date_end'=>'2012-12-13T10:15:00-0700')),
                                      'PUT');
        $ret = $GLOBALS['db']->query("SELECT date_end FROM meetings WHERE id = '{$this->meeting->id}'",true);
        $row = $GLOBALS['db']->fetchByAssoc($ret);
        $this->assertEquals('2012-12-13 17:15:00',$row['date_end']);

        // Check saving in GMT
        $restReply = $this->_restCall("Meetings/{$this->meeting->id}",
                                      json_encode(array('date_end'=>'2012-12-13T17:15:00Z')),
                                      'PUT');
        $ret = $GLOBALS['db']->query("SELECT date_end FROM meetings WHERE id = '{$this->meeting->id}'",true);
        $row = $GLOBALS['db']->fetchByAssoc($ret);
        $this->assertEquals('2012-12-13 17:15:00',$row['date_end']);

        // Check saving with the user's offset (in JS format)
        $restReply = $this->_restCall("Meetings/{$this->meeting->id}",
                                      json_encode(array('date_end'=>'2012-12-13T10:15:00.1234-0700')),
                                      'PUT');
        $ret = $GLOBALS['db']->query("SELECT date_end FROM meetings WHERE id = '{$this->meeting->id}'",true);
        $row = $GLOBALS['db']->fetchByAssoc($ret);
        $this->assertEquals('2012-12-13 17:15:00',$row['date_end']);

        // Check saving in GMT (in JS format)
        $restReply = $this->_restCall("Meetings/{$this->meeting->id}",
                                      json_encode(array('date_end'=>'2012-12-13T17:15:00.1234Z')),
                                      'PUT');
        $ret = $GLOBALS['db']->query("SELECT date_end FROM meetings WHERE id = '{$this->meeting->id}'",true);
        $row = $GLOBALS['db']->fetchByAssoc($ret);
        $this->assertEquals('2012-12-13 17:15:00',$row['date_end']);


        $GLOBALS['current_user']->setPreference('timezone','Europe/Helsinki');
        $GLOBALS['current_user']->savePreferencesToDB();

        // Need to logout so the perference changes will come in to effect.
        unset($this->authToken);

        $restReply = $this->_restCall("Meetings/{$this->meeting->id}");
        $this->assertEquals($restReply['reply']['date_start'],'2012-12-13T19:00:00+0200');
        $this->assertEquals($restReply['reply']['date_end'],'2012-12-13T19:15:00+0200');

        // Check saving without offset
        $restReply = $this->_restCall("Meetings/{$this->meeting->id}",
                                      json_encode(array('date_end'=>'2012-12-13T19:15:00')),
                                      'PUT');
        $ret = $GLOBALS['db']->query("SELECT date_end FROM meetings WHERE id = '{$this->meeting->id}'",true);
        $row = $GLOBALS['db']->fetchByAssoc($ret);
        $this->assertEquals('2012-12-13 17:15:00',$row['date_end']);

        // Check saving without offset (in JS format)
        $restReply = $this->_restCall("Meetings/{$this->meeting->id}",
                                      json_encode(array('date_end'=>'2012-12-13T19:15:00.1234')),
                                      'PUT');
        $ret = $GLOBALS['db']->query("SELECT date_end FROM meetings WHERE id = '{$this->meeting->id}'",true);
        $row = $GLOBALS['db']->fetchByAssoc($ret);
        $this->assertEquals('2012-12-13 17:15:00',$row['date_end']);


        // Check saving with the user's offset
        $restReply = $this->_restCall("Meetings/{$this->meeting->id}",
                                      json_encode(array('date_end'=>'2012-12-13T19:15:00+0200')),
                                      'PUT');
        $ret = $GLOBALS['db']->query("SELECT date_end FROM meetings WHERE id = '{$this->meeting->id}'",true);
        $row = $GLOBALS['db']->fetchByAssoc($ret);
        $this->assertEquals('2012-12-13 17:15:00',$row['date_end']);

        // Check saving in GMT
        $restReply = $this->_restCall("Meetings/{$this->meeting->id}",
                                      json_encode(array('date_end'=>'2012-12-13T17:15:00Z')),
                                      'PUT');
        $ret = $GLOBALS['db']->query("SELECT date_end FROM meetings WHERE id = '{$this->meeting->id}'",true);
        $row = $GLOBALS['db']->fetchByAssoc($ret);
        $this->assertEquals('2012-12-13 17:15:00',$row['date_end']);
        
    }

    /**
     * @group rest
     */
    public function testRestInvalidDateTime()
    {
        $this->meeting = new Meeting();
        $this->meeting->name = "UNIT TEST - Meeting";
        $this->meeting->date_start = "2012-12-13 17:00:00";
        $this->meeting->date_end = "2012-12-13 17:15:00";
        $this->meeting->duration_hours = 0;
        $this->meeting->duration_minutes = 15;
        $this->meeting->assigned_user_id = $GLOBALS['current_user']->id;
        $this->meeting->team_id = '1';
        $this->meeting->team_set_id = '1';
        $this->meeting->save();
        
        $GLOBALS['db']->commit();
        
        $GLOBALS['current_user']->setPreference('timezone','America/Boise');
        $GLOBALS['current_user']->savePreferencesToDB();

        // Check saving without offset
        $restReply = $this->_restCall("Meetings/{$this->meeting->id}",
                                      json_encode(array('date_end'=>'this meeting will never end')),
                                      'PUT');
        $this->assertEquals('invalid_parameter',$restReply['reply']['error']);
    }


    /**
     * @group rest
     */
    public function testRestBlankDateTime()
    {
        $this->meeting = new Meeting();
        $this->meeting->name = "UNIT TEST - Meeting";
        $this->meeting->date_start = "2012-12-13 17:00:00";
        $this->meeting->date_end = "2012-12-13 17:15:00";
        $this->meeting->duration_hours = 0;
        $this->meeting->duration_minutes = 15;
        $this->meeting->assigned_user_id = $GLOBALS['current_user']->id;
        $this->meeting->team_id = '1';
        $this->meeting->team_set_id = '1';
        $this->meeting->save();
        
        $GLOBALS['db']->commit();
        
        $GLOBALS['current_user']->setPreference('timezone','America/Boise');
        $GLOBALS['current_user']->savePreferencesToDB();

        // Check saving without offset
        $restReply = $this->_restCall("Meetings/{$this->meeting->id}",
                                      json_encode(array('date_start'=>'','date_end'=>'')),
                                      'PUT');

        $ret = $GLOBALS['db']->query("SELECT date_end FROM meetings WHERE id = '{$this->meeting->id}'",true);
        $row = $GLOBALS['db']->fetchByAssoc($ret);
        $this->assertEquals('',$row['date_end']);
    }

}