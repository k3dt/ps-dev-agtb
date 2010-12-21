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
 * $Id: User.php 56851 2010-06-07 22:17:02Z jenny $
 * Description: TODO:  To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/SugarObjects/templates/person/Person.php');

//BEGIN SUGARCRM flav=dce ONLY

//END SUGARCRM flav=dce ONLY

// User is used to store customer information.
class User extends Person {
	// Stored fields
	var $name = '';
	var $full_name;
	var $id;
	var $user_name;
	var $user_hash;
	//BEGIN SUGARCRM flav=sales ONLY
	var $user_type;
	//END SUGARCRM flav=sales ONLY
	var $salutation;
	var $first_name;
	var $last_name;
	var $date_entered;
	var $date_modified;
	var $modified_user_id;
	var $created_by;
	var $created_by_name;
	var $modified_by_name;
	var $description;
	var $phone_home;
	var $phone_mobile;
	var $phone_work;
	var $phone_other;
	var $phone_fax;
	var $email1;
	var $email2;
	var $address_street;
	var $address_city;
	var $address_state;
	var $address_postalcode;
	var $address_country;
	var $status;
	var $title;
	var $portal_only;
	var $department;
	var $authenticated = false;
	var $error_string;
	var $is_admin;
	var $employee_status;
	var $messenger_id;
	var $messenger_type;
	var $is_group;
	var $accept_status; // to support Meetings
	//adding a property called team_id so we can populate it for use in the team widget
	var $team_id;

	var $receive_notifications;
	//BEGIN SUGARCRM flav=pro ONLY
	var $default_team;
	//END SUGARCRM flav=pro ONLY

	var $reports_to_name;
	var $reports_to_id;
	var $team_exists = false;
	var $table_name = "users";
	var $module_dir = 'Users';
	var $object_name = "User";
	var $user_preferences;

	var $importable = true;
	var $_userPreferenceFocus;

	var $encodeFields = Array ("first_name", "last_name", "description");

	// This is used to retrieve related fields from form posts.
	var $additional_column_fields = array ('reports_to_name'
//BEGIN SUGARCRM flav=dce ONLY
,'instance_id'
//END SUGARCRM flav=dce ONLY
	);

	var $emailAddress;


	var $new_schema = true;
//BEGIN SUGARCRM flav=dce ONLY
    var $relationship_fields = Array('instance_id'=>'dceinstances');
	var $dceinstance_role;
    var $dceinstance_rel_id;
    var $instance_id;
    var $dceinstance_role_id;
    var $rel_dceinstance_table = "dceinstances_users";
//END SUGARCRM flav=dce ONLY

	function User() {
		parent::Person();
		//BEGIN SUGARCRM flav=pro ONLY
		$this->disable_row_level_security = true;
		//END SUGARCRM flav=pro ONLY

		$this->_loadUserPreferencesFocus();
	}

	protected function _loadUserPreferencesFocus()
	{
	    $this->_userPreferenceFocus = new UserPreference($this);
	}

    /**
     * returns an admin user
     */
    public function getSystemUser()
    {
        if (null === $this->retrieve('1'))
            // handle cases where someone deleted user with id "1"
            $this->retrieve_by_string_fields(array(
                'status' => 'Active',
                'is_admin' => '1',
                ));

        return $this;
    }


	/**
	 * convenience function to get user's default signature
	 */
	function getDefaultSignature() {
		if($defaultId = $this->getPreference('signature_default')) {
			return $this->getSignature($defaultId);
		} else {
			return array();
		}
	}

	/**
	 * retrieves the signatures for a user
	 * @param string id ID of user_signature
	 * @return array ID, signature, and signature_html
	 */
	public function getSignature($id)
	{
	    $signatures = $this->getSignaturesArray();

	    return $signatures[$id];
	}

	function getSignaturesArray() {
		$q = 'SELECT * FROM users_signatures WHERE user_id = \''.$this->id.'\' AND deleted = 0 ORDER BY name ASC';
		$r = $this->db->query($q);

		// provide "none"
		$sig = array(""=>"");

		while($a = $this->db->fetchByAssoc($r)) {
			$sig[$a['id']] = $a;
		}

		return $sig;
	}

	/**
	 * retrieves any signatures that the User may have created as <select>
	 */
	public function getSignatures(
	    $live = false,
	    $defaultSig = '',
	    $forSettings = false
	    )
	{
		$sig = $this->getSignaturesArray();
		$sigs = array();
		foreach ($sig as $key => $arr)
		{
			$sigs[$key] = !empty($arr['name']) ? $arr['name'] : '';
		}

		$change = '';
		if(!$live) {
			$change = ($forSettings) ? "onChange='displaySignatureEdit();'" : "onChange='setSigEditButtonVisibility();'";
		}

		$id = (!$forSettings) ? 'signature_id' : 'signature_idDisplay';

		$out  = "<select {$change} id='{$id}' name='{$id}'>";
		$out .= get_select_options_with_id($sigs, $defaultSig).'</select>';

		return $out;
	}

	/**
	 * returns buttons and JS for signatures
	 */
	function getSignatureButtons($jscall='', $defaultDisplay=false) {
		global $mod_strings;

		$jscall = empty($jscall) ? 'open_email_signature_form' : $jscall;

		$butts  = "<input class='button' onclick='javascript:{$jscall}(\"\", \"{$this->id}\");' value='{$mod_strings['LBL_BUTTON_CREATE']}' type='button'>&nbsp;";
		if($defaultDisplay) {
			$butts .= '<span name="edit_sig" id="edit_sig" style="visibility:inherit;"><input class="button" onclick="javascript:'.$jscall.'(document.getElementById(\'signature_id\', \'\').value)" value="'.$mod_strings['LBL_BUTTON_EDIT'].'" type="button" tabindex="392">&nbsp;
					</span>';
		} else {
			$butts .= '<span name="edit_sig" id="edit_sig" style="visibility:hidden;"><input class="button" onclick="javascript:'.$jscall.'(document.getElementById(\'signature_id\', \'\').value)" value="'.$mod_strings['LBL_BUTTON_EDIT'].'" type="button" tabindex="392">&nbsp;
					</span>';
		}
		return $butts;
	}

	/**
	 * performs a rudimentary check to verify if a given user has setup personal
	 * InboundEmail
	 *
	 * @return bool
	 */
	public function hasPersonalEmail()
	{
	    $focus = new InboundEmail;
	    $focus->retrieve_by_string_fields(array('group_id' => $this->id));

	    return !empty($focus->id);
	}

