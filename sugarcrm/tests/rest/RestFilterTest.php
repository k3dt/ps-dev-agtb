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

class RestFilterTest extends RestTestBase
{
    public function setUp()
    {
        parent::setUp();

        // Need at least 20 records so we can test pagination
        for ( $i = 0 ; $i < 20 ; $i++ ) {
            $account = BeanFactory::newBean('Accounts');
            $account->id = 'UNIT-TEST-' . create_guid_section(10);
            $account->new_with_id = true;
            $account->name = "TEST $i Account";
            $account->billing_address_postalcode = ($i%10)."0210";
            $account->save();
            $this->accounts[] = $account;
            for ( $ii = 0; $ii < 2 ; $ii++ ) {
                $opp = BeanFactory::newBean('Opportunities');
                $opp->id = 'UNIT-TEST-' . create_guid_section(10);
                $opp->new_with_id = true;
                $opp->name = "TEST $ii Opportunity FOR $i Account";
                $opp->amount = $ii * 10000;
                $opp->expected_close_date = '12-1'.$ii.'-2012';
                $opp->save();
                $this->opps[] = $opp;
                $account->load_relationship('opportunities');
                $account->opportunities->add(array($opp));
            }
            if ( $i < 5 ) {
                // Only need a few notes
                $note = BeanFactory::newBean('Notes');
                $note->id = 'UNIT-TEST-' . create_guid_section(10);
                $note->new_with_id = true;
                $note->name = "Test $i Note";
                $note->description = "This is a note for account $i";
                $note->save();
                $account->load_relationship('notes');
                $account->notes->add(array($note));
                $note->save();
                $this->notes[] = $note;
            }
        }

        // Clean up any hanging related records
        SugarRelationship::resaveRelatedBeans();
    }

    public function tearDown()
    {
        parent::tearDown();

        //BEGIN SUGARCRM flav=pro ONLY
        $GLOBALS['db']->query("DELETE FROM sugarfavorites WHERE created_by = '".$GLOBALS['current_user']->id."'");
        //END SUGARCRM flav=pro ONLY

        $this->_cleanUpRecords();
    }

    public function testSimpleFilter()
    {
        $reply = $this->_restCall('Accounts/filter?filter='.urlencode('[{"name":"TEST 7 Account"}]').'&fields=id,name');
        $this->assertEquals('TEST 7 Account',$reply['reply']['records'][0]['name'],'Simple: The name is not set correctly');
        $this->assertEquals(-1,$reply['reply']['next_offset'],'Simple: Next offset is not set correctly');
        $this->assertEquals(1,count($reply['reply']['records']),'Simple: Returned too many results');
    }

    public function testSimpleJoinFilter()
    {
        $reply = $this->_restCall('Accounts/filter?filter='.urlencode('[{"notes.name":"Test 3 Note"}]').'&fields=id,name');
        $this->assertEquals('TEST 3 Account',$reply['reply']['records'][0]['name'],'SimpleJoin: The account name is not set correctly');
        $this->assertEquals(-1,$reply['reply']['next_offset'],'SimpleJoin: Next offset is not set correctly');
        $this->assertEquals(1,count($reply['reply']['records']),'SimpleJoin: Returned too many results');
    }


    public function testSimpleFilterWithOffset()
    {
        $reply = $this->_restCall('Accounts/filter?filter='.urlencode('[{"name":{"$starts":"TEST 1"}}]').'&fields=id,name&max_num=5');
        $this->assertEquals(5,$reply['reply']['next_offset'],'Offset-1: Next offset is not set correctly');
        $this->assertEquals(5,count($reply['reply']['records']),'Offset-1: Returned too many results');

        $reply = $this->_restCall('Accounts/filter?filter='.urlencode('[{"name":{"$starts":"TEST 1"}}]').'&fields=id,name&max_num=5&offset=5');
        $this->assertEquals(10,$reply['reply']['next_offset'],'Offset-2: Next offset is not set correctly');
        $this->assertEquals(5,count($reply['reply']['records']),'Offset-2: Returned too many results');

        $reply = $this->_restCall('Accounts/filter?filter='.urlencode('[{"name":{"$starts":"TEST 1"}}]').'&fields=id,name&max_num=5&offset=10');
        $this->assertEquals(-1,$reply['reply']['next_offset'],'Offset-3: Next offset is not set correctly');
        $this->assertEquals(1,count($reply['reply']['records']),'Offset-3: Returned too many results');
    }

