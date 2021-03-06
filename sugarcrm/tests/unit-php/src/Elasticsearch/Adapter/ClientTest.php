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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Adapter;

use Elastica\Response;
use Exception;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Client;
use Sugarcrm\Sugarcrm\Elasticsearch\Logger;
use Sugarcrm\SugarcrmTestsUnit\TestMockHelper;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Client
 */
class ClientTest extends TestCase
{
    protected $config = ['host' => 'localhost', 'port' => '9200'];
    protected $logger;
    /**
     * @covers ::__construct
     * @covers ::setLogger
     * @covers ::parseConfig
     */
    public function testConstructor()
    {
        $client = $this->getTestClient();
        $this->assertSame($this->logger, TestReflection::getProtectedValue($client, '_logger'));
    }

    /**
     * @covers ::setConfig
     * @covers ::getConfig
     * @covers ::getVersion
     * @covers ::getAllowedVersions
     *
     * @dataProvider providerTestSettersAndGetters
     */
    public function testSettersAndGetters($version)
    {
        $client = $this->getTestClient();
        $client->setConfig($this->config);

        $this->assertSame($this->config['host'], $client->getConfig()['host']);
        $this->assertSame($this->config['host'], $client->getConfig('host'));
        $this->assertSame($this->config['port'], $client->getConfig('port'));

        TestReflection::setProtectedValue($client, 'version', $version);
        $this->assertSame($version, $client->getVersion());

        $this->assertTrue(in_array($version, $client->getAllowedVersions()));
    }

    public function providerTestSettersAndGetters()
    {
        return [
            ['5.4'],
            ['5.6'],
            ['6.x'],
        ];
    }

    /**
     * @covers ::checkEsVersion
     *
     * @dataProvider providerTestCheckVersion
     */
    public function testCheckVersion($version, $expected)
    {
        $client = $this->getTestClient();
        $this->assertSame($expected, TestReflection::callProtectedMethod($client, 'checkEsVersion', [$version]));
    }

    public function providerTestCheckVersion()
    {
        return [
            //6.0.x is supported
            ['6.0.0', true],
            ['6.0.9', true],
            ['6.9', true],
            // version 5.4 to 5.6.x are supported
            ['5.6.0', true],
            ['5.6.9', true],
            ['5.4.0', true],
            ['5.4.9', true],
            ['5.4', true],
            ['5.5.0', true],
            ['5.5', true],
            // 1.x and 2.x are not supported
            ['1.7', false],
            ['2.3.1', false],
        ];
    }

    /**
     * @covers ::getVersion
     * @dataProvider providerTestGetVersion
     */
    public function testGetVersion(string $responseString, string $expected)
    {
        $clientMock = $this->getClientMock(['ping']);
        $clientMock->expects($this->any())
            ->method('ping')
            ->will($this->returnValue(new Response($responseString)));

        $this->assertSame($expected, $clientMock->getVersion());
    }

    public function providerTestGetVersion() :array
    {
        return [
            [
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "6.0.0"
                  }
                }',
                '6.0.0',
            ],
            [
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "5.4"
                  }
                }',
                '5.4',
            ],
        ];
    }

    /**
     * @covers ::getVersion
     *
     * @dataProvider providerTestGetVersionException
     */
    public function testGetVersionException(?string $responseString)
    {
        $clientMock = $this->getClientMock(['ping']);
        $clientMock->expects($this->any())
            ->method('ping')
            ->will($this->returnValue(new Response($responseString)));

        $this->expectException(Exception::class);
        $clientMock->getVersion();
    }

    public function providerTestGetVersionException() :array
    {
        return [
            [
                '{
                  "status" : 401,
                  "name" : "not_authorized",
                  "version" : {
                    "number" : "6.0.0"
                  }
                }',
            ],
            [
                '{
                  "status" : 200,
                  "name" : "no_version",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : ""
                  }
                }',
            ],
            // no response
            [null],
        ];
    }

    /**
     * @covers ::isAvailable
     * @covers ::verifyConnectivity
     * @covers ::loadAvailability
     * @covers ::updateAvailability
     * @covers ::processDataResponse
     *
     * @dataProvider providerTestIsAvailable
     */
    public function testIsAvailable($force, $isSearchEngineAvallble, $responseString, $expected)
    {
        $clientMock = $this->getClientMock(['ping', 'isSearchEngineAvailable', 'saveAdminStatus']);
        $clientMock->expects($this->any())
            ->method('ping')
            ->will($this->returnValue(new Response($responseString)));
        $clientMock->expects($this->any())
            ->method('isSearchEngineAvailable')
            ->will($this->returnValue($isSearchEngineAvallble));

        $clientMock->expects($this->any())
            ->method('saveAdminStatus');

        $this->assertSame($expected, $clientMock->isAvailable($force));
    }

    public function providerTestIsAvailable()
    {
        return [
            // ES 6.x support
            // no force update
            [
                false,
                true,
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "6.0.0",
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                true,
            ],
            // force update, all good
            [
                true,
                true,
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "6.0.0",
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                true,
            ],
            // force update, new ES status is good
            [
                true,
                false,
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "6.0.0",
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                true,
            ],
            // ES 5.6.x support
            [
                false,
                true,
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "5.6.9",
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                true,
            ],
            [
                false,
                true,
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "5.6.0",
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                true,
            ],
            [
                false,
                true,
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "5.4.0",
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                true,
            ],
            // force update, all good
            [
                true,
                true,
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "5.4.0",
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                true,
            ],
            // force update, new ES status is good
            [
                true,
                false,
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "5.4.0",
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                true,
            ],
            // update to not available
            [
                true,
                true,
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                false,
            ],
            // bad status
            [
                true,
                false,
                '{
                  "status" : 401,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "5.4.0",
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                false,
            ],
            // ES version 1.7, not supported
            [
                true,
                true,
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "1.7",
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                false,
            ],
            // ES version 2.3, not supported
            [
                true,
                true,
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "2.3.0",
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                false,
            ],
            // ES version 5.3, not supported
            [
                true,
                true,
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "5.3.0.",
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                false,
            ],
        ];
    }

    /**
     * @covers ::verifyConnectivity
     * @covers ::onConnectionFailure
     */
    public function testVerifyConnectivityHandleException()
    {
        $clientMock = $this->getClientMock(['ping']);
        $clientMock->expects($this->any())
            ->method('ping')
            ->will($this->throwException(new Exception()));

        $status = $clientMock->verifyConnectivity(false);
        $this->assertSame(Client::CONN_FAILURE, $status);
    }

    /**
     * @covers ::request
     */
    public function testRequestException()
    {
        $clientMock = $this->getClientMock(['isAvailable']);
        $clientMock->expects($this->any())
            ->method('isAvailable')
            ->will($this->returnValue(false));

        $this->expectException(Exception::class);
        $clientMock->request('/');
    }

    /**
     * @return Client Mock object
     */
    protected function getClientMock(array $methods = null)
    {
        $this->setLogger();
        $mock = TestMockHelper::getObjectMock($this, Client::class, $methods);
        $mock->setLogger($this->logger);
        return $mock;
    }

    /**
     * to get real Client instance
     * @return Client
     */
    protected function getTestClient()
    {
        $this->setLogger();
        $client = new Client($this->config, $this->logger);
        return $client;
    }

    /**
     * set logger
     */
    protected function setLogger()
    {
        $logMgr = \LoggerManager::getLogger();
        // don't record anything in the log
        $logMgr->setLevel('off');
        $this->logger = new Logger($logMgr);
    }
}