	/* Returns the User's private GUID; this is unassociated with the User's
	 * actual GUID.  It is used to secure file names that must be HTTP://
	 * accesible, but obfusicated.
	 */
	function getUserPrivGuid() {
        $userPrivGuid = $this->getPreference('userPrivGuid', 'global', $this);
		if ($userPrivGuid) {
			return $userPrivGuid;
		} else {
			$this->setUserPrivGuid();
			if (!isset ($_SESSION['setPrivGuid'])) {
				$_SESSION['setPrivGuid'] = true;
				$userPrivGuid = $this->getUserPrivGuid();
				return $userPrivGuid;
			} else {
				sugar_die("Breaking Infinite Loop Condition: Could not setUserPrivGuid.");
			}
		}
	}

	function setUserPrivGuid() {
		$privGuid = create_guid();
		//($name, $value, $nosession=0)
		$this->setPreference('userPrivGuid', $privGuid, 0, 'global', $this);
	}

	/**
	 * Interface for the User object to calling the UserPreference::setPreference() method in modules/UserPreferences/UserPreference.php
	 *
	 * @see UserPreference::setPreference()
	 *
	 * @param string $name Name of the preference to set
	 * @param string $value Value to set preference to
	 * @param null $nosession For BC, ignored
	 * @param string $category Name of the category to retrieve
	 */
	public function setPreference(
	    $name,
	    $value,
	    $nosession = 0,
	    $category = 'global'
	    )
	{
	    // for BC
	    if ( func_num_args() > 4 ) {
	        $user = func_get_arg(4);
	        $GLOBALS['log']->deprecated('User::setPreferences() should not be used statically.');
	    }
	    else
	        $user = $this;

        $user->_userPreferenceFocus->setPreference($name, $value, $category);
	}

	/**
	 * Interface for the User object to calling the UserPreference::resetPreferences() method in modules/UserPreferences/UserPreference.php
	 *
	 * @see UserPreference::resetPreferences()
	 *
	 * @param string $category category to reset
	 */
	public function resetPreferences(
	    $category = null
	    )
	{
	    // for BC
	    if ( func_num_args() > 1 ) {
	        $user = func_get_arg(1);
	        $GLOBALS['log']->deprecated('User::resetPreferences() should not be used statically.');
	    }
	    else
	        $user = $this;

        $user->_userPreferenceFocus->resetPreferences($category);
	}


	/**
	 * Interface for the User object to calling the UserPreference::isPreferenceSizeTooLarge() method in modules/UserPreferences/UserPreference.php
	 *
	 * @see UserPreference::isPreferenceSizeTooLarge()
	 *
	 * @param string $category category to check
	 */
	public function isPreferenceSizeTooLarge(
	    $category = 'global'
	    )
	{
	    // for BC
	    if ( func_num_args() > 1 ) {
	        $user = func_get_arg(1);
	        $GLOBALS['log']->deprecated('User::resetPreferences() should not be used statically.');
	    }
	    else
	        $user = $this;

        return $user->_userPreferenceFocus->isPreferenceSizeTooLarge($category);
	}
	
	
	/**
	 * Interface for the User object to calling the UserPreference::savePreferencesToDB() method in modules/UserPreferences/UserPreference.php
	 *
	 * @see UserPreference::savePreferencesToDB()
	 */
	public function savePreferencesToDB()
	{
        // for BC
	    if ( func_num_args() > 0 ) {
	        $user = func_get_arg(0);
	        $GLOBALS['log']->deprecated('User::savePreferencesToDB() should not be used statically.');
	    }
	    else
	        $user = $this;

        $user->_userPreferenceFocus->savePreferencesToDB();
	}

	/**
	 * Unconditionally reloads user preferences from the DB and updates the session
	 * @param string $category name of the category to retreive, defaults to global scope
	 * @return bool successful?
	 */
	public function reloadPreferences($category = 'global')
	{
	    return $this->_userPreferenceFocus->reloadPreferences($category = 'global');
	}

	/**
	 * Interface for the User object to calling the UserPreference::getUserDateTimePreferences() method in modules/UserPreferences/UserPreference.php
	 *
	 * @see UserPreference::getUserDateTimePreferences()
	 *
	 * @return array 'date' - date format for user ; 'time' - time format for user
	 */
	public function getUserDateTimePreferences()
	{
        // for BC
	    if ( func_num_args() > 0 ) {
	        $user = func_get_arg(0);
	        $GLOBALS['log']->deprecated('User::getUserDateTimePreferences() should not be used statically.');
	    }
	    else
	        $user = $this;

        return $user->_userPreferenceFocus->getUserDateTimePreferences();
	}

	/**
	 * Interface for the User object to calling the UserPreference::loadPreferences() method in modules/UserPreferences/UserPreference.php
	 *
	 * @see UserPreference::loadPreferences()
	 *
	 * @param string $category name of the category to retreive, defaults to global scope
	 * @return bool successful?
	 */
	public function loadPreferences(
	    $category = 'global'
	    )
	{
	    // for BC
	    if ( func_num_args() > 1 ) {
	        $user = func_get_arg(1);
	        $GLOBALS['log']->deprecated('User::loadPreferences() should not be used statically.');
	    }
	    else
	        $user = $this;

        return $user->_userPreferenceFocus->loadPreferences($category);
	}

	/**
	 * Interface for the User object to calling the UserPreference::setPreference() method in modules/UserPreferences/UserPreference.php
	 *
	 * @see UserPreference::getPreference()
	 *
	 * @param string $name name of the preference to retreive
	 * @param string $category name of the category to retreive, defaults to global scope
	 * @return mixed the value of the preference (string, array, int etc)
	 */
	public function getPreference(
	    $name,
	    $category = 'global'
	    )
	{
	    // for BC
	    if ( func_num_args() > 2 ) {
	        $user = func_get_arg(2);
	        $GLOBALS['log']->deprecated('User::getPreference() should not be used statically.');
	    }
	    else
	        $user = $this;

        return $user->_userPreferenceFocus->getPreference($name, $category);
	}

