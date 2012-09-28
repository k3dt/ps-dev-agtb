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
/**
 * Implements the annual representation of a time period
 * @api
 */
class MonthTimePeriod extends TimePeriod implements iTimePeriod {

    /**
     * constructor override
     *
     * @param null $start_date date string to set the start date of the month time period
     * @param bool $is_fiscal flag to determine if the month should follow a fiscal pattern vs calendar
     * @param int $week_count to be used in conjunction with is_fiscal, if fiscal month, then this is how many weeks to include
     */
    public function __construct($start_date = null, $is_fiscal = false, $week_count = 4) {
        parent::__construct();
        $timedate = TimeDate::getInstance();

        //set defaults
        $this->time_period_type = 'Month';
        $this->is_fiscal = $is_fiscal;
        $this->is_leaf = false;

        $this->setStartDate($start_date, $week_count);
    }

    /**
     * sets the start date, based on a db formatted date string passed in.  If null is passed in, now is used.
     * The end date is adjusted as well to hold to the contract of this being an quarter time period
     *
     * @param null $startDate  db format date string to set the start date of the quarter time period
     */
    public function setStartDate($start_date = null, $week_count = 4) {
        $timedate = TimeDate::getInstance();
        //check start_date, put it to now if it's not passed in
        if(is_null($start_date)) {
            $start_date = $timedate->getNow()->asDbDate();
        }

        $end_date = $timedate->fromDbDate($start_date);

        //set the start/end date
        $this->start_date = $start_date;

        if($this->is_fiscal) {
            $end_date = $end_date->modify('+'.$week_count.' week');
            $end_date = $end_date->modify('-1 day');
        } else {
            $end_date = $end_date->modify('+1 month');
            $end_date = $end_date->modify('-1 day');
        }
        $this->end_date = $timedate->asDbDate($end_date);
    }

    /**
     * creates a new MonthTimePeriod to start to use
     *
     * @param int $week_length denotes how many weeks should be included in month for a fiscal month
     *
     * @return MonthTimePeriod
     */
    public function createNextTimePeriod($week_length=4) {
        $timedate = TimeDate::getInstance();
        $nextEndDate = $timedate->fromDbDate($this->end_date);

        $nextStartDate = $nextEndDate->modify('+1 day');
        $nextStartDate = $timedate->asDbDate($nextStartDate);
        if($this->is_fiscal)
        {
            $nextPeriod = new MonthTimePeriod($nextStartDate, true, $week_length);
        } else {
            $nextPeriod = new MonthTimePeriod($nextStartDate, false);
        }
        $nextPeriod->is_leaf = $this->is_leaf;
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
        //$this->load_relationship('related_timeperiods');
        $leaves = array();
        $db = DBManagerFactory::getInstance();
        $query = "select id, time_period_type from timeperiods "
        . "WHERE parent_id = " . $db->quoted($this->id) . " "
        . "AND is_leaf = 1 AND deleted = 0 order by start_date_timestamp";

        $result = $db->query($query);

        while($row = $db->fetchByAssoc($result)) {
            array_push($leaves, BeanFactory::getBean($row['time_period_type']."TimePeriods", $row['id']));
        }
        return $leaves;
    }


    /**
     * build leaves for the timeperiod by creating the specified types of timeperiods
     * currently the monthly time period doesn't allow these, so it will throw an exception right now
     * this can be changed in the future to allow drillable periods if necessary
     *
     * @param string $timePeriodType
     * @return mixed
     */
    public function buildLeaves($timePeriodType) {
        throw new Exception("This TimePeriod is a leaf only and not allowed to be a leaf");

    }
}