<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * Layout definition for Bugs
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
 
// $Id: layout_defs.php 14232 2006-06-23 00:33:16Z wayne $

$layout_defs['Bugs'] = array(
	// list of what Subpanels to show in the DetailView 
	'subpanel_setup' => array(
        'contacts' => array(
			'order' => 30,
			'module' => 'Contacts',
			'sort_order' => 'asc',
			'sort_by' => 'last_name, first_name',
			'subpanel_name' => 'default',
			'get_subpanel_data' => 'contacts',
			'add_subpanel_data' => 'contact_id',
			'title_key' => 'LBL_CONTACTS_SUBPANEL_TITLE',
            'top_buttons' => array(
                array('widget_class' => 'SubPanelTopButtonQuickCreate'),
                array('widget_class' => 'SubPanelTopSelectButton', 'mode'=>'MultiSelect')
            ),
		),
        'activities' => array(
			'order' => 10,
			'sort_order' => 'desc',
			'sort_by' => 'date_start',
			'title_key' => 'LBL_ACTIVITIES_SUBPANEL_TITLE',
			'type' => 'collection',
			'subpanel_name' => 'activities',   //this values is not associated with a physical file.
			'module'=>'Activities',

			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopCreateTaskButton'),
				array('widget_class' => 'SubPanelTopScheduleMeetingButton'),
				array('widget_class' => 'SubPanelTopScheduleCallButton'),
				array('widget_class' => 'SubPanelTopComposeEmailButton'),
			),

			'collection_list' => array(	
				'meetings' => array(
					'module' => 'Meetings',
					'subpanel_name' => 'ForActivities',
					'get_subpanel_data' => 'meetings',
				),
				'tasks' => array(
					'module' => 'Tasks',
					'subpanel_name' => 'ForActivities',
					'get_subpanel_data' => 'tasks',
				),
				'calls' => array(
					'module' => 'Calls',
					'subpanel_name' => 'ForActivities',
					'get_subpanel_data' => 'calls',
				),	
			)			
		),
        'history' => array(
			'order' => 20,
			'sort_order' => 'desc',
			'sort_by' => 'date_entered',
			'title_key' => 'LBL_HISTORY_SUBPANEL_TITLE',
			'type' => 'collection',
			'subpanel_name' => 'history',   //this values is not associated with a physical file.
			'module'=>'History',

			'top_buttons' => array(
			array('widget_class' => 'SubPanelTopCreateNoteButton'),
			array('widget_class' => 'SubPanelTopArchiveEmailButton'),
            array('widget_class' => 'SubPanelTopSummaryButton'),
			),

			'collection_list' => array(	
				'meetings' => array(
					'module' => 'Meetings',
					'subpanel_name' => 'ForHistory',
					'get_subpanel_data' => 'meetings',
				),
				'tasks' => array(
					'module' => 'Tasks',
					'subpanel_name' => 'ForHistory',
					'get_subpanel_data' => 'tasks',
				),
				'calls' => array(
					'module' => 'Calls',
					'subpanel_name' => 'ForHistory',
					'get_subpanel_data' => 'calls',
				),	
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
			)			
		),
        'documents' => array(
            'order' => 25,
            'module' => 'Documents',
            'subpanel_name' => 'default',
            'sort_order' => 'asc',
            'sort_by' => 'id',
            'title_key' => 'LBL_DOCUMENTS_SUBPANEL_TITLE',
            'get_subpanel_data' => 'documents',
            'top_buttons' => 
            array (
                0 => 
                array (
                    'widget_class' => 'SubPanelTopButtonQuickCreate',
                    ),
                1 => 
                array (
                    'widget_class' => 'SubPanelTopSelectButton',
                    'mode' => 'MultiSelect',
                    ),
                ),
        ),
        'accounts' => array(
			'order' => 40,
			'module' => 'Accounts',
			'sort_order' => 'asc',
			'sort_by' => 'name',
			'subpanel_name' => 'default',
			'get_subpanel_data' => 'accounts',
			'add_subpanel_data' => 'account_id',
			'title_key' => 'LBL_ACCOUNTS_SUBPANEL_TITLE',
            'top_buttons' => array(
                array('widget_class' => 'SubPanelTopButtonQuickCreate'),
                array('widget_class' => 'SubPanelTopSelectButton', 'mode'=>'MultiSelect')
            ),
		),
        'cases' => array(
			'order' => 50,
			'module' => 'Cases',
			'sort_order' => 'desc',
			'sort_by' => 'case_number',
			'subpanel_name' => 'default',
			'get_subpanel_data' => 'cases',
			'add_subpanel_data' => 'case_id',
			'title_key' => 'LBL_CASES_SUBPANEL_TITLE',
            'top_buttons' => array(
                array('widget_class' => 'SubPanelTopButtonQuickCreate'),
                array('widget_class' => 'SubPanelTopSelectButton', 'mode'=>'MultiSelect'),
            ),				
		),
		'project' => array(
			'order' => 60,
			'sort_order' => 'asc',
			'sort_by' => 'name',
			'module' => 'Project',
			'subpanel_name' => 'default',
			'get_subpanel_data' => 'project',
			'add_subpanel_data' => 'project_id',
			'title_key' => 'LBL_PROJECTS_SUBPANEL_TITLE',
			'top_buttons' => array(
                array('widget_class' => 'SubPanelTopButtonQuickCreate'),
                array('widget_class' => 'SubPanelTopSelectButton'),
            ),
        ),
	),
);
?>