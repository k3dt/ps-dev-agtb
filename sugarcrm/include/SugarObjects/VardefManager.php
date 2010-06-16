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

  
class VardefManager{
	static $custom_disabled_modules = array();

	/**
	 * this method is called within a vardefs.php file which extends from a SugarObject.
	 * It is meant to load the vardefs from the SugarObject.
	 */
   static function createVardef($module,$object, $templates=array('default'), $object_name=false){
                        global $dictionary;

                        //reverse the sort order so priority goes highest to lowest;
                    $templates = array_reverse($templates);
                        foreach($templates as $template){
                                VardefManager::addTemplate($module,$object,$template, $object_name);
                        }
                        LanguageManager::createLanguageFile($module, $templates);

        if (isset(VardefManager::$custom_disabled_modules[$module])) {
                        $vardef_paths = array(
                                'custom/modules/'.$module.'/Ext/Vardefs/vardefs.ext.php',
                                'custom/Extension/modules/'.$module.'/Ext/Vardefs/vardefs.php'
                        );

                        //search a predefined set of locations for the vardef files
                        foreach($vardef_paths as $path){
                                if(file_exists($path)){
                                        require($path);
                                }
                        }
        }
    }
	
	/**
	 * Enables/Disables the loading of custom vardefs for a module.
	 * @param String $module Module to be enabled/disabled
	 * @param Boolean $enable true to enable, false to disable
	 * @return  null
	 */
	public static function setCustomAllowedForModule($module, $enable) {
		if ($enable && isset($custom_disabled_modules[$module])) {
		  	unset($custom_disabled_modules[$module]);
		} else if (!$enable) {
		  	$custom_disabled_modules[$module] = true;
		}
	}
	
	static function addTemplate($module, $object, $template, $object_name=false){
		if($template == 'default')$template = 'basic';
		$templates = array();
		$fields = array();
		if(empty($object_name))$object_name = $object;
		$_object_name = strtolower($object_name);
		if(!empty($GLOBALS['dictionary'][$object]['table'])){
			$table_name = $GLOBALS['dictionary'][$object]['table'];
		}else{
			$table_name = strtolower($module);
		}
		
		if(empty($templates[$template])){
			$path = 'include/SugarObjects/templates/' . $template . '/vardefs.php';
			if(file_exists($path)){
				require($path);
				$templates[$template] = $vardefs;
			}else{
				$path = 'include/SugarObjects/implements/' . $template . '/vardefs.php';
				if(file_exists($path)){
					require($path);
					$templates[$template] = $vardefs;
				}
			}
		}

		if(!empty($templates[$template])){
			if(empty($GLOBALS['dictionary'][$object]['fields']))$GLOBALS['dictionary'][$object]['fields'] = array();
			if(empty($GLOBALS['dictionary'][$object]['relationships']))$GLOBALS['dictionary'][$object]['relationships'] = array();
			if(empty($GLOBALS['dictionary'][$object]['indices']))$GLOBALS['dictionary'][$object]['indices'] = array();
			$GLOBALS['dictionary'][$object]['fields'] = array_merge($templates[$template]['fields'], $GLOBALS['dictionary'][$object]['fields']);
			if(!empty($templates[$template]['relationships']))$GLOBALS['dictionary'][$object]['relationships'] = array_merge($templates[$template]['relationships'], $GLOBALS['dictionary'][$object]['relationships']);
			if(!empty($templates[$template]['indices']))$GLOBALS['dictionary'][$object]['indices'] = array_merge($templates[$template]['indices'], $GLOBALS['dictionary'][$object]['indices']);
			if(!empty($templates[$template]['favorites']))$GLOBALS['dictionary'][$object]['favorites'] = $templates[$template]['favorites'];
			// maintain a record of this objects inheritance from the SugarObject templates...
            $GLOBALS['dictionary'][$object]['templates'][ $template ] = $template ;
		}
		
	}
	
	/**
	 * Save the dictionary object to the cache
	 * @param string $module the name of the module
	 * @param string $object the name of the object
	 */
	static function saveCache($module,$object, $additonal_objects= array()){
		
		$file = create_cache_directory('modules/' . $module . '/' . $object . 'vardefs.php');
		write_array_to_file('GLOBALS["dictionary"]["'. $object . '"]',$GLOBALS['dictionary'][$object], $file);
		include($file);
		
		// put the item in the sugar cache.
		$key = "VardefManager.$module.$object";
		$data = $GLOBALS['dictionary'][$object];
		sugar_cache_put($key,$data);
	}
	
	/**
	 * clear out the vardef cache. If we receive a module name then just clear the vardef cache for that module
	 * otherwise clear out the cache for every module
	 * @param string module_dir the module_dir to clear, if not specified then clear
	 *                      clear vardef cache for all modules.
	 * @param string object_name the name of the object we are clearing this is for sugar_cache
	 */
	static function clearVardef($module_dir = '', $object_name = ''){
		//if we have a module name specified then just remove that vardef file
		//otherwise go through each module and remove the vardefs.php
		if(!empty($module_dir) && !empty($object_name)){
			VardefManager::_clearCache($module_dir, $object_name);
		}else{
			global $beanList;
			foreach($beanList as $module_dir => $object_name){
				VardefManager::_clearCache($module_dir, $object_name);
			}
		}
	}
	
