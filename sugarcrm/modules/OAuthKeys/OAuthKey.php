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
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once 'Zend/Oauth/Provider.php';

class OAuthKey extends Basic
{
	public $module_dir = 'OAuthKeys';
	public $object_name = 'OAuthKey';
	public $table_name = 'oauth_consumer';
	public $c_key;
	public $c_secret;
	public $name;
	public $disable_row_level_security = true;

	static public $keys_cache = array();

	/**
	 * Get record by consumer key
	 * @param string $key
     * @param string $oauth_type Either "oauth1" or "oauth2", defaults to "oauth1"
	 */
	public function getByKey($key,$oauth_type="oauth1")
	{
	    $this->retrieve_by_string_fields(array("c_key" => $key,"oauth_type"=>$oauth_type));
	    if(empty($this->id)) return false;
	    // need this to decrypt the key
        $this->check_date_relationships_load();
	    return $this;
	}

	/**
	 * Fetch customer key by id
	 * @param string $key
     * @param string $oauth_type Either "oauth1" or "oauth2", defaults to "oauth1"
	 */
	public static function fetchKey($key,$oauth_type="oauth1")
	{
	    if(isset(self::$keys_cache[$key])&&self::$keys_cache[$key]->oauth_type==$oauth_type) {
	        return self::$keys_cache[$key];
	    }
	    $k = new self();
	    if($k->getByKey($key,$oauth_type)) {
	        self::$keys_cache[$key] = $k;
	        return $k;
	    }
	    return false;
	}

	public function mark_deleted($id)
	{
	    $this->db->query("DELETE from {$this->table_name} WHERE id='".$this->db->quote($id)."'");
	    $this->db->query("DELETE from oauth_tokens WHERE consumer='".$this->db->quote($id)."'");
	}

}
