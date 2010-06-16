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
 * $Id: WebToLeadFormSave.php 17399 2006-10-31 19:18:15 +0000 (Tue, 23 Nov 2006) Vineet $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
require_once('include/formbase.php');
require_once('include/SugarTinyMCE.php');



global $mod_strings;
global $app_strings;


$rawsource = false;

if(!empty($_REQUEST['body_html'])){
  $dir_path = "{$GLOBALS['sugar_config']['cache_dir']}generated_forms/";
  if(!file_exists($dir_path)){
  	sugar_mkdir($dir_path,0777);  	
  }
  //Check to ensure we have <html> tags in the form. Without them, IE8 will attempt to display the page as XML.
  $rawsource = $_REQUEST['body_html'];
  
  $SugarTiny =  new SugarTinyMCE();
  $rawsource = $SugarTiny->cleanEncodedMCEHtml($rawsource);
  $html = from_html($rawsource);
  
  if (stripos($html, "<html") === false)
  {
  	$html = "<html><body>" . $html . "</body></html>"; 
  }
  $file = $dir_path.'WebToLeadForm_'.time().'.html';
  $fp = sugar_fopen($file,'wb');
  fwrite($fp, $html);
  fclose($fp);	 
} 
$xtpl=new XTemplate ('modules/Campaigns/WebToLeadDownloadForm.html');
$xtpl->assign("MOD", $mod_strings);
$xtpl->assign("APP", $app_strings);

$webformlink = "<b>$mod_strings[LBL_DOWNLOAD_TEXT_WEB_TO_LEAD_FORM]</b><br/>";
$webformlink .= "<a href={$GLOBALS['sugar_config']['cache_dir']}generated_forms/WebToLeadForm_".time().".html>$mod_strings[LBL_DOWNLOAD_WEB_TO_LEAD_FORM]</a>";
$xtpl->assign("LINK_TO_WEB_FORM",$webformlink);
if ($rawsource !== false)
{
	$xtpl->assign("RAW_SOURCE", $rawsource);
	$xtpl->parse("main.copy_source");
}
	$xtpl->parse("main");
$xtpl->out("main");

?>