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

require_once 'data/BeanFactory.php';
require_once 'modules/ModuleBuilder/parsers/relationships/DeployedRelationships.php';
require_once 'modules/ModuleBuilder/parsers/constants.php';

class StudioModule
{
    public $name;
    private $popups = array();
    public $module;
    public $fields;
    public $seed;

    /**
     * Backwards compatibility check, set here in the event that a bean is not
     * found for the requested module
     *
     * @var bool
     */
    public $bwc = false;

    /**
     * The indicator to use in the tree and menus to indicate a backward compatible
     * module
     *
     * @var string
     */
    public static $bwcIndicator = '*';

    /**
     * Class constructor
     * 
     * @param string $module The name of the module to base this object on
     */
    public function __construct($module)
    {
        $moduleList = $GLOBALS['app_list_strings']['moduleList'];
        if (empty($moduleList) && !is_array($moduleList)) {
            $moduleList = array();
        }

        $moduleNames = array_change_key_case($moduleList);
        $this->name = isset($moduleNames[strtolower($module)]) ? $moduleNames[strtolower($module)] : strtolower($module);
        $this->module = $module;
        $this->seed = BeanFactory::getBean($this->module);
        if ($this->seed) {
            $this->fields = $this->seed->field_defs;
        }

        // Set BWC since this is needed for sources
        $this->bwc = isModuleBWC($module);

        $this->setSources();
    }

    /**
     * Sets the viewdef file sources for use in studio
     */
    protected function setSources()
    {
        // Backward Compatible modules need the old way of doing things
        if ($this->bwc) {
            // Sources can be used to override the file name mapping for a specific
            // view or the parser for a view.
            $this->sources = array(
                array(
                    'name'  => translate('LBL_EDITVIEW'),
                    'type'  => MB_EDITVIEW,
                    'image' => 'EditView',
                    'path'  => "modules/{$this->module}/metadata/editviewdefs.php",
                ),
                array(
                    'name'  => translate('LBL_DETAILVIEW'),
                    'type'  => MB_DETAILVIEW,
                    'image' => 'DetailView',
                    'path'  => "modules/{$this->module}/metadata/detailviewdefs.php",
                ),
                array(
                    'name'  => translate('LBL_LISTVIEW'),
                    'type'  => MB_LISTVIEW,
                    'image' => 'ListView',
                    'path'  => "modules/{$this->module}/metadata/listviewdefs.php",
                ),
            );
        } else {
            $this->sources = array(
                array(
                    'name'  => translate('LBL_RECORDVIEW'),
                    'type'  => MB_RECORDVIEW,
                    'image' => 'RecordView',
                    'path'  => "modules/{$this->module}/clients/base/views/record/record.php",
                ),
                array(
                    'name'  => translate('LBL_LISTVIEW'),
                    'type'  => MB_LISTVIEW,
                    'image' => 'ListView',
                    'path'  => "modules/{$this->module}/clients/base/views/list/list.php",
                ),
            );
        }
    }

    /**
     * Gets the name of this module. Some modules have naming inconsistencies 
     * such as Bug Tracker and Bugs which causes warnings in Relationships
     * Added to resolve bug #20257
     * 
     * @return string
     */
    public function getModuleName()
    {
        $modules_with_odd_names = array(
            'Bug Tracker'=>'Bugs'
        );
        
        if (isset($modules_with_odd_names[$this->name])) {
            return $modules_with_odd_names[$this->name];
        }

        return $this->name;
    }

    /**
     * Attempt to determine the type of a module, for example 'basic' or 'company'
     * These types are defined by the SugarObject Templates in /include/SugarObjects/templates
     * Custom modules extend one of these standard SugarObject types, so the type can be determined from their parent
     * Standard module types can be determined simply from the module name - 'bugs' for example is of type 'issue'
     * If all else fails, fall back on type 'basic'...
     * 
     * @return string Module's type
     */
    public function getType()
    {
        // first, get a list of a possible parent types
        $templates = array();
        $d = dir('include/SugarObjects/templates');
        while ($filename = $d->read()) {
            if (substr($filename, 0, 1) != '.') {
                $templates[strtolower($filename)] = strtolower($filename);
            }
        }

        // If a custom module, then its type is determined by the parent SugarObject that it extends
        $seed = BeanFactory::getBean($this->module);
        if (empty($seed)) {
            //If there is no bean at all for this module, use the basic template for base files
            return "basic";
        }
        $type = get_class($seed);
        do {
            $type = get_parent_class($type);
        } while (!in_array(strtolower($type), $templates) && $type != 'SugarBean');

        if ($type != 'SugarBean') {
            return strtolower($type);
        }

        // If a standard module then just look up its type - type is implicit 
        // for standard modules. Perhaps one day we will make it explicit, just 
        // as we have done for custom modules...
        $types = array(
            'Accounts' => 'company' ,
            'Bugs' => 'issue' ,
            'Cases' => 'issue' ,
            'Contacts' => 'person' ,
            'Documents' => 'file' ,
            'Leads' => 'person' ,
            'Opportunities' => 'sale'
        );
        if (isset($types[$this->module])) {
            return $types[$this->module];
        }

        return "basic";
    }

