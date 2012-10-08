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

require_once "lib/phpmailer/class.phpmailer.php"; // needs the PHPMailer library
require_once "lib/phpmailer/class.smtp.php";      // required to establish the SMTP connection prior to PHPMailer's
                                                  // send for error handling purposes
require_once "BaseMailer.php";                    // requires BaseMailer in order to extend it
require_once "SmtpMailerConfiguration.php";       // needs to take on an SmtpMailerConfiguration

/**
 * This class implements the basic functionality that is expected from a Mailer that uses PHPMailer to deliver its
 * messages.
 *
 * @extends BaseMailer
 */
class SmtpMailer extends BaseMailer
{
    // constants used for documenting which mail transmission protocols are valid
    const MailTransmissionProtocolSmtp = "smtp";

    /**
     * Performs the send of an email using PHPMailer (currently version 5.2.1).
     *
     * @access public
     * @throws MailerException
     */
    public function send() {
        $mailer = $this->generateMailer(); // get a fresh PHPMailer object

        $this->transferConfigurations($mailer); // transfer the configurations to set up the PHPMailer object before
                                                // attempting to send with it
        $this->connectToHost($mailer);          // connect to the SMTP server
        $this->transferHeaders($mailer);        // transfer the email headers to PHPMailer
        $this->transferRecipients($mailer);     // transfer the recipients to PHPMailer
        $this->transferBody($mailer);           // transfer the message to PHPMailer
        $this->transferAttachments($mailer);    // transfer the attachments to PHPMailer

        try {
            // send the email with PHPMailer
            $mailer->Send();
        } catch (Exception $e) {
            // eat the phpmailerException but use it's message to provide context for the failure
            throw new MailerException("Failed to send the email: " . $e->getMessage(), MailerException::FailedToSend);
        }
    }

    /**
     * Performs any logic necessary to instantiate an object of the Mailer of choice and return it.
     *
     * @access protected
     * @return PHPMailer
     */
    protected function generateMailer() {
        return new PHPMailer(true); // use PHPMailer with exceptions
    }

    /**
     * Transfers the configurations to set up the PHPMailer object before attempting to send with it.
     *
     * @access protected
     * @param PHPMailer $mailer
     */
    protected function transferConfigurations(PHPMailer &$mailer) {
        // explicitly set the language even though PHPMailer will do it on its own
        // this call could fail (only if English is not used), but it should be safe to ignore it
        $mailer->SetLanguage();

        // transfer the basic configurations to PHPMailer
        $mailer->Mailer   = self::MailTransmissionProtocolSmtp; // only use SMTP to send email with PHPMailer
        $mailer->Hostname = $this->config->getHostname();
        $mailer->CharSet  = $this->config->getCharset();
        $mailer->Encoding = $this->config->getEncoding();
        $mailer->WordWrap = $this->config->getWordwrap();

        // transfer the SMTP configurations to PHPMailer
        $mailer->Host       = $this->config->getHost();
        $mailer->Port       = $this->config->getPort();
        $mailer->SMTPSecure = $this->config->getSecurityProtocol();
        $mailer->SMTPAuth   = $this->config->isAuthenticationRequired();
        $mailer->Username   = $this->config->getUsername();
        $mailer->Password   = from_html($this->config->getPassword());  // perform HTML character translations
    }

    /**
     * Connects to the SMTP server specified in the PHPMailer configurations. This allows us to establish the connection
     * to the SMTP server and catch any errors associated with the connection instead of letting PHPMailer establish
     * the connection at send time, which would result in losing the context of the failure.
     *
     * @access protected
     * @param PHPMailer $mailer
     * @throws MailerException
     */
    protected function connectToHost(PHPMailer &$mailer) {
        try {
            // have PHPMailer attempt to connect to the SMTP server
            $mailer->SmtpConnect();
        } catch (Exception $e) {
            //@todo need to tell the class what error messages to use, so the following is for reference only
//            global $app_strings;
//            if(isset($this->oe) && $this->oe->type == "system") {
//                $this->SetError($app_strings['LBL_EMAIL_INVALID_SYSTEM_OUTBOUND']);
//            } else {
//                $this->SetError($app_strings['LBL_EMAIL_INVALID_PERSONAL_OUTBOUND']);
//            }
            throw new MailerException(
                "Failed to connect to the remote server",
                MailerException::FailedToConnectToRemoteServer
            );
        }
    }

