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
$dictionary['Meeting'] = array('table' => 'meetings', 
	'unified_search' => true,
	'comment' => 'Meeting activities'
                               ,'fields' => array (
  'name' =>
  array (
    'name' => 'name',
    'vname' => 'LBL_SUBJECT',
    'required' => true,
    'type' => 'name',
    'dbType' => 'varchar',
	'unified_search' => true,
    'len' => '50',
    'comment' => 'Meeting name',
    'importable' => 'required',
  ),
  'accept_status' => array (
    'name' => 'accept_status',
    'vname' => 'LBL_SUBJECT',
    'type' => 'varchar',
    'dbType' => 'varchar',
    'len' => '20',
    'source'=>'non-db',
  ),
  'location' =>
  array (
    'name' => 'location',
    'vname' => 'LBL_LOCATION',
    'type' => 'varchar',
    'len' => '50',
    'comment' => 'Meeting location'
  ),
  'password' =>
  array (
    'name' => 'password',
    'vname' => 'LBL_PASSWORD',
    'type' => 'varchar',
    'len' => '50',
    'comment' => 'Meeting password',
    'dependency' => 'isInEnum($type,getDD("extapi_meeting_password"))',
  ),
  'join_url' =>
  array (
    'name' => 'join_url',
    'vname' => 'LBL_URL',
    'type' => 'varchar',
    'len' => '200',
    'comment' => 'Join URL',
    'studio' => false,
  ),
  'host_url' =>
  array (
    'name' => 'host_url',
    'vname' => 'LBL_URL',
    'type' => 'varchar',
    'len' => '400',
    'comment' => 'Host URL',
    'studio' => false,
  ),
  'displayed_url' =>
  array (
    'name' => 'displayed_url',
    'vname' => 'LBL_URL',
    'type' => 'url',
    'len' => '400',
    'comment' => 'Meeting URL',
  ),
  'creator' =>
  array (
    'name' => 'creator',
    'vname' => 'LBL_CREATOR',
    'type' => 'varchar',
    'len' => '50',
    'comment' => 'Meeting creator',
    'studio' => false,
  ),
  'external_id' =>
  array (
    'name' => 'external_id',
    'vname' => 'LBL_EXTERNALID',
    'type' => 'varchar',
    'len' => '50',
    'comment' => 'Meeting ID for external app API',
    'studio' => false,
   ),
  'duration_hours' =>
  array (
    'name' => 'duration_hours',
    'vname' => 'LBL_DURATION_HOURS',
    'type' => 'int',
    'len' => '2',
    'comment' => 'Duration (hours)',
    'importable' => 'required',
    'required' => true,
  ),
  'duration_minutes' =>
  array (
    'name' => 'duration_minutes',
    'vname' => 'LBL_DURATION_MINUTES',
    'type' => 'int',
    'group'=>'duration_hours',
    'function' => array('name'=>'getDurationMinutesOptions', 'returns'=>'html', 'include'=>'modules/Calls/CallHelper.php'),
    'len' => '2',
    'comment' => 'Duration (minutes)'
  ),
  'date_start' =>
  array (
    'name' => 'date_start',
    'vname' => 'LBL_DATE',
    'type' => 'datetimecombo',
    'dbType' => 'datetime',
    'comment' => 'Date of start of meeting',
    'importable' => 'required',
    'required' => true,
  ),

  'date_end' =>
  array (
    'name' => 'date_end',
    'vname' => 'LBL_DATE_END',
    'type' => 'date',
    'massupdate'=>false,
    'comment' => 'Date meeting ends'
  ),
  'parent_type' =>
  array (
    'name' => 'parent_type',
    'vname'=>'LBL_LIST_RELATED_TO',
    'type' =>'parent_type',
    'dbType' => 'varchar',
    'group'=>'parent_name',
    'len' => 100,
    'comment' => 'Module meeting is associated with'
  ),
  'status' =>
  array (
    'name' => 'status',
    'vname' => 'LBL_STATUS',
    'type' => 'enum',
    'len' => 100,
    'options' => 'meeting_status_dom',
    'comment' => 'Meeting status (ex: Planned, Held, Not held)'
  ),
  'type' =>
   array (
     'name' => 'type',
      'vname' => 'LBL_TYPE',
      'type' => 'enum',
      'len' => 255,
      'function' => 'getMeetingsExternalApiDropDown',
      'comment' => 'Meeting type (ex: WebEx, Other)' 
   ),
  // Bug 24170 - Added only to allow the sidequickcreate form to work correctly
  'direction' =>
  array (
    'name' => 'direction',
    'vname' => 'LBL_DIRECTION',
    'type' => 'enum',
    'len' => 100,
    'options' => 'call_direction_dom',
    'comment' => 'Indicates whether call is inbound or outbound',
    'source' => 'non-db',
    'importable' => 'false',
    'massupdate'=>false,
    'reportable'=>false,
	'studio' => false,
  ),
  'parent_id' =>
  array (
    'name' => 'parent_id',
    'vname'=>'LBL_LIST_RELATED_TO',
    'type' => 'id',
    'group'=>'parent_name',
    'reportable'=>false,
    'comment' => 'ID of item indicated by parent_type'
  ),
  'reminder_checked'=>array(
    'name' => 'reminder_checked',
    'vname' => 'LBL_REMINDER',
    'type' => 'bool',
    'source' => 'non-db',
    'comment' => 'checkbox indicating whether or not the reminder value is set (Meta-data only)',
    'massupdate'=>false,
   ),

  'reminder_time' =>
  array (
    'name' => 'reminder_time',
    'vname' => 'LBL_REMINDER_TIME',
    'type' => 'int',
    'function' => array('name'=>'getReminderTime', 'returns'=>'html', 'include'=>'modules/Calls/CallHelper.php', 'onListView'=>true ),
    'reportable' => false,
    'default'=>-1,
    'comment' => 'Specifies when a reminder alert should be issued; -1 means no alert; otherwise the number of seconds prior to the start'
  ),
   'outlook_id' =>
  array (
    'name' => 'outlook_id',
    'vname' => 'LBL_OUTLOOK_ID',
    'type' => 'varchar',
    'len' => '255',
    'reportable' => false,
    'comment' => 'When the Sugar Plug-in for Microsoft Outlook syncs an Outlook appointment, this is the Outlook appointment item ID'
  ),

  'contact_name' =>
  array (
    'name' => 'contact_name',
    'rname' => 'last_name',
    'db_concat_fields'=> array(0=>'first_name', 1=>'last_name'),
    'id_name' => 'contact_id',
    'massupdate' => false,
    'vname' => 'LBL_CONTACT_NAME',
    'type' => 'relate',
    'link'=>'contacts',
    'table' => 'contacts',
    'isnull' => 'true',
    'module' => 'Contacts',
    'join_name' => 'contacts',
    'dbType' => 'varchar',
    'source'=>'non-db',
    'len' => 36,
  	'studio' => 'false',
	),

  'contacts' =>
  array (
  	'name' => 'contacts',
    'type' => 'link',
    'relationship' => 'meetings_contacts',
    'source'=>'non-db',
		'vname'=>'LBL_CONTACTS',
  ),
   'parent_name'=>
 	array(
		'name'=> 'parent_name',
		'parent_type'=>'record_type_display' ,
		'type_name'=>'parent_type',
		'id_name'=>'parent_id',
		'vname'=>'LBL_LIST_RELATED_TO',
		'type'=>'parent',
		'group'=>'parent_name',
		'source'=>'non-db',
		'options'=> 'parent_type_display',
		),
  'users' =>
  array (
  	'name' => 'users',
    'type' => 'link',
    'relationship' => 'meetings_users',
    'source'=>'non-db',
		'vname'=>'LBL_USERS',
  ),
  'accounts' =>
  array (
  	'name' => 'accounts',
    'type' => 'link',
    'relationship' => 'account_meetings',
    'source'=>'non-db',
		'vname'=>'LBL_ACCOUNT',
  ),
  'leads' =>
  array (
    'name' => 'leads',
    'type' => 'link',
    'relationship' => 'meetings_leads',
    'source'=>'non-db',
        'vname'=>'LBL_LEADS',
  ),
  'opportunity' =>
  array (
  	'name' => 'opportunity',
    'type' => 'link',
    'relationship' => 'opportunity_meetings',
    'source'=>'non-db',
		'vname'=>'LBL_OPPORTUNITY',
  ),
  'case' =>
  array (
  	'name' => 'case',
    'type' => 'link',
    'relationship' => 'case_meetings',
    'source'=>'non-db',
		'vname'=>'LBL_CASE',
  ),
    'notes' =>
  array (
  	'name' => 'notes',
    'type' => 'link',
    'relationship' => 'meetings_notes',
    'module'=>'Notes',
    'bean_name'=>'Note',
    'source'=>'non-db',
		'vname'=>'LBL_NOTES',
  ),
	'contact_id' => array(
		'name' => 'contact_id',
		'type' => 'id',
		'source' => 'non-db',
	),
),
 'relationships' => array (
	  'meetings_assigned_user' =>
   array('lhs_module'=> 'Users', 'lhs_table'=> 'users', 'lhs_key' => 'id',
   'rhs_module'=> 'Meetings', 'rhs_table'=> 'meetings', 'rhs_key' => 'assigned_user_id',
   'relationship_type'=>'one-to-many')

   ,'meetings_modified_user' =>
   array('lhs_module'=> 'Users', 'lhs_table'=> 'users', 'lhs_key' => 'id',
   'rhs_module'=> 'Meetings', 'rhs_table'=> 'meetings', 'rhs_key' => 'modified_user_id',
   'relationship_type'=>'one-to-many')

   ,'meetings_created_by' =>
   array('lhs_module'=> 'Users', 'lhs_table'=> 'users', 'lhs_key' => 'id',
   'rhs_module'=> 'Meetings', 'rhs_table'=> 'meetings', 'rhs_key' => 'created_by',
   'relationship_type'=>'one-to-many')

	,'meetings_notes' => array('lhs_module'=> 'Meetings', 'lhs_table'=> 'meetings', 'lhs_key' => 'id',
							  'rhs_module'=> 'Notes', 'rhs_table'=> 'notes', 'rhs_key' => 'parent_id',
							  'relationship_type'=>'one-to-many', 'relationship_role_column'=>'parent_type',
							  'relationship_role_column_value'=>'Meetings')
	)

                                                      , 'indices' => array (
       array('name' =>'idx_mtg_name', 'type'=>'index', 'fields'=>array('name')),
       array('name' =>'idx_meet_par_del', 'type'=>'index', 'fields'=>array('parent_id','parent_type','deleted')),
       array('name' => 'idx_meet_stat_del', 'type' => 'index', 'fields'=> array('assigned_user_id', 'status', 'deleted')),
      
                                                   )
//This enables optimistic locking for Saves From EditView
	,'optimistic_locking'=>true,
                            );

VardefManager::createVardef('Meetings','Meeting', array('default', 'assignable',
//BEGIN SUGARCRM flav=pro ONLY
'team_security',
//END SUGARCRM flav=pro ONLY
));
?>