    /**
     * Return the fields for this module as sourced from the SugarBean
     * 
     * @return Array of fields
     */

    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Gets all nodes for this module for use in rendering studio
     * 
     * @return array
     */
    public function getNodes()
    {
        $bwc = $this->bwc ? ' ' . self::$bwcIndicator : '';

        return array(
            'name' => $this->name . $bwc,
            'module' => $this->module,
            'type' => 'StudioModule',
            'action' => "module=ModuleBuilder&action=wizard&view_module={$this->module}",
            'children' => $this->getModule(),
            'bwc' => $this->bwc,
        );
    }

    /**
     * Gets specific nodes and actions related to this module
     * 
     * @return array
     */
    public function getModule()
    {
        $sources = array(
            translate('LBL_LABELS') => array(
                'action' => "module=ModuleBuilder&action=editLabels&view_module={$this->module}",
                'imageTitle' => 'Labels', 
                'help' => 'labelsBtn',
            ),
            translate('LBL_FIELDS') => array(
                'action' => "module=ModuleBuilder&action=modulefields&view_package=studio&view_module={$this->module}",
                'imageTitle' => 'Fields', 
                'help' => 'fieldsBtn',
            ),
            translate('LBL_RELATIONSHIPS') => array(
                'action' => "get_tpl=true&module=ModuleBuilder&action=relationships&view_module={$this->module}",
                'imageTitle' => 'Relationships',
                'help' => 'relationshipsBtn',
            ),
            translate('LBL_LAYOUTS') => array(
                'children' => 'getLayouts', 
                'action' => "module=ModuleBuilder&action=wizard&view=layouts&view_module={$this->module}", 
                'imageTitle' => 'Layouts', 
                'help' => 'layoutsBtn',
            ),
            translate('LBL_SUBPANELS') => array(
                'children' => 'getSubpanels',
                'action' => "module=ModuleBuilder&action=wizard&view=subpanels&view_module={$this->module}",
                'imageTitle' => 'Subpanels',
                'help' => 'subpanelsBtn',
            ), 
        );
        //BEGIN SUGARCRM flav=pro ONLY
        $sources[translate('LBL_WIRELESSLAYOUTS')] = array(
            'children' => 'getWirelessLayouts', 
            'action' => "module=ModuleBuilder&action=wizard&view=wirelesslayouts&view_module={$this->module}",
            'imageTitle' => 'MobileLayouts',
            'help' => 'wirelesslayoutsBtn',
        );
        //END SUGARCRM flav=pro ONLY
        //BEGIN SUGARCRM flav=ent ONLY
        $sources[translate('LBL_PORTAL_LAYOUTS')] = array(
            'children' => 'getPortal',
            'action' => "module=ModuleBuilder&action=wizard&portal=1&view_module={$this->module}",
            'imageTitle' => 'Portal',
            'help' => 'portalBtn',
        );
        //END SUGARCRM flav=ent ONLY

        $nodes = array();
        foreach ($sources as $source => $def) {
            $nodes[$source] = $def;
            $nodes[$source]['name'] = translate($source);
            if (isset($def['children'])) {
                $childNodes = $this->$def['children']();
                if (!empty($childNodes)) {
                    $nodes[$source]['type'] = 'Folder';
                    $nodes[$source]['children'] = $childNodes;
                } else {
                    unset($nodes[$source]);
                }
            }
        }

        return $nodes ;
    }

    /**
     * Gets views for this module
     * 
     * @return array
     */
    public function getViews()
    {
        $views = array () ;

        foreach ($this->sources as $def) {
            // Remove path from the defs as it's not needed in the views array
            $path = $def['path'];
            unset($def['path']);
            if (file_exists($path) || file_exists("custom/$path")) {
                $views[basename($path, '.php')] = $def;
            }
        }

        return $views;
    }