    /**
     * Transfers the email headers to PHPMailer.
     *
     * @access protected
     * @param PHPMailer $mailer
     * @throws MailerException
     * @throws phpmailerException
     */
    protected function transferHeaders(PHPMailer &$mailer) {
        // will throw an exception if an error occurs; will let it bubble up
        $headers = $this->headers->packageHeaders();

        foreach ($headers as $key => $value) {
            switch ($key) {
                case EmailHeaders::From:
                    if (!empty($value[1])) {
                        // perform character set translations on the name in the From header
                        $value[1] = $this->config->getLocale()->translateCharset(
                            $value[1],
                            "UTF-8",
                            $this->config->getCharset()
                        );

                        // perform HTML character translations on the From name
                        $value[1] = from_html($value[1]);
                    }

                    // set PHPMailer's From so that PHPMailer can correctly construct the From header at send time
                    try {
                        $mailer->SetFrom($value[0], $value[1]);
                    } catch (Exception $e) {
                        throw new MailerException(
                            "Failed to add the " . EmailHeaders::From . " header: " . $e->getMessage(),
                            MailerException::FailedToTransferHeaders
                        );
                    }

                    break;
                case EmailHeaders::ReplyTo:
                    // only allow PHPMailer to automatically set the Reply-To if this header isn't provided
                    // so clear PHPMailer's Reply-To array if this header is provided
                    $mailer->ClearReplyTos();

                    if (!empty($value[1])) {
                        // perform character set translations on the name in the Reply-To header
                        $value[1] = $this->config->getLocale()->translateCharset(
                            $value[1],
                            "UTF-8",
                            $this->config->getCharset()
                        );

                        // perform HTML character translations on the Reply-To name
                        $value[1] = from_html($value[1]);
                    }

                    // set PHPMailer's ReplyTo so that PHPMailer can correctly construct the Reply-To header at send
                    // time
                    try {
                        // PHPMailer's AddReplyTo could return true or false or allow an exception to bubble up. We
                        // want the same behavior to be applied for both false and on error, so throw a
                        // phpMailerException on failure.
                        if (!$mailer->AddReplyTo($value[0], $value[1])) {
                            // doesn't matter what the message is since we're going to eat phpmailerExceptions
                            throw new phpmailerException();
                        }
                    } catch (Exception $e) {
                        throw new MailerException(
                            "Failed to add the " . EmailHeaders::ReplyTo . " header: " . $e->getMessage(),
                            MailerException::FailedToTransferHeaders
                        );
                    }

                    break;
                case EmailHeaders::Sender:
                    // set PHPMailer's Sender so that PHPMailer can correctly construct the Sender header at send time
                    $mailer->Sender = $value;
                    break;
                case EmailHeaders::MessageId:
                    // set PHPMailer's MessageId so that PHPMailer can correctly construct the Message-ID header at
                    // send time
                    $mailer->MessageID = $value;
                    break;
                case EmailHeaders::Priority:
                    // set PHPMailer's Priority so that PHPMailer can correctly construct the Priority header at send
                    // time
                    $mailer->Priority = $value;
                    break;
                case EmailHeaders::DispositionNotificationTo:
                    // set PHPMailer's ConfirmReadingTo so that PHPMailer can correctly construct the
                    // Disposition-Notification-To header at send time
                    $mailer->ConfirmReadingTo = $value;
                    break;
                case EmailHeaders::Subject:
                    // perform character set translations on the subject
                    $value = $this->config->getLocale()->translateCharset($value, "UTF-8", $this->config->getCharset());

                    // perform HTML character translations on the subject
                    $value = from_html($value);

                    // set PHPMailer's Subject so that PHPMailer can correctly construct the Subject header at send time
                    $mailer->Subject = $value;
                    break;
                default:
                    // it's not known, so it must be a custom header; add it to PHPMailer's custom headers array
                    //@todo any need for charset translations for from_html on the value?
                    $mailer->AddCustomHeader("{$key}:{$value}");
                    break;
            }
        }
    }

