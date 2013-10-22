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

// For clearing the metadata API cache
require_once "include/MetaDataManager/MetaDataManager.php";

/**
 * Create relationship objects
 * @api
 */
class SugarRelationshipFactory {
    static $rfInstance;

    protected $relationships;

    protected function __construct(){
        //Load the relationship definitions from the cache.
        $this->loadRelationships();
    }

    /**
     * @static
     * @return SugarRelationshipFactory
     */
    public static function getInstance()
    {
        if (is_null(self::$rfInstance))
            self::$rfInstance = new SugarRelationshipFactory();
        return self::$rfInstance;
    }

    public static function rebuildCache()
    {
        self::getInstance()->buildRelationshipCache();
    }

    public static function deleteCache()
    {
        $file = self::getInstance()->getCacheFile();
        if(sugar_is_file($file))
        {
            unlink($file);
        }

        //clear out the api metadata cache
        MetaDataManager::clearAPICache();
    }

    /**
     * @param  $relationshipName String name of relationship to load
     * @return void
     *
     *
     *
     */
    public function getRelationship($relationshipName)
    {
        if (empty($this->relationships[$relationshipName])) {
            $GLOBALS['log']->error("Unable to find relationship $relationshipName");
            return false;
        }

        $def = $this->relationships[$relationshipName];

        $type = isset($def['true_relationship_type']) ? $def['true_relationship_type'] : $def['relationship_type'];
        switch($type)
        {
            case "many-to-many":
                if (isset($def['rhs_module']) && $def['rhs_module'] == 'EmailAddresses')
                {
                    return new EmailAddressRelationship($def);
                }
                
                return new M2MRelationship($def);
            break;
            case "user-based":
                return new UserBasedRelationship($def);
            break;
            case "one-to-many":
                
                //If a relationship has no table or join keys, it must be bean based
                if (empty($def['true_relationship_type']) || (empty($def['table']) && empty($def['join_table'])) || empty($def['join_key_rhs'])){
                    return new One2MBeanRelationship($def);
                }
                else {
                    return new One2MRelationship($def);
                }
                break;
            case "one-to-one":
                if (empty($def['true_relationship_type'])){
                    return new One2OneBeanRelationship($def);
                }
                else {
                    return new One2OneRelationship($def);
                }
                break;
        }

        $GLOBALS['log']->fatal ("$relationshipName had an unknown type $type ");

        return false;
    }

    public function getRelationshipDef($relationshipName)
    {
        if (empty($this->relationships[$relationshipName])) {
            $GLOBALS['log']->error("Unable to find relationship $relationshipName");
            return false;
        }

        return $this->relationships[$relationshipName];
    }

    /**
     * This function returns an array of every relationship in the system
     * @return array An array of relationships, indexed by the relationship name
     */
    public function getRelationshipDefs()
    {
        // The relationships are loaded in the constructor
        return $this->relationships;
    }

    protected function loadRelationships()
    {
        if(sugar_is_file($this->getCacheFile()))
        {
            include($this->getCacheFile());
            $this->relationships = $relationships;
        } else {
            $this->buildRelationshipCache();
        }
    }

    protected function buildRelationshipCache()
    {
        global $beanList, $dictionary, $buildingRelCache;
        if ($buildingRelCache)
            return;
        $buildingRelCache = true;
        include("modules/TableDictionary.php");

        if (empty($beanList))
            include("include/modules.php");
        //Reload ALL the module vardefs....
        foreach($beanList as $moduleName => $beanName)
        {
            VardefManager::loadVardef($moduleName, BeanFactory::getObjectName($moduleName), false, array(
                //If relationships are not yet loaded, we can't figure out the rel_calc_fields.
                "ignore_rel_calc_fields" => true,
            ));
        }

        $relationships = array();

        //Grab all the relationships from the dictionary.
        foreach ($dictionary as $key => $def)
        {
            if (!empty($def['relationships']))
            {
                foreach($def['relationships'] as $relKey => $relDef)
                {
                    if ($key == $relKey) //Relationship only entry, we need to capture everything
                        $relationships[$key] = array_merge(array('name' => $key), $def, $relDef);
                    else {
                        $relationships[$relKey] = array_merge(array('name' => $relKey), $relDef);
                        if(!empty($relationships[$relKey]['join_table']) && empty($relationships[$relKey]['fields'])
                            && isset($dictionary[$relationships[$relKey]['join_table']]['fields'])) {
                            $relationships[$relKey]['fields'] = $dictionary[$relationships[$relKey]['join_table']]['fields'];
                        }
                    }
                }
            }
        }
        //Save it out
        sugar_mkdir(dirname($this->getCacheFile()), null, true);
        $out = "<?php \n \$relationships = " . var_export($relationships, true) . ";";
        sugar_file_put_contents_atomic($this->getCacheFile(), $out);

        $this->relationships = $relationships;

        //Now load all vardefs a second time populating the rel_calc_fields
        foreach ($beanList as $moduleName => $beanName) {
            // need to refresh the vardef so that the related calc fields are loaded
            VardefManager::loadVardef($moduleName, BeanFactory::getObjectName($moduleName), true);
        }

        $buildingRelCache = false;
    }

	protected function getCacheFile() {
		return sugar_cached("Relationships/relationships.cache.php");
	}



}
