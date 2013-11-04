<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

require_once('include/api/SugarApi.php');
require_once('modules/ExpressionEngine/formulaHelper.php');

class SugarLogicFunctionsApi extends SugarApi
{
    /**
     * Rest Api Registration Method
     *
     * @return array
     */
    public function registerApiRest()
    {
        $parentApi = array(
            'sugarlogic_functions' => array(
                'reqType' => 'GET',
                'path' => array('ExpressionEngine', 'functions'),
                'pathVars' => array('', ''),
                'method' => 'getSugarLogicFunctions',
                'shortHelp' => 'Retrieve the js for SugarLogic Expressions and Actions',
                'longHelp' => '',
                'noLoginRequired' => true,
                'rawReply' => true,
                'noEtag' => true,
                'ignoreMetaHash' => true,
                'ignoreSystemStatusError' => true,
            ),
        );
        return $parentApi;
    }

    /**
     * Will return the javascript for the Sugar Logic expressions and actions installed on this instance.
     *
     * @param ServiceBase $api
     * @param array       $args
     */
    public function getSugarLogicFunctions($api, $args){
        $useDebug = (inDeveloperMode() || !empty($args['debug']));
        $phpCacheFile = sugar_cached("Expressions/functionmap.php");
        $jsCacheFile = $useDebug ?
            sugar_cached("Expressions/functions_cache_debug.js") :
            sugar_cached('Expressions/functions_cache.js');
        if (SugarAutoLoader::fileExists($phpCacheFile) || !SugarAutoLoader::fileExists($jsCacheFile)) {
            $GLOBALS['updateSilent'] = true;
            include("include/Expressions/updatecache.php");
        }
        $api->setHeader("Content-Type", "application/javascript");
        return sugar_file_get_contents($jsCacheFile);
    }
}
