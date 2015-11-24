<?php
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
 * $Id: EditView.php 13951 2006-06-12 19:44:03Z awu $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
require_once('XTemplate/xtpl.php');
require_once('data/Tracker.php');
require_once('modules/Songs/Song.php');

require_once('modules/Administration/Administration.php');
$admin = new Administration();
$admin->retrieveSettings("notify");

global $app_strings;
global $app_list_strings;
global $mod_strings;

$focus =& new Song();

if (!isset($_REQUEST['record'])) $_REQUEST['record'] = "";

if(isset($_REQUEST['record']) && isset($_REQUEST['record'])) {
    $focus->retrieve($_REQUEST['record']);
}

//if duplicate record request then clear the Primary key(id) value.
if(isset($_REQUEST['isDuplicate']) && $_REQUEST['isDuplicate'] == '1') {
	$focus->id = "";
}

echo "\n<p>\n";
echo get_module_title($mod_strings['LBL_MODULE_NAME'], $mod_strings['LBL_MODULE_NAME'].": ".$focus->title, true);
echo "\n</p>\n";

global $theme;
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";
require_once($theme_path.'layout_utils.php');

$log->info("Song edit view");
$xtpl=new XTemplate ('modules/Songs/EditView.html');
$xtpl->assign("MOD", $mod_strings);
$xtpl->assign("APP", $app_strings);

if (isset($_REQUEST['return_module'])) $xtpl->assign("RETURN_MODULE", $_REQUEST['return_module']);
if (isset($_REQUEST['return_action'])) $xtpl->assign("RETURN_ACTION", $_REQUEST['return_action']);
if (isset($_REQUEST['return_id'])) $xtpl->assign("RETURN_ID", $_REQUEST['return_id']);
$xtpl->assign("JAVASCRIPT", get_set_focus_js());
$xtpl->assign("IMAGE_PATH", $image_path);
$xtpl->assign("ID", $focus->id);

$xtpl->assign("TITLE", $focus->title);
if (isset($focus->genre)) $xtpl->assign("GENRE_OPTIONS", get_select_options_with_id($app_list_strings['song_genre_dom'], $focus->genre));
else $xtpl->assign("GENRE_OPTIONS", get_select_options_with_id($app_list_strings['song_genre_dom'], ''));

if (isset($focus->format)) $xtpl->assign("FORMAT_OPTIONS", get_select_options_with_id($app_list_strings['song_format_dom'], $focus->format));
else $xtpl->assign("FORMAT_OPTIONS", get_select_options_with_id($app_list_strings['song_format_dom'], ''));
$xtpl->assign("LENGTH", $focus->length);

$xtpl->assign("BITRATE", $focus->bitrate);
if ($focus->explicit == 1) {
	$xtpl->assign("EXPLICIT", "checked");
}

$xtpl->assign("DESCRIPTION", $focus->description);

if (isset($_REQUEST['contact_id'])) {
	$xtpl->assign("CONTACT_ID", $_REQUEST['contact_id']);
}	
if (isset($_REQUEST['product_id'])) {
	$xtpl->assign("PRODUCT_ID", $_REQUEST['product_id']);
}	

//Add Custom Fields
require_once('modules/DynamicFields/templates/Files/EditView.php');


$xtpl->assign("THEME", $theme);
$xtpl->parse("main");
$xtpl->out("main");

?>
