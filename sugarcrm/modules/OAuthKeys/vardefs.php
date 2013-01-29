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
$dictionary['OAuthKey'] = array('table' => 'oauth_consumer',
	//BEGIN SUGARCRM flav=pro ONLY
    'favorites'=>false,
    //END SUGARCRM flav=pro ONLY
	'comment' => 'OAuth consumer keys',
	'audited'=>false,
	'fields' => array (
          'c_key' =>
          array (
            'name' => 'c_key',
            'vname' => 'LBL_CONSKEY',
            'type' => 'varchar',
            'required' => true,
            'comment' => 'Consumer public key',
            'importable' => 'required',
        	'massupdate' => 0,
            'reportable'=>false,
        	'studio' => 'hidden',
          ),
          'c_secret' =>
          array (
            'name' => 'c_secret',
            'vname' => 'LBL_CONSSECRET',
            //'type' => 'encrypt',
            'type' => 'varchar',
          	'required' => true,
            'comment' => 'Consumer secret key',
            'importable' => 'required',
        	'massupdate' => 0,
            'reportable'=>false,
        	'studio' => 'hidden',
          ),
          'tokens' =>
          array (
            'name' => 'tokens',
            'type' => 'link',
            'relationship' => 'consumer_tokens',
            'module'=>'OAuthTokens',
            'bean_name'=>'OAuthToken',
            'source'=>'non-db',
            'vname'=>'LBL_TOKENS',
          ),
          'oauth_type' =>
          array (
            'name' => 'oauth_type',
            'type' => 'enum',
            'options' => 'oauth_type_dom',
            'len' => 50,
            'comment' => 'Is this client an OAuth1 or OAuth2 client',
            'default'=>'oauth1',
            'vname'=>'LBL_OAUTH_TYPE',
          ),
          'client_type' =>
          array (
            'name' => 'client_type',
            'type' => 'enum',
            'options' => 'oauth_client_type_dom',
            'len' => 50,
            'comment' => 'What type of client does this key belong to, mobile, portal, UI or other.',
            'default' => 'user',
            'vname'=>'LBL_CLIENT_TYPE',
            'dependency'=>'equal($oauth_type,"oauth2")',
          ),
    ),
    'acls' => array('SugarACLAdminOnly' => true, 'SugarACLOAuthKeys' => true),
    'indices' => array (
       array('name' =>'ckey', 'type' =>'unique', 'fields'=>array('c_key')),
    )
);
if (!class_exists('VardefManager')){
        require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef('OAuthKeys','OAuthKey', array('basic','assignable'));
