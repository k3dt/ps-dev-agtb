<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
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
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: UnifiedSearchAdvanced.php 56345 2010-05-10 21:19:37Z jenny $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/



class UnifiedSearchAdvanced {

    var $query_string = '';

    function __construct(){
        if(!empty($_REQUEST['query_string'])){
            $query_string = trim($_REQUEST['query_string']);
            if(!empty($query_string)){
                $this->query_string = $query_string;
            }
        }
        $this->cache_search = sugar_cached('modules/unified_search_modules.php');
        $this->cache_display = sugar_cached('modules/unified_search_modules_display.php');
    }

	function getDropDownDiv($tpl = 'modules/Home/UnifiedSearchAdvanced.tpl') {
		global $app_list_strings, $app_strings;

		if(!file_exists($this->cache_search))
		{
			$this->buildCache();
		}

		$unified_search_modules_display = $this->getUnifiedSearchModulesDisplay();

		global $mod_strings, $modListHeader, $app_list_strings, $current_user, $app_strings, $beanList;
		$users_modules = $current_user->getPreference('globalSearch', 'search');

		// preferences are empty, select all
		if(empty($users_modules)) {
			$users_modules = array();
			foreach($unified_search_modules_display as $module=>$data) {
				if (!empty($data['visible']) ) {
                    $users_modules[$module] = $beanList[$module];
                }
			}
			$current_user->setPreference('globalSearch', $users_modules, 0, 'search');
		}

		$sugar_smarty = new Sugar_Smarty();

		$modules_to_search = array();

		foreach($users_modules as $key=>$module)
		{
            if(ACLController::checkAccess($key, 'list', true))
            {
                $modules_to_search[$key]['checked'] = true;
            }
		}

		if(!empty($this->query_string))
		{
			$sugar_smarty->assign('query_string', securexss($this->query_string));
		} else {
			$sugar_smarty->assign('query_string', '');
		}

		$sugar_smarty->assign('MOD', return_module_language($GLOBALS['current_language'], 'Administration'));
		$sugar_smarty->assign('APP', $app_strings);
		$sugar_smarty->assign('USE_SEARCH_GIF', 0);
		$sugar_smarty->assign('LBL_SEARCH_BUTTON_LABEL', $app_strings['LBL_SEARCH_BUTTON_LABEL']);

		$json_enabled = array();
		$json_disabled = array();

		//Now add the rest of the modules that are searchable via Global Search settings
		foreach($unified_search_modules_display as $module=>$data)
		{
			if(!isset($modules_to_search[$module]) && $data['visible'] && ACLController::checkAccess($module, 'list', true))
			{
			   $modules_to_search[$module]['checked'] = false;
			} else if (isset($modules_to_search[$module]) && !$data['visible']) {
			   unset($modules_to_search[$module]);
			}
		}

		//Create the two lists (doing it this way preserves the user's ordering choice for enabled modules)
		foreach($modules_to_search as $module=>$data)
		{
			$label = isset($app_list_strings['moduleList'][$module]) ? $app_list_strings['moduleList'][$module] : $module;
			if(!empty($data['checked']))
			{
				$json_enabled[] = array("module" => $module, 'label' => $label);
			} else {
				$json_disabled[] = array("module" => $module, 'label' => $label);
			}
		}

		$sugar_smarty->assign('enabled_modules', json_encode($json_enabled));
		$sugar_smarty->assign('disabled_modules', json_encode($json_disabled));

		$showDiv = $current_user->getPreference('showGSDiv', 'search');
		if(!isset($showDiv))
		{
		   $showDiv = 'no';
		}

		$sugar_smarty->assign('SHOWGSDIV', $showDiv);
		$sugar_smarty->debugging = true;
		return $sugar_smarty->fetch($tpl);
	}


