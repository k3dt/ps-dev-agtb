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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('include/SugarSearchEngine/SugarSearchEngineIndexerBase.php');

/**
 * Indexer job for Search to sync records
 * @api
 */
class SugarSearchEngineSyncIndexer extends SugarSearchEngineIndexerBase
{
    /**
     * Create a job queue FTS SyncConsumer
     *
     * @return String Id of newly created job
     */
    public function createJobQueueConsumer()
    {
        // if a job is already present, just return it's id
        if ($jobId = $this->isJobQueueConsumerPresent()) {
            $GLOBALS['log']->fatal("Active FTS SyncConsumer job found -> $jobId");
            return $jobId;
        }

        $GLOBALS['log']->info("Creating FTS SyncConsumer in Job queue");

        global $timedate;
        if (empty($timedate))
        {
            $timedate = TimeDate::getInstance();
        }

        $job = BeanFactory::getBean('SchedulersJobs');
        $job->requeue = 1;
        $job->name = "FTSSyncConsumer";
        $job->target = "class::SugarSearchEngineSyncIndexer";
        $queue = new SugarJobQueue();
        $queue->submitJob($job);

        return $job->id;
    }

    /**
     *
     * Check if an FTSSyncConsumer job is present and active (not "done"). If one
     * is present we return it's id
     *
     * @return mixed (boolean|string)
     */
    protected function isJobQueueConsumerPresent()
    {
        $jobBean = BeanFactory::getBean('SchedulersJobs');
        return $this->db->getOne("SELECT id FROM {$jobBean->table_name} WHERE name = 'FTSSyncConsumer' AND deleted = 0 AND status != 'done'");
    }

    /**
     * Index records into search engine
     *
     * @param String module
     * @param array fieldDefinitions
     * @return integer number of indexed records, -1 if fails
     */
    public function indexRecords($module, $fieldDefinitions)
    {
        $GLOBALS['log']->info('Indexing for module '.$module);

        $count = 0;
        $processedBeans = array();
        $docs = array();

        $GLOBALS['log']->info("SugarSyncIndexer will use db to index records");
        $sql = $this->generateFTSQuery($module, $fieldDefinitions);
        $result = $this->db->limitQuery($sql,0, self::MAX_BULK_QUERY_THRESHOLD, true, "Unable to retrieve records from FTS queue");

        while ($row = $this->db->fetchByAssoc($result, FALSE) )
        {
            $beanID = $row['id'];
            $row['module_dir'] = $module;
            $bean = (object) $row;

            if($bean !== FALSE)
            {
                $GLOBALS['log']->debug("About to index bean: $beanID $module");
                $docs[] = $this->SSEngine->createIndexDocument($bean, $fieldDefinitions);
                $processedBeans[] = $beanID;
                $count++;
            }

            if($count != 0 && $count % self::MAX_BULK_THRESHOLD == 0)
            {
                $ok = $this->SSEngine->bulkInsert($docs);
                if ($ok) {
                    $this->markBeansProcessed($processedBeans);
                } else {
                    return -1;
                }
                $docs = $processedBeans = array();
                SugarCache::instance()->flush();
                if( function_exists('gc_collect_cycles') )
                    gc_collect_cycles();

                $lastMemoryUsage = isset($lastMemoryUsage) ? $lastMemoryUsage : 0;
                $currentMemUsage = memory_get_usage();
                $totalMemUsage = $currentMemUsage - $lastMemoryUsage;
                $GLOBALS['log']->info("Flushing records, count: $count mem. usage:" .  memory_get_usage() . " , mem. delta: " . $totalMemUsage);
                $lastMemoryUsage = $currentMemUsage;
            }
        }

        if(count($docs) > 0)
        {
            $ok = $this->SSEngine->bulkInsert($docs);
            if (!$ok) {
                return -1;
            }
        }

        $this->markBeansProcessed($processedBeans);

        return $count;
    }


    /**
     * Check FTS server status and update cache file and notification.
     *
     * @return boolean
     */
    protected function updateFTSServerStatus()
    {
        $GLOBALS['log']->debug('Going to check and update FTS Server status.');
        // check FTS server status
        $result = $this->SSEngine->getServerStatus();
        if ($result['valid'])
        {
            $GLOBALS['log']->debug('FTS Server is OK.');
            // server is ok
            if (SugarSearchEngineAbstractBase::isSearchEngineDown())
            {
                $GLOBALS['log']->debug('Restoring FTS Server status.');

                // mark fts server as up
                SugarSearchEngineAbstractBase::markSearchEngineStatus(false);

                // remove notification
                $cfg = new Configurator();
                $cfg->config['fts_disable_notification'] = false;
                $cfg->handleOverride();
            }

            return true;
        }
        else
        {
            $GLOBALS['log']->info('FTS Server is down?');
            // server is down
            if (!SugarSearchEngineAbstractBase::isSearchEngineDown())
            {
                $GLOBALS['log']->fatal('Marking FTS Server as down.');
                // fts is not marked as down, so mark it as down
                SugarSearchEngineAbstractBase::markSearchEngineStatus(true);
                $this->createJobQueueConsumer();
            }

            return false;
        }
    }

    /**
     * Main function that handles the indexing of the queued beans and is called by the job queue system.
     *
     * @param $data
     */
    public function run($data)
    {
        $serverOK = $this->updateFTSServerStatus();
        if (!$serverOK)
        {
            // server is down, postpone the job
            $GLOBALS['log']->fatal('FTS Server is down, postponing the job.');
            $this->schedulerJob->postponeJob('FTS down', self::POSTPONE_JOB_TIME);
            return true;
        }

        $GLOBALS['log']->info("Going to sync records in fts queue...");

        // index queued records for each enabled module
        $allFieldDef = SugarSearchEngineMetadataHelper::retrieveFtsEnabledFieldsForAllModules();
        foreach ($allFieldDef as $module=>$fieldDefinitions) {
            $count = $this->indexRecords($module, $fieldDefinitions);
            if ($count == -1) {
                $GLOBALS['log']->fatal('FTS failed to index records, postponing job for next cron');
                $this->schedulerJob->postponeJob('FTS failed to index', self::POSTPONE_JOB_TIME);
                return true;
            }
        }

        // check the fts queue to see if any records left, if no, then we are done, succeed the job
        // otherwise postpone the job so it can be invoked by the next cron
        $tableName = self::QUEUE_TABLE;
        $res = $this->db->query("SELECT count(*) as cnt FROM {$tableName} WHERE processed = 0");
        if ($row = $this->db->fetchByAssoc($res))
        {
            $count = $row['cnt'];
            if( $count == 0)
            {
                // If no items were processed we've exhausted the list and can therefore succeed job.
                $GLOBALS['log']->info('succeed job');
                $this->schedulerJob->succeedJob();
                $GLOBALS['log']->info("FTS Sync Indexing completed.");
            }
            else
            {
                // Mark the job that as pending so we can be invoked later.
                $GLOBALS['log']->info('FTS Sync Indexing partially done, postponing job for next cron');
                $this->schedulerJob->postponeJob('FTS indexing from queue not completed', self::POSTPONE_JOB_TIME);
            }
        }

        return true;
    }

}
