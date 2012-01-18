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

/**
 * Job queue job
 * @api
 */
class SchedulersJob extends Basic
{
    const JOB_STATUS_QUEUED = 'queued';
    const JOB_STATUS_RUNNING = 'running';
    const JOB_STATUS_DONE = 'done';

    const JOB_PENDING = 'queued';
    const JOB_PARTIAL = 'partial';
    const JOB_SUCCESS = 'success';
    const JOB_FAILURE = 'failure';

    // schema attributes
	public $id;
	public $name;
	public $deleted;
	public $date_entered;
	public $date_modified;
	public $scheduler_id;
	public $execute_time; // when to execute
    public $status;
    public $resolution;
    public $message;
	public $target; // URL or function name
    public $data; // Data set
    public $requeue; // Requeue on failure?
    public $retry_count;
    public $failure_count;
    public $interval=0; // Frequency to run it
    public $assigned_user_id; // User under which the task is running
    public $client; // Client ID that owns this job

	// standard SugarBean child attrs
	var $table_name		= "job_queue";
	var $object_name		= "SchedulersJob";
	var $module_dir		= "SchedulersJobs";
	var $new_schema		= true;
	var $process_save_dates = true;
	// related fields
	var $job_name;	// the Scheduler's 'name' field
	var $job;		// the Scheduler's 'job' field
	// object specific attributes
	var $user; // User object
	var $scheduler; // Scheduler parent
	// TODO: make configurable
	public $min_interval = 30; // minimal interval for job reruns
	protected $job_done = true;

	/**
	 * Job constructor.
	 */
	function SchedulersJob()
	{
        parent::Basic();
        //BEGIN SUGARCRM flav=pro ONLY
        $this->disable_row_level_security = true;
        //END SUGARCRM flav=pro ONLY
	}


	///////////////////////////////////////////////////////////////////////////
	////	SCHEDULERSJOB HELPER FUNCTIONS