    /**
     * search
     *
     * Search function run when user goes to Show All and runs a search again.  This outputs the search results
     * calling upon the various listview display functions for each module searched on.
     * 
     * Todo: Sync this up with SugarSpot.php search method.
     *
     *
     */
	function search() {

        $unified_search_modules = $this->getUnifiedSearchModules();
		$unified_search_modules_display = $this->getUnifiedSearchModulesDisplay();


		require_once 'include/ListView/ListViewSmarty.php';

		global $modListHeader, $beanList, $beanFiles, $current_language, $app_strings, $current_user, $mod_strings;
		$home_mod_strings = return_module_language($current_language, 'Home');

		$this->query_string = $GLOBALS['db']->quote(securexss(from_html(clean_string($this->query_string, 'UNIFIED_SEARCH'))));

		if(!empty($_REQUEST['advanced']) && $_REQUEST['advanced'] != 'false') {
			$modules_to_search = array();
			if(!empty($_REQUEST['search_modules']))
			{
			    foreach(explode (',', $_REQUEST['search_modules'] ) as $e)
	            {
	                $modules_to_search[$e] = $beanList[$e];
	            }
			}

			$current_user->setPreference('showGSDiv', isset($_REQUEST['showGSDiv']) ? $_REQUEST['showGSDiv'] : 'no', 0, 'search');
			$current_user->setPreference('globalSearch', $modules_to_search, 0, 'search'); // save selections to user preference
		} else {
			$users_modules = $current_user->getPreference('globalSearch', 'search');
			$modules_to_search = array();

			if(!empty($users_modules)) {
				// use user's previous selections
			    foreach ( $users_modules as $key => $value ) {
			    	if (isset($unified_search_modules_display[$key]) && !empty($unified_search_modules_display[$key]['visible'])) {
		            	$modules_to_search[$key] = $beanList[$key];
		        	}
			    }
			} else {
				foreach($unified_search_modules_display as $module=>$data) {
				    if (!empty($data['visible']) ) {
				        $modules_to_search[$module] = $beanList[$module];
				    }
				}
			}
			$current_user->setPreference('globalSearch', $modules_to_search, 'search');
		}


		$templateFile = 'modules/Home/UnifiedSearchAdvancedForm.tpl';
		if(file_exists('custom/' . $templateFile))
		{
		   $templateFile = 'custom/'.$templateFile;
		}

		echo $this->getDropDownDiv($templateFile);

		$module_results = array();
		$module_counts = array();
		$has_results = false;

		if(!empty($this->query_string)) {
			foreach($modules_to_search as $moduleName => $beanName) {
                require_once $beanFiles[$beanName] ;
                $seed = new $beanName();

                $lv = new ListViewSmarty();
                $lv->lvd->additionalDetails = false;
                $mod_strings = return_module_language($current_language, $seed->module_dir);

                //retrieve the original list view defs and store for processing in case of custom layout changes
                require('modules/'.$seed->module_dir.'/metadata/listviewdefs.php');
				$orig_listViewDefs = $listViewDefs;

                if(file_exists('custom/modules/'.$seed->module_dir.'/metadata/listviewdefs.php'))
                {
                    require('custom/modules/'.$seed->module_dir.'/metadata/listviewdefs.php');
                }

                if ( !isset($listViewDefs) || !isset($listViewDefs[$seed->module_dir]) )
                {
                    continue;
                }

			    $unifiedSearchFields = array () ;
                $innerJoins = array();
                foreach ( $unified_search_modules[ $moduleName ]['fields'] as $field=>$def )
                {
                	$listViewCheckField = strtoupper($field);
                	//check to see if the field is in listview defs
					if ( empty($listViewDefs[$seed->module_dir][$listViewCheckField]['default']) ) {
						//check to see if field is in original list view defs (in case we are using custom layout defs)
						if (!empty($orig_listViewDefs[$seed->module_dir][$listViewCheckField]['default']) ) {
							//if we are here then the layout has been customized, but the field is still needed for query creation
							$listViewDefs[$seed->module_dir][$listViewCheckField] = $orig_listViewDefs[$seed->module_dir][$listViewCheckField];
						}

					}

                    //bug: 34125 we might want to try to use the LEFT JOIN operator instead of the INNER JOIN in the case we are
                    //joining against a field that has not been populated.
                    if(!empty($def['innerjoin']) )
                    {
                        if (empty($def['db_field']) )
                        {
                            continue;
                        }
                        $innerJoins[$field] = $def;
                        $def['innerjoin'] = str_replace('INNER', 'LEFT', $def['innerjoin']);
                    }

                    if(isset($seed->field_defs[$field]['type']))
                    {
                        $type = $seed->field_defs[$field]['type'];
                        if($type == 'int' && !is_numeric($this->query_string))
                        {
                            continue;
                        }
                    }

                    $unifiedSearchFields[ $moduleName ] [ $field ] = $def ;
                    $unifiedSearchFields[ $moduleName ] [ $field ][ 'value' ] = $this->query_string ;
                }

                /*
                 * Use searchForm2->generateSearchWhere() to create the search query, as it can generate SQL for the full set of comparisons required
                 * generateSearchWhere() expects to find the search conditions for a field in the 'value' parameter of the searchFields entry for that field
                 */
                require_once $beanFiles[$beanName] ;
                $seed = new $beanName();
				require_once 'include/SearchForm/SearchForm2.php' ;
                $searchForm = new SearchForm ( $seed, $moduleName ) ;

                $searchForm->setup (array ( $moduleName => array() ) , $unifiedSearchFields , '' , 'saved_views' /* hack to avoid setup doing further unwanted processing */ ) ;
                $where_clauses = $searchForm->generateSearchWhere() ;
                //add inner joins back into the where clause
                $params = array('custom_select' => "");
                foreach($innerJoins as $field=>$def) {
                    if (isset ($def['db_field'])) {
                      foreach($def['db_field'] as $dbfield)
                          $where_clauses[] = $dbfield . " LIKE '" . $this->query_string . "%'";
                          $params['custom_select'] .= ", $dbfield";
                          $params['distinct'] = true;
                          //$filterFields[$dbfield] = $dbfield;
                    }
                }

                if (count($where_clauses) > 0)
                {
                    $where = '(('. implode(' ) OR ( ', $where_clauses) . '))';
                }

                $displayColumns = array();
                foreach($listViewDefs[$seed->module_dir] as $colName => $param)
                {
                    if(!empty($param['default']) && $param['default'] == true)
                    {
                        $param['url_sort'] = true;//bug 27933
                        $displayColumns[$colName] = $param;
                    }
                }

                if(count($displayColumns) > 0)
                {
                	$lv->displayColumns = $displayColumns;
                } else {
                	$lv->displayColumns = $listViewDefs[$seed->module_dir];
                }

                $lv->export = false;
                $lv->mergeduplicates = false;
                $lv->multiSelect = false;
                $lv->delete = false;
                $lv->select = false;
                $lv->showMassupdateFields = false;
                $lv->email = false;

                $lv->setup($seed, 'include/ListView/ListViewNoMassUpdate.tpl', $where, $params, 0, 10);

                $module_results[$moduleName] = '<br /><br />' . get_form_header($GLOBALS['app_list_strings']['moduleList'][$seed->module_dir] . ' (' . $lv->data['pageData']['offsets']['total'] . ')', '', false);
                $module_counts[$moduleName] = $lv->data['pageData']['offsets']['total'];

                if($lv->data['pageData']['offsets']['total'] == 0) {
                    //$module_results[$moduleName] .= "<li class='noBullet' id='whole_subpanel_{$moduleName}'><div id='div_{$moduleName}'><h2>" . $home_mod_strings['LBL_NO_RESULTS_IN_MODULE'] . '</h2></div></li>';
                    $module_results[$moduleName] .= '<h2>' . $home_mod_strings['LBL_NO_RESULTS_IN_MODULE'] . '</h2>';
                } else {
                    $has_results = true;
                    //$module_results[$moduleName] .= "<li class='noBullet' id='whole_subpanel_{$moduleName}'><div id='div_{$moduleName}'>" . $lv->display(false, false) . '</div></li>';
                    $module_results[$moduleName] .= $lv->display(false, false);
                }

			}
		}

		if($has_results) {
			foreach($module_counts as $name=>$value) {
				echo $module_results[$name];
			}
		} else if(empty($_REQUEST['form_only'])) {
			echo $home_mod_strings['LBL_NO_RESULTS'];
			echo $home_mod_strings['LBL_NO_RESULTS_TIPS'];
		}

	}

