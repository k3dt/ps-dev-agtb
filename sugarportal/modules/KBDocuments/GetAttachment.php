<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
/*********************************************************************************
 * $Id: GetAttachment.php,v 1.74 2006/06/06 17:57:56 majed Exp $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
global $portal;

// Bug 31864: "Downloading attachments from portal not working on OD2"
// Similar to other download bugs we encountered on main instance functionality in relation to OD2 stack (i.e. downloading quote PDFs - bug 27537).
// Chris Raffle - We should also add this code to line 2 of ./modules/KBDocuments/GetAttachment.php
if(function_exists('gzopen') 
    && headers_sent() == false 
    && (ini_get('zlib.output_compression') == 1)) 
{
    ini_set('zlib.output_compression', 'Off');
}

//Retrieve document via id
$result = $portal->getKBDocumentAttachment($_REQUEST['id']);
$output = base64_decode($result['note_attachment']['file']);
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-type: application/force-download");

// explicitly specify 8bit encoding to handle the case when strlen() function is
// overloaded by mbstring extension (bug #49728)
header('Content-Length: ' . mb_strlen($output, '8bit'));
header("Content-disposition: attachment; filename=\"".$result['note_attachment']['filename']."\";");
//header("Pragma: ");
header("Expires: 0");
set_time_limit(0);
ob_clean();
ob_start();
echo $output;
//echo base64_decode($result['note_attachment']['file']);
ob_flush();
?>
