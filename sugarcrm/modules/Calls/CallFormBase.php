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
 * $Id: CallFormBase.php 56853 2010-06-08 02:36:54Z clee $
 * Description: Call Form Base
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

class CallFormBase{

function getFormBody($prefix, $mod='', $formname='',$cal_date='',$cal_time=''){
if(!ACLController::checkAccess('Calls', 'edit', true)){
		return '';
	}
global $mod_strings;
$temp_strings = $mod_strings;
if(!empty($mod)){
	global $current_language;
	$mod_strings = return_module_language($current_language, $mod);
}
global $app_strings;
global $app_list_strings;
global $current_user;
global $theme;


$lbl_subject = $mod_strings['LBL_SUBJECT'];
// Unimplemented until jscalendar language files are fixed
// global $current_language;
// global $default_language;
// global $cal_codes;
// Unimplemented until jscalendar language files are fixed
// $cal_lang = (empty($cal_codes[$current_language])) ? $cal_codes[$default_language] : $cal_codes[$current_language];

global $timedate;
$cal_lang = "en";
$cal_dateformat = $timedate->get_cal_date_format();

$lbl_required_symbol = $app_strings['LBL_REQUIRED_SYMBOL'];
$lbl_date = $mod_strings['LBL_DATE'];
$lbl_time = $mod_strings['LBL_TIME'];
$ntc_date_format = $timedate->get_user_date_format();
$ntc_time_format = '('.$timedate->get_user_time_format().')';

	$user_id = $current_user->id;
$default_status = $app_list_strings['call_status_default'];
$default_parent_type= $app_list_strings['record_type_default_key'];
$date = TimeDate::getInstance()->nowDb();
$default_date_start = $timedate->to_display_date($date,false);
$default_time_start = $timedate->to_display_time($date);
$time_ampm = $timedate->AMPMMenu($prefix,$default_time_start);
$lbl_save_button_title = $app_strings['LBL_SAVE_BUTTON_TITLE'];
$lbl_save_button_key = $app_strings['LBL_SAVE_BUTTON_KEY'];
$lbl_save_button_label = $app_strings['LBL_SAVE_BUTTON_LABEL'];
	$form =	<<<EOQ
			<form name="${formname}" onSubmit="return check_form('${formname}') "method="POST" action="index.php">
			<input type="hidden" name="${prefix}module" value="Calls">
			<input type="hidden" name="${prefix}action" value="Save">
				<input type="hidden" name="${prefix}record" value="">
			<input type="hidden"  name="${prefix}direction" value="Outbound">
			<input type="hidden" name="${prefix}status" value="${default_status}">
			<input type="hidden" name="${prefix}parent_type" value="${default_parent_type}">
			<input type="hidden" name="${prefix}assigned_user_id" value='${user_id}'>
			<input type="hidden" name="${prefix}duration_hours" value="1">
			<input type="hidden" name="${prefix}duration_minutes" value="0">
			<input type="hidden" name="${prefix}user_id" value="${user_id}">

		<table cellspacing="1" cellpadding="0" border="0">
<tr>
    <td colspan="2"><input type='radio' name='appointment' value='Call' class='radio' onchange='document.${formname}.module.value="Calls";' style='vertical-align: middle;' checked> <span scope="row">${mod_strings['LNK_NEW_CALL']}</span>
&nbsp;
&nbsp;
<input type='radio' name='appointment' value='Meeting' class='radio' onchange='document.${formname}.module.value="Meetings";'><span scope="row">${mod_strings['LNK_NEW_MEETING']}</span></td>
</tr>
<tr>
    <td colspan="2"><span scope="row">$lbl_subject</span>&nbsp;<span class="required">$lbl_required_symbol</span></td>
</tr>
<tr><td valign=top><input name='${prefix}name' size='30' maxlength='255' type="text"></td>
    <td><input name='${prefix}date_start' id='${formname}jscal_field' maxlength='10' type="hidden" value="${cal_date}"></td>
    <td><input name='${prefix}time_start' type="hidden" maxlength='10' value="{$cal_time}"></td>

			<script type="text/javascript">
//		Calendar.setup ({
//			inputField : "${formname}jscal_field", daFormat : "$cal_dateformat" ifFormat : "$cal_dateformat", showsTime : false, button : "${formname}jscal_trigger", singleClick : true, step : 1, weekNumbers:false
//		});
		</script>



EOQ;



$javascript = new javascript();
$javascript->setFormName($formname);
$javascript->setSugarBean(new Call());
$javascript->addRequiredFields($prefix);
$form .=$javascript->getScript();
$form .= "<td align=\"left\" valign=top><input title='$lbl_save_button_title' accessKey='$lbl_save_button_key' class='button' type='submit' name='button' value=' $lbl_save_button_label ' ></td></tr></table></form>";
$mod_strings = $temp_strings;
return $form;

}
function getFormHeader($prefix, $mod='', $title=''){
	if(!ACLController::checkAccess('Calls', 'edit', true)){
		return '';
	}
	if(!empty($mod)){
	global $current_language;
	$mod_strings = return_module_language($current_language, $mod);
}else global $mod_strings;






if(!empty($title)){
	$the_form = get_left_form_header($title);
}else{
	$the_form = get_left_form_header($mod_strings['LBL_NEW_FORM_TITLE']);
}
$the_form .= <<<EOQ
		<form name="${prefix}CallSave" onSubmit="return check_form('${prefix}CallSave') "method="POST" action="index.php">
			<input type="hidden" name="${prefix}module" value="Calls">
			<input type="hidden" name="${prefix}action" value="Save">

EOQ;
return $the_form;
}
function getFormFooter($prefic, $mod=''){
	if(!ACLController::checkAccess('Calls', 'edit', true)){
		return '';
	}
global $app_strings;
global $app_list_strings;
$lbl_save_button_title = $app_strings['LBL_SAVE_BUTTON_TITLE'];
$lbl_save_button_key = $app_strings['LBL_SAVE_BUTTON_KEY'];
$lbl_save_button_label = $app_strings['LBL_SAVE_BUTTON_LABEL'];
$the_form = "	<p><input title='$lbl_save_button_title' accessKey='$lbl_save_button_key' class='button' type='submit' name='button' value=' $lbl_save_button_label ' ></p></form>";
$the_form .= get_left_form_footer();
$the_form .= get_validate_record_js();
return $the_form;
}

function getForm($prefix, $mod=''){
	if(!ACLController::checkAccess('Calls', 'edit', true)){
		return '';
	}
$the_form = $this->getFormHeader($prefix, $mod);
$the_form .= $this->getFormBody($prefix, $mod, "${prefix}CallSave");
$the_form .= $this->getFormFooter($prefix, $mod);

return $the_form;
}


function handleSave($prefix,$redirect=true,$useRequired=false) {


	require_once('include/formbase.php');

	global $current_user;
	global $timedate;

	//BUG 17418 MFH
	if (isset($_POST[$prefix.'duration_hours'])){
		$_POST[$prefix.'duration_hours'] = trim($_POST[$prefix.'duration_hours']);
	}

	$focus = new Call();

	if($useRequired && !checkRequired($prefix, array_keys($focus->required_fields))) {
		return null;
	}
    if ( !isset($_POST[$prefix.'reminder_checked']) or ($_POST[$prefix.'reminder_checked'] == 0)) {
        $GLOBALS['log']->debug(__FILE__.'('.__LINE__.'): No reminder checked, resetting the reminder_time');
        $_POST[$prefix.'reminder_time'] = -1;
    }

	if(!isset($_POST[$prefix.'reminder_time'])) {
        $GLOBALS['log']->debug(__FILE__.'('.__LINE__.'): Getting the users default reminder time');
		$_POST[$prefix.'reminder_time'] = $current_user->getPreference('reminder_time');
	}

	$time_format = $timedate->get_user_time_format();
    $time_separator = ":";
    if(preg_match('/\d+([^\d])\d+([^\d]*)/s', $time_format, $match)) {
       $time_separator = $match[1];
    }

	if(!empty($_POST[$prefix.'time_hour_start']) && empty($_POST[$prefix.'time_start'])) {
		$_POST[$prefix.'time_start'] = $_POST[$prefix.'time_hour_start']. $time_separator .$_POST[$prefix.'time_minute_start'];
	}

	if(isset($_POST[$prefix.'meridiem']) && !empty($_POST[$prefix.'meridiem'])) {
		$_POST[$prefix.'time_start'] = $timedate->merge_time_meridiem($_POST[$prefix.'time_start'],$timedate->get_time_format(), $_POST[$prefix.'meridiem']);
	}

	if(isset($_POST[$prefix.'time_start']) && strlen($_POST[$prefix.'date_start']) == 10) {
	   $_POST[$prefix.'date_start'] = $_POST[$prefix.'date_start'] . ' ' . $_POST[$prefix.'time_start'];
	}

	// retrieve happens here
	$focus = populateFromPost($prefix, $focus);
	if(!$focus->ACLAccess('Save')) {
	   ACLController::displayNoAccess(true);
	   sugar_cleanup(true);
	}

	//add assigned user and current user if this is the first time bean is saved
  	if(empty($focus->id) && !empty($_REQUEST['return_module']) && $_REQUEST['return_module'] =='Calls' && !empty($_REQUEST['return_action']) && $_REQUEST['return_action'] =='DetailView'){
		//if return action is set to detail view and return module to call, then this is from the long form, do not add the assigned user (only the current user)
		//The current user is already added to UI and we want to give the current user the option of opting out of meeting.
  		if($current_user->id != $_POST['assigned_user_id']){
  			$_POST['user_invitees'] .= ','.$_POST['assigned_user_id'].', ';
  			$_POST['user_invitees'] = str_replace(',,', ',', $_POST['user_invitees']);
  		}
  	}elseif (empty($focus->id) ){
	  	//this is not from long form so add assigned and current user automatically as there is no invitee list UI.
	  	//This call could be through an ajax call from subpanels or shortcut bar
	  	$_POST['user_invitees'] .= ','.$_POST['assigned_user_id'].', ';

	  	//add current user if the assigned to user is different than current user.
	  	if($current_user->id != $_POST['assigned_user_id']){
	  		$_POST['user_invitees'] .= ','.$current_user->id.', ';
	  	}

	  	//remove any double comma's introduced during appending
	    $_POST['user_invitees'] = str_replace(',,', ',', $_POST['user_invitees']);
  	}

    if( (isset($_POST['isSaveFromDetailView']) && $_POST['isSaveFromDetailView'] == 'true') ||
        (isset($_POST['is_ajax_call']) && !empty($_POST['is_ajax_call']) && !empty($focus->id))
    ){
        $focus->save(true);
        $return_id = $focus->id;
    }else{

	    if(empty($_REQUEST['return_module']) && empty($_REQUEST['return_action']) && $focus->status == 'Held'){
    		//if we are closing the call, and the request does not have a return module AND return action set, then
    		//the request is coming from a dashlet or subpanel close icon and there is no need to process user invitees,
    		//just save the current values.
    		$focus->save(true);
	    }else{
	    	///////////////////////////////////////////////////////////////////////////
	    	////	REMOVE INVITEE RELATIONSHIPS
	    	if(!empty($_POST['user_invitees'])) {
	    	   $userInvitees = explode(',', trim($_POST['user_invitees'], ','));
	    	} else {
	    	   $userInvitees = array();
	    	}

	        // Calculate which users to flag as deleted and which to add
	        $deleteUsers = array();
	    	$focus->load_relationship('users');
	    	// Get all users for the call
	    	$q = 'SELECT mu.user_id, mu.accept_status FROM calls_users mu WHERE mu.call_id = \''.$focus->id.'\'';
	    	$r = $focus->db->query($q);
	    	$acceptStatusUsers = array();
	    	while($a = $focus->db->fetchByAssoc($r)) {
	    		  if(!in_array($a['user_id'], $userInvitees)) {
	    		  	 $deleteUsers[$a['user_id']] = $a['user_id'];
	    		  } else {
	    		     $acceptStatusUsers[$a['user_id']] = $a['accept_status'];
	    		  }
	    	}

	    	if(count($deleteUsers) > 0) {
	    		$sql = '';
		    	foreach($deleteUsers as $u) {
	                $sql .= ",'" . $u . "'";
	    		}

	    		$sql = substr($sql, 1);
	    		// We could run a delete SQL statement here, but will just mark as deleted instead
	    		$sql = "UPDATE calls_users set deleted = 1 where user_id in ($sql) AND call_id = '". $focus->id . "'";
	    		$focus->db->query($sql);
	    	}

	        // Get all contacts for the call
	    	if(!empty($_POST['contact_invitees'])) {
	    	   $contactInvitees = explode(',', trim($_POST['contact_invitees'], ','));
	    	} else {
	    	   $contactInvitees = array();
	    	}

	        $deleteContacts = array();
	    	$focus->load_relationship('contacts');
	    	$q = 'SELECT mu.contact_id, mu.accept_status FROM calls_contacts mu WHERE mu.call_id = \''.$focus->id.'\'';
	    	$r = $focus->db->query($q);
	    	$acceptStatusContacts = array();
	    	while($a = $focus->db->fetchByAssoc($r)) {
	    		  if(!in_array($a['contact_id'], $contactInvitees)) {
	    		  	 $deleteContacts[$a['contact_id']] = $a['contact_id'];
	    		  }	else {
	    		  	 $acceptStatusContacts[$a['contact_id']] = $a['accept_status'];
	    		  }
	    	}

	    	if(count($deleteContacts) > 0) {
	    		$sql = '';
	    		foreach($deleteContacts as $u) {
	    		        $sql .= ",'" . $u . "'";
	    		}
	    		$sql = substr($sql, 1);
	    		// We could run a delete SQL statement here, but will just mark as deleted instead
	    		$sql = "UPDATE calls_contacts set deleted = 1 where contact_id in ($sql) AND call_id = '". $focus->id . "'";
	    		$focus->db->query($sql);
	    	}
	        //BEGIN SUGARCRM flav!=sales ONLY
	        if(!empty($_POST['lead_invitees'])) {
	    	   $leadInvitees = explode(',', trim($_POST['lead_invitees'], ','));
	    	} else {
	    	   $leadInvitees = array();
	    	}

	        // Calculate which leads to flag as deleted and which to add
	        $deleteLeads = array();
	    	$focus->load_relationship('leads');
	    	// Get all leads for the call
	    	$q = 'SELECT mu.lead_id, mu.accept_status FROM calls_leads mu WHERE mu.call_id = \''.$focus->id.'\'';
	    	$r = $focus->db->query($q);
	    	$acceptStatusLeads = array();
	    	while($a = $focus->db->fetchByAssoc($r)) {
	    		  if(!in_array($a['lead_id'], $leadInvitees)) {
	    		  	 $deleteLeads[$a['lead_id']] = $a['lead_id'];
	    		  } else {
	    		     $acceptStatusLeads[$a['user_id']] = $a['accept_status'];
	    		  }
	    	}

	    	if(count($deleteLeads) > 0) {
	    		$sql = '';
	    		foreach($deleteLeads as $u) {
	                // make sure we don't delete the assigned user
	                if ( $u != $focus->assigned_user_id )
	    		        $sql .= ",'" . $u . "'";
	    		}
	    		$sql = substr($sql, 1);
	    		// We could run a delete SQL statement here, but will just mark as deleted instead
	    		$sql = "UPDATE calls_leads set deleted = 1 where lead_id in ($sql) AND call_id = '". $focus->id . "'";
	    		$focus->db->query($sql);
	    	}
	    	//END SUGARCRM flav!=sales ONLY
	    	////	END REMOVE
	    	///////////////////////////////////////////////////////////////////////////


	    	///////////////////////////////////////////////////////////////////////////
	    	////	REBUILD INVITEE RELATIONSHIPS
	    	$focus->users_arr = array();
	    	$focus->users_arr = $userInvitees;
	    	$focus->contacts_arr = array();
	    	$focus->contacts_arr = $contactInvitees;
	    	//BEGIN SUGARCRM flav!=sales ONLY
	        $focus->leads_arr = array();
	    	$focus->leads_arr = $leadInvitees;
	        //END SUGARCRM flav!=sales ONLY
	    	if(!empty($_POST['parent_id']) && $_POST['parent_type'] == 'Contacts') {
	    		$focus->contacts_arr[] = $_POST['parent_id'];
	    	}
	    	//BEGIN SUGARCRM flav!=sales ONLY
	        if(!empty($_POST['parent_id']) && $_POST['parent_type'] == 'Leads') {
	    		$focus->leads_arr[] = $_POST['parent_id'];
	    	}
	    	//END SUGARCRM flav!=sales ONLY
	    	// Call the Call module's save function to handle saving other fields besides
	    	// the users and contacts relationships
	    	$focus->save(true);
	    	$return_id = $focus->id;

	    	// Process users
	    	$existing_users = array();
	    	if(!empty($_POST['existing_invitees'])) {
	    	   $existing_users =  explode(",", trim($_POST['existing_invitees'], ','));
	    	}

	    	foreach($focus->users_arr as $user_id) {
	    	    if(empty($user_id) || isset($existing_users[$user_id]) || isset($deleteUsers[$user_id])) {
	    			continue;
	    		}

	    		if(!isset($acceptStatusUsers[$user_id])) {
	    			$focus->load_relationship('users');
	    			$focus->users->add($user_id);
	    		} else {
	    			// update query to preserve accept_status
	    			$qU  = 'UPDATE calls_users SET deleted = 0, accept_status = \''.$acceptStatusUsers[$user_id].'\' ';
	    			$qU .= 'WHERE call_id = \''.$focus->id.'\' ';
	    			$qU .= 'AND user_id = \''.$user_id.'\'';
	    			$focus->db->query($qU);
	    		}
	    	}

	        // Process contacts
	    	$existing_contacts =  array();
	    	if(!empty($_POST['existing_contact_invitees'])) {
	    	   $existing_contacts =  explode(",", trim($_POST['existing_contact_invitees'], ','));
	    	}

	    	foreach($focus->contacts_arr as $contact_id) {
	    		if(empty($contact_id) || isset($existing_contacts[$contact_id]) || (isset($deleteContacts[$contact_id]) && $contact_id !=  $_POST['parent_id'])) {
	    			continue;
	    		}

	    		if(!isset($acceptStatusContacts[$contact_id])) {
	    			$focus->load_relationship('contacts');
	    		    $focus->contacts->add($contact_id);
	    		} else {
	    			// update query to preserve accept_status
	    			$qU  = 'UPDATE calls_contacts SET deleted = 0, accept_status = \''.$acceptStatusContacts[$contact_id].'\' ';
	    			$qU .= 'WHERE call_id = \''.$focus->id.'\' ';
	    			$qU .= 'AND contact_id = \''.$contact_id.'\'';
	    			$focus->db->query($qU);
	    		}
	    	}
	        //BEGIN SUGARCRM flav!=sales ONLY
	        // Process leads
	    	$existing_leads =  array();
	    	if(!empty($_POST['existing_lead_invitees'])) {
	    	   $existing_leads =  explode(",", trim($_POST['existing_lead_invitees'], ','));
	    	}

	    	foreach($focus->leads_arr as $lead_id) {
	    		if(empty($lead_id) || isset($existing_leads[$lead_id]) || (isset($deleteLeads[$lead_id]) && $lead_id !=  $_POST['parent_id'])) {
	    			continue;
	    		}

	    		if(!isset($acceptStatusLeads[$lead_id])) {
	    			$focus->load_relationship('leads');
	    		    $focus->leads->add($lead_id);
	    		} else {
	    			// update query to preserve accept_status
	    			$qU  = 'UPDATE calls_leads SET deleted = 0, accept_status = \''.$acceptStatusLeads[$lead_id].'\' ';
	    			$qU .= 'WHERE call_id = \''.$focus->id.'\' ';
	    			$qU .= 'AND lead_id = \''.$lead_id.'\'';
	    			$focus->db->query($qU);
	    		}
	    	}
	    	//END SUGARCRM flav!=sales ONLY

	    	// CCL - Comment out call to set $current_user as invitee
	    	//set organizer to auto-accept
	    	//$focus->set_accept_status($current_user, 'accept');

	    	////	END REBUILD INVITEE RELATIONSHIPS
	    	///////////////////////////////////////////////////////////////////////////
	    }
    }
	if (isset($_REQUEST['return_module']) && $_REQUEST['return_module'] == 'Home'){
		$_REQUEST['return_action'] = 'index';
        handleRedirect('', 'Home');
	}
	else if($redirect) {
		handleRedirect($return_id, 'Calls');
	} else {
		return $focus;
	}

} // end handleSave();

function getWideFormBody ($prefix, $mod='', $formname='', $wide =true){
	if(!ACLController::checkAccess('Calls', 'edit', true)){
		return '';
	}
global $mod_strings;
$temp_strings = $mod_strings;
if(!empty($mod)){
	global $current_language;
	$mod_strings = return_module_language($current_language, $mod);
}
global $app_strings;
global $app_list_strings;
global $current_user;
global $theme;

$lbl_subject = $mod_strings['LBL_SUBJECT'];
// Unimplemented until jscalendar language files are fixed
// global $current_language;
// global $default_language;
// global $cal_codes;
// Unimplemented until jscalendar language files are fixed
// $cal_lang = (empty($cal_codes[$current_language])) ? $cal_codes[$default_language] : $cal_codes[$current_language];
$cal_lang = "en";


$lbl_required_symbol = $app_strings['LBL_REQUIRED_SYMBOL'];
	$lbl_date = $mod_strings['LBL_DATE'];
$lbl_time = $mod_strings['LBL_TIME'];
global $timedate;
$ntc_date_format = '('.$timedate->get_user_date_format(). ')';
$ntc_time_format = '('.$timedate->get_user_time_format(). ')';
$cal_dateformat = $timedate->get_cal_date_format();

	$user_id = $current_user->id;
$default_status = $app_list_strings['call_status_default'];
$default_parent_type= $app_list_strings['record_type_default_key'];
$date = TimeDate::getInstance()->nowDb();
$default_date_start = $timedate->to_display_date($date);
$default_time_start = $timedate->to_display_time($date,true);
$time_ampm = $timedate->AMPMMenu($prefix,$default_time_start);
	$form =	<<<EOQ
			<input type="hidden"  name="${prefix}direction" value="Outbound">
			<input type="hidden" name="${prefix}record" value="">
			<input type="hidden" name="${prefix}status" value="${default_status}">
			<input type="hidden" name="${prefix}parent_type" value="${default_parent_type}">
			<input type="hidden" name="${prefix}assigned_user_id" value='${user_id}'>
			<input type="hidden" name="${prefix}duration_hours" value="1">
			<input type="hidden" name="${prefix}duration_minutes" value="0">
			<input type="hidden" name="${prefix}user_id" value="${user_id}">

		<table cellspacing='0' cellpadding='0' border='0' width="100%">
<tr>
EOQ;

if($wide){
$form .= <<<EOQ
<td scope='row' width="20%"><input type='radio' name='appointment' value='Call' class='radio' checked> ${mod_strings['LNK_NEW_CALL']}</td>
<td scope='row' width="80%">${mod_strings['LBL_DESCRIPTION']}</td>
</tr>

<tr>
<td scope='row'><input type='radio' name='appointment' value='Meeting' class='radio'> ${mod_strings['LNK_NEW_MEETING']}</td>

<td rowspan='8' ><textarea name='Appointmentsdescription' cols='50' rows='5'></textarea></td>
</tr>
EOQ;
}else{
		$form .= <<<EOQ
<td scope='row' width="20%"><input type='radio' name='appointment' value='Call' class='radio' onchange='document.$formname.module.value="Calls";' checked> ${mod_strings['LNK_NEW_CALL']}</td>
</tr>

<tr>
<td scope='row'><input type='radio' name='appointment' value='Meeting' class='radio' onchange='document.$formname.module.value="Meetings";'> ${mod_strings['LNK_NEW_MEETING']}</td>
</tr>
EOQ;
}
$jscalenderImage = SugarThemeRegistry::current()->getImageURL('jscalendar.gif');
$form .=	<<<EOQ


<tr>
<td scope='row'>$lbl_subject&nbsp;<span class="required">$lbl_required_symbol</span></td>
</tr>

<tr>
<td ><input name='${prefix}name' maxlength='255' type="text"></td>
</tr>

<tr>
<td scope='row'>$lbl_date&nbsp;<span class="required">$lbl_required_symbol</span>&nbsp;<span class="dateFormat">$ntc_date_format</span></td>
</tr>
<tr>
<td ><input onblur="parseDate(this, '$cal_dateformat');" name='${prefix}date_start' size="12" id='${prefix}jscal_field' maxlength='10' type="text" value="${default_date_start}"> <img src="{$jscalenderImage}" alt="{$app_strings['LBL_ENTER_DATE']}"  id="${prefix}jscal_trigger" align="absmiddle"></td>
</tr>

<tr>
<td scope='row'>$lbl_time&nbsp;<span class="required">$lbl_required_symbol</span>&nbsp;<span class="dateFormat">$ntc_time_format</span></td>
</tr>
<tr>
<td ><input name='${prefix}time_start' size="12" type="text" maxlength='5' value="{$default_time_start}">$time_ampm</td>
</tr>

</table>

		<script type="text/javascript">
		Calendar.setup ({
			inputField : "${prefix}jscal_field", daFormat : "$cal_dateformat", ifFormat : "$cal_dateformat", showsTime : false, button : "${prefix}jscal_trigger", singleClick : true, step : 1, weekNumbers:false
		});
		</script>
EOQ;


$javascript = new javascript();
$javascript->setFormName($formname);
$javascript->setSugarBean(new Call());
$javascript->addRequiredFields($prefix);
$form .=$javascript->getScript();
$mod_strings = $temp_strings;
return $form;

}



}
?>
