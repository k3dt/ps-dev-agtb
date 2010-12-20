<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/



class ReportSchedule{

var $table_name='report_schedules';
var $db;
function ReportSchedule(){
	$this->db = DBManagerFactory::getInstance();

}


function drop_tables ()
{
    $query = 'DROP TABLE IF EXISTS '.$this->table_name;
    $this->db->query($query);
}

function save_schedule($id, $user_id, $report_id, $date_start, $interval, $active, $schedule_type){
	global $timedate;
	$time = time();
	$origDateStart = $date_start;
	$date_start_str = "";
	if(empty($id))
	{
		$id = create_guid();

		if( empty($date_start) )
		    $date_start = gmdate($timedate->get_db_date_time_format() );

        $next_run_date = $this->getNextRunDate($date_start, $interval);

	    if(strlen(trim($origDateStart))==0)
	        $date_start_str = " NULL ";
	    else
	        $date_start_str = " '$origDateStart' ";

		$query = "INSERT INTO $this->table_name (id, user_id, report_id, date_start,next_run, time_interval, active, schedule_type) VALUES ('$id', '$user_id', '$report_id', $date_start_str , '$next_run_date', '$interval', '$active', '$schedule_type')";
	}
	else
	{
	    if(strlen(trim($origDateStart))==0)
	       $date_start_str = " date_start = NULL, ";
	    else
	       $date_start_str = " date_start = '$origDateStart', ";

		$query = "UPDATE $this->table_name  SET time_interval=$interval, ".$date_start_str." active=$active, schedule_type='".$schedule_type."'";
		if(!empty($date_start) && $active)
		{
		    $next_run_date = $this->getNextRunDate($date_start, $interval);
			$query .= ", next_run='$next_run_date'";
		}
		$query .= " WHERE id='$id'";
	}
	$this->db->query($query, true, "error saving schedule");

	return $id;
}

function getNextRunDate($date_start,$interval)
{
    global $timedate;
	$time = time();

    $date_start = strtotime($date_start . ' GMT');
    if( $date_start <= $time )
    {
        while($date_start <= $time)
            $date_start += $interval;
    }
    else
        $date_start += $interval;

    return gmdate($timedate->get_db_date_time_format(), $date_start);
}

function get_users_schedule($id=''){
		if(empty($id)){
			global $current_user;
			$id = $current_user->id;
		}
		$return_array = array();
		$query = "SELECT * FROM $this->table_name WHERE user_id='$id'";
		$results = $this->db->query($query);
		while($row = $this->db->fetchByAssoc($results)){
			$return_array[$row['report_id']] = $row;
		}
		return $return_array;
}

function get_report_schedule_for_user($report_id, $user_id=''){
	if(empty($user_id)){
			global $current_user;
			$user_id = $current_user->id;
		}
	$query = "SELECT * FROM $this->table_name WHERE report_id='$report_id' AND user_id='$user_id' AND deleted=0";
	$results = $this->db->query($query);
	$row = $this->db->fetchByAssoc($results);
	return $row;
}

function get_report_schedule($report_id){
	$query = "SELECT * FROM $this->table_name WHERE report_id='$report_id' AND deleted=0";
	$results = $this->db->query($query);
	$return_array = array();
	while($row = $this->db->fetchByAssoc($results)){
			$return_array[$row['report_id']] = $row;
	}
	return $return_array;
}

function get_reports_to_email($user_id= '', $schedule_type="pro"){
	$where = '';
	if(!empty($user_id)){
		$where = "AND user_id='$user_id'";
	}
	$time = gmdate($GLOBALS['timedate']->get_db_date_time_format(), time());
	$query = "SELECT report_schedules.* FROM $this->table_name \n".
			"join saved_reports on saved_reports.id=$this->table_name.report_id \n".
			"join users on users.id = report_schedules.user_id".
			" WHERE saved_reports.deleted=0 AND \n" .
			"$this->table_name.next_run < '$time' $where AND \n".
			"$this->table_name.deleted=0 AND \n".
			"$this->table_name.active=1 AND " .
			"$this->table_name.schedule_type='".$schedule_type."' AND\n".
			"users.status='Active' AND users.deleted='0'".
			"ORDER BY $this->table_name.next_run ASC";

	$results = $this->db->query($query);
	$return_array = array();
	while($row = $this->db->fetchByAssoc($results)){
			$return_array[$row['report_id']] = $row;
	}
	return $return_array;

}

function get_ent_reports_to_email($user_id= '', $schedule_type="ent"){
	$where = '';
	if(!empty($user_id)){
		$where = "AND user_id='$user_id'";
	}
	$time = gmdate($GLOBALS['timedate']->get_db_date_time_format(), time());
	$query = "SELECT report_schedules.* FROM $this->table_name \n".
			"join report_maker on report_maker.id=$this->table_name.report_id \n".
			"join users on users.id = report_schedules.user_id".
			" WHERE report_maker.deleted=0 AND \n" .
			"$this->table_name.next_run < '$time' $where AND \n".
			"$this->table_name.deleted=0 AND \n".
			"$this->table_name.active=1 AND " .
			"$this->table_name.schedule_type='".$schedule_type."' AND\n".
			"users.status='Active' AND users.deleted='0'".
			"ORDER BY $this->table_name.next_run ASC";
	$results = $this->db->query($query);
	$return_array = array();
	while($row = $this->db->fetchByAssoc($results)){
			$return_array[$row['report_id']] = $row;
	}
	return $return_array;

}

function update_next_run_time($schedule_id, $next_run, $interval){
    $next_run .= " GMT"; // Append GMT to the value from the database to ensure it is converted correctly in strtotime
    $last_run = strtotime($next_run);
    $time = time();

    while($last_run <= $time){
        $last_run += $interval;
    }

    $next_run = gmdate($GLOBALS['timedate']->get_db_date_time_format(), $last_run); //GMT for database
    $query = "UPDATE $this->table_name SET next_run='$next_run' WHERE id='$schedule_id'";
    $this->db->query($query);
			
}
}
?>