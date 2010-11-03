<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************

 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
// to prevent StoreQuery() from hijacking all values.
$_REQUEST = $_REQUEST;
/* default to Inbox view: this does belong in the OUTPUT section, but we need these set for StoreQuery */
if(!empty($_REQUEST['action']) && $_REQUEST['action'] == 'index') {
	global $current_user;
	$_REQUEST['type'] = 'inbound';
	$_REQUEST['assigned_user_id'] = $current_user->id;
}
///////////////////////////////////////////////////////////////////////////////
////	NAV FROM MASS UPDATE
if(isset($_REQUEST['ie_assigned_user_id']) && !empty($_REQUEST['ie_assigned_user_id'])) {
	// cn: bug 9103 - hooked from MassUpdate->getMassUpdateFormHeader();
	$_REQUEST['assigned_user_id'] = $_REQUEST['ie_assigned_user_id'];
	$_REQUEST['status'] = ''; // "Archiving" MyInbox emails presets the search criteria below
}
////	END NAV FROM MASS UPDATE
///////////////////////////////////////////////////////////////////////////////








require_once('modules/MySettings/StoreQuery.php');
global $app_strings;
global $app_list_strings;
global $mod_strings;
global $urlPrefix;
global $currentModule;

global $theme;
global $focus_list; // focus_list is the means of passing data to a ListView.

$focus				= new Email();
$header_text		= '';
$where				= '';
$type				= '';
$assigned_user_id	= '';
$group				= '';
$search_adv			= '';
$whereClauses		= array();
$error				= '';

///////////////////////////////////////////////////////////////////////////////
////
////	SEARCH FORM FUNCTIONALITY
////	SEARCH QUERY GENERATION
$storeQuery = new StoreQuery();

// this allows My Inbox, Group Inbox, etc. to have separate stored queries
// for the same ListView.php
if(isset($_REQUEST['type'])) $Qtype = $_REQUEST['type'];
else $Qtype = '';
if(isset($_REQUEST['assigned_user_id']) && $_REQUEST['assigned_user_id'] == $current_user->id) {
	$Qassigned_user_id = $_REQUEST['assigned_user_id'];
} else {
	$Qassigned_user_id = '';
}

if(!isset($_REQUEST['query'])){
	//_pp('loading: '.$currentModule.$Qtype.$Qgroup);
	//_pp($current_user->user_preferences[$currentModule.$Qtype.'Q']);
	$storeQuery->loadQuery($currentModule.$Qtype);
	$storeQuery->populateRequest();
} else {
	//_pp($current_user->user_preferences[$currentModule.$Qtype.'Q']);
	//_pp('saving: '.$currentModule.$Qtype);
	$storeQuery->saveFromGet($currentModule.$Qtype);
}


if(isset($_REQUEST['query'])) {
	// we have a query
	if (isset($_REQUEST['email_type']))		$email_type = $_REQUEST['email_type'];
	if (isset($_REQUEST['assigned_to']))	$assigned_to = $_REQUEST['assigned_to'];
	if (isset($_REQUEST['status']))			$status = $_REQUEST['status'];
	if (isset($_REQUEST['name']))			$name = $_REQUEST['name'];
	if (isset($_REQUEST['contact_name']))	$contact_name = $_REQUEST['contact_name'];
	
	if(isset($email_type) && $email_type != "")		$whereClauses['emails.type'] = "emails.type = '".$GLOBALS['db']->quote($email_type)."'";
	if(isset($assigned_to) && $assigned_to != "")	$whereClauses['emails.assigned_user_id'] = "emails.assigned_user_id = '".$GLOBALS['db']->quote($assigned_to)."'";
	if(isset($status) && $status != "")				$whereClauses['emails.status'] = "emails.status = '".$GLOBALS['db']->quote($status)."'";
	if(isset($name) && $name != "")					$whereClauses['emails.name'] = "emails.name like '".$GLOBALS['db']->quote($name)."%'";
	if(isset($contact_name) && $contact_name != '') {
		$contact_names = explode(" ", $contact_name);
		foreach ($contact_names as $name) {
			$whereClauses['contacts.name'] = "(contacts.first_name like '".$GLOBALS['db']->quote($name)."%' OR contacts.last_name like '".$GLOBALS['db']->quote($name)."%')";
		}
	}

	$focus->custom_fields->setWhereClauses($whereClauses);
	$GLOBALS['log']->info("Here is the where clause for the list view: $where");
} // end isset($_REQUEST['query'])