	function save($check_notify = false) {
		//BEGIN SUGARCRM flav=pro ONLY
		// this will cause the logged in admin to have the licensed user count refreshed
		if (isset($_SESSION)) unset($_SESSION['license_seats_needed']);
		//END SUGARCRM flav=pro ONLY

		//BEGIN SUGARCRM dep=od ONLY
		$query = "SELECT count(id) as total from users WHERE status='Active' AND is_group=0 AND portal_only=0 AND user_name not like 'SugarCRMSupport' AND user_name not like '%_SupportUser'";
		//END SUGARCRM dep=od ONLY
		//BEGIN SUGARCRM dep=os ONLY
		$query = "SELECT count(id) as total from users WHERE status='Active' AND deleted=0 AND is_group=0 AND portal_only=0";
		//END SUGARCRM dep=os ONLY

		  //BEGIN SUGARCRM lic=sub ONLY

		global $sugar_flavor;
        $admin = new Administration();
        $admin->retrieveSettings();
		if((isset($sugar_flavor) && $sugar_flavor != null) &&
			($sugar_flavor=='CE' || isset($admin->settings['license_enforce_user_limit']) && $admin->settings['license_enforce_user_limit'] == 1)){

	        // Begin Express License Enforcement Check
			// this will cause the logged in admin to have the licensed user count refreshed
				if( isset($_SESSION['license_seats_needed']))
			        unset($_SESSION['license_seats_needed']);
		     	if ($this->portal_only != 1 && $this->is_group != 1 && (empty($this->fetched_row) || $this->fetched_row['status'] == 'Inactive' || $this->fetched_row['status'] == '') && $this->status == 'Active'){
			        global $sugar_flavor;
					//if((isset($sugar_flavor) && $sugar_flavor != null) && ($sugar_flavor=='CE')){
			            $license_users = $admin->settings['license_users'];
			            if ($license_users != '') {
	            			global $db;
	            			//$query = "SELECT count(id) as total from users WHERE status='Active' AND deleted=0 AND is_group=0 AND portal_only=0";
							$result = $db->query($query, true, "Error filling in user array: ");
							$row = $db->fetchByAssoc($result);
				            $license_seats_needed = $row['total'] - $license_users;
			            }
				        else
				        	$license_seats_needed = -1;
				        if( $license_seats_needed >= 0 ){
				           // displayAdminError( translate('WARN_LICENSE_SEATS_MAXED', 'Administration'). ($license_seats_needed + 1) . translate('WARN_LICENSE_SEATS2', 'Administration')  );
						    if (isset($_REQUEST['action']) && $_REQUEST['action'] != 'MassUpdate' && $_REQUEST['action'] != 'Save') {
					            die(translate('WARN_LICENSE_SEATS_EDIT_USER', 'Administration'). ' ' . translate('WARN_LICENSE_SEATS2', 'Administration'));
						    }
							else if (isset($_REQUEST['action'])){ // When this is not set, we're coming from the installer.
								$sv = new SugarView();
							    $sv->init('Users');
							    $sv->renderJavascript();
							    $sv->displayHeader();
		        				//$sv->includeClassicFile('themes/' . $GLOBALS['theme'] . '/header.php');
		        				//$sv->includeClassicFile('modules/Administration/DisplayWarnings.php');
							    displayAdminError(translate('WARN_LICENSE_SEATS_EDIT_USER', 'Administration'). ' ' . translate('WARN_LICENSE_SEATS2', 'Administration'));
							    $sv->displayFooter();
							    die();
						  	}
				        }
			        //}
		     	}
			}
            // End Express License Enforcement Check

		  //END SUGARCRM lic=sub ONLY

		// wp: do not save user_preferences in this table, see user_preferences module
		$this->user_preferences = '';

		// if this is an admin user, do not allow is_group or portal_only flag to be set.
		if ($this->is_admin) {
			$this->is_group = 0;
			$this->portal_only = 0;
		}


		//BEGIN SUGARCRM flav=sales ONLY
		// set some default preferences when creating a new user
		$setNewUserPreferences = empty($this->id);
		//END SUGARCRM flav=sales ONLY

		//BEGIN SUGARCRM flav=pro ONLY

		//this code is meant to allow for the team widget to set the team_id as the 'Primary' team and
		//then b/c Users uses the default_team field we can map it back when committing the user to the database.
		if(!empty($this->team_id)){
			$this->default_team = $this->team_id;
		}else{
			$this->team_id = $this->default_team;
		}

		//END SUGARCRM flav=pro ONLY


		parent::save($check_notify);

		//BEGIN SUGARCRM flav=pro ONLY
		$GLOBALS['sugar_config']['disable_team_access_check'] = true;
		//END SUGARCRM flav=pro ONLY

		//BEGIN SUGARCRM flav=sales ONLY
		// set some default preferences when creating a new user
		if ( $setNewUserPreferences ) {
		    // set default reminder time to 30 minutes
		    $this->setPreference('reminder_time', 1800);
		    $this->setPreference('mailmerge_on', 'on');
		}
		//END SUGARCRM flav=sales ONLY

        $this->savePreferencesToDB();
        return $this->id;
	}

	/**
	* @return boolean true if the user is a member of the role_name, false otherwise
	* @param string $role_name - Must be the exact name of the acl_role
	* @param string $user_id - The user id to check for the role membership, empty string if current user
	* @desc Determine whether or not a user is a member of an ACL Role. This function caches the
	*       results in the session or to prevent running queries after the first time executed.
	* Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	* All Rights Reserved..
	* Contributor(s): ______________________________________..
	*/
	function check_role_membership($role_name, $user_id = ''){

		global $current_user;

		if(empty($user_id))
			$user_id = $current_user->id;

		// Check the Sugar External Cache to see if this users memberships were cached
		$role_array = sugar_cache_retrieve("RoleMemberships_".$user_id);

		// If we are pulling the roles for the current user
		if($user_id == $current_user->id){
			// If the Session doesn't contain the values
			if(!isset($_SESSION['role_memberships'])){
				// This means the external cache already had it loaded
				if(!empty($role_array))
					$_SESSION['role_memberships'] = $role_array;
				else{
					$_SESSION['role_memberships'] = ACLRole::getUserRoleNames($user_id);
					$role_array = $_SESSION['role_memberships'];
				}
			}
			// else the session had the values, so we assign to the role array
			else{
				$role_array = $_SESSION['role_memberships'];
			}
		}
		else{
			// If the external cache didn't contain the values, we get them and put them in cache
			if(!$role_array){
				$role_array = ACLRole::getUserRoleNames($user_id);
				sugar_cache_put("RoleMemberships_".$user_id, $role_array);
			}
		}

		// If the role doesn't exist in the list of the user's roles
		if(!empty($role_array) && in_array($role_name, $role_array))
			return true;
		else
			return false;
	}

    function get_summary_text() {
        //$this->_create_proper_name_field();
        return $this->name;
	}

	/**
	* @return string encrypted password for storage in DB and comparison against DB password.
	* @param string $user_name - Must be non null and at least 2 characters
	* @param string $user_password - Must be non null and at least 1 character.
	* @desc Take an unencrypted username and password and return the encrypted password
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
	*/
	function encrypt_password($user_password) {
		// encrypt the password.
		$salt = substr($this->user_name, 0, 2);
		$encrypted_password = crypt($user_password, $salt);

		return $encrypted_password;
	}

	/**
	 * Authenicates the user; returns true if successful
	 *
	 * @param $password
	 * @return bool
	 */
	public function authenticate_user(
	    $password
	    )
	{
		$password = $GLOBALS['db']->quote($password);
		$user_name = $GLOBALS['db']->quote($this->user_name);
		$query = "SELECT * from $this->table_name where user_name='$user_name' AND user_hash='$password' AND (portal_only IS NULL OR portal_only !='1') AND (is_group IS NULL OR is_group !='1') ";
		//$result = $this->db->requireSingleResult($query, false);
		$result = $this->db->limitQuery($query,0,1,false);
		$a = $this->db->fetchByAssoc($result);
		// set the ID in the seed user.  This can be used for retrieving the full user record later
		if (empty ($a)) {
			// already logging this in load_user() method
			//$GLOBALS['log']->fatal("SECURITY: failed login by $this->user_name");
			return false;
		} else {
			$this->id = $a['id'];
			return true;
		}
	}

