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

        return true;
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
