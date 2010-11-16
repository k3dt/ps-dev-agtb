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
    }
    
	function getDropDownDiv($tpl = 'modules/Home/UnifiedSearchAdvanced.tpl') {
		global $app_list_strings, $app_strings;

		if(!file_exists($GLOBALS['sugar_config']['cache_dir'].'modules/unified_search_modules.php'))
		$this->buildCache();
		include($GLOBALS['sugar_config']['cache_dir'].'modules/unified_search_modules.php');

		global $mod_strings, $modListHeader, $app_list_strings, $current_user, $app_strings, $beanList;
		$users_modules = $current_user->getPreference('globalSearch', 'search');

		if(!isset($users_modules)) { // preferences are empty, select all
			$users_modules = array();
			foreach($unified_search_modules as $module=>$data) {
				if ( !empty($data['default']) ) {
                    $users_modules[$module] = $beanList[$module];
                }
			}
			$current_user->setPreference('globalSearch', $users_modules, 0, 'search');
		}
		$sugar_smarty = new Sugar_Smarty();

		$modules_to_search = array();
		foreach($unified_search_modules as $module => $data) {
            if(ACLController::checkAccess($module, 'list', true)) {
                $modules_to_search[$module] = array('translated' => $app_list_strings['moduleList'][$module]);
                if(array_key_exists($module, $users_modules)) $modules_to_search[$module]['checked'] = true;
                else $modules_to_search[$module]['checked'] = false;
            }
		}

		if(!empty($this->query_string))
		{
			$sugar_smarty->assign('query_string', securexss($this->query_string));
		} else {
			$sugar_smarty->assign('query_string', '');
		}
		
		$sugar_smarty->assign('APP', $app_strings);
		$sugar_smarty->assign('USE_SEARCH_GIF', 0);
		$sugar_smarty->assign('LBL_SEARCH_BUTTON_LABEL', $app_strings['LBL_SEARCH_BUTTON_LABEL']);
		$sugar_smarty->assign('MODULES_TO_SEARCH', $modules_to_search);
		$sugar_smarty->debugging = true;

		return $sugar_smarty->fetch($tpl);
	}

	function search() {
		if(!file_exists($GLOBALS['sugar_config']['cache_dir'].'modules/unified_search_modules.php'))
			$this->buildCache();

		include $GLOBALS['sugar_config']['cache_dir'].'modules/unified_search_modules.php';
		require_once 'include/ListView/ListViewSmarty.php';
		

		global $modListHeader, $beanList, $beanFiles, $current_language, $app_strings, $current_user, $mod_strings;
		$home_mod_strings = return_module_language($current_language, 'Home');

		$overlib = true;
		$this->query_string = $GLOBALS['db']->quote(securexss(from_html(clean_string($this->query_string, 'UNIFIED_SEARCH'))));

		if(!empty($_REQUEST['advanced']) && $_REQUEST['advanced'] != 'false') {
			$modules_to_search = array();
			foreach($_REQUEST as $param => $value) {
				if(preg_match('/^search_mod_(.*)$/', $param, $match)) {
					$modules_to_search[$match[1]] = $beanList[$match[1]];
				}
			}
			$current_user->setPreference('globalSearch', $modules_to_search, 0, 'search'); // save selections to user preference
		    header('Location: index.php?module=Administration&action=index');
		} else {
			$users_modules = $current_user->getPreference('globalSearch', 'search');
			if(isset($users_modules)) { // use user's previous selections
			    foreach ( $users_modules as $key => $value ) {
			        if ( isset($unified_search_modules[$key]) ) {
			            $modules_to_search[$key] = $value;
			        }
			    }
			}
			else { // select all the modules (ie first time user has used global search)
				foreach($unified_search_modules as $module=>$data) {
				    if ( !empty($data['default']) ) {
				        $modules_to_search[$module] = $beanList[$module];
				    }
				}
			}
			$current_user->setPreference('globalSearch', $modules_to_search, 'search');
		}
		echo $this->getDropDownDiv('modules/Home/UnifiedSearchAdvancedForm.tpl');

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
                if(file_exists('custom/modules/'.$seed->module_dir.'/metadata/listviewdefs.php')){
                    require_once('custom/modules/'.$seed->module_dir.'/metadata/listviewdefs.php');
                }else{
                    require_once('modules/'.$seed->module_dir.'/metadata/listviewdefs.php');
                }
                if ( !isset($listViewDefs) || !isset($listViewDefs[$seed->module_dir]) )
                    continue;
                
			    $unifiedSearchFields = array () ;
                $innerJoins = array();
                foreach ( $unified_search_modules[ $moduleName ]['fields'] as $field=>$def )
                {
                    $listViewCheckField = strtoupper($field);
                    if ( empty($listViewDefs[$seed->module_dir][$listViewCheckField]['default']) ) {
                        // Bug 40032 - Add special case for field EMAIL; check for matching column 
                        //             EMAIL1 in the listviewdefs as an alternate column.
                        if ( $listViewCheckField == 'EMAIL' 
                                && !empty($listViewDefs[$seed->module_dir]['EMAIL1']['default']) ) {
                            // we've found the alternate matching column
                        } else {
                            continue;
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
                    $unifiedSearchFields[ $moduleName ] [ $field ] = $def ;
                    $unifiedSearchFields[ $moduleName ] [ $field ][ 'value' ] = $this->query_string ;
                }
                
                /*
                 * Use searchForm2->generateSearchWhere() to create the search query, as it can generate SQL for the full set of comparisons required
                 * generateSearchWhere() expects to find the search conditions for a field in the 'value' parameter of the searchFields entry for that field
                 */
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

                                    if (count($where_clauses) > 0 )
                                        $where = '(('. implode(' ) OR ( ', $where_clauses) . '))';

                $displayColumns = array();
                foreach($listViewDefs[$seed->module_dir] as $colName => $param) {
                    if(!empty($param['default']) && $param['default'] == true) {
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
                if($overlib) {
                    $lv->overlib = true;
                    $overlib = false;
                } else {
                	$lv->overlib = false;
                }
                
                $lv->setup($seed, 'include/ListView/ListViewGeneric.tpl', $where, $params, 0, 10);

                $module_results[$moduleName] = '<br /><br />' . get_form_header($GLOBALS['app_list_strings']['moduleList'][$seed->module_dir] . ' (' . $lv->data['pageData']['offsets']['total'] . ')', '', false);
                $module_counts[$moduleName] = $lv->data['pageData']['offsets']['total'];

                if($lv->data['pageData']['offsets']['total'] == 0) {
                    $module_results[$moduleName] .= '<h2>' . $home_mod_strings['LBL_NO_RESULTS_IN_MODULE'] . '</h2>';
                } else {
                    $has_results = true;
                    $module_results[$moduleName] .= $lv->display(false, false);
                }
			}
		}

		if($has_results) {
			arsort($module_counts);
			foreach($module_counts as $name=>$value) {
				echo $module_results[$name];
			}
		}
		else {
			echo '<br>';
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

			//BEGIN SUGARCRM flav!=sales ONLY
			if($beanName == 'aCase') $beanName = 'Case';
            //END SUGARCRM flav!=sales ONLY
			
			$manager = new VardefManager ( );
			$manager->loadVardef( $moduleName , $beanName ) ;

			// obtain the field definitions used by generateSearchWhere (duplicate code in view.list.php)
			if(file_exists('custom/modules/'.$moduleName.'/metadata/metafiles.php')){
                require('custom/modules/'.$moduleName.'/metadata/metafiles.php');	
            }elseif(file_exists('modules/'.$moduleName.'/metadata/metafiles.php')){
                require('modules/'.$moduleName.'/metadata/metafiles.php');
            }
 		
			
			if(!empty($metafiles[$moduleName]['searchfields']))
				require $metafiles[$moduleName]['searchfields'] ;
			elseif(file_exists("modules/{$moduleName}/metadata/SearchFields.php"))
				require "modules/{$moduleName}/metadata/SearchFields.php" ;

			if(!empty($dictionary[$beanName]['unified_search'])) // if bean participates in uf
			{

				$fields = array();
				foreach ( $dictionary [ $beanName ][ 'fields' ] as $field => $def )
				{
					// We cannot enable or disable unified_search for email in the vardefs as we don't actually have a vardef entry for 'email' -
					// the searchFields entry for 'email' doesn't correspond to any vardef entry. Instead it contains SQL to directly perform the search.
					// So as a proxy we allow any field in the vardefs that has a name starting with 'email...' to be tagged with the 'unified_search' parameter

					if (strpos($field,'email') !== false)
						$field = 'email' ;
						
					//bug: 38139 - allow phone to be searched through Global Search
					if (strpos($field,'phone') !== false)
						$field = 'phone' ;

					if ( !empty($def['unified_search']) && isset ( $searchFields [ $moduleName ] [ $field ]  ))
					{
						$fields [ $field ] = $searchFields [ $moduleName ] [ $field ] ;
					}
				}

				if(count($fields) > 0) {
					$supported_modules [$moduleName] ['fields'] = $fields;
					if ( isset($dictionary[$beanName]['unified_search_default_enabled']) && 
					        $dictionary[$beanName]['unified_search_default_enabled'] == FALSE ) {
                        $supported_modules [$moduleName]['default'] = false;
                    }
                    else {
                        $supported_modules [$moduleName]['default'] = true;
                    }
				}

			}

		}
		
		ksort($supported_modules);
		write_array_to_file('unified_search_modules', $supported_modules, $GLOBALS['sugar_config']['cache_dir'].'modules/unified_search_modules.php');

	}
}

?>