    public function testOrFilter()
    {
        $reply = $this->_restCall('Accounts/filter?filter='.urlencode('[{"$or":[{"name":"TEST 7 Account"},{"name":"TEST 17 Account"}]}]').'&fields=id,name&order_by=name:ASC');
        $this->assertEquals('TEST 17 Account',$reply['reply']['records'][0]['name'],'Or-1: The name is not set correctly');
        $this->assertEquals('TEST 7 Account',$reply['reply']['records'][1]['name'],'Or-2: The name is not set correctly');
        $this->assertEquals(-1,$reply['reply']['next_offset'],'Or: Next offset is not set correctly');
        $this->assertEquals(2,count($reply['reply']['records']),'Or: Returned too many results');
    }

    public function testAndFilter()
    {
        $reply = $this->_restCall('Accounts/filter?filter='.urlencode('[{"$and":[{"name":{"$starts":"TEST 1"}},{"billing_address_postalcode":"70210"}]}]').'&fields=id,name&order_by=name:ASC');
        $this->assertEquals('TEST 17 Account',$reply['reply']['records'][0]['name'],'And: The name is not set correctly');
        $this->assertEquals(-1,$reply['reply']['next_offset'],'And: Next offset is not set correctly');
        $this->assertEquals(1,count($reply['reply']['records']),'And: Returned too many results');
    }

    //BEGIN SUGARCRM flav=pro ONLY
    public function testFavoriteFilter()
    {
        $this->assertEquals('TEST 4 Account',$this->accounts[4]->name,'Favorites: Making sure the name is correct before favoriting.');
        
        $fav = new SugarFavorites();
        $fav->id = SugarFavorites::generateGUID('Accounts',$this->accounts[4]->id);
        $fav->new_with_id = true;
        $fav->module = 'Accounts';
        $fav->record_id = $this->accounts[4]->id;
        $fav->created_by = $GLOBALS['current_user']->id;
        $fav->assigned_user_id = $GLOBALS['current_user']->id;
        $fav->deleted = 0;
        $fav->save();

        $reply = $this->_restCall('Accounts/filter?filter='.urlencode('[{"$favorite":""}]').'&fields=id,name&order_by=name:ASC');
        $this->assertEquals('TEST 4 Account',$reply['reply']['records'][0]['name'],'Favorites: The name is not set correctly');
        $this->assertEquals(-1,$reply['reply']['next_offset'],'Favorites: Next offset is not set correctly');
        $this->assertEquals(1,count($reply['reply']['records']),'Favorites: Returned too many results');
        
    }

