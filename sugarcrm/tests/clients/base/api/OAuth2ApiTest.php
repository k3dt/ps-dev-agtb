<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
require_once 'include/api/RestService.php';
require_once 'clients/base/api/OAuth2Api.php';

class OAuth2ApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_SESSION = array();
        parent::setUp();
    }

    public function tearDown()
    {
        $_SESSION = array();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testSudo()
    {
        $stdArgs = array('user_name'=>'unit_test_user',
                         'client_id'=>'sugar',
                         'platform'=>'base',
        );

        // Non-admin attempting to sudo
        $service = $this->getMock('RestService');
        $service->user = $this->getMock('User',array('isAdmin'));
        $service->user->expects($this->once())
            ->method('isAdmin')
            ->will($this->returnValue(false));

        $api = $this->getMock('OAuth2Api',array('getOAuth2Server'));
        $api->expects($this->never())
            ->method('getOAuth2Server');
        
        $caughtException = false;
        try {
            $api->sudo($service,$stdArgs);
        } catch ( SugarApiExceptionNotAuthorized $e ) {
            $caughtException = true;
        }
        $this->assertTrue($caughtException,'Did not deny a non-admin user from sudoing');

        // Admin user that is already being sudo-ed
        $service->user = $this->getMock('User',array('isAdmin'));
        $service->user->expects($this->any())
            ->method('isAdmin')
            ->will($this->returnValue(true));
        $_SESSION['sudo_for'] = 'other_unit_test_user';

        $caughtException = false;
        try {
            $api->sudo($service,$stdArgs);
        } catch ( SugarApiExceptionNotAuthorized $e ) {
            $caughtException = true;
        }
        $this->assertTrue($caughtException,'Did not deny an already sudoed user from sudoing');
        $_SESSION = array();

        // Deny the oauth2 request
        $oauth2 = $this->getMock('stdClass',array('getSudoToken'));
        $oauth2->expects($this->once())
            ->method('getSudoToken')
            ->will($this->returnValue(false));
        
        $api = $this->getMock('OAuth2Api',array('getOAuth2Server'));
        $api->expects($this->once())
            ->method('getOAuth2Server')
            ->will($this->returnValue($oauth2));
        
        $caughtException = false;
        try {
            $api->sudo($service,$stdArgs);
        } catch ( SugarApiExceptionRequestMethodFailure $e ) {
            $caughtException = true;
        }
        $this->assertTrue($caughtException,'Did not fail when the token was false');
        
        // Try a successful run
        $oauth2 = $this->getMock('stdClass',array('getSudoToken'));
        $oauth2->expects($this->once())
            ->method('getSudoToken')
            ->will($this->returnValue(array('access_token'=>'i_am_only_a_test')));
        
        $api = $this->getMock('OAuth2Api',array('getOAuth2Server'));
        $api->expects($this->once())
            ->method('getOAuth2Server')
            ->will($this->returnValue($oauth2));
        
        $ret = $api->sudo($service, $stdArgs);
    }

    /**
     * @param array $info
     * @param boolean $expected
     * @param string $message
     *
     * @dataProvider clientVersionProvider
     */
    public function testIsSupportedClientVersion(array $info, $expected, $message)
    {
        $service = $this->getMock('RestService');
        $service->api_settings = array(
            'minClientVersions' => array(
                'the-client' => '1.2.0',
            ),
        );
        $api = new OAuth2Api();

        $ret = $api->isSupportedClientVersion($service, $info);
        $this->assertEquals($expected, $ret, $message);
    }

    public static function clientVersionProvider()
    {
        return array(
            array(
                array(
                    'some' => 'things',
                    'keep' => 'happening',
                ),
                true,
                'Check client version was pleased by the lack of version'
            ),
            array(
                array(
                    'client_info' => array(
                        'app' => array(
                            'name' => 'the-client',
                            'version' => '1.0.1',
                        )
                    )
                ),
                false,
                'Returned true on an out of date client'
            ),
            array(
                array(
                    'client_info' => array(
                        'app' => array(
                            'name' => 'the-client',
                            'version' => '1.2.0',
                        )
                    )
                ),
                true,
                'Returned false on an up to date client'
            ),
        );
    }
}