////	OUTPUT GENERATION

if (!isset($_REQUEST['search_form']) || $_REQUEST['search_form'] != 'false') {
	// ASSIGNMENTS pre-processing
	$email_type_sel = '';
	$assigned_to_sel = '';
	$status_sel = '';
	if(isset($_REQUEST['email_type']))		$email_type_sel = $_REQUEST['email_type'];
	if(isset($_REQUEST['assigned_to']))		$assigned_to_sel = $_REQUEST['assigned_to'];
	if(isset($_REQUEST['status']))			$status_sel = $_REQUEST['status'];
	if(isset($_REQUEST['search']))			$search_adv = $_REQUEST['search'];

	// drop-downs values
	$r = $focus->db->query("SELECT id, user_name FROM users WHERE deleted = 0 AND status = 'Active' OR users.is_group = 1 ORDER BY status");
	$users[] = '';
	while($a = $focus->db->fetchByAssoc($r)) {
		$users[$a['id']] = $a['user_name'];
	}
	
	$email_types[] = '';
	$email_types = array_merge($email_types, $app_list_strings['dom_email_types']);
	$email_status[] = '';
	$email_status = array_merge($email_status, $app_list_strings['dom_email_status']);
	$types			= get_select_options_with_id($email_types, $email_type_sel);
	$assigned_to	= get_select_options_with_id($users, $assigned_to_sel);
	$email_status	= get_select_options_with_id($email_status, $status_sel);
	
	// ASSIGNMENTS AND OUTPUT
	if(isset($_REQUEST['type']) && $_REQUEST['type'] != '') $emailType = $_REQUEST['type'];
	else $emailType = '';
	switch($emailType) {
		case 'out':
			$search_form = new XTemplate ('modules/Emails/SearchFormSent.html');
		break;

		case 'draft':
			$search_form = new XTemplate ('modules/Emails/SearchFormSent.html');
		break;

		case 'archived':
		case 'inbound':
			if ($emailType == "archived") {
				$email_status = array();
				$email_status[] = '';
				$email_status = array_merge($email_status, $app_list_strings['dom_email_archived_status']);
				$email_status = get_select_options_with_id($email_status, $status_sel);
			}
			$search_form = new XTemplate ('modules/Emails/SearchFormMyInbox.html');
		break;
		
		default:
			$search_form = new XTemplate ('modules/Emails/SearchFormMyInbox.html');
		break;
	}
	
	$search_form->assign('MOD', $mod_strings);
	$search_form->assign('APP', $app_strings);
	$search_form->assign('ADVANCED_SEARCH_PNG', SugarThemeRegistry::current()->getImage('advanced_search','alt="'.$app_strings['LNK_ADVANCED_SEARCH'].'"  border="0"'));
	$search_form->assign('BASIC_SEARCH_PNG', SugarThemeRegistry::current()->getImage('basic_search','alt="'.$app_strings['LNK_BASIC_SEARCH'].'"  border="0"'));
	$search_form->assign('TYPE_OPTIONS', $types);
	$search_form->assign('ASSIGNED_TO_OPTIONS', $assigned_to);
	$search_form->assign('STATUS_OPTIONS', $email_status);
	$search_form->assign('ADV_URL', $_SERVER['REQUEST_URI']);
	$search_form->assign('SEARCH_ADV', $search_adv);
	

	if(isset($_REQUEST['name']))			$search_form->assign('NAME', $_REQUEST['name']);
	if(isset($_REQUEST['contact_name']))	$search_form->assign('CONTACT_NAME', $_REQUEST['contact_name']);
	if(isset($current_user_only))			$search_form->assign('CURRENT_USER_ONLY', "checked");

	// adding custom fields:
	$focus->custom_fields->populateXTPL($search_form, 'search' );
	$search_form->assign('SEARCH_ACTION', 'ListView');
	$search_form->assign('TYPE', $Qtype);
	if(!empty($_REQUEST['assigned_user_id'])) {
		$search_form->assign('ASSIGNED_USER_ID', $_REQUEST['assigned_user_id']);
	}
	$search_form->assign('JAVASCRIPT', $focus->js_set_archived().$focus->u_get_clear_form_js($Qtype, '', $Qassigned_user_id));
}
////	END SEARCH FORM FUNCTIONALITY
////	
///////////////////////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////////////////////////
////	NAVIGATION HACK
$_SESSION['emailStartAction'] = ''; // empty this value to allow new writes
////	END NAVIGATION HACK
///////////////////////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////////////////////////
////
////	INBOX FUNCTIONALITY
// for Inbox

