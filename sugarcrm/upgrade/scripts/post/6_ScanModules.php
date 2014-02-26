<?php
 if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/**
 * Scan all modules and find which ones are MB modules and which ones are
 * new non-MB modules. Move new non-MB modules into BWC mode.
 */
require_once 'ModuleInstall/ModuleInstaller.php';

class SugarUpgradeScanModules extends UpgradeScript
{
    public $order = 6000;
    public $version = "7.0.0";
    public $type = self::UPGRADE_CUSTOM;

    /**
     * MD5 sums from files.md5
     * @var array
     */
    protected $md5_files;

    protected $bwcModules = array();

    /**
     * Is $module a new module or standard Sugar module?
     * @param string $module
     * @return boolean $module is new?
     */
    protected function isNewModule($module)
    {
        if(empty($this->beanList[$module])) {
            // absent from module list, not an actual module
            return false;
        }
        $object = $this->beanList[$module];
        if(empty($this->beanFiles[$object])) {
            // no bean file - check directly
            foreach(glob("modules/$module/*") as $file) {
                // if any file from this dir mentioned in md5 - not a new module
                if(!empty($this->md5_files["./$file"])) {
                    return false;
                }
            }
            return true;
        }

        if(empty($this->md5_files["./".$this->beanFiles[$object]])) {
            // no mention of the bean in files.md5 - new module
            return true;
        }

        return false;
    }

    /**
     * Extract hook filenames from logic hook file and put them into hook files list
     * @param string $hookfile
     * @param array &$hook_files
     */
    protected function extractHooks($hookfile, &$hook_files)
    {
        $hook_array = array();
        if(!is_readable($hookfile)) {
            return;
        }
        include $hookfile;
        if(empty($hook_array)) {
            return;
        }
        foreach($hook_array as $hooks) {
            foreach($hooks as $hook) {
                $hook_files[$hook[2]] = true;
            }
        }
    }

    /**
     * Check if views dir was created by file template
     * @param string $view_dir
     * @return boolean
     */
    protected function checkViewsDir($view_dir)
    {
        foreach(glob("$view_dir/*") as $file) {
            // for now we allow only view.edit.php
            if(basename($file) != 'view.edit.php') {
                $this->log("Unknown file $view_dir/$file");
                return false;
            }
            $data = file_get_contents($file);
            // start with first {
            $data= substr($data, strpos($data, '{'));
            // drop function names
            $data = preg_replace('/function\s[<>_\w]+/', '', $data);
            // drop whitespace
            $data = preg_replace('/\s+/', '', $data);
            /* File data is:
             * {(){parent::ViewEdit();}(){if(isset($this->bean->id)){$this->ss->assign("FILE_OR_HIDDEN","hidden");if(empty($_REQUEST['isDuplicate'])||$_REQUEST['isDuplicate']=='false'){$this->ss->assign("DISABLED","disabled");}}else{$this->ss->assign("FILE_OR_HIDDEN","file");}parent::display();}}?>
             * md5 is: c8251f6b50e3e814135c936f6b5292eb
             */
            if(md5($data) !== 'c8251f6b50e3e814135c936f6b5292eb') {
                $this->log("Bad md5 for $file");
                return false;
            }
        }
        return true;
    }

