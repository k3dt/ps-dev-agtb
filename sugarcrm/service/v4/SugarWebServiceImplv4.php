<?php
if(!defined('sugarEntry'))define('sugarEntry', true);
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
 *
 ********************************************************************************/

/**
 * This class is an implemenatation class for all the rest services
 */
require_once('service/v3_1/SugarWebServiceImplv3_1.php');
require_once('SugarWebServiceUtilv4.php');


class SugarWebServiceImplv4 extends SugarWebServiceImplv3_1 {

    public function __construct()
    {
        self::$helperObject = new SugarWebServiceUtilv4();
    }
    
        /**
     * Log the user into the application
     *
     * @param UserAuth array $user_auth -- Set user_name and password (password needs to be
     *      in the right encoding for the type of authentication the user is setup for.  For Base
     *      sugar validation, password is the MD5 sum of the plain text password.
     * @param String $application -- The name of the application you are logging in from.  (Currently unused).
     * @param array $name_value_list -- Array of name value pair of extra parameters. As of today only 'language' and 'notifyonsave' is supported
     * @return Array - id - String id is the session_id of the session that was created.
     * 				 - module_name - String - module name of user
     * 				 - name_value_list - Array - The name value pair of user_id, user_name, user_language, user_currency_id, user_currency_name,
     *                                         - user_default_team_id, user_is_admin, user_default_dateformat, user_default_timeformat
     * @exception 'SoapFault' -- The SOAP error, if any
     */
    public function login($user_auth, $application, $name_value_list = array()){
        $GLOBALS['log']->info('Begin: SugarWebServiceImpl->login');
        global $sugar_config, $system_config;
        $error = new SoapError();
        $user = new User();
        $success = false;
        //rrs
        $system_config = new Administration();
        $system_config->retrieveSettings('system');
        $authController = new AuthenticationController((!empty($sugar_config['authenticationClass'])? $sugar_config['authenticationClass'] : 'SugarAuthenticate'));
        //rrs
        if(!empty($user_auth['encryption']) && $user_auth['encryption'] === 'PLAIN' && $authController->authController->userAuthenticateClass != "LDAPAuthenticateUser")
        {
            $user_auth['password'] = md5($user_auth['password']);
        }
        $isLoginSuccess = $authController->login($user_auth['user_name'], $user_auth['password'], array('passwordEncrypted' => true));
        $usr_id=$user->retrieve_user_id($user_auth['user_name']);
        if($usr_id)
            $user->retrieve($usr_id);

        if ($isLoginSuccess)
        {
            if ($_SESSION['hasExpiredPassword'] =='1')
            {
                $error->set_error('password_expired');
                $GLOBALS['log']->fatal('password expired for user ' . $user_auth['user_name']);
                LogicHook::initialize();
                $GLOBALS['logic_hook']->call_custom_logic('Users', 'login_failed');
                self::$helperObject->setFaultObject($error);
                return;
            }
            if(!empty($user) && !empty($user->id) && !$user->is_group)
            {
                $success = true;
                global $current_user;
                $current_user = $user;
            }
        }
        else if($usr_id && isset($user->user_name) && ($user->getPreference('lockout') == '1'))
        {
            $error->set_error('lockout_reached');
            $GLOBALS['log']->fatal('Lockout reached for user ' . $user_auth['user_name']);
            LogicHook::initialize();
            $GLOBALS['logic_hook']->call_custom_logic('Users', 'login_failed');
            self::$helperObject->setFaultObject($error);
            return;
        }
        else if( $authController->authController->userAuthenticateClass == "LDAPAuthenticateUser"
                 && (empty($user_auth['encryption']) || $user_auth['encryption'] !== 'PLAIN' ) )
        {
            $error->set_error('ldap_error');
            LogicHook::initialize();
            $GLOBALS['logic_hook']->call_custom_logic('Users', 'login_failed');
            self::$helperObject->setFaultObject($error);
            return;
        }
        else if(function_exists('mcrypt_cbc'))
        {
            $password = self::$helperObject->decrypt_string($user_auth['password']);
            if($authController->login($user_auth['user_name'], $password) && isset($_SESSION['authenticated_user_id']))
                $success = true;
        }

        if($success)
        {
            session_start();
            global $current_user;
            //$current_user = $user;
            self::$helperObject->login_success($name_value_list);
            $current_user->loadPreferences();
            $_SESSION['is_valid_session']= true;
            $_SESSION['ip_address'] = query_client_ip();
            $_SESSION['user_id'] = $current_user->id;
            $_SESSION['type'] = 'user';
            $_SESSION['avail_modules']= self::$helperObject->get_user_module_list($current_user);
            $_SESSION['authenticated_user_id'] = $current_user->id;
            $_SESSION['unique_key'] = $sugar_config['unique_key'];
            $current_user->call_custom_logic('after_login');
            $GLOBALS['log']->info('End: SugarWebServiceImpl->login - succesful login');
            $nameValueArray = array();
            global $current_language;
            $nameValueArray['user_id'] = self::$helperObject->get_name_value('user_id', $current_user->id);
            $nameValueArray['user_name'] = self::$helperObject->get_name_value('user_name', $current_user->user_name);
            $nameValueArray['user_language'] = self::$helperObject->get_name_value('user_language', $current_language);
            $cur_id = $current_user->getPreference('currency');
            $nameValueArray['user_currency_id'] = self::$helperObject->get_name_value('user_currency_id', $cur_id);
            $nameValueArray['user_is_admin'] = self::$helperObject->get_name_value('user_is_admin', is_admin($current_user));
            $nameValueArray['user_default_team_id'] = self::$helperObject->get_name_value('user_default_team_id', $current_user->default_team );
            $nameValueArray['user_default_dateformat'] = self::$helperObject->get_name_value('user_default_dateformat', $current_user->getPreference('datef') );
            $nameValueArray['user_default_timeformat'] = self::$helperObject->get_name_value('user_default_timeformat', $current_user->getPreference('timef') );

            $num_grp_sep = $current_user->getPreference('num_grp_sep');
            $dec_sep = $current_user->getPreference('dec_sep');
            $nameValueArray['user_number_seperator'] = self::$helperObject->get_name_value('user_number_seperator', empty($num_grp_sep) ? $sugar_config['default_number_grouping_seperator'] : $num_grp_sep);
            $nameValueArray['user_decimal_seperator'] = self::$helperObject->get_name_value('user_decimal_seperator', empty($dec_sep) ? $sugar_config['default_decimal_seperator'] : $dec_sep);

            $nameValueArray['mobile_max_list_entries'] = self::$helperObject->get_name_value('mobile_max_list_entries', $sugar_config['wl_list_max_entries_per_page'] );
            $nameValueArray['mobile_max_subpanel_entries'] = self::$helperObject->get_name_value('mobile_max_subpanel_entries', $sugar_config['wl_list_max_entries_per_subpanel'] );

            //BEGIN SUGARCRM flav=pro ONLY
            if($application == 'mobile')
            {
                $modules = $availModuleNames = array();
                $availModules = array_keys($_SESSION['avail_modules']); //ACL check already performed.
                $modules = self::$helperObject->get_visible_mobile_modules($availModules);
                $nameValueArray['available_modules'] = $modules;
                //Get the vardefs md5
                foreach($modules as $mod_def)
                    $availModuleNames[] = $mod_def['module_key'];

                $nameValueArray['vardefs_md5'] = self::get_module_fields_md5(session_id(), $availModuleNames);
                
                self::$helperObject->get_mobile_login_data($nameValueArray);
            }
            //END SUGARCRM flav=pro ONLY
            
            $currencyObject = new Currency();
            $currencyObject->retrieve($cur_id);
            $nameValueArray['user_currency_name'] = self::$helperObject->get_name_value('user_currency_name', $currencyObject->name);
            $_SESSION['user_language'] = $current_language;
            return array('id'=>session_id(), 'module_name'=>'Users', 'name_value_list'=>$nameValueArray);
        }
        LogicHook::initialize();
        $GLOBALS['logic_hook']->call_custom_logic('Users', 'login_failed');
        $error->set_error('invalid_login');
        self::$helperObject->setFaultObject($error);
        $GLOBALS['log']->info('End: SugarWebServiceImpl->login - failed login');
    }
    
    	
	/**
	 * Retrieve a list of SugarBean's based on provided IDs. This API will not wotk with report module
	 *
	 * @param String $session -- Session ID returned by a previous call to login.
	 * @param String $module_name -- The name of the module to return records from.  This name should be the name the module was developed under (changing a tab name is studio does not affect the name that should be passed into this method)..
	 * @param Array $ids -- An array of SugarBean IDs.
	 * @param Array $select_fields -- A list of the fields to be included in the results. This optional parameter allows for only needed fields to be retrieved.
	 * @param Array $link_name_to_fields_array -- A list of link_names and for each link_name, what fields value to be returned. For ex.'link_name_to_fields_array' => array(array('name' =>  'email_addresses', 'value' => array('id', 'email_address', 'opt_out', 'primary_address')))
	 * @return Array
	 *        'entry_list' -- Array - The records name value pair for the simple data types excluding link field data.
	 *	     'relationship_list' -- Array - The records link field data. The example is if asked about accounts email address then return data would look like Array ( [0] => Array ( [name] => email_addresses [records] => Array ( [0] => Array ( [0] => Array ( [name] => id [value] => 3fb16797-8d90-0a94-ac12-490b63a6be67 ) [1] => Array ( [name] => email_address [value] => hr.kid.qa@example.com ) [2] => Array ( [name] => opt_out [value] => 0 ) [3] => Array ( [name] => primary_address [value] => 1 ) ) [1] => Array ( [0] => Array ( [name] => id [value] => 403f8da1-214b-6a88-9cef-490b63d43566 ) [1] => Array ( [name] => email_address [value] => kid.hr@example.name ) [2] => Array ( [name] => opt_out [value] => 0 ) [3] => Array ( [name] => primary_address [value] => 0 ) ) ) ) )
	 * @exception 'SoapFault' -- The SOAP error, if any
	 */
	public function get_entries($session, $module_name, $ids, $select_fields, $link_name_to_fields_array)
	{
	    $result = parent::get_entries($session, $module_name, $ids, $select_fields, $link_name_to_fields_array);
		$relationshipList = $result['relationship_list'];
		$returnRelationshipList = array();
		foreach($relationshipList as $rel){
			$link_output = array();
			foreach($rel as $row){
				$rowArray = array();
				foreach($row['records'] as $record){
					$rowArray[]['link_value'] = $record;
				}
				$link_output[] = array('name' => $row['name'], 'records' => $rowArray);
			}
			$returnRelationshipList[]['link_list'] = $link_output;
		}
		
		$result['relationship_list'] = $returnRelationshipList;
		return $result;
	}
	
