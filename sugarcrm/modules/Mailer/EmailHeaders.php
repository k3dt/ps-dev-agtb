<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/********************************************************************************
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

require_once "MailerException.php"; // requires MailerException in order to throw exceptions of that type
require_once "EmailIdentity.php";   // requires EmailIdentity for From, Reply-To, and Sender

/**
 * This class encapsulates properties and behavior of email headers so that business logic relating to headers can exist
 * in a single place and adherence to the requisite RFC's can be easily managed.
 */
class EmailHeaders
{
    // the non-custom header field names we support
    const MessageId                 = "Message-ID";
    const Priority                  = "Priority";
    const DispositionNotificationTo = "Disposition-Notification-To";
    const From                      = "From";
    const ReplyTo                   = "Reply-To";
    const Sender                    = "Sender";
    const Subject                   = "Subject";

    // protected members
    protected $messageId;           // The value to be mapped to the Message-ID header.
    protected $priority;            // The value to be mapped to the Priority header.
    protected $requestConfirmation; // The value to be mapped to the Disposition-Notification-To header.
    protected $from;                // The value to be mapped to the From header.
    protected $replyTo;             // The value to be mapped to the Reply-To header.
    protected $sender;              // The value to be mapped to the Sender header.
    protected $subject;             // The value to be mapped to the Subject header.
    protected $custom;              // Array of key value pairs for custom headers to add to the message.

    /**
     * @access public
     */
    public function __construct() {
        $this->setPriority(); // set the Priority header to its default
        $this->setRequestConfirmation(); // set the Disposition-Notification-To header to its default
        $this->clearCustomHeaders(); // initialize the custom headers array
    }

    /**
     * Allows a caller to hydrate a Headers object from an array to reduce the burden of building the object correctly.
     * The array keys should look like the real headers they represent.
     *
     * @access public
     * @param array $headers
     */
    public function buildFromArray($headers = array()) {
        if (is_array($headers)) {
            foreach ($headers as $key => $value) {
                $this->setHeader($key, $value);
            }
        }
    }

    /**
     * Adds or replaces header values. Prevents adding of custom headers that are actually represented by the
     * reserved headers; will simply replace the values.
     *
     * @access public
     * @param string $key   required Should look like the real header it represents.
     * @param mixed  $value required The value of the header.
     * @throws MailerException
     */
    public function setHeader($key, $value) {
        switch ($key) {
            case self::MessageId:
                $this->messageId = $value;
                break;
            case self::Priority:
                $this->setPriority($value);
                break;
            case self::DispositionNotificationTo:
                $this->setRequestConfirmation($value);
                break;
            case self::From:
                $this->setFrom($value);
                break;
            case self::ReplyTo:
                $this->setReplyTo($value);
                break;
            case self::Sender:
                $this->setSender($value);
                break;
            case self::Subject:
                $this->setSubject($value);
                break;
            default:
                // it's not known, so it must be a custom header
                $this->addCustomHeader($key, $value);
                break;
        }
    }


    /**
     * @access public
     * @param string $subject required
     * @throws MailerException
     */
    public function setSubject($subject) {
        if (!is_string($subject)) {
            throw new MailerException(
                "Invalid header: " . self::Subject . " must be a string",
                MailerException::InvalidHeader
            );
        }

        $this->subject = $subject;
    }

    /**
     * @access public
     * @return string
     */
    public function getMessageId() {
        return $this->messageId;
    }

    /**
     * @access public
     * @return int
     */
    public function getPriority() {
        return $this->priority;
    }

    /**
     * @access public
     * @return bool
     */
    public function getRequestConfirmation() {
        return $this->requestConfirmation;
    }

    /**
     * @access public
     * @return EmailIdentity
     */
    public function getFrom() {
        return $this->from;
    }

    /**
     * @access public
     * @return EmailIdentity
     */
    public function getReplyTo() {
        return $this->replyTo;
    }

    /**
     * @access public
     * @return EmailIdentity
     */
    public function getSender() {
        return $this->sender;
    }

    /**
     * @access public
     * @return string
     */
    public function getSubject() {
        return $this->subject;
    }

