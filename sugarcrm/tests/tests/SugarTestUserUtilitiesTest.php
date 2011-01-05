<?php
require_once 'SugarTestUserUtilities.php';

class SugarTestUserUtilitiesTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_before_snapshot = array();
    
    public function setUp() 
    {
        $this->_before_snapshot = $this->_takeUserDBSnapshot();
    }

    public function tearDown() 
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function _takeUserDBSnapshot() 
    {
        $snapshot = array();
        $query = 'SELECT * FROM users';
        $result = $GLOBALS['db']->query($query);
        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            $snapshot[] = $row;
        }
        return $snapshot;
    }
    
    //BEGIN SUGARCRM flav=pro ONLY
    public function _takeTeamDBSnapshot() 
    {
        $snapshot = array();
        $query = 'SELECT * FROM teams';
        $result = $GLOBALS['db']->query($query);
        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            $snapshot[] = $row;
        }
        return $snapshot;
    }
    //END SUGARCRM flav=pro ONLY

    public function testCanCreateAnAnonymousUser() 
    {
        $user = SugarTestUserUtilities::createAnonymousUser();

        $this->assertInstanceOf('User', $user);

        $after_snapshot = $this->_takeUserDBSnapshot();
        $this->assertNotEquals($this->_before_snapshot, $after_snapshot, 
            "Simply insure that something was added");
    }

    public function testAnonymousUserHasARandomUserName() 
    {
        $first_user = SugarTestUserUtilities::createAnonymousUser();
        $this->assertTrue(!empty($first_user->user_name), 'team name should not be empty');

        $second_user = SugarTestUserUtilities::createAnonymousUser();
        $this->assertNotEquals($first_user->user_name, $second_user->user_name,
            'each user should have a unique name property');
    }

    public function testCanTearDownAllCreatedAnonymousUsers() 
    {
        //BEGIN SUGARCRM flav=pro ONLY
        $before_snapshot_teams = $this->_takeTeamDBSnapshot();
        //END SUGARCRM flav=pro ONLY
        for ($i = 0; $i < 5; $i++) {
            SugarTestUserUtilities::createAnonymousUser();
        }
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        
        $this->assertEquals($this->_before_snapshot, $this->_takeUserDBSnapshot(),
            'SugarTest_UserUtilities::removeAllCreatedAnonymousUsers() should have removed the users it added');
        //BEGIN SUGARCRM flav=pro ONLY
        $this->assertEquals($before_snapshot_teams, $this->_takeTeamDBSnapshot(),
            'SugarTest_UserUtilities::removeAllCreatedAnonymousUsers() should have removed the teams it added');
        //END SUGARCRM flav=pro ONLY
    }
}

