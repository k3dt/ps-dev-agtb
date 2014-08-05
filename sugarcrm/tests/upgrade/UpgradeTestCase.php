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

require_once 'modules/UpgradeWizard/TestUpgrader.php';

abstract class UpgradeTestCase extends Sugar_PHPUnit_Framework_TestCase
{

    protected $upgrader;
    /**
     * admin user
     * @var User
     */
    static protected $admin;

    public static function setUpBeforeClass()
    {
        // create admin user
        self::$admin = SugarTestUserUtilities::createAnonymousUser(true, 1);
        $GLOBALS['current_user'] = static::$admin;
    }

    public static function tearDownAfterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

	public function setUp()
	{
	    $this->upgrader = new TestUpgrader(self::$admin);
	    SugarTestHelper::setUp("files");
	}

	public function tearDown()
	{
	    $this->upgrader->cleanState();
	    $this->upgrader->cleanDir($this->upgrader->getTempDir());
	    SugarTestHelper::tearDown();
	}
}