    /**
     * Clears the custom headers array.
     *
     * @access public
     */
    public function clearCustomHeaders() {
        $this->custom = array();
    }

    /**
     * @access public
     * @param string $key required
     * @return null|string The value of the header or null if the header has not been defined.
     */
    public function getCustomHeader($key) {
        if (array_key_exists($key, $this->custom)) {
            return $this->custom[$key];
        }

        return null;
    }

    /**
     * @access public
     * @return array Array of key value pairs representing the custom headers and their values.
     */
    public function getCustomHeaders() {
        return $this->custom;
    }

    /**
     * Packages the headers in an array in such a way that they are ready to be included in an email.
     *
     * @access public
     * @return array Array of key value pairs representing the headers and their values.
     * @throws MailerException
     */
    public function packageHeaders() {
        $headers = array();

        $this->packageFrom($headers);
        $this->packageReplyTo($headers);
        $this->packageSender($headers);
        $this->packageMessageId($headers);
        $this->packagePriority($headers);
        $this->packageRequestConfirmation($headers);
        $this->packageSubject($headers);
        $this->packageCustomHeaders($headers);

        return $headers;
    }

    /**
     * Performs the logic required to prepare the From header to be included in an email. There is no existing
     * requirement in this class to provide a From header value before the packageHeaders method is called. Thus,
     * it is essential that packaging the headers also performs validation to verify that the headers and their
     * values are okay to include.
     *
     * @access private
     * @param array $headers required The headers array to fill that packaging will return.
     * @throws MailerException
     */
    private function packageFrom(&$headers) {
        $from = $this->getFrom();

        // validate that the From header is present, but its setter took care of validating the actual value so that
        // is not necessary
        if (is_null($from)) {
            throw new MailerException(
                "Invalid header: " . self::From . " cannot be null",
                MailerException::InvalidHeader
            );
        }

        // add the From header to the package as an array with an email address and a name
        $headers[self::From] = array($from->getEmail(), $from->getName());
    }

    /**
     * Performs the logic required to prepare the Reply-To header to be included in an email. There is no existing
     * requirement in this class to provide a Reply-To header value before the packageHeaders method is called. Thus,
     * it is essential that packaging the headers also performs validation to verify that the headers and their
     * values are okay to include.
     *
     * @access private
     * @param array $headers required The headers array to fill that packaging will return.
     */
    private function packageReplyTo(&$headers) {
        $replyTo = $this->getReplyTo();

        // only bother with packaging this header if there is a value
        if (!is_null($replyTo)) {
            // validate the header value
            if (!($replyTo instanceof EmailIdentity)) {
                //@todo stringify $replyTo and add it to the log
               $GLOBALS["log"]->warn("Invalid header: " . self::ReplyTo);
            } else {
                // add the Reply-To header to the package as an array with an email address and a name
                $headers[self::ReplyTo] = array($replyTo->getEmail(), $replyTo->getName());
            }
        }
    }

    /**
     * Performs the logic required to prepare the Sender header to be included in an email. There is no existing
     * requirement in this class to provide a Sender header value before the packageHeaders method is called. Thus,
     * it is essential that packaging the headers also performs validation to verify that the headers and their
     * values are okay to include.
     *
     * @access private
     * @param array $headers required The headers array to fill that packaging will return.
     */
    private function packageSender(&$headers) {
        $sender = $this->getSender();

        // only bother with packaging this header if there is a value
        if (!is_null($sender)) {
            // validate the header value
            if (!($sender instanceof EmailIdentity)) {
                //@todo stringify $sender and add it to the log
                $GLOBALS["log"]->warn("Invalid header: " . self::Sender);
            } else {
                // add the Sender header to the package; only the email address is accepted
                $headers[self::Sender] = $sender->getEmail();
            }
        }
    }

    /**
     * Performs the logic required to prepare the Message-ID header to be included in an email.
     *
     * @access private
     * @param array $headers required The headers array to fill that packaging will return.
     */
    private function packageMessageId(&$headers) {
        $messageId = $this->getMessageId();

        // only bother with packaging this header if there is a value
        if (!is_null($messageId)) {
            // add the Message-ID header to the package
            $headers[self::MessageId] = $messageId;
        }
    }

