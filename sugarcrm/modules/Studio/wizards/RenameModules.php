<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

require_once('modules/Studio/DropDowns/DropDownHelper.php');
require_once 'modules/ModuleBuilder/parsers/parser.label.php' ;

class RenameModules
{
    /**
     * Selected language user is renaming for (eg. en_us).
     *
     * @var string
     */
    private $selectedLanguage;

    /**
     * An array containing the modules which should be renamed.
     *
     * @var array
     */
    private $changedModules;

    /**
     * An array containing the modules which have had their module strings modified as part of the
     * renaming process.
     *
     * @var array
     */
    private $renamedModules = array();


    /**
     *
     * @param string $options
     * @return void
     */
    public function process($options = '')
    {
        if($options == 'SaveDropDown')
            $this->save();

        $this->display();

    }

    /**
     * Main display function.
     *
     * @return void
     */
    protected function display()
    {
        global $app_list_strings, $mod_strings;

        
        require_once('modules/Studio/parsers/StudioParser.php');
        $dh = new DropDownHelper();
        
        $smarty = new Sugar_Smarty();
        $smarty->assign('MOD', $GLOBALS['mod_strings']);
        $title=getClassicModuleTitle($mod_strings['LBL_MODULE_NAME'], array($mod_strings['LBL_RENAME_TABS']), false);
        $smarty->assign('title', $title);

        $selected_lang = (!empty($_REQUEST['dropdown_lang'])?$_REQUEST['dropdown_lang']:$_SESSION['authenticated_user_language']);
        if(empty($selected_lang))
        {
            $selected_lang = $GLOBALS['sugar_config']['default_language'];
        }

        if($selected_lang == $GLOBALS['current_language'])
        {
            $my_list_strings = $GLOBALS['app_list_strings'];
        }
        else
        {
            $my_list_strings = return_app_list_strings_language($selected_lang);
        }

        $selected_dropdown = $my_list_strings['moduleList'];
        $selected_dropdown_singular = $my_list_strings['moduleListSingular'];


        foreach($selected_dropdown as $key=>$value)
        {
           $singularValue = isset($selected_dropdown_singular[$key]) ? $selected_dropdown_singular[$key] : $value;
           if($selected_lang != $_SESSION['authenticated_user_language'] && !empty($app_list_strings['moduleList']) && isset($app_list_strings['moduleList'][$key]))
           {
                $selected_dropdown[$key]=array('lang'=>$value, 'user_lang'=> '['.$app_list_strings['moduleList'][$key] . ']', 'singular' => $singularValue);
           }
           else
           {
               $selected_dropdown[$key]=array('lang'=>$value, 'singular' => $singularValue);
           }
        }


        $selected_dropdown = $dh->filterDropDown('moduleList', $selected_dropdown);

        $smarty->assign('dropdown', $selected_dropdown);
        $smarty->assign('dropdown_languages', get_languages());

        $buttons = array();
        $buttons[] = array('text'=>$mod_strings['LBL_BTN_UNDO'],'actionScript'=>"onclick='jstransaction.undo()'" );
        $buttons[] = array('text'=>$mod_strings['LBL_BTN_REDO'],'actionScript'=>"onclick='jstransaction.redo()'" );
        $buttons[] = array('text'=>$mod_strings['LBL_BTN_SAVE'],'actionScript'=>"onclick='if(check_form(\"editdropdown\")){document.editdropdown.submit();}'");
        $buttonTxt = StudioParser::buildImageButtons($buttons);
        $smarty->assign('buttons', $buttonTxt);
        $smarty->assign('dropdown_lang', $selected_lang);

        $editImage = SugarThemeRegistry::current()->getImage( 'edit_inline', '');
        $smarty->assign('editImage',$editImage);
        $deleteImage = SugarThemeRegistry::current()->getImage( 'delete_inline', '');
        $smarty->assign('deleteImage',$deleteImage);
        $smarty->display("modules/Studio/wizards/RenameModules.tpl");
    }

