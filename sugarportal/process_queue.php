<?php
if (!defined("sugarEntry")) define("sugarEntry", true);
/*
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright(C) 2005 SugarCRM, Inc.; All Rights Reserved.
 */

// $Id: process_queue.php,v 1.25 2006/06/06 17:58:54 majed Exp $

// FILE SUGARCRM flav=pro ONLY 
$modListHeader = array();
require_once("include/modules.php");
require_once("config.php");
require_once("modules/Users/User.php");
require_once("modules/Administration/Administration.php");
require_once("modules/Reports/SavedReport.php");
require_once("modules/Reports/schedule/ReportSchedule.php");
require_once("modules/Reports/templates/templates_pdf.php");
require_once("include/SugarPHPMailer.php");
require_once("log4php/LoggerManager.php");
require_once("modules/ACL/ACLController.php");
require_once("include/utils.php");

clean_special_arguments();
// cn: set php.ini settings at entry points
setPhpIniSettings();

$GLOBALS["log"] = LoggerManager::getLogger("SugarCRM");

global $sugar_config;

$language         = $sugar_config["default_language"];
$app_list_strings = return_app_list_strings_language($language);
$app_strings      = return_application_language($language);

$reportSchedule = new ReportSchedule();
$reportsToEmail = $reportSchedule->get_reports_to_email();

// BEGIN SUGARCRM flav=int ONLY 
//Process Enterprise Schedule reports via CSV
require_once("modules/ReportMaker/process_scheduled.php");
// END SUGARCRM flav=int ONLY

global $report_modules,
       $modListHeader;

foreach ($reportsToEmail as $scheduleId => $scheduleInfo) {
    $GLOBALS["log"]->debug("-----> in Reports foreach() loop");

    $user = new User();
    $user->retrieve($scheduleInfo["user_id"]);

    $current_user = $user;

    $modListHeader  = query_module_access_list($current_user);
    $report_modules = getAllowedReportModules($modListHeader);

    if (empty($user->email1)) {
        if (empty($user->email2)) {
            $recipientEmailAddress = "";
        } else {
            $recipientEmailAddress = $user->email2;
        }
    } else {
        $recipientEmailAddress = $user->email1;
    }

    $recipientName = empty($user->first_name) ? $user->last_name : $user->first_name . " " . $user->last_name;

    $theme       = $sugar_config["default_theme"];
    $savedReport = new SavedReport();
    $savedReport->retrieve($scheduleInfo["report_id"]);

    $GLOBALS["log"]->debug("-----> Generating Reporter");
    $reporter = new Report(html_entity_decode($savedReport->content));

    $GLOBALS["log"]->debug("-----> Reporter settings attributes");
    $reporter->layout_manager->setAttribute("no_sort", 1);
    $module_for_lang = $reporter->module;
    $mod_strings     = return_module_language($language, "Reports");

    $GLOBALS["log"]->debug("-----> Reporter Handling PDF output");
    $reportFilename = template_handle_pdf($reporter, false);

    $GLOBALS["log"]->debug("-----> Generating SugarPHPMailer");
    $mail = new SugarPHPMailer();
    $mail->AddAddress($recipientEmailAddress, $recipientName);

    $admin = new Administration();
    $admin->retrieveSettings();

    if ($admin->settings["mail_sendtype"] == "SMTP") {
        $mail->Mailer = "smtp";
        $mail->Host   = $admin->settings["mail_smtpserver"];
        $mail->Port   = $admin->settings["mail_smtpport"];

        if ($admin->settings["mail_smtpauth_req"]) {
            $mail->SMTPAuth = TRUE;
            $mail->Username = $admin->settings["mail_smtpuser"];
            $mail->Password = $admin->settings["mail_smtppass"];
        }
    } else {
        $mail->Mailer = "sendmail";
    }

    $mail->From     = $admin->settings["notify_fromaddress"];
    $mail->FromName = empty($admin->settings["notify_fromname"]) ? " " : $admin->settings["notify_fromname"];
    $mail->Subject  = empty($reporter->report_def["report_name"]) ? "Report" : $reporter->report_def["report_name"];
    $cr             = array("\r", "\n");
    $attachmentName = str_replace(" ", "_", str_replace($cr, "", $mail->Subject) . ".pdf");

    $mail->AddAttachment($reportFilename, $attachmentName, "base64", "application/pdf");

    $body = $mod_strings["LBL_HELLO"];

    if ($recipientName != "") {
        $body .= " {$recipientName}";
    }

    $body .= ",\n\n" .
             $mod_strings["LBL_SCHEDULED_REPORT_MSG_INTRO"] .
             $savedReport->date_entered .
             $mod_strings["LBL_SCHEDULED_REPORT_MSG_BODY1"] .
             $reporter->report_def["report_name"] .
             $mod_strings["LBL_SCHEDULED_REPORT_MSG_BODY2"];
    $mail->Body = $body;

    if ($recipientEmailAddress == "") {
        $GLOBALS["log"]->info("No email address for {$recipientName}");
    } else {
        $GLOBALS["log"]->debug("-----> Sending PDF via Email to [ {$mail->to[0][1]} ]");

        if ($mail->Send()) {
            $GLOBALS["log"]->debug("-----> Send successful");
            $reportSchedule->update_next_run_time($scheduleInfo["id"], $scheduleInfo["next_run"], $scheduleInfo["time_interval"]);
        } else {
            $GLOBALS["log"]->fatal("Mail error: {$mail->ErrorInfo}");
        }
    }

    $GLOBALS["log"]->debug("-----> Removing temporary PDF file");
    unlink($reportFilename);
}

sugar_cleanup(false); // continue script execution so that if run from Scheduler, job status will be set back to "Active"
