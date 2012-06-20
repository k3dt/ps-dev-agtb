<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

require_once('include/api/ChartApi.php');
require_once('include/SugarParsers/Filter.php');
require_once("include/SugarParsers/Converter/Report.php");
require_once("include/SugarCharts/ReportBuilder.php");

class ForecastsChartApi extends ChartApi
{
    public function registerApiRest()
    {
        $parentApi = array(
            'forecasts_chart' => array(
                'reqType' => 'GET',
                'path' => array('Forecasts', 'chart'),
                'pathVars' => array('', ''),
                'method' => 'chart',
                'shortHelp' => 'forecast chart',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastChartApi.html#chart',
            ),
        );
        return $parentApi;
    }

    /**
     * Build out the chart for the sales rep view in the forecast module
     *
     * @param ServiceBase $api      The Api Class
     * @param array $args           Service Call Arguments
     * @return mixed
     */
    public function chart($api, $args)
    {
        require_once('modules/Reports/Report.php');
        global $mod_strings, $app_list_strings, $app_strings;
        $app_list_strings = return_app_list_strings_language('en');
        $app_strings = return_application_language('en');
        $mod_strings = return_module_language('en', 'Opportunities');
        $report_defs = array();
        $report_defs['ForecastSeedReport1'] = array('Opportunities', 'ForecastSeedReport1', '{"display_columns":[{"name":"forecast","label":"Include in Forecast","table_key":"self"},{"name":"name","label":"Opportunity Name","table_key":"self"},{"name":"date_closed","label":"Expected Close Date","table_key":"self"},{"name":"sales_stage","label":"Sales Stage","table_key":"self"},{"name":"probability","label":"Probability (%)","table_key":"self"},{"name":"amount","label":"Opportunity Amount","table_key":"self"},{"name":"best_case_worksheet","label":"Best Case (adjusted)","table_key":"self"},{"name":"likely_case_worksheet","label":"Likely Case (adjusted)","table_key":"self"}],"module":"Opportunities","group_defs":[{"name":"date_closed","label":"Month: Expected Close Date","column_function":"month","qualifier":"month","table_key":"self","type":"date"},{"name":"sales_stage","label":"Sales Stage","table_key":"self","type":"enum"}],"summary_columns":[{"name":"date_closed","label":"Month: Expected Close Date","column_function":"month","qualifier":"month","table_key":"self"},{"name":"sales_stage","label":"Sales Stage","table_key":"self"},{"name":"amount","label":"SUM: Opportunity Amount","field_type":"currency","group_function":"sum","table_key":"self"},{"name":"likely_case_worksheet","label":"SUM: Likely Case (adjusted)","field_type":"currency","group_function":"sum","table_key":"self"},{"name":"best_case_worksheet","label":"SUM: Best Case (adjusted)","field_type":"currency","group_function":"sum","table_key":"self"}],"report_name":"abc123","chart_type":"vBarF","do_round":1,"chart_description":"","numerical_chart_column":"self:likely_case_worksheet:sum","numerical_chart_column_type":"","assigned_user_id":"seed_chris_id","report_type":"summary","full_table_list":{"self":{"value":"Opportunities","module":"Opportunities","label":"Opportunities"}},"filters_def":[]}', 'detailed_summary', 'vBarF');

        if (!isset($args['user']) || empty($args['user'])) {
            global $current_user;
            $args['user'] = $current_user->id;
        }

        $timeperiod = TimePeriod::getCurrentId();
        if (isset($args['tp']) && !empty($args['tp'])) {
            $timeperiod = $args['tp'];
        }

        $testFilters = array(
            'timeperiod_id' => array('$is' => $timeperiod),
            'assigned_user_link' => array('id' => $args['user']),
            //'probability' => array('$between' => array('0', '70')),
            //'sales_stage' => array('$in' => array('Prospecting', 'Qualification', 'Needs Analysis')),
        );

        // generate the report builder instance
        $rb = $this->generateReportBuilder('Opportunities', $report_defs['ForecastSeedReport1'][2], $testFilters);

        if (isset($args['ct']) && !empty($args['ct'])) {
            $rb->setChartType($this->mapChartType($args['ct']));
        }

        // create the json for the reporting engine to use
        $chart_contents = $rb->toJson();

        //Get the goal marker values
        require_once("include/SugarCharts/ChartDisplay.php");
        // create the chart display engine
        $chartDisplay = new ChartDisplay();
        // set the reporter with the chart contents from the report builder
        $chartDisplay->setReporter(new Report($chart_contents));

        // if we can't draw the chart, kick it back
        if ($chartDisplay->canDrawChart() === false) {
            // no chart to display, so lets just kick back the error message
            global $current_language;
            $mod_strings = return_module_language($current_language, 'Reports');
            return $mod_strings['LBL_NO_CHART_DRAWN_MESSAGE'];
        }

        // lets get some json!
        $json = $chartDisplay->generateJson();

        // if we have no data return an empty string
        if ($json == "No Data") {
            return '';
        }

        // since we have data let get the quota line
        /* @var $quota_bean Quota */
        $quota_bean = BeanFactory::getBean('Quotas');
        $quota = $quota_bean->getCurrentUserQuota($timeperiod, $args['user']);
        $likely_values = $this->getLikelyValues($testFilters);


        // decode the data to add stuff to the properties
        $dataArray = json_decode($json, true);

        // add the goal marker stuff
        $dataArray['properties'][0]['subtitle'] = $args['user'];
        $dataArray['properties'][0]['goal_marker_type'] = array('group', 'pareto');
        $dataArray['properties'][0]['goal_marker_color'] = array('#3FB300', '#7D12B2');
        $dataArray['properties'][0]['goal_marker_label'] = array('Quota', 'Likely');
        $dataArray['properties'][0]['label_name'] = 'Sales Stage';
        $dataArray['properties'][0]['value_name'] = 'Amount';

        foreach ($dataArray['values'] as $key => $value) {

            $likely = 0;
            $likely_label = 0;

            //$dataArray['values'][$key]['sales_stage'] = $dataArray['label'];

            // format the value labels
            foreach($value['valuelabels'] as $vl_key => $vl_val) {
                // ignore the empties
                if(empty($vl_val)) continue;

                $dataArray['values'][$key]['valuelabels'][$vl_key] = format_number($vl_val, null, null, array('currency_symbol' => true));
            }

            // extract the values to variables
            if (isset($likely_values[$value['label']])) {
                list($likely, $likely_label) = array_values($likely_values[$value['label']]);
            }

            // set the variables
            $dataArray['values'][$key]['goalmarkervalue'] = array(intval($quota['amount']), intval($likely));
            $dataArray['values'][$key]['goalmarkervaluelabel'] = array($quota['formatted_amount'], $likely_label);
        }

        // return the data now
        return $dataArray;
    }

