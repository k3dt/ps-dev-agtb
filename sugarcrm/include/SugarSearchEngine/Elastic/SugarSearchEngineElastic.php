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
 *Portions created by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights
 *Reserved.
 ********************************************************************************/
require_once('include/SugarSearchEngine/SugarSearchEngineAbstractBase.php');
require_once('include/SugarSearchEngine/Elastic/SugarSearchEngineElasticResultSet.php');

class SugarSearchEngineElastic extends SugarSearchEngineAbstractBase
{
    private $_server = "";
    private $_config = array();
    private $_client = null;
    private $_indexName = "";

    const DEFAULT_INDEX_TYPE = 'SugarBean';
    const SUMMARY_TEXT = 'summary_text';

    private $_indexType = 'SugarBean';

    public function __construct($params = array())
    {
        $this->_config = $params;
        $this->_indexName = $GLOBALS['sugar_config']['unique_key'];

        //Elastica client uses own auto-load schema similar to ZF.
        spl_autoload_register(array($this, 'loader'));
        $this->_client = new Elastica_Client($this->_config);
    }


    public function indexBean($bean, $batch = TRUE)
    {
        if(!$this->isModuleFtsEnabled($bean->module_dir) )
            return;

        if(!$batch)
            $this->indexSingleBean($bean);
        else
        {
            $GLOBALS['log']->info("Adding bean to doc list with id: {$bean->id}");

            //Group our beans by index type for bulk insertion
            $indexType = $this->getIndexType($bean);
            if(! isset($this->_documents[$indexType]) )
                $this->_documents = array();

            //Create and store our document index which will be bulk inserted later, do not store beans as they are heavy.
            $this->_documents[] = $this->createIndexDocument($bean);
        }
    }

    /**
     *
     * Return the 'type' for the index.  By using the bean type we can specify mappings on a per bean basis if we need
     * to in the future.
     *
     * @param $bean
     * @return string
     */
    protected function getIndexType($bean)
    {
        if(!empty($bean->module_dir))
            return $bean->module_dir;
        else
            return self::DEFAULT_INDEX_TYPE;
    }

    /**
     *
     * @param SugarBean $bean
     * @param $searchFields
     * @return Elastica_Document|null
     */
    public function createIndexDocument(SugarBean $bean, $searchFields = null)
    {
        if($searchFields == null)
            $searchFields = $this->retrieveFtsEnabledFieldsPerModule($bean);

        $keyValues = array();
        foreach($searchFields as $fieldName => $fieldDef)
        {
            //All fields have already been formatted to db values at this point so no further processing necessary
            if( !empty($bean->$fieldName) )
                $keyValues[$fieldName] = $bean->$fieldName;
        }

        //Always add our module
        $keyValues['module'] = $bean->module_dir;
        $keyValues['team_set_id'] = str_replace("-", "",$bean->team_set_id);
        $keyValues[self::SUMMARY_TEXT] = $bean->get_summary_text();

        if( empty($keyValues) )
            return null;
        else
            return new Elastica_Document($bean->id, $keyValues, $this->getIndexType($bean));
    }

    protected function indexSingleBean($bean)
    {
        $GLOBALS['log']->info("Preforming single bean index");
        try
        {
            $index = new Elastica_Index($this->_client, $this->_indexName);
            $type = new Elastica_Type($index, $this->getIndexType($bean));
            $doc = $this->createIndexDocument($bean);
            if($doc != null)
                $type->addDocument($doc);
        }
        catch(Exception $e)
        {
            $GLOBALS['log']->fatal("Unable to index bean with error: {$e->getMessage()}");
        }

    }

    public function flush()
    {

    }

    public function delete(SugarBean $bean)
    {
        if(empty($bean->id))
            return;

        try
        {
            $GLOBALS['log']->info("Going to delete {$bean->id}");
            $index = new Elastica_Index($this->_client, $this->_indexName);
            $type = new Elastica_Type($index, $this->getIndexType($bean));
            $type->deleteById($bean->id);
        }
        catch(Exception $e)
        {
            $GLOBALS['log']->fatal("Unable to delete index: {$e->getMessage()}");
        }
    }

    /**
     *
     */
    public function bulkInsert(array $docs)
    {
        try
        {
            $index = new Elastica_Index($this->_client, $this->_indexName);
            $batchedDocs = array();
            $x = 0;
            foreach($docs as $singleDoc)
            {
                if($x != 0 && $x % self::MAX_BULK_THRESHOLD == 0)
                {
                    $index->addDocuments($batchedDocs);
                    $batchedDocs = array();
                }
                else
                {
                   $batchedDocs[] = $singleDoc;
                }

                $x++;
            }

            //Commit the stragglers
            if(count($batchedDocs) > 0)
            {
                $index->addDocuments($batchedDocs);
            }
        }
        catch(Exception $e)
        {
            $GLOBALS['log']->fatal("Error performing bulk update operation: {$e->getMessage()}");
        }

    }

