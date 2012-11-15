<?php
if(!defined('sugarEntry'))define('sugarEntry', true);
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('soap/SoapHelperFunctions.php');
require_once 'modules/ModuleBuilder/parsers/MetaDataFiles.php';
require_once 'include/SugarFields/SugarFieldHandler.php';

/**
 * This class is for access metadata for all sugarcrm modules in a read only
 * state.  This means that you can not modifiy any of the metadata using this
 * class currently.
 *
 *
 * @method Array getData getData() gets all meta data.
 *
 *
 *  "platform": is a bool value which lets you know if the data is for a mobile view, portal or not.
 *
 */
class MetaDataManager {
    /**
     * SugarFieldHandler, to assist with cleansing default sugar field values
     * 
     * @var SugarFieldHandler
     */
    protected $sfh;
    
    /**
     * The user bean for the logged in user
     *
     * @var User
     */
    protected $user;

    /**
     * The constructor for the class.
     *
     * @param User $user A User bean
     * @param array $platforms A list of clients
     * @param bool $public is this a public metadata grab
     */
    function __construct ($user, $platforms = null, $public = false) {
        if ( $platforms == null ) {
            $platforms = array('base');
        }

        $this->user = $user;
        $this->platforms = $platforms;

    }

    /**
     * For a specific module get any existing Subpanel Definitions it may have
     * @param string $moduleName
     * @return array
     */
    public function getSubpanelDefs($moduleName)
    {
        require_once('include/SubPanel/SubPanelDefinitions.php');
        $parent_bean = BeanFactory::getBean($moduleName);
        //Hack to allow the SubPanelDefinitions class to check the correct module dir
        if (!$parent_bean){
            $parent_bean = (object) array('module_dir' => $moduleName);
        }

        $spd = new SubPanelDefinitions($parent_bean);
        $layout_defs = $spd->layout_defs;

        if(is_array($layout_defs) && isset($layout_defs['subpanel_setup']))
        {
            foreach($layout_defs['subpanel_setup'] AS $name => $subpanel_info)
            {
                $aSubPanel = $spd->load_subpanel($name, '', $parent_bean);

                if(!$aSubPanel)
                {
                    continue;
                }

                if($aSubPanel->isCollection())
                {
                    $collection = array();
                    foreach($aSubPanel->sub_subpanels AS $key => $subpanel)
                    {
                        $collection[$key] = $subpanel->panel_definition;
                    }
                    $layout_defs['subpanel_setup'][$name]['panel_definition'] = $collection;
                }
                else
                {
                    $layout_defs['subpanel_setup'][$name]['panel_definition'] = $aSubPanel->panel_definition;
                }

            }
        }

        return $layout_defs;
    }

    /**
     * This method collects all view data for a module
     *
     * @param $moduleName The name of the sugar module to collect info about.
     *
     * @return Array A hash of all of the view data.
     */
    public function getModuleViews($moduleName) {
        return $this->getClientFiles('view',$moduleName);
    }

    /**
     * This method collects all view data for a module
     *
     * @param $moduleName The name of the sugar module to collect info about.
     *
     * @return Array A hash of all of the view data.
     */
    public function getModuleLayouts($moduleName) {
        return $this->getClientFiles('layout', $moduleName);
    }

    /**
     * This method collects all field data for a module
     *
     * @param string $moduleName    The name of the sugar module to collect info about.
     *
     * @return Array A hash of all of the view data.
     */
    public function getModuleFields($moduleName) {
        return $this->getClientFiles('field', $moduleName);
    }

