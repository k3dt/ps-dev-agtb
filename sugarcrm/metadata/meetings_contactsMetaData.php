<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the Licenseand You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall notamong other things: 1) sublicense, resell, rent, lease, redistributeassign or
 *otherwise transfer Your rights to the Softwareand 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 *(i) the "Powered by SugarCRM" logo and
 *(ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright(C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
$dictionary['meetings_contacts'] = array(
	'table'=> 'meetings_contacts',
	'fields'=> array(
		array(	'name'			=> 'id', 
				'type'			=> 'id', 
				'len'			=> '36'
		),
		array(	'name'			=> 'meeting_id', 
				'type'			=> 'id', 
				'len'			=> '36',
		),
		array(	'name'			=> 'contact_id', 
				'type'			=> 'id', 
				'len'			=> '36',
		),
		array(	'name'			=> 'required', 
				'type'			=> 'varchar', 
				'len'			=> '1', 
				'default'		=> '1',
		),
		array(	'name'			=> 'accept_status', 
				'type'			=> 'varchar', 
				'len'			=> '25', 
				'default'		=> 'none'
		),
		array(	'name'			=> 'date_modified',
				'type'			=> 'datetime'
		),
		array(	'name'			=> 'deleted', 
				'type'			=> 'bool', 
				'len'			=> '1', 
				'default'		=> '0', 
				'required'		=> false
		),
 	), 
	'indices' => array(
 		array(	'name'			=> 'meetings_contactspk', 
				'type'			=> 'primary', 
				'fields'		=> array('id'),
		),
		array(	'name'			=> 'idx_con_mtg_mtg', 
				'type'			=> 'index', 
				'fields'		=> array('meeting_id'),
		),
		array(	'name'			=> 'idx_con_mtg_con', 
				'type'			=> 'index', 
				'fields'		=> array('contact_id'),
		),
		array(	'name'			=> 'idx_meeting_contact', 
				'type'			=> 'alternate_key', 
				'fields'		=> array('meeting_id','contact_id'),
		),
	),
	'relationships' => array(
		'meetings_contacts' => array(
			'lhs_module'		=> 'Meetings', 
			'lhs_table'			=> 'meetings', 
			'lhs_key'			=> 'id',
			'rhs_module'		=> 'Contacts', 
			'rhs_table'			=> 'contacts', 
			'rhs_key'			=> 'id',
			'relationship_type'	=> 'many-to-many',
			'join_table'		=> 'meetings_contacts', 
			'join_key_lhs'		=> 'meeting_id', 
			'join_key_rhs'		=> 'contact_id',
		),
	),
);
?>