    /**
     * Run a report to generate the likely values for the main report
     *
     * @param array $arrFilters     Which filters to apply to the report
     * @return array                The likely values from the system.
     */
    protected function getLikelyValues($arrFilters)
    {
        // base report
        $report_base = '{"display_columns":[],"module":"Opportunities","group_defs":[{"name":"date_closed","label":"Month: Expected Close Date","column_function":"month","qualifier":"month","table_key":"self","type":"date"}],"summary_columns":[{"name":"date_closed","label":"Month: Expected Close Date","column_function":"month","qualifier":"month","table_key":"self"},{"name":"likely_case_worksheet","label":"SUM: Likely Case (adjusted)","field_type":"currency","group_function":"sum","table_key":"self"}],"report_name":"Test Goal Marker Report","chart_type":"none","do_round":1,"chart_description":"","numerical_chart_column":"self:likely_case_worksheet:sum","numerical_chart_column_type":"currency","assigned_user_id":"1","report_type":"summary","full_table_list":{"self":{"value":"Opportunities","module":"Opportunities","label":"Opportunities"}},"filters_def":{}}';

        // generate a report builder instance
        $rb = $this->generateReportBuilder("Opportunities", $report_base, $arrFilters);

        // run the report
        $report = new Report($rb->toJson());
        $report->run_chart_queries();

        $results = array();
        $sum = 0;

        // lets build a usable arary
        foreach ($report->chart_rows as $row) {
            // ignore the total line
            if (count($row['cells']) != 2) continue;

            // keep a running total of the values
            $sum += unformat_number($row['cells'][1]['val']);

            // key is the same that would be used for the main report
            $results[$row['cells'][0]['val']] = array(
                'amount' => $sum, // use the unformatted number for the value in the chart
                'amount_formatted' => format_number($sum, null, null, array('currency_symbol' => true))  // format the number for the label
            );
        }

        // return the array
        return $results;
    }

    /**
     * Common code to generate the report builder
     *
     * @param string|SugarBean $module      Which module are we basing this off of
     * @param string $report_base           The base report to start with in a json string
     * @param array $filters                What filters to apply
     * @return ReportBuilder
     */
    protected function generateReportBuilder($module, $report_base, $filters)
    {

        // make sure module is a string and not a sugar bean
        if ($module instanceof SugarBean) {
            $module = $module->module_dir;
        }

        // create the a report builder instance
        $rb = new ReportBuilder($module);
        // load the default report into the report builder
        $rb->setDefaultReport($report_base);

        // create the filter parser with the base module
        $filter = new SugarParsers_Filter(BeanFactory::getBean($module));
        $filter->parse($filters);
        // convert the filters into a reporting engine format
        $converter = new SugarParsers_Converter_Report($rb);
        $reportFilters = $filter->convert($converter);
        // add the filter to the report builder
        $rb->addFilter($reportFilters);

        // return the report builder
        return $rb;
    }

}
