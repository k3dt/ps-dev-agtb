<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
require_once 'include/MetaDataManager/MetaDataManager.php';
require_once 'modules/MySettings/TabController.php';
require_once 'modules/ModuleBuilder/Module/SugarPortalBrowser.php';

class MetaDataManagerPortal extends MetaDataManager
{

    /**
     * Override to replace logo url by portal logo url
     *
     * @param ServiceBase $api
     * @param array $args
     * @return array
     */
    public function getPublicMetadata(ServiceBase $api, array $args)
    {
        $meta = parent::getPublicMetadata($api, $args);
        $meta['logo_url'] = $this->loadPortalLogoUrl();
        $meta['_hash'] = $this->hashChunk($meta);
        return $meta;
    }

    /**
     * Override to allow only Portal modules
     * Override to replace logo url by portal logo url
     *
     * @param ServiceBase $api
     * @param array $args
     * @return array
     */
    protected function getAllMetadata($args = array(), $buildCache = true) 
    {
        $portalModuleList = $this->findPortalModules();
        if (!empty($args['module_filter'])) {
            //If need be, update module filter to get intersection with Portal enabled modules
            $intersection = array_intersect($portalModuleList, explode(',', $args['module_filter']));
            if (!empty($intersection)) { //If we set filter to empty list, then we'd load ALL metadata. (NO.)
                $portalModuleList = $intersection;
            }
        }
        $args['module_filter'] = implode(',', $portalModuleList);
        $meta = parent::getAllMetadata($args, $buildCache);
        $meta['logo_url'] = $this->loadPortalLogoUrl();
        $meta['_hash'] = $this->hashChunk($meta);
        return $meta;
    }

    /**
     * Find all modules with Portal metadata
     * @return array List of Portal module names
     */
    public function findPortalModules()
    {
        $modules = array();
        foreach (SugarAutoLoader::getDirFiles("modules", true) as $mdir) {
            // strip modules/ from name
            $mname = substr($mdir, 8);
            if (SugarAutoLoader::fileExists("$mdir/clients/portal/")) {
                $modules[] = $mname;
            }
        }
        return $modules;
    }

    /**
     * Gets configs
     * 
     * @return array
     */
    protected function getConfigs() {
        $configs = array();
        $admin = new Administration();
        $admin->retrieveSettings();
        foreach($admin->settings AS $setting_name => $setting_value) {
            if(stristr($setting_name, 'portal_')) {
                $key = str_replace('portal_', '', $setting_name);
                $configs[$key] = json_decode(html_entity_decode($setting_value),true);
            }
        }
        
        return $configs;
    }


    /**
     * Fills in additional app list strings data as needed by the client
     * 
     * @param array $public Public app list strings
     * @param array $main Core app list strings
     * @return array
     */
    protected function fillInAppListStrings(Array $public, Array $main) {
        $public['countries_dom'] = $main['countries_dom'];
        $public['state_dom'] = $main['state_dom'];
        
        return $public;
    }

    /**
     * Gets the module list for the current user
     * 
     * @return array The list of modules for portal
     */
    public function getUserModuleList() {
        // Use SugarPortalBrowser to get the portal modules that would appear
        // in Studio
        $pb = new SugarPortalBrowser();
        $pb->loadModules();
        
        // Now that the portal modules are loaded, cross check them with the 
        // visible tabs array for the current user
        $controller = new TabController();
        $ret = array_intersect_key($controller->get_user_tabs($this->getCurrentUser()), $pb->modules);

        // Needed for portal
        $ret['Home'] = true;
        return array_keys($ret);
    }

    /**
     * Retrieves the portal logo if defined, otherwise the company logo url
     *
     * @return string url of the portal logo
     */
    protected function loadPortalLogoUrl() {
        global $sugar_config;
        $config = $this->getConfigs();
        if (!empty($config['logoURL'])) {
            return $config['logoURL'];
        } else {
            $themeObject = SugarThemeRegistry::current();
            return $sugar_config['site_url'] . '/' . $themeObject->getImageURL('company_logo.png');
        }
    }

    /**
     * Load Portal specific metadata (heavily pruned to only show modules enabled for Portal)
     * @return array Portal metadata
     */
    protected function loadMetadata($args = array())
    {
        $data = parent::loadMetadata($args);

        if (!empty($data['modules'])) {
            foreach ($data['modules'] as $modKey => $modMeta) {
                if (!empty($modMeta['isBwcEnabled'])) {
                    // portal has no concept of bwc so get rid of it
                    unset($data['modules'][$modKey]['isBwcEnabled']);
                }
            }
        }

        // Rehash the hash
        $data['_hash'] = $this->hashChunk($data);
        return $data;
    }
}
