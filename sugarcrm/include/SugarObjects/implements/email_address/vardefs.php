<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

$vardefs = array(
    'fields' => array(
        'email'=> array(
            'name' => 'email',
            'type' => 'email',
			'query_type' => 'default',
			'source' => 'non-db',
			'operator' => 'subquery',
			'subquery' => 'SELECT eabr.bean_id FROM email_addr_bean_rel eabr JOIN email_addresses ea ON (ea.id = eabr.email_address_id) WHERE eabr.deleted=0 AND ea.email_address LIKE',
			'db_field' => array(
				'id',
            ),
			'vname' =>'LBL_ANY_EMAIL',
			'studio' => array('visible'=>false, 'searchview'=>true),
            'duplicate_on_record_copy' => 'always',
            'len' => 100,
            'sort_on' => 'email1',
        ),
        'email1' => array(
			'name'		=> 'email1',
			'vname'		=> 'LBL_EMAIL_ADDRESS',
			'type'		=> 'varchar',
			'function'	=> array(
				'name'		=> 'getEmailAddressWidget',
				'returns'	=> 'html',
            ),
			'source'	=> 'non-db',
			'link' => 'email_addresses_primary',
			'rname' => 'email_address',
			'group'=>'email1',
            'merge_filter' => 'enabled',
            'module' => 'EmailAddresses',
		    'studio' => array(
                'editview' => true, 
                'editField' => true, 
                'searchview' => false, 
                'popupsearch' => false
            ),
            'full_text_search' => array(
                'enabled' => true,
                'boost' => 3, 
                'type' => 'email',
            ),
            'duplicate_on_record_copy' => 'always',
        ),
        'email2' => array(
			'name'		=> 'email2',
			'vname'		=> 'LBL_OTHER_EMAIL_ADDRESS',
			'type'		=> 'varchar',
			'function'	=> array(
				'name'		=> 'getEmailAddressWidget',
				'returns'	=> 'html',
            ),
			'source'	=> 'non-db',
			'group'=>'email2',
            'merge_filter' => 'enabled',
		    'studio' => 'false',
            'duplicate_on_record_copy' => 'always',
		),
        'invalid_email' => array(
			'name'		=> 'invalid_email',
			'vname'     => 'LBL_INVALID_EMAIL',
			'source'	=> 'non-db',
			'type'		=> 'bool',
			'link'      => 'email_addresses_primary',
			'rname'     => 'invalid_email',
		    'massupdate' => false,
		    'studio' => 'false',
            'duplicate_on_record_copy' => 'always',
		),
        'email_opt_out' => array(
			'name'		=> 'email_opt_out',
			'vname'     => 'LBL_EMAIL_OPT_OUT',
			'source'	=> 'non-db',
			'type'		=> 'bool',
			'link'      => 'email_addresses_primary',
			'rname'     => 'opt_out',
		    'massupdate' => false,
			'studio'=>'false',
            'duplicate_on_record_copy' => 'always',
		),
        'email_addresses_primary' => array (
            'name' => 'email_addresses_primary',
            'type' => 'link',
            'relationship' => strtolower($module).'_email_addresses_primary',
            'source' => 'non-db',
            'vname' => 'LBL_EMAIL_ADDRESS_PRIMARY',
            'duplicate_merge' => 'disabled',
        ),
        'email_addresses' => array (
            'name' => 'email_addresses',
            'type' => 'link',
            'relationship' => strtolower($module).'_email_addresses',
            'source' => 'non-db',
            'vname' => 'LBL_EMAIL_ADDRESSES',
            'reportable'=>false,
            'unified_search' => true,
            'rel_fields' => array('primary_address' => array('type'=>'bool')),
        ),
    ),
    'relationships' => array(
        strtolower($module).'_email_addresses' => array(
            'lhs_module'=> $module, 
            'lhs_table'=> strtolower($module), 
            'lhs_key' => 'id',
            'rhs_module'=> 'EmailAddresses', 
            'rhs_table'=> 'email_addresses', 
            'rhs_key' => 'id',
            'relationship_type'=>'many-to-many',
            'join_table'=> 'email_addr_bean_rel', 
            'join_key_lhs'=>'bean_id', 
            'join_key_rhs'=>'email_address_id', 
            'relationship_role_column'=>'bean_module',
            'relationship_role_column_value'=>$module,
        ),
        strtolower($module).'_email_addresses_primary' => array(
            'lhs_module'=> $module, 
            'lhs_table'=> strtolower($module), 
            'lhs_key' => 'id',
            'rhs_module'=> 'EmailAddresses', 
            'rhs_table'=> 'email_addresses', 
            'rhs_key' => 'id',
            'relationship_type'=>'many-to-many',
            'join_table'=> 'email_addr_bean_rel', 
            'join_key_lhs'=>'bean_id', 
            'join_key_rhs'=>'email_address_id', 
            'relationship_role_column'=>'primary_address', 
            'relationship_role_column_value'=>'1'
        ),
    ),
    'indices' => array(
    ),
    'acls'=>array(
        'SugarACLEmailAddress'=>true
    ),
);

