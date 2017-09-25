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

namespace Sugarcrm\SugarcrmTestsUnit\IdentityProvider\Authentication;

use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Config;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Config
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::get
     */
    public function testGet()
    {
        $sugarConfig = $this->createMock(\SugarConfig::class);
        $sugarConfig->expects($this->any())
            ->method('get')
            ->willReturn('sugar_config_value');
        $config = new Config($sugarConfig);
        $this->assertEquals('sugar_config_value', $config->get('some_key'), 'Proxying to sugar config');
    }

    public function getSAMLConfigDataProvider()
    {
        return [
            'no override in config' => [
                'expectedConfig' => [
                    'default' => 'config',
                ],
                'defaultConfig' => ['default' => 'config'],
                'configValues' => [],
                'customSettings' => [],
                'authenticationClass' => 'SAMLAuthenticate',
            ],
            'saml config provided' => [
                'expectedConfig' => [
                    'default' => 'overridden config',
                    'sp' => [
                        'assertionConsumerService' => [
                            'url' =>
                                'config_site_url/index.php?platform%3Dbase%26module%3DUsers%26action%3DAuthenticate',
                        ],
                    ],
                ],
                'defaultConfig' => ['default' => 'config'],
                'configValues' => [
                    'default' => 'overridden config',
                    'sp' => [
                        'assertionConsumerService' => [
                            'url' =>
                                'config_site_url/index.php?platform%3Dbase%26module%3DUsers%26action%3DAuthenticate',
                        ],
                    ],
                ],
                'customSettings' => [],
                'authenticationClass' => 'SAMLAuthenticate',
            ],
            'saml config and sugar custom settings provided' => [
                'expectedConfig' => [
                    'default' => 'overridden config',
                    'sp' => [
                        'foo' => 'bar',
                        'sugarCustom' => [
                            'useXML' => true,
                            'id' => 'first_name',
                        ],
                    ],
                ],
                'defaultConfig' => ['default' => 'config'],
                'configValues' => [
                    'default' => 'overridden config',
                    'sp' => [
                        'foo' => 'bar',
                    ],
                ],
                'customSettings' => [
                    'sp' => [
                        'sugarCustom' => [
                            'useXML' => true,
                            'id' => 'first_name',
                        ],
                    ],
                ],
                'authenticationClass' => 'SAMLAuthenticate',
            ],
            'saml not configured' => [
                'expectedConfig' => [],
                'defaultConfig' => ['default' => 'config'],
                'configValues' => [
                    'default' => 'overridden config',
                    'sp' => [
                        'foo' => 'bar',
                    ],
                ],
                'customSettings' => [
                    'sp' => [
                        'sugarCustom' => [
                            'useXML' => true,
                            'id' => 'first_name',
                        ],
                    ],
                ],
                'authenticationClass' => '',
            ],
        ];
    }

    /**
     * @param array $expectedConfig
     * @param array $defaultConfig
     * @param array $configValues
     * @param array $customSettings
     * @param string $authenticationClass
     *
     * @covers ::getSAMLConfig
     * @dataProvider getSAMLConfigDataProvider
     */
    public function testGetSAMLConfig(
        array $expectedConfig,
        array $defaultConfig,
        array $configValues,
        array $customSettings,
        $authenticationClass
    ) {
        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'getSAMLDefaultConfig', 'getSugarCustomSAMLSettings'])
            ->getMock();
        $config->method('getSugarCustomSAMLSettings')->willReturn($customSettings);
        $config->expects($this->any())
            ->method('get')
            ->withConsecutive(
                ['authenticationClass', null],
                ['SAML', []]
            )
            ->willReturnOnConsecutiveCalls(
                $authenticationClass,
                $configValues
            );
        $config->method('getSAMLDefaultConfig')
            ->willReturn($defaultConfig);
        $samlConfig = $config->getSAMLConfig();
        $this->assertEquals($expectedConfig, $samlConfig);
    }

    /**
     * Checks default config when it created from SugarCRM config values.
     *
     * @covers ::getSAMLConfig
     */
    public function testGetSAMLDefaultConfig()
    {
        $expectedConfig = [
            'strict' => false,
            'debug' => false,
            'sp' => [
                'entityId' => 'SAML_issuer',
                'assertionConsumerService' => [
                    'url' => 'site_url/index.php?module=Users&action=Authenticate',
                    'binding' => \OneLogin_Saml2_Constants::BINDING_HTTP_POST,
                ],
                'singleLogoutService' => [
                    'url' => 'site_url/index.php?module=Users&action=Logout',
                    'binding' => \OneLogin_Saml2_Constants::BINDING_HTTP_REDIRECT,
                ],
                'NameIDFormat' => \OneLogin_Saml2_Constants::NAMEID_EMAIL_ADDRESS,
                'x509cert' => 'SAML_REQUEST_SIGNING_X509',
                'privateKey' => 'SAML_REQUEST_SIGNING_PKEY',
                'provisionUser' => 'SAML_provisionUser',
            ],

            'idp' => [
                'entityId' => 'SAML_idp_entityId',
                'singleSignOnService' => [
                    'url' => 'SAML_loginurl',
                    'binding' => \OneLogin_Saml2_Constants::BINDING_HTTP_REDIRECT,
                ],
                'singleLogoutService' => [
                    'url' => 'SAML_SLO',
                    'binding' => \OneLogin_Saml2_Constants::BINDING_HTTP_REDIRECT,
                ],
                'x509cert' => 'SAML_X509Cert',
            ],

            'security' => [
                'authnRequestsSigned' => true,
                'logoutRequestSigned' => true,
                'logoutResponseSigned' => true,
                'signatureAlgorithm' => 'SAML_REQUEST_SIGNING_METHOD',
                'validateRequestId' => true,
            ],
        ];
        $config = $this->getMockBuilder(Config::class)
                       ->disableOriginalConstructor()
                       ->setMethods(['get', 'getSugarCustomSAMLSettings', 'isSamlEnabled'])
                       ->getMock();
        $config->method('isSamlEnabled')->willReturn(true);
        $config->method('getSugarCustomSAMLSettings')->willReturn([]);
        $config->method('get')
               ->willReturnMap(
                   [
                       ['SAML_request_signing_pkey', null, 'SAML_REQUEST_SIGNING_PKEY'],
                       ['site_url', null, 'site_url'],
                       ['SAML_loginurl', null, 'SAML_loginurl'],
                       ['SAML_issuer', 'php-saml', 'SAML_issuer'],
                       ['SAML_request_signing_x509', '', 'SAML_REQUEST_SIGNING_X509'],
                       ['SAML_request_signing_x509', null, 'SAML_REQUEST_SIGNING_X509'],
                       ['SAML_request_signing_pkey', '', 'SAML_REQUEST_SIGNING_PKEY'],
                       ['SAML_provisionUser', true, 'SAML_provisionUser'],
                       ['SAML_idp_entityId', 'SAML_loginurl', 'SAML_idp_entityId'],
                       ['SAML_SLO', null, 'SAML_SLO'],
                       ['SAML_X509Cert', null, 'SAML_X509Cert'],
                       [
                           'SAML_request_signing_method',
                           \XMLSecurityKey::RSA_SHA256,
                           'SAML_REQUEST_SIGNING_METHOD',
                       ],
                       ['SAML', [], []],
                       ['SAML_sign_authn', false, true],
                       ['SAML_sign_logout_request', false, true],
                       ['SAML_sign_logout_response', false, true],
                       ['saml.validate_request_id', false, true],
                   ]
               );
        $this->assertEquals($expectedConfig, $config->getSAMLConfig());
    }

    public function getSAMLConfigIdpStoredValuesProperlyEscapeProvider()
    {
        return [
            ['https://test.local', 'https://test.local'],
            ['https://test.local?idp1=test', 'https://test.local?idp1=test'],
            ['https://test.local/idp=test&idp1=test', 'https://test.local/idp=test&idp1=test'],
            ['https://test.local/idp=test&amp;idp1=test', 'https://test.local/idp=test&idp1=test'],
        ];
    }

    /**
     * @param string $storedValue
     * @param string $expectedValue
     *
     * @covers ::getSAMLConfig
     * @dataProvider getSAMLConfigIdpStoredValuesProperlyEscapeProvider
     */
    public function testGetSAMLConfigIdpStoredValuesProperlyEscape($storedValue, $expectedValue)
    {
        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'getSugarCustomSAMLSettings'])
            ->getMock();

        $config->expects($this->any())->method('get')
            ->will($this->returnValueMap(
                [
                    ['SAML_loginurl', null, $storedValue],
                    ['SAML_SLO', null, $storedValue],
                    ['SAML_issuer', 'php-saml', $storedValue],
                    ['SAML_idp_entityId', $expectedValue, $storedValue],
                    ['SAML', [], []],
                ]
            ));

        $config->expects($this->any())->method('getSugarCustomSAMLSettings')->willReturn([]);

        $samlConfig = $config->getSAMLConfig();

        $this->assertArrayHasKey('idp', $samlConfig);
        $this->assertArrayHasKey('singleSignOnService', $samlConfig['idp']);
        $this->assertArrayHasKey('singleLogoutService', $samlConfig['idp']);
        $this->assertArrayHasKey('entityId', $samlConfig['idp']);
        $this->assertArrayHasKey('url', $samlConfig['idp']['singleSignOnService']);
        $this->assertArrayHasKey('url', $samlConfig['idp']['singleLogoutService']);

        $this->assertArrayHasKey('sp', $samlConfig);
        $this->assertArrayHasKey('entityId', $samlConfig['sp']);

        $this->assertEquals($expectedValue, $samlConfig['idp']['singleSignOnService']['url'], 'SSO url invalid');
        $this->assertEquals($expectedValue, $samlConfig['idp']['singleLogoutService']['url'], 'SLO url invalid');
        $this->assertEquals($expectedValue, $samlConfig['idp']['entityId'], 'IdP Entity ID invalid');
        $this->assertEquals($expectedValue, $samlConfig['sp']['entityId'], 'SugarCRM Entity ID invalid');
    }

    /**
     * @covers ::getLdapConfig
     */
    public function testGetLdapConfigNoLdap()
    {
        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['isLdapEnabled'])
            ->getMock();
        $config->expects($this->once())
            ->method('isLdapEnabled')
            ->willReturn(false);

        $this->assertEmpty($config->getLdapConfig());
    }

    public function getLdapConfigDataProvider()
    {
        return [
            'regular LDAP' => [
                [
                    'user' => [
                        'mapping' => [
                            'givenName' => 'first_name',
                            'sn' => 'last_name',
                            'mail' => 'email1',
                            'telephoneNumber' => 'phone_work',
                            'facsimileTelephoneNumber' => 'phone_fax',
                            'mobile' => 'phone_mobile',
                            'street' => 'address_street',
                            'l' => 'address_city',
                            'st' => 'address_state',
                            'postalCode' => 'address_postalcode',
                            'c' => 'address_country',
                        ],
                    ],
                    'adapter_config' => [
                        'host' => '127.0.0.1',
                        'port' => '389',
                        'options' => [
                            'network_timeout' => 60,
                            'timelimit' => 60,
                        ],
                        'encryption' => 'none',
                    ],
                    'adapter_connection_protocol_version' => 3,
                    'baseDn' => 'dn',
                    'uidKey' => 'uidKey',
                    'filter' => '({uid_key}={username})',
                    'dnString' => null,
                    'entryAttribute' => 'ldap_bind_attr',
                    'autoCreateUser' => true,
                    'searchDn' => 'admin',
                    'searchPassword' => 'test',
                    'groupMembership' => true,
                    'groupDn' => 'group,group_dn',
                    'groupAttribute' => 'group_attr',
                    'userUniqueAttribute' => 'ldap_group_user_attr',
                    'includeUserDN' => true,
                ],
                [
                    ['ldap_hostname', '127.0.0.1', '127.0.0.1'],
                    ['ldap_port', 389, 389],
                    ['ldap_base_dn', '', 'dn'],
                    ['ldap_login_attr', '', 'uidKey'],
                    ['ldap_login_filter', '', ''],
                    ['ldap_bind_attr', null, 'ldap_bind_attr'],
                    ['ldap_auto_create_users', false, true],
                    ['ldap_authentication', null, true],
                    ['ldap_admin_user', null, 'admin'],
                    ['ldap_admin_password', null, 'test'],
                    ['ldap_group', null, true],
                    ['ldap_group_name', null, 'group'],
                    ['ldap_group_dn', null, 'group_dn'],
                    ['ldap_group_attr', null, 'group_attr'],
                    ['ldap_group_user_attr', null, 'ldap_group_user_attr'],
                    ['ldap_group_attr_req_dn', false, '1'],
                ],
            ],
            'LDAP over SSL' => [
                [
                    'user' => [
                        'mapping' => [
                            'givenName' => 'first_name',
                            'sn' => 'last_name',
                            'mail' => 'email1',
                            'telephoneNumber' => 'phone_work',
                            'facsimileTelephoneNumber' => 'phone_fax',
                            'mobile' => 'phone_mobile',
                            'street' => 'address_street',
                            'l' => 'address_city',
                            'st' => 'address_state',
                            'postalCode' => 'address_postalcode',
                            'c' => 'address_country',
                        ],
                    ],
                    'adapter_config' => [
                        'host' => '127.0.0.1',
                        'port' => 636,
                        'options' => [
                            'network_timeout' => 60,
                            'timelimit' => 60,
                        ],
                        'encryption' => 'ssl',
                    ],
                    'adapter_connection_protocol_version' => 3,
                    'baseDn' => 'dn',
                    'uidKey' => 'uidKey',
                    'filter' => '({uid_key}={username})',
                    'dnString' => null,
                    'entryAttribute' => 'ldap_bind_attr',
                    'autoCreateUser' => true,
                    'searchDn' => 'admin',
                    'searchPassword' => 'test',
                    'groupMembership' => true,
                    'groupDn' => 'group,group_dn',
                    'groupAttribute' => 'group_attr',
                    'userUniqueAttribute' => 'ldap_group_user_attr',
                    'includeUserDN' => true,
                ],
                [
                    ['ldap_hostname', '127.0.0.1', 'ldaps://127.0.0.1'],
                    ['ldap_port', 389, 636],
                    ['ldap_base_dn', '', 'dn'],
                    ['ldap_login_attr', '', 'uidKey'],
                    ['ldap_login_filter', '', ''],
                    ['ldap_bind_attr', null, 'ldap_bind_attr'],
                    ['ldap_auto_create_users', false, true],
                    ['ldap_authentication', null, true],
                    ['ldap_admin_user', null, 'admin'],
                    ['ldap_admin_password', null, 'test'],
                    ['ldap_group', null, true],
                    ['ldap_group_name', null, 'group'],
                    ['ldap_group_dn', null, 'group_dn'],
                    ['ldap_group_attr', null, 'group_attr'],
                    ['ldap_group_user_attr', null, 'ldap_group_user_attr'],
                    ['ldap_group_attr_req_dn', false, '1'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getLdapConfigDataProvider
     * @covers ::getLdapConfig
     */
    public function testGetLdapConfig($expected, $returnValueMap)
    {
        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['isLdapEnabled', 'getLdapSetting'])
            ->getMock();
        $config->expects($this->once())
            ->method('isLdapEnabled')
            ->willReturn(true);
        $config->expects($this->exactly(16))
            ->method('getLdapSetting')
            ->willReturnMap($returnValueMap);

        $this->assertEquals($expected, $config->getLdapConfig());
    }

    /**
     * Provides data for testGetLdapConfigWithDifferentFilters.
     * @return array
     */
    public function getLdapConfigWithDifferentFiltersProvider()
    {
        return [
            'emptyConfigFilter' => [
                'configFilter' => '',
                'expectedFilter' => '({uid_key}={username})',
            ],
            'notEmptyConfigFilterWithBrackets' => [
                'configFilter' => '(objectClass=person)',
                'expectedFilter' => '(&({uid_key}={username})(objectClass=person))',
            ],
            'notEmptyConfigFilterWithoutBrackets' => [
                'configFilter' => 'objectClass=person',
                'expectedFilter' => '(&({uid_key}={username})(objectClass=person))',
            ],
            'notEmptyConfigFilterWithOneBracketsAndSpaces' => [
                'configFilter' => '  objectClass=person) ',
                'expectedFilter' => '(&({uid_key}={username})(objectClass=person))',
            ],
            'notEmptyConfigFilterWithOneBracketsAndSpecCharacters' => [
                'configFilter' => "\n\x0B" . '    (objectClass=person' . "\t\n\r\0",
                'expectedFilter' => '(&({uid_key}={username})(objectClass=person))',
            ],
        ];
    }

    /**
     * @param string $configFilter
     * @param string $expectedFilter
     *
     * @covers ::getLdapConfig
     * @dataProvider getLdapConfigWithDifferentFiltersProvider
     */
    public function testGetLdapConfigWithDifferentFilters($configFilter, $expectedFilter)
    {
        /** @var \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Config $config */
        $config = $this->getMockBuilder(Config::class)
                       ->disableOriginalConstructor()
                       ->setMethods(['isLdapEnabled', 'getLdapSetting'])
                       ->getMock();
        $config->expects($this->once())
               ->method('isLdapEnabled')
               ->willReturn(true);

        $config->method('getLdapSetting')->willReturnMap([['ldap_login_filter', '', $configFilter]]);
        $result = $config->getLdapConfig();
        $this->assertEquals($expectedFilter, $result['filter']);
    }
}
