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

require_once('Modules/Forecasts/ForecastOpportunities.php');

class ForecastsProgressApi extends ModuleApi {

    public function __construct()
    {

    }
	
	// All requests will need to be filtered by time period, forecasts, and direct (true/false)
    public function registerApiRest()
    {
        $parentApi = parent::registerApiRest();
        //Extend with test method
        $parentApi= array (
            'chart' => array(
                'reqType' => 'GET',
                'path' => array('Forecasts','progress'),
                'pathVars' => array('',''),
                'method' => 'progress',
                'shortHelp' => 'Progress data',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastProgressApi.html#progress',
            ),
        );
        return $parentApi;
    }

    public function progress($api, $args) {
		
        // Just a placeholder for now
        $progressData = array(
			"quota" => array(
				"total" => $this->getQuota($api, $args),
				"likely" => array(
					"percent" => 0.7,
					"current" => 123000,
				),
				"best" => array(
					"percent" => 0.3,
					"current" => 700000,
				),
			),
			"closed" => array(
				"total" => 123,
				"likely" => array(
					"percent" => 0.7,
					"current" => 123000,
				),
				"best" => array(
					"percent" => 0.3,
					"current" => 700000,
				),
			),
			"opportunities" => 123,
			"revenue" => 321,
			"pipelineSize" => 2,
		);
		
		return $progressData;
    }
	
	public function getQuota($api, $args) {
		$user_id = ( array_key_exists("userId", $args) ? $args["userId"] : $GLOBALS["current_user"]->id );
		$timeperiod_id = ( array_key_exists("timePeriodId", $args) ? $args["timePeriodId"] : TimePeriod::getCurrentId() );
		$should_rollup = ( array_key_exists("shouldRollup", $args) ? $args["shouldRollup"] : 1 );
		$should_rollup = $should_rollup == 1 ? TRUE : FALSE;
		
		$quota = new Quota();
		$data = $quota->getRollupQuota($timeperiod_id, $user_id, $should_rollup);
		return $data["amount"];
	}

}
