<?php
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

require_once("include/SugarForecasting/Progress/AbstractProgress.php");
class SugarForecasting_Progress_Individual extends SugarForecasting_Progress_AbstractProgress
{
    /**
     * Process the code to return the values that we need
     *
     * @return array
     */
    public function process()
    {
        return $this->getIndividualProgress();
    }

    /**
     * Get the Numbers for the Individual (Sales Rep) View, this number comes from the quota right now
     *
     * @return array
     */
    protected function getIndividualProgress()
    {
        //get the quota data for user
        /* @var $quota Quota */
        $quota = BeanFactory::getBean('Quotas');
        $quotaData = $quota->getRollupQuota($this->getArg('timeperiod_id'), $this->getArg('user_id'));

        $progressData = array(
            "quota_amount"      => isset($quotaData["amount"]) ? $quotaData["amount"] : 0
        );

        // get what we are forecasting on
        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');
        $settings = $admin->getConfigForModule('Forecasts');

        $forecast_by = $settings['forecast_by'];

        $user_id = $this->getArg('user_id');
        $timeperiod_id = $this->getArg('timeperiod_id');

        $worksheet = BeanFactory::getBean('ForecastWorksheets');
        $totals = $worksheet->worksheetTotals($timeperiod_id, $user_id,  $forecast_by);

        $acl = new SugarACLForecastWorksheets();

        $bestAccess = $acl->checkAccess(
            'ForecastWorksheets',
            'field',
            array('field' => 'best_case', 'action' => 'view')
        );

        $worstAccess = $acl->checkAccess(
            'ForecastWorksheets',
            'field',
            array('field' => 'worst_case', 'action' => 'view')
        );

        // if the user doesn't have access to best field, remove the value from totals
        if(!$bestAccess) {
            unset($totals['best_case']);
        }

        // if the user doesn't have access to worst field, remove the value from totals
        if(!$worstAccess) {
            unset($totals['worst_case']);
        }

        $totals['user_id'] = $user_id;
        $totals['timeperiod_id'] = $timeperiod_id;

        // unset some vars that come from the worksheet to avoid confusion with correct data
        // coming from this endpoint for progress
        unset($totals['pipeline_opp_count'], $totals['pipeline_amount']);

        // combine totals in with other progress data
        $progressData = array_merge($progressData, $totals);

        return $progressData;
    }
}
