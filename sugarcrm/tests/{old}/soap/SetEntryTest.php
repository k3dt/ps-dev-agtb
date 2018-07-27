<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
require_once('vendor/nusoap//nusoap.php');

/**
 * @group bug43282
 */
class SetEntryTest extends SOAPTestCase
{
    public $_soapURL = null;
    private $_tsk = null;

    public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'] . '/service/v4_1/soap.php';
        parent::setUp();

        $this->_tsk = new Task();
        $this->_tsk->name = "Unit Test";
        $this->_tsk->save();
    }

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM tasks WHERE id = '{$this->_tsk->id}'");
        parent::tearDown();
    }

    /**
     * Ensure that when updating the team_id value for a bean that the team_set_id is not
     * populated into the team_id field if the team_id value is already set.
     *
     * @return void
     */
    public function testUpdateRecordsTeamID()
    {
        $privateTeamID = $GLOBALS['current_user']->getPrivateTeamID();

        $this->_login();
        $result = $this->_soapClient->call('set_entry',
            array(
                'session' => $this->_sessionId,
                'module' => 'Tasks',
                'name_value_list' => array(
                    array('name' => 'id', 'value' => $this->_tsk->id),
                    array('name' => 'team_id', 'value' => $privateTeamID),
                ),
            )
        );

        $modifiedTask = new Task();
        $modifiedTask->retrieve($this->_tsk->id);
        $this->assertEquals($privateTeamID, $modifiedTask->team_id);
    }
}