    /**
     * Transfers the recipients to PHPMailer.
     *
     * @access protected
     * @param PHPMailer $mailer
     */
    protected function transferRecipients(PHPMailer &$mailer) {
        // clear the recipients from PHPMailer; only necessary if the PHPMailer object can be re-used, but there is
        // no harm in doing it anyway
        $mailer->ClearAllRecipients();

        // get the recipients
        $to  = $this->recipients->getTo();
        $cc  = $this->recipients->getCc();
        $bcc = $this->recipients->getBcc();

        //@todo should you be able to initiate a send without any To recipients?
        foreach ($to as $recipient) {
            $recipient->decode();

            // perform MIME character set translations on the recipient's name
            //@todo why translateCharsetMIME here but translateCharset on other headers?
            $name = $this->config->getLocale()->translateCharsetMIME(
                $recipient->getName(),
                "UTF-8",
                $this->config->getCharset()
            );

            try {
                // attempt to add the recipient to PHPMailer in the To list
                $mailer->AddAddress($recipient->getEmail(), $name);
            } catch (Exception $e) {
                // eat the exception for now as we'll send to as many valid recipients as possible
            }
        }

        foreach ($cc as $recipient) {
            $recipient->decode();

            // perform MIME character set translations on the recipient's name
            //@todo why translateCharsetMIME here but translateCharset on other headers?
            $name = $this->config->getLocale()->translateCharsetMIME(
                $recipient->getName(),
                "UTF-8",
                $this->config->getCharset()
            );

            try {
                // attempt to add the recipient to PHPMailer in the CC list
                $mailer->AddCC($recipient->getEmail(), $name);
            } catch (Exception $e) {
                // eat the exception for now as we'll send to as many valid recipients as possible
            }
        }

        foreach ($bcc as $recipient) {
            $recipient->decode();

            // perform MIME character set translations on the recipient's name
            //@todo why translateCharsetMIME here but translateCharset on other headers?
            $name = $this->config->getLocale()->translateCharsetMIME(
                $recipient->getName(),
                "UTF-8",
                $this->config->getCharset()
            );

            try {
                // attempt to add the recipient to PHPMailer in the BCC list
                $mailer->AddBCC($recipient->getEmail(), $name);
            } catch (Exception $e) {
                // eat the exception for now as we'll send to as many valid recipients as possible
            }
        }
    }

    /**
     * Transfers the message to PHPMailer.
     *
     * @access protected
     * @param PHPMailer $mailer
     * @throws MailerException
     */
    protected function transferBody(PHPMailer &$mailer) {
        $hasText = $this->hasMessagePart($this->textBody); // is there a plain-text part?
        $hasHtml = $this->hasMessagePart($this->htmlBody); // is there an HTML part?

        // make sure there is at least one message part
        if (!$hasText && !$hasHtml) {
            throw new MailerException("No email body was provided", MailerException::InvalidMessageBody);
        }

        if ($hasText) {
            // perform character set translations on the plain-text body
            $textBody = $this->prepareTextBody($this->textBody);
        }

        if ($hasHtml) {
            // perform character set translations HTML body
            $htmlBody = $this->prepareHtmlBody($this->htmlBody);

            // there is an HTML part so set up PHPMailer appropriately for sending a multi-part email
            $mailer->IsHTML(true);
            $mailer->Encoding = Encoding::Base64; // so that embedded images are encoded properly
            $mailer->Body     = $htmlBody;        // the HTML part is the primary message body
        }

        if ($hasText && $hasHtml) {
            // it's a multi-part email with both the plain-text and HTML parts
            $mailer->AltBody = $textBody; // the plain-text part is the alternate message body
        } elseif ($hasText) {
            // there is only a plain-text part so set up PHPMailer appropriately for sending a text-only email
            $mailer->Body = $textBody; // the plain-text part is the primary message body
        } else {
            // you should never actually send an email without a plain-text part, but we'll allow it (for now)
        }
    }

