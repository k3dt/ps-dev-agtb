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

/**
 * THIS CLASS IS GENERATED BY MODULE BUILDER
 * PLEASE DO NOT CHANGE THIS CLASS
 * PLACE ANY CUSTOMIZATIONS IN pmse_BpmActivityDefinition
 */
class pmse_BpmActivityDefinition_sugar extends Basic {
	var $new_schema = true;
	var $module_dir = 'pmse_Project/pmse_BpmActivityDefinition';
	var $object_name = 'pmse_BpmActivityDefinition';
	var $table_name = 'pmse_bpm_activity_definition';
	var $importable = false;
        var $id;
		var $name;
		var $date_entered;
		var $date_modified;
		var $modified_user_id;
		var $modified_by_name;
		var $created_by;
		var $created_by_name;
		var $description;
		var $deleted;
		var $created_by_link;
		var $modified_user_link;
		var $activities;
		var $assigned_user_id;
		var $assigned_user_name;
		var $assigned_user_link;
    var $pro_id;
    var $act_type;
    var $act_duration;
    var $act_duration_unit;
    var $act_send_notification;
    var $act_assignment_method;
    var $act_assign_team;
    var $act_assign_user;
    var $act_value_based_assignment;
    var $act_reassign;
    var $act_reassign_team;
    var $act_adhoc;
    var $act_adhoc_behavior;
    var $act_adhoc_team;
    var $act_response_buttons;
    var $act_last_user_assigned;
    var $act_field_module;
    var $act_fields;
    var $act_readonly_fields;
    var $act_expected_time;
    var $act_required_fields;
    var $act_related_modules;
    var $act_service_url;
    var $act_service_params;
    var $act_service_method;
    var $act_update_record_owner;
    var $execution_mode;

	/**
	 * This is a depreciated method, please start using __construct() as this method will be removed in a future version
     *
     * @see __construct
     * @depreciated
	 */
	function pmse_BpmActivityDefinition_sugar(){
		self::__construct();
	}

	public function __construct(){
		parent::__construct();
	}
}
