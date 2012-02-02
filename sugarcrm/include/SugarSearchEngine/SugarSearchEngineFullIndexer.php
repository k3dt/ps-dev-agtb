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

require_once('include/SugarSearchEngine/SugarSearchEngineFactory.php');

/**
 *
 */
class SugarSearchEngineFullIndexer
{

    /**
     * @var \SugarSearchEngineAbstractBase
     */
    private $SSEngine;

    /**
     * @var array
     */
    private $results;

    /**
     * The max number of beans we process before starting to bulk insert so we dont hit memory issues.
     */
    const MAX_BULK_THRESHOLD = 5000;

    /**
     * Name of the scheduler to perform a full index
     * @var string
     */
    public static $schedulerName = "Full Text Search Indexer";

    /**
     * @param SugarSearchEngineAqbstractBase $engine
     */
    public function __construct(SugarSearchEngineAbstractBase $engine = null)
    {
        if($engine != null)
            $this->SSEngine = $engine;
        else
            $this->SSEngine = SugarSearchEngineFactory::getInstance();

        $this->results = array();
    }

    /**
     * Index the entire system. This should only be called from a worker process as this is a time intensive process.
     */
    public function performFullSystemIndex()
    {
        if(! $this->SSEngine instanceof SugarSearchEngineAbstractBase)
            return $this;

        $GLOBALS['log']->fatal("Performing Full System Index");
        $startTime = microtime(true);
        $allModules = $this->SSEngine->retrieveFtsEnabledFieldsForAllModules();
        $db = DBManagerFactory::getInstance();
        $totalCount = 0;
        foreach($allModules as $module => $fieldDefinitions)
        {
            $GLOBALS['log']->fatal("Going to index all records in module {$module} ");
            $count = 0;
            $obj = BeanFactory::getBean($module, null);
            $selectAllQuery = "SELECT id FROM {$obj->table_name} WHERE deleted='0'";

            $result = $db->query($selectAllQuery, true, "Error filling in team names: ");

            $docs = array();
            while ($row = $db->fetchByAssoc($result, FALSE) )
            {
                $beanID = $row['id'];
                $bean = BeanFactory::getBean($module, $beanID);
                if($bean !== FALSE)
                {
                    $docs[] = $this->SSEngine->createIndexDocument($bean, $fieldDefinitions);
                    $count++;
                }

                if($count != 0 && $count % self::MAX_BULK_THRESHOLD == 0)
                {
                    $this->SSEngine->bulkInsert($docs);
                    $docs = array();
                    sugar_cache_reset();
                    gc_collect_cycles();
                    $lastMemoryUsage = isset($lastMemoryUsage) ? $lastMemoryUsage : 0;
                    $currentMemUsage = memory_get_usage();
                    $totalMemUsage = $currentMemUsage - $lastMemoryUsage;
                    $GLOBALS['log']->fatal("Flushing records, count: $count mem. usage:" .  memory_get_usage() . " , mem. delta: " . $totalMemUsage);
                    $lastMemoryUsage = $currentMemUsage;
                }
            }

            if(count($docs) > 0)
            {
                $this->SSEngine->bulkInsert($docs);
            }

            $this->results[$module] = $count;
            $totalCount += $count;
        }

        $totalTime = number_format(round(microtime(true) - $startTime, 2), 2);
        $this->results['totalTime'] = $totalTime;
        $GLOBALS['log']->fatal("Total time to perform full system index: $totalTime (s)");
        $avgRecs = number_format(round(($totalCount / $totalTime), 2), 2);
        $GLOBALS['log']->fatal("Total number of records indexed: $totalCount , records per sec. $avgRecs");

        return $this;
    }

    /**
     * Return statistics about how many records per module were indexed.
     *
     * @return array
     */
    public function getStatistics()
    {
        return $this->results;
    }

    /**
     * Schedule a full system index.
     *
     * @static
     *
     */
    public static function scheduleFullSystemIndex()
    {
        $previousSchedulerID = self::isFTSIndexScheduled();
        //If there is an old scheduler, delete it. We only want to keep
        //a single copy of the scheduler around so we can see if it has completed
        //by examine the log history.
        if($previousSchedulerID !== FALSE)
        {
            $oldSched = new Scheduler();
            $oldSched->retrieve($previousSchedulerID);
            $oldSched->deleted = 1;
            $oldSched->save(FALSE);
        }
        $td = TimeDate::getInstance()->getNow(true)->modify("+5 min");
        $before = TimeDate::getInstance()->getNow(true)->modify("-5 min");
        $future = TimeDate::getInstance()->getNow(true)->modify("+5 year");
        $sched = new Scheduler();
        $sched->name = self::$schedulerName;
        $sched->job = "function::performFullFTSIndex";
        $sched->status = 'Active';
        $sched->job_interval = $td->min."::".$td->hour."::".$td->day."::".$td->month."::".$td->day_of_week;
        $sched->date_time_start = TimeDate::getInstance()->asUser($before, $GLOBALS['current_user']);
        $sched->date_time_end = TimeDate::getInstance()->asUser($future, $GLOBALS['current_user']);
        $sched->catch_up = 0;
        $sched->save(); //die($sched->job_interval);
    }

    /**
     * Determine if a pre-existing scheduler for fts exists.  If so return the id, else false.
     *
     * @static
     * @return mixed
     */
    public static function isFTSIndexScheduled()
    {
        $sched = new Scheduler();
        $sched = $sched->retrieve_by_string_fields(array('name'=> self::$schedulerName));

        if($sched == NULL)
            return FALSE;
        else
            return $sched->id;

    }

    /**
     * Determine if a given scheduler has completed it's task. 
     *
     * @static
     * @param Scheduler $s
     * @return bool
     */
    public static function isFTSIndexScheduleCompleted($id)
    {
        if( empty($id) )
            return FALSE;

        $scheduler = new Scheduler();
        $scheduler->retrieve($id);
        $scheduler->load_relationship('schedulers_times');
        $runs  = $scheduler->schedulers_times->get();

        if(count($runs) >= 1)
            return TRUE;
        else
            return FALSE;
    }
}