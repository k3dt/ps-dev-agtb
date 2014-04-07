<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
require_once dirname(__FILE__).'/UpgradeDriver.php';

/**
 * Web driver
 *
 */
class WebUpgrader extends UpgradeDriver
{
    /**
     * License file content
     * @var string
     */
    public $license;
    /**
     * Readme file content
     * @var string
     */
    public $readme;

    public function runStage($stage)
    {
        return $this->run($stage);
    }

    public function __construct($dir)
    {
        $this->context['source_dir'] = $dir;
        $this->context['log'] = "UpgradeWizard.log";
        $this->context['zip'] = ''; // temporary
        parent::__construct();
    }

    protected function initSession()
    {
        if (!isset($_SESSION)) {
            // Oauth token support
            if(isset($_SERVER['HTTP_OAUTH_TOKEN'])) {
                session_id($_SERVER['HTTP_OAUTH_TOKEN']);
            }
            session_start();
        }
    }

    /**
     * Check if we've started upgrade process and have correct token
     * If yes, setup current request
     * @param string $token
     * @return boolean
     */
    public function startRequest($token)
    {
        if(empty($token) || empty($this->state['webToken']) || $token != $this->state['webToken']) {
            return false;
        }
        if(empty($this->state['admin'])) {
            return false;
        }
        if(!empty($this->state['zip'])) {
            $this->context['zip'] = $this->state['zip'];
            $this->context['backup_dir'] = $this->config['upload_dir']."/upgrades/backup/".pathinfo($this->context['zip'], PATHINFO_FILENAME) . "-restore";
        }
        return true;
    }

    /**
     * Get user from state
     * @see UpgradeDriver::getUser()
     */
    protected function getUser()
    {
        $user = BeanFactory::getBean('Users', $this->state['admin']);
        if($user) {
            $this->context['admin'] = $user->user_name;
        }
        return $user;
    }

    /**
     * Files that are used by the upgrade driver
     * We copy them so that upgrading would not mess them up
     * @var array
     */
    protected $upgradeFiles = array('WebUpgrader.php', 'UpgradeDriver.php', 'upgrade_screen.php');

    /**
     * Start upgrade process
     * @return boolean
     */
    public function startUpgrade()
    {
        // Load admin user name
         $this->initSession();
         if(empty($_SESSION['authenticated_user_id'])) {
             return false;
         }
         $this->cleanState();
         $this->state['admin'] = $_SESSION['authenticated_user_id'];
         $this->initSugar();
         if(empty($GLOBALS['current_user']) || !$GLOBALS['current_user']->isAdmin()) {
             return false;
         }
         $this->state['webToken'] = create_guid();
         $this->saveState();
         // copy upgrader files
         $upg_dir = $this->cacheDir("upgrades/driver/");
         $this->ensureDir($upg_dir);
         $_SESSION['upgrade_dir'] = $upg_dir;
         foreach($this->upgradeFiles as $ufile) {
             copy("modules/UpgradeWizard/$ufile", "{$upg_dir}{$ufile}");
         }
         return $this->state['webToken'];
    }

    /**
     * Get upgrade status
     * @return array
     */
    protected function getStatus()
    {
        $state = array();
        if(isset($this->state['stage'])) {
            $state['stage'] = $this->state['stage'];
        }
        if(isset($this->state['scripts'])) {
            $state['scripts'] = $this->state['scripts'];
        }
            if(isset($this->state['script_count'])) {
            $state['script_count'] = $this->state['script_count'];
        }
        return $state;
    }

    /**
     * Process upgrade action
     * @param string $action
     * @return next stage name or false on error
     */
    public function process($action)
    {
        if($action == "status") {
            return $this->getStatus();
        }
        if(!in_array($action, $this->stages)) {
            return $this->error("Unknown stage $action", true);
        }
        if($action == 'unpack') {
            // accept file upload
            if(!$this->handleUpload()) {
                return false;
            }
        }
       $res = $this->runStep($action);
       if($res !== false) {
        	if($action == 'unpack') {
        	    $manifest = $this->getManifest();
        	    if (empty($manifest)) {
        	        return false;
        	    }
        	    if(!empty($manifest['copy_files']['from_dir'])) {
        	        $new_source_dir = $this->context['temp_dir']."/".$manifest['copy_files']['from_dir'];
        	    } else {
        	        $this->error("No from_dir in manifest", true);
        	        return false;
        	    }
        		if(is_file("$new_source_dir/LICENSE")) {
        			$this->license = file_get_contents("$new_source_dir/LICENSE");
        		} elseif(is_file("$new_source_dir/LICENSE.txt")) {
        			$this->license = file_get_contents("$new_source_dir/LICENSE.txt");
        		} elseif(is_file($this->context['source_dir']."/LICENSE.txt")) {
        		    $this->license = file_get_contents($this->context['source_dir']."/LICENSE.txt");
        		} elseif(is_file($this->context['source_dir']."/LICENSE")) {
        		    $this->license = file_get_contents($this->context['source_dir']."/LICENSE");
        		}
        	    if(is_file($this->context['temp_dir']."/README")) {
        			$this->readme = file_get_contents($this->context['temp_dir']."/README");
        		} elseif(is_file($this->context['temp_dir']."/README.txt")) {
        			$this->readme = file_get_contents($this->context['temp_dir']."/README.txt");
        		}
        	}
        	return $res;
        }
        return false;
    }

    /**
     * Messages for upload errors
     * @var array
     */
    protected $upload_errors = array(
        0=>"There is no error, the file uploaded with success",
        1=>"The uploaded file exceeds the upload_max_filesize directive in php.ini",
        2=>"The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
        3=>"The uploaded file was only partially uploaded",
        4=>"No file was uploaded",
        6=>"Missing a temporary folder",
        7=>"Failed to write file to disk",
        8=>"A PHP extension stopped the file upload",

    );

    /**
     * Handle zip file upload
     * @return boolean
     */
    protected function handleUpload()
    {
        if(empty($_FILES['zip'])) {
            return $this->error("Expected file upload", true);
        }
        if($_FILES['zip']['error'] != UPLOAD_ERR_OK) {
            return $this->error("File upload error: {$this->upload_errors[$_FILES['zip']['error']]} ({$_FILES['zip']['error']})", true);
        }
        if(!is_uploaded_file($_FILES['zip']['tmp_name'])) {
            return $this->error("Upload failed", true);
        }
        $this->ensureDir($this->config['upload_dir']."/upgrades");
        $this->context['zip'] = $this->config['upload_dir']."/upgrades/".basename($_FILES['zip']['name']);
        if (move_uploaded_file($_FILES['zip']['tmp_name'], $this->context['zip'])) {
            $this->state['zip'] = $this->context['zip'];
            $this->context['backup_dir'] = $this->config['upload_dir']."/upgrades/backup/".pathinfo($this->context['zip'], PATHINFO_FILENAME) . "-restore";
            $this->saveState();
            return true;
        } else {
            return $this->error("Failed to move uploaded file to {$this->context['zip']}", true);
        }

    }

    /**
     * Display upgrade screen page
     */
    public function displayUpgradePage()
    {
        global $token;
        include dirname(__FILE__).'/upgrade_screen.php';
    }

    /**
     * Remove temp files for upgrader
     */
    public function removeTempFiles()
    {
        parent::removeTempFiles();
        $this->removeDir($this->cacheDir("upgrades/driver/"));
    }
}