<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

require_once('modules/Trackers/Metric.php');
require_once('modules/Trackers/Trackable.php');

define('MAX_SESSION_LENGTH', 36);

class Monitor implements Trackable {

    var $metricsFile;
    var $name;
    protected $metrics;
    protected $cachedStores;
    var $stores;
    var $monitor_id;
    var $table_name;
    protected $enabled = true;
	protected $dirty = false;

	var $date_start;
	var $date_end;
	var $active;
	var $round_trips;
	var $seconds;
	var $session_id;

    /**
     * Monitor constructor
     */
    function Monitor($name='', $monitorId='', $metadata='', $store='') {

    	if(empty($metadata) || !SugarAutoLoader::fileExists($metadata)) {
    	   $GLOBALS['log']->error($GLOBALS['app_strings']['ERR_MONITOR_FILE_MISSING'] . "($metadata)");
    	   throw new Exception($GLOBALS['app_strings']['ERR_MONITOR_FILE_MISSING'] . "($metadata)");
    	}

    	$this->name = $name;
    	$this->metricsFile = $metadata;

    	require($this->metricsFile);
    	$fields = $dictionary[$this->name]['fields'];
    	$this->table_name = !empty($dictionary[$this->name]['table']) ? $dictionary[$this->name]['table'] : $this->name;
    	$this->metrics = array();
    	foreach($fields as $field) {

    	   //We need to skip auto_increment fields; they are not real metrics
    	   //since they are generated by the database.
    	   if(isset($field['auto_increment'])) {
    	   	  continue;
    	   }

    	   $type = isset($field['dbType']) ? $field['dbType'] : $field['type'];
    	   $name = $field['name'];
    	   $this->metrics[$name] = new Metric($type, $name);
    	}

    	$this->monitor_id = $monitorId;
    	$this->stores = $store;
    }

    /**
     * setValue
     * Sets the value defined in the monitor's metrics for the given name
     * @param $name String value of metric name
     * @param $value Mixed value
     * @throws Exception Thrown if metric name is not configured for monitor instance
     */
    public function setValue($name, $value) {
        if(!isset($this->metrics[$name])) {
          $GLOBALS['log']->error($GLOBALS['app_strings']['ERR_UNDEFINED_METRIC'] . "($name)");
          throw new Exception($GLOBALS['app_strings']['ERR_UNDEFINED_METRIC'] . "($name)");
        } else if($this->metrics[$name]->isMutable()) {
          $this->$name = is_object($value) ? get_class($value) : $value;
          $this->dirty = true;
        }
    }

    public function getValue($name){
    	return $this->$name;
    }

    /**
     * getStores
     * Returns Array of store names defined for monitor instance
     * @return Array of store names defined for monitor instance
     */
    function getStores() {
        return $this->stores;
    }

    /**
     * getMetrics
     * Returns Array of metric instances defined for monitor instance
     * @return Array of metric instances defined for monitor instance
     */
    function getMetrics() {
    	return $this->metrics;
    }

	/**
	 * isDirty
	 * Returns if the monitor has data that needs to be saved
	 * @return $dirty boolean
	 */
	function isDirty(){
		return $this->dirty;
	}

    /**
     * save
     * This method retrieves the Store instances associated with monitor and calls
     * the flush method passing with the montior ($this) instance.
     * @param $flush boolean parameter indicating whether or not to flush the instance data to store or possibly cache
     */
    public function save($flush=true) {

        if (!$this->isEnabled()) {
            return false;
        }

    	//if the monitor does not have values set no need to do the work saving.
    	if(!$this->dirty)return false;

    	if(empty($GLOBALS['tracker_' . $this->table_name])) {
    	    foreach($this->stores as $s) {
	    		$store = $this->getStore($s);
	    		$store->flush($this);
    		}
    	}
    	$this->clear();
    }

	/**
	 * clear
	 * This function clears the metrics data in the monitor instance
	 */
	public function clear() {
	    $metrics = $this->getMetrics();
	    foreach($metrics as $name=>$metric) {
	    	    if($metric->isMutable()) {
                   $this->$name = '';
	    	    }
	    }
		$this->dirty = false;
	}

	/**
	 * getStore
	 * This method checks if the Store implementation has already been instantiated and
	 * will return the one stored; otherwise it will create the Store implementation and
	 * save it to the Array of Stores.
	 * @param $store The name of the store as defined in the 'modules/Trackers/config.php' settings
	 * @return An instance of a Store implementation
	 * @throws Exception Thrown if $store class cannot be loaded
	 */
	protected function getStore($store) {

		if(isset($this->cachedStores[$store])) {
		   return $this->cachedStores[$store];
		}

        if(!SugarAutoLoader::requireWithCustom("modules/Trackers/store/$store.php")) {
           $GLOBALS['log']->error($GLOBALS['app_strings']['ERR_STORE_FILE_MISSING'] . "($store)");
           throw new Exception($GLOBALS['app_strings']['ERR_STORE_FILE_MISSING'] . "($store)");
        }

		$s = new $store();
		$this->cachedStores[$store] = $s;
		return $s;
	}

 	/**
 	 * Returns the monitor's metrics/values as an Array
 	 * @return An Array of data for the monitor's corresponding metrics
 	 */
 	public function toArray() {
 		$to_arr = array();
 		$metrics = $this->getMetrics();
	    foreach($metrics as $name=>$metric) {
	    	    $to_arr[$name] = isset($this->$name) ? $this->$name : null;
	    }
	    return $to_arr;
 	}

 	public function setEnabled($enable=true) {
 		$this->enabled = $enable;
 	}

 	public function isEnabled() {
 		return $this->enabled;
 	}
}
