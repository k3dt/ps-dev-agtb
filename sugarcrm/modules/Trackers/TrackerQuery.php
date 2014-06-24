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
/*********************************************************************************
 * $Id: TrackerPerf.php 31071 2008-01-17 02:08:30Z jmertic $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
//FILE SUGARCRM flav=pro ONLY
require_once('data/SugarBean.php');

class TrackerQuery extends SugarBean {

    var $module_dir = 'Trackers';
    var $module_name = 'TrackerQueries';
    var $object_name = 'tracker_queries';
    var $table_name = 'tracker_queries';
    var $acltype = 'TrackerQuery';
    var $acl_category = 'TrackerQueries';
    var $disable_custom_fields = true;

    public function __construct() {
        global $dictionary;
        if(isset($this->module_dir) && isset($this->object_name) && !isset($GLOBALS['dictionary'][$this->object_name])){
            require('metadata/tracker_queriesMetaData.php');
        }
        parent::__construct();
        //BEGIN SUGARCRM flav=pro ONLY
        $this->disable_row_level_security = true;
        //END SUGARCRM flav=pro ONLY
    }

    function bean_implements($interface){
        switch($interface){
            case 'ACL': return true;
        }
        return false;
    }
}
?>
