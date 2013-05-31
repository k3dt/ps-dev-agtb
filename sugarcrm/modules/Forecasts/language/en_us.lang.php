<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
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
/*********************************************************************************
 * $Id: en_us.lang.php 55055 2010-03-03 19:00:58Z roger $
 * Description:  Defines the English language pack for the base application.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

$mod_strings = array (

    //module strings.
    'LBL_MODULE_NAME' => 'Forecasts',
    'LBL_MODULE_NAME_SINGULAR' => 'Forecast',
    'LNK_NEW_OPPORTUNITY' => 'Create Opportunity',
    'LBL_MODULE_TITLE' => 'Forecasts',
    'LBL_LIST_FORM_TITLE' => 'Committed Forecasts',
    'LNK_UPD_FORECAST' => 'Forecast Worksheet',
    'LNK_QUOTA' => 'View Quotas',
    'LNK_FORECAST_LIST' => 'View Forecast History',
    'LBL_FORECAST_HISTORY' => 'Forecasts: History',
    'LBL_FORECAST_HISTORY_TITLE' => 'History',

    //var defs
    'LBL_TIMEPERIOD_NAME' => 'Time Period',
    'LBL_USER_NAME' => 'User Name',
    'LBL_REPORTS_TO_USER_NAME' => 'Reports To',

    //forecast table
    'LBL_FORECAST_ID' => 'Forecast ID',
    'LBL_FORECAST_TIME_ID' => 'Time Period ID',
    'LBL_FORECAST_TYPE' => 'Forecast Type',
    'LBL_FORECAST_OPP_COUNT' => 'Total Opportunity Count',
    'LBL_FORECAST_PIPELINE_OPP_COUNT' => 'Pipeline Opportunity Count',
    'LBL_FORECAST_OPP_WEIGH'=> 'Weighted Amount',
    'LBL_FORECAST_OPP_COMMIT' => 'Likely Case',
    'LBL_FORECAST_OPP_BEST_CASE'=>'Best Case',
    'LBL_FORECAST_OPP_WORST'=>'Worst Case',
    'LBL_FORECAST_USER' => 'User',
    'LBL_DATE_COMMITTED'=> 'Date Committed',
    'LBL_DATE_ENTERED' => 'Date Entered',
    'LBL_DATE_MODIFIED' => 'Date Modified',
    'LBL_CREATED_BY' => 'Created by',
    'LBL_DELETED' => 'Deleted',
    'LBL_MODIFIED_USER_ID'=>'Modified By',
    'LBL_WK_VERSION' => 'Version',
    'LBL_WK_REVISION' => 'Revision',

    //Quick Commit labels.
    'LBL_QC_TIME_PERIOD' => 'Time Period:',
    'LBL_QC_OPPORTUNITY_COUNT' => 'Opportunity Count:',
    'LBL_QC_WEIGHT_VALUE' => 'Weighted Amount:',
    'LBL_QC_COMMIT_VALUE' => 'Commit Amount:',
    'LBL_QC_COMMIT_BUTTON' => 'Commit',
    'LBL_QC_WORKSHEET_BUTTON' => 'Worksheet',
    'LBL_QC_ROLL_COMMIT_VALUE' => 'Rollup Commit Amount:',
    'LBL_QC_DIRECT_FORECAST' => 'My Direct Forecast:',
    'LBL_QC_ROLLUP_FORECAST' => 'My Group Forecast:',
    'LBL_QC_UPCOMING_FORECASTS' => 'My Forecasts',
    'LBL_QC_LAST_DATE_COMMITTED' => 'Last Commit Date:',
    'LBL_QC_LAST_COMMIT_VALUE' => 'Last Commit Amount:',
    'LBL_QC_HEADER_DELIM'=> 'To',

    //opportunity worksheet list view labels
    'LBL_OW_OPPORTUNITIES' => "Opportunity",
    'LBL_OW_ACCOUNTNAME' => "Account",
    'LBL_OW_REVENUE' => "Amount",
    'LBL_OW_WEIGHTED' => "Weighted Amount",
    'LBL_OW_MODULE_TITLE'=> 'Opportunity Worksheet',
    'LBL_OW_PROBABILITY'=>'Probability',
    'LBL_OW_NEXT_STEP'=>'Next Step',
    'LBL_OW_DESCRIPTION'=>'Description',
    'LBL_OW_TYPE'=>'Type',

    //forecast schedule shortcuts
    'LNK_NEW_TIMEPERIOD' => 'Create Time Period',
    'LNK_TIMEPERIOD_LIST' => 'View Time Periods',

    //Forecast schedule sub panel list view.
    'LBL_SVFS_FORECASTDATE' => 'Schedule Start Date',
    'LBL_SVFS_STATUS' => 'Status',
    'LBL_SVFS_USER' => 'For',
    'LBL_SVFS_CASCADE' => 'Cascade to Reports?',
    'LBL_SVFS_HEADER' => 'Forecast Schedule:',

    //Forecast Schedule detail; view.....
    'LB_FS_KEY' => 'ID',
    'LBL_FS_TIMEPERIOD_ID' => 'Time Period ID',
    'LBL_FS_USER_ID' => 'User ID',
    'LBL_FS_TIMEPERIOD' => 'Time Period',
    'LBL_FS_START_DATE' => 'Start Date',
    'LBL_FS_END_DATE' => 'End Date',
    'LBL_FS_FORECAST_START_DATE' => "Forecast Start Date",
    'LBL_FS_STATUS' => 'Status',
    'LBL_FS_FORECAST_FOR' => 'Schedule For:',
    'LBL_FS_CASCADE' =>'Cascade?',
    'LBL_FS_MODULE_NAME' => 'Forecast Schedule',
    'LBL_FS_CREATED_BY' =>'Created by',
    'LBL_FS_DATE_ENTERED' => 'Date Entered',
    'LBL_FS_DATE_MODIFIED' => 'Date Modified',
    'LBL_FS_DELETED' => 'Deleted',

    //forecast worksheet direct reports forecast
    'LBL_FDR_USER_NAME'=>'Direct Report',
    'LBL_FDR_OPPORTUNITIES'=>'Opportunities in forecast:',
    'LBL_FDR_WEIGH'=>'Weighted Amount of opportunities:',
    'LBL_FDR_COMMIT'=>'Committed Amount',
    'LBL_FDR_DATE_COMMIT'=>'Commit Date',

    //detail view.
    'LBL_DV_HEADER' => 'Forecasts:Worksheet',
    'LBL_DV_MY_FORECASTS' => 'My Forecasts',
    'LBL_DV_MY_TEAM' => "My Team's Forecasts" ,
    'LBL_DV_TIMEPERIODS' => 'Time Periods:',
    'LBL_DV_FORECAST_PERIOD' => 'Forecast Time Period',
    'LBL_DV_FORECAST_OPPORTUNITY' => 'Forecast Opportunities',
    'LBL_SEARCH' => 'Select',
    'LBL_SEARCH_LABEL' => 'Select',
    'LBL_COMMIT_HEADER' => 'Forecast Commit',
    'LBL_DV_LAST_COMMIT_DATE' =>'Last Commit Date:',
    'LBL_DV_LAST_COMMIT_AMOUNT' =>'Last Commit Amounts:',
    'LBL_DV_FORECAST_ROLLUP' => 'Forecast Rollup',
    'LBL_DV_TIMEPERIOD' => 'Time Period:',
    'LBL_DV_TIMPERIOD_DATES' => 'Date Range:',
    'LBL_LOADING_COMMIT_HISTORY' => 'Loading Commit History...',

    //list view
    'LBL_LV_TIMPERIOD'=> 'Time period',
    'LBL_LV_TIMPERIOD_START_DATE'=> 'Start Date',
    'LBL_LV_TIMPERIOD_END_DATE'=> 'End Date',
    'LBL_LV_TYPE'=> 'Forecast Type',
    'LBL_LV_COMMIT_DATE'=> 'Date Committed',
    'LBL_LV_OPPORTUNITIES'=> 'Opportunities',
    'LBL_LV_WEIGH'=> 'Weighted Amount',
    'LBL_LV_COMMIT'=> 'Committed Amount',

    'LBL_COMMIT_NOTE'=> 'Enter amounts that you would like to commit for the selected Time Period:',

    'LBL_COMMIT_MESSAGE'=> 'Do you want to commit these amounts?',
    'ERR_FORECAST_AMOUNT' => 'Commit Amount is required and must be a number.',

    // js error strings
    'LBL_FC_START_DATE' => 'Start Date',
    'LBL_FC_USER' => 'Schedule For',

    'LBL_NO_ACTIVE_TIMEPERIOD'=>'No Active time periods for Forecasting.',
    'LBL_FDR_ADJ_AMOUNT'=>'Adjusted Amount',
    'LBL_SAVE_WOKSHEET'=>'Save Worksheet',
    'LBL_RESET_WOKSHEET'=>'Reset Worksheet',
    'LBL_SHOW_CHART'=>'View Chart',
    'LBL_RESET_CHECK'=>'All worksheet data for the selected time period and logged in user will be removed. Continue?',

    'LB_FS_LIKELY_CASE'=>'Likely Case',
    'LB_FS_WORST_CASE'=>'Worst Case',
    'LB_FS_BEST_CASE'=>'Best Case',
    'LBL_FDR_WK_LIKELY_CASE'=>'Est. Likely Case',
    'LBL_FDR_WK_BEST_CASE'=> 'Est. Best Case',
    'LBL_FDR_WK_WORST_CASE'=>'Est. Worst Case',
    'LBL_BEST_CASE'=>'Best Case',
    'LBL_LIKELY_CASE'=>'Likely Case',
    'LBL_WORST_CASE'=>'Worst Case',
    'LBL_FDR_C_BEST_CASE'=>'Best Case',
    'LBL_FDR_C_WORST_CASE'=>'Worst Case',
    'LBL_FDR_C_LIKELY_CASE'=>'Likely Case',
    'LBL_QC_LAST_BEST_CASE'=>'Last Commit Amount (Best Case):',
    'LBL_QC_LAST_LIKELY_CASE'=>'Last Commit Amount (Likely Case):',
    'LBL_QC_LAST_WORST_CASE'=>'Last Commit Amount (Worst Case):',
    'LBL_QC_ROLL_BEST_VALUE'=>'Rollup Commit Amount (Best Case):',
    'LBL_QC_ROLL_LIKELY_VALUE'=>'Rollup Commit Amount (Likely Case):',
    'LBL_QC_ROLL_WORST_VALUE'=>'Rollup Commit Amount (Worst Case):',
    'LBL_QC_COMMIT_BEST_CASE'=>'Commit Amount (Best Case):',
    'LBL_QC_COMMIT_LIKELY_CASE'=>'Commit Amount (Likely Case):',
    'LBL_QC_COMMIT_WORST_CASE'=>'Commit Amount (Worst Case):',
    'LBL_BEST_CASE_VALUE' => 'Best (Adjusted)',
    'LBL_LIKELY_CASE_VALUE' => 'Likely (Adjusted)',
    'LBL_WORST_CASE_VALUE' => 'Worst (Adjusted)',
    'LBL_CURRENCY' => 'Currency',
    'LBL_CURRENCY_ID' => 'Currency ID',
    'LBL_CURRENCY_RATE' => 'Currency Rate',
    'LBL_BASE_RATE' => 'Base Rate',

    'LBL_INCLUDED_BEST_TOTALS'=>'Best Included Totals',
    'LBL_INCLUDED_LIKELY_TOTALS'=>'Likely Included Totals',
    'LBL_INCLUDED_WORST_TOTALS'=>'Worst Included Totals',

    'LBL_BEST_CASE_BASE_CURRENCY' => 'Best (Adjusted) base currency',
    'LBL_LIKELY_CASE_BASE_CURRENCY' => 'Likely (Adjusted) base currency',
    'LBL_WORST_CASE_BASE_CURRENCY' => 'Worst (Adjusted) base currency',
    'LBL_QUOTA' => 'Quota',

    'LBL_FORECAST_FOR'=>'Forecast Worksheet for: ',
    'LBL_FMT_ROLLUP_FORECAST'=>'(Rollup)',
    'LBL_FMT_DIRECT_FORECAST'=>'(Direct)',

    //labels used by the chart.
    'LBL_GRAPH_TITLE'=>'Forecast History',
    'LBL_GRAPH_QUOTA_ALTTEXT'=>'Quota for %s',
    'LBL_GRAPH_COMMIT_ALTTEXT'=>'Committed Amount for %s',
    'LBL_GRAPH_OPPS_ALTTEXT'=>'Value of opportunities closed in %s',

    'LBL_GRAPH_QUOTA_LEGEND'=>'Quota',
    'LBL_GRAPH_COMMIT_LEGEND'=>'Committed Forecast',
    'LBL_GRAPH_OPPS_LEGEND'=>'Closed Opportunities',
    'LBL_TP_QUOTA'=>'Quota:',
    'LBL_CHART_FOOTER'=>'Forecast History<br>Quota vs Forecasted Amount vs Closed Opportunity Value',
    'LBL_TOTAL_VALUE'=>'Totals:',
    'LBL_COPY_AMOUNT'=>'Total amount',
    'LBL_COPY_WEIGH_AMOUNT'=>'Total weighted amount',
    'LBL_WORKSHEET_AMOUNT'=>'Total estimated Amounts',
    'LBL_COPY'=>'Copy Values',
    'LBL_COMMIT_AMOUNT'=>'Sum of Committed values.',
    'LBL_COPY_FROM'=>'Copy value from:',

    'LBL_CHART_TITLE'=>'Quota vs. Committed vs. Actual',

    'LBL_FORECAST' => 'Forecast',
    'LBL_COMMIT_STAGE' => 'Commit Stage',
    'LBL_SALES_STAGE' => 'Stage',
    'LBL_AMOUNT' => 'Amount',
    'LBL_PERCENT' => 'Percent',
    'LBL_DATE_CLOSED' => 'Expected Close',
    'LBL_PRODUCT_ID' => 'Product ID',
    'LBL_QUOTA_ID' => 'Quota ID',
    'LBL_VERSION' => 'Version',

    //Labels for forecasting history log and endpoint
    'LBL_ERROR_NOT_MANAGER' => 'Error: user {0} does not have manager access to request forecasts for {1}',
    'LBL_UP' => 'up',
    'LBL_DOWN' => 'down',
    'LBL_PREVIOUS_COMMIT' => 'Latest Commit:',

    'LBL_COMMITTED_HISTORY_SETUP_FORECAST' => 'Setup forecast',
    'LBL_COMMITTED_HISTORY_UPDATED_FORECAST' => 'Updated forecast',
    'LBL_COMMITTED_HISTORY_1_SHOWN' => '{{{intro}}} {{{first}}}',
    'LBL_COMMITTED_HISTORY_2_SHOWN' => '{{{intro}}} {{{first}}}, {{{second}}}',
    'LBL_COMMITTED_HISTORY_3_SHOWN' => '{{{intro}}} {{{first}}}, {{{second}}}, and {{{third}}}',
    'LBL_COMMITTED_HISTORY_LIKELY_CHANGED' => 'likely {{{direction}}} {{{from}}} to {{{to}}}',
    'LBL_COMMITTED_HISTORY_BEST_CHANGED' => 'best {{{direction}}} {{{from}}} to {{{to}}}',
    'LBL_COMMITTED_HISTORY_WORST_CHANGED' => 'worst {{{direction}}} {{{from}}} to {{{to}}}',
    'LBL_COMMITTED_HISTORY_LIKELY_SAME' => 'likely stayed the same',
    'LBL_COMMITTED_HISTORY_BEST_SAME' => 'best stayed the same',
    'LBL_COMMITTED_HISTORY_WORST_SAME' => 'worst stayed the same',

    'LBL_COMMITTED_THIS_MONTH' => 'This month on {0}',
    'LBL_COMMITTED_MONTHS_AGO' => '{0} months ago on {1}',

    //Labels for jsTree implementation
    'LBL_TREE_PARENT' => 'Parent',

    'LBL_MY_OPPORTUNITIES' => 'Opportunities ({0})',

    //Labels for worksheet items
    'LBL_EXPECTED_OPPORTUNITIES' => 'Expected Opportunities',
    'LBL_INCLUDED_TOTAL' => 'Included Total',
    'LBL_OVERALL_TOTAL' => 'Overall Total',
    'LBL_TOTAL' => 'Total',
    'LBL_EDITABLE_INVALID' => 'Invalid Value for {0}',
    'LBL_EDITABLE_INVALID_RANGE' => 'Value must be between {0} and {1}',
    'LBL_WORKSHEET_SAVE_CONFIRM' => 'You have unsaved changes in your Worksheet. Press Ok to save these as a draft and continue or Cancel to discard these changes and continue.',
    'LBL_WORKSHEET_COMMIT_CONFIRM' => 'You have saved changes that have not been committed in the rep view. The saved changes will not be visible in the Manager view until you commit.<br>Press OK to commit the changes and continue, or Cancel to not commit the changes and continue.',
    'LBL_WORKSHEET_COMMIT_ALERT' => 'You have committed your Rep view, but not your Manager view; the team\'s forecast will not be committed until your Manager view is committed.',
    'LBL_WORKSHEET_SAVE_CONFIRM_UNLOAD' => 'You have unsaved changes in your Worksheet.',
    'LBL_WORKSHEET_EXPORT_CONFIRM' => 'Please note that only saved or committed data can be exported. Click OK to continue exporting, or click Cancel to return to the worksheet.',
    'LBL_WORKSHEET_ID' => 'Worksheet ID',

    // Labels for Chart Options
    'LBL_DATA_SET' => 'Data Set:',
    'LBL_GROUP_BY' => 'Group By:',
    'LBL_CHART_OPTIONS' => 'Chart Options',
    'LBL_CHART_AMOUNT' => 'Amount',
    'LBL_CHART_TYPE' => 'Type',

    // Labels for Data Filters
    'LBL_FILTERS' => 'Filters',

    // Labels for toggle buttons
    'LBL_MORE' => 'More',
    'LBL_LESS' => 'Less',

    // Labels for Progress
    'LBL_PROJECTED' => 'Projected',
    'LBL_DISTANCE_ABOVE_LIKELY_FROM_QUOTA' => 'Likely above Quota',
    'LBL_DISTANCE_LEFT_LIKELY_TO_QUOTA' => 'Likely below Quota',
    'LBL_DISTANCE_ABOVE_BEST_FROM_QUOTA' => 'Best above Quota',
    'LBL_DISTANCE_LEFT_BEST_TO_QUOTA' => 'Best below Quota',
    'LBL_DISTANCE_ABOVE_WORST_FROM_QUOTA' => 'Worst above Quota',
    'LBL_DISTANCE_LEFT_WORST_TO_QUOTA' => 'Worst below Quota',
    'LBL_CLOSED' => 'Closed',
    'LBL_DISTANCE_ABOVE_LIKELY_FROM_CLOSED' => 'Likely above Closed',
    'LBL_DISTANCE_LEFT_LIKELY_TO_CLOSED' => 'Likely below Closed',
    'LBL_DISTANCE_ABOVE_BEST_FROM_CLOSED' => 'Best above Closed',
    'LBL_DISTANCE_LEFT_BEST_TO_CLOSED' => 'Best below Closed',
    'LBL_DISTANCE_ABOVE_WORST_FROM_CLOSED' => 'Worst above Closed',
    'LBL_DISTANCE_LEFT_WORST_TO_CLOSED' => 'Worst below Closed',
    'LBL_REVENUE' => 'Revenue',
    'LBL_PIPELINE_SIZE' => 'Pipeline Size',
    'LBL_PIPELINE_REVENUE' => 'Pipeline Revenue',
    'LBL_PIPELINE_OPPORTUNITIES' => 'Pipeline Opportunities',
    'LBL_LOADING' => 'Loading',

    // Actions Dropdown
    'LBL_ACTIONS' => 'Actions',
    'LBL_EXPORT_CSV' => 'Export CSV',
    'LBL_CANCEL' => 'Cancel',

    'LBL_CHART_FORECAST_FOR' => 'Forecast for {0}',
    'LBL_FORECAST_TITLE' => 'Forecast: {0}',
    'LBL_CHART_INCLUDED' => 'Included',
    'LBL_CHART_NOT_INCLUDED' => 'Not Included',
    'LBL_CHART_ADJUSTED' => ' (Adjusted)',
    'LBL_SAVE_DRAFT' => 'Save Draft',
    'LBL_CHANGES_BY' => 'Changes by {0}',
    'LBL_FORECAST_SETTINGS' => 'Settings',

    // config panels strings
    'LBL_FORECASTS_CONFIG_TITLE' => 'Forecasts Setup',

    'LBL_FORECASTS_MISSING_STAGE_TITLE' => 'Forecasts Configuration Error:',
    'LBL_FORECASTS_MISSING_SALES_STAGE_VALUES' => 'The Forecasts module has been improperly configured and is no longer available. Sales Stage Won and Sales Stage Lost must contain at least one value found in Sales Stage. Please contact your Administrator.',
    'LBL_FORECASTS_MISSING_SALES_STAGE_VALUES_ADMIN' => 'The Forecasts module has been improperly configured and is no longer available. Sales Stage Won and Sales Stage Lost must contain at least one value found in Sales Stage. Please set these options up in <a href="javascript:void(0);" id="forecastConfigLink">Forecasts configuration</a>.',

    // Panel and BreadCrumb Labels
    'LBL_FORECASTS_CONFIG_BREADCRUMB_WORKSHEET_LAYOUT' => 'Worksheet Layout',
    'LBL_FORECASTS_CONFIG_BREADCRUMB_RANGES' => 'Ranges',
    'LBL_FORECASTS_CONFIG_BREADCRUMB_SCENARIOS' => 'Scenarios',
    'LBL_FORECASTS_CONFIG_BREADCRUMB_TIMEPERIODS' => 'Time Periods',
    'LBL_FORECASTS_CONFIG_BREADCRUMB_VARIABLES' => 'Variables',

    // Admin UI
    'LBL_FORECASTS_CONFIG_TITLE_FORECAST_SETTINGS' => 'Forecast Settings',
    'LBL_FORECASTS_CONFIG_TITLE_TIMEPERIODS' => 'Time Period',
    'LBL_FORECASTS_CONFIG_TITLE_RANGES' => 'Forecast Ranges',
    'LBL_FORECASTS_CONFIG_TITLE_SCENARIOS' => 'Scenarios',
    'LBL_FORECASTS_CONFIG_TITLE_WORKSHEET_COLUMNS' => 'Worksheet Columns',
    'LBL_FORECASTS_CONFIG_TITLE_FORECAST_BY' => 'View Forecast Worksheet By',

    'LBL_FORECASTS_CONFIG_HOWTO_TITLE_FORECAST_BY' => 'Forecast By',

    'LBL_FORECASTS_CONFIG_TITLE_MESSAGE_TIMEPERIODS' => 'Fiscal year start date:',

    'LBL_FORECASTS_CONFIG_HELP_TIMEPERIODS' => 'Conﬁgure the time period that will be used in the Forecasts module.<br><br>Start by choosing the Start Date of your ﬁscal year. Then choose the type of  time period that you want to forecast over. The date range for the time periods will be automatically calculated based on your selections. The Sub Time Period is the base for the Forecast worksheet.<br><br>The viewable future and past time periods will determine the number of visible sub-periods in the Forecasts module. The users are able to view and edit the forecasting numbers in the visible  sub-periods.',
    'LBL_FORECASTS_CONFIG_HELP_RANGES' => 'Configure the way you would like to tag the opportunities (for example which opportunities should be included or excluded from the forecasting numbers). You can choose the number of categories and determine the ranges of the probabilities for the categories. The opportunities with certain probabilities will default  to the corresponding category. After the initial tag, the user can manually modify the value. Only the opportunities in the "Included" category will be reported to managers as committed.',
    'LBL_FORECASTS_CONFIG_HELP_SCENARIOS' => 'Select the columns you would like the user to ﬁll out for their forecasts of each opportunity. Please note the Likely amount is tied to the amount shown in Opportunities; for this reason the Likely column cannot be hidden.',
    'LBL_FORECASTS_CONFIG_HELP_WORKSHEET_COLUMNS' => 'Select which columns you would like to view in the Forecast module. The list of fields will combine the worksheet and allow the user to choose how to configure its view.',
    'LBL_FORECASTS_CONFIG_HELP_FORECAST_BY' => 'I am a placeholder for Forecast By how-to text!',
    'LBL_FORECASTS_CONFIG_SETTINGS_SAVED' => 'Forecast config settings have been saved.',

    // timeperiod config
    //TODO-sfa remove this once the ability to map buckets when they get changed is implemented (SFA-215).
    'LBL_FORECASTS_CONFIG_TIMEPERIOD_SETUP_NOTICE' => 'Time Period settings cannot be changed after initial setup.',
    'LBL_FORECASTS_CONFIG_TIMEPERIOD_DESC' => 'Configure the Time Periods used for forecasting.',
    'LBL_FORECASTS_CONFIG_TIMEPERIOD_TYPE' => 'Select the type of year your organization uses for accounting.',
    'LBL_FORECASTS_CONFIG_TIMEPERIOD' => 'Choose the type of Time Period',
    'LBL_FORECASTS_CONFIG_LEAFPERIOD' => 'Choose the sub period that you want to view your time period over:',
    'LBL_FORECASTS_CONFIG_START_DATE' => 'Choose fiscal year start date',
    'LBL_FORECASTS_CONFIG_TIMEPERIODS_FORWARD' => 'Choose the number of future Time Periods to view in the worksheet.<br><i>This number applies to the base Time Period selected. For example, choosing 2 with Yearly Time Period will show 8 future Quarters</i>',
    'LBL_FORECASTS_CONFIG_TIMEPERIODS_BACKWARD' => 'Choose the number of past Time Periods to view in the worksheet.<br><i>This number applies to the base Time Period selected. For example, choosing 2 with Monthly Time Period will show 6 past Months</i>',

    // worksheet layout config
    'LBL_FORECASTS_CONFIG_GENERAL_FORECAST_BY_TEXT' => 'Select how to populate the forecasting worksheet:',
    'LBL_FORECASTS_CONFIG_GENERAL_FORECAST_BY_OPPORTUNITIES' => 'Opportunities',
    'LBL_FORECASTS_CONFIG_GENERAL_FORECAST_BY_PRODUCT_LINE_ITEMS' => 'Revenue Line Items',
    'LBL_FORECASTS_CONFIG_WORKSHEET_LAYOUT_DETAIL_MESSAGE' => 'Worksheets will be populated with:',

    // ranges config
    //TODO-sfa remove this once the ability to map buckets when they get changed is implemented (SFA-215).
    'LBL_FORECASTS_CONFIG_RANGES_SETUP_NOTICE' => 'Range settings cannot be changed after first save draft or commit in the Forecasts module. For an upgraded instance however, Range settings cannot be changed after the initial setup as the Forecasts data is already available through the upgrade.',
    'LBL_FORECASTS_CONFIG_RANGES' => 'Forecast Range Options:',
    'LBL_FORECASTS_CONFIG_RANGES_OPTIONS' => 'Select the way you would like to categorize opportunities.',
    'LBL_FORECASTS_CONFIG_SHOW_BINARY_RANGES_DESCRIPTION' => 'This option gives a user the ability to specify opportunities that will be included or excluded from a forecast.',
    'LBL_FORECASTS_CONFIG_SHOW_BUCKETS_RANGES_DESCRIPTION' => 'This option gives a user the ability to categorize their opportunities that are not included in the commit but are upside and have the potential of closing if everything goes well and opportunities that are to be excluded from the forecast.',
    'LBL_FORECASTS_CONFIG_SHOW_CUSTOM_BUCKETS_RANGES_DESCRIPTION' => 'Custom Ranges: This option gives a user the ability to categorize their opportunities to be committed into the forecast into a committed range, excluded range and any others that you setup.',
    'LBL_FORECASTS_CONFIG_RANGES_EXCLUDE_INFO' => 'The Exclude Range is from 0% to the minimum of the previous Forecast Range by default.',

    // scenarios config
    //TODO-sfa refactors the code references for scenarios to be scenarios (SFA-337).
    'LBL_FORECASTS_CONFIG_WORKSHEET_SCENARIOS' => 'Choose the Scenarios to include on the forecasting worksheet.',
    'LBL_FORECASTS_CONFIG_WORKSHEET_LIKELY_INFO' => 'Likely is based on the amount entered in the Opportunities module.',
    'LBL_FORECASTS_CONFIG_WORKSHEET_SCENARIOS_LIKELY' => 'Likely',
    'LBL_FORECASTS_CONFIG_WORKSHEET_SCENARIOS_BEST' => 'Best',
    'LBL_FORECASTS_CONFIG_WORKSHEET_SCENARIOS_WORST' => 'Worst',
    'LBL_FORECASTS_CONFIG_PROJECTED_SCENARIOS' => 'Show projected scenarios in the totals',
    'LBL_FORECASTS_CONFIG_PROJECTED_SCENARIOS_LIKELY' => 'Show Likely Case Totals',
    'LBL_FORECASTS_CONFIG_PROJECTED_SCENARIOS_BEST' => 'Show Best Case Totals',
    'LBL_FORECASTS_CONFIG_PROJECTED_SCENARIOS_WORST' => 'Show Worst Case Totals',

    // variables config
    'LBL_FORECASTS_CONFIG_VARIABLES' => 'Variables',
    'LBL_FORECASTS_CONFIG_VARIABLES_DESC' => 'The formulas for the Metrics Table rely on the sales stage for opportunities that need to be excluded from the pipleline, i.e., opportunities that are closed and lost.',
    'LBL_FORECASTS_CONFIG_VARIABLES_CLOSED_LOST_STAGE' => 'Please select the Sales Stage that represent closed and lost opportunities:',
    'LBL_FORECASTS_CONFIG_VARIABLES_CLOSED_WON_STAGE' => 'Please select the Sales Stage that represent closed and won opportunities:',
    'LBL_FORECASTS_CONFIG_VARIABLES_FORMULA_DESC' => 'Therefore the pipeline formula will be:',

    'LBL_FORECASTS_WIZARD_SUCCESS_TITLE' => 'Success:',
    'LBL_FORECASTS_WIZARD_SUCCESS_MESSAGE' => 'You successfully set up your forecasting module. Please wait while the module loads.',
    'LBL_FORECASTS_WIZARD_REFRESH_NOTICE' => 'This is your first time using the Forecasts module and your Opportunities need to be loaded. This process may take a few minutes and you may need to refresh the page.',
    'LBL_FORECASTS_TABBED_CONFIG_SUCCESS_MESSAGE' => 'The setting has been saved. Please wait while the module reloads.',
    // Labels for Success Messages:
    'LBL_FORECASTS_WORKSHEET_SAVE_DRAFT_SUCCESS' => 'You have saved the forecasting worksheet as draft for the selected time period.',
    'LBL_FORECASTS_WORKSHEET_COMMIT_SUCCESS' => 'You have committed the forecasting worksheet for the selected time period.',

    // custom ranges
    'LBL_FORECASTS_CUSTOM_RANGES_DEFAULT_NAME' => 'Custom Range',
    'LBL_UNAUTH_FORECASTS' => 'Unauthorized access to forecast settings.',
    'LBL_FORECASTS_RANGES_BASED_TITLE' => 'Ranges based on probabilities',
    'LBL_FORECASTS_CUSTOM_BASED_TITLE' => 'Custom Ranges based on probabilities',
    'LBL_FORECASTS_CUSTOM_NO_BASED_TITLE' =>'Ranges not based on probabilities',

    // pipline opportunities dashlet
    'LBL_DASHLET_FORECAST_NOT_SETUP' => 'Forecasts has not been configured and needs to be setup in order to use this widget. Please contact your system administrator.',
    'LBL_DASHLET_FORECAST_NOT_SETUP_ADMIN' => 'Forecasts has not been configured and needs to be setup in order to use this widget. <a href="#Forecasts/config">Please click here to configure Forecasting</a>.'
);