	/**
	 * PRIVATE function used within clearVardefCache so we do not repeat logic
	 * @param string module_dir the module_dir to clear
	 * @param string object_name the name of the object we are clearing this is for sugar_cache
	 */
	static function _clearCache($module_dir = '', $object_name = ''){
		if(!empty($module_dir) && !empty($object_name)){
			
			//BEGIN SUGARCRM flav!=sales ONLY
			if($object_name == 'aCase') {
				$object_name = 'Case';
			}
			//END SUGARCRM flav!=sales ONLY
			
			$file = $GLOBALS['sugar_config']['cache_dir'].'modules/'.$module_dir.'/' . $object_name . 'vardefs.php';
			if(file_exists($file)){
				unlink($file);
				$key = "VardefManager.$module_dir.$object_name";
				sugar_cache_clear($key);
			}
		}
	}
	
	/**
	 * Given a module, search all of the specified locations, and any others as specified
	 * in order to refresh the cache file
	 * 
	 * @param string $module the given module we want to load the vardefs for
	 * @param string $object the given object we wish to load the vardefs for
	 * @param array $additional_search_paths an array which allows a consumer to pass in additional vardef locations to search
	 */
	static function refreshVardefs($module, $object, $additional_search_paths = null, $cacheCustom = true){
		// Some of the vardefs do not correctly define dictionary as global.  Declare it first.
		global $dictionary;
		$vardef_paths = array(
					'modules/'.$module.'/vardefs.php',
					'custom/modules/'.$module.'/Ext/Vardefs/vardefs.ext.php',
					'custom/Extension/modules/'.$module.'/Ext/Vardefs/vardefs.php'
				 );

		// Add in additional search paths if they were provided.
		if(!empty($additional_search_paths) && is_array($additional_search_paths))
		{
			$vardef_paths = array_merge($vardef_paths, $additional_search_paths);
		}
		//search a predefined set of locations for the vardef files
		foreach($vardef_paths as $path){
			if(file_exists($path)){
				
				require($path);
			}
		}

		//load custom fields into the vardef cache
		if($cacheCustom){
			require_once("modules/DynamicFields/DynamicField.php");
			$df = new DynamicField ($module) ;
        	$df->buildCache($module);
		}
		
		//great! now that we have loaded all of our vardefs.
		//let's go save them to the cache file.
		if(!empty($GLOBALS['dictionary'][$object]))
			VardefManager::saveCache($module, $object);
	}
	
	/**
	 * load the vardefs for a given module and object
	 * @param string $module the given module we want to load the vardefs for
	 * @param string $object the given object we wish to load the vardefs for
	 * @param bool   $refresh whether or not we wish to refresh the cache file.
	 */
	static function loadVardef($module, $object, $refresh=false){
		//here check if the cache file exists, if it does then load it, if it doesn't
		//then call refreshVardef
		//if either our session or the system is set to developerMode then refresh is set to true
		if(!empty($GLOBALS['sugar_config']['developerMode']) || !empty($_SESSION['developerMode'])){
        	$refresh = true;
    	}
		// Retrieve the vardefs from cache.
		$key = "VardefManager.$module.$object";
		
		if(!$refresh)
		{
			$return_result = sugar_cache_retrieve($key);
			if(!empty($return_result))
			{
				$GLOBALS['dictionary'][$object] = $return_result;
				return;
			}
		}
		        
		// Some of the vardefs do not correctly define dictionary as global.  Declare it first.
		global $dictionary;
		if(empty($GLOBALS['dictionary'][$object]) || $refresh){
			//if the consumer has demanded a refresh or the cache/modules... file
			//does not exist, then we should do out and try to reload things
			if($refresh || !file_exists($GLOBALS['sugar_config']['cache_dir'].'modules/'. $module . '/' . $object . 'vardefs.php')){
				VardefManager::refreshVardefs($module, $object);
			}
			
			//at this point we should have the cache/modules/... file
			//which was created from the refreshVardefs so let's try to load it.
			if(file_exists($GLOBALS['sugar_config']['cache_dir'].'modules/'. $module .  '/' . $object . 'vardefs.php'))
			{
				include_once($GLOBALS['sugar_config']['cache_dir'].'modules/'. $module .  '/' . $object . 'vardefs.php');
				
				// now that we hae loaded the data from disk, put it in the cache.
				if(!empty($GLOBALS['dictionary'][$object]))
					sugar_cache_put($key,$GLOBALS['dictionary'][$object]);
			}
    		////BEGIN SUGARCRM flav=int ONLY
    		else{
    			display_notice('<B> MISSING FIELD_DEFS ' . 'modules/'. strtoupper($module) . '/vardefs.php for ' . $object . '</b><BR>');
    		}
    		//END SUGARCRM flav=int ONLY
		}
	}
}