    /**
     * retrieves an User bean
     * preformat name & full_name attribute with first/last
     * loads User's preferences
     *
     * @param string id ID of the User
     * @param bool encode encode the result
     * @return object User bean
     * @return null null if no User found
     */
	function retrieve($id, $encode = true) {
		$ret = parent::retrieve($id, $encode);
		if ($ret) {
			if (isset ($_SESSION)) {
				$this->loadPreferences();
			}
		}
		return $ret;
	}

	function retrieve_by_email_address($email) {

		$email1= strtoupper($email);
		$q=<<<EOQ

		select id from users where id in ( SELECT  er.bean_id AS id FROM email_addr_bean_rel er,
			email_addresses ea WHERE ea.id = er.email_address_id
		    AND ea.deleted = 0 AND er.deleted = 0 AND er.bean_module = 'Users' AND email_address_caps IN ('{$email}') )
EOQ;


		$res=$this->db->query($q);
		$row=$this->db->fetchByAssoc($res);

		if (!empty($row['id'])) {
			return $this->retrieve($row['id']);
		}
		return '';
	}

   function bean_implements($interface) {
        switch($interface){
//BEGIN SUGARCRM flav!=sales ONLY
            case 'ACL':return true;
//END SUGARCRM flav!=sales ONLY
//BEGIN SUGARCRM flav=sales ONLY
            case 'ACL':return false;
//END SUGARCRM flav=sales ONLY
        }
        return false;
    }


	/**
	 * Load a user based on the user_name in $this
	 * @return -- this if load was successul and null if load failed.
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
	 */
	function load_user($user_password) {
		global $login_error;
		unset($GLOBALS['login_error']);
		if(isset ($_SESSION['loginattempts'])) {
			$_SESSION['loginattempts'] += 1;
		} else {
			$_SESSION['loginattempts'] = 1;
		}
		if($_SESSION['loginattempts'] > 5) {
			$GLOBALS['log']->fatal('SECURITY: '.$this->user_name.' has attempted to login '.$_SESSION['loginattempts'].' times from IP address: '.$_SERVER['REMOTE_ADDR'].'.');
		}

		$GLOBALS['log']->debug("Starting user load for $this->user_name");

		if (!isset ($this->user_name) || $this->user_name == "" || !isset ($user_password) || $user_password == "")
			return null;

		$user_hash = strtolower(md5($user_password));
		if($this->authenticate_user($user_hash)) {
			$query = "SELECT * from $this->table_name where id='$this->id'";
		} else {
			$GLOBALS['log']->fatal('SECURITY: User authentication for '.$this->user_name.' failed');
			return null;
		}
		$r = $this->db->limitQuery($query, 0, 1, false);
		$a = $this->db->fetchByAssoc($r);
		if(empty($a) || !empty ($GLOBALS['login_error'])) {
			$GLOBALS['log']->fatal('SECURITY: User authentication for '.$this->user_name.' failed - could not Load User from Database');
			return null;
		}

		// Get the fields for the user
		$row = $a;

		// If there is no user_hash is not present or is out of date, then create a new one.
		if (!isset ($row['user_hash']) || $row['user_hash'] != $user_hash) {
			$query = "UPDATE $this->table_name SET user_hash='$user_hash' where id='{$row['id']}'";
			$this->db->query($query, true, "Error setting new hash for {$row['user_name']}: ");
		}

		// now fill in the fields.
		foreach ($this->column_fields as $field) {
			$GLOBALS['log']->info($field);

			if (isset ($row[$field])) {
				$GLOBALS['log']->info("=".$row[$field]);

				$this-> $field = $row[$field];
			}
		}

		$this->loadPreferences();


		require_once ('modules/Versions/CheckVersions.php');
		$invalid_versions = get_invalid_versions();

		if (!empty ($invalid_versions)) {
			if (isset ($invalid_versions['Rebuild Relationships'])) {
				unset ($invalid_versions['Rebuild Relationships']);

				// flag for pickup in DisplayWarnings.php
				$_SESSION['rebuild_relationships'] = true;
			}

			if (isset ($invalid_versions['Rebuild Extensions'])) {
				unset ($invalid_versions['Rebuild Extensions']);

				// flag for pickup in DisplayWarnings.php
				$_SESSION['rebuild_extensions'] = true;
			}

			$_SESSION['invalid_versions'] = $invalid_versions;
		}
		$this->fill_in_additional_detail_fields();
		if ($this->status != "Inactive")
			$this->authenticated = true;

		unset ($_SESSION['loginattempts']);
		return $this;
	}

	/**
	 * Verify that the current password is correct and write the new password to the DB.
	 *
	 * @param string $user name - Must be non null and at least 1 character.
	 * @param string $user_password - Must be non null and at least 1 character.
	 * @param string $new_password - Must be non null and at least 1 character.
	 * @return boolean - If passwords pass verification and query succeeds, return true, else return false.
	 */
	function change_password(
	    $user_password,
	    $new_password,
	    $system_generated = '0'
	    )
	{
	    global $mod_strings;
		global $current_user;
		$GLOBALS['log']->debug("Starting password change for $this->user_name");

		if (!isset ($new_password) || $new_password == "") {
			$this->error_string = $mod_strings['ERR_PASSWORD_CHANGE_FAILED_1'].$current_user['user_name'].$mod_strings['ERR_PASSWORD_CHANGE_FAILED_2'];
			return false;
		}

		$old_user_hash = strtolower(md5($user_password));

		if (!is_admin($current_user) && !is_admin_for_module($current_user,'Users')) {
			//check old password first
			$query = "SELECT user_name FROM $this->table_name WHERE user_hash='$old_user_hash' AND id='$this->id'";
			$result = $this->db->query($query, true);
			$row = $this->db->fetchByAssoc($result);
			$GLOBALS['log']->debug("select old password query: $query");
			$GLOBALS['log']->debug("return result of $row");
            if ($row == null) {
				$GLOBALS['log']->warn("Incorrect old password for ".$this->user_name."");
				$this->error_string = $mod_strings['ERR_PASSWORD_INCORRECT_OLD_1'].$this->user_name.$mod_strings['ERR_PASSWORD_INCORRECT_OLD_2'];
				return false;
			}
		}

        $user_hash = strtolower(md5($new_password));
        $this->setPreference('loginexpiration','0');
        //set new password
        $now=date("Y-m-d H:i:s");
		$query = "UPDATE $this->table_name SET user_hash='$user_hash', system_generated_password='$system_generated', pwd_last_changed='$now' where id='$this->id'";
		$this->db->query($query, true, "Error setting new password for $this->user_name: ");
        $_SESSION['hasExpiredPassword'] = '0';
		return true;
	}

