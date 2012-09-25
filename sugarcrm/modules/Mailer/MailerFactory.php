<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once "MailerException.php";                      // requires MailerException in order to throw exceptions of
                                                         // that type
require_once "modules/Emails/MailConfigurationPeer.php"; // needs the constants that represent the modes
require_once "modules/Emails/MailConfiguration.php";     // uses the properties to produce the expected mailer
require_once "MailerConfiguration.php";                 // required if producing a base Mailer
require_once "SmtpMailerConfiguration.php";              // required if producing an SMTP Mailer
require_once "EmailHeaders.php";                         // email headers are contained in an EmailHeaders object
require_once "EmailIdentity.php";                        // requires EmailIdentity to build the From header

/**
 * Factory to create Mailers.
 */
class MailerFactory
{
    // protected members

    // Maps the mode from a MailConfiguration to the class that represents the sending strategy for that
    // configuration.
    // key = mode; value = mailer class
    protected static $modeToMailerMap = array(
        MailConfigurationPeer::MODE_DEFAULT => array(
            "path"  => ".",            // the path to the class file without trailing slash ("/")
            "class" => "SimpleMailer", // the name of the class
        ),
        MailConfigurationPeer::MODE_SMTP    => array(
            "path"  => ".",
            "class" => "SugarMailer",
        ),
        MailConfigurationPeer::MODE_WEB     => array(
            "path"  => ".",
            "class" => "WebMailer",
        ),
    );

    /**
     * Determines the correct Mailer to use based on the configuration that is provided to it and constructs and
     * returns that object.
     *
     * @static
     * @access public
     * @param MailConfiguration $config required The configuration that provides context to the chosen sending
     *                                           strategy.
     * @return mixed An object of one of the Mailers defined in $modeToMailerMap.
     * @throws MailerException
     */
    public static function getMailer(MailConfiguration $config) {
        // copy the config value becuase you don't want to modify the object by reassigning a public variable
        // in the case of mode being null
        $mode = is_null($config->mode) ? "default" : strtolower($config->mode); // make sure it's lower case

        if (!MailConfigurationPeer::isValidMode($mode)) {
            throw new MailerException("Invalid Mailer: '{$mode}' is an invalid mode", MailerException::InvalidMailer);
        }

        // these method calls can bubble up a MailerException
        $headers = self::buildHeadersForMailer($config->sender_email, $config->sender_name);
        $mailer  = self::buildMailer($mode, $config->mailerConfigData);
        $mailer->setHeaders($headers);

        return $mailer;
    }

    /**
     * Instantiates the requisite Mailer and returns it.
     *
     * @static
     * @access private
     * @param string              $mode   required The mode that represents the sending strategy.
     * @param MailerConfiguration $config required Must be a MailerConfiguration or a type that derives from it.
     * @return mixed An object of one of the Mailers defined in $modeToMailerMap.
     * @throws MailerException
     */
    private static function buildMailer($mode, MailerConfiguration $config) {
        $path   = self::$modeToMailerMap[$mode]["path"];
        $class  = self::$modeToMailerMap[$mode]["class"];
        $file   = "{$path}/{$class}.php";
        @include_once $file; // suppress errors

        if (!class_exists($class)) {
            throw new MailerException(
                "Invalid Mailer: Could not find class '{$class}'",
                MailerException::InvalidMailer
            );
        }

        return new $class($config);
    }

    /**
     * Constructs and returns the Headers object to be used by the Mailer and takes care of initializing the From
     * header.
     *
     * @static
     * @access private
     * @param string      $senderEmail required
     * @param null|string $senderName           Should be a string, but null is acceptable if no name is associated.
     * @return EmailHeaders
     * @throws MailerException
     */
    private static function buildHeadersForMailer($senderEmail, $senderName = null) {
        // add the known email headers
        $from    = new EmailIdentity($senderEmail, $senderName);
        $headers = new EmailHeaders();
        $headers->setHeader(EmailHeaders::From, $from);

        return $headers;
    }
}
