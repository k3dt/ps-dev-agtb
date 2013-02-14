<?php
if (!defined('sugarEntry') || !sugarEntry) {
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

require_once('clients/base/api/FilterApi.php');
class ForecastWorksheetsFilterApi extends FilterApi
{

    /**
     * We need the limit higher for this filter call since we don't support pagination
     *
     * @var int
     */
    protected $defaultLimit = 1000;

    public function registerApiRest()
    {
        return array(
            'forecastWorksheetGet' => array(
                'reqType' => 'GET',
                'path' => array('ForecastWorksheets'),
                'pathVars' => array('module'),
                'method' => 'forecastWorksheetsGet',
                'jsonParams' => array(),
                'shortHelp' => 'Filter records from a single module',
                'longHelp' => 'modules/Forecasts/clients/base/api/help/ForecastWorksheetGet.html',
            ),
            'forecastWorksheetTimePeriodGet' => array(
                'reqType' => 'GET',
                'path' => array('ForecastWorksheets','?'),
                'pathVars' => array('module', 'timeperiod_id'),
                'method' => 'forecastWorksheetsGet',
                'jsonParams' => array(),
                'shortHelp' => 'Filter records from a single module',
                'longHelp' => 'modules/Forecasts/clients/base/api/help/ForecastWorksheetGet.html',
            ),
            'forecastWorksheetTimePeriodUserIdGet' => array(
                'reqType' => 'GET',
                'path' => array('ForecastWorksheets','?', '?'),
                'pathVars' => array('module', 'timeperiod_id', 'user_id'),
                'method' => 'forecastWorksheetsGet',
                'jsonParams' => array(),
                'shortHelp' => 'Filter records from a single module',
                'longHelp' => 'modules/Forecasts/clients/base/api/help/ForecastWorksheetGet.html',
            ),
            'forecastWorksheetChartTimePeriodUserIdGet' => array(
                'reqType' => 'GET',
                'path' => array('ForecastWorksheets', 'chart', '?', '?'),
                'pathVars' => array('module', '', 'timeperiod_id', 'user_id'),
                'method' => 'forecastWorksheetsChartGet',
                'jsonParams' => array(),
                'shortHelp' => 'Filter records and reformat data for chart presentation',
                'longHelp' => 'modules/Forecasts/clients/base/api/help/ForecastsWorksheetChartGet.html'
            ),
            'filterModuleGet' => array(
                'reqType' => 'GET',
                'path' => array('ForecastWorksheets', 'filter'),
                'pathVars' => array('module', ''),
                'method' => 'filterList',
                'jsonParams' => array('filter'),
                'shortHelp' => 'Filter records from a single module',
                'longHelp' => 'modules/Forecasts/clients/base/api/help/ForecastWorksheetFilter.html',
            ),
            'filterModulePost' => array(
                'reqType' => 'POST',
                'path' => array('ForecastWorksheets', 'filter'),
                'pathVars' => array('module', ''),
                'method' => 'filterList',
                'shortHelp' => 'Filter records from a single module',
                'longHelp' => 'modules/Forecasts/clients/base/api/help/ForecastWorksheetFilter.html',
            ),
        );
    }

    /**
     * Forecast Worksheet API Handler
     *
     * @param ServiceBase $api
     * @param array $args
     * @return array
     */
    public function forecastWorksheetsGet(ServiceBase $api, array $args)
    {
        // if no timeperiod is set, just set it to false, and the current time period will be set
        if(!isset($args['timeperiod_id'])) {
            $args['timeperiod_id'] = false;
        }
        // if no user id is set, just set it to false so it will use the default user
        if(!isset($args['user_id'])) {
            $args['user_id'] = false;
        }
        // make sure the type arg is set to prevent notices
        if(!isset($args['type'])) {
            $args['type'] = '';
        }

        $args['filter'] = $this->createFilter($api, $args['user_id'], $args['timeperiod_id'], $args['type']);

        return parent::filterList($api, $args);
    }

    public function forecastWorksheetsChartGet(ServiceBase $api, array $args)
    {

        //get data via forecastWorksheetsGet, no need to bother with filter setup, get will do that
        $worksheetData = $this->forecastWorksheetsGet($api, $args);

        // default to the Individual Code
        $file = 'include/SugarForecasting/Chart/Individual.php';
        $klass = 'SugarForecasting_Chart_Individual';

        // check for a custom file exists
        SugarAutoLoader::requireWithCustom($file);
        $klass = SugarAutoLoader::customClass($klass);
        // create the class

        /* @var $obj SugarForecasting_Chart_AbstractChart */
        $args['data_array'] = $worksheetData['records'];
        $obj = new $klass($args);
        //$obj->setDataArray($worksheetData['records']);

        $chartData = $obj->process();

        return $chartData;
    }

    /**
     * Forecast Worksheet Filter API Handler
     *
     * @param ServiceBase $api
     * @param array $args
     * @return array
     */
    public function filterList(ServiceBase $api, array $args)
    {
        // some local variables
        $found_assigned_user = false;
        $found_timeperiod = false;
        $found_type = false;

        // if filter is not defined, define it
        if (!isset($args['filter']) || !is_array($args['filter'])) {
            $args['filter'] = array();
        }

        // if there are filters set, process through them
        if (!empty($args['filter'])) {
            // todo-sfa: clean this up as it currently doesn't handle much in the way of nested arrays
            foreach ($args['filter'] as $key => $filter) {
                $filter_key = array_shift(array_keys($filter));
                // if the key is assigned_user_id, take the value and save it for later
                if ($found_assigned_user == false && $filter_key == 'assigned_user_id') {
                    $found_assigned_user = array_pop($filter);
                }
                // if the key is timeperiod_id, take the value, save it for later, and remove the filter
                if ($found_timeperiod == false && $filter_key == 'timeperiod_id') {
                    $found_timeperiod = array_pop($filter);
                    // remove the timeperiod_id
                    unset($args['filter'][$key]);
                }
                // if the key is 'draft', remote it from the filter
                if ($filter_key == 'draft') {
                    unset($args['filter'][$key]);
                }
                if ($found_type == false && $filter_key == 'type') {
                    $found_type = array_pop($filter);
                    unset($args['filter'][$key]);
                }
            }
        }

        $args['filter'] = $this->createFilter($api, $found_assigned_user, $found_timeperiod, $found_type);

        return parent::filterList($api, $args);
    }

    /**
     * Utility Method to create the filter for the filer API to use
     *
     * @param ServiceBase $api                  Service Api Class
     * @param mixed $user_id                    Passed in User ID, if false, it will use the current use from $api->user
     * @param mixed $timeperiod_id              TimePeriod Id, if false, the current time period will be found an used
     * @param string $parent_type               Type of worksheet to return, defaults to 'opportunities', but can be 'products'
     * @return array                            The Filer array to be passed back into the filerList Api
     * @throws SugarApiExceptionNotAuthorized
     * @throws SugarApiExceptionInvalidParameter
     */
    protected function createFilter(ServiceBase $api, $user_id, $timeperiod_id, $parent_type = 'opportunities')
    {
        $filter = array();

        // default draft to be 1
        $draft = 1;
        // if we did not find a user in the filters array, set it to the current user's id
        if ($user_id == false) {
            // use the current user, since on one was passed in
            $user_id = $api->user->id;
        } else {
            // make sure that the passed in user is a valid user
            /* @var $user User */
            // we use retrieveBean so it will return NULL and not an empty bean if the $args['user_id'] is invalid
            $user = BeanFactory::retrieveBean('Users', $user_id);
            if (is_null($user)) {
                throw new SugarApiExceptionInvalidParameter('Provided User is not valid');
            }
            // we found a user, so check to make sure that if it's not the current user, they only see committed data
            $draft = ($user_id == $api->user->id) ? 1 : 0;
        }

        // so we have a valid user, and it's not the $api->user, we need to check if the $api->user is a manager
        // if they are not a manager, throw back a 403 (Not Authorized) error
        if ($draft == 0 && !User::isManager($api->user->id)) {
            throw new SugarApiExceptionNotAuthorized();
        }
        // todo-sfa: Make sure that the passed in user can be viewed by the $api->user, need to check reportee tree
        // set the assigned_user_id
        array_push($filter, array('assigned_user_id' => $user_id));
        // set the draft flag depending on the assigned_user_id that is set from above
        array_push($filter, array('draft' => $draft));

        // if we didn't find a time period, set the time period to be the current time period
        if ($timeperiod_id == false) {
            $timeperiod_id = TimePeriod::getCurrentId();
        }

        // fix up the timeperiod filter
        /* @var $tp TimePeriod */
        // we use retrieveBean so it will return NULL and not an empty bean if the $args['timeperiod_id'] is invalid
        $tp = BeanFactory::retrieveBean('TimePeriods', $timeperiod_id);
        if (is_null($tp)) {
            throw new SugarApiExceptionInvalidParameter('Provided TimePeriod is not valid');
        }
        array_push(
            $filter,
            array(
                '$and' => array(
                    array('date_closed_timestamp' => array('$gte' => $tp->start_date_timestamp)),
                    array('date_closed_timestamp' => array('$lte' => $tp->end_date_timestamp)),
                )
            )
        );

        // we only want to view parent_types of 'Opportunities' here
        array_push($filter, array('parent_type' => $this->getParentType($parent_type)));

        return $filter;
    }

    /**
     * Utility Method to find the proper ParentType for the filter
     *
     * @param string $param         The Type from the ajax request
     * @return string
     */
    protected function getParentType($param)
    {
        // make sure that type is a module name
        if (is_string($param)) {
            switch (strtolower($param)) {
                case 'lineitem':
                case 'lineitems':
                case 'product':
                case 'products':
                    $param = 'Products';
                    break;
                case 'opportunity':
                case 'opportunities':
                default:
                    $param = 'Opportunities';

            }
        } else {
            $param = 'Opportunities';
        }

        return $param;
    }

}