	function is_authenticated() {
		return $this->authenticated;
	}

	function fill_in_additional_list_fields() {
		$this->fill_in_additional_detail_fields();
	}

	function fill_in_additional_detail_fields() {
		global $locale;

		$query = "SELECT u1.first_name, u1.last_name from users  u1, users  u2 where u1.id = u2.reports_to_id AND u2.id = '$this->id' and u1.deleted=0";
		$result = $this->db->query($query, true, "Error filling in additional detail fields");

		$row = $this->db->fetchByAssoc($result);
		$GLOBALS['log']->debug("additional detail query results: $row");

		if ($row != null) {
			$this->reports_to_name = stripslashes($row['first_name'].' '.$row['last_name']);
		} else {
			$this->reports_to_name = '';
		}
		//BEGIN SUGARCRM flav=pro ONLY
		$query = "SELECT team_id, teams.name, teams.name_2 FROM team_memberships rel RIGHT JOIN teams ON (rel.team_id = teams.id) WHERE rel.user_id = '{$this->id}' AND rel.team_id = '{$this->default_team}'";
		$result = $this->db->query($query, false, "Error retrieving team name: ");

		//rrs bug: 31277 - this tempDefaultTeam works in conjunction with the 'if' stmt below/
		//what was happening was that if the user was an admin user and they did not have team membership to their primary team
		//then this query would not return anything and the default_team would be set to empty, which is fine, but
		//we need to ensure that the team_id is not empty for team set widget purposes.
		$tempDefaultTeam = $this->default_team;

		$row = $this->db->fetchByAssoc($result);
		if (!empty ($row['team_id'])) {
			$this->default_team = $row['team_id'];
			$this->default_team_name = Team::getDisplayName($row['name'], $row['name_2'], $this->showLastNameFirst());
		} else {
			$this->default_team = '';
			$this->default_team_name = '';
			$this->team_set_id = '';
		}

		if(!empty($this->is_admin) && empty($this->default_team)){
			$this->team_id = $tempDefaultTeam;
		}else{
			$this->team_id = $this->default_team;
		}
		//END SUGARCRM flav=pro ONLY

		$this->_create_proper_name_field();
	}

	public function retrieve_user_id(
	    $user_name
	    )
	{
	    $userFocus = new User;
	    $userFocus->retrieve_by_string_fields(array('user_name'=>$user_name));
	    if ( empty($userFocus->id) )
	        return false;

        return $userFocus->id;
	}

	/**
	 * @return -- returns a list of all users in the system.
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
	 */
	function verify_data($ieVerified=true) {
		global $mod_strings, $current_user;
		$verified = TRUE;

		if (!empty ($this->id)) {
			// Make sure the user doesn't report to themselves.
			$reports_to_self = 0;
			$check_user = $this->reports_to_id;
			$already_seen_list = array ();
			while (!empty ($check_user)) {
				if (isset ($already_seen_list[$check_user])) {
					// This user doesn't actually report to themselves
					// But someone above them does.
					$reports_to_self = 1;
					break;
				}
				if ($check_user == $this->id) {
					$reports_to_self = 1;
					break;
				}
				$already_seen_list[$check_user] = 1;
				$query = "SELECT reports_to_id FROM users WHERE id='".$this->db->quote($check_user)."'";
				$result = $this->db->query($query, true, "Error checking for reporting-loop");
				$row = $this->db->fetchByAssoc($result);
				echo ("fetched: ".$row['reports_to_id']." from ".$check_user."<br>");
				$check_user = $row['reports_to_id'];
			}

			if ($reports_to_self == 1) {
				$this->error_string .= $mod_strings['ERR_REPORT_LOOP'];
				$verified = FALSE;
			}
		}

		$query = "SELECT user_name from users where user_name='$this->user_name' AND deleted=0";
		if(!empty($this->id))$query .=  " AND id<>'$this->id'";
		$result = $this->db->query($query, true, "Error selecting possible duplicate users: ");
		$dup_users = $this->db->fetchByAssoc($result);

		if (!empty($dup_users)) {
			$this->error_string .= $mod_strings['ERR_USER_NAME_EXISTS_1'].$this->user_name.$mod_strings['ERR_USER_NAME_EXISTS_2'];
			$verified = FALSE;
		}

		if (($current_user->is_admin == "on")) {
            if($this->db->dbType == 'mssql'){
                $query = "SELECT user_name from users where is_admin = 1 AND deleted=0";
            }else{
                $query = "SELECT user_name from users where is_admin = 'on' AND deleted=0";
            }
			$result = $this->db->query($query, true, "Error selecting possible duplicate users: ");
			$remaining_admins = $this->db->getRowCount($result);

			if (($remaining_admins <= 1) && ($this->is_admin != "on") && ($this->id == $current_user->id)) {
				$GLOBALS['log']->debug("Number of remaining administrator accounts: {$remaining_admins}");
				$this->error_string .= $mod_strings['ERR_LAST_ADMIN_1'].$this->user_name.$mod_strings['ERR_LAST_ADMIN_2'];
				$verified = FALSE;
			}
		}
		///////////////////////////////////////////////////////////////////////
		////	InboundEmail verification failure
		if(!$ieVerified) {
			$verified = false;
			$this->error_string .= '<br />'.$mod_strings['ERR_EMAIL_NO_OPTS'];
		}

		return $verified;
	}

	function get_list_view_data() {

		global $current_user;

		$user_fields = $this->get_list_view_array();
		if ($this->is_admin)
			$user_fields['IS_ADMIN_IMAGE'] = SugarThemeRegistry::current()->getImage('check_inline', '');
		elseif (!$this->is_admin) $user_fields['IS_ADMIN'] = '';
		if ($this->is_group)
			$user_fields['IS_GROUP_IMAGE'] = SugarThemeRegistry::current()->getImage('check_inline', '');
		else
			$user_fields['IS_GROUP_IMAGE'] = '';
		$user_fields['NAME'] = empty ($this->name) ? '' : $this->name;

		$user_fields['REPORTS_TO_NAME'] = $this->reports_to_name;

		//BEGIN SUGARCRM flav=pro ONLY
		if(isset($_REQUEST['module']) && $_REQUEST['module'] == 'Teams' &&
			(isset($_REQUEST['record']) && !empty($_REQUEST['record'])) ) {
			$q = "SELECT count(*) c FROM team_memberships WHERE deleted=0 AND user_id = '{$this->id}' AND team_id = '{$_REQUEST['record']}' AND explicit_assign = 1";
			$r = $this->db->query($q);
			$a = $this->db->fetchByAssoc($r);

			$user_fields['UPLINE'] = translate('LBL_TEAM_UPLINE','Users');

			if($a['c'] > 0) {
				$user_fields['UPLINE'] = translate('LBL_TEAM_UPLINE_EXPLICIT','Users');
			}
		}
		//END SUGARCRM flav=pro ONLY
		$user_fields['EMAIL1'] = $this->emailAddress->getPrimaryAddress($this);

		return $user_fields;
	}