    /**
     * Save function responsible executing all sub-save functions required to rename a module.
     *
     * @return void
     */
    public function save($redirect = TRUE)
    {
        $this->selectedLanguage = (!empty($_REQUEST['dropdown_lang'])? $_REQUEST['dropdown_lang']:$_SESSION['authenticated_user_language']);

        //Clear all relevant language caches
        $this->clearLanguageCaches();
        
        //Retrieve changes the user is requesting and store previous values for future use.
        $this->changedModules = $this->getChangedModules();

        //Change module, appStrings, subpanels, and related links.
        $this->changeAppStringEntries()->changeAllModuleModStrings()->renameAllRelatedLinks()->renameAllSubpanels();

        //Refresh the page again so module tabs are changed as the save process happens after module tabs are already generated.
        if($redirect)
            SugarApplication::redirect('index.php?action=wizard&module=Studio&wizard=StudioWizard&option=RenameTabs');
    }

    /**
     * Rename all subpanels within the application.
     *
     *
     * @return RenameModules
     */
    private function renameAllSubpanels()
    {
        global $beanList;

        foreach($beanList as $moduleName => $beanName)
        {
            if( class_exists($beanName) )
            {
                $this->renameModuleSubpanel($moduleName, $beanName, $this->changedModules);
            }
            else
            {
                $GLOBALS['log']->error("Class $beanName does not exist, unable to rename.");
            }
        }

        return $this;

    }

    /**
     * Rename subpanels for a particular module.
     *
     * @param  string $moduleName The name of the module to be renamed
     * @param  string $beanName  The name of the SugarBean to be renamed.
     * @return void
     */
    private function renameModuleSubpanel($moduleName, $beanName)
    {
        $GLOBALS['log']->info("About to rename subpanel for module: $moduleName");
        $bean = new $beanName();
        //Get the subpanel def
        $subpanelDefs = $this->getSubpanelDefs($bean);

        if( count($subpanelDefs) <= 0)
        {
            $GLOBALS['log']->debug("Found empty subpanel defs for $moduleName");
            return;
        }

        $mod_strings = return_module_language($this->selectedLanguage, $moduleName);
        $replacementStrings = array();

        //Iterate over all subpanel entries and see if we need to make a change.
        foreach($subpanelDefs as $subpanelName => $subpanelMetaData)
        {
            $GLOBALS['log']->debug("Examining subpanel definition for potential rename: $subpanelName ");
            //For each subpanel def, check if they are in our changed modules set.
            foreach($this->changedModules as $changedModuleName => $renameFields)
            {
                if( !( isset($subpanelMetaData['type']) &&  $subpanelMetaData['type'] == 'collection') //Dont bother with collections
                    && $subpanelMetaData['module'] == $changedModuleName && isset($subpanelMetaData['title_key']) )
                {
                    $replaceKey = $subpanelMetaData['title_key'];
                    if( !isset($mod_strings[$replaceKey]) )
                    {
                        $GLOBALS['log']->info("No module string entry defined for: {$mod_strings[$replaceKey]}");
                        continue;
                    }
                    $oldStringValue = $mod_strings[$replaceKey];
                    //At this point we don't know if we should replace the string with the plural or singular version of the new
                    //strings so we'll try both but with the plural version first since it should be longer than the singular.
                    $replacedString = str_replace($renameFields['prev_plural'], $renameFields['plural'], $oldStringValue);
                    $replacedString = str_replace($renameFields['prev_singular'], $renameFields['singular'], $replacedString);
                    $replacementStrings[$replaceKey] = $replacedString;
                }
            }
        }

        //Now we can write out the replaced language strings for each module
        if(count($replacementStrings) > 0)
        {
            $GLOBALS['log']->debug("Writing out labels for subpanel changes for module $moduleName, labels: " . var_export($replacementStrings,true));
            ParserLabel::addLabels($this->selectedLanguage, $replacementStrings, $moduleName);
            $this->renamedModules[$moduleName] = true;
        }
    }