if(isset($_REQUEST['type']) && !empty($_REQUEST['type'])) {
	$type = $_REQUEST['type'];
	switch ($_REQUEST['type']) {
		case 'archived':
			// disabling since it would break the "INBOUND" thing
			$search_form->assign('TYPE_DISABLED', 'DISABLED');
			
			if( (isset($_REQUEST['assigned_user_id']) && $_REQUEST['assigned_user_id'] != '') ) {
				// would break the user's email thing
				$search_form->assign('ASSIGN_TO_DISABLED', 'DISABLED');
				
				$assigned_user_id = $_REQUEST['assigned_user_id'];

				$whereClauses['emails.assigned_user_id'] = "emails.assigned_user_id = '{$_REQUEST['assigned_user_id']}'";
				$whereClauses['emails.status'] = "emails.status = 'archived'";
				$display_title = $mod_strings['LBL_LIST_TITLE_MY_ARCHIVES'];
			}
		
		break;
		
		case 'inbound':
			// disabling since it would break the "INBOUND" thing
			$search_form->assign('TYPE_DISABLED', 'DISABLED');
			
			if( (isset($_REQUEST['assigned_user_id']) && $_REQUEST['assigned_user_id'] != '') ) {
				// would break the user's email thing
				$search_form->assign('ASSIGN_TO_DISABLED', 'DISABLED');
				
				$assigned_user_id = $_REQUEST['assigned_user_id'];

				$whereClauses['emails.assigned_user_id'] = "emails.assigned_user_id = '{$_REQUEST['assigned_user_id']}'";
				$whereClauses['emails.type'] = "emails.type = '{$_REQUEST['type']}'";
				$whereClauses['emails.status'] = "emails.status != 'archived'";
				if(!empty($_REQUEST['status'])) {
					$whereClauses['emails.status'] = "emails.status != 'archived' AND emails.status = '{$_REQUEST['status']}'";
				}
				
				$display_title = $mod_strings['LBL_LIST_TITLE_MY_INBOX'];
			}
		break;
		
		case 'draft':
			// disabling since it would break the "DRAFT" thing
			$search_form->assign('TYPE_DISABLED', 'DISABLED');

			if( (isset($_REQUEST['assigned_user_id']) && $_REQUEST['assigned_user_id'] != '') ) {
				// would break the user's email thing
				$search_form->assign('ASSIGN_TO_DISABLED', 'DISABLED');

				$whereClauses['emails.assigned_user_id'] = 'emails.assigned_user_id = \''.$_REQUEST['assigned_user_id'].'\' ';
				
				$display_title = $mod_strings['LBL_LIST_TITLE_MY_DRAFTS'];
			} else {
				$display_title = $mod_strings['LNK_DRAFTS_EMAIL_LIST'];
			}

			$whereClauses['emails.type'] = 'emails.type = \''.$_REQUEST['type'].'\'';
			// ListForm title:
			
		break;
		
		case 'out':
			// disabling since it would break the "SENT" thing
			$search_form->assign('TYPE_DISABLED', 'DISABLED');

			if( (isset($_REQUEST['assigned_user_id']) && $_REQUEST['assigned_user_id'] != '') ) {
				// would break the user's email thing
				$search_form->assign('ASSIGN_TO_DISABLED', 'DISABLED');
				$whereClauses['emails.assigned_user_id'] = 'emails.assigned_user_id = \''.$_REQUEST['assigned_user_id'].'\' ';
			}

			$whereClauses['emails.type'] = ' emails.type=\'out\'';
			
			$display_title = $mod_strings['LBL_LIST_TITLE_MY_SENT'];
		break;
		
		default:
		break;	
	}
} else {
	// STANDARD EMAIL BOX FUNCTIONS
	global $email_title;
	$display_title = $mod_strings['LBL_LIST_FORM_TITLE'];
	if($email_title)$display_title = $email_title;
}
////	END INBOX FUNCTIONALITY
////
///////////////////////////////////////////////////////////////////////////////

