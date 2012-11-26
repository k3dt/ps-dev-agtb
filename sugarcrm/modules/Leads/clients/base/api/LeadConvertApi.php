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

require_once('clients/base/api/ModuleApi.php');
require_once('modules/Leads/LeadConvert.php');

class LeadConvertApi extends ModuleApi {

    public function registerApiRest()
    {
        //Extend with test method
        $api= array (
            'convertLead' => array(
                'reqType' => 'POST',
                'path' => array('Leads', '?', 'convert'),
                'pathVars' => array('','leadId',''),
                'method' => 'convertLead',
                'shortHelp' => 'Convert Lead to Account/Contact/Opportunity',
                'longHelp' => 'include/api/html/modules/Leads/LeadsConversionsApi.html#conversions',
            ),
        );

        return $api;
    }

    /**
     * This method handles the /Lead/:id/convert REST endpoint
     *
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
     * @param $args array The arguments array passed in from the API
     * @return Array of worksheet data entries
     * @throws SugarApiExceptionNotAuthorized
     */
    public function convertLead($api, $args)
    {
        //This is just till the opp form is created
        //TODO:  Remove the following lines
        if (isset($args['modules']['Opportunities'])) {
            $args['modules']['Opportunities']['curreny_id']='-99';
            $args['modules']['Opportunities']['amount']='50000';
            $args['modules']['Opportunities']['amount_usdollar']='50000';
        }

        $leadConvert = new LeadConvert($args['leadId']);
        $modules = $this->loadModules($api, $leadConvert->getAvailableModules(), $args['modules']);
        $modules = $leadConvert->convertLead($modules);

        return array (
            'modules' => $this->formatBeans($api, $args, $modules)
        );
    }

    /**
     * This method loads a bean from posted data through api
     *
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
     * @param $module The module name to be loaded/created.
     * @param $data The posted data
     * @return SugarBean The loaded bean
     */
    protected function loadModule($api, $module, $data) {
        if (isset($data['id'])) {
            $moduleDef = array (
                'module' => $module,
                'record' => $data['id']
            );
            $bean = $this->loadBean($api, $moduleDef);
        }
        else {
            $bean = BeanFactory::newBean($module);
            $this->updateBean($bean,$api, $data);
        }
        return $bean;
    }

    /**
     * This method loads an array of beans based on available modules for lead convert
     *
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
     * @param $module Array The modules that will be loaded/created.
     * @param $data The posted data
     * @return Array SugarBean The loaded beans
     */
    protected function loadModules($api, $modulesToConvert, $data) {
        $beans = array();

        foreach ($modulesToConvert as $moduleName) {
            if (!isset($data[$moduleName])) {
                continue;
            }
            $beans[$moduleName] = $this->loadModule($api, $moduleName, $data[$moduleName]);
        }
        return $beans;
    }
}