    /**
     * The collector method for modules.  Gets metadata for all of the module specific data
     *
     * @param $moduleName The name of the module to collect metadata about.
     * @return array An array of hashes containing the metadata.  Empty arrays are
     * returned in the case of no metadata.
     */
    public function getModuleData($moduleName) {
        //BEGIN SUGARCRM flav=pro ONLY
        require_once('include/SugarSearchEngine/SugarSearchEngineMetadataHelper.php');
        //END SUGARCRM flav=pro ONLY
        $vardefs = $this->getVarDef($moduleName);

        $data['fields'] = isset($vardefs['fields']) ? $vardefs['fields'] : array();
        $data['views'] = $this->getModuleViews($moduleName);
        $data['layouts'] = $this->getModuleLayouts($moduleName);
        $data['fieldTemplates'] = $this->getModuleFields($moduleName);
        $data['subpanels'] = $this->getSubpanelDefs($moduleName);
        $data['config'] = $this->getModuleConfig($moduleName);

        //BEGIN SUGARCRM flav=pro ONLY
        $data['ftsEnabled'] = SugarSearchEngineMetadataHelper::isModuleFtsEnabled($moduleName);
        //END SUGARCRM flav=pro ONLY

        $seed = BeanFactory::newBean($moduleName);

        //BEGIN SUGARCRM flav=pro ONLY
        if ($seed !== false) {
            $favoritesEnabled = ($seed->isFavoritesEnabled() !== false) ? true : false;
            $data['favoritesEnabled'] = $favoritesEnabled;
        }
        //END SUGARCRM flav=pro ONLY

        $data["_hash"] = md5(serialize($data));

        return $data;
    }

    /**
     * Get the config for a specific module from the Administration Layer
     *
     * @param string $moduleName        The Module we want the data back for.
     * @return array
     */
    public function getModuleConfig($moduleName) {
        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');
        return $admin->getConfigForModule($moduleName, $this->platforms[0]);
    }

    /**
     * The collector method for relationships.
     *
     * @return array An array of relationships, indexed by the relationship name
     */
    public function getRelationshipData() {
        require_once('data/Relationships/RelationshipFactory.php');
        $relFactory = SugarRelationshipFactory::getInstance();

        $data = $relFactory->getRelationshipDefs();
        foreach ( $data as $relKey => $relData ) {
            unset($data[$relKey]['table']);
            unset($data[$relKey]['fields']);
            unset($data[$relKey]['indices']);
            unset($data[$relKey]['relationships']);
        }

        $data["_hash"] = md5(serialize($data));

        return $data;
    }

    /**
     * Gets vardef info for a given module.
     *
     * @param $moduleName The name of the module to collect vardef information about.
     * @return array The vardef's $dictonary array.
     */
    public function getVarDef($moduleName) {

        require_once("data/BeanFactory.php");
        $obj = BeanFactory::getObjectName($moduleName);

        require_once("include/SugarObjects/VardefManager.php");
        global $dictionary;
        VardefManager::loadVardef($moduleName, $obj);
        if ( isset($dictionary[$obj]) ) {
            $data = $dictionary[$obj];
        }

        // vardefs are missing something, for consistancy let's populate some arrays
        if (!isset($data['fields']) ) {
            $data['fields'] = array();
        }
        
        // Bug 56505 - multiselect fields default value wrapped in '^' character
        $data['fields'] = $this->normalizeFielddefs($data['fields']);
        
        if (!isset($data['relationships'])) {
            $data['relationships'] = array();
        }

        // loop over the fields to find if they can be sortable
        // get the indexes on the module and the first field of each index
        $indexes = array();
        if(isset($data['indices'])) {
            foreach($data['indices'] AS $index) {
                if(isset($index['fields'][0]))
                {
                    $indexes[$index['fields'][0]] = $index['fields'][0];
                }
            }
        }

        // If sortable isn't already set THEN
        //      Set it sortable to TRUE, if the field is indexed.
        //      Set sortable to FALSE, otherwise. (Bug56943, Bug57644)
        $isIndexed = !empty($indexes);
        foreach($data['fields'] AS $field_name => $info) {
            if(!isset($data['fields'][$field_name]['sortable'])){
                $data['fields'][$field_name]['sortable'] = false;
                if($isIndexed && isset($indexes[$field_name])) {
                    $data['fields'][$field_name]['sortable'] = true;
                }
            }
        }

        return $data;
    }