	function list_view_parse_additional_sections(& $list_form, $xTemplateSection) {
		return $list_form;
	}

	function save_relationship_changes($is_update) {
		//BEGIN SUGARCRM flav=pro ONLY
		if($this->portal_only) {
		   return;
		}


		//todo: move this logic into a post save helper method.
		// If this is not an update, then make sure the new user logic is executed.
		if ($is_update == false) {
			// If this is a new user, make sure to add them to the appriate default teams
			if (!$this->team_exists) {
				$team = new Team();
				$team->new_user_created($this);
			}
		}else{
			//if this is an update, then we need to ensure we keep the user's
			//private team name and name_2 in sync with their name.
			$team_id = $this->getPrivateTeamID();
			if(!empty($team_id)){

				$team = new Team();
				$team->retrieve($team_id);
                Team::set_team_name_from_user($team, $this);
				$team->save();
			}
		}
		//END SUGARCRM flav=pro ONLY
	}



    //BEGIN SUGARCRM flav=pro ONLY
	/**
	 * returns the private team_id of the user, or if an ID is passed, of the
	 * target user
	 * @param id guid
	 * @return string
	 */
	function getPrivateTeam($id='') {
		if(!empty($id)) {
			$user = new User();
			$user->retrieve($id);
			return $user->getPrivateTeamID();
		}
		return $this->getPrivateTeamID();
	}


	function get_my_teams($return_obj = FALSE) {
		$query = "SELECT DISTINCT rel.team_id, teams.name, teams.name_2, rel.implicit_assign FROM team_memberships rel RIGHT JOIN teams ON (rel.team_id = teams.id) WHERE rel.user_id = '{$this->id}' AND rel.deleted = 0 ORDER BY teams.name ASC";
		$result = $this->db->query($query, false, "Error retrieving user ID: ");
		$out = Array ();

		if ($return_obj) {

			$x = 0;
		}

		while ($row = $this->db->fetchByAssoc($result)) {
			if ($return_obj) {
				$out[$x] = new Team();
				$out[$x]->retrieve($row['team_id']);
				$out[$x++]->implicit_assign = $row['implicit_assign'];
			} else {
				$out[$row['team_id']] = Team::getDisplayName($row['name'], $row['name_2']);
			}
		}

		return $out;
	}

	function getAllTeams() {
		$q = "SELECT id, name FROM teams WHERE private = 0 AND deleted = 0";
		$r = $this->db->query($q);

		$ret = array();
		while($a = $this->db->fetchByAssoc($r)) {
			$ret[$a['id']] = $a['name'];
		}

		return $ret;
	}


	/**
	 * When the user's reports to id is changed, this method is called.  This method needs to remove all
	 * of the implicit assignements that were created based on this user, then recreated all of the implicit
	 * assignments in the new location
	 */
	function update_team_memberships($old_reports_to_id) {

		$team = new Team();
		$team->user_manager_changed($this->id, $old_reports_to_id, $this->reports_to_id);
	}
	//END SUGARCRM flav=pro ONLY

	function create_export_query($order_by, $where) {
		include('modules/Users/field_arrays.php');

		$cols = '';
		foreach($fields_array['User']['export_fields'] as $field) {
			$cols .= (empty($cols)) ? '' : ', ';
			$cols .= $field;
		}

		$query = "SELECT {$cols} FROM users ";

		$where_auto = " users.deleted = 0";

		if ($where != "")
			$query .= " WHERE $where AND ".$where_auto;
		else
			$query .= " WHERE ".$where_auto;

		// admin for module user is not be able to export a super-admin
		global $current_user;
		if(!$current_user->is_admin){
			$query .= " AND users.is_admin=0";
		}

		if ($order_by != "")
			$query .= " ORDER BY $order_by";
		else
			$query .= " ORDER BY users.user_name";

		return $query;
	}

	/** Returns a list of the associated users
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
	*/
	function get_meetings() {
		// First, get the list of IDs.
		$query = "SELECT meeting_id as id from meetings_users where user_id='$this->id' AND deleted=0";
		return $this->build_related_list($query, new Meeting());
	}
	function get_calls() {
		// First, get the list of IDs.
		$query = "SELECT call_id as id from calls_users where user_id='$this->id' AND deleted=0";
		return $this->build_related_list($query, new Call());
	}

	/**
	 * generates Javascript to display I-E mail counts, both personal and group
	 */
	function displayEmailCounts() {
		global $theme;
		$new = translate('LBL_NEW', 'Emails');
		$default = 'index.php?module=Emails&action=ListView&assigned_user_id='.$this->id;
		$count = '';
		$verts = array('Love', 'Links', 'Pipeline', 'RipCurl', 'SugarLite');

		if($this->hasPersonalEmail()) {
			$r = $this->db->query('SELECT count(*) AS c FROM emails WHERE deleted=0 AND assigned_user_id = \''.$this->id.'\' AND type = \'inbound\' AND status = \'unread\'');
			$a = $this->db->fetchByAssoc($r);
			if(in_array($theme, $verts)) {
				$count .= '<br />';
			} else {
				$count .= '&nbsp;&nbsp;&nbsp;&nbsp;';
			}
			$count .= '<a href='.$default.'&type=inbound>'.translate('LBL_LIST_TITLE_MY_INBOX', 'Emails').': ('.$a['c'].' '.$new.')</a>';

			if(!in_array($theme, $verts)) {
				$count .= ' - ';
			}
		}

		$r = $this->db->query('SELECT id FROM users WHERE users.is_group = 1 AND deleted = 0');
		$groupIds = '';
		$groupNew = '';
		while($a = $this->db->fetchByAssoc($r)) {
			if($groupIds != '') {$groupIds .= ', ';}
			$groupIds .= "'".$a['id']."'";
		}

		$total = 0;
		if(strlen($groupIds) > 0) {
			$groupQuery = 'SELECT count(*) AS c FROM emails ';
			//BEGIN SUGARCRM flav=pro ONLY
			$this->add_team_security_where_clause($groupQuery);
			//END SUGARCRM flav=pro ONLY
			$groupQuery .= ' WHERE emails.deleted=0 AND emails.assigned_user_id IN ('.$groupIds.') AND emails.type = \'inbound\' AND emails.status = \'unread\'';
			$r = $this->db->query($groupQuery);
			if(is_resource($r)) {
				$a = $this->db->fetchByAssoc($r);
				if($a['c'] > 0) {
					$total = $a['c'];
				}
			}
		}
		if(in_array($theme, $verts)) $count .= '<br />';
		if(empty($count)) $count .= '&nbsp;&nbsp;&nbsp;&nbsp;';
		$count .= '<a href=index.php?module=Emails&action=ListViewGroup>'.translate('LBL_LIST_TITLE_GROUP_INBOX', 'Emails').': ('.$total.' '.$new.')</a>';

		$out  = '<script type="text/javascript" language="Javascript">';
		$out .= 'var welcome = document.getElementById("welcome");';
		$out .= 'var welcomeContent = welcome.innerHTML;';
		$out .= 'welcome.innerHTML = welcomeContent + "'.$count.'";';
		$out .= '</script>';

		echo $out;
	}

