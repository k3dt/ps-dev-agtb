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

namespace Sugarcrm\SugarcrmTests\Socket;

use Sugarcrm\Sugarcrm\Socket\Client;

/**
 * Class SocketClientTest
 * @package Sugarcrm\SugarcrmTests\Socket
 * @covers Sugarcrm\Sugarcrm\Socket\Client
 */
class SocketClientTest extends \Sugar_PHPUnit_Framework_TestCase
{
    const SITE_URL = 'http://dummy-site';
    const SOCKET_SERVER_URL = 'http://dummy-socket-server';
    const TOKEN = 'token';

    const AUTH_TOKEN_HEADER = 'X-Auth-Token';
    const AUTH_VERSION_HEADER = 'X-Auth-Version';
    const AUTH_VERSION = 1;

    public $getLastResponseCallCount = 0;

    /** @var \Sugarcrm\Sugarcrm\Socket\HttpHelper|\PHPUnit_Framework_MockObject_MockObject */
    protected $httpHelper;

    /** @var Client|\PHPUnit_Framework_MockObject_MockObject */
    protected $client;

    /** @var \SugarConfig|\PHPUnit_Framework_MockObject_MockObject */
    protected $config;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->httpHelper = $this->getMock('Sugarcrm\Sugarcrm\Socket\HttpHelper');

        $this->client = $this->getMock(
            'Sugarcrm\Sugarcrm\Socket\Client',
            array('getHttpHelper', 'getSugarConfig')
        );
        $this->config = $this->getMock('SugarConfig');