    /**
     * Gets layouts for this module
     * 
     * @return array
     */
    public function getLayouts()
    {
        $views = $this->getViews();

        $layouts = array();
        foreach ($views as $def) {
            $view = !empty($def['view']) ? $def['view'] : $def['type'];
            $layouts[$def['name']] = array(
                'name' => $def['name'],
                'action' => "module=ModuleBuilder&action=editLayout&view={$view}&view_module={$this->module}",
                'imageTitle' => $def['image'],
                'help' => "viewBtn{$def['type']}",
                'size' => '48',
            );
        }

        //For popup tree node
        $popups = array();
        $popups[] = array(
            'name' => translate('LBL_POPUPLISTVIEW'),
            'type' => 'popuplistview',
            'action' => 'module=ModuleBuilder&action=editLayout&view=popuplist&view_module=' . $this->module,
        );
        $popups[] = array(
            'name' => translate('LBL_POPUPSEARCH'),
            'type' => 'popupsearch',
            'action' => 'module=ModuleBuilder&action=editLayout&view=popupsearch&view_module=' . $this->module,
        );
        $layouts[translate('LBL_POPUP')] = array(
            'name' => translate('LBL_POPUP'),
            'type' => 'Folder',
            'children' => $popups,
            'imageTitle' => 'Popup',
            'action' => 'module=ModuleBuilder&action=wizard&view=popup&view_module=' . $this->module,
        );

        $nodes = $this->getSearch();
        if (!empty($nodes)) {
            $layouts[translate('LBL_SEARCH')] = array(
                'name' => translate('LBL_SEARCH'),
                'type' => 'Folder',
                'children' => $nodes,
                'action' => "module=ModuleBuilder&action=wizard&view=search&view_module={$this->module}",
                'imageTitle' => 'BasicSearch',
                'help' => 'searchBtn',
                'size' => '48',
            );
        }

        return $layouts ;

    }

    //BEGIN SUGARCRM flav=pro ONLY
    /**
     * Gets wiresless layouts for this module
     * 
     * @return array
     */
    public function getWirelessLayouts()
    {
        $layouts[translate('LBL_WIRELESSEDITVIEW')] = array(
            'name' => translate('LBL_WIRELESSEDITVIEW'),
            'type' => MB_WIRELESSEDITVIEW,
            'action' => "module=ModuleBuilder&action=editLayout&view=".MB_WIRELESSEDITVIEW."&view_module={$this->module}",
            'imageTitle' => 'EditView',
            'help' => "viewBtn".MB_WIRELESSEDITVIEW,
            'size' => '48',
        );
        $layouts[translate('LBL_WIRELESSDETAILVIEW')] = array(
            'name' => translate('LBL_WIRELESSDETAILVIEW'),
            'type' => MB_WIRELESSDETAILVIEW,
            'action' => "module=ModuleBuilder&action=editLayout&view=".MB_WIRELESSDETAILVIEW."&view_module={$this->module}",
            'imageTitle' => 'DetailView',
            'help' => "viewBtn".MB_WIRELESSDETAILVIEW,
            'size' => '48',
        );
        $layouts[translate('LBL_WIRELESSLISTVIEW')] = array(
            'name' => translate('LBL_WIRELESSLISTVIEW'),
            'type' => MB_WIRELESSLISTVIEW,
            'action' => "module=ModuleBuilder&action=editLayout&view=".MB_WIRELESSLISTVIEW."&view_module={$this->module}",
            'imageTitle' => 'ListView',
            'help' => "viewBtn".MB_WIRELESSLISTVIEW,
            'size' => '48',
        );
        $layouts[translate('LBL_WIRELESSSEARCH')] = array(
            'name' => translate('LBL_WIRELESSSEARCH'),
            'type' => MB_WIRELESSBASICSEARCH,
            'action' => "module=ModuleBuilder&action=editLayout&view=".MB_WIRELESSBASICSEARCH."&view_module={$this->module}",
            'imageTitle' => 'BasicSearch',
            'help' => "searchBtn",
            'size' => '48',
        );

        return $layouts ;
    }
    //END SUGARCRM flav=pro ONLY

