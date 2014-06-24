<?php
//FILE SUGARCRM flav=pro ONLY
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



require_once('modules/Trackers/store/TrackerSessionsDatabaseStore.php');
require_once('modules/Trackers/TrackerManager.php');

class Bug41051Test extends Sugar_PHPUnit_Framework_TestCase {

var $disabledMonitors;
var $isPaused;

public function setup()
{
	$trackerManager = TrackerManager::getInstance();
	$this->disabledMonitors = $trackerManager->getDisabledMonitors();
	$this->isPaused = $trackerManager->isPaused();
    $trackerManager->isPaused = false;
	$trackerManager->setDisabledMonitors(array());
    $GLOBALS['db']->query("DELETE FROM tracker_sessions WHERE session_id = 'Bug41051Test'");
}

public function tearDown()
{
/*	$trackerManager = TrackerManager::getInstance();
	$trackerManager->isPaused = $this->isPaused;
	$trackerManager->setDisabledMonitors($this->disabledMonitors);
*/
    $GLOBALS['db']->query("DELETE FROM tracker_sessions WHERE session_id = 'Bug41051Test'");
}

public function testTrackerSessionDatabaseStore()
{
	$trackerManager = TrackerManager::getInstance();
    $trackerManager->unPause();
	if($monitor = $trackerManager->getMonitor('tracker_sessions'))
	{
		$monitor->setValue('session_id', 'Bug41051Test');
		$monitor->setValue('user_id', 'Bug41051Test');
		$monitor->setValue('date_start', TimeDate::getInstance()->nowDb());
		$monitor->setValue('date_end', TimeDate::getInstance()->nowDb());
		$monitor->setValue('seconds', '10');
		$monitor->setValue('round_trips', 1);
		$monitor->setValue('active', 0);
		$trackerManager->saveMonitor($monitor, true);

		$seconds = $GLOBALS['db']->getOne("SELECT seconds FROM tracker_sessions WHERE session_id = 'Bug41051Test'");
		$this->assertEquals('10', $seconds, 'Assert that new database entry is created');

		$seconds = $GLOBALS['db']->query("UPDATE tracker_sessions SET seconds='10' WHERE session_id = 'Bug41051Test'");
		if($monitor = $trackerManager->getMonitor('tracker_sessions'))
		{
			$monitor->setValue('session_id', 'Bug41051Test');
			$monitor->setValue('round_trips', 0);
			$monitor->setValue('seconds', '');
			$trackerManager->save($monitor, true);

			$seconds = $GLOBALS['db']->getOne("SELECT seconds FROM tracker_sessions WHERE session_id = 'Bug41051Test'");
			$this->assertEquals('0', $seconds, 'Assert that new database entry is modified as expected');
		}

		$seconds = $GLOBALS['db']->query("UPDATE tracker_sessions SET seconds='10' WHERE session_id = 'Bug41051Test'");
		if($monitor = $trackerManager->getMonitor('tracker_sessions'))
		{
			$monitor->setValue('session_id', 'Bug41051Test');
			$monitor->setValue('round_trips', 0);
			$monitor->setValue('seconds', 0);
			$trackerManager->save($monitor, true);

			$seconds = $GLOBALS['db']->getOne("SELECT seconds FROM tracker_sessions WHERE session_id = 'Bug41051Test'");
			$this->assertEquals('0', $seconds, 'Assert that new database entry is modified as expected');
		}


	} else {
		$this->markTestSkipped = true;
	}
}

}