    /**
     * Prepares the Priority header to be included in an email. The Priority header is defaulted by the constructor
     * and the its setter includes validation, so it can simply be added without performing any validation.
     *
     * @access private
     * @param array $headers required The headers array to fill that packaging will return.
     */
    private function packagePriority(&$headers) {
        // add the Priority header to the package
        $headers[self::Priority] = $this->getPriority();
    }

    /**
     * Performs the logic required to prepare the Disposition-Notification-To header to be included in an email. The
     * Disposition-Notification-To header is defaulted by the constructor and the its setter includes validation, so
     * the only necessary logic is to determine if it should be included and what value should be used.
     *
     * @access private
     * @param array $headers required The headers array to fill that packaging will return.
     */
    private function packageRequestConfirmation(&$headers) {
        // only bother with packaging this header if the request is true
        if ($this->getRequestConfirmation()) {
            $sender = $this->getSender();

            if (!is_null($sender)) {
                // use the Sender email address if it exists
                // no validation on $from because this method would not have been reached if the requisite validation
                // in packageFrom had failed
                $headers[self::DispositionNotificationTo] = $sender->getEmail();
            } else {
                // otherwise use the From email address
                // no validation on $from because this method would not have been reached if the requisite validation
                // in packageFrom had failed
                $from                                     = $this->getFrom();
                $headers[self::DispositionNotificationTo] = $from->getEmail();
            }
        }
    }

    /**
     * Performs the logic required to prepare the Subject header to be included in an email. There is no existing
     * requirement in this class to provide a Subject header value before the packageHeaders method is called. Thus,
     * it is essential that packaging the headers also performs validation to verify that the headers and their
     * values are okay to include.
     *
     * @access private
     * @param array $headers required The headers array to fill that packaging will return.
     * @throws MailerException
     */
    private function packageSubject(&$headers) {
        $subject = $this->getSubject();

        // validate that the Subject header is present, but its setter took care of validating the actual value so that
        // is not necessary
        if (is_null($subject)) {
            throw new MailerException(
                "Invalid header: " . self::Subject . " cannot be null",
                MailerException::InvalidHeader
            );
        }

        // add the Subject header to the package
        $headers[self::Subject] = $subject;
    }

    /**
     * Custom headers are not required and there is no true validation to perform on the values, since the headers are
     * custom. So this method simply adds the custom headers to the package as they are.
     *
     * @access private
     * @param array $headers required The headers array to fill that packaging will return.
     */
    private function packageCustomHeaders(&$headers) {
        foreach ($this->custom as $key => $value) {
            // add a custom header to the package
            $headers[$key] = $value;
        }
    }

    /**
     * @access private
     * @param EmailIdentity $from required
     */
    private function setFrom(EmailIdentity $from) {
        $this->from = $from;
    }

    /**
     * @access private
     * @param EmailIdentity $replyTo required
     */
    private function setReplyTo(EmailIdentity $replyTo) {
        $this->replyTo = $replyTo;
    }

    /**
     * @access public
     * @param EmailIdentity $sender required
     */
    private function setSender(EmailIdentity $sender) {
        $this->sender = $sender;
    }

    /**
     * @access private
     * @param int $priority
     * @todo report if not changing the header?
     */
    private function setPriority($priority = 3) {
        if (is_integer($priority)) {
            // don't change the Priority header if it's not a valid parameter
            $this->priority = $priority;
        }
    }

    /**
     * @access private
     * @param bool $request
     * @todo report if not changing the header?
     */
    private function setRequestConfirmation($request = false) {
        if (is_bool($request)) {
            // don't change the Disposition-Notification-To header if it's not a valid parameter
            $this->requestConfirmation = $request;
        }
    }

    /**
     * @access private
     * @param string $key   required Should look like the real header it represents.
     * @param string $value required The value of the header.
     * @throws MailerException
     */
    private function addCustomHeader($key, $value) {
        if (is_string($key) && is_string($value)) {
            $this->custom[$key] = $value;
        } else {
            throw new MailerException(
                "Invalid custom header: '{$key}' and '{$value}' must be strings",
                MailerException::InvalidHeader
            );
        }
    }
}