    /**
     * Gets appropriate search layouts for the module
     * 
     * @return array
     */
    public function getSearch()
    {
        $nodes = array();
        $options =  $this->bwc ? array(MB_BASICSEARCH => 'LBL_BASIC_SEARCH', MB_ADVANCEDSEARCH => 'LBL_ADVANCED_SEARCH') : array(MB_BASICSEARCH => 'LBL_FILTER_SEARCH',);
        foreach ($options as $view => $label) {
            try {
                $title = translate($label);
                if ($label == 'LBL_BASIC_SEARCH') {
                    $name = 'BasicSearch';
                } elseif ($label == 'LBL_ADVANCED_SEARCH') {
                    $name = 'AdvancedSearch';
                } elseif ($label == 'LBL_FILTER_SEARCH') {
                    $name = "FilterSearch";
                } else {
                    $name = str_replace(' ', '', $title);
                }
                $nodes[$title] = array(
                    'name' => $title, 
                    'action' => "module=ModuleBuilder&action=editLayout&view={$view}&view_module={$this->module}", 
                    'imageTitle' => $title, 
                    'imageName' => $name, 
                    'help' => "{$name}Btn", 
                    'size' => '48',
                );
            } catch (Exception $e) {
                $GLOBALS['log']->info('No search layout : '. $e->getMessage());
            }
        }

        return $nodes;
    }

    /**
     * Return an object containing all the relationships participated in by this
     * module
     * 
     * @return AbstractRelationships Set of relationships
     */
    public function getRelationships()
    {
        return new DeployedRelationships($this->module);
    }

    //BEGIN SUGARCRM flav=pro ONLY
    /**
     * Gets the collection of portal layouts for this module, if they exist
     * 
     * @return array
     */
    public function getPortal()
    {
        $nodes = array();
        foreach ($this->sources as $file => $def) {
            $file = str_replace('viewdefs', '', $file);
            if (file_exists("modules/{$this->module}/metadata/portal/views/$file")) {
                $nodes[] = array(
                   'name' => $def['name'],
                   'action' => 'module=ModuleBuilder&action=editPortal&view=' . ucfirst($def['type']) . '&view_module=' . $this->module,
                );
            }
        }

        return $nodes;
    }

    //END SUGARCRM flav=pro ONLY

    /**
     * Gets a list of subpanels used by the current module
     * 
     * @return array
     */
    public function getSubpanels()
    {
        if(!empty($GLOBALS['current_user']) && empty($GLOBALS['modListHeader'])) {
            $GLOBALS['modListHeader'] = query_module_access_list($GLOBALS['current_user']);
        }

        require_once 'include/SubPanel/SubPanel.php';

        $nodes = array();

        $GLOBALS['log']->debug("StudioModule->getSubpanels(): getting subpanels for " . $this->module);

        // counter to add a unique key to assoc array below
        $ct = 0;
        foreach (SubPanel::getModuleSubpanels($this->module) as $name => $label) {
            if ($name == 'users') {
                continue;
            }
            $subname = sugar_ucfirst((!empty($label)) ? translate($label, $this->module) : $name);
            $action = "module=ModuleBuilder&action=editLayout&view=ListView&view_module={$this->module}&subpanel={$name}&subpanelLabel=" . urlencode($subname);

            //  bug47452 - adding a unique number to the $nodes[ key ] so if you have 2+ panels
            //  with the same subname they will not cancel each other out
            $nodes[$subname . $ct++] = array(
                'name' => $name,
                'label' => $subname,
                'action' =>  $action,
                'imageTitle' => $subname,
                'imageName' => 'icon_' . ucfirst($name) . '_32',
                'altImageName' => 'Subpanels',
                'size' => '48',
            );
        }

        return $nodes;
    }

    /**
     * Sets and gets a list of subpanels provided to other modules
     * 
     * @return array
     */
    public function getProvidedSubpanels()
    {
        require_once 'modules/ModuleBuilder/parsers/relationships/AbstractRelationships.php';
        $this->providedSubpanels = array();
        $subpanelDir = 'modules/' . $this->module . '/metadata/subpanels/';
        foreach (array($subpanelDir, "custom/$subpanelDir") as $dir) {
            if (is_dir($dir)) {
                foreach (scandir($dir) as $fileName) {
                    // sanity check to confirm that this is a usable subpanel...
                    if (substr($fileName, 0, 1) != '.' 
                        && substr(strtolower($fileName), -4) == ".php"
                        && AbstractRelationships::validSubpanel("$dir/$fileName")
                    ) {
                        $subname = str_replace('.php', '', $fileName);
                        $this->providedSubpanels[$subname] = $subname;
                    }
                }
            }
        }

        return $this->providedSubpanels;
    }

