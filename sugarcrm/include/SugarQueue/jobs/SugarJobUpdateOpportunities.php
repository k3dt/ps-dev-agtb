<?php
//FILE SUGARCRM flav=pro ONLY
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('modules/SchedulersJobs/SchedulersJob.php');

/**
 * SugarJobUpdateOpportunities.php
 *
 * Class to run a job which should upgrade every old opp with commit stage, date_closed_timestamp,
 * best/worst cases and related product
 */
class SugarJobUpdateOpportunities implements RunnableSchedulerJob {

    protected $job;

    /**
     * @param SchedulersJob $job
     */
    public function setJob(SchedulersJob $job)
    {
        $this->job = $job;
    }
    /**
     * @param string $data opportunity id
     */
    public function run($data)
    {
        $this->job->runnable_ran = true;
        $this->job->runnable_data = $data; 
        $this->job->succeedJob();

        $db = DBManagerFactory::getInstance();
        $sql = "SELECT id FROM opportunities WHERE deleted = 0";
        $result = $db->query($sql);

        while (($row = $db->fetchByAssoc($result)) != null)
        {
            $opp = BeanFactory::getBean('Opportunities');
            $opp->retrieve($row['id']);
            $opp->save();
        }

        $td = TimeDate::getInstance();
        $now = $td->getNow()->asDb();
        $guidSQL = $db->getGuidSQL();

        $sql = "INSERT INTO products (id,
                                    name,
                                    date_entered,
                                    date_modified,
                                    likely_case,
                                    best_case,
                                    worst_case,
                                    cost_price,
                                    quantity,
                                    currency_id,
                                    base_rate,
                                    probability,
                                    date_closed,
                                    date_closed_timestamp,
                                    assigned_user_id,
                                    opportunity_id,
                                    commit_stage)
                SELECT  {$guidSQL},
                        name,
                        '{$now}',
                        '{$now}',
                        amount,
                        amount,
                        amount,
                        amount,
                        1,
                        currency_id,
                        base_rate,
                        probability,
                        date_closed,
                        date_closed_timestamp,
                        assigned_user_id,
                        id,
                        commit_stage
                FROM opportunities 
                WHERE deleted = 0";
        $db->query($sql);
        $db->commit();
    }
}