    /**
     * Is this a pure ModuleBuilder module?
     * @param string $module_dir
     * @return boolean
     */
    protected function isMBModule($module_dir)
    {
        $module_name = substr($module_dir, 8); // cut off modules/
        if(empty($this->beanList[$module_name])) {
            // if this is not a deployed one, don't bother
            return false;
        }
        $bean = $this->beanList[$module_name];
        if(empty($this->beanFiles[$bean])) {
            return false;
        }
        $mbFiles = array("Dashlets", "Menu.php", "language", "metadata", "vardefs.php", "clients", "workflow");
        $mbFiles[] = basename($this->beanFiles[$bean]);
        $mbFiles[] = pathinfo($this->beanFiles[$bean], PATHINFO_FILENAME)."_sugar.php";

        // to make checks faster
        $mbFiles = array_flip($mbFiles);

        $hook_files = array();
        $this->extractHooks("custom/$module_dir/logic_hooks.php", $hook_files);
        $this->extractHooks("custom/$module_dir/Ext/LogicHooks/logichooks.ext.php", $hook_files);

        // For now, the check is just checking if we have any files
        // in the directory that we do not recognize. If we do, we
        // put the module in BC.
        foreach(glob("$module_dir/*") as $file) {
            if(isset($hook_files[$file])) {
                // logic hook files are OK
                continue;
            }
            if(basename($file) == "views") {
                // check views separately because of file template that has view.edit.php
                if(!$this->checkViewsDir("$module_dir/views")) {
                    $this->log("Unknown file views present - $module_name is not MB module");
                    return false;
                } else {
                    continue;
                }
            }
            if(!isset($mbFiles[basename($file)])) {
                // unknown file, not MB module
                $this->log("Unknown file $file - $module_name is not MB module");
                return false;
            }
        }
        // files that are OK for custom:
        $mbFiles['Ext'] = true;
        $mbFiles['logic_hooks.php'] = true;

        // now check custom/ for unknown files
        foreach(glob("custom/$module_dir/*") as $file) {
            if(isset($hook_files[$file])) {
                // logic hook files are OK
                continue;
            }
            if(!isset($mbFiles[basename($file)])) {
                // unknown file, not MB module
                $this->log("Unknown file $file - $module_name is not MB module");
                return false;
            }
        }
        $badExts = array("ActionViewMap", "ActionFileMap", "ActionReMap", "EntryPointRegistry",
            "FileAccessControlMap", "WirelessModuleRegistry");
        $badExts = array_flip($badExts);
        // Check Ext for any "dangerous" extentsions
        foreach(glob("custom/$module_dir/Ext/*") as $extdir) {
            if(isset($badExts[basename($extdir)])) {
                $extfiles = glob("$extdir/*");
                if(!empty($extfiles)) {
                    $this->log("Extension dir $extdir detected - $module_name is not MB module");
                    return false;
                }
            }
        }

        if(!$this->checkVardefs($module_name, $bean))
        {
            return false;
        }

        return true;
    }

    /**
     * Check vardefs for module
     * @param string $module
     * @param string $object
     * @return boolean true if vardefs OK, false if module needs to be BWCed
     */
    protected function checkVardefs($module, $object)
    {
        if(empty($GLOBALS['dictionary'][$object]['fields'])) {
            $this->log("Failed to load vardefs for $module:$object");
            return true;
        }
        $status = true;
        foreach($GLOBALS['dictionary'][$object]['fields'] as $key => $value) {
            if(empty($value['name']) || $key != $value['name']) {
                $this->log("Bad vardefs - key $key, name {$value['name']}");
                $status = false;
            }
            if(!empty($value['type']) && ($value['type'] == 'enum' || $value['type'] == 'multienum')
                && !empty($value['function']['returns']) && $value['function']['returns'] == 'html'
            ) {
               // found html functional enum
                $this->log("Vardef $key has HTML function");
                $status = false;
            }
        }

        return $status;
    }

    /**
     * Rebuild everything we need after we changed the bwc list
     */
    protected function rebuild()
    {
        $this->cleanCaches();
        $mi = new ModuleInstaller();
        $mi->silent = true;
        $mi->rebuild_modules();
    }

    public function run()
    {
        if(version_compare($this->from_version, '7.0', '>=')) {
            // no need to run this on 7
            return;
        }

        $md5_string = array();
        if(!file_exists('files.md5')) {
            $this->fail("files.md5 not found");
        }
        require 'files.md5';
        $this->md5_files = $md5_string;

        require 'include/modules.php';
        $this->beanList = $beanList;
        $this->beanFiles = $beanFiles;

        $modules = glob("modules/*", GLOB_ONLYDIR);
        foreach($modules as $module) {
            if(isModuleBWC($module)) {
                // it's already bwc, don't bother it
                continue;
            }
            $module_name = substr($module, 8); // cut off modules/
            if($this->isNewModule($module_name)) {
                if(!$this->isMBModule($module)) {
                    // new and not MB - list as BWC
                    $this->log("Setting $module_name as BWC module");
                    // keep list of modules we BWC'ed in state so we could tell the user
                    $this->upgrader->state['bwcModules'][] = $module_name;
                    $this->bwcModules[] = $module_name;
                } else {
                    $mbModules[] = $module_name;
                }
            }
        }
        if(!empty($mbModules)) {
            $this->upgrader->state['MBModules'] = $mbModules;
        }

        if(!empty($this->bwcModules)) {
            $data = "<?php \n/* This file was generated by Sugar Upgrade */\n";
            foreach($this->bwcModules as $module) {
                $data .= '$bwcModules[] = \''.addslashes($module)."';\n";
                // update current list, we may need it for later scripts
                $GLOBALS['bwcModules'][] = $module;
            }
            $this->putFile("custom/Extension/application/Ext/Include/upgrade_bwc.php", $data);
            $this->rebuild();
        }
    }
}