	function buildCache()
	{

		global $beanList, $beanFiles, $dictionary;

		$supported_modules = array();

		foreach($beanList as $moduleName=>$beanName)
		{
			if (!isset($beanFiles[$beanName]))
				continue;

			$beanName = BeanFactory::getObjectName($moduleName);
			$manager = new VardefManager ( );
			$manager->loadVardef( $moduleName , $beanName ) ;

			// obtain the field definitions used by generateSearchWhere (duplicate code in view.list.php)
			if(file_exists('custom/modules/'.$moduleName.'/metadata/metafiles.php')){
                require('custom/modules/'.$moduleName.'/metadata/metafiles.php');
            }elseif(file_exists('modules/'.$moduleName.'/metadata/metafiles.php')){
                require('modules/'.$moduleName.'/metadata/metafiles.php');
            }


			if(!empty($metafiles[$moduleName]['searchfields']))
			{
				require $metafiles[$moduleName]['searchfields'] ;
			} else if(file_exists("modules/{$moduleName}/metadata/SearchFields.php")) {
				require "modules/{$moduleName}/metadata/SearchFields.php" ;
			}

			//Load custom SearchFields.php if it exists
			if(file_exists("custom/modules/{$moduleName}/metadata/SearchFields.php"))
			{
				require "custom/modules/{$moduleName}/metadata/SearchFields.php" ;
			}				

            //If there are $searchFields are empty, just continue, there are no search fields defined for the module
            if(empty($searchFields[$moduleName]))
            {
                continue;
            }

			$isCustomModule = preg_match('/^([a-z0-9]{1,5})_([a-z0-9_]+)$/i' , $moduleName);

			//If the bean supports unified search or if it's a custom module bean and unified search is not defined
			if(!empty($dictionary[$beanName]['unified_search']) || $isCustomModule)
			{
				$fields = array();
				foreach ( $dictionary [ $beanName ][ 'fields' ] as $field => $def )
				{
					// We cannot enable or disable unified_search for email in the vardefs as we don't actually have a vardef entry for 'email'
					// the searchFields entry for 'email' doesn't correspond to any vardef entry. Instead it contains SQL to directly perform the search.
					// So as a proxy we allow any field in the vardefs that has a name starting with 'email...' to be tagged with the 'unified_search' parameter

					if (strpos($field,'email') !== false)
					{
						$field = 'email' ;
					}

					//bug: 38139 - allow phone to be searched through Global Search
					if (strpos($field,'phone') !== false)
					{
						$field = 'phone' ;
					}

					if ( !empty($def['unified_search']) && isset ( $searchFields [ $moduleName ] [ $field ]  ))
					{
						$fields [ $field ] = $searchFields [ $moduleName ] [ $field ] ;
					}
				}

                foreach ($searchFields[$moduleName] as $field => $def)
                {
                    if (
                        isset($def['force_unifiedsearch'])
                        and $def['force_unifiedsearch']
                    )
                    {
                        $fields[$field] = $def;
                    }
                }

				if(count($fields) > 0) {
					$supported_modules [$moduleName] ['fields'] = $fields;
					if (isset($dictionary[$beanName]['unified_search_default_enabled']) && $dictionary[$beanName]['unified_search_default_enabled'] === TRUE)
					{
                        $supported_modules [$moduleName]['default'] = true;
                    } else {
                        $supported_modules [$moduleName]['default'] = false;
                    }
				}

			}

		}

		ksort($supported_modules);
		write_array_to_file('unified_search_modules', $supported_modules, $this->cache_search);
	}