        $this->client->method('getHttpHelper')->willReturn($this->httpHelper);
        $this->client->method('getSugarConfig')->willReturn($this->config);
    }

    /**
     * Data provider for testRecipient().
     *
     * @return array
     */
    public function recipientsProvider()
    {
        return array(
            'user' => array(
                array(
                    'url' => 'http://come.sugar.url.com/some/path',
                    'type' => Client::RECIPIENT_USER_ID,
                    'id' => 123,
                ),
            ),
            'team' => array(
                array(
                    'url' => 'http://come.sugar.url.com/some/path',
                    'type' => Client::RECIPIENT_TEAM_ID,
                    'id' => 456,
                ),
            ),
            'group' => array(
                array(
                    'url' => 'http://come.sugar.url.com/some/path',
                    'type' => Client::RECIPIENT_USER_TYPE,
                    'id' => 'admin',
                ),
            ),
            'channel_user' => array(
                array(
                    'url' => 'http://come.sugar.url.com/some/path',
                    'channel' => 'channel-home',
                    'type' => Client::RECIPIENT_USER_ID,
                    'id' => 123,
                ),
            ),
            'channel_team' => array(
                array(
                    'url' => 'http://come.sugar.url.com/some/path',
                    'channel' => 'channel-home',
                    'type' => Client::RECIPIENT_TEAM_ID,
                    'id' => 456,
                ),
            ),
            'channel_group' => array(
                array(
                    'url' => 'http://come.sugar.url.com/some/path',
                    'channel' => 'channel-home',
                    'type' => Client::RECIPIENT_USER_TYPE,
                    'id' => 'admin',
                ),
            ),
        );
    }

    /**
     * Data provider for testSendReturnValue().
     *
     * @return array
     */
    public function messageUrlsProvider()
    {
        return array(
            'invalidUrl' => array(false),
            'validUrl' => array(true),
        );
    }

    /**
     * Data provider for testSend().
     *
     * @return array
     */
    public function messageDataProvider()
    {
        return array(
            'messageToUser' => array(
                'test message',
                null,
            ),
            'messageWithData' => array(
                'test message',
                array(
                    'var1' => 123,
                    'var2' => 'test',
                ),
            ),
        );
    }

    /**
     * Tests socket server settings check with invalid url.
     * Socket server availability should be false.
     *
     * @covers Sugarcrm\Sugarcrm\Socket\Client::checkWSSettings
     */
    public function testCheckWSSettingsInvalidUrl()
    {
        $url = 'invalid-url';
        $expectedResult = array(
            'url' => $url,
            'available' => false,
            'type' => false,
            'isBalancer' => false,
        );

        $httpHelper = $this->getMock('Sugarcrm\Sugarcrm\Socket\HttpHelper');
        $httpHelper->expects($this->never())->method('ping');
        $httpHelper->expects($this->never())->method('send');

        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock('Sugarcrm\Sugarcrm\Socket\Client', array('getHttpHelper', 'getWSUrl'));
        $client->expects($this->once())->method('getHttpHelper')->willReturn($httpHelper);

        $actualResult = $client->checkWSSettings($url);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Tests socket server settings check with unreachable socket server url.
     *
     * @covers Sugarcrm\Sugarcrm\Socket\Client::checkWSSettings
     */
    public function testCheckWSSettingsUnreachableUrl()
    {
        $url = 'http://unreachable.host.com';
        $expectedResult = array(
            'url' => $url,
            'available' => false,
            'type' => false,
            'isBalancer' => false,
        );

        $httpHelper = $this->getMock('Sugarcrm\Sugarcrm\Socket\HttpHelper');
        $httpHelper->expects($this->once())
            ->method('ping')
            ->with($this->equalTo($url))
            ->willReturn(false);
        $httpHelper->expects($this->never())->method('send');

        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock('Sugarcrm\Sugarcrm\Socket\Client', array('getHttpHelper'));
        $client->expects($this->once())->method('getHttpHelper')->willReturn($httpHelper);

        $actualResult = $client->checkWSSettings($url);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Tests socket server settings check with valid and reachable url.
     * Socket server availability should be true.
     *
     * @covers Sugarcrm\Sugarcrm\Socket\Client::checkWSSettings
     */
    public function testCheckWSSettingsValidClientUrl()
    {
        $url = 'http://dummy.net';
        $remoteData = array(
            'type' => 'client',
        );
        $expectedResult = array(
            'url' => $url,
            'type' => 'client',
            'available' => true,
            'isBalancer' => false,
        );

        $httpHelper = $this->getMock('Sugarcrm\Sugarcrm\Socket\HttpHelper');
        $httpHelper->expects($this->once())
            ->method('ping')
            ->with($this->equalTo($url))
            ->willReturn(true);
        $httpHelper->expects($this->once())->method('send')
            ->with($this->equalTo('get'), $this->equalTo($url))
            ->willReturn(true);
        $httpHelper->expects($this->once())->method('getLastResponse')
            ->willReturn($remoteData);

        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock('Sugarcrm\Sugarcrm\Socket\Client', array('getHttpHelper'));
        $client->expects($this->once())->method('getHttpHelper')->willReturn($httpHelper);

        $actualResult = $client->checkWSSettings($url);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Tests socket server settings check with valid and reachable url.
     * Socket server availability should be true.
     *
     * @covers Sugarcrm\Sugarcrm\Socket\Client::checkWSSettings
     */
    public function testCheckWSSettingsValidServerUrl()
    {
        $url = 'http://dummy.net';
        $remoteData = array(
            'type' => 'server',
        );
        $expectedResult = array(
            'url' => $url,
            'type' => 'server',
            'available' => true,
            'isBalancer' => false,
        );

        $httpHelper = $this->getMock('Sugarcrm\Sugarcrm\Socket\HttpHelper');
        $httpHelper->expects($this->once())
            ->method('ping')
            ->with($this->equalTo($url))
            ->willReturn(true);
        $httpHelper->expects($this->once())->method('send')
            ->with($this->equalTo('get'), $this->equalTo($url))
            ->willReturn(true);
        $httpHelper->expects($this->once())->method('getLastResponse')
            ->willReturn($remoteData);

        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock('Sugarcrm\Sugarcrm\Socket\Client', array('getHttpHelper'));
        $client->expects($this->once())->method('getHttpHelper')->willReturn($httpHelper);

        $actualResult = $client->checkWSSettings($url);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Tests socket server settings check with invalid socket server type in response.
     *
     * @covers Sugarcrm\Sugarcrm\Socket\Client::checkWSSettings
     */
    public function testCheckWSSettingsInvalidType()
    {
        $url = 'http://dummy.net';
        $remoteData = array(
            'type' => 'invalid',
        );
        $expectedResult = array(
            'url' => $url,
            'type' => false,
            'available' => false,
            'isBalancer' => false,
        );

        $httpHelper = $this->getMock('Sugarcrm\Sugarcrm\Socket\HttpHelper');
        $httpHelper->expects($this->once())->method('ping')->with($this->equalTo($url))->willReturn(true);
        $httpHelper->expects($this->once())->method('send')
            ->with($this->equalTo('get'), $this->equalTo($url))
            ->willReturn(true);
        $httpHelper->expects($this->once())->method('getLastResponse')
            ->willReturn($remoteData);

        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock('Sugarcrm\Sugarcrm\Socket\Client', array('getHttpHelper'));
        $client->expects($this->once())->method('getHttpHelper')->willReturn($httpHelper);

        $actualResult = $client->checkWSSettings($url);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Tests socket server settings check with invalid socket server response.
     *
     * @covers Sugarcrm\Sugarcrm\Socket\Client::checkWSSettings
     */
    public function testCheckWSSettingsInvalidResponse()
    {
        $url = 'http://dummy.net/invalid_response';
        $remoteData = 'invalid response';
        $expectedResult = array(
            'url' => $url,
            'type' => false,
            'available' => false,
            'isBalancer' => false,
        );

        $httpHelper = $this->getMock('Sugarcrm\Sugarcrm\Socket\HttpHelper');
        $httpHelper->expects($this->once())
            ->method('ping')
            ->with($this->equalTo($url))
            ->willReturn(true);
        $httpHelper->expects($this->once())->method('send')
            ->with($this->equalTo('get'), $this->equalTo($url))
            ->willReturn(true);
        $httpHelper->expects($this->once())->method('getLastResponse')
            ->willReturn($remoteData);

        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock('Sugarcrm\Sugarcrm\Socket\Client', array('getHttpHelper'));
        $client->expects($this->once())->method('getHttpHelper')->willReturn($httpHelper);

        $actualResult = $client->checkWSSettings($url);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Tests socket server settings check with balanced urls.
     *
     * @covers Sugarcrm\Sugarcrm\Socket\Client::checkWSSettings
     */
    public function testCheckWSSettingsBalancerValid()
    {
        $url = 'http://balanced.dummy.net';
        $finalUrl = 'http://dummy.net';
        $balancerResponse = array(
            'type' => 'balancer',
            'location' => $finalUrl,
        );
        $finalResponse = array(
            'type' => 'client',
        );
        $expectedResult = array(
            'url' => $url,
            'type' => 'client',
            'available' => true,
            'isBalancer' => true,
        );

        $httpHelper = $this->getMock('Sugarcrm\Sugarcrm\Socket\HttpHelper');
        $httpHelper->expects($this->once())
            ->method('ping')
            ->with($this->equalTo($url))
            ->willReturn(true);

        $httpHelper->expects($this->exactly(2))->method('send')->willReturnMap(array(
            array('get', $url, '', array(), true),
            array('get', $balancerResponse['location'], '', array(), true),
        ));

        $this->getLastResponseCallCount = 0;
        $httpHelper->expects($this->exactly(2))->method('getLastResponse')
            ->willReturnCallback(function () use ($balancerResponse, $finalResponse) {
                $this->getLastResponseCallCount++;
                switch ($this->getLastResponseCallCount) {
                    case 1:
                        return $balancerResponse;
                    case 2:
                        return $finalResponse;
                }
                return false;
            });

        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock('Sugarcrm\Sugarcrm\Socket\Client', array('getHttpHelper'));
        $client->expects($this->once())->method('getHttpHelper')->willReturn($httpHelper);

        $actualResult = $client->checkWSSettings($url);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Tests socket server settings check with invalid server response at balanced url.
     *
     * @covers Sugarcrm\Sugarcrm\Socket\Client::checkWSSettings
     */
    public function testCheckWSSettingsBalancerInvalidResponse()
    {
        $url = 'http://balanced.dummy.net';
        $balancerResponse = array(
            'type' => 'balancer',
            'location' => 'http://dummy.net/invalid_response',
        );
        $expectedResult = array(
            'url' => $url,
            'available' => false,
            'type' => false,
            'isBalancer' => true,
        );

        $httpHelper = $this->getMock('Sugarcrm\Sugarcrm\Socket\HttpHelper');
        $httpHelper->expects($this->once())
            ->method('ping')
            ->with($this->equalTo($url))
            ->willReturn(true);

        $httpHelper->expects($this->exactly(2))->method('send')->willReturnMap(array(
            array('get', $url, '', array(), true),
            array('get', $balancerResponse['location'], '', array(), true),
        ));

        $this->getLastResponseCallCount = 0;
        $httpHelper->expects($this->exactly(2))->method('getLastResponse')
            ->willReturnCallback(function () use ($balancerResponse) {
                $this->getLastResponseCallCount++;
                switch ($this->getLastResponseCallCount) {
                    case 1:
                        return $balancerResponse;
                    case 2:
                        return 'invalid response data';
                }
                return false;
            });

        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock('Sugarcrm\Sugarcrm\Socket\Client', array('getHttpHelper'));
        $client->expects($this->once())->method('getHttpHelper')->willReturn($httpHelper);

        $actualResult = $client->checkWSSettings($url);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Tests if send() method returns correct operation status.
     *
     * @dataProvider messageUrlsProvider
     * @covers Sugarcrm\Sugarcrm\Socket\Client::send
     * @param bool $expectedResult - expected response from send() method
     */
    public function testSendReturnValue($expectedResult)
    {
        $messageToSend = 'dummy message';

        $httpHelper = $this->getMock('Sugarcrm\Sugarcrm\Socket\HttpHelper');
        $httpHelper->expects($this->atLeastOnce())->method('send')->willReturn($expectedResult);

        $config = $this->getMock('SugarConfig');
        $config->method('get')->willReturnMap(array(
            array('websockets.server.url', null, SocketClientTest::SOCKET_SERVER_URL),
            array('site_url', null, SocketClientTest::SITE_URL),
        ));

        $adminBean = $this->getMockBuilder('Administration')
            ->disableOriginalConstructor()
            ->getMock();
        $adminBean->method('getConfigForModule')->willReturnMap(array(
            array('auth', 'base', true, array('external_token_socket' => static::TOKEN)),
        ));

        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock(
            'Sugarcrm\Sugarcrm\Socket\Client',
            array('getHttpHelper', 'getSugarConfig', 'getAdministrationBean')
        );
        $client->method('getAdministrationBean')->willReturn($adminBean);
        $client->method('getHttpHelper')->willReturn($httpHelper);
        $client->method('getSugarConfig')->willReturn($config);

        $actualResult = $client->send($messageToSend);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Tests sending message with data payload to socket server.
     *
     * @dataProvider messageDataProvider
     * @covers Sugarcrm\Sugarcrm\Socket\Client::send
     * @param string $message - message to be sent
     * @param array|null $args - data payload to be sent
     */
    public function testSend($message, $args)
    {
        $httpHelper = $this->getMock('Sugarcrm\Sugarcrm\Socket\HttpHelper');
        $httpHelper->expects($this->once())
            ->method('send')
            ->with(
                $this->equalTo('post'),
                $this->anything(),
                $this->callback(function ($val) use ($message, $args) {
                    $data = json_decode($val, true);
                    $messagePassed = (isset($data['data']['message']) && $message == $data['data']['message']);
                    $argsPassed = (array_key_exists('args', $data['data']) && $args == $data['data']['args']);
                    return $messagePassed && $argsPassed;
                })
            );

        $config = $this->getMock('SugarConfig');
        $config->method('get')->willReturnMap(array(
            array('websockets.server.url', null, SocketClientTest::SOCKET_SERVER_URL),
            array('site_url', null, SocketClientTest::SITE_URL),
        ));

        $adminBean = $this->getMockBuilder('Administration')
            ->disableOriginalConstructor()
            ->getMock();
        $adminBean->method('getConfigForModule')->willReturnMap(array(
            array('auth', 'base', true, array('external_token_socket' => static::TOKEN)),
        ));

        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock(
            'Sugarcrm\Sugarcrm\Socket\Client',
            array('getHttpHelper', 'getSugarConfig', 'getAdministrationBean')
        );
        $client->method('getAdministrationBean')->willReturn($adminBean);
        $client->method('getHttpHelper')->willReturn($httpHelper);
        $client->method('getSugarConfig')->willReturn($config);

        $client->send($message, $args);
    }

    /**
     * Tests sending message depending is Socket server configured.
     *
     * @param boolean $isConfigured
     * @param boolean $send
     * @param boolean $returned
     * @dataProvider providerCheckIsConfigured
     * @covers Sugarcrm\Sugarcrm\Socket\Client::send
     */
    public function testSendIsConfigured($isConfigured, $send, $returned)
    {
        $httpHelper = $this->getMock('Sugarcrm\Sugarcrm\Socket\HttpHelper');
        $httpHelper->expects($this->exactly($send ? 1 : 0))->method('send')->willReturn(true);

        $config = $this->getMock('SugarConfig');
        $config->method('get')->willReturnMap(array(
            array('websockets.server.url', null, $isConfigured ? SocketClientTest::SOCKET_SERVER_URL : ''),
            array('site_url', null, SocketClientTest::SITE_URL),
        ));

        $adminBean = $this->getMockBuilder('Administration')
            ->disableOriginalConstructor()
            ->getMock();
        $adminBean->method('getConfigForModule')->willReturnMap(array(
            array('auth', 'base', true, array('external_token_socket' => static::TOKEN)),
        ));

        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock(
            'Sugarcrm\Sugarcrm\Socket\Client',
            array('getHttpHelper', 'getSugarConfig', 'getAdministrationBean')
        );
        $client->method('getAdministrationBean')->willReturn($adminBean);
        $client->method('getHttpHelper')->willReturn($httpHelper);
        $client->method('getSugarConfig')->willReturn($config);

        $this->assertEquals($returned, $client->send('test message'));
    }

    /**
     * (is Socket server configured, is HttpHelper::send() called, returned).
     * @return array
     */
    public function providerCheckIsConfigured()
    {
        return array(
            'Socket server is not configured' => array(false, false, false),
            'Socket server is configured' => array(true, true, true),
        );
    }

    /**
     * Tests correct recipient transfer to httpHelper.
     *
     * @dataProvider recipientsProvider
     * @param array $expectedTo
     */
    public function testRecipient($expectedTo)
    {
        $message = 'test-message';

        $httpHelper = $this->getMock('Sugarcrm\Sugarcrm\Socket\HttpHelper');
        $httpHelper->expects($this->once())
            ->method('send')
            ->with(
                $this->equalTo('post'),
                $this->anything(),
                $this->callback(function ($val) use ($expectedTo) {
                    $actualData = json_decode($val, true);
                    return 0 == count(array_diff($expectedTo, $actualData['to']));
                })
            );

        $config = $this->getMock('SugarConfig');
        $config->method('get')
            ->will($this->returnCallback(function ($arg) use ($expectedTo) {
                $map = array(
                    'site_url' => $expectedTo['url'],
                    'websockets.server.url' => 'http://someValue',
                );
                return $map[$arg];
            }));

        $adminBean = $this->getMockBuilder('Administration')
            ->disableOriginalConstructor()
            ->getMock();
        $adminBean->method('getConfigForModule')->willReturnMap(array(
            array('auth', 'base', true, array('external_token_socket' => static::TOKEN)),
        ));

        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock(
            'Sugarcrm\Sugarcrm\Socket\Client',
            array('getHttpHelper', 'getSugarConfig', 'getAdministrationBean')
        );
        $client->recipient($expectedTo['type'], $expectedTo['id']);
        if (isset($expectedTo['channel']) && $expectedTo['channel']) {
            $client->channel($expectedTo['channel']);
        }
        $client->method('getAdministrationBean')->willReturn($adminBean);
        $client->method('getHttpHelper')->willReturn($httpHelper);
        $client->method('getSugarConfig')->willReturn($config);

        $client->send($message);
    }

    /**
     * Tests correct default recipient transfer to httpHelper.
     */
    public function testDefaultRecipient()
    {
        $message = 'test-message';
        $actualData = '';

        $httpHelper = $this->getMock('Sugarcrm\Sugarcrm\Socket\HttpHelper');
        $httpHelper->expects($this->once())
            ->method('send')
            ->with(
                $this->equalTo('post'),
                $this->anything(),
                $this->callback(function ($val) use ($actualData) {
                    $actualData = json_decode($val, true);
                    return $actualData['to']['type'] == Client::RECIPIENT_ALL
                    && is_null($actualData['to']['id'])
                    && is_null($actualData['to']['channel']);
                })
            );

        $config = $this->getMock('SugarConfig');
        $config->method('get')->willReturnMap(array(
            array('websockets.server.url', null, SocketClientTest::SOCKET_SERVER_URL),
            array('site_url', null, SocketClientTest::SITE_URL),
        ));

        $adminBean = $this->getMockBuilder('Administration')
            ->disableOriginalConstructor()
            ->getMock();
        $adminBean->method('getConfigForModule')->willReturnMap(array(
            array('auth', 'base', true, array('external_token_socket' => static::TOKEN)),
        ));

        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock(
            'Sugarcrm\Sugarcrm\Socket\Client',
            array('getHttpHelper', 'getSugarConfig', 'getAdministrationBean')
        );
        $client->method('getAdministrationBean')->willReturn($adminBean);
        $client->method('getHttpHelper')->willReturn($httpHelper);
        $client->method('getSugarConfig')->willReturn($config);

        $client->send($message);
    }

    /**
     * Tests getInstance() factory method.
     *
     * @covers Sugarcrm\Sugarcrm\Socket\Client::getInstance
     */
    public function testGetInstance()
    {
        $this->assertInstanceOf('Sugarcrm\Sugarcrm\Socket\Client', Client::getInstance());
    }

    /**
     * Tests getInstance() factory method resets class properties to default values.
     *
     * @covers Sugarcrm\Sugarcrm\Socket\Client::getInstance
     */
    public function testGetInstanceResetsItself()
    {
        $expected = array(
            'type' => Client::RECIPIENT_ALL,
            'id' => null,
            'channel' => null,
        );

        $client = Client::getInstance();
        $client->recipient(Client::RECIPIENT_TEAM_ID, 'team-id');
        $client->channel('channel-id');

        $anotherClient = Client::getInstance();
        $this->assertAttributeEquals($expected, 'to', $anotherClient);
    }

    /**
     * Tests getHttpHelper() factory method.
     *
     * @covers Sugarcrm\Sugarcrm\Socket\Client::getHttpHelper
     */
    public function testGetHttpHelper()
    {
        $this->assertInstanceOf(
            'Sugarcrm\Sugarcrm\Socket\HttpHelper',
            \SugarTestReflection::callProtectedMethod(new Client(), 'getHttpHelper')
        );
    }

    /**
     * Tests getAdministrationBean() factory method.
     *
     * @covers Sugarcrm\Sugarcrm\Socket\Client::getAdministrationBean
     */
    public function testGetAdministrationBean()
    {
        $this->assertInstanceOf(
            'Administration',
            \SugarTestReflection::callProtectedMethod(new Client(), 'getAdministrationBean')
        );
    }

    /**
     * Tests getSugarConfig() factory method.
     *
     * @covers Sugarcrm\Sugarcrm\Socket\Client::getSugarConfig
     */
    public function testGetSugarConfig()
    {
        $this->assertInstanceOf(
            'SugarConfig',
            \SugarTestReflection::callProtectedMethod(new Client(), 'getSugarConfig')
        );
    }

    /**
     * Tests auth token retrieve (token is stored in DB).
     *
     * @covers Sugarcrm\Sugarcrm\Socket\Client::retrieveToken
     */
    public function testRetrieveToken()
    {
        $config = array(
            'external_token_socket' => 'sample-token',
        );

        $admin = $this->getMockBuilder('Administration')
            ->disableOriginalConstructor()
            ->getMock();
        $admin->expects($this->atLeastOnce())->method('getConfigForModule')->willReturn($config);
        $admin->expects($this->never())->method('saveSetting');

        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock('Sugarcrm\Sugarcrm\Socket\Client', array('getAdministrationBean'));
        $client->expects($this->atLeastOnce())->method('getAdministrationBean')->willReturn($admin);

        $token = \SugarTestReflection::callProtectedMethod($client, 'retrieveToken');
        $this->assertEquals($config['external_token_socket'], $token);
    }

    /**
     * Code run in daemon, should not use cache in memory.
     */
    public function testIsClearSettingsCache()
    {
        $config = array(
            'external_token_socket' => 'sample-token',
        );
        $admin = $this->getMockBuilder('Administration')
            ->disableOriginalConstructor()
            ->getMock();
        $admin->expects($this->atLeastOnce())
            ->method('getConfigForModule')
            ->with($this->equalTo('auth'), $this->equalTo('base'), $this->isTrue())
            ->willReturn($config);

        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock('Sugarcrm\Sugarcrm\Socket\Client', array('getAdministrationBean'));
        $client->expects($this->atLeastOnce())->method('getAdministrationBean')->willReturn($admin);

        \SugarTestReflection::callProtectedMethod($client, 'retrieveToken');
    }

    /**
     * Tests auth token retrieve (token is generated).
     *
     * @covers Sugarcrm\Sugarcrm\Socket\Client::retrieveToken
     */
    public function testGenerateToken()
    {
        $config = array(
            'external_token_socket' => '',
        );
        $token = '';

        $admin = $this->getMockBuilder('Administration')
            ->disableOriginalConstructor()
            ->getMock();
        $admin->expects($this->once())
            ->method('saveSetting')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->callback(function ($val) use (&$token) {
                    $token = $val;
                    return !empty($token);
                }),
                $this->anything()
            );
        $admin->expects($this->atLeastOnce())->method('getConfigForModule')->willReturn($config);

        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock('Sugarcrm\Sugarcrm\Socket\Client', array('getAdministrationBean'));
        $client->expects($this->atLeastOnce())->method('getAdministrationBean')->willReturn($admin);

        $newToken = \SugarTestReflection::callProtectedMethod($client, 'retrieveToken');
        $this->assertEquals($token, $newToken);
    }

    /**
     * Tests correct auth token transfer to httpHelper.
     */
    public function testUseToken()
    {
        $dummyToken = 'test-token';

        $httpHelper = $this->getMock('Sugarcrm\Sugarcrm\Socket\HttpHelper');
        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock(
            'Sugarcrm\Sugarcrm\Socket\Client',
            array('getHttpHelper', 'getSugarConfig', 'retrieveToken')
        );

        $config = $this->getMock('SugarConfig');

        $config->method('get')->will($this->returnValueMap(array(
            array('websockets.server.url', null, SocketClientTest::SOCKET_SERVER_URL),
            array('site_url', null, SocketClientTest::SITE_URL),

        )));;

        $client->expects($this->any())->method('getHttpHelper')->willReturn($httpHelper);
        $client->expects($this->any())->method('getSugarConfig')->willReturn($config);
        $client->expects($this->any())->method('retrieveToken')->willReturn($dummyToken);

        $test = $this;
        $headers = array(
            SocketClientTest::AUTH_TOKEN_HEADER . ': ' . $dummyToken,
            SocketClientTest::AUTH_VERSION_HEADER . ': ' . SocketClientTest::AUTH_VERSION,
        );

        $httpHelper->expects($this->once())
            ->method('send')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->callback(function ($val) use ($headers, $test) {
                    $test->assertEquals($headers, $val);
                    return true;
                })
            );

        $config = $this->getMock('SugarConfig');
        $config->method('get')->willReturnMap(array(
            array('websockets.server.url', null, SocketClientTest::SOCKET_SERVER_URL),
            array('site_url', null, SocketClientTest::SITE_URL),
        ));

        $adminBean = $this->getMockBuilder('Administration')
            ->disableOriginalConstructor()
            ->getMock();
        $adminBean->method('getConfigForModule')->willReturnMap(array(
            array('auth', 'base', true, array('external_token_socket' => $dummyToken)),
        ));

        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock(
            'Sugarcrm\Sugarcrm\Socket\Client',
            array('getHttpHelper', 'getSugarConfig', 'getAdministrationBean')
        );
        $client->method('getAdministrationBean')->willReturn($adminBean);
        $client->method('getHttpHelper')->willReturn($httpHelper);
        $client->method('getSugarConfig')->willReturn($config);

        $client->send('test');
    }

    /**
     * Tests correct sugar instance url transfer to httpHelper.
     */
    public function testInstanceUrl()
    {
        $url = 'http://dummy.net';

        $httpHelper = $this->getMock('Sugarcrm\Sugarcrm\Socket\HttpHelper');
        $httpHelper->expects($this->once())
            ->method('send')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->callback(function ($val) use ($url) {
                    $actualData = json_decode($val, true);
                    return $actualData['to']['url'] == $url;
                }),
                $this->anything()
            );

        $config = $this->getMock('SugarConfig');
        $config->method('get')->willReturnMap(array(
            array('websockets.server.url', null, SocketClientTest::SOCKET_SERVER_URL),
            array('site_url', null, $url),
        ));

        $adminBean = $this->getMockBuilder('Administration')
            ->disableOriginalConstructor()
            ->getMock();
        $adminBean->method('getConfigForModule')->willReturnMap(array(
            array('auth', 'base', true, array('external_token_socket' => static::TOKEN)),
        ));

        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock(
            'Sugarcrm\Sugarcrm\Socket\Client',
            array('getHttpHelper', 'getSugarConfig', 'getAdministrationBean')
        );
        $client->method('getAdministrationBean')->willReturn($adminBean);
        $client->method('getHttpHelper')->willReturn($httpHelper);
        $client->method('getSugarConfig')->willReturn($config);

        $client->send('test');
    }

    /**
     * Data provider for test testWebSocketServerUrl.
     *
     * @see testWebSocketServerUrl
     * @return array
     */
    public function webSocketServerUrlProvider()
    {
        return array(
            'serverUrlWithoutPortWithSlash' => array(
                'serverUrl' => 'http://server.dummy/',
                'expectedUrl' => 'http://server.dummy/forward',
            ),
            'serverUrlWithoutPortWithoutSlash' => array(
                'serverUrl' => 'http://server.dummy',
                'expectedUrl' => 'http://server.dummy/forward',
            ),
            'serverUrlWithPortAndWithSlash' => array(
                'serverUrl' => 'http://server.dummy:2999/',
                'expectedUrl' => 'http://server.dummy:2999/forward',
            ),
            'serverUrlWithPortAndWithoutSlash' => array(
                'serverUrl' => 'http://server.dummy:2999',
                'expectedUrl' => 'http://server.dummy:2999/forward',
            ),
            'serverUrlWithPortAndWithPathAndWithSlash' => array(
                'serverUrl' => 'http://server.dummy:2999/test/',
                'expectedUrl' => 'http://server.dummy:2999/test/forward',
            ),
            'serverUrlWithPortAndWithPathAndWithoutSlash' => array(
                'serverUrl' => 'http://server.dummy:2999/test',
                'expectedUrl' => 'http://server.dummy:2999/test/forward',
            ),
        );
    }

    /**
     * Tests correct socket server url transfer to httpHelper.
     *
     * @dataProvider webSocketServerUrlProvider
     * @param string $serverUrl
     * @param string $expectedUrl
     */
    public function testWebSocketServerUrl($serverUrl, $expectedUrl)
    {
        $this->config->method('get')
            ->willReturnMap(array(
                array('websockets.server.url', null, $serverUrl),
                array('site_url', null, SocketClientTest::SITE_URL),
            ));
        $this->httpHelper->expects($this->once())
            ->method('send')
            ->with($this->anything(), $this->equalTo($expectedUrl), $this->anything(), $this->anything());

        $this->client->send('test');
    }
}
