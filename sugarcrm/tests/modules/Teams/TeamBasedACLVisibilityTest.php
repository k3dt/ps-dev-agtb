<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once 'modules/Teams/include/TeamBasedACLConfigurator.php';

class TeamBasedACLVisibilityTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var TeamBasedACLConfigurator
     */
    protected $tbaConfig;

    /**
     * @var string
     */
    protected $module = 'Accounts';

    /**
     * @var TeamSet
     */
    protected $teamSet;

    /**
     * @var Team
     */
    protected $team;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var SugarBean
     */
    protected $bean;

    /**
     * @var boolean
     */
    protected $tbaGlobal;

    /**
     * @var boolean
     */
    protected $tbaModule;

    public function setUp()
    {
        SugarTestHelper::setUp('current_user', array(true, true));
        $this->tbaConfig = new TeamBasedACLConfigurator();
        $this->tbaGlobal = $this->tbaConfig->isEnabledGlobally();
        $this->tbaModule = $this->tbaConfig->isEnabledForModule($this->module);

        $this->tbaConfig->setGlobal(true);
        $this->tbaConfig->setForModule($this->module, true);

        $this->team = SugarTestTeamUtilities::createAnonymousTeam();
        $this->teamSet = BeanFactory::getBean('TeamSets');
        $this->teamSet->addTeams(array($this->team->id));

        $this->user = SugarTestUserUtilities::createAnonymousUser();
        $this->bean = SugarTestAccountUtilities::createAccount();
        $this->bean->team_set_selected_id = $this->teamSet->id;
        $this->bean->save();
    }

    public function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $this->teamSet->mark_deleted($this->teamSet->id);
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        $this->tbaConfig->setForModule($this->module, $this->tbaModule);
        $this->tbaConfig->setGlobal($this->tbaGlobal);
        SugarTestHelper::tearDown();
    }

    public function testVisibleRecord()
    {
        $this->team->add_user_to_team($this->user->id);
        $this->assertTrue($this->isBeanAvailableUsingFrom());
        $this->assertTrue($this->isBeanAvailableUsingWhere());
    }

    public function testInvisibleRecord()
    {
        $this->team->remove_user_from_team($this->user->id);
        $this->assertFalse($this->isBeanAvailableUsingFrom());
        $this->assertFalse($this->isBeanAvailableUsingWhere());
    }

    /**
     * Test that visibility affects implicitly assigned users.
     * Original user should receive a record that assigned to new user's private team.
     */
    public function testImplicitTeamMembership()
    {
        $newUser = SugarTestUserUtilities::createAnonymousUser();
        $privateTeamSet = BeanFactory::getBean('TeamSets');
        $privateTeamSet->addTeams(array($newUser->getPrivateTeamID()));

        $this->bean->team_set_selected_id = $privateTeamSet->id;
        $this->bean->save();

        $this->assertFalse($this->isBeanAvailableUsingFrom());
        $this->assertFalse($this->isBeanAvailableUsingWhere());

        // The user will appear in new user's private team.
        // If the user reported to another one he would get to the new user's team as well.
        $newUser->reports_to_id = $this->user->id;
        $newUser->save();

        $this->assertTrue($this->isBeanAvailableUsingFrom());
        $this->assertTrue($this->isBeanAvailableUsingWhere());
    }

    /**
     * Check possibility to retrieve a record with visibility's FROM part only.
     * @return boolean
     */
    protected function isBeanAvailableUsingFrom()
    {
        $oldCurrentUser = $GLOBALS['current_user'];
        $GLOBALS['current_user'] = $this->user;

        $sq = new SugarQuery();
        $sq->select(array('id'));
        $sq->from($this->bean);
        $sq->where()->equals('id', $this->bean->id);
        $result = $sq->execute();

        $GLOBALS['current_user'] = $oldCurrentUser;

        return empty($result) ? false : true;
    }

    /**
     * Check possibility to retrieve a record with visibility's WHERE part only.
     * @return boolean
     */
    protected function isBeanAvailableUsingWhere()
    {
        $oldCurrentUser = $GLOBALS['current_user'];
        $GLOBALS['current_user'] = $this->user;

        $this->bean->disable_row_level_security = false;
        $record = $this->bean->retrieve();
        $this->bean->disable_row_level_security = true;

        $GLOBALS['current_user'] = $oldCurrentUser;
        return $record ? true : false;
    }
}
