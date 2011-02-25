<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

global $current_user;

$dashletData['MyNotesDashlet']['searchFields'] = array('date_entered'     => array('default' => ''),
														//BEGIN SUGARCRM flav=pro ONLY
														'team_id'          => array('default' => '', 'label'=>'LBL_TEAMS'),
														//END SUGARCRM flav=pro ONLY
														'assigned_user_id' => array('type'    => 'assigned_user_name',
																					'label'   => 'LBL_ASSIGNED_TO', 
																					'default' => $current_user->name),
																					'name' => array( 'default'=>''),
														);
                                                                                           
$dashletData['MyNotesDashlet']['columns'] = array (
											  'name' => 
											  array (
											    'width' => '40%',
											    'label' => 'LBL_LIST_SUBJECT',
											    'link' => true,
											    'default' => true,
											  ),
											  'contact_name' => 
											  array (
											    'width' => '20%',
											    'label' => 'LBL_LIST_CONTACT',
											    'link' => true,
											    'id' => 'CONTACT_ID',
											    'module' => 'Contacts',
											    'default' => true,
											    'ACLTag' => 'CONTACT',
											    'related_fields' => 
											    array (
											      0 => 'contact_id',
											    ),
											  ),
											  'parent_name' => 
											  array (
											    'width' => '20%',
											    'label' => 'LBL_LIST_RELATED_TO',
											    'dynamic_module' => 'PARENT_TYPE',
											    'id' => 'PARENT_ID',
											    'link' => true,
											    'default' => true,
											    'sortable' => false,
											    'ACLTag' => 'PARENT',
											    'related_fields' => 
											    array (
											      0 => 'parent_id',
											      1 => 'parent_type',
											    ),
											  ),  
											  //BEGIN SUGARCRM flav=pro ONLY
											  'doc_type' =>
											  array(
												'width' => '5%', 
											  	'label' => 'LBL_DOC_TYPE',
											    'link' => false,
											 	'default' => true,
											  ), 
											   //END SUGARCRM flav=pro ONLY
											  'filename' => 
											  array (
											    'width' => '20%',
											    'label' => 'LBL_LIST_FILENAME',
											    'default' => true,
											    'type' => 'file',
											    'related_fields' => 
											    array (
											      0 => 'file_url',
											      1 => 'id',
											      2 => 'doc_id',
											      3 => 'doc_type',
											    ),
											    'displayParams' =>
											    array(
											      'module' => 'Notes',
											    ),
											  ),
											  'created_by_name' => 
											  array (
											    'type' => 'relate',
											    'label' => 'LBL_CREATED_BY',
											    'width' => '10%',
											    'default' => true,
											  ),
											  'date_entered' => 
											  array (
											    'type' => 'datetime',
											    'label' => 'LBL_DATE_ENTERED',
											    'width' => '10%',
											    'default' => false,
											  ),
											  'date_modified' => 
											  array (
											    'width' => '20%',
											    'label' => 'LBL_DATE_MODIFIED',
											    'link' => false,
											    'default' => false,
											  ),
											  //BEGIN SUGARCRM flav=pro ONLY
											  'team_name' => array(
											    'width' => '2', 
											    'label' => 'LBL_LIST_TEAM',
											    'default' => false
											  ),        
											  //END SUGARCRM flav=pro ONLY
											);
											?>
