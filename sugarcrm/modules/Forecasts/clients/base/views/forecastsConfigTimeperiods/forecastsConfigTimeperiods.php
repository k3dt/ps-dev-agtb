<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
$viewdefs['Forecasts']['base']['view']['forecastsConfigTimeperiods'] = array(
    'panels' => array(
        array(
            'label' => 'LBL_FORECASTS_CONFIG_TIMEPERIOD_DESC',
            'fields' => array(
                array(
                    'name' => 'fiscal_period_start_month',
                    'type' => 'enum',
                    'label' => 'LBL_FORECASTS_CONFIG_FISCAL_START_MONTH',
                    'options' => '',
                    'default' => false,
                    'enabled' => true,
                    'view' => 'edit'
                ),
                array(
                    'name' => 'fiscal_period_start_day',
                    'type' => 'enum',
                    'label' => 'LBL_FORECASTS_CONFIG_FISCAL_START_DAY',
                    'options' => '',
                    'default' => false,
                    'enabled' => true,
                    'view' => 'edit'
                ),
                array(
                    'name' => 'timeperiod_interval',
                    'type' => 'enum',
                    'options' => array(
                        'annual' => 'Annual',
                        'quarter' => 'Quarter',
                    ),
                    'label' => 'LBL_FORECASTS_CONFIG_TIMEPERIOD',
                    'default' => false,
                    'enabled' => true,
                    'view' => 'edit'
                ),
                array(
                    'name' => 'timeperiod_leaf_interval',
                    'type' => 'enum',
                    'options' => array(
                        'weekly' => 'Weekly',
                        'monthly' => 'Monthly',
                    ),
                    'label' => 'LBL_FORECASTS_CONFIG_LEAFPERIOD',
                    'default' => false,
                    'enabled' => true,
                    'view' => 'edit'
                ),
                array(
                    'name' => 'timeperiods_shown_forward',
                    'type' => 'enum',
                    'options' => array(
                        '1' => '1',
                        '2' => '2',
                        '3' => '3',
                        '4' => '4',
                    ),
                    'label' => 'LBL_FORECASTS_CONFIG_TIMEPERIODS_FORWARD',
                    'default' => false,
                    'enabled' => true,
                    'view' => 'edit'
                ),
                array(
                    'name' => 'timeperiods_shown_backward',
                    'type' => 'enum',
                    'options' => array(
                        '1' => '1',
                        '2' => '2',
                        '3' => '3',
                        '4' => '4',
                    ),
                    'label' => 'LBL_FORECASTS_CONFIG_TIMEPERIODS_BACKWARD',
                    'default' => false,
                    'enabled' => true,
                    'view' => 'edit'
                ),
            ),
        ),
    )
);