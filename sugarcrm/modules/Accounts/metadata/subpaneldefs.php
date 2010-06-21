<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * Layout definition for Accounts
 *
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
 * by SugarCRM are Copyright (C) 2005 SugarCRM, Inc.; All Rights Reserved.

 */
// $Id: layout_defs.php 14538 2006-07-12 00:27:59Z awu $

$layout_defs['Accounts'] = array(
	// list of what Subpanels to show in the DetailView 
	'subpanel_setup' => array(

		'activities' => array(
			'order' => 10,
			'sort_order' => 'desc',
			'sort_by' => 'date_start',
			'title_key' => 'LBL_ACTIVITIES_SUBPANEL_TITLE',
			'type' => 'collection',
			'subpanel_name' => 'activities',   //this values is not associated with a physical file.
//BEGIN SUGARCRM flav!=dce ONLY
			'header_definition_from_subpanel'=> 'meetings',
//END SUGARCRM flav!=dce ONLY
			'module'=>'Activities',
			
			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopCreateTaskButton'),
//BEGIN SUGARCRM flav!=dce ONLY
				array('widget_class' => 'SubPanelTopScheduleMeetingButton'),
				array('widget_class' => 'SubPanelTopScheduleCallButton'),
//END SUGARCRM flav!=dce ONLY
				array('widget_class' => 'SubPanelTopComposeEmailButton'),
			),	
					
			'collection_list' => array(	
				'tasks' => array(
					'module' => 'Tasks',
					'subpanel_name' => 'ForActivities',
					'get_subpanel_data' => 'tasks',
				),
//BEGIN SUGARCRM flav!=dce ONLY
                'meetings' => array(
                    'module' => 'Meetings',
                    'subpanel_name' => 'ForActivities',
                    'get_subpanel_data' => 'meetings',
                ),
				'calls' => array(
					'module' => 'Calls',
					'subpanel_name' => 'ForActivities',
					'get_subpanel_data' => 'calls',
				),
//END SUGARCRM flav!=dce ONLY
			)			
		),
		'history' => array(
			'order' => 20,
			'sort_order' => 'desc',
			'sort_by' => 'date_entered',
			'title_key' => 'LBL_HISTORY_SUBPANEL_TITLE',
			'type' => 'collection',
			'subpanel_name' => 'history',   //this values is not associated with a physical file.
//BEGIN SUGARCRM flav!=dce ONLY
			'header_definition_from_subpanel'=> 'meetings',
//END SUGARCRM flav!=dce ONLY
			'module'=>'History',
			
			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopCreateNoteButton'),
				array('widget_class' => 'SubPanelTopArchiveEmailButton'),
            	array('widget_class' => 'SubPanelTopSummaryButton'),
			),	
					
			'collection_list' => array(	
				'tasks' => array(
					'module' => 'Tasks',
					'subpanel_name' => 'ForHistory',
					'get_subpanel_data' => 'tasks',
				),
//BEGIN SUGARCRM flav!=dce ONLY
                'meetings' => array(
                    'module' => 'Meetings',
                    'subpanel_name' => 'ForHistory',
                    'get_subpanel_data' => 'meetings',
                ),
				'calls' => array(
					'module' => 'Calls',
					'subpanel_name' => 'ForHistory',
					'get_subpanel_data' => 'calls',
				),
//END SUGARCRM flav!=dce ONLY
				'notes' => array(
					'module' => 'Notes',
					'subpanel_name' => 'ForHistory',
					'get_subpanel_data' => 'notes',
				),	
				'emails' => array(
					'module' => 'Emails',
					'subpanel_name' => 'ForHistory',
					'get_subpanel_data' => 'emails',
				),	
				'linkedemails' => array(
	                'module' => 'Emails',
	                'subpanel_name' => 'ForUnlinkedEmailHistory',
	                'get_subpanel_data' => 'function:get_unlinked_email_query',
	                'generate_select'=>true,
	                'function_parameters' => array('return_as_array'=>'true'),
	    		),          
			)			
		),
		'contacts' => array(
			'order' => 30,
			'module' => 'Contacts',
			'sort_order' => 'asc',
			'sort_by' => 'last_name, first_name',
			'subpanel_name' => 'ForAccounts',
			'get_subpanel_data' => 'contacts',
			'add_subpanel_data' => 'contact_id',
			'title_key' => 'LBL_CONTACTS_SUBPANEL_TITLE',
			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopCreateAccountNameButton'),
				array('widget_class' => 'SubPanelTopSelectButton', 'mode'=>'MultiSelect')
			),

		),		
//BEGIN SUGARCRM flav!=dce ONLY
		'opportunities' => array(
			'order' => 40,
			'module' => 'Opportunities',
			'subpanel_name' => 'ForAccounts',
			'sort_order' => 'desc',
			'sort_by' => 'date_closed',
			'get_subpanel_data' => 'opportunities',
			'add_subpanel_data' => 'opportunity_id',
			'title_key' => 'LBL_OPPORTUNITIES_SUBPANEL_TITLE',
			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopButtonQuickCreate'),
				array('widget_class' => 'SubPanelTopSelectButton', 'mode'=>'MultiSelect')
			),
		),
