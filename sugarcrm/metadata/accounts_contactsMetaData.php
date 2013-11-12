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
$dictionary['accounts_contacts'] = array (
    'table' => 'accounts_contacts',
    'fields' => array(
        'id' => array(
            'name' => 'id',
            'type' => 'varchar',
            'len' => '36',
        ),
        'contact_id' => array(
            'name' => 'contact_id',
            'type' => 'varchar',
            'len' => '36',
        ),
        'account_id' => array(
            'name' => 'account_id',
            'type' => 'varchar',
            'len' => '36',
        ),
        'date_modified' => array (
            'name' => 'date_modified',
            'type' => 'datetime',
        ),
        'primary_account' => array(
            'name' => 'primary_account',
            'type' => 'bool',
            'default' => '0',
        ),
        'deleted' => array(
            'name' => 'deleted',
            'type' => 'bool',
            'len' => '1',
            'required' => false,
            'default' => '0',
        ),
    ),
    'indices' => array (
        array(
            'name' =>'accounts_contactspk',
            'type' =>'primary',
            'fields'=>array('id'),
        ),
        array(
            'name' => 'idx_account_contact',
            'type'=>'alternate_key',
            'fields'=>array('account_id','contact_id'),
        ),
        array(
            'name' => 'idx_contid_del_accid',
            'type' => 'index',
            'fields'=> array('contact_id', 'deleted', 'account_id'),
        ),
    ),
    'relationships' => array (
        'accounts_contacts' => array(
            'lhs_module' => 'Accounts',
            'lhs_table' => 'accounts',
            'lhs_key' => 'id',
            'rhs_module' => 'Contacts',
            'rhs_table' => 'contacts',
            'rhs_key' => 'id',
            'relationship_type' => 'many-to-many',
            'join_table' => 'accounts_contacts',
            'join_key_lhs' => 'account_id',
            'join_key_rhs' => 'contact_id',
            'primary_flag_column' => 'primary_account',
            'primary_flag_side' => 'rhs',
            'primary_flag_default' => true,
        ),
    ),
);
