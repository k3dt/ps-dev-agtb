<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: Administration.php 57528 2010-07-19 01:37:50Z kjing $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
require_once('data/SugarBean.php');
require_once('include/OutboundEmail/OutboundEmail.php');

class Administration extends SugarBean {
    var $settings;
    var $table_name = "config";
    var $object_name = "Administration";
    var $new_schema = true;
    var $module_dir = 'Administration';
    var $config_categories = array(
        // 'mail', // cn: moved to include/OutboundEmail
        'disclosure', // appended to all outbound emails
        'notify',
        'system',
        'portal',
        'proxy',
        'massemailer',
        'ldap',
        'captcha',
        'sugarpdf',
        'base',

            //BEGIN SUGARCRM lic=sub ONLY

        'license',

            //END SUGARCRM lic=sub ONLY
    );
    var $disable_custom_fields = true;
    var $checkbox_fields = Array("notify_send_by_default", "mail_smtpauth_req", "notify_on", 'portal_on', 'skypeout_on', 'system_mailmerge_on', 'proxy_auth', 'proxy_on', 'system_ldap_enabled','captcha_on');

    public function __construct() {
        parent::__construct();

        $this->setupCustomFields('Administration');
        //BEGIN SUGARCRM flav=pro ONLY
        $this->disable_row_level_security =true;
        //END SUGARCRM flav=pro ONLY
    }

    function retrieveSettings($category = FALSE, $clean=false) {
        // declare a cache for all settings
        $settings_cache = sugar_cache_retrieve('admin_settings_cache');

        if($clean) {
            $settings_cache = array();
        }

        // Check for a cache hit
        if(!empty($settings_cache)) {
            $this->settings = $settings_cache;
            if (!empty($this->settings[$category]))
            {
                return $this;
            }
        }

        if ( ! empty($category) ) {
            $query = "SELECT category, name, value FROM {$this->table_name} WHERE category = '{$category}'";
        } else {
            $query = "SELECT category, name, value FROM {$this->table_name}";
        }

        $result = $this->db->query($query, true, "Unable to retrieve system settings");

        if(empty($result)) {
            return NULL;
        }

        while($row = $this->db->fetchByAssoc($result)) {
            if($row['category']."_".$row['name'] == 'ldap_admin_password' || $row['category']."_".$row['name'] == 'proxy_password')
                $this->settings[$row['category']."_".$row['name']] = $this->decrypt_after_retrieve($row['value']);
            else
                $this->settings[$row['category']."_".$row['name']] = $row['value'];
        }
        $this->settings[$category] = true;

        // outbound email settings
        $oe = new OutboundEmail();
        $oe->getSystemMailerSettings();

        foreach($oe->field_defs as $def) {
            if(strpos($def, "mail_") !== false)
                $this->settings[$def] = $oe->$def;
        }

        // At this point, we have built a new array that should be cached.
        sugar_cache_put('admin_settings_cache',$this->settings);
        return $this;
    }

    function saveConfig() {
        //BEGIN SUGARCRM flav=ent ONLY
        $this->retrieveSettings(false, true);
        if(isset($this->settings['portal_on']) && isset($_POST['portal_on']) && (bool)$this->settings['portal_on'] != (bool)$_POST['portal_on']) {
            if(file_exists($cachefile = sugar_cached('modules/Contacts/EditView.tpl'))) {
                unlink($cachefile);
            }

            if(file_exists($cachefile = sugar_cached('modules/Contacts/DetailView.tpl'))) {
                unlink($cachefile);
            }

            if(file_exists($cachefile = sugar_cached('modules/Contacts/form_EmailQCView_Contacts.tpl'))) {
                unlink($cachefile);
            }
        }
        //END SUGARCRM flav!=ent ONLY

        //BEGIN SUGARCRM flav=ent ONLY
        $this->retrieveSettings(false, true);
        if(isset($this->settings['portal_on']) && isset($_POST['portal_on']) && (bool)$this->settings['portal_on'] != (bool)$_POST['portal_on']) {
            if(file_exists($cachefile = sugar_cached('modules/Contacts/EditView.tpl'))) {
                unlink($cachefile);
            }

            if(file_exists($cachefile = sugar_cached('modules/Contacts/DetailView.tpl'))) {
                unlink($cachefile);
            }

            if(file_exists($cachefile = sugar_cached('modules/Contacts/form_EmailQCView_Contacts.tpl'))) {
                unlink($cachefile);
            }
        }
        //END SUGARCRM flav!=ent ONLY

        // outbound email settings
        $oe = new OutboundEmail();

        foreach($_POST as $key => $val) {
            $prefix = $this->get_config_prefix($key);
            if(in_array($prefix[0], $this->config_categories)) {
                if(is_array($val)){
                    $val=implode(",",$val);
                }
                $this->saveSetting($prefix[0], $prefix[1], $val);
            }
            if(strpos($key, "mail_") !== false) {
                if(in_array($key, $oe->field_defs)) {
                    $oe->$key = $val;
                }
            }
        }

        //saving outbound email from here is probably redundant, adding a check to make sure
        //smtpserver name is set.
        if (!empty($oe->mail_smtpserver)) {
            $oe->saveSystem();
        }

        $this->retrieveSettings(false, true);
    }