    /**
     * Gets parent modules of a subpanel
     * 
     * @param string $subpanel The name of the subpanel
     * @return array
     */
    public function getParentModulesOfSubpanel($subpanel)
    {
        global $moduleList, $beanFiles, $beanList, $module;

        //use tab controller function to get module list with named keys
        require_once 'modules/MySettings/TabController.php';
        require_once 'include/SubPanel/SubPanelDefinitions.php';
        $modules_to_check = TabController::get_key_array($moduleList);

        //change case to match subpanel processing later on
        $modules_to_check = array_change_key_case($modules_to_check);

        $spd = '';
        $spd_arr = array();
        //iterate through modules and build subpanel array
        foreach ($modules_to_check as $mod_name) {
            $bean = BeanFactory::getBean($mod_name);
            if(empty($bean)) continue;

            //create new subpanel definition instance and get list of tabs
            $spd = new SubPanelDefinitions($bean);
            if (isset($spd->layout_defs['subpanel_setup'][strtolower($subpanel)]['module'])) {
                $spd_arr[] = $mod_name;
            }
        }

        return  $spd_arr;
    }

    /**
     * Removes a field from the layouts that it is on
     * 
     * @param string $fieldName The name of the field to remove
     */
    public function removeFieldFromLayouts($fieldName)
    {
        require_once 'modules/ModuleBuilder/parsers/ParserFactory.php';
        $GLOBALS ['log']->info(get_class($this) . "->removeFieldFromLayouts($fieldName)");
        $sources = $this->getViewMetadataSources();
        $sources[] = array('type'  => MB_BASICSEARCH);
        $sources[] = array('type'  => MB_ADVANCEDSEARCH);
        $sources[] = array('type'  => MB_POPUPSEARCH);
        //BEGIN SUGARCRM flav=pro ONLY
        $sources = array_merge($sources, $this->getWirelessLayouts());
        //END SUGARCRM flav=pro ONLY
        //BEGIN SUGARCRM flav=ent ONLY
        $sources = array_merge($sources, $this->getPortalLayoutSources());
        //END SUGARCRM flav=ent ONLY

        $GLOBALS['log']->debug(print_r($sources, true));
        foreach ($sources as $name => $defs) {
            // If this module type doesn't support a given metadata type, we will
            // get an exception from getParser()
            try {
                $parser = ParserFactory::getParser($defs['type'], $this->module);
                if ($parser && method_exists($parser, 'removeField') && $parser->removeField($fieldName)) {
                    // don't populate from $_REQUEST, just save as is...
                    $parser->handleSave(false); 
                }
            } catch (Exception $e) {}
        }

        //Remove the fields in subpanel
        $data = $this->getParentModulesOfSubpanel($this->module);
        foreach ($data as $parentModule) {
            // If this module type doesn't support a given metadata type, we will
            // get an exception from getParser()
            try {
                $parser = ParserFactory::getParser(MB_LISTVIEW, $parentModule, null, $this->module);
                if ($parser->removeField($fieldName)) {
                    $parser->handleSave(false);
                }
            } catch (Exception $e) {}
        }
    }

    /**
     * Gets a list of source metadata view types. Used in resetting a module and
     * for the field removal process.
     *
     * @return array
     */
    public function getViewMetadataSources()
    {
        $sources = $this->getViews();
        $sources[] = array('type'  => MB_BASICSEARCH);
        $sources[] = array('type'  => MB_ADVANCEDSEARCH);
        $sources[] = array('type'  => MB_POPUPLIST);
        //BEGIN SUGARCRM flav=pro ONLY
        $sources = array_merge($sources, $this->getWirelessLayouts());
        //END SUGARCRM flav=pro ONLY
        //BEGIN SUGARCRM flav=ent ONLY
        $sources = array_merge($sources, $this->getPortalLayoutSources());
        //END SUGARCRM flav=ent ONLY
        return $sources;
    }

    /**
     * Gets the type for a view
     * 
     * @param string $view The view to get the type from
     * @return string
     */
    public function getViewType($view)
    {
        foreach ($this->sources as $file => $def) {
            if (!empty($def['view']) && $def['view'] == $view && !empty($def['type'])) {
                return $def['type'];
            }
        }

        return $view;
    }
    //BEGIN SUGARCRM flav=ent ONLY

    /**
     * Gets a simple array of source layout types for field deletion
     *
     * @return array
     */
    public function getPortalLayoutSources()
    {
        return array(
            array('type' => MB_PORTALRECORDVIEW),
            array('type' => MB_PORTALLISTVIEW),
        );
    }
    //END SUGARCRM flav=ent ONLY
}