$ListView = new ListView();
switch($emailType) {
	case 'inbound':
		$ListView->initNewXTemplate('modules/Emails/ListViewMyInbox.html',$mod_strings);
	break;
	
	default:
		$ListView->initNewXTemplate('modules/Emails/ListView.html',$mod_strings);
	break;
}


///////////////////////////////////////////////////////////////////////////////
////	OUTPUT
///////////////////////////////////////////////////////////////////////////////

// make sure $display_title is set prior to display
if (!isset($display_title)) {
	$display_title = '';
}

echo get_module_title("Emails", $mod_strings['LBL_MODULE_TITLE'].$display_title, true); 
// admin-edit
if(is_admin($current_user) && $_REQUEST['module'] != 'DynamicLayout' && !empty($_SESSION['editinplace'])){	
	$header_text = "&nbsp;<a href='index.php?action=index&module=DynamicLayout&from_action=SearchForm&from_module=".$_REQUEST['module'] ."'>".SugarThemeRegistry::current()->getImage("EditLayout","border='0' alt='Edit Layout' align='bottom'")."</a>";
}
// search form
echo get_form_header($mod_strings['LBL_SEARCH_FORM_TITLE']. $header_text, "", false);
// ADVANCED SEARCH
if(isset($_REQUEST['search']) && $_REQUEST['search'] == 'advanced') {
	$search_form->parse('adv');
	$search_form->out('adv');

} else {
	$search_form->parse('main');
	$search_form->out('main');
}

// CONSTRUCT WHERE STRING FROM WHERECLAUSE ARRAY
foreach($whereClauses as $clause) {
	if($where != "")
	$where .= " AND ";
	$where .= $clause;
}

//echo $where;

if( (isset($_REQUEST['assigned_user_id']) && $_REQUEST['assigned_user_id'] != '') && $_REQUEST['type'] == 'inbound') { 
	$ListView->xTemplateAssign('TAKE',$focus->pickOneButton());
	if($current_user->hasPersonalEmail()) {
		$ListView->xTemplateAssign('CHECK_MAIL',$focus->checkInbox('personal'));
	}
}
if( (isset($_REQUEST['show_error']) && $_REQUEST['show_error'] == 'true') && $_REQUEST['type'] == 'inbound') {
	$ListView->xTemplateAssign('TAKE_ERROR', $focus->takeError());
}
//echo $focus->quickCreateJS();
$ListView->setAdditionalDetails();
$ListView->xTemplateAssign('ATTACHMENT_HEADER', SugarThemeRegistry::current()->getImage('attachment',"","",""));
$ListView->xTemplateAssign('ERROR', $error);
$ListView->setHeaderTitle($display_title . $header_text );
$ListView->setQuery($where, "", "emails.date_sent, emails.date_entered DESC", "EMAIL");
$ListView->processListView($focus, "main", "EMAIL");

?>