    /**
     * Gets the ACL's for the module, will also expand them so the client side of the ACL's don't have to do as many checks.
     *
     * @param string $module The module we want to fetch the ACL for
     * @param string $userId The user id for the ACL's we are retrieving.
     * @return array Array of ACL's, first the action ACL's (access, create, edit, delete) then an array of the field level acl's
     */
    public function getAclForModule($module,$userId) {
        $aclAction = new ACLAction();
        //BEGIN SUGARCRM flav=pro ONLY
        $aclField = new ACLField();
        //END SUGARCRM flav=pro ONLY
        $acls = $aclAction->getUserActions($userId);
        $userObject = BeanFactory::getBean('Users',$userId);
        $obj = BeanFactory::getObjectName($module);

        $outputAcl = array('fields'=>array());
        if ( is_admin($userObject) ) {
            foreach ( array('admin','developer','access','view','list','edit','delete','import','export','massupdate') as $action ) {
                $outputAcl[$action] = 'yes';
            }
        } else if ( isset($acls[$module]['module']) ) {
            $moduleAcl = $acls[$module]['module'];

            if ( isset($moduleAcl['admin']) && isset($moduleAcl['admin']['aclaccess']) && (($moduleAcl['admin']['aclaccess'] == ACL_ALLOW_ADMIN) || ($moduleAcl['admin']['aclaccess'] == ACL_ALLOW_ADMIN_DEV)) ) {
                $outputAcl['admin'] = 'yes';
                $isAdmin = true;
            } else {
                $outputAcl['admin'] = 'no';
                $isAdmin = false;
            }

            if ( isset($moduleAcl['admin']) && isset($moduleAcl['admin']['aclaccess']) && (($moduleAcl['admin']['aclaccess'] == ACL_ALLOW_DEV) || ($moduleAcl['admin']['aclaccess'] == ACL_ALLOW_ADMIN_DEV)) ) {
                $outputAcl['developer'] = 'yes';
            } else {
                $outputAcl['developer'] = 'no';
            }

            if ( ($moduleAcl['access']['aclaccess'] == ACL_ALLOW_ENABLED) || $isAdmin ) {
                $outputAcl['access'] = 'yes';
            } else {
                $outputAcl['access'] = 'no';
            }

            // Only loop through the fields if we have a reason to, admins give full access on everything, no access gives no access to anything
            if ( $outputAcl['access'] == 'yes' && $outputAcl['developer'] == 'no' ) {

                foreach ( array('view','list','edit','delete','import','export','massupdate') as $action ) {
                    if ( $moduleAcl[$action]['aclaccess'] == ACL_ALLOW_ALL ) {
                        $outputAcl[$action] = 'yes';
                    } else if ( $moduleAcl[$action]['aclaccess'] == ACL_ALLOW_OWNER ) {
                        $outputAcl[$action] = 'owner';
                    } else {
                        $outputAcl[$action] = 'no';
                    }
                }

                // Currently create just uses the edit permission, but there is probably a need for a separate permission for create
                $outputAcl['create'] = $outputAcl['edit'];

                // Now time to dig through the fields
                $fieldsAcl = array();
                //BEGIN SUGARCRM flav=pro ONLY
                $fieldsAcl = $aclField->loadUserFields($module,$obj,$userId,true);
                //END SUGARCRM flav=pro ONLY
                foreach ( $fieldsAcl as $field => $fieldAcl ) {
                    switch ( $fieldAcl ) {
                        case ACL_READ_WRITE:
                            // Default, don't need to send anything down
                            break;
                        case ACL_READ_OWNER_WRITE:
                            // $outputAcl['fields'][$field]['read'] = 'yes';
                            $outputAcl['fields'][$field]['write'] = 'owner';
                            $outputAcl['fields'][$field]['create'] = 'owner';
                            break;
                        case ACL_READ_ONLY:
                            // $outputAcl['fields'][$field]['read'] = 'yes';
                            $outputAcl['fields'][$field]['write'] = 'no';
                            $outputAcl['fields'][$field]['create'] = 'no';
                            break;
                        case ACL_OWNER_READ_WRITE:
                            $outputAcl['fields'][$field]['read'] = 'owner';
                            $outputAcl['fields'][$field]['write'] = 'owner';
                            $outputAcl['fields'][$field]['create'] = 'owner';
                            break;
                        case ACL_ALLOW_NONE:
                        default:
                            $outputAcl['fields'][$field]['read'] = 'no';
                            $outputAcl['fields'][$field]['write'] = 'no';
                            $outputAcl['fields'][$field]['create'] = 'no';
                            break;
                    }
                }

            }
        }
        $outputAcl['_hash'] = md5(serialize($outputAcl));
        return $outputAcl;
    }

    /**
     * Fields accessor, gets sugar fields
     *
     * @return array array of sugarfields with a hash
     */
    public function getSugarFields()
    {
        return $this->getClientFiles('field');
    }

    /**
     * Views accessor Gets client views
     *
     * @return array
     */
    public function getSugarViews()
    {
        return $this->getClientFiles('view');
    }