    /**
     * Transfers both file attachments and embedded images to PHPMailer.
     *
     * @access protected
     * @param PHPMailer $mailer
     * @throws MailerException
     */
    protected function transferAttachments(PHPMailer &$mailer) {
        // clear the attachments from PHPMailer; only necessary if the PHPMailer object can be re-used, but there is
        // no harm in doing it anyway
        $mailer->ClearAttachments();

        foreach ($this->attachments as $attachment) {
            if ($attachment instanceof EmbeddedImage) {
                // it's an embedded image

                // perform character set and HTML character translations on the file name
                $name = $this->formatter->translateCharacters(
                    $attachment->getName(),
                    $this->config->getLocale(),
                    $this->config->getCharset()
                );

                // transfer the image to PHPMailer so it can be embedded correctly at send time
                if (!$mailer->AddEmbeddedImage(
                    $attachment->getPath(),
                    $attachment->getCid(),
                    $name,
                    $attachment->getEncoding(),
                    $attachment->getMimeType())
                ) {
                    throw new MailerException(
                        "Failed to embed the image at " . $attachment->getPath(),
                        MailerException::InvalidAttachment
                    );
                }
            } elseif ($attachment instanceof Attachment) {
                // it's a normal file attachment
                try {
                    // perform character set and HTML character translations on the file name
                    $name = $this->formatter->translateCharacters(
                        $attachment->getName(),
                        $this->config->getLocale(),
                        $this->config->getCharset()
                    );

                    // transfer the attachment to PHPMailer so it can be attached correctly at send time
                    $mailer->AddAttachment(
                        $attachment->getPath(),
                        $name,
                        $attachment->getEncoding(),
                        $attachment->getMimeType());
                } catch (Exception $e) {
                    throw new MailerException(
                        "Failed to add the attachment at " . $attachment->getPath() . ": " . $e->getMessage(),
                        MailerException::InvalidAttachment
                    );
                }
            } else {
                // oops!
                // there really shouldn't be a way to get an attachment into the Mailer that isn't an Attachment
                // or an EmbeddedImage, but it's probably best to verify it anyway
                throw new MailerException("Invalid attachment type", MailerException::InvalidAttachment);
            }
        }
    }

    /**
     * Performs character set translations on the plain-text body based on the charset defined in the configuration.
     *
     * @access protected
     * @param string $body required The plain-text body that is to be translated.
     * @return string The translated body.
     */
    protected function prepareTextBody($body) {
        $body = $this->formatter->formatTextBody($body);
        $body = $this->config->getLocale()->translateCharset($body, "UTF-8", $this->config->getCharset());
        $body = from_html($body); // perform HTML character translations on the plain-text body

        return $body;
    }

    /**
     * Enforces RFC compliance to the structure of the HTML body and performs character set translations on the body
     * based on the charset defined in the configuration.
     *
     * @access protected
     * @param string $body required The HTML body that is to be translated.
     * @return string The compliant and translated body.
     */
    protected function prepareHtmlBody($body) {
        $formatted = $this->formatter->formatHtmlBody($body);
        $body      = $formatted["body"];
        $images    = $formatted["images"];

        foreach ($images as $embeddedImage) {
            $this->addEmbeddedImage($embeddedImage);
        }

        $body = $this->forceRfcComplianceOnHtmlBody($body);
        $body = $this->config->getLocale()->translateCharset($body, "UTF-8", $this->config->getCharset());
        $body = from_html($body); // perform HTML character translations on the HTML body

        return $body;
    }

    /**
     * HTML message bodies must include a doctype and appropriate html, head, and body elements to be RFC compliant.
     * Enforces this compliance by wrapping the body with the compliant document structure if the body is not found
     * to be compliant.
     *
     * @note This is flawed because the absence of "<html" doesn't also indicate the absence of "</body>" or "</html>".
     *       Yet, the assumption is that it is an indication of said absence. Thus, there is a potential for the
     *       document body to become invalid HTML.
     *
     * @param string $body required The HTML body to test for compliance and modify, if necessary.
     * @return string The compliant HTML body.
     */
    protected function forceRfcComplianceOnHtmlBody($body) {
        if (strpos($body, "<html") === false) {
            $subject   = $this->headers->getSubject(); // used for the document title
            $charset   = $this->config->getCharset();  // used for the document charset
            $language  = get_language_header();

            // prepend the document head and append the footer elements to the body
            $body      = <<<eoq
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" {$language}>
<head>
<meta http-equiv="Content-Type" content="text/html; charset={$charset}" />
<title>{$subject}</title>
</head>
<body>
{$body}
</body>
</html>
eoq;
        }

        return $body;
    }
}
