<?php
 if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/
/**
 * Merge sidecar defs for Sugar 7
 */
class SugarUpgradeMerge7 extends UpgradeScript
{
    public $order = 400;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        if(version_compare($this->from_version, '7.0', '<')) {
            // This is for 7->7 upgrades
            return;
        }

        if(empty($this->context['new_source_dir'])) {
            $this->log("**** Merge skipped - no new source dir");
            return;
        }

        /* look for views that:
         * 1. Have custom view
         * 2. Changed between old and new
         */
        foreach(glob("modules/*", GLOB_ONLYDIR) as $dir) {
            if(!is_dir("$dir/clients/") || !is_dir("custom/$dir/clients") || !is_dir("{$this->context['new_source_dir']}/$dir/clients")) {
                // either does not have clients or is not customized
                continue;
            }
            $module_name = substr($dir, 8); // cut off modules/
            $this->log("Checking $dir");
            foreach(glob("$dir/clients/*/views/*/*.php") as $phpfile) {
                if(!file_exists("{$this->context['new_source_dir']}/$phpfile")) {
                    // no longer in the source - skip
                    continue;
                }
                if(!file_exists("custom/$phpfile")) {
                    // not customized - skip
                    continue;
                }
                $this->checkFile($phpfile);
            }
        }
    }

    /**
     * Load view file
     * @param string $filename
     * @param string $module_name
     * @param string $platform
     * @param string $viewname
     * @return NULL|array
     */
    protected function loadFile($filename, $module_name, $platform, $viewname)
    {
        $viewdefs = array();
        include $filename;
        if(empty($viewdefs) || empty($viewdefs[$module_name][$platform]['view'][$viewname]['panels'])) {
            // we do not handle non-panel views for now
            return null;
        }
        return $viewdefs;
    }

    /**
     * Check if the file needs to be merged
     * @param string $filename
     */
    protected function checkFile($filename)
    {
        list($modules, $module_name, $clients, $platform, $views, $viewname) = explode(DIRECTORY_SEPARATOR, $filename);

        $old_viewdefs = $this->loadFile($filename, $module_name, $platform, $viewname);
        $new_viewdefs = $this->loadFile("{$this->context['new_source_dir']}/$filename", $module_name, $platform, $viewname);
        $custom_viewdefs =  $this->loadFile("custom/$filename", $module_name, $platform, $viewname);

        if(empty($old_viewdefs) || empty($new_viewdefs) || empty($custom_viewdefs)) {
            // defs missing - can't do anything here
            return;
        }
        if($old_viewdefs[$module_name][$platform]['view'][$viewname]['panels'] == $new_viewdefs[$module_name][$platform]['view'][$viewname]['panels']
             || $custom_viewdefs[$module_name][$platform]['view'][$viewname]['panels'] == $new_viewdefs[$module_name][$platform]['view'][$viewname]['panels']) {
            // no changes to handle
            return;
        }
        $this->log("Queued for merge: $filename");
        $this->upgrader->state['for_merge'][$filename] = $old_viewdefs;
    }
}