    /**
     * @param string $category      Category for the config value
     * @param string $key           Key for the config value
     * @param string $value         Value of the config param
     * @param string $platform      Which platform this belongs to (API use only, If platform is empty it will not be returned in the API calls)
     * @return int                  Number of records Returned
     */
    public function saveSetting($category, $key, $value, $platform = '') {
        // platform is always lower case
        $platform = strtolower($platform);
        $result = $this->db->query("SELECT count(*) AS the_count FROM config WHERE category = '{$category}' AND name = '{$key}' AND platform = '{$platform}'");
        $row = $this->db->fetchByAssoc($result);
        $row_count = $row['the_count'];

        if($category."_".$key == 'ldap_admin_password' || $category."_".$key == 'proxy_password')
            $value = $this->encrpyt_before_save($value);

        if( $row_count == 0){
            $result = $this->db->query("INSERT INTO config (value, category, name, platform) VALUES ('$value','$category', '$key', '$platform')");
        }
        else{
            $result = $this->db->query("UPDATE config SET value = '{$value}' WHERE category = '{$category}' AND name = '{$key}' AND platform = '{$platform}'");
        }
        sugar_cache_clear('admin_settings_cache');

        // check to see if category is a module
        if(!empty($platform)) {
            // we have an api call so lets clear out the cache for the module + platform
            global $moduleList;
            if(in_array($category, $moduleList)) {
                $cache_key = "ModuleConfig-" . $category;
                if($platform != "base")  {
                    $cache_key .= $platform;
                }
                sugar_cache_clear($cache_key);
            }
        }

        return $this->db->getAffectedRowCount($result);
    }

    /**
     * Return the config for a specific module.
     *
     * @param string $module        The module we are wanting to get the config for
     * @param string $platform      The platform we want to get the data back for
     * @return array
     */
    public function getConfigForModule($module, $platform = 'base') {
        // platform is always lower case
        $platform = strtolower($platform);

        $cache_key = "ModuleConfig-" . $module;
        if($platform != "base")  {
            $cache_key .= $platform;
        }

        // try and see if there is a cache for this
        $moduleConfig = sugar_cache_retrieve($cache_key);

        if(!empty($moduleConfig)) {
            return $moduleConfig;
        }

        $sql = "SELECT name, value FROM config WHERE category = '{$module}'";
        if($platform != "base") {
            // if the platform is not base, we need to order it so the platform we are looking for overrides any base values
            $sql .= "and platform in ('base', '{$platform}') ORDER BY CASE WHEN platform='base' THEN 0
                        WHEN platform='{$platform}' THEN 1 ELSE 2 END ASC";
        } else {
            $sql .= " and platform = '{$platform}'";
        }

        $result = $this->db->query($sql);

        $moduleConfig = array();
        while($row = $this->db->fetchByAssoc($result)) {
            $temp = json_decode(html_entity_decode(stripslashes($row['value'])), true);
            if (is_null($temp)) {
                $temp = $row['value'];
            }

            $moduleConfig[$row['name']] = $temp;
        }

        if(!empty($moduleConfig)) {
            sugar_cache_put($cache_key, $moduleConfig);
        }

        return $moduleConfig;
    }

    function get_config_prefix($str) {
        return Array(substr($str, 0, strpos($str, "_")), substr($str, strpos($str, "_")+1));
    }
}