	/**
	 *
	 */
	function modifyGlobalSearchSettings()
	{
		global $mod_strings, $app_strings, $app_list_strings;

        $unified_search_modules_display = $this->getUnifiedSearchModulesDisplay();

		$sugar_smarty = new Sugar_Smarty();
		$sugar_smarty->assign('APP', $app_strings);
		$sugar_smarty->assign('MOD', $mod_strings);

		//Add the translated attribute for display label
		$json_enabled = array();
		$json_disabled = array();
		foreach($unified_search_modules_display as $module=>$data)
		{
			$label = isset($app_list_strings['moduleList'][$module]) ? $app_list_strings['moduleList'][$module] : $module;
			if($data['visible'] === true)
			{
			   $json_enabled[] = array("module" => $module, 'label' => $label);
			} else {
			   $json_disabled[] = array("module" => $module, 'label' => $label);
			}
		}

		//If the file doesn't exist
		if(!file_exists($this->cache_search))
		{
			$this->buildCache();
		}

		include($this->cache_search);

		//Now add any new modules that may have since been added to unified_search_modules.php
		foreach($unified_search_modules as $module=>$data)
		{
			if(!isset($unified_search_modules_display[$module]))
			{
			    $label = isset($app_list_strings['moduleList'][$module]) ? $app_list_strings['moduleList'][$module] : $module;
				if($data['default'])
				{
				  $json_enabled[] = array("module" => $module, 'label' => $label);
				} else {
				  $json_disabled[] = array("module" => $module, 'label' => $label);
				}
			}
		}

		$sugar_smarty->assign('enabled_modules', json_encode($json_enabled));
		$sugar_smarty->assign('disabled_modules', json_encode($json_disabled));

		//uasort($unified_search_modules_display, 'unified_search_modules_cmp');
		$tpl = 'modules/Administration/templates/GlobalSearchSettings.tpl';
		if(file_exists('custom/' . $tpl))
		{
		   $tpl = 'custom/' . $tpl;
		}
		return $sugar_smarty->fetch($tpl);
	}