	/**
     * Retrieve the layout metadata for a given module given a specific type and view.
     *
     * @param String $session -- Session ID returned by a previous call to login.
     * @param array $module_name(s) -- The name of the module(s) to return records from.  This name should be the name the module was developed under (changing a tab name is studio does not affect the name that should be passed into this method)..
     * @return array $type The type(s) of views requested.  Current supported types are 'default' (for application) and 'wireless'
     * @return array $view The view(s) requested.  Current supported types are edit, detail, list, and subpanel.
     * @exception 'SoapFault' -- The SOAP error, if any
     */
    function get_module_layout($session, $a_module_names, $a_type, $a_view,$acl_check = TRUE, $md5 = FALSE){
    	$GLOBALS['log']->fatal('Begin: SugarWebServiceImpl->get_module_layout');
    
    	global  $beanList, $beanFiles;
    	$error = new SoapError();
        $results = array();
        foreach ($a_module_names as $module_name)
        {
            if (!self::$helperObject->checkSessionAndModuleAccess($session, 'invalid_session', $module_name, 'read', 'no_access', $error))
            {
                $GLOBALS['log']->info('End: SugarWebServiceImpl->get_module_layout');
                continue;
            }

            if( empty($module_name) )
                continue;
                
            $class_name = $beanList[$module_name];
            require_once($beanFiles[$class_name]);
            $seed = new $class_name();

            foreach ($a_view as $view)
            {
                $aclViewCheck = (strtolower($view) == 'subpanel') ? 'DetailView' : ucfirst(strtolower($view)) . 'View';
                if(!$acl_check || $seed->ACLAccess($aclViewCheck, true) )
                {
                    foreach ($a_type as $type)
                    {
                        $a_vardefs = self::$helperObject->get_module_view_defs($module_name, $type, $view);
                        if($md5)
                            $results[$module_name][$type][$view] = md5(serialize($a_vardefs));
                        else
                            $results[$module_name][$type][$view] = $a_vardefs;
                    }
                }
            }
        }
    	 
        $GLOBALS['log']->info('End: SugarWebServiceImpl->get_module_layout');
    	
        return $results;
    }
    
	
	/**
     * Given a list of modules to search and a search string, return the id, module_name, along with the fields
     * We will support Accounts, Bug Tracker, Cases, Contacts, Leads, Opportunities, Project, ProjectTask, Quotes
     *
     * @param string $session			- Session ID returned by a previous call to login.
     * @param string $search_string 	- string to search
     * @param string[] $modules			- array of modules to query
     * @param int $offset				- a specified offset in the query
     * @param int $max_results			- max number of records to return
     * @param string $assigned_user_id	- a user id to filter all records by, leave empty to exclude the filter
     * @param string[] $select_fields   - An array of fields to return.  If empty the default return fields will be from the active list view defs.
     * @param bool $unified_search_only - A boolean indicating if we should only search against those modules participating in the unified search.  
     * @param bool $favorites           - A boolean indicating if we should only search against records marked as favorites.  
     * @return Array return_search_result 	- Array('Accounts' => array(array('name' => 'first_name', 'value' => 'John', 'name' => 'last_name', 'value' => 'Do')))
     * @exception 'SoapFault' -- The SOAP error, if any
     */
    function search_by_module($session, $search_string, $modules, $offset, $max_results,$assigned_user_id = '', $select_fields = array(), $unified_search_only = TRUE, $favorites = FALSE){
    	$GLOBALS['log']->info('Begin: SugarWebServiceImpl->search_by_module');
    	global  $beanList, $beanFiles;
    	global $sugar_config,$current_language;
    
    	$error = new SoapError();
    	$output_list = array();
    	if (!self::$helperObject->checkSessionAndModuleAccess($session, 'invalid_session', '', '', '', $error)) {
    		$error->set_error('invalid_login');
    		$GLOBALS['log']->info('End: SugarWebServiceImpl->search_by_module');
    		return;
    	}
    	global $current_user;
    	if($max_results > 0){
    		$sugar_config['list_max_entries_per_page'] = $max_results;
    	}
    
    	require_once('modules/Home/UnifiedSearchAdvanced.php');
    	require_once 'include/utils.php';
    	$usa = new UnifiedSearchAdvanced();
        if(!file_exists($GLOBALS['sugar_config']['cache_dir'].'modules/unified_search_modules.php')) {
            $usa->buildCache();
        }
    
    	include($GLOBALS['sugar_config']['cache_dir'].'modules/unified_search_modules.php');
    	$modules_to_search = array();
    	$unified_search_modules['Users'] =   array('fields' => array());
    	
    	//BEGIN SUGARCRM flav!=sales ONLY
    	$unified_search_modules['ProjectTask'] =   array('fields' => array());
        //END SUGARCRM flav!=sales ONLY
    	
        //If we are ignoring the unified search flag within the vardef we need to re-create the search fields.  This allows us to search
        //against a specific module even though it is not enabled for the unified search within the application.
        if( !$unified_search_only )
        {
            foreach ($modules as $singleModule)
            {
                if( !isset($unified_search_modules[$singleModule]) )
                {
                    $newSearchFields = array('fields' => self::$helperObject->generateUnifiedSearchFields($singleModule) );
                    $unified_search_modules[$singleModule] = $newSearchFields;
                }
            }
        }
        
        
        foreach($unified_search_modules as $module=>$data) {
        	if (in_array($module, $modules)) {
            	$modules_to_search[$module] = $beanList[$module];
        	} // if
        } // foreach
    
        $GLOBALS['log']->info('SugarWebServiceImpl->search_by_module - search string = ' . $search_string);
    
    	if(!empty($search_string) && isset($search_string)) {
    		$search_string = trim($GLOBALS['db']->quote(securexss(from_html(clean_string($search_string, 'UNIFIED_SEARCH')))));
        	foreach($modules_to_search as $name => $beanName) {
        		$where_clauses_array = array();
    			$unifiedSearchFields = array () ;
    			foreach ($unified_search_modules[$name]['fields'] as $field=>$def ) {
    				$unifiedSearchFields[$name] [ $field ] = $def ;
    				$unifiedSearchFields[$name] [ $field ]['value'] = $search_string;
    			}
    
    			require_once $beanFiles[$beanName] ;
    			$seed = new $beanName();
    			require_once 'include/SearchForm/SearchForm2.php' ;
    			if ($beanName == "User" 
    			     //BEGIN SUGARCRM flav!=sales ONLY
    			    || $beanName == "ProjectTask"
    			     //END SUGARCRM flav!=sales ONLY
    			    ) {
    				if(!self::$helperObject->check_modules_access($current_user, $seed->module_dir, 'read')){
    					continue;
    				} // if
    				if(!$seed->ACLAccess('ListView')) {
    					continue;
    				} // if
    			}
    
    			if ($beanName != "User" 
    			     //BEGIN SUGARCRM flav!=sales ONLY
    			    && $beanName != "ProjectTask"
    			     //END SUGARCRM flav!=sales ONLY
    			    ) {
    				$searchForm = new SearchForm ($seed, $name ) ;
    
    				$searchForm->setup(array ($name => array()) ,$unifiedSearchFields , '' , 'saved_views' /* hack to avoid setup doing further unwanted processing */ ) ;
    				$where_clauses = $searchForm->generateSearchWhere() ;
    				require_once 'include/SearchForm/SearchForm2.php' ;
    				$searchForm = new SearchForm ($seed, $name ) ;
    
    				$searchForm->setup(array ($name => array()) ,$unifiedSearchFields , '' , 'saved_views' /* hack to avoid setup doing further unwanted processing */ ) ;
    				$where_clauses = $searchForm->generateSearchWhere() ;
    				$emailQuery = false;
    
    				$where = '';
    				if (count($where_clauses) > 0 ) {
    					$where = '('. implode(' ) OR ( ', $where_clauses) . ')';
    				}
                    
    				$mod_strings = return_module_language($current_language, $seed->module_dir);
    				
    				if(count($select_fields) > 0) 
    				    $filterFields = $select_fields;
    				else {
    				    if(file_exists('custom/modules/'.$seed->module_dir.'/metadata/listviewdefs.php'))
    					   require_once('custom/modules/'.$seed->module_dir.'/metadata/listviewdefs.php');
        				else
        					require_once('modules/'.$seed->module_dir.'/metadata/listviewdefs.php');
        				
        				$filterFields = array();
        				foreach($listViewDefs[$seed->module_dir] as $colName => $param) {
        	                if(!empty($param['default']) && $param['default'] == true) 
        	                    $filterFields[] = strtolower($colName);
        	            } 
        	            if (!in_array('id', $filterFields))
        	            	$filterFields[] = 'id';
    				}
    				
    				//Pull in any db fields used for the unified search query so the correct joins will be added
    				$selectOnlyQueryFields = array();
    				foreach ($unifiedSearchFields[$name] as $field => $def){
    				    if( isset($def['db_field']) && !in_array($field,$filterFields) ){
    				        $filterFields[] = $field;
    				        $selectOnlyQueryFields[] = $field;
    				    }
    				}

    	            //Add the assigned user filter if applicable
    	            if (!empty($assigned_user_id) && isset( $seed->field_defs['assigned_user_id']) ) {
    	               $ownerWhere = $seed->getOwnerWhere($assigned_user_id);
    	               $where = "($where) AND $ownerWhere";
    	            }
                    
    	            if( $beanName == "Employee" )
    	            {
    	                $where = "($where) AND users.deleted = 0 AND users.is_group = 0 AND users.employee_status = 'Active'";
    	            }
    	            
    	            $list_params = array();
    	            //BEGIN SUGARCRM flav=pro ONLY
    	            if( $seed->isFavoritesEnabled() && $favorites === TRUE )
    	            {
    	                $list_params['favorites'] = 2;
    	            }
    	            //END SUGARCRM flav=pro ONLY
    	            
    				$ret_array = $seed->create_new_list_query('', $where, $filterFields, $list_params, 0, '', true, $seed, true);
    		        if(empty($params) or !is_array($params)) $params = array();
    		        if(!isset($params['custom_select'])) $params['custom_select'] = '';
    		        if(!isset($params['custom_from'])) $params['custom_from'] = '';
    		        if(!isset($params['custom_where'])) $params['custom_where'] = '';
    		        if(!isset($params['custom_order_by'])) $params['custom_order_by'] = '';
    				$main_query = $ret_array['select'] . $params['custom_select'] . $ret_array['from'] . $params['custom_from'] . $ret_array['where'] . $params['custom_where'] . $ret_array['order_by'] . $params['custom_order_by'];
    			} else {
    				if ($beanName == "User") {
    					$filterFields = array('id', 'user_name', 'first_name', 'last_name', 'email_address');
    					$main_query = "select users.id, ea.email_address, users.user_name, first_name, last_name from users ";
    					$main_query = $main_query . " LEFT JOIN email_addr_bean_rel eabl ON eabl.bean_module = '{$seed->module_dir}'
    LEFT JOIN email_addresses ea ON (ea.id = eabl.email_address_id) ";
    					$main_query = $main_query . "where ((users.first_name like '{$search_string}') or (users.last_name like '{$search_string}') or (users.user_name like '{$search_string}') or (ea.email_address like '{$search_string}')) and users.deleted = 0 and users.is_group = 0 and users.employee_status = 'Active'";
    				} // if
    				 //BEGIN SUGARCRM flav!=sales ONLY
    				if ($beanName == "ProjectTask") {
    					$filterFields = array('id', 'name', 'project_id', 'project_name');
    					$main_query = "select {$seed->table_name}.project_task_id id,{$seed->table_name}.project_id, {$seed->table_name}.name, project.name project_name from {$seed->table_name} ";
    					$seed->add_team_security_where_clause($main_query);
    					$main_query .= "LEFT JOIN teams ON $seed->table_name.team_id=teams.id AND (teams.deleted=0) ";
    		            $main_query .= "LEFT JOIN project ON $seed->table_name.project_id = project.id ";
    		            $main_query .= "where {$seed->table_name}.name like '{$search_string}%'";
    				} // if
    				 //END SUGARCRM flav!=sales ONLY
    			} // else

    			$GLOBALS['log']->info('SugarWebServiceImpl->search_by_module - query = ' . $main_query);
    	   		if($max_results < -1) {
    				$result = $seed->db->query($main_query);
    			}
    			else {
    				if($max_results == -1) {
    					$limit = $sugar_config['list_max_entries_per_page'];
    	            } else {
    	            	$limit = $max_results;
    	            }
    	            $result = $seed->db->limitQuery($main_query, $offset, $limit + 1);
    			}
    
    			$rowArray = array();
    			while($row = $seed->db->fetchByAssoc($result)) {
    				$nameValueArray = array();
    				foreach ($filterFields as $field) {
    				    if(in_array($field, $selectOnlyQueryFields))
    				        continue;
    					$nameValue = array();
    					if (isset($row[$field])) {
    						$nameValueArray[$field] = self::$helperObject->get_name_value($field, $row[$field]);
    					} // if
    				} // foreach
    				$rowArray[] = $nameValueArray;
    			} // while
    			$output_list[] = array('name' => $name, 'records' => $rowArray);
        	} // foreach
    
    	$GLOBALS['log']->info('End: SugarWebServiceImpl->search_by_module');
    	return array('entry_list'=>$output_list);
    	} // if
    	return array('entry_list'=>$output_list);
    } // fn
}

SugarWebServiceImplv4::$helperObject = new SugarWebServiceUtilv4();