    /**
     * Retrieve the subpanel definitions for a given SugarBean object. Unforunately we can't reuse
     * any of the SubPanelDefinion.php functions.
     *
     * @param  SugarBean $bean
     * @return array The subpanel definitions.
     */
    private function getSubpanelDefs($bean )
	{

		$layout_defs = array();

        if ( file_exists( 'modules/' . $bean->module_dir . '/metadata/subpaneldefs.php') )
            require('modules/' . $bean->module_dir . '/metadata/subpaneldefs.php');

        if ( file_exists( 'custom/modules/' . $bean->module_dir . '/Ext/Layoutdefs/layoutdefs.ext.php'))
            require('custom/modules/' . $bean->module_dir . '/Ext/Layoutdefs/layoutdefs.ext.php');

         return isset($layout_defs[$bean->module_dir]['subpanel_setup']) ? $layout_defs[$bean->module_dir]['subpanel_setup'] : $layout_defs;
	}

    /**
     * Rename all related linked within the application
     *
     * @return RenameModules
     */
    private function renameAllRelatedLinks()
    {
        global $beanList;

        foreach($beanList as $moduleName => $beanName)
        {
            if( class_exists($beanName) )
            {
                $this->renameModuleRelatedLinks($moduleName, $beanName);
            }
            else
            {
                $GLOBALS['log']->fatal("Class $beanName does not exist, unable to rename.");
            }
        }

        return $this;
    }

    /**
     * Rename the related links within a module.
     *
     * @param  string $moduleName The module to be renamed
     * @param  string $moduleClass The class name of the module to be renamed
     * @return void
     */
    private function renameModuleRelatedLinks($moduleName, $moduleClass)
    {
        $GLOBALS['log']->info("Begining to renameModuleRelatedLinks for $moduleClass\n");
        $tmp = new $moduleClass;
        if( ! method_exists($tmp, 'get_related_fields') )
        {
            $GLOBALS['log']->info("Unable to resolve linked fields for module $moduleClass ");
            return;
        }

        $linkedFields = $tmp->get_related_fields();
        $mod_strings = return_module_language($this->selectedLanguage, $moduleName);
        $replacementStrings = array();

        foreach($linkedFields as $link => $linkEntry)
        {
            //For each linked field check if the module referenced to is in our changed module list.
            foreach($this->changedModules as $changedModuleName => $renameFields)
            {
                if( isset($linkEntry['module']) && $linkEntry['module'] ==  $changedModuleName)
                {
                    $GLOBALS['log']->debug("Begining to rename for link field {$link}");
                    if( !isset($mod_strings[$linkEntry['vname']]) )
                    {
                        $GLOBALS['log']->debug("No label attribute for link $link, continuing.");
                        continue;
                    }

                    $replaceKey = $linkEntry['vname'];
                    $oldStringValue = $mod_strings[$replaceKey];
                    //At this point we don't know if we should replace the string with the plural or singular version of the new
                    //strings so we'll try both but with the plural version first since it should be longer than the singular.
                    $replacedString = str_replace($renameFields['prev_plural'], $renameFields['plural'], $oldStringValue);
                    $replacedString = str_replace($renameFields['prev_singular'], $renameFields['singular'], $replacedString);
                    $replacementStrings[$replaceKey] = $replacedString;
                }
            }
        }

        //Now we can write out the replaced language strings for each module
        if(count($replacementStrings) > 0)
        {
            $GLOBALS['log']->debug("Writing out labels for link changes for module $moduleName, labels: " . var_export($replacementStrings,true));
            ParserLabel::addLabels($this->selectedLanguage, $replacementStrings, $moduleName);
            $this->renamedModules[$moduleName] = true;
        }
    }

    /**
     * Clear all related language cache files.
     *
     * @return void
     */
    private function clearLanguageCaches()
    {
        //remove the js language files
        LanguageManager::removeJSLanguageFiles();

        //remove lanugage cache files
        LanguageManager::clearLanguageCache();
    }