	/**
	 * saveGlobalSearchSettings
	 * This method handles the administrator's request to save the searchable modules selected and stores
	 * the results in the unified_search_modules_display.php file
	 *
	 */
	function saveGlobalSearchSettings()
	{
		if(isset($_REQUEST['enabled_modules']))
		{
            $unified_search_modules_display = $this->getUnifiedSearchModulesDisplay();

			$new_unified_search_modules_display = array();

            foreach(explode (',', $_REQUEST['enabled_modules'] ) as $module)
            {
                $new_unified_search_modules_display[$module]['visible'] = true;
            }

			foreach($unified_search_modules_display as $module=>$data)
			{
				if(!isset($new_unified_search_modules_display[$module]))
				{
				   $new_unified_search_modules_display[$module]['visible'] = false;
				}
			}

			$this->writeUnifiedSearchModulesDisplayFile($new_unified_search_modules_display);
		}
	}


	public static function unlinkUnifiedSearchModulesFile() {
		//clear the unified_search_module.php file
		$cache_search = sugar_cached('modules/unified_search_modules.php');
    	if(file_exists($cache_search))
    	{
    		$GLOBALS['log']->info("unlink {$cache_search}");
    		unlink($cache_search);
    	}
	}
    

    /**
     * getUnifiedSearchModules
     *
     * Returns the value of the $unified_search_modules variable based on the module's vardefs.php file
     * and which fields are marked with the unified_search attribute.
     *
     * @return $unified_search_modules Array of metadata module definitions along with their fields
     */
    public function getUnifiedSearchModules()
    {
		//Make directory if it doesn't exist
        $cachedir = sugar_cached('modules');
		if(!file_exists($cachedir))
		{
		   mkdir_recursive($cachedir);
		}

		//Load unified_search_modules.php file
        $cachedFile = sugar_cached('modules/unified_search_modules.php');
		if(!file_exists($cachedFile))
		{
			$this->buildCache();
		}

		include $cachedFile;
        return $unified_search_modules;
    }


    /**
     * getUnifiedSearchModulesDisplay
     *
     * Returns the value of the $unified_search_modules_display variable which is based on the $unified_search_modules
     * entries that have been selected to be allowed for searching.
     *
     * @return $unified_search_modules_display Array value of modules that have enabled for searching
     */
    public function getUnifiedSearchModulesDisplay()
    {
		if(!file_exists('custom/modules/unified_search_modules_display.php'))
		{
            $unified_search_modules = $this->getUnifiedSearchModules();

            $unified_search_modules_display = array();

            if(!empty($unified_search_modules))
            {
                foreach($unified_search_modules as $module=>$data)
                {
                    $unified_search_modules_display[$module]['visible'] = (isset($data['default']) && $data['default']) ? true : false;
                }
            }

            $this->writeUnifiedSearchModulesDisplayFile($unified_search_modules_display);
		}

		include('custom/modules/unified_search_modules_display.php');
        return $unified_search_modules_display;
    }

	/*
	 * writeUnifiedSearchModulesDisplayFile
	 * Private method to handle writing the unified_search_modules_display value to file
	 *
	 * @param mixed The array of the unified search modules and their display attributes
	 * @return boolean value indication whether or not file was successfully written
	 * @throws Exception Thrown if the file write operation fails
	 */
	private function writeUnifiedSearchModulesDisplayFile($unified_search_modules_display)
	{
		if(is_null($unified_search_modules_display) || empty($unified_search_modules_display))
		{
		   return false;
		}

	    if(!write_array_to_file("unified_search_modules_display", $unified_search_modules_display, 'custom/modules/unified_search_modules_display.php'))
	    {
	    	//Log error message and throw Exception
	    	global $app_strings;
	    	$msg = string_format($app_strings['ERR_FILE_WRITE'], array('custom/modules/unified_search_modules_display.php'));
	    	$GLOBALS['log']->error($msg);
	    	throw new Exception($msg);
	    }

	    return true;
	}
}


function unified_search_modules_cmp($a, $b) {
	if(!isset($a['translated']) || !isset($b['translated']))
	{
	   return 0;
	}

	$name1 = strtolower($a['translated']);
	$name2 = strtolower($b['translated']);

	return $name1 < $name2 ? -1 : 1;
}

?>