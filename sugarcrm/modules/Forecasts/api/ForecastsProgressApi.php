<?php
if ( !defined('sugarEntry') || !sugarEntry ) {
	die('Not A Valid Entry Point');
}
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

require_once('modules/Forecasts/ForecastOpportunities.php');

class ForecastsProgressApi extends ModuleApi
{
	protected $api;
	protected $args;
	
	protected $closed;
	protected $opportunitiesInPipeline;
	protected $user_id;
	protected $timeperiod_id;
	protected $should_rollup;
	protected $quotaData;
	protected $opportunity;
    protected $sales_stage_lost;
    protected $sales_stage_won;

	public function __construct()
	{
	}


	// All requests will need to be filtered by time period, forecasts, and direct (true/false)
	public function registerApiRest()
	{
		$parentApi = parent::registerApiRest();

		//Extend with test method
		$parentApi = array(
			'progress' => array(
				'reqType'   => 'GET',
				'path'      => array('Forecasts', 'progress', '?', '?', '?'),
				'pathVars'  => array('', '','user_id','timeperiod_id','should_rollup'),
				'method'    => 'progress',
				'shortHelp' => 'Progress data',
				'longHelp'  => 'include/api/html/modules/Forecasts/ForecastProgressApi.html#progress',
			)
        );
		return $parentApi;
	}

    /**
     * retreives the number of opportunities set to be used in this forecast period
     *
     * @param null $user_id
     * @param null $timeperiod_id
     * @param bool $should_rollup
     * @return mixed
     */
    protected function getPipelineOpportunityCount( $user_id = NULL, $timeperiod_id = NULL, $should_rollup=false, $excluded_sales_stages_won, $excluded_sales_stages_lost  )
   	{
   		global $current_user;

        $where = "";

   		if ( is_null($user_id) ) {
   			$user_id = $current_user->id;
   		}
   		if ( is_null($timeperiod_id) ) {
   			$timeperiod_id = TimePeriod::getCurrentId();
   		}

        if ($should_rollup and !is_null($user_id)) {
           $where .= " opportunities.assigned_user_id in (SELECT id from users where reports_to_id = '$user_id')";
        } else if ( !is_null($user_id) ) {
           $where .= " opportunities.assigned_user_id='$user_id'";
        }

   		$where .= " AND opportunities.timeperiod_id = " . $GLOBALS['db']->quoted($timeperiod_id);


       if(count($excluded_sales_stages_won)) {
           foreach($excluded_sales_stages_won as $exclusion)
           {
               $where .= " AND opportunities.sales_stage != " . $GLOBALS['db']->quoted($exclusion);
           }
       }
      if(count($excluded_sales_stages_lost)) {
           foreach($excluded_sales_stages_lost as $exclusion)
           {
               $where .= " AND opportunities.sales_stage != " . $GLOBALS['db']->quoted($exclusion);
           }
       }
        $where .= " AND opportunities.deleted = 0";

   		$query = $this->opportunity->create_list_query(NULL, $where);
   		$query = $this->opportunity->create_list_count_query($query);

   		$result = $GLOBALS['db']->query($query);
   		$row = $GLOBALS['db']->fetchByAssoc($result);
   		$opportunitiesCount = $row['c'];

   		return $opportunitiesCount;
   	}


    /**
   	 * @param null $user_id
   	 * @param null $timeperiod_id
   	 *
   	 * @return int
   	 */
   	public function getClosedAmount( $user_id = NULL, $timeperiod_id = NULL, $should_rollup = false, $sales_stage_won=array() )
   	{
   		$amountSum = 0;
   		$where     = "";

        if ($should_rollup and !is_null($user_id)) {
            $where .= " opportunities.assigned_user_id in (SELECT id from users where reports_to_id = '$user_id')";
        } else if ( !is_null($user_id) ) {
            $where .= " opportunities.assigned_user_id='$user_id'";
   		}

   		if ( !is_null($timeperiod_id) ) {
   			$where .= " AND opportunities.timeperiod_id = " . $GLOBALS['db']->quoted($timeperiod_id);
   		}
       if(count($sales_stage_won)) {
           $where .= " AND opportunities.sales_stage in ( '";
           $where .= join("','", $sales_stage_won) . "')";
       }

   		$query  = $this->opportunity->create_list_query(NULL, $where);
   		$result = $GLOBALS['db']->query($query);

   		while ( $row = $GLOBALS['db']->fetchByAssoc($result) ) {
   			$amountSum += $row["amount"];
   		}

   		return $amountSum;
   	}


	/**
	 * Load data for API request.
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	protected function loadProgressData( $args )
	{
        $admin = new Administration();
        $admin->retrieveSettings();
        $this->user_id = (array_key_exists("user_id", $args) ? $args["user_id"] : $GLOBALS["current_user"]->id);

		$this->timeperiod_id = (array_key_exists("timeperiod_id", $args) ? $args["timeperiod_id"] : TimePeriod::getCurrentId());
		$this->should_rollup = (array_key_exists("should_rollup", $args) ? $args["should_rollup"] : User::isManager($this->user_id));
		$this->sales_stage_won = json_decode(html_entity_decode($admin->settings["base_sales_stage_won"]));
        $this->sales_stage_lost = json_decode(html_entity_decode($admin->settings["base_sales_stage_lost"]));

        if ( !is_bool($this->should_rollup) ) {
			$this->should_rollup = $this->should_rollup == 1 ? TRUE : FALSE;
		}
        if($this->should_rollup) {
            $this->opportunity = new Opportunity();
            $this->quotaData = array('amount' => 0);
            $this->closed      = $this->getClosedAmount($this->user_id, $this->timeperiod_id, $this->should_rollup, $this->sales_stage_won);
            $this->opportunitiesInPipeline = $this->getPipelineOpportunityCount($this->user_id, $this->timeperiod_id, $this->should_rollup, $this->sales_stage_won, $this->sales_stage_lost);
        } else {
            $this->opportunitiesInPipeline = 0;
            $this->closed = 0;
            $quota = new Quota();
          	$this->quotaData = $quota->getRollupQuota($this->timeperiod_id, $this->user_id, $this->should_rollup);

        }
	}


	/**
	 * Formats the return values for bestToLikely, closedToBest, etc.
	 * 
	 * @param $caseValue
	 * @param $stageValue
	 *
	 * @return array
	 */
	protected function formatCaseToStage($caseValue, $stageValue)
	{
		$percent = 0;
		if ( $caseValue <= $stageValue ) {
			$amount = $stageValue - $caseValue;
			$isAbove = false;
		}
		else {
			$amount = $caseValue - $stageValue;
			$isAbove = true;
		}

		if ( !is_null($stageValue) ) {
			$percent = $stageValue != 0 ? $caseValue / $stageValue : 0;
		}
		
		return array(
			"amount"  => $amount,
			"percent" => $percent,
			"above"   => $isAbove,
		);
	}

	public function progress( $api, $args )
	{
		$this->loadProgressData($args);

		$progressData = array(
            "quota_amount"      => $this->quotaData["amount"],
            "closed_amount"     => $this->closed,
            "opportunities"     => $this->opportunitiesInPipeline
		);

		return $progressData;
	}
}
