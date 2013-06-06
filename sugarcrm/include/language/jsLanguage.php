<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 * 
 * $Id: jsLanguage.php 32812 2008-03-14 18:33:02Z roger $
 * Description:  Creates javascript versions of language files
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
 
class jsLanguage {
    
    /**
     * Creates javascript versions of language files
     */
    function jsLanguage() {
    }
    
    function createAppStringsCache($lang = 'en_us') {
        // cn: bug 8242 - non-US langpack chokes
        $app_strings = return_application_language($lang);
        $app_list_strings = return_app_list_strings_language($lang);
        
        $json = getJSONobj();
        $app_list_strings_encoded = $json->encode($app_list_strings);
        $app_strings_encoded = $json->encode($app_strings);
        
        $str = <<<EOQ
SUGAR.language.setLanguage('app_strings', $app_strings_encoded);
SUGAR.language.setLanguage('app_list_strings', $app_list_strings_encoded);
EOQ;
        
        $cacheDir = create_cache_directory('jsLanguage/');
        if($fh = @sugar_fopen($cacheDir . $lang . '.js', "w")){
            fputs($fh, $str);
            fclose($fh);
        }
    }
    
    function createModuleStringsCache($moduleDir, $lang = 'en_us', $return = false) {
        global $mod_strings;
        $json = getJSONobj();

        // cn: bug 8242 - non-US langpack chokes
        // Allows for modification of mod_strings by individual modules prior to 
        // sending down to JS
        if (empty($mod_strings)) {
            $mod_strings = return_module_language($lang, $moduleDir);
        }

        $mod_strings_encoded = $json->encode($mod_strings);
        $str = "SUGAR.language.setLanguage('" . $moduleDir . "', " . $mod_strings_encoded . ");";
        
        $cacheDir = create_cache_directory('jsLanguage/' . $moduleDir . '/');
        
        if($fh = @fopen($cacheDir . $lang . '.js', "w")){
            fputs($fh, $str);
            fclose($fh);
        }

        if($return) {
            return $str;
        }
    }

}
?>