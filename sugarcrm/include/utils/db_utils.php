<?php
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

 // $Id: db_utils.php 56343 2010-05-10 21:18:10Z jenny $

/**
 * @deprecated use DBManager::convert() instead.
 */
function db_convert($string, $type, $additional_parameters=array(),$additional_parameters_oracle_only=array())
	{
    return $GLOBALS['db']->convert($string, $type, $additional_parameters, $additional_parameters_oracle_only);
            }

/**
 * @deprecated use DBManager::concat() instead.
 */
function db_concat($table, $fields)
	{
    return $GLOBALS['db']->concat($table, $fields);
}

/**
 * @deprecated use DBManager::fromConvert() instead.
 */
function from_db_convert($string, $type)
	{
    return $GLOBALS['db']->fromConvert($string, $type);
	}

$toHTML = array(
	'"' => '&quot;',
	'<' => '&lt;',
	'>' => '&gt;',
	"'" => '&#039;',
);
$GLOBALS['toHTML_keys'] = array_keys($toHTML);
$GLOBALS['toHTML_values'] = array_values($toHTML);

/**
 * Replaces specific characters with their HTML entity values
 * @param string $string String to check/replace
 * @param bool $encode Default true
 * @return string
 *
 * @todo Make this utilize the external caching mechanism after re-testing (see
 *       log on r25320).
 */
function to_html($string, $encode=true){
	if (empty($string)) {
		return $string;
	}
	static $cache = array();
	global $toHTML;
	if (isset($cache['c'.$string])) {
	    return $cache['c'.$string];
	}

	$cache_key = 'c'.$string;

	if($encode && is_string($string)){//$string = htmlentities($string, ENT_QUOTES);
		/*
		 * cn: bug 13376 - handle ampersands separately
		 * credit: ashimamura via bug portal
		 */
		//$string = str_replace("&", "&amp;", $string);

		if(is_array($toHTML)) { // cn: causing errors in i18n test suite ($toHTML is non-array)
			$string = str_replace(
				$GLOBALS['toHTML_keys'],
				$GLOBALS['toHTML_values'],
				$string
			);
		}
	}
	$cache[$cache_key] = $string;
	return $cache[$cache_key];
}

/**
 * Replaces specific HTML entity values with the true characters
 * @param string $string String to check/replace
 * @param bool $encode Default true
 * @return string
 */
function from_html($string, $encode=true) {
    if (!is_string($string) || !$encode) {
        return $string;
    }

	global $toHTML;
    static $toHTML_values = null;
    static $toHTML_keys = null;
    static $cache = array();
    if (!isset($toHTML_values) || !empty($GLOBALS['from_html_cache_clear'])) {
        $toHTML_values = array_values($toHTML);
        $toHTML_keys = array_keys($toHTML);
    }

    // Bug 36261 - Decode &amp; so we can handle double encoded entities
	$string = str_replace("&amp;", "&", $string);

    if (!isset($cache[$string])) {
        $cache[$string] = str_replace($toHTML_values, $toHTML_keys, $string);
    }
    return $cache[$string];
}

/**
 * @deprecated
 * @todo this function is only used by one function ( run_upgrade_wizard_sql() ), which isn't
 *       used either; trying kill this off
 */
function run_sql_file( $filename )
{
    if( !is_file( $filename ) ){
        print( "Could not find file: $filename <br>" );
        return( false );
    }


    $contents = sugar_file_get_contents($filename);

    $lastsemi   = strrpos( $contents, ';') ;
    $contents   = substr( $contents, 0, $lastsemi );
    $queries    = explode( ';', $contents );
    $db         = DBManagerFactory::getInstance();

    foreach( $queries as $query ){
        if( !empty($query) ){
        	//BEGIN SUGARCRM flav=int ONLY
			// BUG 10339: wrapping print statement in INT tag to prevent from being sent in builds
            print( "Sending query: $query ;<br>" );
            //END SUGARCRM flav=int ONLY
			if($db->dbType == 'oci8')
			{
			//BEGIN SUGARCRM flav=ent ONLY
				$db->query( $query, true, "An error has occured while running.<br>" );
			//END SUGARCRM flav=ent ONLY
			}
			else
			{
				$db->query( $query.';', true, "An error has occured while running.<br>" );
			}
        }
    }
    return( true );
}

?>
