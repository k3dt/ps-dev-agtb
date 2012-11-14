<?php
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

require_once('include/SugarForecasting/ForecastProcessInterface.php');
require_once('include/SugarForecasting/ForecastSaveInterface.php');
require_once('include/SugarForecasting/AbstractForecastArgs.php');
abstract class SugarForecasting_AbstractForecast extends SugarForecasting_AbstractForecastArgs implements SugarForecasting_ForecastProcessInterface
{
    /**
     * Where we store the data we want to use
     *
     * @var array
     */
    protected $dataArray = array();

    /**
     * Return the data array
     *
     * @return array
     */
    public function getDataArray()
    {
        return $this->dataArray;
    }

    /**
     * Get the months for the current time period
     *
     * @param $timeperiod_id
     * @return array
     */
    protected function getTimePeriodMonths($timeperiod_id)
    {
        /* @var $timeperiod TimePeriod */
        $timeperiod = BeanFactory::getBean('TimePeriods', $timeperiod_id);

        $months = array();

        $start = strtotime($timeperiod->start_date);
        $end = strtotime($timeperiod->end_date);
        while ($start < $end) {
            $months[] = date('F Y', $start);
            $start = strtotime("+1 month", $start);
        }

        return $months;
    }

    /**
     * Get the direct reportees for a user.
     *
     * @param $user_id
     * @return array
     */
    protected function getUserReportees($user_id)
    {
        $db = DBManagerFactory::getInstance();

        //Remove use of getRecursiveSelectSQL for now since MysqlManager does not support this and needs to be phased out first
        //BEGIN SUGARCRM flav=int ONLY
        /*
        $sql = $db->getRecursiveSelectSQL('users', 'id', 'reports_to_id',
            'id, user_name, first_name, last_name, reports_to_id, _level', false,
            "id = '{$user_id}' AND status = 'Active' AND deleted = 0", null, " AND status = 'Active' AND deleted = 0"
        );

        $result = $db-query($sql);
        $reportees = array();

        while ($row = $db->fetchByAssoc($result)) {

            if ($row['_level'] > 2) continue;

            if ($row['_level'] == 1) {
                $reportees = array_merge(array($row['id'] => $row['user_name']), $reportees);
            } else {
                $reportees[$row['id']] = $row['user_name'];
            }
        }
        */
        //END SUGARCRM flav=int ONLY

        $sql = sprintf("SELECT id, user_name, first_name, last_name, reports_to_id FROM users WHERE (reports_to_id = '%s' OR id = '%s') AND status = 'Active' AND deleted = 0", $user_id, $user_id);
        $result = $db->query($sql);
        $reportees = array();

        while ($row = $db->fetchByAssoc($result)) {
            if ($row['id'] == $user_id) {
                $reportees = array_merge(array($row['id'] => $row['user_name']), $reportees);
            } else {
                $reportees[$row['id']] = $row['user_name'];
            }
        }

        return $reportees;
    }

    /**
     * Get the passes in users reportee's who have a forecast for the passed in time period
     *
     * @param string $user_id           A User Id
     * @param string $timeperiod_id     The Time period you want to check for
     * @return array
     */
    public function getUserReporteesWithForecasts($user_id, $timeperiod_id)
    {

        $db = DBManagerFactory::getInstance();
        $return = array();
        $query = "SELECT distinct users.user_name FROM users, forecasts
                WHERE forecasts.timeperiod_id = '" . $timeperiod_id . "' AND forecasts.deleted = 0
                AND users.id = forecasts.user_id AND (users.reports_to_id = '" . $user_id . "')";

        $result = $db->query($query, true, " Error fetching user's reporting hierarchy: ");
        while (($row = $db->fetchByAssoc($result)) != null) {
            $return[] = $row['user_name'];
        }

        return $return;
    }

    /**
     * Utility method to convert a date time string into an ISO data time string for Sidecar usage.
     *
     * @param string $dt_string     Date Time value to from the db to convert into ISO format for Sidecar to consume
     * @return string               The ISO version of the string
     */
    protected function convertDateTimeToISO($dt_string)
    {
        $timedate = TimeDate::getInstance();
        if ($timedate->check_matching_format($dt_string, TimeDate::DB_DATETIME_FORMAT) === false) {
            $dt_string = $timedate->to_db($dt_string);
        }
        global $current_user;
        return $timedate->asIso($timedate->fromDb($dt_string), $current_user);
    }
}
