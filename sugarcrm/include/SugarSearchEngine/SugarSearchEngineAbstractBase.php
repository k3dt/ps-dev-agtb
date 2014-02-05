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
require_once('include/SugarSearchEngine/Interface.php');
require_once('include/SugarSearchEngine/SugarSearchEngineMetadataHelper.php');

/**
 * Base class for search engine drivers
 * @api
 */
abstract class SugarSearchEngineAbstractBase implements SugarSearchEngineInterface
{
    /**
     * @var array
     */
    protected $_documents = array();

    /**
     * @var The max number of documents to bulk insert at a time
     */
    protected $max_bulk_doc_threshold = 100;

    /**
     * Logger to use to report problems
     * @var LoggerManager
     */
    public $logger;

    public function __construct()
    {
        $this->max_bulk_doc_threshold = SugarConfig::getInstance()->get('search_engine.max_bulk_doc_threshold', $this->max_bulk_doc_threshold);
        $this->logger = $GLOBALS['log'];
    }
    /**
     * Determine if a module is FTS enabled.
     *
     * @param $module
     * @return bool
     */
    protected function isModuleFtsEnabled($module)
    {
        return SugarSearchEngineMetadataHelper::isModuleFtsEnabled($module);

    }

    /**
     * This is needed to prevent unserialize vulnerability
     */
    public function __wakeup()
    {
        // clean all properties
        foreach(get_object_vars($this) as $k => $v) {
            $this->$k = null;
        }
        throw new Exception("Not a serializable object");
    }

    /**
     * Bulk insert any documents that have been marked for bulk insertion.
     */
    public function __destruct()
    {
        if (count($this->_documents) > 0 )
        {
            $this->bulkInsert($this->_documents);
        }

    }

    /**
     * Disable FTS and write to config.
     *
     */
    protected function disableFTS()
    {
        $this->logger->fatal('Full Text Search has been disabled because the system is not able to connect to the search engine.');
        self::markSearchEngineStatus(true);

        // notification
        if(empty($GLOBALS['sugar_config']['fts_disable_notification'])) {
            $cfg = new Configurator();
            $cfg->config['fts_disable_notification'] = true;
            $cfg->handleOverride();
        }
    }

    /**
     * This function adds records to FTS queue.
     *
     * @param $records array of records
     */
    protected function addRecordsToQueue($records)
    {
        $this->logger->info('addRecordsToQueue');
        $db = DBManagerFactory::getInstance('fts');
        $db->resetQueryCount();

        foreach ($records as $rec)
        {
            if (!is_array($rec) || empty($rec['bean_id']) || empty($rec['bean_module'])) {
                $this->logger->error('Error populating fts_queue. Empty bean_id or bean_module.');
                continue;
            }
            $query = "INSERT INTO fts_queue (bean_id,bean_module) values ('{$rec['bean_id']}', '{$rec['bean_module']}')";
            $db->query($query, true, "Error populating index queue for fts");
        }

        // create a cron job consumer to digest the beans
        require_once('include/SugarSearchEngine/SugarSearchEngineSyncIndexer.php');
        $indexer = new SugarSearchEngineSyncIndexer();
        $indexer->createJobQueueConsumer();
    }

    /**
     * This function checks config to see if search engine is down.
     *
     * @return Boolean
     */
    static public function isSearchEngineDown()
    {
        $settings = Administration::getSettings();
        if (!empty($settings->settings['info_fts_down'])) {
            return true;
        }
        return false;
    }

    /**
     * This function marks config to indicate that search engine is up or down.
     *
     * @param Boolean $isDown
     */
    static public function markSearchEngineStatus($isDown = true)
    {
        $admin = BeanFactory::getBean('Administration');
        $admin->saveSetting('info', 'fts_down', $isDown? 1: 0);
    }

    protected function reportException($message, $e)
    {
        $this->logger->fatal("$message: ".get_class($e));
        if($this->logger->wouldLog('error')) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * This function queries db to get the value of a field.
     * @param String $fieldName field name
     * @param String $bean SugarBean
     * @return Mix field value
     */
    protected function getFieldValue($fieldName, $bean)
    {
        $value = null;

        if (!empty($bean->table_name) && !empty($fieldName) && !empty($bean->id)) {
            $db = DBManagerFactory::getInstance('fts');
            $sql = "SELECT {$fieldName} from {$bean->table_name} where id = " . $db->quoted($bean->id);
            $value = $db->getOne($sql, false, 'Error getting field value in fts');
        }

        return $value;
    }
}
