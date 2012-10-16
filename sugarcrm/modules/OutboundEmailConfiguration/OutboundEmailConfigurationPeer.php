<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) decodesublicense, resell, rent, lease,
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

require_once "OutboundSmtpEmailConfiguration.php"; // also imports OutboundEmailConfiguration.php

// external imports
require_once "include/OutboundEmail/OutboundEmail.php";
require_once "modules/InboundEmail/InboundEmail.php";
require_once "modules/Users/User.php";

class OutboundEmailConfigurationPeer
{
    const MODE_DEFAULT = "default";
    const MODE_SMTP    = "smtp";
    const MODE_WEB     = "web";

    /**
     * Returns true/false indicating whether or not $mode is a valid sending strategy.
     *
     * @static
     * @access public
     * @param string $mode required
     * @return bool
     */
    public static function isValidMode($mode) {
        switch ($mode) {
            case self::MODE_DEFAULT:
            case self::MODE_SMTP:
            case self::MODE_WEB:
                return true;
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * @access public
     * @param User         $user    required
     * @param Localization $locale
     * @param string       $charset
     * @return OutboundEmailConfiguration System- or User-defined System-Override Mail Configuration
     * @throws MailerException
     */
    public static function getSystemMailConfiguration(User $user, Localization $locale = null, $charset = null) {
        $mailConfigurations = self::listMailConfigurations($user, $locale, $charset);

        foreach($mailConfigurations AS $mailConfiguration) {
            if ($mailConfiguration->getConfigType() == 'system') {
                return $mailConfiguration;
            }
        }

        throw new MailerException("No Valid Mail Configurations Found", MailerException::InvalidConfiguration);
    }

    /**
     * @access public
     * @param User         $user    required
     * @param Localization $locale
     * @param string       $charset
     * @return array MailConfigurations
     * @throws MailerException
     */
    public static function listMailConfigurations(User $user, Localization $locale = null, $charset = null) {
        if (is_null($locale)) {
            $locale = $GLOBALS["locale"];
        }

        if (is_null($charset)) {
            $charset = $locale->getPrecedentPreference("default_email_charset");
        }

        $outboundEmailConfigurations = array();
        $ret                         = $user->getUsersNameAndEmail();

        if (empty($ret['email'])) {
            $systemReturn          = $user->getSystemDefaultNameAndEmail();
            $ret['email']          = $systemReturn['email'];
            $ret['name']           = $systemReturn['name'];
            $system_replyToAddress = $ret['email'];
        } else {
            $system_replyToAddress = '';
        }

        $system_replyToName = $ret['name'];
        $replyTo            = $user->emailAddress->getReplyToAddress($user, true);

        if (!empty($replyTo)) {
            $system_replyToAddress = $replyTo;
        }

        /* Retrieve any Inbound User Mail Accounts and the Outbound Mail Accounts Associated with them */
        $ie         = new InboundEmail();
        $ieAccounts = $ie->retrieveAllByGroupIdWithGroupAccounts($user->id);

        foreach ($ieAccounts as $k => $v) {
            $name          = $v->get_stored_options('from_name');
            $addr          = $v->get_stored_options('from_addr');
            $storedOptions = unserialize(base64_decode($v->stored_options));

            $outbound_config_id = $storedOptions["outbound_email"];
            $oe                 = null;

            if (!empty($outbound_config_id)) {
                $oe = new OutboundEmail();
                $oe->retrieve($outbound_config_id);
            }

            if ($name != null && $addr != null && !empty($outbound_config_id) && !empty($oe) && ($outbound_config_id == $oe->id)) {
                // turn the OutboundEmail object into a useable set of mail configurations
                $oeAsArray                       = self::toArray($oe);
                $configurations                  = array();
                $configurations["config_id"]     = $outbound_config_id;
                $configurations["config_type"]   = "user";
                $configurations["inbox_id"]      = $k;
                $configurations["from_email"]    = $addr;
                $configurations["from_name"]     = $name;
                $configurations["display_name"]  = "{$name} ({$addr})";
                $configurations["personal"]      = (bool)($v->is_personal);
                $configurations["replyto_email"] = (!empty($storedOptions["reply_to_addr"])) ?
                                                    $storedOptions["reply_to_addr"] :
                                                    $addr;
                $configurations["replyto_name"]  = (!empty($storedOptions["reply_to_name"])) ?
                                                    $storedOptions["reply_to_name"] :
                                                    $name;
                $configurations["locale"]        = $locale;
                $configurations["charset"]       = $charset;
                $outboundEmailConfiguration      = self::buildOutboundEmailConfiguration($user, $configurations, $oeAsArray);
                $outboundEmailConfigurations[]   = $outboundEmailConfiguration;
            }
        }

        $oe     = new OutboundEmail();
        $system = $oe->getSystemMailerSettings();

        //Substitute in the users system override if its available.
        $userSystemOverride = $oe->getUsersMailerForSystemOverride($user->id);
        $personal           = false;

        if ($userSystemOverride != null) {
            $system   = $userSystemOverride;
            $personal = true;
        }

        if (empty($system->id)) {
            throw new MailerException("No Valid Mail Configurations Found", MailerException::InvalidConfiguration);
        }

        // turn the OutboundEmail object into a useable set of mail configurations
        $oe = new OutboundEmail();
        $oe->retrieve($system->id);

        $oeAsArray                       = self::toArray($oe);
        $configurations                  = array();
        $configurations["config_id"]     = $system->id;
        $configurations["config_type"]   = "system";
        $configurations["inbox_id"]      = null;
        $configurations["from_email"]    = $ret["email"];
        $configurations["from_name"]     = $ret["name"];
        $configurations["display_name"]  = "{$ret["name"]} ({$ret["email"]})";
        $configurations["personal"]      = $personal;
        $configurations["replyto_email"] = $system_replyToAddress;
        $configurations["replyto_name"]  = $system_replyToName;
        $configurations["locale"]        = $locale;
        $configurations["charset"]       = $charset;
        $outboundEmailConfiguration      = self::buildOutboundEmailConfiguration($user, $configurations, $oeAsArray);
        $outboundEmailConfigurations[]   = $outboundEmailConfiguration;

        return $outboundEmailConfigurations;
    }

    /**
     * @access private
     * @param User  $user           required
     * @param array $configurations required
     * @param array $outboundEmail  required
     * @return OutboundEmailConfiguration|OutboundSmtpEmailConfiguration
     */
    private static function buildOutboundEmailConfiguration(User $user, $configurations, $outboundEmail) {
        $outboundEmailConfiguration = null;
        $mode                       = strtolower($outboundEmail["mail_sendtype"]);

        // setup the mailer's known configurations based on the type of mailer
        switch ($mode) {
            case self::MODE_SMTP:
                $outboundEmailConfiguration = new OutboundSmtpEmailConfiguration($user);
                $outboundEmailConfiguration->setHost($outboundEmail["mail_smtpserver"]);
                $outboundEmailConfiguration->setPort($outboundEmail["mail_smtpport"]);

                if ($outboundEmail["mail_smtpauth_req"]) {
                    // require authentication with the SMTP server
                    $outboundEmailConfiguration->setAuthenticationRequirement(true);
                    $outboundEmailConfiguration->setUsername($outboundEmail["mail_smtpuser"]);
                    $outboundEmailConfiguration->setPassword($outboundEmail["mail_smtppass"]);
                }

                // determine the appropriate encryption layer for the sending strategy
                if ($outboundEmail["mail_smtpssl"] === 1) {
                    $outboundEmailConfiguration->setSecurityProtocol(OutboundSmtpEmailConfiguration::SecurityProtocolSsl);
                } elseif ($outboundEmail["mail_smtpssl"] === 2) {
                    $outboundEmailConfiguration->setSecurityProtocol(OutboundSmtpEmailConfiguration::SecurityProtocolTls);
                }

                break;
            default:
                $outboundEmailConfiguration = new OutboundEmailConfiguration($user);
                break;
        }

        $outboundEmailConfiguration->setConfigId($configurations["config_id"]);
        $outboundEmailConfiguration->setConfigType($configurations["config_type"]);
        $outboundEmailConfiguration->setMode($mode);
        $outboundEmailConfiguration->setConfigName($outboundEmail["name"]);
        $outboundEmailConfiguration->setInboxId($configurations["inbox_id"]);
        $outboundEmailConfiguration->setFrom($configurations["from_email"], $configurations["from_name"]);
        $outboundEmailConfiguration->setDisplayName($configurations["display_name"]);
        $outboundEmailConfiguration->setPersonal($configurations["personal"]);
        $outboundEmailConfiguration->setReplyTo($configurations["replyto_email"], $configurations["replyto_name"]);
        $outboundEmailConfiguration->setLocale($configurations["locale"]);
        $outboundEmailConfiguration->setCharset($configurations["charset"]);

        return $outboundEmailConfiguration;
    }

    /**
     * @access private
     * @param      $obj        required
     * @param bool $scalarOnly
     * @return array
     */
    private static function toArray($obj, $scalarOnly=true) {
        $fields = get_object_vars($obj);
        $arr    = array();

        foreach ($fields as $name => $type) {
            if (isset($obj->$name)) {
                if ((!$scalarOnly) || (!is_array($obj->$name) && !is_object($obj->$name))) {
                    $arr[$name] = $obj->$name;
                }
            } else {
                $arr[$name] = '';
            }
        }

        return $arr;
    }
}