    /**
     * Gets client layouts, similar to module specific layouts except used on a
     * global level by the clients consuming this data
     *
     * @return array
     */
    public function getSugarLayouts()
    {
        return $this->getClientFiles('layout');
    }

    /**
     * Gets client files of type $type (view, layout, field) for a module or for the system
     *
     * @param string $type The type of files to get
     * @param string $module Module name (leave blank to get the system wide files)
     * @return array
     */
    public function getClientFiles($type, $module='')
    {

        // This is a semi-complicated multi-step process, so we're going to try and make this as easy as possible.
        
        // First, build a list of paths to check
        $checkPaths = array();
        if ( $module == '' ) {
            foreach ( $this->platforms as $platform ) {
                // These are sorted in order of priority.
                // No templates for the non-module stuff
                $checkPaths['custom/clients/'.$platform.'/'.$type.'s'] = array('platform'=>$platform,'template'=>false);
                $checkPaths['clients/'.$platform.'/'.$type.'s'] = array('platform'=>$platform,'template'=>false);
            }
            
        } else {
            foreach ( $this->platforms as $platform ) {
                // These are sorted in order of priority.
                // The template flag is if that file needs to be "built" by the metadata loader so it
                // is no longer a template file, but a real file.
                $checkPaths['custom/modules/'.$module.'/clients/'.$platform.'/'.$type.'s'] = array('platform'=>$platform,'template'=>false);
                $checkPaths['modules/'.$module.'/clients/'.$platform.'/'.$type.'s'] = array('platform'=>$platform,'template'=>false);
                $baseTemplateDir = 'include/SugarObjects/templates/basic/clients/'.$platform.'/'.$type.'s';
                $nonBaseTemplateDir = MetaDataFiles::getSugarObjectFileDir($module, $platform, $type);
                if (!empty($nonBaseTemplateDir) && $nonBaseTemplateDir != $baseTemplateDir ) {
                    $checkPaths['custom/'.$nonBaseTemplateDir] = array('platform'=>$platform,'template'=>true);
                    $checkPaths[$nonBaseTemplateDir] = array('platform'=>$platform, 'template'=>true);
                }
                $checkPaths['custom/'.$baseTemplateDir] = array('platform'=>$platform,'template'=>true);
                $checkPaths[$baseTemplateDir] = array('platform'=>$platform,'template'=>true);
            }
        }

        // Second, get a list of files in those directories, sorted by "relevance"
        $fileList = array();
        $templateFiles = array();
        foreach ( $checkPaths as $path => $pathInfo ) {
            // Looks at /modules/Accounts/clients/base/views/*
            // So should pull up "record","list","preview"
            $dirsInPath = SugarAutoLoader::getDirFiles($path,true);
            foreach ( $dirsInPath as $fullSubPath ) {
                $subPath = basename($fullSubPath);
                // This should find the files in each view/layout
                // So it should pull up list.js, list.php, list.hbt
                $filesInDir = SugarAutoLoader::getDirFiles($fullSubPath,false);
                foreach ( $filesInDir as $fullFile ) {
                    $file = basename($fullFile);
                    $fileIndex = $subPath.'/'.$file;
                    if ( !isset($fileList[$fileIndex]) ) {
                        $fileList[$fileIndex] = array('path'=>$fullFile, 'file'=>$file, 'subPath'=>$subPath, 'platform'=>$pathInfo['platform']);
                        if ( $pathInfo['template'] && (substr($file,-4)=='.php') ) {
                            $templateFiles[] = $fileIndex;
                        }
                    }
                }
            }
        }

        // Third, if there are any files in that list that are from template objects build out the final files
        // Third-and-a-half, load those final files instead of the template object ones.
        if ( !empty($templateFiles) ) {
            // We have templates to build
            foreach ( $templateFiles as $fileIndex ) {
                $fileInfo = $fileList[$fileIndex];
                // Unset the entry in filelist so it is only loaded if we successfully generate a new template
                unset($fileList[$fileIndex]);
                $viewdefs = array();
                require $fileInfo['path'];
                $bean = BeanFactory::getBean($module);
                if ( !is_a($bean,'SugarBean') ) {
                    continue;
                }
                // Figure out the filename to store this in
                $pathParts = explode('/',$fileInfo['path']);
                while ( $pathParts[0] != 'clients' ) {
                    // Keep stripping off array elements until we hit the clients directory
                    array_shift($pathParts);
                    if ( count($pathParts) == 0 ) {
                        break;
                    }
                }
                if ( count($pathParts) != 0 ) {
                    // We found the directory
                    array_unshift($pathParts,'custom','modules',$module);
                    $outputPath = implode('/',$pathParts);
                    // Remove the filename off of the path
                    array_pop($pathParts);
                    $outputDir = implode('/',$pathParts);

                    $viewdefs = MetaDataFiles::getModuleMetaDataDefsWithReplacements($bean, $viewdefs);
                    if ( ! isset($viewdefs[$module][$fileInfo['platform']][$type][$fileInfo['subPath']]) ) {
                        $GLOBALS['log']->error('Could not generate a metadata file for module '.$module.', platform: '.$fileInfo['platform'].', type: '.$type);
                        continue;
                    }
                    $output = "<?php\n".'$viewdefs["'.$module.'"]["'.$fileInfo['platform'].'"]["'.$type.'"]["'.$fileInfo['subPath'].'"] = '.var_export_helper($viewdefs[$module][$fileInfo['platform']][$type][$fileInfo['subPath']]).";\n";
                    if ( SugarAutoLoader::ensureDir($outputDir) ) {
                        SugarAutoLoader::put($outputPath, $output);
                        $fileInfo['path'] = $outputPath;
                        $fileList[$fileIndex] = $fileInfo;
                    } else {
                        // Can't write a new file, just throw away this item.
                        $GLOBALS['log']->error('Could not write a new metadata entry to '.$outputPath.' cannot load this piece of metadata');
                    }
                }
            }
        }
        
        // Forth, actually load up those files and return them in an array
        $results = array();
        foreach ( $fileList as $fileIndex => $fileInfo ) {
            $extension = substr($fileInfo['path'],-3);
            switch ( $extension ) {
                case '.js':
                    $results[$fileInfo['subPath']]['controller'] = file_get_contents($fileInfo['path']);
                    break;
                case 'hbt':
                    $layoutName = substr($fileInfo['file'],0,-4);
                    $results[$fileInfo['subPath']]['templates'][$layoutName] = file_get_contents($fileInfo['path']);
                    // $results[$fileInfo['subPath']]['template'] = file_get_contents($fileInfo['path']);
                    break;
                case 'php':
                    $viewdefs = array();
                    require $fileInfo['path'];
                    if ( empty($module) ) {
                        if ( !isset($viewdefs[$fileInfo['platform']][$type][$fileInfo['subPath']]) ) {
                            $GLOBALS['log']->error('No viewdefs for type: '.$type.' viewdefs @ '.$fileInfo['path']);
                        } else {
                            $results[$fileInfo['subPath']]['meta'] = $viewdefs[$fileInfo['platform']][$type][$fileInfo['subPath']];
                        }
                    } else {
                        if ( !isset($viewdefs[$module][$fileInfo['platform']][$type][$fileInfo['subPath']]) ) {
                            $GLOBALS['log']->error('No viewdefs for module: '.$module.' viewdefs @ '.$fileInfo['path']);
                        } else {
                            $results[$fileInfo['subPath']]['meta'] = $viewdefs[$module][$fileInfo['platform']][$type][$fileInfo['subPath']];
                        }
                    }
                    break;
            }
        }
        $results['_hash'] = md5(serialize($results));
        return $results;
    }