    /**
     * Rename all module strings within the application.
     *
     * @return RenameModules
     */
    private function changeAllModuleModStrings()
    {
        foreach($this->changedModules as $moduleName => $replacementLabels)
        {
            $this->changeModuleModStrings($moduleName, $replacementLabels);
        }

        return $this;
    }

    /**
     * For a particular module, rename any relevant module strings that need to be replaced.
     *
     * @param  string $moduleName The name of the module to be renamed.
     * @param  $replacementLabels
     * @return void
     */
    private function changeModuleModStrings($moduleName, $replacementLabels)
    {
        $GLOBALS['log']->info("Begining to change module labels for: $moduleName");
        $currentModuleStrings = return_module_language($this->selectedLanguage, $moduleName);
        $labelKeysToReplace = array(
            array('name' => 'LNK_NEW_RECORD', 'type' => 'plural'), //Module built modules, Create <moduleName>
            array('name' => 'LNK_LIST', 'type' => 'plural'), //Module built modules, View <moduleName>
            array('name' => 'LNK_NEW_###MODULE_SINGULAR###', 'type' => 'singular'),
            array('name' => 'LNK_###MODULE_SINGULAR###_LIST', 'type' => 'plural'),
            array('name' => 'LNK_###MODULE_SINGULAR###_REPORTS', 'type' => 'singular'),
            array('name' => 'LNK_IMPORT_VCARD', 'type' => 'singular'),
            array('name' => 'LNK_IMPORT_###MODULE_PLURAL###', 'type' => 'plural'),
            array('name' => 'LBL_LIST_FORM_TITLE', 'type' => 'singular'), //Popup title
            array('name' => 'LBL_SEARCH_FORM_TITLE', 'type' => 'singular'), //Popup title

        );

        $replacedLabels = array();
        foreach($labelKeysToReplace as $entry)
        {
            $formattedLanguageKey = $this->formatModuleLanguageKey($entry['name'], $replacementLabels);

            //If the static of dynamic key exists it should be replaced.
            if( isset($currentModuleStrings[$formattedLanguageKey]) )
            {
                $oldStringValue = $currentModuleStrings[$formattedLanguageKey];
                $replacedLabels[$formattedLanguageKey] = $this->replaceSingleLabel($oldStringValue, $replacementLabels, $entry);
            }
        }

        //Save all entries
        ParserLabel::addLabels($this->selectedLanguage, $replacedLabels, $moduleName);
        $this->renamedModules[$moduleName] = true;
    }

    /**
     * Format our dynamic keys containing module strings to a valid key depending on the module.
     *
     * @param  string $unformatedKey
     * @param  string $replacementStrings
     * @return string
     */
    private function formatModuleLanguageKey($unformatedKey, $replacementStrings)
    {
        $unformatedKey = str_replace('###MODULE_SINGULAR###', strtoupper($replacementStrings['key_singular']), $unformatedKey);
        return str_replace('###MODULE_PLURAL###', strtoupper($replacementStrings['key_plural']), $unformatedKey);

    }

    /**
     * Replace a label with a new value based on metadata which specifies the label as either singular or plural.
     *
     * @param  string $oldStringValue
     * @param  string $replacementLabels
     * @param  array $replacementMetaData
     * @return string
     */
    private function replaceSingleLabel($oldStringValue, $replacementLabels, $replacementMetaData)
    {
        $replaceKey = 'prev_' . $replacementMetaData['type'];
        return str_replace($replacementLabels[$replaceKey] , $replacementLabels[$replacementMetaData['type']], $oldStringValue);
    }


    /**
     * Save changes to the module names to the app string entries for both the moduleList and moduleListSingular entries.
     *
     * @return RenameModules
     */
    private function changeAppStringEntries()
    {
        $GLOBALS['log']->debug('Begining to save app string entries');
        //Save changes to the moduleList app string entry
        DropDownHelper::saveDropDown($_REQUEST);

        //Save changes to the moduleListSingular app string entry
        $newParams = array();
        $newParams['dropdown_name'] = 'moduleListSingular';
        $newParams['dropdown_lang'] = isset($_REQUEST['dropdown_lang']) ? $_REQUEST['dropdown_lang'] : '';
        $newParams['use_push'] = true;
        DropDownHelper::saveDropDown($this->createModuleListSingularPackage($newParams, $this->changedModules));
        return $this;
    }

