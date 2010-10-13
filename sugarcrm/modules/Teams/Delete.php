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
 * $Id: Delete.php 50752 2009-09-10 22:18:28Z dwong $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

global $current_user;

if (!is_admin($current_user)&& !is_admin_for_module($GLOBALS['current_user'],'Users')) sugar_die("Unauthorized access to administration.");

global $mod_strings;
global $app_strings;


$focus = new Team();
$focus->retrieve($_REQUEST['record']);

//Check if there are module records where this team is assigned to in a team_set_id
//if so, redirect to prompt the Administrator to select a new team
if($focus->has_records_in_modules()) {
   header("Location: index.php?module=Teams&action=ReassignTeams&record={$focus->id}");
} else {
	//todo: Verify that no items are still assigned to this team.
	if($focus->id == $focus->global_team) {
		$msg = $GLOBALS['app_strings']['LBL_MASSUPDATE_DELETE_GLOBAL_TEAM'];
		$GLOBALS['log']->fatal($msg);
		header('Location: index.php?module=Teams&action=DetailView&record='.$focus->id.'&message=LBL_MASSUPDATE_DELETE_GLOBAL_TEAM');
		return;
	}
	
	//Check if the associated user is deleted
	$user = new User();
	$user->retrieve($focus->associated_user_id);
	if($focus->private == 1 && (!empty($user->id) && $user->deleted != 1))
	{
		$msg = string_format($GLOBALS['app_strings']['LBL_MASSUPDATE_DELETE_USER_EXISTS'], array($user->user_name));
		$GLOBALS['log']->fatal($msg);
		header('Location: index.php?module=Teams&action=DetailView&record='.$focus->id.'&message=LBL_MASSUPDATE_DELETE_USER_EXISTS');
		return;
	}

	// Delete all team memberships for this team_id.
	$query = "delete from team_memberships where team_id='{$focus->id}'";
	$GLOBALS['db']->query($query,true,"Error deleting memberships while deleting team: ");

	// Delete the team record itself.
	$query = "delete from teams where id='{$focus->id}'";
	$GLOBALS['db']->query($query,true,"Error deleting team: ");
	
	require_once('modules/Teams/TeamSetManager.php');
	TeamSetManager::flushBackendCache();
	//clean up any team sets that use this team id
	TeamSetManager::removeTeamFromSets($focus->id);
		   
    // Take the item off the recently viewed lists
    $tracker = new Tracker();
    $tracker->makeInvisibleForAll($focus->id);$focus->mark_deleted();
    header("Location: index.php?module=Teams&action=index");
}
?>