    /**
     * The collector method for the module strings
     *
     * @return array The module strings for the current language
     */
    public function getModuleStrings( $moduleName ) {
        // Bug 58174 - Escaped labels are sent to the client escaped
        $strings = return_module_language($GLOBALS['current_language'],$moduleName);
        if (is_array($strings)) {
            foreach ($strings as $k => $v) {
                $strings[$k] = $this->decodeStrings($v);
            }
        }
        
        return $strings;
    }

    /**
     * The collector method for the app strings
     *
     * @return array The app strings for the current language, and a hash of the app strings
     */
    public function getAppStrings() {
        $appStrings = $GLOBALS['app_strings'];
        $appStrings['_hash'] = md5(serialize($appStrings));
        return $appStrings;
    }

    /**
     * The collector method for the app strings
     *
     * @return array The app strings for the current language, and a hash of the app strings
     */
    public function getAppListStrings() {
        $appStrings = $GLOBALS['app_list_strings'];
        $appStrings['_hash'] = md5(serialize($appStrings));
        return $appStrings;
    }

    /**
     * The method for getting the module list, can collect for base, portal and mobile
     *
     * @return array The list of modules that are supported by this platform
     * @deprecated Functionality for this method moved into the MetadataApi class
     */
    public function getModuleList($platform = 'base') {
        if ( $platform == 'portal' ) {
            // Use SugarPortalBrowser to get the portal modules that would appear
            // in Studio
            require_once 'modules/ModuleBuilder/Module/SugarPortalBrowser.php';
            $pb = new SugarPortalBrowser();
            $pb->loadModules();
            $moduleList = array_keys($pb->modules);

            // Bug 56911 - Notes metadata is needed for portal
            $moduleList[] = "Notes";
        } else if ( $platform == 'mobile' ) {
            // replicate the essential part of the behavior of the private loadMapping() method in SugarController
            foreach(SugarAutoLoader::existingCustom('include/MVC/Controller/wireless_module_registry.php') as $file) {
                require $file;
            }

            // $wireless_module_registry is defined in the file loaded above
            $moduleList = array_keys($wireless_module_registry);
        } else {
            // Loading a standard module list
            require_once("modules/MySettings/TabController.php");
            $controller = new TabController();
            $moduleList = array_keys($controller->get_user_tabs($this->user));
            $moduleList[] = 'ActivityStream';
            $moduleList[] = 'Users';
        }

        $oldModuleList = $moduleList;
        $moduleList = array();
        foreach ( $oldModuleList as $module ) {
            $moduleList[$module] = $module;
        }

        $moduleList['_hash'] = md5(serialize($moduleList));
        return $moduleList;
    }

