<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


/**
 * Helper class for search engine mappings
 * @api
 */
class SugarSearchEngineMappingHelper
{
    /**
     * mapping map
     * This defines the vardefs to search engine mapping.
     * Technically we only need to define them if the vardef name is different from the search engine mapping name.
     * But it won't hurt to define them even if they are the same.
     * @var array
     */
    protected static $mappingMap = array (
        'Elastic' => array (
            'boost' => 'boost',
            'analyzer' => 'analyzer',
            'type' => 'type',
        ),
    );

    /**
     * non string type map
     * sugar vardef type to search engine type mapping
     * @var array
     */
    private static $typeMap = array(
        'Elastic' => array (
            // searching string in non string types seems to cause elastic to return 500 error
            // for example, search 'aaa' in case_number field (type=long) when no data indexed causes error
            // we also need to figure out how date works with date format
            // so use only strings for now
            /*
            'type' => array(
                'bool' => 'boolean',
                'int' => 'long',
                'currency' => 'double',
                'date' => 'date',
                'datetime' => 'date',
            ),
            'dbType' => array(
                'decimal' => 'double',
            ),
            */
           'type' => array(
                'datetimecombo'  =>  'date',
    			'relate' => 'string',
            ),
        ),
    );

    /**
     *
     * Default field type to analyzer map
     * @var array
     */
    protected static $analyzerMap = array(
        'Elastic' => array(
            'string' => 'standard',
        ),
    );


    /**
     *
     * Given a search engine name and a vardef name, this function returns corresponding search engine map type.
     *
     * @param $name search engine name
     * @param $sugarName vardef name
     *
     * @return string search engine map name, or the original name if the mapping is not found
     */
    public static function getMappingName($name, $sugarName)
    {
        if (isset(self::$mappingMap[$name]) && isset(self::$mappingMap[$name][$sugarName]))
        {
            return self::$mappingMap[$name][$sugarName];
        }

        return $sugarName;
    }
    /**
     *
     * This function returns search engine dependent field type.
     *
     * @param $name search engine name
     * @param $fieldDefs array of field definitions
     *
     * @return string search engine dependent type
     */
    public static function getTypeFromSugarType($name, $fieldDef)
    {
        $searchEngineType = '';
        if (isset($fieldDef['type']))
        {
            $sugarType = $fieldDef['type'];
            if (isset(self::$typeMap[$name]['type'][$sugarType]))
            {
                $searchEngineType = self::$typeMap[$name]['type'][$sugarType];
            }
        }

        if (empty($searchEngineType) && isset($fieldDef['dbType']))
        {
            $sugarType = $fieldDef['dbType'];
            if (isset(self::$typeMap[$name]['dbType'][$sugarType]))
            {
                $searchEngineType = self::$typeMap[$name]['dbType'][$sugarType];
            }
        }

        if (empty($searchEngineType))
        {
            $searchEngineType = 'string'; // default
        }

        return $searchEngineType;
    }

    /**
     * 
     * Return analyzer based on the field type
     * @param string $name   search engine name
     * @param string $params elastic field parameters
     */
    public static function getAnalyzerFromType($name, $params)
    {
    	// dont return analyzer if not index or analyzed
    	if (isset($params['index']) && ($params['index'] == 'not_analyzed' || $params['index'] == 'no')) {
    		return false;
    	}

    	// return from analyzer map or default
    	$type = $params['type'];
        if (isset(self::$analyzerMap[$name][$type])) {
            return self::$analyzerMap[$name][$type];
        }
        return 'standard';
    }
}