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

require_once('include/api/ModuleApi.php');

class ForecastsConfigApi extends ModuleApi {

    public function __construct()
    {

    }

    public function registerApiRest()
    {
        $parentApi = parent::registerApiRest();
        //Extend with test method
        $parentApi= array (
            'config' => array(
                'reqType' => 'GET',
                'path' => array('Forecasts','config'),
                'pathVars' => array('',''),
                'method' => 'config',
                'shortHelp' => 'forecasts config',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastConfigApi.html#config',
                'noLoginRequired' => true,
            ),
            'configSave' => array(
                'reqType' => 'POST',
                'path' => array('Forecasts','config'),
                'pathVars' => array('',''),
                'method' => 'configSave',
                'shortHelp' => 'save forecasts config',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastConfigApi.html#configSave',
            ),
        );
        return $parentApi;
    }

    /**
     * Returns the config settings for the forecasts module
     * @param $api
     * @param $args
     */
    public function config($api, $args) {
        $adminBean = BeanFactory::getBean("Administration");
        if (!empty($args['module'])) {
            $data = $adminBean->getConfigForModule($args['module']);
        } else {
            return;
        }

        foreach($data as $key => $setting) {
            $temp = json_decode(html_entity_decode(stripslashes($setting)));
            if ($temp !== NULL) {
                $data[$key] = $temp;
            }
        }

        return $data;
    }

    /**
     * Save function for the forecast config settings
     * @param $api
     * @param $args
     */
    public function configSave($api, $args) {
        $admin = BeanFactory::getBean('Administration');

        foreach ($args as $name => $value) {
            if($name != 'module' && $name != '__sugar_url' && is_array($value)) {
                $admin->saveSetting('Forecasts', $name, json_encode($value), 'base');
            } else {
                $admin->saveSetting('Forecasts', $name, $value, 'base');
            }
        }
    }

}
