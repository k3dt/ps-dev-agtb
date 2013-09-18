<?php

/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

$mod_strings = array(
    'LBL_MODULE_NAME' => 'Opportunities',
    'LBL_MODULE_NAME_SINGULAR' => 'Opportunity',
    'LBL_MODULE_TITLE' => 'Opportunities: Home',
    'LBL_SEARCH_FORM_TITLE' => 'Opportunity Search',
    'LBL_VIEW_FORM_TITLE' => 'Opportunity View',
    'LBL_LIST_FORM_TITLE' => 'Opportunity List',
    'LBL_OPPORTUNITY_NAME' => 'Opportunity Name:',
    'LBL_OPPORTUNITY' => 'Opportunity:',
    'LBL_NAME' => 'Opportunity Name',
    'LBL_INVITEE' => 'Contacts',
    'LBL_CURRENCIES' => 'Currencies',
    'LBL_LIST_OPPORTUNITY_NAME' => 'Name',
    'LBL_LIST_ACCOUNT_NAME' => 'Account Name',
    'LBL_LIST_DATE_CLOSED' => 'Expected Close Date',
    'LBL_LIST_SALES_STAGE' => 'Sales Stage',
    'LBL_LIST_AMOUNT_USDOLLAR' => 'Total Amount',
    'LBL_ACCOUNT_ID' => 'Account ID',
    'LBL_CURRENCY_RATE' => 'Currency Rate',
    'LBL_CURRENCY_ID' => 'Currency ID',
    'LBL_CURRENCY_NAME' => 'Currency Name',
    'LBL_CURRENCY_SYMBOL' => 'Currency Symbol',
//DON'T CONVERT THESE THEY ARE MAPPINGS
    'db_sales_stage' => 'LBL_LIST_SALES_STAGE',
    'db_name' => 'LBL_NAME',
    'db_amount' => 'LBL_LIST_AMOUNT',
    'db_date_closed' => 'LBL_LIST_DATE_CLOSED',
//END DON'T CONVERT
    'UPDATE' => 'Opportunity - Currency Update',
    'UPDATE_DOLLARAMOUNTS' => 'Update U.S. Dollar Amounts',
    'UPDATE_VERIFY' => 'Verify Amounts',
    'UPDATE_VERIFY_TXT' => 'Verifies that the amount values in opportunities are valid decimal numbers with only numeric characters(0-9) and decimals(.)',
    'UPDATE_FIX' => 'Fix Amounts',
    'UPDATE_FIX_TXT' => 'Attempts to fix any invalid amounts by creating a valid decimal from the current amount. Any modified amount is backed up in the amount_backup database field. If you run this and notice bugs, do not rerun it without restoring from the backup as it may overwrite the backup with new invalid data.',
    'UPDATE_DOLLARAMOUNTS_TXT' => 'Update the U.S. Dollar amounts for opportunities based on the current set currency rates. This value is used to calculate Graphs and List View Currency Amounts.',
    'UPDATE_CREATE_CURRENCY' => 'Creating New Currency:',
    'UPDATE_VERIFY_FAIL' => 'Record Failed Verification:',
    'UPDATE_VERIFY_CURAMOUNT' => 'Current Amount:',
    'UPDATE_VERIFY_FIX' => 'Running Fix would give',
    'UPDATE_INCLUDE_CLOSE' => 'Include Closed Records',
    'UPDATE_VERIFY_NEWAMOUNT' => 'New Amount:',
    'UPDATE_VERIFY_NEWCURRENCY' => 'New Currency:',
    'UPDATE_DONE' => 'Done',
    'UPDATE_BUG_COUNT' => 'Bugs Found and Attempted to Resolve:',
    'UPDATE_BUGFOUND_COUNT' => 'Bugs Found:',
    'UPDATE_COUNT' => 'Records Updated:',
    'UPDATE_RESTORE_COUNT' => 'Record Amounts Restored:',
    'UPDATE_RESTORE' => 'Restore Amounts',
    'UPDATE_RESTORE_TXT' => 'Restores amount values from the backups created during fix.',
    'UPDATE_FAIL' => 'Could not update - ',
    'UPDATE_NULL_VALUE' => 'Amount is NULL setting it to 0 -',
    'UPDATE_MERGE' => 'Merge Currencies',
    'UPDATE_MERGE_TXT' => 'Merge multiple currencies into a single currency. If there are multiple currency records for the same currency, you merge them together. This will also merge the currencies for all other modules.',
    'LBL_ACCOUNT_NAME' => 'Account Name:',
    'LBL_CURRENCY' => 'Currency:',
    'LBL_DATE_CLOSED' => 'Expected Close Date:',
    'LBL_DATE_CLOSED_TIMESTAMP' => 'Expected Close Date Timestamp',
    'LBL_TYPE' => 'Type:',
    'LBL_CAMPAIGN' => 'Campaign:',
    'LBL_NEXT_STEP' => 'Next Step:',
    'LBL_LEAD_SOURCE' => 'Lead Source',
    'LBL_SALES_STAGE' => 'Sales Stage',
    'LBL_SALES_STATUS' => 'Status',
    'LBL_PROBABILITY' => 'Probability (%)',
    'LBL_DESCRIPTION' => 'Description',
    'LBL_DUPLICATE' => 'Possible Duplicate Opportunity',
    'MSG_DUPLICATE' => 'The opportunity record you are about to create might be a duplicate of a opportunity record that already exists. Opportunity records containing similar names are listed below.<br>Click Save to continue creating this new opportunity, or click Cancel to return to the module without creating the opportunity.',
    'LBL_NEW_FORM_TITLE' => 'Create Opportunity',
    'LNK_NEW_OPPORTUNITY' => 'Create Opportunity',
    'LNK_CREATE' => 'Create Deal',
    'LNK_OPPORTUNITY_LIST' => 'View Opportunities',
    'ERR_DELETE_RECORD' => 'A record number must be specified to delete the opportunity.',
    'LBL_TOP_OPPORTUNITIES' => 'My Top Open Opportunities',
    'NTC_REMOVE_OPP_CONFIRMATION' => 'Are you sure you want to remove this contact from the opportunity?',
    'OPPORTUNITY_REMOVE_PROJECT_CONFIRM' => 'Are you sure you want to remove this opportunity from the project?',
    'LBL_DEFAULT_SUBPANEL_TITLE' => 'Opportunities',
    'LBL_ACTIVITIES_SUBPANEL_TITLE' => 'Activities',
    'LBL_HISTORY_SUBPANEL_TITLE' => 'History',
    'LBL_RAW_AMOUNT' => 'Raw Amount',
    'LBL_LEADS_SUBPANEL_TITLE' => 'Leads',
    'LBL_CONTACTS_SUBPANEL_TITLE' => 'Contacts',
    'LBL_DOCUMENTS_SUBPANEL_TITLE' => 'Documents',
    'LBL_ASSIGNED_TO_NAME' => 'Assigned to:',
    'LBL_LIST_ASSIGNED_TO_NAME' => 'Assigned User',
    'LBL_LIST_SALES_STAGE' => 'Sales Stage',
    'LBL_MY_CLOSED_OPPORTUNITIES' => 'My Closed Opportunities',
    'LBL_TOTAL_OPPORTUNITIES' => 'Total Opportunities',
    'LBL_CLOSED_WON_OPPORTUNITIES' => 'Closed Won Opportunities',
    'LBL_ASSIGNED_TO_ID' => 'Assigned User:',
    'LBL_CREATED_ID' => 'Created by ID',
    'LBL_MODIFIED_ID' => 'Modified by ID',
    'LBL_MODIFIED_NAME' => 'Modified by User Name',
    'LBL_CREATED_USER' => 'Created User',
    'LBL_MODIFIED_USER' => 'Modified User',
    'LBL_CAMPAIGN_OPPORTUNITY' => 'Campaign Opportunity',
    'LBL_CAMPAIGN_LINK' => 'Campaign Link',
    'LABEL_PANEL_ASSIGNMENT' => 'Assignment',
    'LNK_IMPORT_OPPORTUNITIES' => 'Import Opportunities',
    'LBL_EDITLAYOUT' => 'Edit Layout' /*for 508 compliance fix*/,
    //For export labels
    'LBL_EXPORT_CAMPAIGN_ID' => 'Campaign ID',
    'LBL_OPPORTUNITY_TYPE' => 'Opportunity Type',
    'LBL_EXPORT_ASSIGNED_USER_NAME' => 'Assigned User Name',
    'LBL_EXPORT_ASSIGNED_USER_ID' => 'Assigned User ID',
    'LBL_EXPORT_MODIFIED_USER_ID' => 'Modified By ID',
    'LBL_EXPORT_CREATED_BY' => 'Created By ID',
    'LBL_EXPORT_NAME' => 'Name',
    // SNIP
    'LBL_CONTACT_HISTORY_SUBPANEL_TITLE' => 'Related Contacts\' Emails',
    'LBL_FILENAME' => 'Attachment',
    'LBL_PRIMARY_QUOTE_ID' => 'Primary Quote',
    //BEGIN SUGARCRM flav=pro ONLY
    'LBL_CONTRACTS' => 'Contracts',
    'LBL_CONTRACTS_SUBPANEL_TITLE' => 'Contracts',
    'LBL_PRODUCTS' => 'Quoted Line Items',
    'LBL_RLI' => 'Revenue Line Items',
    'LNK_OPPORTUNITY_REPORTS' => 'View Opportunity Reports',
    'LBL_QUOTES_SUBPANEL_TITLE' => 'Quotes',
    'LBL_TEAM_ID' => 'Team ID',
    'LBL_TIMEPERIODS' => 'TimePeriods',
    'LBL_TIMEPERIOD_ID' => 'TimePeriod ID',
    'LBL_COMMITTED' => 'Committed',
    'LBL_FORECAST' => 'Include in Forecast',
    'LBL_COMMIT_STAGE' => 'Commit Stage',
    'LBL_WORKSHEET' => 'Worksheet',
    //END SUGARCRM flav=pro ONLY

    //BEGIN SUGARCRM flav=ent ONLY
    'TPL_RLI_CREATE' => 'An opportunity must have an associated Revenue Line Item. <a href="javascript:void(0);" id="createRLI">Create a Revenue Line Item</a>.',
    'LBL_PRODUCTS_SUBPANEL_TITLE' => 'Quoted Line Items',
    'LBL_RLI_SUBPANEL_TITLE' => 'Revenue Line Items',

    'LBL_TOTAL_RLIS' => '# of Total Revenue Line Items',
    'LBL_CLOSED_RLIS' => '# of Closed Revenue Line Items',
    'NOTICE_NO_DELETE_CLOSED_RLIS' => 'You cannot delete Opportunities that contain closed Revenue Line Items',
    'WARNING_NO_DELETE_CLOSED_SELECTED' => 'One or more of the selected records contains closed Revenue Line Items and cannot be deleted.',
    //END SUGARCRM flav=ent ONLY

    'LBL_QUOTE_SUBPANEL_TITLE' => 'Quotes',

    //Dashlet
    'LBL_DASHLET_LISTVIEW_NAME' => 'My Closed Opportunities',
    'LBL_DASHLET_LISTVIEW_DESCRIPTION' => 'Opportunities that you have closed',
    'LBL_PIPELINE_TOTAL_IS' => 'Pipeline Total is ',

    'LBL_OPPORTUNITY_ROLE'=>'Opportunity Role',
);
