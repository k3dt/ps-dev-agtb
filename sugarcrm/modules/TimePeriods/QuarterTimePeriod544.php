<?php
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

require_once('modules/TimePeriods/iTimePeriod.php');
class QuarterTimePeriod544 extends TimePeriod implements iTimePeriod {
    /**
     * constructor override
     *
     * @param null $start_date date string to set the start date of the quarter time period
     */
    public function __construct($start_date = null) {
        parent::__construct();
        $timedate = TimeDate::getInstance();

        //set defaults
        $this->time_period_type = 'Quarter544';
        $this->is_fiscal = true;
        $this->is_leaf = false;

        $this->setStartDate($start_date);
    }

    /**
     * sets the start date, based on a db formatted date string passed in.  If null is passed in, now is used.
     * The end date is adjusted as well to hold to the contract of this being an quarter time period
     *
     * @param null $startDate  db format date string to set the start date of the quarter time period
     */
    public function setStartDate($start_date = null) {
        $timedate = TimeDate::getInstance();
        //check start_date, put it to now if it's not passed in
        if(is_null($start_date)) {
            $start_date = $timedate->getNow()->asDbDate();
        }

        $start_date = $timedate->fromDbDate($start_date);

        //set the start/end date
        $this->start_date = $timedate->asUserDate($start_date);

        $endDate = $start_date->modify('+13 week');
        $endDate = $endDate->modify('-1 day');
        $this->end_date = $timedate->asUserDate($endDate);
    }

    /**
     * creates a new QuarterTimePeriod544 to start to use
     *
     * @return QuarterTimePeriod544
     */
    public function createNextTimePeriod() {
        $timedate = TimeDate::getInstance();
        $nextPeriod = new QuarterTimePeriod544($timedate->to_db_date($this->start_date));
        $nextPeriod->save();

        return $nextPeriod;
    }

    /**
     * loads related time periods and returns whether there are leaves populated.
     *
     * @return bool
     */
    public function hasLeaves() {
        $this->load_relationship('related_timeperiods');

        if(count($this->related_timeperiods))
            return true;

        return false;

    }

    /**
     * loads the related time periods and returns the array
     *
     * @return mixed
     */
    public function getLeaves() {
        $this->load_relationship('related_timeperiods');

        return $this->related_timeperiods;
    }

    /**
     * build leaves for the timeperiod by creating the specified types of timeperiods
     *
     * @param string $timePeriodType
     * @return mixed
     */
    public function buildLeaves($timePeriodType) {
        if($this->hasLeaves()) {
            return;
        }

        switch($timePeriodType) {
            case "Monthly":
                break;
            case "Weekly":
                break;

        }

    }
}