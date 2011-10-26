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
//FILE SUGARCRM flav=ent ONLY
class Bug46486Test extends Sugar_PHPUnit_Framework_TestCase
{

    private $sm;
    private $defaultPortalUsersCount;
    private $enforce;

    function setUp()
    {

        $admin = new Administration();
        $admin->retrieveSettings('license');

        if(!isset($admin->settings['license_num_portal_users']))
        {
           $admin->settings['license_num_portal_users'] = 0;
           $admin->saveSetting('license', 'num_portal_users', '0');
        }

        $this->sm = new SessionManager();
        $this->defaultPortalUsersCount = $this->sm->getNumPortalUsers();

        $admin->retrieveSettings('system');
        if(!isset($admin->settings['system_session_timeout']))
        {
           $session_timeout = abs(ini_get('session.gc_maxlifetime'));
           $admin->saveSetting('system', 'session_timeout', $session_timeout);
        }
        $admin->retrieveSettings('license');
        $this->enforce =  !empty($admin->settings['license_enforce_portal_user_limit']);

        $admin->saveSetting('license', 'enforce_portal_user_limit', '1');

        $admin->retrieveSettings(false, true);
        sugar_cache_clear('admin_settings_cache');
    }

    function tearDown()
    {
        $admin = new Administration();
        $admin->saveSetting('license', 'num_portal_users', $this->defaultPortalUsersCount);
        $admin->saveSetting('license', 'enforce_portal_user_limit', $this->enforce);

        //Remove any 'fake' sessions created.
        $query = "DELETE FROM {$this->sm->table_name}";
        $GLOBALS['db']->query($query);
    }

    function testGetActiveSessionCount()
    {
        $totalSessions = rand(0,10);
        $this->createFakeSessions($totalSessions);
        $this->assertEquals($totalSessions, $this->sm->getNumActiveSessions($totalSessions) );
    }

    function testGetNumPortalUsers()
    {
        $fakeCounts = array(200,5,398,102,234);
        foreach($fakeCounts as $count)
        {
            $admin = new Administration();
            $admin->saveSetting('license', 'num_portal_users', $count);
            $this->assertEquals($count, $this->sm->getNumPortalUsers() );
        }
    }

    private function createFakeSessions($totalSessionsToCreate)
    {
        for($i=0; $i<$totalSessionsToCreate; $i++)
        {
            $sm = new SessionManager();
            $sm->session_id = uniqid();
            $sm->save(FALSE);
        }
    }


    function providerPortalSessionLoginCount()
    {
        return array(
            //Valid
            array(1, 0, TRUE),
            array(2, 2, TRUE),
            array(3, 0, TRUE),
            array(5, 5, TRUE),
            array(10, 6, TRUE),
            array(100, 119, TRUE),
            //Invalid
            array(0,0, FALSE),
            array(0,1, FALSE),
            array(5,6, FALSE),
            array(100,120, FALSE),
            array(500,600, FALSE),
        );
    }

     /**
     *
     * @dataProvider providerPortalSessionLoginCount
     */
    function testPortalSessionLoginCount($systemPortalUsers, $activeSessions, $expectedResult)
    {
        $admin = new Administration();
        $admin->saveSetting('license', 'num_portal_users', $systemPortalUsers);

        $this->createFakeSessions($activeSessions);

        $this->assertEquals($expectedResult, $this->sm->canAddSession(), "Unable to add new session for portal users.License count: $systemPortalUsers, Active Sessions: $activeSessions");
    }

}