	/**
	 * This function takes a passed URL and cURLs it to fake multi-threading with another httpd instance
	 * @param	$job		String in URI-clean format
	 * @param	$timeout	Int value in secs for cURL to timeout. 30 default.
	 */
	public function fireUrl($job, $timeout=30)
	{
	// TODO: figure out what error is thrown when no more apache instances can be spun off
	    // cURL inits
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $job); // set url
		curl_setopt($ch, CURLOPT_FAILONERROR, true); // silent failure (code >300);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // do not follow location(); inits - we always use the current
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false);  // not thread-safe
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // return into a variable to continue program execution
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); // never times out - bad idea?
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // 5 secs for connect timeout
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);  // open brand new conn
		curl_setopt($ch, CURLOPT_HEADER, true); // do not return header info with result
		curl_setopt($ch, CURLOPT_NOPROGRESS, true); // do not have progress bar
		$urlparts = parse_url($job);
		if(empty($urlparts['port'])) {
		    if($urlparts['scheme'] == 'https'){
				$urlparts['port'] = 443;
			} else {
				$urlparts['port'] = 80;
			}
		}
		curl_setopt($ch, CURLOPT_PORT, $urlparts['port']); // set port as reported by Server
		//TODO make the below configurable
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // most customers will not have Certificate Authority account
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // most customers will not have Certificate Authority account

		curl_setopt($ch, CURLOPT_NOSIGNAL, true); // ignore any cURL signals to PHP (for multi-threading)
		$result = curl_exec($ch);
		$cInfo = curl_getinfo($ch);	//url,content_type,header_size,request_size,filetime,http_code
									//ssl_verify_result,total_time,namelookup_time,connect_time
									//pretransfer_time,size_upload,size_download,speed_download,
									//speed_upload,download_content_length,upload_content_length
									//starttransfer_time,redirect_time
		curl_close($ch);

		if($result !== FALSE && $cInfo['http_code'] < 400) {
			$GLOBALS['log']->debug("----->Firing was successful: $job");
			$GLOBALS['log']->debug('----->WTIH RESULT: '.strip_tags($result).' AND '.strip_tags(print_r($cInfo, true)));
			return true;
		} else {
			$GLOBALS['log']->fatal("Job failed: $job");
			return false;
		}
	}
	////	END SCHEDULERSJOB HELPER FUNCTIONS
	///////////////////////////////////////////////////////////////////////////


	///////////////////////////////////////////////////////////////////////////
	////	STANDARD SUGARBEAN OVERRIDES
	/**
	 * This function gets DB data and preps it for ListViews
	 */
	function get_list_view_data()
	{
		global $mod_strings;

		$temp_array = $this->get_list_view_array();
		$temp_array['JOB_NAME'] = $this->job_name;
		$temp_array['JOB']		= $this->job;

    	return $temp_array;
	}

	/** method stub for future customization
	 *
	 */
	function fill_in_additional_list_fields()
	{
		$this->fill_in_additional_detail_fields();
	}


	/**
	 * Mark this job as failed
	 * @param string $message
	 */
    public function failJob($message = null)
    {
        return $this->resolveJob(self::JOB_FAILURE, $message);
    }

	/**
	 * Mark this job as success
	 * @param string $message
	 */
    public function succeedJob($message = null)
    {
        return $this->resolveJob(self::JOB_SUCCESS, $message);
    }

    /**
     * Called if job failed but will be retried
     */
    public function onFailure()
    {
        // TODO: what we do if job fails, notify somebody?
    }

    /**
     * Called if job has failed and will not be retried
     */
    public function onFinalFailure()
    {
        // TODO: what we do if job fails, notify somebody?
    }

    /**
     * Resolve job as success or failure
     * @param string $resolution One of JOB_ constants that define job status
     * @param string $message
     * @return bool
     */
    public function resolveJob($resolution, $message = null)
    {
        $GLOBALS['log']->info("Resolving job {$this->id} as $resolution: $message");
        if($resolution == self::JOB_FAILURE) {
            if($this->requeue && $this->retry_count > 0) {
                // retry failed job
                $this->status = self::JOB_STATUS_QUEUED;
                if($this->interval < $this->min_interval) {
                    $this->interval = $this->min_interval;
                }
                $this->execute_time = $GLOBALS['timedate']->getNow()->modify("+{$this->interval} seconds")->asDb();
                $this->retry_count--;
                $this->failure_count++;
                $GLOBALS['log']->info("Will retry job {$this->id} at {$this->execute_time} ($this->retry_count)");
                $this->onFailure();
            } else {
                // final failure
                $this->onFinalFailure();
            }
        } else {
            $this->status = self::JOB_STATUS_DONE;
        }
        $this->addMessages($message);
        $this->resolution = $resolution;
        $this->save();
        return true;
    }

    /**
     * Assemle job messages
     * Takes messages in $this->message, errors & $message and assembles them into $this->message
     * @param string $message
     */
    protected function addMessages($message)
    {
        if(!empty($this->errors)) {
            $this->message .= $this->errors;
            $this->errors = '';
        }
        if(!empty($message)) {
            $this->message .= "$message\n";
        }
    }

    /**
     * Rerun this job again
     * @param string $message
     * @return bool
     */
    public function postponeJob($jobId, $message = null)
    {
        $this->status = self::JOB_STATUS_QUEUED;
        $this->addMessages($message);
        $this->resolution = self::JOB_PARTIAL;
        $this->execute_time = $GLOBALS['timedate']->getNow()->modify("+{$this->interval} seconds")->asDb();
        $GLOBALS['log']->info("Postponing job {$this->id} to {$this->execute_time}: $message");

        $this->save();
        return true;
    }

    /**
     * Delete a job
     * @see SugarBean::mark_deleted($id)
     */
    public function mark_deleted($id)
    {
        return $this->db->query("DELETE FROM jobs WHERE id=".$this->db->quoted($id));
    }

    /**
     * Shutdown handler to be called if something breaks in the middle of the job
     */
    public function unexpectedExit()
    {
        if(!$this->job_done) {
            // Job wasn't properly finished, fail it
            $this->resolveJob(self::JOB_FAILURE, translate('ERR_FAILED', 'SchedulersJobs'));
        }
    }

    /**
     * Run the job by ID
     * @param string $id
     * @param string $client Client that is trying to run the job
     */
    public static function runJobId($id, $client)
    {
        $job = new self();
        $job->retrieve($id);
        if(empty($job->id)) {
            $GLOBALS['log']->fatal("Job $id not found.");
            return false;
        }
        if($job->status != self::JOB_STATUS_RUNNING) {
            $GLOBALS['log']->fatal("Job $id is not marked as running.");
            return false;
        }
        if($job->client != $client) {
            $GLOBALS['log']->fatal("Job $id belongs to client {$job->client}, can not run as $client.");
            return false;
        }
        $job->job_done = false;
        register_shutdown_function(array($job, "unexpectedExit"));
        $job->runJob();
        $job->job_done = true;
        return true;
    }

    /**
     * Error handler, assembles the error messages
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     */
    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        switch($errno)
        {
    		case E_USER_WARNING:
    		case E_COMPILE_WARNING:
    		case E_CORE_WARNING:
    		case E_WARNING:
    			$type = "Warning";
    			break;
    		case E_USER_ERROR:
    		case E_COMPILE_ERROR:
    		case E_CORE_ERROR:
    		case E_ERROR:
    			$type = "Fatal Error";
	    		break;
    		case E_PARSE:
    			$type = "Parse Error";
	    		break;
    		case E_RECOVERABLE_ERROR:
    			$type = "Recoverable Error";
	    		break;
		    default:
		        // Ignore errors we don't know about
		        return;
    	}
        $errstr = strip_tags($errstr);
        $this->errors .= sprintf(translate('ERR_PHP', 'SchedulersJobs'), $type, $errno, $errstr, $errfile, $errline);
    }

    /**
     * Change current user to given user
     * @param User $user
     */
    protected function sudo($user)
    {
        $GLOBALS['current_user'] = $user;
        // Reset the session
        session_write_close();
        session_destroy();
		session_start();
        session_regenerate_id();
		$_SESSION['is_valid_session']= true;
		$_SESSION['user_id'] = $user->id;
		$_SESSION['type'] = 'user';
		$_SESSION['authenticated_user_id'] = $user->id;
    }

    /**
     * Run this job
     */
    public function runJob()
    {
        $this->errors = "";
        $exJob = explode('::', $this->target);
        if($exJob[0] == 'function') {
            // set up the current user and drop session
            if($this->assigned_user_id) {
                $user = new User();
                $user->retrieve($this->assigned_user_id);
                if(empty($user->id)) {
                    $this->resolveJob(self::JOB_FAILURE, sprintf(translate('ERR_NOSUCHUSER', 'SchedulersJobs'), $this->assigned_user_id));
                    return;
                }
                $this->sudo($user);
            } else {
                $this->resolveJob(self::JOB_FAILURE, translate('ERR_NOUSER', 'SchedulersJobs'));
                return;
            }
    		require_once('modules/Schedulers/_AddJobsHere.php');
    		$func = $exJob[1];
			$GLOBALS['log']->debug("----->SchedulersJob calling function: $func");
            set_error_handler(array($this, "errorHandler"), E_ALL & ~E_NOTICE & ~E_STRICT);
			if(!is_callable($func)) {
			    $this->resolveJob(self::JOB_FAILURE, sprintf(translate('ERR_CALL', 'SchedulersJobs'), $func));
			}
			$data = array($this);
			if(!empty($this->data)) {
			    $data += $this->data;
			}
            $res = call_user_func_array($func, $data);
            restore_error_handler();
			if($this->status == self::JOB_STATUS_RUNNING) {
			    // nobody updates the status yet - job can do that
    			if($res) {
    			    $this->resolveJob(self::JOB_SUCCESS);
    				return true;
    			} else {
    			    $this->resolveJob(self::JOB_FAILURE);
    			    return false;
    			}
			}
		} elseif($exJob[0] == 'url') {
			if(function_exists('curl_init')) {
				$GLOBALS['log']->debug('----->SchedulersJob firing URL job: '.$exJob[1]);
                set_error_handler(array($this, "errorHandler"), E_ALL & ~E_NOTICE & ~E_STRICT);
				if($this->fireUrl($exJob[1])) {
                    restore_error_handler();
                    $this->resolveJob(self::JOB_SUCCESS);
					return true;
				} else {
                    restore_error_handler();
				    $this->resolveJob(self::JOB_FAILURE);
					return false;
				}
			} else {
			    $this->resolveJob(self::JOB_FAILURE, translate('ERR_CURL', 'SchedulersJobs'));
			}
		} else {
		    $this->resolveJob(self::JOB_FAILURE, translate('ERR_JOBTYPE', 'SchedulersJobs'), strip_tags($this->target));
		}
		return false;
    }

}  // end class Job