    public function testRelatedFavoriteFilter()
    {
        $this->assertEquals('TEST 0 Opportunity FOR 3 Account',$this->opps[6]->name,'FavRelated: Making sure the name is correct before favoriting.');
        
        $fav = new SugarFavorites();
        $fav->id = SugarFavorites::generateGUID('Opportunities',$this->opps[6]->id);
        $fav->new_with_id = true;
        $fav->module = 'Opportunities';
        $fav->record_id = $this->opps[6]->id;
        $fav->created_by = $GLOBALS['current_user']->id;
        $fav->assigned_user_id = $GLOBALS['current_user']->id;
        $fav->deleted = 0;
        $fav->save();

        $reply = $this->_restCall('Accounts/filter?filter='.urlencode('[{"$favorite":"opportunities"}]').'&fields=id,name&order_by=name:ASC');
        $this->assertEquals('TEST 3 Account',$reply['reply']['records'][0]['name'],'FavRelated: The name is not set correctly');
        $this->assertEquals(-1,$reply['reply']['next_offset'],'FavRelated: Next offset is not set correctly');
        $this->assertEquals(1,count($reply['reply']['records']),'FavRelated: Returned too many results');
        
    }
    public function testMultipleRelatedFavoriteFilter()
    {
        $this->assertEquals('TEST 0 Opportunity FOR 0 Account',$this->opps[0]->name,'FavMulRelated: Making sure the opp name is correct before favoriting.');

        $this->assertEquals('Test 4 Note',$this->notes[4]->name,'FavMulRelated: Making sure the note name is correct before favoriting.');
        
        $fav = new SugarFavorites();
        $fav->id = SugarFavorites::generateGUID('Opportunities',$this->opps[0]->id);
        $fav->new_with_id = true;
        $fav->module = 'Opportunities';
        $fav->record_id = $this->opps[0]->id;
        $fav->created_by = $GLOBALS['current_user']->id;
        $fav->assigned_user_id = $GLOBALS['current_user']->id;
        $fav->deleted = 0;
        $fav->save();

        $fav = new SugarFavorites();
        $fav->id = SugarFavorites::generateGUID('Notes',$this->notes[4]->id);
        $fav->new_with_id = true;
        $fav->module = 'Notes';
        $fav->record_id = $this->notes[4]->id;
        $fav->created_by = $GLOBALS['current_user']->id;
        $fav->assigned_user_id = $GLOBALS['current_user']->id;
        $fav->deleted = 0;
        $fav->save();

        $reply = $this->_restCall('Accounts/filter?filter='.urlencode('[{"$or":[{"$favorite":"opportunities"},{"$favorite":"notes"}]}]').'&fields=id,name&order_by=name:ASC');
        $this->assertEquals('TEST 0 Account',$reply['reply']['records'][0]['name'],'FavMulRelated: The first name is not set correctly');
        $this->assertEquals('TEST 4 Account',$reply['reply']['records'][1]['name'],'FavMulRelated: The second name is not set correctly');
        $this->assertEquals(-1,$reply['reply']['next_offset'],'FavMulRelated: Next offset is not set correctly');
        $this->assertEquals(2,count($reply['reply']['records']),'FavMulRelated: Returned too many results');
        
    }
    //BEGIN SUGARCRM flav=pro ONLY

    public function testOwnerFilter()
    {
        $this->assertEquals('TEST 7 Account',$this->accounts[7]->name,'Owner: Making sure the name is correct before ownering.');
        
        $this->accounts[7]->assigned_user_id = $GLOBALS['current_user']->id;
        $this->accounts[7]->save();

        $reply = $this->_restCall('Accounts/filter?filter='.urlencode('[{"$owner":""}]').'&fields=id,name&order_by=name:ASC');
        $this->assertEquals('TEST 7 Account',$reply['reply']['records'][0]['name'],'Owner: The name is not set correctly');
        $this->assertEquals(-1,$reply['reply']['next_offset'],'Owner: Next offset is not set correctly');
        $this->assertEquals(1,count($reply['reply']['records']),'Owner: Returned too many results');
        
    }

    public function testRelatedOwnerFilter()
    {
        $this->assertEquals('TEST 1 Opportunity FOR 3 Account',$this->opps[7]->name,'OwnerRelated: Making sure the name is correct before ownering.');

        $this->opps[7]->assigned_user_id = $GLOBALS['current_user']->id;
        $this->opps[7]->save();

        $reply = $this->_restCall('Accounts/filter?filter='.urlencode('[{"$owner":"opportunities"}]').'&fields=id,name&order_by=name:ASC');
        $this->assertEquals('TEST 3 Account',$reply['reply']['records'][0]['name'],'OwnerRelated: The name is not set correctly');
        $this->assertEquals(-1,$reply['reply']['next_offset'],'OwnerRelated: Next offset is not set correctly');
        $this->assertEquals(1,count($reply['reply']['records']),'OwnerRelated: Returned too many results');
        
    }

}