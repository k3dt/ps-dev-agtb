<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue;

use Sugarcrm\Sugarcrm\JobQueue\Handler\RunnableInterface;
use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory as CalDavAdapterFactory;
use Sugarcrm\Sugarcrm\Dav\Cal\Handler as CalDavHandler;
use Sugarcrm\Sugarcrm\JobQueue\Manager\Manager as JQManager;

/**
 * Class Base
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue
 * Base class for import or export process
 */
abstract class Base implements RunnableInterface
{
    /**
     * @var array
     */
    protected $processedData = array();

    /**
     * @var int
     */
    protected $saveCounter;

    /**
     * @param array $processedData All needed job's data.
     * @param int $saveCounter Job counter.
     */
    public function __construct(array $processedData, $saveCounter)
    {
        $this->processedData = $processedData;
        $this->saveCounter = $saveCounter;
    }

    /**
     * @return \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory
     */
    protected function getAdapterFactory()
    {
        return CalDavAdapterFactory::getInstance();
    }

    /**
     * return CalDav handler for export processing
     * @return CalDavHandler
     */
    protected function getHandler()
    {
        return new CalDavHandler();
    }

    /**
     * Set current job to the end of queue if needed
     * @param \CalDavEventCollection $calDavBean
     * @return bool true - if job set to end
     */
    protected function setJobToEnd($calDavBean)
    {
        $currentJobCounter = $calDavBean->getSynchronizationObject()->getJobCounter();
        if ($this->saveCounter - $currentJobCounter > 1) {
            $this->reschedule();
            return true;
        }

        return false;
    }

    /**
     * function return manager object for handler processing
     * @return \Sugarcrm\Sugarcrm\JobQueue\Manager\Manager
     */
    protected function getManager()
    {
        return new JQManager();
    }

    /**
     * Set job to the end
     */
    abstract protected function reschedule();
}