	function getPreferredEmail() {
		$ret = array ();
		$nameEmail = $this->getUsersNameAndEmail();
		$prefAddr = $nameEmail['email'];
		$fullName = $nameEmail['name'];
		if (empty ($prefAddr)) {
			$nameEmail = $this->getSystemDefaultNameAndEmail();
			$prefAddr = $nameEmail['email'];
			$fullName = $nameEmail['name'];
		} // if
		$fullName = from_html($fullName);
		$ret['name'] = $fullName;
		$ret['email'] = $prefAddr;
		return $ret;
	}

	function getUsersNameAndEmail() {
		$salutation = '';
		$fullName = '';
		if(!empty($this->salutation)) $salutation = $this->salutation;

		if(!empty($this->first_name)) {
			$fullName = trim($salutation.' '.$this->first_name.' '.$this->last_name);
		} elseif(!empty($this->name)) {
			$fullName = $this->name;
		}
		$prefAddr = $this->emailAddress->getPrimaryAddress($this);

		if (empty ($prefAddr)) {
			$prefAddr = $this->emailAddress->getReplyToAddress($this);
		}
		return array('email' => $prefAddr , 'name' => $fullName);

	} // fn

	function getSystemDefaultNameAndEmail() {

		$email = new Email();
		$return = $email->getSystemDefaultEmail();
		$prefAddr = $return['email'];
		$fullName = $return['name'];
		return array('email' => $prefAddr , 'name' => $fullName);
	} // fn

	/**
	 * sets User email default in config.php if not already set by install - i.
	 * e., upgrades
	 */
	function setDefaultsInConfig() {
		global $sugar_config;
		$sugar_config['email_default_client'] = 'sugar';
		$sugar_config['email_default_editor'] = 'html';
		ksort($sugar_config);
		write_array_to_file('sugar_config', $sugar_config, 'config.php');
		return $sugar_config;
	}

    /**
     * returns User's email address based on descending order of preferences
     *
     * @param string id GUID of target user if needed
     * @return array Assoc array for an email and name
     */
    function getEmailInfo($id='') {
        $user = $this;
        if(!empty($id)) {
            $user = new User();
            $user->retrieve($id);
        }

        // from name
        $fromName = $user->getPreference('mail_fromname');
        if(empty($fromName)) {
        	// cn: bug 8586 - localized name format
            $fromName = $user->full_name;
        }

        // from address
        $fromaddr = $user->getPreference('mail_fromaddress');
        if(empty($fromaddr)) {
            if(!empty($user->email1) && isset($user->email1)) {
                $fromaddr = $user->email1;
            } elseif(!empty($user->email2) && isset($user->email2)) {
                $fromaddr = $user->email2;
            } else {
                $r = $user->db->query("SELECT value FROM config WHERE name = 'fromaddress'");
                $a = $user->db->fetchByAssoc($r);
                $fromddr = $a['value'];
            }
        }

        $ret['name'] = $fromName;
        $ret['email'] = $fromaddr;

        return $ret;
    }

	/**
	 * returns opening <a href=xxxx for a contact, account, etc
	 * cascades from User set preference to System-wide default
	 * @return string	link
	 * @param attribute the email addy
	 * @param focus the parent bean
	 * @param contact_id
	 * @param return_module
	 * @param return_action
	 * @param return_id
	 * @param class
	 */
	function getEmailLink2($emailAddress, &$focus, $contact_id='', $ret_module='', $ret_action='DetailView', $ret_id='', $class='') {
		$emailLink = '';
		global $sugar_config;

		if(!isset($sugar_config['email_default_client'])) {
			$this->setDefaultsInConfig();
		}

		$userPref = $this->getPreference('email_link_type');
		$defaultPref = $sugar_config['email_default_client'];
		if($userPref != '') {
			$client = $userPref;
		} else {
			$client = $defaultPref;
		}
		//BEGIN SUGARCRM flav=pro ONLY
		// check for presence of a mobile device, if so use it's email client
		if(isset($_SESSION['isMobile'])){
			$client = 'other';
		}
		//END SUGARCRM flav=pro ONLY

		if($client == 'sugar') {
			$salutation = '';
			$fullName = '';
			$email = '';
			$to_addrs_ids = '';
			$to_addrs_names = '';
			$to_addrs_emails = '';

			if(!empty($focus->salutation)) $salutation = $focus->salutation;

			if(!empty($focus->first_name)) {
				$fullName = trim($salutation.' '.$focus->first_name.' '.$focus->last_name);
			} elseif(!empty($focus->name)) {
				$fullName = $focus->name;
			}

			if(empty($ret_module)) $ret_module = $focus->module_dir;
			if(empty($ret_id)) $ret_id = $focus->id;
			if($focus->object_name == 'Contact') {
				$contact_id = $focus->id;
				$to_addrs_ids = $focus->id;
				$to_addrs_names = $fullName;
				$to_addrs_emails = $focus->email1;
			}

			$emailLinkUrl = 'contact_id='.$contact_id.
				'&parent_type='.$focus->module_dir.
				'&parent_id='.$focus->id.
				'&parent_name='.urlencode($fullName).
				'&to_addrs_ids='.$to_addrs_ids.
				'&to_addrs_names='.urlencode($to_addrs_names).
				'&to_addrs_emails='.urlencode($to_addrs_emails).
				'&to_email_addrs='.urlencode($fullName . '&nbsp;&lt;' . $emailAddress . '&gt;').
				'&return_module='.$ret_module.
				'&return_action='.$ret_action.
				'&return_id='.$ret_id;

    		//Generate the compose package for the quick create options.
    		//$json = getJSONobj();
    		//$composeOptionsLink = $json->encode( array('composeOptionsLink' => $emailLinkUrl,'id' => $focus->id) );
			require_once('modules/Emails/EmailUI.php');
            $eUi = new EmailUI();
            $j_quickComposeOptions = $eUi->generateComposePackageForQuickCreateFromComposeUrl($emailLinkUrl);

    		$emailLink = "<a href='javascript:void(0);' onclick='SUGAR.quickCompose.init($j_quickComposeOptions);' class='$class'>";

		} else {
			// straight mailto:
			$emailLink = '<a href="mailto:'.$emailAddress.'" class="'.$class.'">';
		}

		return $emailLink;
	}

