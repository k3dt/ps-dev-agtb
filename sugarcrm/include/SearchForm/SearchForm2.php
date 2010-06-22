<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*
 * Created on May 30, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once('include/tabs.php');
require_once('include/ListView/ListViewSmarty.php');

require_once('include/TemplateHandler/TemplateHandler.php');
require_once('include/EditView/EditView2.php');


 class SearchForm extends EditView{
 	var $seed = null;
 	var $module = '';
 	var $action = 'index';
 	var $searchdefs = array();
 	var $listViewDefs = array();
 	var $lv;
 	var $th;
    var $tpl;
    var $view = 'SearchForm';
    var $displayView = 'basic_search';
    var $formData;
    var $fieldDefs;
    var $customFieldDefs;
    var $tabs;
    var $parsedView = 'basic';
    //may remove
    var $searchFields;
    var $displaySavedSearch = true;
    //show the advanced tab
    var $showAdvanced = true;
    //show the basic tab
    var $showBasic = true;
    //array of custom tab to show declare in searchdefs (no custom tab if false)
    var $showCustom = false;
    // nb of tab to show
    var $nbTabs = 0;
    // hide saved searches drop and down near the search button
    var $showSavedSearchesOptions = true;

    var $displayType = 'searchView';

 	function SearchForm($seed, $module, $action = 'index'){
 		$this->th = new TemplateHandler();
 		$this->th->loadSmarty();
		$this->seed = $seed;
		$this->module = $module;
		$this->action = $action;
        $this->tabs = array(array('title'  => $GLOBALS['app_strings']['LNK_BASIC_SEARCH'],
                            'link'   => $module . '|basic_search',
                            'key'    => $module . '|basic_search',
                            'name'   => 'basic',
                            'displayDiv'   => ''),
                      array('title'  => $GLOBALS['app_strings']['LNK_ADVANCED_SEARCH'],
                            'link'   => $module . '|advanced_search',
                            'key'    => $module . '|advanced_search',
                            'name'   => 'advanced',
                            'displayDiv'   => 'display:none'),
                       );
        $this->searchColumns = array () ;
 	}

 	function setup($searchdefs, $searchFields = array(), $tpl, $displayView = 'basic_search', $listViewDefs = array()){
		$this->searchdefs =  $searchdefs[$this->module];
 		$this->tpl = $tpl;
 		//used by advanced search
 		$this->listViewDefs = $listViewDefs;
 		$this->displayView = $displayView;
 		$this->view = $this->view.'_'.$displayView;
 		$tokens = explode('_', $this->displayView);
 		$this->parsedView = $tokens[0];
 		if($this->displayView != 'saved_views'){
 			$this->_build_field_defs();
 		}

 		/*if(file_exists('modules/' . $this->module . '/metadata/SearchFields.php'))
 			require_once('modules/' . $this->module . '/metadata/SearchFields.php');
       	if(file_exists('custom/modules/' . $this->module . '/metadata/SearchFields.php'))
       		require_once('custom/modules/' . $this->module . '/metadata/SearchFields.php');
        */
        $this->searchFields = $searchFields[$this->module];

        // Setub the tab array
        $this->tabs = array();
        if($this->showBasic){
            $this->nbTabs++;
            $this->tabs[]=array('title'  => $GLOBALS['app_strings']['LNK_BASIC_SEARCH'],
                                'link'   => $this->module . '|basic_search',
                                'key'    => $this->module . '|basic_search',
                                'name'   => 'basic',
                                'displayDiv' => '');
        }
        if($this->showAdvanced){
            $this->nbTabs++;
            $this->tabs[]=array('title'  => $GLOBALS['app_strings']['LNK_ADVANCED_SEARCH'],
                                'link'   => $this->module . '|advanced_search',
                                'key'    => $this->module . '|advanced_search',
                                'name'   => 'advanced',
                                'displayDiv' => 'display:none');
        }
        if($this->showCustom){
            foreach($this->showCustom as $v){
                $this->nbTabs++;
                $this->tabs[]=array('title'  => $GLOBALS['app_strings']["LNK_" . strtoupper($v)],
                    'link'   => $this->module . '|' . $v,
                    'key'    => $this->module . '|' . $v,
                    'name'   => str_replace('_search','',$v),
                    'displayDiv' => 'display:none',);
            }
        }
 	}

 	function display($header = true){
    	global $theme, $timedate;
 		$header_txt = '';
 		$footer_txt = '';
 		$return_txt = '';
		$this->th->ss->assign('module', $this->module);
		$this->th->ss->assign('action', $this->action);
		//BEGIN SUGARCRM flav=pro ONLY
		ACLField::listFilter($this->fieldDefs, $this->module, $GLOBALS['current_user']->id, true, false, 1,false, true, '_'.$this->parsedView);
		//END SUGARCRM flav=pro ONLY
		$this->th->ss->assign('displayView', $this->displayView);
		$this->th->ss->assign('APP', $GLOBALS['app_strings']);
		//Show the tabs only if there is more than one
		if($this->nbTabs>1){
		    $this->th->ss->assign('TABS', $this->_displayTabs($this->module . '|' . $this->displayView));
		}
		$this->th->ss->assign('searchTableColumnCount',
		    ((isset($this->searchdefs['templateMeta']['maxColumns']) ? $this->searchdefs['templateMeta']['maxColumns'] : 2) * 2 ) - 1);
		$this->th->ss->assign('fields', $this->fieldDefs);
		$this->th->ss->assign('customFields', $this->customFieldDefs);
		$this->th->ss->assign('formData', $this->formData);
        $time_format = $timedate->get_user_time_format();
        $this->th->ss->assign('TIME_FORMAT', $time_format);
        $this->th->ss->assign('USER_DATEFORMAT', $timedate->get_user_date_format());

        $date_format = $timedate->get_cal_date_format();
        $time_separator = ":";
        if(preg_match('/\d+([^\d])\d+([^\d]*)/s', $time_format, $match)) {
           $time_separator = $match[1];
        }
        // Create Smarty variables for the Calendar picker widget
        $t23 = strpos($time_format, '23') !== false ? '%H' : '%I';
        if(!isset($match[2]) || $match[2] == '') {
          $this->th->ss->assign('CALENDAR_FORMAT', $date_format . ' ' . $t23 . $time_separator . "%M");
        } else {
          $pm = $match[2] == "pm" ? "%P" : "%p";
          $this->th->ss->assign('CALENDAR_FORMAT', $date_format . ' ' . $t23 . $time_separator . "%M" . $pm);
        }
        $this->th->ss->assign('TIME_SEPARATOR', $time_separator);

        //Show and hide the good tab form
        foreach($this->tabs as $tabkey=>$viewtab){
            $viewName=str_replace(array($this->module . '|','_search'),'',$viewtab['key']);
            if(strpos($this->view,$viewName)!==false){
                $this->tabs[$tabkey]['displayDiv']='';
                //if this is advanced tab, use form with saved search sub form built in
                if($viewName=='advanced'){
                    $this->tpl = 'include/SearchForm/tpls/SearchFormGenericAdvanced.tpl';
                    if ($this->action =='ListView') {
                        $this->th->ss->assign('DISPLAY_SEARCH_HELP', true);
                    }
                    $this->th->ss->assign('DISPLAY_SAVED_SEARCH', $this->displaySavedSearch);
                    $this->th->ss->assign('SAVED_SEARCH', $this->displaySavedSearch());
                    //this determines whether the saved search subform should be rendered open or not
                    if(isset($_REQUEST['showSSDIV']) && $_REQUEST['showSSDIV']=='yes'){
                        $this->th->ss->assign('SHOWSSDIV', 'yes');
                        $this->th->ss->assign('DISPLAYSS', '');
                    }else{
                        $this->th->ss->assign('SHOWSSDIV', 'no');
                        $this->th->ss->assign('DISPLAYSS', 'display:none');
                    }
                }
            }else{
                $this->tabs[$tabkey]['displayDiv']='display:none';
            }

        }

        $this->th->ss->assign('TAB_ARRAY', $this->tabs);

        $totalWidth = 0;
        if ( isset($this->searchdefs['templateMeta']['widths'])
                && isset($this->searchdefs['templateMeta']['maxColumns'])) {
            $totalWidth = ( $this->searchdefs['templateMeta']['widths']['label'] +
                                $this->searchdefs['templateMeta']['widths']['field'] ) *
                                $this->searchdefs['templateMeta']['maxColumns'];
            // redo the widths in case they are too big
            if ( $totalWidth > 100 ) {
                $resize = 100 / $totalWidth;
                $this->searchdefs['templateMeta']['widths']['label'] =
                    $this->searchdefs['templateMeta']['widths']['label'] * $resize;
                $this->searchdefs['templateMeta']['widths']['field'] =
                    $this->searchdefs['templateMeta']['widths']['field'] * $resize;
            }
        }
        $this->th->ss->assign('templateMeta', $this->searchdefs['templateMeta']);
        $this->th->ss->assign('HAS_ADVANCED_SEARCH', !empty($this->searchdefs['layout']['advanced_search']));
        $this->th->ss->assign('displayType', $this->displayType);
        // return the form of the shown tab only
        if($this->showSavedSearchesOptions){
            $this->th->ss->assign('SAVED_SEARCHES_OPTIONS', $this->displaySavedSearchSelect());
        }
        if ($this->module == 'Documents'){
            $this->th->ss->assign('DOCUMENTS_MODULE', true);
        }
        $return_txt = $this->th->displayTemplate($this->seed->module_dir, 'SearchForm_'.$this->parsedView, $this->tpl);
        if($header){
			$this->th->ss->assign('return_txt', $return_txt);
			$header_txt = $this->th->displayTemplate($this->seed->module_dir, 'SearchFormHeader', 'include/SearchForm/tpls/header.tpl');
            //pass in info to render the select dropdown below the form
            $footer_txt = $this->th->displayTemplate($this->seed->module_dir, 'SearchFormFooter', 'include/SearchForm/tpls/footer.tpl');
			$return_txt = $header_txt.$footer_txt;
		}
		return $return_txt;
 	}

  function displaySavedSearch(){
        $savedSearch = new SavedSearch($this->listViewDefs[$this->module], $this->lv->data['pageData']['ordering']['orderBy'], $this->lv->data['pageData']['ordering']['sortOrder']);
        return $savedSearch->getForm($this->module, false);
    }


  function displaySavedSearchSelect(){
        $savedSearch = new SavedSearch($this->listViewDefs[$this->module], $this->lv->data['pageData']['ordering']['orderBy'], $this->lv->data['pageData']['ordering']['sortOrder']);
        return $savedSearch->getSelect($this->module);
    }



 	/**
     * displays the tabs (top of the search form)
     *
     * @param string $currentKey key in $this->tabs to show as the current tab
     *
     * @return string html
     */
    function _displayTabs($currentKey)
    {
        if(isset($_REQUEST['saved_search_select']) && $_REQUEST['saved_search_select']!='_none') {
            $saved_search=loadBean('SavedSearch');
            $saved_search->retrieveSavedSearch($_REQUEST['saved_search_select']);
        }

        $str = '<script>';
        if(!empty($_REQUEST['displayColumns']))
            $str .= 'SUGAR.savedViews.displayColumns = "' . $_REQUEST['displayColumns'] . '";';
        elseif(isset($saved_search->contents['displayColumns']) && !empty($saved_search->contents['displayColumns']))
            $str .= 'SUGAR.savedViews.displayColumns = "' . $saved_search->contents['displayColumns'] . '";';
        if(!empty($_REQUEST['hideTabs']))
            $str .= 'SUGAR.savedViews.hideTabs = "' . $_REQUEST['hideTabs'] . '";';
        elseif(isset($saved_search->contents['hideTabs']) && !empty($saved_search->contents['hideTabs']))
            $str .= 'SUGAR.savedViews.hideTabs = "' . $saved_search->contents['hideTabs'] . '";';
        if(!empty($_REQUEST['orderBy']))
            $str .= 'SUGAR.savedViews.selectedOrderBy = "' . $_REQUEST['orderBy'] . '";';
        elseif(isset($saved_search->contents['orderBy']) && !empty($saved_search->contents['orderBy']))
            $str .= 'SUGAR.savedViews.selectedOrderBy = "' . $saved_search->contents['orderBy'] . '";';
        if(!empty($_REQUEST['sortOrder']))
            $str .= 'SUGAR.savedViews.selectedSortOrder = "' . $_REQUEST['sortOrder'] . '";';
        elseif(isset($saved_search->contents['sortOrder']) && !empty($saved_search->contents['sortOrder']))
            $str .= 'SUGAR.savedViews.selectedSortOrder = "' . $saved_search->contents['sortOrder'] . '";';

        $str .= '</script>';

        return $str;
    }

 	/*
	 * Generate the data
	 */
	function _build_field_defs(){
		$this->formData = array();
		$this->fieldDefs = array();
		foreach($this->searchdefs['layout'][$this->displayView] as $data){
			if(is_array($data)){
				$data['name'] = $data['name'].'_'.$this->parsedView;
				$this->formData[] = array('field' => $data);
				$this->fieldDefs[$data['name']]= $data;
			} else {
				$this->formData[] = array('field' => array('name'=>$data.'_'.$this->parsedView));
			}
		}

		if($this->seed){
			$this->seed->fill_in_additional_detail_fields();
			// hack to make the employee status field for the Users/Employees module display correctly
			if($this->seed->object_name == 'Employee' || $this->seed->object_name == 'User'){
                $this->seed->field_defs['employee_status']['type'] = 'enum';
                $this->seed->field_defs['employee_status']['massupdate'] = true;
                $this->seed->field_defs['employee_status']['options'] = 'employee_status_dom';
                unset($this->seed->field_defs['employee_status']['function']);
            }

	        foreach($this->seed->toArray() as $name => $value) {
	            if(!empty($this->fieldDefs[$name.'_'.$this->parsedView]))
	            	$this->fieldDefs[$name.'_'.$this->parsedView] = array_merge($this->seed->field_defs[$name], $this->fieldDefs[$name.'_'.$this->parsedView]);
	            else{
	            	$this->fieldDefs[$name.'_'.$this->parsedView] = $this->seed->field_defs[$name];
	            	$this->fieldDefs[$name.'_'.$this->parsedView]['name'] = $this->fieldDefs[$name.'_'.$this->parsedView]['name'].'_'.$this->parsedView;
	            }

	            if(isset($this->fieldDefs[$name.'_'.$this->parsedView]['type']) && $this->fieldDefs[$name.'_'.$this->parsedView]['type'] == 'relate') {
	                if(isset($this->fieldDefs[$name.'_'.$this->parsedView]['id_name'])) {
	                   $this->fieldDefs[$name.'_'.$this->parsedView]['id_name'] .= '_'.$this->parsedView;
	                }
	            }

	            if(isset($this->fieldDefs[$name.'_'.$this->parsedView]['options']) && isset($GLOBALS['app_list_strings'][$this->fieldDefs[$name.'_'.$this->parsedView]['options']])) {
	                $this->fieldDefs[$name.'_'.$this->parsedView]['options'] = $GLOBALS['app_list_strings'][$this->fieldDefs[$name.'_'.$this->parsedView]['options']]; // fill in enums
	            }

	            if(isset($this->fieldDefs[$name.'_'.$this->parsedView]['function'])) {

	            	$this->fieldDefs[$name.'_'.$this->parsedView]['type']='multienum';

	       	 		if(is_array($this->fieldDefs[$name.'_'.$this->parsedView]['function'])) {
	       	 		   $this->fieldDefs[$name.'_'.$this->parsedView]['function']['preserveFunctionValue']=true;
	       	 		}

	       	 		$function = $this->fieldDefs[$name.'_'.$this->parsedView]['function'];

	       			if(is_array($function) && isset($function['name'])){
	       				$function_name = $this->fieldDefs[$name.'_'.$this->parsedView]['function']['name'];
	       			}else{
	       				$function_name = $this->fieldDefs[$name.'_'.$this->parsedView]['function'];
	       			}

					if(!empty($this->fieldDefs[$name.'_'.$this->parsedView]['function']['returns']) && $this->fieldDefs[$name.'_'.$this->parsedView]['function']['returns'] == 'html'){
						if(!empty($this->fieldDefs[$name.'_'.$this->parsedView]['function']['include'])){
								require_once($this->fieldDefs[$name.'_'.$this->parsedView]['function']['include']);
						}
						$value = $function_name($this->seed, $name, $value, $this->view);
						$this->fieldDefs[$name.'_'.$this->parsedView]['value'] = $value;
					}else{
						if(!isset($function['params']) || !is_array($function['params'])) {
							$this->fieldDefs[$name.'_'.$this->parsedView]['options'] = $function_name($this->seed, $name, $value, $this->view);
						} else {
							$this->fieldDefs[$name.'_'.$this->parsedView]['options'] = call_user_func_array($function_name, $function['params']);
						}
					}
	       	 	}
	       	 	if(isset($this->fieldDefs[$name]['type']) && $this->fieldDefs[$name.'_'.$this->parsedView]['type'] == 'function' && isset($this->fieldDefs[$name.'_'.$this->parsedView]['function_name'])){
	       	 		$value = $this->callFunction($this->fieldDefs[$name.'_'.$this->parsedView]);
	       	 		$this->fieldDefs[$name.'_'.$this->parsedView]['value'] = $value;
	       	 	}

	            $this->fieldDefs[$name]['value'] = $value;


	            if((!empty($_REQUEST[$name.'_'.$this->parsedView]) || (isset($_REQUEST[$name.'_'.$this->parsedView]) && $_REQUEST[$name.'_'.$this->parsedView] == '0'))
                && empty($this->fieldDefs[$name.'_'.$this->parsedView]['function']['preserveFunctionValue'])) {
	            	$value = $_REQUEST[$name.'_'.$this->parsedView];
	            	$this->fieldDefs[$name.'_'.$this->parsedView]['value'] = $value;
	            }

	        } //foreach

	        //BEGIN SUGARCRM flav=pro ONLY
	        //CL: Override the custom_type so that we show a relate field in the basic search screen
	        if(isset($this->fieldDefs['team_name_basic'])) {
	           $this->fieldDefs['team_name_basic']['custom_type'] = 'relate';
	        }
            //END SUGARCRM flav=pro ONLY

		}

	}

	    /**
     * Populate the searchFields from an array
     *
     * @param array $array array to search through
     * @param string $switchVar variable to use in switch statement
     * @param bool $addAllBeanFields true to process at all bean fields
     */
    function populateFromArray(&$array, $switchVar = null, $addAllBeanFields = true) {

       if((!empty($array['searchFormTab']) || !empty($switchVar)) && !empty($this->searchFields)) {
			$arrayKeys = array_keys($array);
            $searchFieldsKeys = array_keys($this->searchFields);
            if(empty($switchVar)) $switchVar = $array['searchFormTab'];
            //name of  the search tab
            $SearchName=str_replace('_search', '', $switchVar);
            if($switchVar=='saved_views'){
                foreach($this->searchFields as $name => $params) {
                    foreach($this->tabs as $tabName){
                        if(!empty($array[$name . '_' . $tabName['name']])) {
                             $this->searchFields[$name]['value'] = $array[$name . '_' . $tabName['name']];
                             if(empty($this->fieldDefs[$name . '_' . $tabName['name']]['value'])) $this->fieldDefs[$name . '_' . $tabName['name']]['value'] = $array[$name . '_' . $tabName['name']];
                        }
                    }
                }
                if($addAllBeanFields) {
                    foreach($this->seed->field_name_map as $key => $params) {
                        if(!in_array($key, $searchFieldsKeys)) {
                            foreach($this->tabs->name as $tabName){
                                if(in_array($key . '_' . $tabName['name'], $arrayKeys) ) {
									$this->searchFields[$key] = array('query_type' => 'default',
                                                                      'value'      => $array[$key . '_' . $tabName['name']]);
                                }
                            }
                        }
                    }

                    //BEGIN SUGARCRM flav=pro ONLY
                    if(isset($array['team_name_advanced_new_on_update'])){
                    	require_once('include/SugarFields/SugarFieldHandler.php');
						$sfh = new SugarFieldHandler();
                    	$sf = $sfh->getSugarField('Teamset', true);
                    	$this->searchFields['team_set_id'] = $sf->getTeamSetIdSearchField('team_name_advanced');

                        if(isset($array["primary_team_name_advanced_collection"])) {
                    	   $this->searchFields['team_id'] = $sf->getTeamIdSearchField("primary_team_name_advanced_collection");
                	    }
                    }
                    //END SUGARCRM flav=pro ONLY
                }
            }else{

            	$fromMergeRecords = isset($array['merge_module']);

                foreach($this->searchFields as $name => $params) {
					$long_name = $name.'_'.$SearchName;           /*nsingh 21648: Add additional check for bool values=0. empty() considers 0 to be empty Only repopulates if value is 0 or 1:( */
                    if(isset($array[$long_name]) && ( $array[$long_name] !== '' || (isset($this->fieldDefs[$long_name]) && $this->fieldDefs[$long_name]['type'] == 'bool' && ($array[$long_name]=='0' || $array[$long_name]=='1'))) ) { //advanced*/
                        $this->searchFields[$name]['value'] = $array[$long_name];
                        if(empty($this->fieldDefs[$long_name]['value'])) $this->fieldDefs[$long_name]['value'] = $array[$long_name];
                    }else if(!empty($array[$name]) && !$fromMergeRecords) { //basic
                        $this->searchFields[$name]['value'] = $array[$name];
                        if(empty($this->fieldDefs[$long_name]['value'])) $this->fieldDefs[$long_name]['value'] = $array[$name];
                    }
                }
                if((empty($array['massupdate']) || $array['massupdate'] == 'false') && $addAllBeanFields) {
                    foreach($this->seed->field_name_map as $key => $params) {
                    	if(in_array($key.'_'.$SearchName, $arrayKeys) && !in_array($key, $searchFieldsKeys)) {
                        	$this->searchFields[$key] = array('query_type' => 'default',
                                                              'value'      => $array[$key.'_'.$SearchName]);


                    		if (!empty($params['type']) && $params['type'] == 'parent' && !empty($params['type_name']) && !empty($this->searchFields[$key]['value'])) {
                    			$this->searchFields[$params['type_name']] = array('query_type' => 'default',
                                                              					  'value'      => $array[$params['type_name']]);
                    		}
                        }
                    }


                    //BEGIN SUGARCRM flav=pro ONLY
                	if(isset($array['team_name_advanced_new_on_update'])){
                    	require_once('include/SugarFields/SugarFieldHandler.php');
						$sfh = new SugarFieldHandler();
                    	$sf = $sfh->getSugarField('Teamset', true);
                    	$teamSearchType = ( isset( $array['team_name_advanced_type'] ) && $array['team_name_advanced_type'] == 'exact' ) ? 'exact' : 'any';
                    	$this->searchFields['team_set_id'] = $sf->getTeamSetIdSearchField('team_name_advanced', $teamSearchType, array(), $array);
                    	if(isset($array["primary_team_name_advanced_collection"])) {
                    	   $this->searchFields['team_id'] = $sf->getTeamIdSearchField("primary_team_name_advanced_collection", 'any', array(), $array);
                	    }
                    }
                    //END SUGARCRM flav=pro ONLY
                }
            }
        }
    }

    /**
     * Populate the searchFields from $_REQUEST
     *
     * @param string $switchVar variable to use in switch statement
     * @param bool $addAllBeanFields true to process at all bean fields
     */
    function populateFromRequest($switchVar = null, $addAllBeanFields = true) {
    	$this->populateFromArray($_REQUEST, $switchVar, $addAllBeanFields);
    }

	function generateSearchWhere($add_custom_fields = false, $module='') {
        global $timedate;

        $this->searchColumns = array () ;

        $values = $this->searchFields;

        $where_clauses = array();
        $like_char = '%';
        $table_name = $this->seed->object_name;
        $this->seed->fill_in_additional_detail_fields();

        //rrs check for team_id
       //BEGIN SUGARCRM flav=pro ONLY
        if(!empty($this->searchFields['team_id']) && !empty($this->searchFields['team_id']['value'])){
        	if(!empty($this->searchFields['team_name'])){
        		unset($this->searchFields['team_name']);
        	}
        }
        //END SUGARCRM flav=pro ONLY

        foreach($this->searchFields as $field=>$parms) {
			$customField = false;
            // Jenny - Bug 7462: We need a type check here to avoid database errors
            // when searching for numeric fields. This is a temporary fix until we have
            // a generic search form validation mechanism.
            $type = (!empty($this->seed->field_name_map[$field]['type']))?$this->seed->field_name_map[$field]['type']:'';

        	if(!empty($this->seed->field_name_map[$field]['source'])
        		&& ($this->seed->field_name_map[$field]['source'] == 'custom_fields' ||
        			//Non-db custom fields, such as custom relates
        			($this->seed->field_name_map[$field]['source'] == 'non-db'
        			&& (!empty($this->seed->field_name_map[$field]['custom_module']) ||
        				 isset($this->seed->field_name_map[$field]['ext2']))))){
                $customField = true;
              }

            if ($type == 'int') {
                if (!empty($parms['value'])) {
                    $tempVal = explode(',', $parms['value']);
                    $newVal = '';
                    foreach($tempVal as $key => $val) {
                        if (!empty($newVal))
                            $newVal .= ',';
                        if(!empty($val) && !(is_numeric($val)))
                            $newVal .= -1;
                        else
                            $newVal .= $val;
                    }
                    $parms['value'] = $newVal;
                }
            }

            //Navjeet- 6/24/08 checkboxes have been changed to dropdowns, so we can query unchecked checkboxes! Bug: 21648.

            // elseif($type == 'bool' && empty($parms['value']) && preg_match("/current_user_only/", string subject, array subpatterns, int flags, [int offset])) {
            //     continue;
            // }
            //
            elseif($type == 'html' && $customField) {
                continue;
            }
            
            if(isset($parms['value']) && $parms['value'] != "") {

                $operator = 'like';
                if(!empty($parms['operator'])) {
                    $operator = $parms['operator'];
                }

                if(is_array($parms['value'])) {
                    $field_value = '';

                    // always construct the where clause for multiselects using the 'like' form to handle combinations of multiple $vals and multiple $parms
                     if(/*$GLOBALS['db']->dbType != 'mysql' &&*/ !empty($this->seed->field_name_map[$field]['isMultiSelect']) && $this->seed->field_name_map[$field]['isMultiSelect']) {
                        // construct the query for multenums
                        // use the 'like' query for all mssql and oracle examples as both custom and OOB multienums are implemented with types that cannot be used with an 'in'
                        $operator = 'custom_enum';
                        $table_name = $this->seed->table_name ;
                        if ($customField)
                            $table_name .= "_cstm" ;
                        $db_field = $table_name . "." . $field;

	                    foreach($parms['value'] as $key => $val) {
                            if($val != ' ' and $val != '') {
	                               $qVal = $GLOBALS['db']->quote($val);
	                               if (!empty($field_value)) {
	                                   $field_value .= ' or ';
	                               }
	                               $field_value .= "$db_field like '%^$qVal^%'";
	                        }
	                    }

                    } else {
                        $operator = $operator != 'subquery' ? 'in' : $operator;
	                    foreach($parms['value'] as $key => $val) {
	                        if($val != ' ' and $val != '') {
	                            if (!empty($field_value)) {
	                                $field_value .= ',';
	                            }
	                            $field_value .= "'" . $GLOBALS['db']->quote($val) . "'";
	                        }
	                    }
                    }

                }
                else {
                    $field_value = $GLOBALS['db']->quote($parms['value']);
                }

                //set db_fields array.
                if(!isset($parms['db_field'])) {
                    $parms['db_field'] = array($field);
                }

                if(isset($parms['my_items']) and $parms['my_items'] == true) {
                   if( $parms['value'] == false ) { //do not include where clause for custom fields with checkboxes that are unchecked

						continue;
					}
					else{ //my items is checked.
						global $current_user;
	                    $field_value = $GLOBALS['db']->quote($current_user->id);
						$operator = '=' ;
					}
//                    $operator = ($parms['value'] == '1') ? '=' : '!=';
                }

                $where = '';
                $itr = 0;
                
                if($field_value != '') {

                    $this->searchColumns [ strtoupper($field) ] = $field ;

                    foreach ($parms['db_field'] as $db_field) {
						if (strstr($db_field, '.') === false) {
                        	//Try to get the table for relate fields from link defs
                        	if ($type == 'relate' && !empty($this->seed->field_name_map[$field]['link'])
                        		&& !empty($this->seed->field_name_map[$field]['rname'])) {
                        			$link = $this->seed->field_name_map[$field]['link'];
                        			$relname = $link['relationship'];
                        			if (($this->seed->load_relationship($link))){
										//Martin fix #27494
										$db_field = $this->seed->field_name_map[$field]['name'];
                        			} else {
                        				//Best Guess for table name
                        				$db_field = strtolower($link['module']) . '.' . $db_field;
                        			}
                        	}
                        	else if ($type == 'parent') {
                        		if (!empty($this->searchFields['parent_type'])) {
                        			$parentType = $this->searchFields['parent_type'];
                        			$rel_module = $parentType['value'];
									global $beanFiles, $beanList;
	                        		if(!empty($beanFiles[$beanList[$rel_module]])) {
	    								require_once($beanFiles[$beanList[$rel_module]]);
									    $rel_seed = new $beanList[$rel_module]();
									    $db_field = 'parent_' . $rel_module . '_' . $rel_seed->table_name . '.name';
	                        		}
                        		}
                        	}
                        	// Relate fields in custom modules and custom relate fields
                        	else if ($type == 'relate' && $customField && !empty($this->seed->field_name_map[$field]['module'])) {
                        		$db_field = !empty($this->seed->field_name_map[$field]['name'])?$this->seed->field_name_map[$field]['name']:'name';
                        	}
                           else if(!$customField){
                               if ( !empty($this->seed->field_name_map[$field]['db_concat_fields']) )
                                   $db_field = db_concat($this->seed->table_name, $this->seed->field_name_map[$db_field]['db_concat_fields']);
                               else
                            	   $db_field = $this->seed->table_name .  "." . $db_field;
                        	}else{
                        		if ( !empty($this->seed->field_name_map[$field]['db_concat_fields']) )
                                   $db_field = db_concat($this->seed->table_name .  "_cstm.", $this->seed->field_name_map[$db_field]['db_concat_fields']);
                               else
                            	   $db_field = $this->seed->table_name .  "_cstm." . $db_field;
                        	}

                        }

                        if($type == 'date') {
                           // Collin - Have mysql as first because it's usually the case
                           // The regular expression check is to circumvent special case YYYY-MM
                           if($GLOBALS['db']->dbType == 'mysql') {
                                 if(preg_match('/^\d{4}.\d{1,2}$/', $field_value) == 0) {
                                    $field_value = $timedate->to_db_date($field_value, false);
                                    $operator = '=';
                                 } else {
                                    $operator = 'db_date';
                                 }
		                   //BEGIN SUGARCRM flav=ent ONLY
                           } else if($GLOBALS['db']->dbType == 'oci8') {
                            	 if(preg_match('/^\d{4}.\d{1,2}$/', $field_value) == 0) {
                                    $field_value = $timedate->to_db_date($field_value, false);
                                    $field_value = "to_date('" . $field_value . "', 'YYYY-MM-DD hh24:mi:ss')";
                            	 }
                                 $operator = 'db_date';
		                   //END SUGARCRM flav=ent ONLY
                           } else if($GLOBALS['db']->dbType == 'mssql') {
                                 if(preg_match('/^\d{4}.\d{1,2}$/', $field_value) == 0) {
                                    $field_value = "Convert(DateTime, '".$timedate->to_db_date($field_value, false)."')";
                                 }
                                 $operator = 'db_date';
                           } else {
                           	     $field_value = $timedate->to_db_date($field_value, false);
                           	     $operator = '=';
                           }
                        }

                        if($type == 'datetime' || $type == 'datetimecombo') {
                            $dates = $timedate->getDayStartEndGMT($field_value);
                            $field_value = $dates["start"] . "<>" . $dates["end"];
                            $operator = 'between';
                        }
                        if($type == 'decimal' || $type == 'float' || $type == 'currency') {
                            require_once('modules/Currencies/Currency.php');

                            $field_value = unformat_number($field_value);
                            if ( $type == 'currency' && stripos($field,'_usdollar')!==FALSE ) {
                                // It's a US Dollar field, we need to do some conversions from the user's local currency
                                $currency_id = $GLOBALS['current_user']->getPreference('currency');
                                if ( empty($currency_id) ) {
                                    $currency_id = -99;
                                }
                                if ( $currency_id != -99 ) {
                                    $currency = new Currency();
                                    $currency->retrieve($currency_id);
                                    $field_value = $currency->convertToDollar($field_value);
                                }
                            }

                            // Databases can't really search for floating point numbers, because they can't be accurately described in binary,
                            // So we have to fuzz out the match a little bit
                            $top = $field_value + 0.01;
                            $bottom = $field_value - 0.01;
                            $field_value = $bottom . "<>" . $top;
                            $operator = 'between';
                        }

                        //BEGIN SUGARCRM flav=pro ONLY
                        if ( preg_match("/favorites_only.*/", $field) ) {
                            if ( $field_value == '1' ) {
                                $field_value = $GLOBALS['current_user']->id;
                            }
                            else {
                                continue 2;
                            }
                        }
                        //END SUGARCRM flav=pro ONLY
                        
		                //BEGIN SUGARCRM flav=ent ONLY
                        if($GLOBALS['db']->dbType == 'oci8' && isset($parms['query_type']) && $parms['query_type'] == 'case_insensitive') {
                              $db_field = 'upper(' . $db_field . ")";
                              $field_value = strtoupper($field_value);
                        }
		                //END SUGARCRM flav=ent ONLY

                        $itr++;
                        if(!empty($where)) {
                            $where .= " OR ";
                        }

                        switch(strtolower($operator)) {
                        	case 'subquery':
                                $sq = $parms['subquery'];
                        		if(is_array($sq)){
                                    $and_or = ' AND ';
                                    if (isset($sq['OR'])){
                                        $and_or = ' OR ';
                                    }
                                    $first = true;
                                    foreach($sq as $q){
                                        if(empty($q) || strlen($q)<2) continue;
                                        if(!$first){
                                            $where .= $and_or;
                                        }
                                        $where .= " {$db_field} IN ({$q} '{$field_value}%') ";
                                        $first = false;
                                    }
                                }elseif(!empty($parms['query_type']) && $parms['query_type'] == 'format'){
                                	$where .= "{$db_field} IN (".string_format($parms['subquery'], array($field_value)).")";
                                }else{
                                  $where .= "{$db_field} IN ({$parms['subquery']} '{$field_value}%')";
                                }

    	                    	break;

                            case 'like':
                                if($type == 'bool' && $field_value == 0) {
                                    $where .=  $db_field . " = '0' OR " . $db_field . " IS NULL";
                                }
                                else {
                                	//check to see if this is coming from unified search or not
                                	$UnifiedSearch = !empty($parms['force_unifiedsearch']);
                                	if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'UnifiedSearch'){
                                		$UnifiedSearch = true;
                                	}
                                	//check to see if this is a universal search, AND the field name is "last_name"
									if($UnifiedSearch && strpos($db_field, 'last_name') !== false){
										//split the string value, and the db field name
										$string = explode(' ', $field_value);
										$column_name =  explode('.', $db_field);

										//when a search is done with a space, we concatenate and search against the full name.
										if(count($string)>1){
										    //add where clause agains concatenated fields
											$where .= $GLOBALS['db']->concat($column_name[0],array('first_name','last_name')) . " LIKE '{$field_value}%'";
										    $where .= ' OR ' . $GLOBALS['db']->concat($column_name[0],array('last_name','first_name')) . " LIKE '{$field_value}%'";
										}else{
											//no space was found, add normal where clause
											$where .=  $db_field . " like '".$field_value.$like_char."'";
										}

									}else{
										//field is not last name or this is not from global unified search, so do normal where clause
										$where .=  $db_field . " like '".$field_value.$like_char."'";
									}
                                }
                                break;
                            case 'in':
                                $where .=  $db_field . " in (".$field_value.')';
                                break;
                            case '=':
                                if($type == 'bool' && $field_value == 0) {
                                    $where .=  $db_field . " = '0' OR " . $db_field . " IS NULL";
                                }
                                else {
                                    $where .=  $db_field . " = '".$field_value ."'";
                                }
                                break;
                            case 'db_date':
                                if(preg_match('/^\d{4}.\d{1,2}$/', $field_value) == 0) {
                                  $where .=  $db_field . " = ". $field_value;
                                } else {
                                  // Create correct date_format conversion String
                                  if($GLOBALS['db']->dbType == 'oci8') {
                                  	$where .= db_convert($db_field,'date_format',array("'YYYY-MM'")) . " = '" . $field_value . "'";
                                  } else {
                                  	$where .= db_convert($db_field,'date_format',array("'%Y-%m'")) . " = '" . $field_value . "'";
                                  }
                                }
                                break;
                            // tyoung bug 15971 - need to add these special cases into the $where query
                            case 'custom_enum':
                            	$where .= $field_value;
                            	break;
                            case 'between':
                                $field_value = explode('<>', $field_value);
                                $where .= $db_field . " >= '".$field_value[0] . "' AND " .$db_field . " <= '".$field_value[1]."'";
                                break;
                            case 'innerjoin':
                                $this->seed->listview_inner_join[] = $parms['innerjoin'] . " '" . $parms['value'] . "%')";
                                break;
                        }
                    }
                }

                if(!empty($where)) {
                    if($itr > 1) {
                        array_push($where_clauses, '( '.$where.' )');
                    }
                    else {
                        array_push($where_clauses, $where);
                    }
                }
            }
        }
        
        return $where_clauses;
    }
 }

?>
