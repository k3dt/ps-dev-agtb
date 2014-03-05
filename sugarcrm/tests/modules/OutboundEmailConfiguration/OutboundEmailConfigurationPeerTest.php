<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
require_once "modules/OutboundEmailConfiguration/OutboundEmailConfigurationPeer.php";
require_once "OutboundEmailConfigurationTestHelper.php";

/**
 * @group email
 * @group outboundemailconfiguration
 */
class OutboundEmailConfigurationPeerTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $systemOverrideConfiguration;

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp("current_user");
        SugarTestHelper::setUp("app_list_strings");
        SugarTestHelper::setUp("app_strings");
        SugarTestHelper::setUp("beanFiles");
        SugarTestHelper::setUp("beanList");
        OutboundEmailConfigurationTestHelper::setUp();

        $this->systemOverrideConfiguration =
            OutboundEmailConfigurationTestHelper::createSystemOverrideOutboundEmailConfiguration(
                $GLOBALS["current_user"]->id
            );
    }

    public function tearDown()
    {
        OutboundEmailConfigurationTestHelper::tearDown();
        SugarTestHelper::tearDown();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        parent::tearDown();
    }

    public function testListMailConfigurations_NoSystemOrSystemOverrideConfigurationsExist_SystemConfigurationIsNotAllowed_SystemOverrideConfigurationIsCreatedAndReturned()
    {
        OutboundEmailConfigurationTestHelper::removeAllCreatedEmailRecords();

        $mockOutboundEmailConfigurationPeer = $this->getMockOutboundEmailConfigurationPeer(false);

        $configuration = $mockOutboundEmailConfigurationPeer::listMailConfigurations($GLOBALS["current_user"]);

        $expected = "system";
        $actual   = $configuration[0]->getConfigType();
        $this->assertEquals($expected, $actual, "The system-override configuration should be of type 'system'");

        $actual = $configuration[0]->getPersonal();
        $this->assertTrue($actual, "The system-override configuration should be a personal configuration");
    }

    public function testListMailConfigurations_NoSystemOrSystemOverrideConfigurationsExist_SystemConfigurationIsAllowed_SystemConfigurationIsCreatedAndReturned()
    {
        OutboundEmailConfigurationTestHelper::removeAllCreatedEmailRecords();

        $mockOutboundEmailConfigurationPeer = $this->getMockOutboundEmailConfigurationPeer(true);

        $configuration = $mockOutboundEmailConfigurationPeer::listMailConfigurations($GLOBALS["current_user"]);

        $expected = "system";
        $actual   = $configuration[0]->getConfigType();
        $this->assertEquals($expected, $actual, "The system configuration should be of type 'system'");

        $actual = $configuration[0]->getPersonal();
        $this->assertFalse($actual, "The system configuration should not be a personal configuration");
    }

    public function testListMailConfigurations_SystemConfigurationIsNotAllowedAndUserHasUserAndSystemOverrideConfigurations_ReturnsAllExceptTheSystemConfiguration()
    {
        $userConfigurations = OutboundEmailConfigurationTestHelper::createUserOutboundEmailConfigurations(2);

        $expected = array(
            $this->systemOverrideConfiguration->id => $this->systemOverrideConfiguration->name,
            $userConfigurations[0]["outbound"]->id => $userConfigurations[0]["outbound"]->name,
            $userConfigurations[1]["outbound"]->id => $userConfigurations[1]["outbound"]->name,
        );

        $mockOutboundEmailConfigurationPeer = $this->getMockOutboundEmailConfigurationPeer(false);

        $configurations = $mockOutboundEmailConfigurationPeer::listMailConfigurations($GLOBALS["current_user"]);
        $actual         = array();

        foreach ($configurations AS $configuration) {
            $actual[$configuration->getConfigId()] = $configuration->getConfigName();
        }

        $this->assertEquals($expected, $actual, "The wrong configurations were returned");
    }

    public function testListMailConfigurations_SystemConfigurationIsAllowedAndUserHasUserAndSystemOverrideConfigurations_ReturnsAllExceptTheSystemOverrideConfiguration()
    {
        $userConfigurations  = OutboundEmailConfigurationTestHelper::createUserOutboundEmailConfigurations(2);
        $systemConfiguration = OutboundEmailConfigurationTestHelper::getSystemConfiguration();

        $expected = array(
            $systemConfiguration->id               => $systemConfiguration->name,
            $userConfigurations[0]["outbound"]->id => $userConfigurations[0]["outbound"]->name,
            $userConfigurations[1]["outbound"]->id => $userConfigurations[1]["outbound"]->name,
        );

        $mockOutboundEmailConfigurationPeer = $this->getMockOutboundEmailConfigurationPeer(true);

        $configurations = $mockOutboundEmailConfigurationPeer::listMailConfigurations($GLOBALS["current_user"]);
        $actual         = array();

        foreach ($configurations AS $configuration) {
            $actual[$configuration->getConfigId()] = $configuration->getConfigName();
        }

        $this->assertEquals($expected, $actual, "The wrong configurations were returned");
    }

    public function testGetSystemMailConfiguration_SystemConfigurationIsNotAllowed_ReturnsTheUsersSystemOverrideConfiguration()
    {
        $mockOutboundEmailConfigurationPeer = $this->getMockOutboundEmailConfigurationPeer(false);

        $configuration = $mockOutboundEmailConfigurationPeer::getSystemMailConfiguration($GLOBALS["current_user"]);

        $expected = $this->systemOverrideConfiguration->id;
        $actual   = $configuration->getConfigId();
        $this->assertEquals($expected, $actual, "The user's system-override configuration should have been returned");
    }

    public function testGetSystemMailConfiguration_SystemConfigurationIsAllowed_ReturnsTheSystemConfiguration()
    {
        $mockOutboundEmailConfigurationPeer = $this->getMockOutboundEmailConfigurationPeer(true);

        $configuration = $mockOutboundEmailConfigurationPeer::getSystemMailConfiguration($GLOBALS["current_user"]);

        $expected = "system";
        $actual   = $configuration->getConfigType();
        $this->assertEquals($expected, $actual, "The system configuration should be of type 'system'");

        $actual = $configuration->getPersonal();
        $this->assertFalse($actual, "The system configuration should not be a personal configuration");
    }

    public function testValidSystemMailConfigurationExists_SystemConfigurationIsAllowedAndSystemConfigurationIsValid_ReturnsTrue()
    {
        $mockOutboundEmailConfigurationPeer = $this->getMockOutboundEmailConfigurationPeer(true);

        $actual = $mockOutboundEmailConfigurationPeer::validSystemMailConfigurationExists($GLOBALS["current_user"]);
        self::assertTrue($actual, "There should be a system configuration and the host should not be empty");
    }

    public function testValidSystemMailConfigurationExists_SystemConfigurationIsAllowedAndSystemConfigurationIsInvalid_ReturnsFalse()
    {
        OutboundEmailConfigurationTestHelper::removeAllCreatedEmailRecords();

        $configuration = array(
            "name"              => "System",
            "type"              => "system",
            "user_id"           => "1",
            "from_email"        => "foo@bar.com",
            "from_name"         => "Foo Bar",
            "mail_sendtype"     => "SMTP",
            "mail_smtptype"     => "other",
            "mail_smtpserver"   => "",
            "mail_smtpport"     => "25",
            "mail_smtpuser"     => "foo",
            "mail_smtppass"     => "foobar",
            "mail_smtpauth_req" => "1",
            "mail_smtpssl"      => "0",
        );
        OutboundEmailConfigurationTestHelper::createOutboundEmail($configuration);

        $mockOutboundEmailConfigurationPeer = $this->getMockOutboundEmailConfigurationPeer(true);

        $actual = $mockOutboundEmailConfigurationPeer::validSystemMailConfigurationExists($GLOBALS["current_user"]);
        self::assertFalse($actual, "There should be a system configuration but the host should be empty");
    }

    public function testValidSystemMailConfigurationExists_SystemConfigurationIsNotAllowedAndSystemOverrideConfigurationIsValid_ReturnsTrue()
    {
        $mockOutboundEmailConfigurationPeer = $this->getMockOutboundEmailConfigurationPeer(false);

        $actual = $mockOutboundEmailConfigurationPeer::validSystemMailConfigurationExists($GLOBALS["current_user"]);
        self::assertTrue($actual, "There should be a system-override configuration and the host should not be empty");
    }

    public function testValidSystemMailConfigurationExists_SystemConfigurationIsNotAllowedAndSystemOverrideConfigurationIsInvalid_ReturnsFalse()
    {
        OutboundEmailConfigurationTestHelper::removeAllCreatedEmailRecords();

        $configuration = array(
            "name"              => "System Override",
            "type"              => "system-override",
            "user_id"           => $GLOBALS["current_user"]->id,
            "from_email"        => "foo@bar.com",
            "from_name"         => "Foo Bar",
            "mail_sendtype"     => "SMTP",
            "mail_smtptype"     => "other",
            "mail_smtpserver"   => "",
            "mail_smtpport"     => "25",
            "mail_smtpuser"     => "foo",
            "mail_smtppass"     => "foobar",
            "mail_smtpauth_req" => "1",
            "mail_smtpssl"      => "0",
        );
        OutboundEmailConfigurationTestHelper::createOutboundEmail($configuration);

        $mockOutboundEmailConfigurationPeer = $this->getMockOutboundEmailConfigurationPeer(false);

        $actual = $mockOutboundEmailConfigurationPeer::validSystemMailConfigurationExists($GLOBALS["current_user"]);
        self::assertFalse($actual, "There should be a system-override configuration but the host should be empty");
    }

    public function testValidSystemMailConfigurationExists_AuthRequired_NoUserPassword_ReturnsFalse()
    {
        OutboundEmailConfigurationTestHelper::removeAllCreatedEmailRecords();

        $configuration = array(
            "name"              => "System Override",
            "type"              => "system-override",
            "user_id"           => $GLOBALS["current_user"]->id,
            "from_email"        => "foo@bar.com",
            "from_name"         => "Foo Bar",
            "mail_sendtype"     => "SMTP",
            "mail_smtptype"     => "other",
            "mail_smtpserver"   => "smtp.example.com",
            "mail_smtpport"     => "25",
            "mail_smtpuser"     => "",
            "mail_smtppass"     => "",
            "mail_smtpauth_req" => "1",
            "mail_smtpssl"      => "0",
        );
        OutboundEmailConfigurationTestHelper::createOutboundEmail($configuration);

        $mockOutboundEmailConfigurationPeer = $this->getMockOutboundEmailConfigurationPeer(false);

        $actual = $mockOutboundEmailConfigurationPeer::validSystemMailConfigurationExists($GLOBALS["current_user"]);
        self::assertFalse($actual, "There should be a system-override configuration but the host should be empty");
    }


    public function testValidSystemMailConfigurationExists_AuthNotRequired_NoUserOrPassword_ReturnsTrue()
    {
        OutboundEmailConfigurationTestHelper::removeAllCreatedEmailRecords();

        $configuration = array(
            "name"              => "System Override",
            "type"              => "system-override",
            "user_id"           => $GLOBALS["current_user"]->id,
            "from_email"        => "foo@bar.com",
            "from_name"         => "Foo Bar",
            "mail_sendtype"     => "SMTP",
            "mail_smtptype"     => "other",
            "mail_smtpserver"   => "smtp.example.com",
            "mail_smtpport"     => "25",
            "mail_smtpuser"     => "",
            "mail_smtppass"     => "",
            "mail_smtpauth_req" => "0",
            "mail_smtpssl"      => "0",
        );
        OutboundEmailConfigurationTestHelper::createOutboundEmail($configuration);

        $mockOutboundEmailConfigurationPeer = $this->getMockOutboundEmailConfigurationPeer(false);

        $actual = $mockOutboundEmailConfigurationPeer::validSystemMailConfigurationExists($GLOBALS["current_user"]);
        self::assertTrue($actual, "Configuration should be Valid - Auth Not Required - No Name or Password exists");
    }


    public function testValidSystemMailConfigurationExists_AuthRequired_UserPasswordExist_ReturnsTrue()
    {
        OutboundEmailConfigurationTestHelper::removeAllCreatedEmailRecords();

        $configuration = array(
            "name"              => "System Override",
            "type"              => "system-override",
            "user_id"           => $GLOBALS["current_user"]->id,
            "from_email"        => "foo@bar.com",
            "from_name"         => "Foo Bar",
            "mail_sendtype"     => "SMTP",
            "mail_smtptype"     => "other",
            "mail_smtpserver"   => "smtp.example.com",
            "mail_smtpport"     => "25",
            "mail_smtpuser"     => "mickey",
            "mail_smtppass"     => "mouse",
            "mail_smtpauth_req" => "1",
            "mail_smtpssl"      => "0",
        );
        OutboundEmailConfigurationTestHelper::createOutboundEmail($configuration);

        $mockOutboundEmailConfigurationPeer = $this->getMockOutboundEmailConfigurationPeer(false);

        $actual = $mockOutboundEmailConfigurationPeer::validSystemMailConfigurationExists($GLOBALS["current_user"]);
        self::assertTrue($actual, "Configuration should be Valid - Auth Required -  Name and Password exist");
    }

    public function testGetMailConfigurationStatusForUser_NoSMTPServer_ReturnsInvalidSystemConfiguration()
    {
        $systemConfiguration = OutboundEmailConfigurationTestHelper::getSystemConfiguration();

        $configuration = new OutboundEmail();
        $configuration->retrieve($systemConfiguration->id);
        $configuration->mail_smtpserver = '';
        $configuration->save();

        $mockOutboundEmail = $this->getMock("OutboundEmail", array("isAllowUserAccessToSystemDefaultOutbound", "getSystemMailerSettings"));
        $mockOutboundEmail->expects($this->any())
            ->method("isAllowUserAccessToSystemDefaultOutbound")
            ->will($this->returnValue(false));
        $mockOutboundEmail->expects($this->any())
            ->method("getSystemMailerSettings")
            ->will($this->returnValue(array()));

        $mockOutboundEmailConfigurationPeer = $this->getMockClass(
            "OutboundEmailConfigurationPeer",
            array("loadOutboundEmail")
        );
        $mockOutboundEmailConfigurationPeer::staticExpects($this->any())
            ->method("loadOutboundEmail")
            ->will($this->returnValue($mockOutboundEmail));

        $status = $mockOutboundEmailConfigurationPeer::getMailConfigurationStatusForUser($GLOBALS["current_user"]);

        $this->assertEquals(OutboundEmailConfigurationPeer::STATUS_INVALID_SYSTEM_CONFIG, $status, "Invalid system configuration should be returned");
    }

    public function testGetMailConfigurationStatusForUser_ValidSystemConfig_AllowAllUsersSet_ReturnsValidConfiguration()
    {
        $mockOutboundEmailConfigurationPeer = $this->getMockOutboundEmailConfigurationPeer(true);

        $status = $mockOutboundEmailConfigurationPeer::getMailConfigurationStatusForUser($GLOBALS["current_user"]);

        $this->assertEquals(OutboundEmailConfigurationPeer::STATUS_VALID_CONFIG, $status, "Should return a valid configuration");
    }

    public function testGetMailConfigurationStatusForUser_ValidSystemConfig_AllowAllUsersNotSet_SMTPAuthenticationNotSet_NoUserData_ReturnsValidUserConfiguration()
    {
        $systemConfiguration = OutboundEmailConfigurationTestHelper::getSystemConfiguration();

        $configuration = new OutboundEmail();
        $configuration->retrieve($systemConfiguration->id);
        $configuration->mail_smtpauth_req = '0';
        $configuration->save();

        $userConfiguration = new OutboundEmail();
        $userConfiguration->retrieve($this->systemOverrideConfiguration->id);
        $userConfiguration->mail_smtpuser = '';
        $userConfiguration->mail_smtppass = '';
        $userConfiguration->save();

        $mockOutboundEmailConfigurationPeer = $this->getMockOutboundEmailConfigurationPeer(false);

        $status = $mockOutboundEmailConfigurationPeer::getMailConfigurationStatusForUser($GLOBALS["current_user"]);

        $this->assertEquals(OutboundEmailConfigurationPeer::STATUS_VALID_CONFIG, $status, "The config should be valid");
    }

    public function testGetMailConfigurationStatusForUser_ValidSystemConfig_AllowAllUsersNotSet_SMTPAuthenticationSet_ValidUserData_ReturnsValidConfiguration()
    {
        $mockOutboundEmailConfigurationPeer = $this->getMockOutboundEmailConfigurationPeer(false);

        $status = $mockOutboundEmailConfigurationPeer::getMailConfigurationStatusForUser($GLOBALS["current_user"]);

        $this->assertEquals(OutboundEmailConfigurationPeer::STATUS_VALID_CONFIG, $status, "The configuration should be valid");
    }

    public function testGetMailConfigurationStatusForUser_ValidSystemConfig_AllowAllUsersNotSet_SMTPAuthenticationSet_NoUserData_ReturnsInvalidConfiguration()
    {
        $outboundEmailConfiguration = new OutboundSmtpEmailConfiguration($GLOBALS["current_user"]);
        $mockOutboundEmail = $this->getMock("OutboundEmail", array("isAllowUserAccessToSystemDefaultOutbound"));
        $mockOutboundEmail->expects($this->any())
            ->method("isAllowUserAccessToSystemDefaultOutbound")
            ->will($this->returnValue(false));

        $mockOutboundEmailConfigurationPeer = $this->getMockClass(
            "OutboundEmailConfigurationPeer",
            array("loadOutboundEmail","getSystemMailConfiguration")
        );
        $mockOutboundEmailConfigurationPeer::staticExpects($this->any())
            ->method("loadOutboundEmail")
            ->will($this->returnValue($mockOutboundEmail));
        $mockOutboundEmailConfigurationPeer::staticExpects($this->any())
            ->method("getSystemMailConfiguration")
            ->will($this->returnValue($outboundEmailConfiguration));

        $status = $mockOutboundEmailConfigurationPeer::getMailConfigurationStatusForUser($GLOBALS["current_user"]);

        $this->assertEquals(OutboundEmailConfigurationPeer::STATUS_INVALID_USER_CONFIG, $status, "The user configuration should not be valid");
    }

    public function testGetMailConfigurationStatusForUser_ValidSystemConfig_AllowAllUsersNotSet_SMTPAuthenticationSet_NoUserData_ReturnsValidConfiguration()
    {
        $systemConfiguration = OutboundEmailConfigurationTestHelper::getSystemConfiguration();

        $configuration = new OutboundEmail();
        $configuration->retrieve($systemConfiguration->id);
        $configuration->mail_smtpauth_req = '0';
        $configuration->save();

        $mockOutboundEmailConfigurationPeer = $this->getMockOutboundEmailConfigurationPeer(false);

        $status = $mockOutboundEmailConfigurationPeer::getMailConfigurationStatusForUser($GLOBALS["current_user"]);

        $this->assertEquals(OutboundEmailConfigurationPeer::STATUS_VALID_CONFIG, $status, "The configuration should be valid");
    }

    private function getMockOutboundEmailConfigurationPeer($isAllowUserAccessToSystemDefaultOutbound = false)
    {
        $mockOutboundEmail = $this->getMock("OutboundEmail", array("isAllowUserAccessToSystemDefaultOutbound"));
        $mockOutboundEmail->expects($this->any())
            ->method("isAllowUserAccessToSystemDefaultOutbound")
            ->will($this->returnValue($isAllowUserAccessToSystemDefaultOutbound));

        $mockOutboundEmailConfigurationPeer = $this->getMockClass(
            "OutboundEmailConfigurationPeer",
            array("loadOutboundEmail")
        );
        $mockOutboundEmailConfigurationPeer::staticExpects($this->any())
            ->method("loadOutboundEmail")
            ->will($this->returnValue($mockOutboundEmail));

        return $mockOutboundEmailConfigurationPeer;
    }
}