	/**
	 * returns opening <a href=xxxx for a contact, account, etc
	 * cascades from User set preference to System-wide default
	 * @return string	link
	 * @param attribute the email addy
	 * @param focus the parent bean
	 * @param contact_id
	 * @param return_module
	 * @param return_action
	 * @param return_id
	 * @param class
	 */
	function getEmailLink($attribute, &$focus, $contact_id='', $ret_module='', $ret_action='DetailView', $ret_id='', $class='') {
	    $emailLink = '';
		global $sugar_config;

		if(!isset($sugar_config['email_default_client'])) {
			$this->setDefaultsInConfig();
		}

		$userPref = $this->getPreference('email_link_type');
		$defaultPref = $sugar_config['email_default_client'];
		if($userPref != '') {
			$client = $userPref;
		} else {
			$client = $defaultPref;
		}
		//BEGIN SUGARCRM flav=pro ONLY
		// check for presence of a mobile device, if so use it's email client
		if(isset($_SESSION['isMobile'])){
			$client = 'other';
		}
		//END SUGARCRM flav=pro ONLY

		if($client == 'sugar') {
			$salutation = '';
			$fullName = '';
			$email = '';
			$to_addrs_ids = '';
			$to_addrs_names = '';
			$to_addrs_emails = '';

			if(!empty($focus->salutation)) $salutation = $focus->salutation;

			if(!empty($focus->first_name)) {
				$fullName = trim($salutation.' '.$focus->first_name.' '.$focus->last_name);
			} elseif(!empty($focus->name)) {
				$fullName = $focus->name;
			}
			if(!empty($focus->$attribute)) {
				$email = $focus->$attribute;
			}


			if(empty($ret_module)) $ret_module = $focus->module_dir;
			if(empty($ret_id)) $ret_id = $focus->id;
			if($focus->object_name == 'Contact') {
				$contact_id = $focus->id;
				$to_addrs_ids = $focus->id;
				$to_addrs_names = $fullName;
				$to_addrs_emails = $focus->email1;
			}

			$emailLinkUrl = 'contact_id='.$contact_id.
				'&parent_type='.$focus->module_dir.
				'&parent_id='.$focus->id.
				'&parent_name='.urlencode($fullName).
				'&to_addrs_ids='.$to_addrs_ids.
				'&to_addrs_names='.urlencode($to_addrs_names).
				'&to_addrs_emails='.urlencode($to_addrs_emails).
				'&to_email_addrs='.urlencode($fullName . '&nbsp;&lt;' . $email . '&gt;').
				'&return_module='.$ret_module.
				'&return_action='.$ret_action.
				'&return_id='.$ret_id;

			//Generate the compose package for the quick create options.
    		require_once('modules/Emails/EmailUI.php');
            $eUi = new EmailUI();
            $j_quickComposeOptions = $eUi->generateComposePackageForQuickCreateFromComposeUrl($emailLinkUrl);
    		$emailLink = "<a href='javascript:void(0);' onclick='SUGAR.quickCompose.init($j_quickComposeOptions);' class='$class'>";

		} else {
			// straight mailto:
			$emailLink = '<a href="mailto:'.$focus->$attribute.'" class="'.$class.'">';
		}

		return $emailLink;
	}


	/**
	 * gets a human-readable explanation of the format macro
	 * @return string Human readable name format
	 */
	function getLocaleFormatDesc() {
		global $locale;
		global $mod_strings;
		global $app_strings;

		$format['f'] = $mod_strings['LBL_LOCALE_DESC_FIRST'];
		$format['l'] = $mod_strings['LBL_LOCALE_DESC_LAST'];
		$format['s'] = $mod_strings['LBL_LOCALE_DESC_SALUTATION'];
		$format['t'] = $mod_strings['LBL_LOCALE_DESC_TITLE'];

		$name['f'] = $app_strings['LBL_LOCALE_NAME_EXAMPLE_FIRST'];
		$name['l'] = $app_strings['LBL_LOCALE_NAME_EXAMPLE_LAST'];
		$name['s'] = $app_strings['LBL_LOCALE_NAME_EXAMPLE_SALUTATION'];
		$name['t'] = $app_strings['LBL_LOCALE_NAME_EXAMPLE_TITLE'];

		$macro = $locale->getLocaleFormatMacro();

		$ret1 = '';
		$ret2 = '';
		for($i=0; $i<strlen($macro); $i++) {
			if(array_key_exists($macro{$i}, $format)) {
				$ret1 .= "<i>".$format[$macro{$i}]."</i>";
				$ret2 .= "<i>".$name[$macro{$i}]."</i>";
			} else {
				$ret1 .= $macro{$i};
				$ret2 .= $macro{$i};
			}
		}
		return $ret1."<br />".$ret2;
	}


	//BEGIN SUGARCRM flav=pro ONLY
	public function getPrivateTeamID()
	{
	    return self::staticGetPrivateTeamID($this->id);
	}

    public static function staticGetPrivateTeamID($user_id)
	{
	    $teamFocus = new Team;
	    $teamFocus->retrieve_by_string_fields(array('associated_user_id'=>$user_id));
	    if ( empty($teamFocus->id) )
	        return '';

	    return $teamFocus->id;
	}
    //END SUGARCRM flav=pro ONLY
	/**
	 * Whether or not based on the user's locale if we should show the last name first.
	 *
	 * @return bool
	 */
	public function showLastNameFirst(){
		global $locale;
    	$localeFormat = $locale->getLocaleFormatMacro($this);
		if ( strpos($localeFormat,'l') > strpos($localeFormat,'f') ) {
                    return false;
        }else {
        	return true;
        }
	}

	//BEGIN SUGARCRM flav!=com ONLY
	function preprocess_fields_on_save(){
		parent::preprocess_fields_on_save();
        require_once('include/upload_file.php');
		$upload_file = new UploadFile("picture");

		//remove file
		if (isset($_REQUEST['remove_imagefile_picture']) && $_REQUEST['remove_imagefile_picture'] == 1)
		{
			$upload_file->unlink_file($this->picture);
			$this->picture="";
		}

		//uploadfile
		if (isset($_FILES['picture']))
		{
			//confirm only image file type can be uploaded
			$imgType = array('image/gif', 'image/png', 'image/bmp', 'image/jpeg', 'image/jpg', 'image/pjpeg');
			if (in_array($_FILES['picture']["type"], $imgType))
			{
				if ($upload_file->confirm_upload())
				{
					$this->picture = create_guid();
					$upload_file->final_move( $this->picture);
					$url=$upload_file->get_url($this->picture);
				}
			}
		}

		//duplicate field handling (in the event the Duplicate button was pressed)
		if(empty($this->picture) && !empty($_REQUEST['picture_duplicate'])) {
           $this->picture = $_REQUEST['picture_duplicate'];
		}
	}
	//END SUGARCRM flav!=com ONLY
}
