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

require_once('include/SugarCurrency.php');
require_once('include/SugarForecasting/Chart/AbstractChart.php');
require_once('include/SugarForecasting/Manager.php');
class SugarForecasting_Chart_Manager extends SugarForecasting_Chart_AbstractChart
{
    /**
     * Constructor
     *
     * @param array $args
     */
    public function __construct($args)
    {
        $this->isManager = true;

        parent::__construct($args);

        if (!is_array($this->dataset)) {
            $this->dataset = array($this->dataset);
        }
    }

    /**
     * Process the data into the current JIT Chart Format
     * @return array
     */
    public function process()
    {
        $this->getManagerData();
        return $this->formatDataForChart();
    }

    /**
     * Run the Manager Code and set the data in this object
     */
    public function getManagerData()
    {
        $mgr_obj = new SugarForecasting_Manager($this->getArgs());
        $this->dataArray = $mgr_obj->process();
    }

    /**
     * Format the data from the Manager Worksheet into a usable format for the charting engine
     *
     * @return array
     */
    protected function formatDataForChart()
    {
        global $current_user, $current_language;
        $currency_id = $current_user->getPreference('currency');

        // get the language strings for the modules that we need
        $forecast_strings = return_module_language($current_language, 'Forecasts');
        $opp_strings = return_module_language($current_language, 'Opportunities');

        // get the quota from the data
        $quota = $this->getQuotaTotalFromData();

        // sort the data so it's in the correct order
        usort($this->dataArray, array($this, 'sortChartColumns'));

        // loop variables
        $values = array();

        $dataset_sums = array();

        // load up the data into the chart
        foreach ($this->dataArray as $data) {
            $val = $this->defaultValueArray;

            $val['label'] = $data['name'];
            $val['goalmarkervaluelabel'][] = SugarCurrency::formatAmountUserLocale($quota, $currency_id);
            $val['goalmarkervalue'][] = number_format($quota, 2, '.', '');
            $val['links'] = "";
            //$val['gvalue'] = number_format($data[$this->dataset . '_adjusted'], 2, '.', '');
            //$val['gvaluelabel'] = number_format($data[$this->dataset . '_adjusted'], 2, '.', '');


            foreach ($this->dataset as $dataset) {
                if (!isset($dataset_sums[$dataset])) {
                    $dataset_sums[$dataset] = 0;
                    $dataset_sums[$dataset . '_adjusted'] = 0;
                }
                $dataset_sums[$dataset] += $data[$dataset . '_case'];
                $dataset_sums[$dataset . '_adjusted'] += $data[$dataset . '_adjusted'];

                $val['values'][] = number_format($data[$dataset . '_case'], 2, '.', '');
                $val['values'][] = number_format($data[$dataset . '_adjusted'], 2, '.', '');
                $val['valuelabels'][] = SugarCurrency::formatAmountUserLocale($data[$dataset . '_case'], $currency_id);
                $val['valuelabels'][] = SugarCurrency::formatAmountUserLocale($data[$dataset . '_adjusted'], $currency_id);
                $val['goalmarkervalue'][] = number_format($dataset_sums[$dataset], 2, '.', '');
                $val['goalmarkervalue'][] = number_format($dataset_sums[$dataset . '_adjusted'], 2, '.', '');
                $val['goalmarkervaluelabel'][] = SugarCurrency::formatAmountUserLocale($dataset_sums[$dataset], $currency_id);
                $val['goalmarkervaluelabel'][] = SugarCurrency::formatAmountUserLocale($dataset_sums[$dataset . '_adjusted'], $currency_id);
            }
            $values[] = $val;
        }

        // fix the properties
        $properties = $this->defaultPropertiesArray;
        // remove the pareto lines
        unset($properties['goal_marker_label'][1]);
        $properties['value_name'] = $forecast_strings['LBL_CHART_AMOUNT'];
        $properties['label_name'] = $forecast_strings['LBL_CHART_TYPE'];
        // add a second pareto line
        $properties['goal_marker_type'][] = "pareto";
        // set the pareto line colors
        $properties['goal_marker_color'][1] = $this->defaultColorsArray[0];
        $properties['goal_marker_color'][2] = $this->defaultColorsArray[1];

        // figure out the labels
        $labels = array();
        foreach ($this->dataset as $dataset) {
            switch ($dataset) {
                case "best":
                    $labels[] = $forecast_strings['LBL_BEST_CASE'];
                    $labels[] = $forecast_strings['LBL_BEST_CASE_VALUE'];
                    break;
                case "worst":
                    $labels[] = $forecast_strings['LBL_WORST_CASE'];
                    $labels[] = $forecast_strings['LBL_WORST_CASE_VALUE'];
                    break;
                case 'likely':
                default:
                    $labels[] = $forecast_strings['LBL_LIKELY_CASE'];
                    $labels[] = $forecast_strings['LBL_LIKELY_CASE_VALUE'];
                    break;
            }
        }

        // set the pareto labels
        $properties['goal_marker_label'] = array_merge($properties['goal_marker_label'], $labels);

        // create the chart array
        $chart = array(
            'properties' => array(
                '0' => $properties
            ),
            'color' => $this->defaultColorsArray,
            'label' => $labels,
            'values' => $values,
        );

        return $chart;
    }

    /**
     * Get the quota from the sum of all the rows in the dataset
     *
     * @return int
     */
    protected function getQuotaTotalFromData()
    {
        $quota = 0;

        foreach ($this->dataArray as $data) {
            $quota += $data['quota'];
        }

        return $quota;
    }

    /**
     * Method for sorting the dataArray before we return it so that the tallest bar is always first and the
     * lowest bar is always last.
     *
     * @param array $a          The left side of the compare
     * @param array $b          The right side of the compare
     * @return int
     */
    protected function sortChartColumns($a, $b)
    {
        $sumA = 0;
        $sumB = 0;

        foreach($this->dataset as $dataset) {
            $sumA += $a[$dataset . '_adjusted'];
            $sumB += $b[$dataset . '_adjusted'];
        }

        if (intval($sumA) > intval($sumB)) {
            return -1;
        } else if (intval($sumA) < intval($sumB)) {
            return 1;
        } else {
            return 0;
        }
    }


}