    /**
     * Check the server status
     */
    public function getServerStatus()
    {
        $isValid = FALSE;
        $displayText = "";
        $timeOutValue = $this->_client->getConfig('timeout');
        try
        {
            $this->_client->setConfigValue('timeout', 2);
            $results = $this->_client->getStatus()->getServerStatus();
            if(!empty($results['ok']) )
            {
                $isValid = TRUE;
                $displayText = $GLOBALS['app_strings']['LBL_EMAIL_SUCCESS'];
            }
            else
                $displayText = $results;
        }
        catch(Exception $e)
        {
            $GLOBALS['log']->fatal("Unable to get server status with error: {$e->getMessage()}");
            $displayText = $e->getMessage();
        }
        //Reset previous timeout value.
        $this->_client->setConfigValue('timeout', $timeOutValue);
        return array('valid' => $isValid, 'status' => $displayText);
    }

    /**
     * @param $queryString
     * @param int $offset
     * @param int $limit
     * @return null|SugarSeachEngineElasticResultSet
     */
    public function search($queryString, $offset = 0, $limit = 20, $options = array(), $isAutoComplete = false)
    {
        $GLOBALS['log']->info("Going to search with query $queryString");
        $results = null;
        try
        {
            $qString = html_entity_decode($queryString, ENT_QUOTES);
            // for auto complete search, we need to append a wildcard
            if ($isAutoComplete) {
                $qString .= '*';
            }
            $queryObj = new Elastica_Query_QueryString($qString);
            $queryObj->setAnalyzeWildcard(false);
            $queryObj->setAutoGeneratePhraseQueries(false);

            if ($isAutoComplete) {
                $queryObj->setFields(array(self::SUMMARY_TEXT));
            }

            if( !is_admin($GLOBALS['current_user']) )
            {
                $teamFilter = new Elastica_Filter_Or();
                $teamIDS = TeamSet::getTeamSetIdsForUser($GLOBALS['current_user']->id);
                //TODO: Determine why term filters aren't working with the hyphen present.
                //Term filters dont' work for terms with '-' present so we need to clean
                $teamIDS = array_map(array($this,'cleanTeamSetID'), $teamIDS);
                foreach ($teamIDS as $teamID)
                {
                    $termFilter = new Elastica_Filter_Term();
                    $termFilter->setTerm('team_set_id',$teamID);
                    $teamFilter->addFilter($termFilter);
                }
                $query = new Elastica_Query_Filtered($queryObj, $teamFilter);
            }
            else
            {
                $query = new Elastica_Query($queryObj);
            }
            $query->setParam('from',$offset);
            $s = new Elastica_Search($this->_client);
            //Only search accross our index.
            $index = new Elastica_Index($this->_client, $this->_indexName);
            $s->addIndex($index);

            //Search accross specific types (modules)
            if(!empty($options['moduleFilter']))
                $s->addTypes($options['moduleFilter']);

            // TODO, for non auto complete searches, ideally we should exclude summary_text field
            $esResultSet = $s->search($query, $limit);
            $results = new SugarSeachEngineElasticResultSet($esResultSet);

        }
        catch(Exception $e)
        {
            $GLOBALS['log']->fatal("Unable to perform search with error: {$e->getMessage()}");
        }

        return $results;
    }

    /**
     * Remove the '-' from our team sets.
     *
     * @param $teamSetID
     * @return mixed
     */
    protected function cleanTeamSetID($teamSetID)
    {
        return str_replace("-", "", $teamSetID);
    }

    protected function loader($className)
    {
        $fileName = str_replace('_', '/', $className);
        $path = 'include/SugarSearchEngine/Elastic/' . $fileName . '.php';
        if( file_exists($path) )
            require_once($path);
        else
            return FALSE;
    }

    /**
     * Create the index and mapping.
     *
     * @param boolean $recreate OPTIONAL Deletes index first if already exists (default = false)
     *
     */
    public function createIndex($recreate = false)
    {
        // create an elastic index
        try
        {
            $index = new Elastica_Index($this->_client, $this->_indexName);
            $index->create(array(), $recreate);
        }
        catch(Exception $e)
        {
            $GLOBALS['log']->fatal("Unable to create index with error: {$e->getMessage()}");
        }
        // create field mappings
        require_once('include/SugarSearchEngine/Elastic/SugarSearchEngineElasticMapping.php');
        $elasticMapping = new SugarSearchEngineElasticMapping($this);
        $elasticMapping->setFullMapping();
    }

    public function getClient()
    {
        return $this->_client;
    }

    public function getIndexName()
    {
        return $this->_indexName;
    }
}