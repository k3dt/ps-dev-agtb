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
    const STAGE_CLOSED_WON  = 'Closed Won';
   	const STAGE_CLOSED_LOST = 'Closed Lost';

	protected $api;
	protected $args;
	
	protected $closed;
	protected $forecastData;
	protected $opportunitiesInPipeline;
	protected $user_id;
	protected $user;
	protected $timeperiod_id;
	protected $revenueInPipeline;
    protected $likelyAmount;
	protected $should_rollup;
	protected $quotaData;
    protected $opportunity;


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
				'path'      => array('Forecasts', 'progress'),
				'pathVars'  => array('', ''),
				'method'    => 'progress',
				'shortHelp' => 'Progress data',
				'longHelp'  => 'include/api/html/modules/Forecasts/ForecastProgressApi.html#progress',
			),
            'closed' => array(
         				'reqType'   => 'GET',
         				'path'      => array('Forecasts', 'closed'),
         				'pathVars'  => array('', ''),
         				'method'    => 'closed',
         				'shortHelp' => 'Closed data',
         				'longHelp'  => 'include/api/html/modules/Forecasts/ForecastProgressApi.html#closed',
         			),
        );
		return $parentApi;
	}


    protected function getPipelineOpportunityCount( $user_id = NULL, $timeperiod_id = NULL, $should_rollup=false  )
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

   		$where .= " AND opportunities.timeperiod_id = " . $GLOBALS['db']->quoted($timeperiod_id)
   				. " AND opportunities.sales_stage != " . $GLOBALS['db']->quoted(Opportunity::STAGE_CLOSED_WON)
   				. " AND opportunities.sales_stage != " . $GLOBALS['db']->quoted(Opportunity::STAGE_CLOSED_LOST)
   				. " AND opportunities.deleted = 0";

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
   	public function getClosedAmount( $user_id = NULL, $timeperiod_id = NULL, $should_rollup = false )
   	{
   		$amountSum = 0;
   		$where     = "opportunities.sales_stage='" . Opportunity::STAGE_CLOSED_WON . "'";

        if ($should_rollup and !is_null($user_id)) {
            $where .= " AND opportunities.assigned_user_id in (SELECT id from users where reports_to_id = '$user_id')";
        } else if ( !is_null($user_id) ) {
            $where .= " AND opportunities.assigned_user_id='$user_id'";
   		}

   		if ( !is_null($timeperiod_id) ) {
   			$where .= " AND opportunities.timeperiod_id='$timeperiod_id'";
   		}

   		$query  = $this->opportunity->create_list_query(NULL, $where);
   		$result = $GLOBALS['db']->query($query);

   		while ( $row = $GLOBALS['db']->fetchByAssoc($result) ) {
   			$amountSum += $row["amount"];
   		}

   		return $amountSum;
   	}

    /**
     * pulls the best amount and sums it from the worksheet
     *
     * @param null $user_id
     * @param null $timeperiod_id
     */
    protected function getManagerBestAmount( $user_id = NULL, $timeperiod_id = NULL)
    {
        global $current_user;

        $db = DBManagerFactory::getInstance();

        if ( is_null($user_id) ) {
      		$user_id = $current_user->id;
        }

        $worksheet_query = "select sum(" . db_convert("best_case","IFNULL",array(0)).") best_value";
        $worksheet_query .= " from worksheet wt ";
        $worksheet_query .= " LEFT JOIN users us ON wt.related_id=us.id";
        $worksheet_query .= " where wt.related_forecast_type = 'Direct'";
        $worksheet_query .= " AND wt.timeperiod_id = " . $GLOBALS['db']->quoted($timeperiod_id);
        $worksheet_query .= " AND wt.user_id = " . $GLOBALS['db']->quoted($user_id);
        $worksheet_query .= " AND (us.id = " . $db->quoted($user_id);
        $worksheet_query .= " OR us.reports_to_id = " . $db->quoted($user_id) . " ) ";
        $worksheet_query .= " and wt.deleted=0";

        $worksheet_result = $db->query($worksheet_query);
        $worksheet_data =$db->fetchByAssoc($worksheet_result);
        if (!empty($worksheet_data['best_value'])) {
            return $worksheet_data['best_value'];
        }

        return 0;
    }

    /**
     * pulls the likely amount and sums it from the worksheet
     *
     * @param null $user_id
     * @param null $timeperiod_id
     */
    protected function getManagerLikelyAmount( $user_id = NULL, $timeperiod_id = NULL)
    {
        global $current_user;

        $db = DBManagerFactory::getInstance();

        if ( is_null($user_id) ) {
      		$user_id = $current_user->id;
        }

        $worksheet_query = "select sum(" . db_convert("likely_case","IFNULL",array(0)).") likely_value";
        $worksheet_query .= " from worksheet wt ";
        $worksheet_query .= " LEFT JOIN users us ON wt.related_id=us.id";
        $worksheet_query .= " where wt.related_forecast_type = 'Direct'";
        $worksheet_query .= " AND wt.timeperiod_id = " . $GLOBALS['db']->quoted($timeperiod_id);
        $worksheet_query .= " AND wt.user_id = " . $GLOBALS['db']->quoted($user_id);
        $worksheet_query .= " AND (us.id = " . $db->quoted($user_id);
        $worksheet_query .= " OR us.reports_to_id = " . $db->quoted($user_id) . " ) ";
        $worksheet_query .= " and wt.deleted=0";

        $worksheet_result = $db->query($worksheet_query);
        $worksheet_data =$db->fetchByAssoc($worksheet_result);
        if (!empty($worksheet_data['likely_value'])) {
            return $worksheet_data['likely_value'];
        }

        return 0;
    }

       /**
      	 * Get the total amount of likely_case for the given user and timeperiod.  Defaults to the current user
      	 * and current timeperiod.
      	 *
      	 * @param null $user_id
      	 * @param null $timeperiod_id
        *  @param false $should_rollup
      	 */
      	protected function getLikelyAmount( $user_id = NULL, $timeperiod_id = NULL, $should_rollup=false )
      	{
      		global $current_user;
      		$revenue = 0;
            $db = DBManagerFactory::getInstance();

            if($should_rollup) {
                return $this->getManagerLikelyAmount($user_id, $timeperiod_id);
            }

      		if ( is_null($user_id) ) {
      			$user_id = $current_user->id;
      		}

            $where = " (users.id = " . $db->quoted($user_id);

            if($should_rollup && !is_null($user_id)) {
                $where .= " OR users.reports_to_id = " . $db->quoted($user_id);
            }
            $where .= ")";

      		if ( is_null($timeperiod_id) ) {
      			$timeperiod_id = TimePeriod::getCurrentId();
      		}

      		$where .= " AND opportunities.timeperiod_id = " . $GLOBALS['db']->quoted($timeperiod_id)
                  . " AND opportunities.sales_stage != " . $GLOBALS['db']->quoted(Opportunity::STAGE_CLOSED_WON)
      		       . " AND opportunities.sales_stage != " . $GLOBALS['db']->quoted(Opportunity::STAGE_CLOSED_LOST)
      		       . " AND opportunities.deleted = 0";

      		$query  = $this->opportunity->create_list_query(NULL, $where);

      		$result = $db->query($query);

      		while ( $row = $db->fetchByAssoc($result) ) {
      			$revenue += $row['likely_case'];
      		}

      		return $revenue;
      	}


   	/**
   	 * Get the total revenue for the given user and timeperiod.  Defaults to the current user
   	 * and current timeperiod.
   	 *
   	 * @param null $user_id
   	 * @param null $timeperiod_id
     * @param false $should_rollup
   	 */
   	protected function getPipelineRevenue( $user_id = NULL, $timeperiod_id = NULL, $should_rollup=false  )
   	{
   		global $current_user;
   		$revenue = 0;
        $db = DBManagerFactory::getInstance();

   		if ( is_null($user_id) ) {
   			$user_id = $current_user->id;
   		}
   		if ( is_null($timeperiod_id) ) {
   			$timeperiod_id = TimePeriod::getCurrentId();
   		}

       $where = " (users.id = " . $db->quoted($user_id);

       if($should_rollup && !is_null($user_id)) {
           $where .= " OR users.reports_to_id = " . $db->quoted($user_id);
       }
       $where .= ")";

   		$where .= " AND opportunities.timeperiod_id = " . $GLOBALS['db']->quoted($timeperiod_id)
   		       . " AND opportunities.sales_stage != " . $GLOBALS['db']->quoted(Opportunity::STAGE_CLOSED_WON)
   		       . " AND opportunities.sales_stage != " . $GLOBALS['db']->quoted(Opportunity::STAGE_CLOSED_LOST)
   		       . " AND opportunities.deleted = 0";

   		$query  = $this->opportunity->create_list_query(NULL, $where);

   		$result = $db->query($query);

   		while ( $row = $db->fetchByAssoc($result) ) {
   			$revenue += $row['amount'];
   		}

   		return $revenue;
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

		$this->user_id = (array_key_exists("user_id", $args) ? $args["user_id"] : $GLOBALS["current_user"]->id);

		$this->timeperiod_id = (array_key_exists("timeperiod_id", $args) ? $args["timeperiod_id"] : TimePeriod::getCurrentId());
		$this->should_rollup = (array_key_exists("shouldRollup", $args) ? $args["shouldRollup"] : User::isManager($this->user_id));
		if ( !is_bool($this->should_rollup) ) {
			$this->should_rollup = $this->should_rollup == 1 ? TRUE : FALSE;
		}


        if($this->should_rollup) {
            $this->forecastData = array (
                'likely_case' => $this->getManagerLikelyAmount($this->user_id, $this->timeperiod_id),
                'best_case' => $this->getManagerBestAmount($this->user_id, $this->timeperiod_id)
            );
        } else {
            $forecast           = new Forecast();
            $this->forecastData = $forecast->getForecastForUser($this->user_id, $this->timeperiod_id, $this->should_rollup);
        }

		$quota           = new Quota();
		$this->quotaData = $quota->getRollupQuota($this->timeperiod_id, $this->user_id, $this->should_rollup);

		$this->opportunity = new Opportunity();
		$this->closed      = $this->getClosedAmount($this->user_id, $this->timeperiod_id, $this->should_rollup);
		$this->revenueInPipeline = $this->getPipelineRevenue($this->user_id, $this->timeperiod_id, $this->should_rollup);
        $this->likelyAmount = $this->getLikelyAmount($this->user_id, $this->timeperiod_id, $this->should_rollup);
		$this->opportunitiesInPipeline = $this->getPipelineOpportunityCount($this->user_id, $this->timeperiod_id, $this->should_rollup);
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
			"quota"         => array(
				"amount"      => $this->getQuota($api, $args),
				"likely_case" => $this->getLikelyToQuota($api, $args),
				"best_case"   => $this->getBestToQuota($api, $args),
			),
			"closed"        => array(
				"amount"      => $this->getClosed($api, $args),
				"likely_case" => $this->getLikelyToClose($api, $args),
				"best_case"   => $this->getBestToClose($api, $args),
			),
			"opportunities" => $this->getOpportunities($api, $args),
			"revenue"       => $this->getRevenue($api, $args),
            "pipeline"      => $this->getPipeline($api, $args),
		);



		return $progressData;
	}

	public function getQuota( $api, $args )
	{
        return isset($this->quotaData["amount"]) ? $this->quotaData["amount"] :  0;
	}


	public function getLikelyToQuota( $api, $args )
	{

		$likely = $this->forecastData["likely_case"];
		$quota  = $this->getQuota($api, $args);

		return $this->formatCaseToStage($likely, $quota);
	}


	public function getBestToQuota( $api, $args )
	{

		$best  = $this->forecastData["best_case"];
		$quota = $this->getQuota($api, $args);

		return $this->formatCaseToStage($best, $quota);
	}


	public function getLikelyToClose( $api, $args )
	{

		$likely = $this->forecastData["likely_case"];
		$closed = $this->getClosed($api, $args);

		return $this->formatCaseToStage($likely, $closed);
	}


	public function getBestToClose( $api, $args )
	{

		$best = $this->forecastData["best_case"];
		$closed = $this->getClosed($api, $args);

		return $this->formatCaseToStage($best, $closed);
	}


	public function getOpportunities( $api, $args )
	{

		return $this->opportunitiesInPipeline;
	}


	public function getRevenue( $api, $args )
	{

		return $this->revenueInPipeline;
	}


	public function getClosed( $api, $args )
	{
		
		return $this->closed;
	}


    public function getPipeline( $api, $args )
    {

           $revenue = $this->getRevenue($api, $args) + $this->closed;
           $likelyToClose = $this->forecastData["likely_case"] - $this->closed;

           if($likelyToClose > 0)
               return round( ( $revenue / $likelyToClose ), 1);
           else
               return 0;
   	}
}