//BEGIN SUGARCRM flav!=sales ONLY
		'leads' => array(
			'order' => 80,
			'module' => 'Leads',
			'sort_order' => 'asc',
			'sort_by' => 'last_name, first_name',
			'subpanel_name' => 'default',
			'get_subpanel_data' => 'leads',
			'add_subpanel_data' => 'lead_id',
			'title_key' => 'LBL_LEADS_SUBPANEL_TITLE',
			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopCreateLeadNameButton'),
				array('widget_class' => 'SubPanelTopSelectButton',
					'popup_module' => 'Opportunities',
					'mode' => 'MultiSelect', 
				),
			),
			
		),
//END SUGARCRM flav!=sales ONLY
//END SUGARCRM flav!=dce ONLY
//BEGIN SUGARCRM flav!=sales ONLY
		'cases' => array(
			'order' => 100,
			'sort_order' => 'desc',
			'sort_by' => 'case_number',
			'module' => 'Cases',
			'subpanel_name' => 'ForAccounts',
			'get_subpanel_data' => 'cases',
			'add_subpanel_data' => 'case_id',
			'title_key' => 'LBL_CASES_SUBPANEL_TITLE',
			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopButtonQuickCreate'),
				array('widget_class' => 'SubPanelTopSelectButton', 'mode'=>'MultiSelect')
			),
		),
//END SUGARCRM flav!=sales ONLY
//BEGIN SUGARCRM flav!=dce ONLY
//BEGIN SUGARCRM flav=pro ONLY
		'products' => array(
			'order' => 60,
			'module' => 'Products',
			'sort_order' => 'desc',
			'sort_by' => 'date_purchased',
			'subpanel_name' => 'ForAccounts',
			'get_subpanel_data' => 'function:get_products_query',
			'add_subpanel_data' => 'product_id',
			'title_key' => 'LBL_PRODUCTS_SUBPANEL_TITLE',
			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopCreateButton'),
			),
			
		),
//END SUGARCRM flav=pro ONLY
//BEGIN SUGARCRM flav=pro ONLY
		'quotes' => array(
			'order' => 50,
			'sort_order' => 'desc',
			'sort_by' => 'date_quote_expected_closed',
			'module' => 'Quotes',
			'subpanel_name' => 'ForAccounts',
			'get_subpanel_data' => 'quotes',
			'get_distinct_data'=> true,
			'add_subpanel_data' => 'quote_id',
			'title_key' => 'LBL_QUOTES_SUBPANEL_TITLE',
			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopCreateButton'),
			),
		),
//END SUGARCRM flav=pro ONLY
//END SUGARCRM flav!=dce ONLY
		'accounts' => array(
			'order' => 90,
			'sort_order' => 'asc',
			'sort_by' => 'name',
			'module' => 'Accounts',
			'subpanel_name' => 'default',
			'get_subpanel_data' => 'members',
			'add_subpanel_data' => 'member_id',
			'title_key' => 'LBL_MEMBER_ORG_SUBPANEL_TITLE',
			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopButtonQuickCreate'),
				array('widget_class' => 'SubPanelTopSelectButton', 'mode'=>'MultiSelect')
			),
		),
//BEGIN SUGARCRM flav!=dce && flav!=sales ONLY
		'bugs' => array(
			'order' => 110,
			'sort_order' => 'desc',
			'sort_by' => 'bug_number',
			'module' => 'Bugs',
			'subpanel_name' => 'default',
			'get_subpanel_data' => 'bugs',
			'add_subpanel_data' => 'bug_id',
			'title_key' => 'LBL_BUGS_SUBPANEL_TITLE',
			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopButtonQuickCreate'),
				array('widget_class' => 'SubPanelTopSelectButton', 'mode'=>'MultiSelect')
			),
		),
		'project' => array(
			'order' => 120,
			'sort_order' => 'asc',
			'sort_by' => 'name',
			'module' => 'Project',
			'subpanel_name' => 'default',
			'get_subpanel_data' => 'project',
			'add_subpanel_data' => 'project_id',
			'title_key' => 'LBL_PROJECTS_SUBPANEL_TITLE',
			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopButtonQuickCreate'),
			),		
		),		
        //BEGIN SUGARCRM flav=pro ONLY
		'contracts' => array(
			'order' => 70,
			'sort_order' => 'desc',
			'sort_by' => 'end_date',
			'module' => 'Contracts',
			'subpanel_name' => 'ForAccounts',
			'get_subpanel_data' => 'contracts',
			'add_subpanel_data' => 'contract_id',
			'title_key' => 'LBL_CONTRACTS_SUBPANEL_TITLE',
			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopButtonQuickCreate'),
			),			
		),
        //END SUGARCRM flav=pro ONLY
//END SUGARCRM flav!=dce && flav!=sales ONLY
	),
);
?>