    public static function getPlatformList()
    {
        $platforms = array();
        // remove ones with _
        foreach(SugarAutoLoader::getFilesCustom("clients", true) as $dir) {
            $dir = basename($dir);
            if($dir[0] == '_') {
                continue;
            }
            $platforms[$dir] = true;
        }

        return array_keys($platforms);
    }
    
    /**
     * Cleans field def default values before returning them as a member of the 
     * metadata response payload
     * 
     * Bug 56505
     * Cleans default value of fields to strip out metacharacters used by the app.
     * Used initially for cleaning default multienum values.
     * 
     * @param array $fielddefs
     * @return array
     */
    protected function normalizeFielddefs(Array $fielddefs) {
        $this->getSugarFieldHandler();
        
        foreach ($fielddefs as $name => $def) {
            if (isset($def['type'])) {
                $type = !empty($def['custom_type']) ? $def['custom_type'] : $def['type'];
                
                $field = $this->sfh->getSugarField($type);
                
                $fielddefs[$name] = $field->getNormalizedDefs($def);
            }
        }
        
        return $fielddefs;
    }
    
    /**
     * Gets the SugarFieldHandler object
     * 
     * @return SugarFieldHandler The SugarFieldHandler
     */
    protected function getSugarFieldHandler() {
        if (!$this->sfh instanceof SugarFieldHandler) {
            $this->sfh = new SugarFieldHandler;
        }
        
        return $this->sfh;
    }

    /**
     * Recursive decoder that handles decoding of HTML entities in metadata strings
     * before returning them to a client
     * 
     * @param mixed $source
     * @return array|string
     */
    protected function decodeStrings($source) {
        if (is_string($source)) {
            return html_entity_decode($source, ENT_QUOTES, 'UTF-8');
        } else {
            if (is_array($source)) {
                foreach ($source as $k => $v) {
                    $source[$k] = $this->decodeStrings($v);
                }
            }
            
            return $source;
        }
    }
    
    /**
     * Clears the API metadata cache of all cache files
     * 
     * @static
     */
    public static function clearAPICache(){
        $metadataFiles = glob(sugar_cached('api/metadata/').'*');
        if ( is_array($metadataFiles) ) {
            foreach ( $metadataFiles as $metadataFile ) {
                // This removes the file and the reference from the map. This does
                // NOT save the file map since that would be expensive in a loop
                // of many deletes.
                SugarAutoLoader::unlink($metadataFile);
            }
            
            // This saves the map. Once. Instead of a bunch of times in the loop
            // above.
            SugarAutoLoader::saveMap();
        }
    }
}