    /**
     * Create an array entry that can be passed to the DropDownHelper:saveDropDown function so we can re-utilize
     * the save logic.
     *
     * @param  array $params
     * @param  array $changedModules
     * @return
     */
    private function createModuleListSingularPackage($params, $changedModules)
    {
        $count = 0;
        foreach($changedModules as $moduleName => $package)
        {
            $singularString = $package['singular'];

            $params['slot_' . $count] = $count;
            $params['key_' . $count] = $moduleName;
            $params['value_' . $count] = $singularString;
            $params['delete_' . $count] = '';

            $count++;
        }

        return $params;

    }

    /**
     * Determine which modules have been updated and return an array with the module name as the key
     * and the singular/plural entries as the value.
     *
     * @return array
     */
    private function getChangedModules()
    {
        $count = 0;
        $allModuleEntries = array();
        $results = array();
        $params = $_REQUEST;

        $selected_lang = (!empty($params['dropdown_lang'])?$params['dropdown_lang']:$_SESSION['authenticated_user_language']);
        $current_app_list_string = return_app_list_strings_language($selected_lang);

        while(isset($params['slot_' . $count]))
        {
            $index = $params['slot_' . $count];
            $key = (isset($params['key_' . $index]))?$params['key_' . $index]: 'BLANK';
            $value = (isset($params['value_' . $index]))?$params['value_' . $index]: '';
            $svalue = (isset($params['svalue_' . $index]))?$params['svalue_' . $index]: $value;
            if($key == 'BLANK')
               $key = '';

            $key = trim($key);
            $value = trim($value);
            $svalue = trim($svalue);

            //If the module key dne then do not continue with this rename.
            if( isset($current_app_list_string['moduleList'][$key]) )
                $allModuleEntries[$key] = array('s' => $svalue, 'p' => $value);
            else
                $_REQUEST['delete_' . $count] = TRUE;


           $count++;
        }


        foreach($allModuleEntries as $k => $e)
        {
            $svalue = $e['s'];
            $pvalue = $e['p'];
            $prev_plural = $current_app_list_string['moduleList'][$k];
            $prev_singular = isset($current_app_list_string['moduleListSingular'][$k]) ? $current_app_list_string['moduleListSingular'][$k] : $prev_plural;
            if( strcmp($prev_plural, $pvalue) != 0 || (strcmp($prev_singular, $svalue) != 0) )
            {
                $results[$k] = array('singular' => $svalue, 'plural' => $pvalue, 'prev_singular' => $prev_singular, 'prev_plural' => $prev_plural,
                                     'key_plural' => $k, 'key_singular' => $this->getModuleSingularKey($k)
                );
            }

        }

        return $results;
    }


    /**
     * Return the 'singular' name of a module (Eg. Opportunity for Opportunities) given a moduleName which is a key
     * in the app string moduleList array.  If no entry is found, simply return the moduleName as this is consistant with modules
     * built by moduleBuilder.
     *
     * @param  string $moduleName
     * @return string The 'singular' name of a module.
     */
    private function getModuleSingularKey($moduleName)
    {
        $className = isset($GLOBALS['beanList'][$moduleName]) ? $GLOBALS['beanList'][$moduleName] : null;
        if( is_null($className) || ! class_exists($className) )
        {
            $GLOBALS['log']->error("Unable to get module singular key for class: $className");
            return $moduleName;
        }

        $tmp = new $className();
        if( property_exists($tmp, 'object_name') )
            return $tmp->object_name;
        else
            return $moduleName;
    }

    /**
     * Return an array of the modules whos mod_strings have been modified.
     *
     * @return array
     */
    public function getRenamedModules()
    {
        return $this->renamedModules;
    }
}



