<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
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

/**
 * This class is here to provide functions to easily call in to the individual module api helpers
 */
class ApiHelper
{
    static $moduleHelpers = array();

    /**
     * This method looks up the helper class for the bean and will provide the default helper
     * if there is not one defined for that particular bean
     *
     * @param $api ServiceBase The API that will be associated to this helper class
     *                         This is used so the formatting functions can handle different
     *                         API's with varying formatting requirements.
     * @param $bean SugarBean Grab the helper module for this bean
     * @returns SugarBeanApiHelper A API helper class for beans
     */
    public static function getHelper(ServiceBase $api, SugarBean $bean) {
        $module = $bean->module_dir;
        if ( !isset(self::$moduleHelpers[$module]) ) {

            if (SugarAutoLoader::requireWithCustom('modules/'.$module.'/'.$module.'ApiHelper.php')) {
                $moduleHelperClass = SugarAutoLoader::customClass($module.'ApiHelper');
            } else {
                SugarAutoLoader::requireWithCustom('data/SugarBeanApiHelper.php');
                $moduleHelperClass = SugarAutoLoader::customClass('SugarBeanApiHelper');
            }

            self::$moduleHelpers[$module] = new $moduleHelperClass($api);
        }

        $moduleHelperClass = self::$moduleHelpers[$module];
        return $moduleHelperClass;
    }
}
