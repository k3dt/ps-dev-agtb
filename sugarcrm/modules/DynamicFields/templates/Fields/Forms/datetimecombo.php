<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
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
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */
require_once('modules/DynamicFields/templates/Fields/TemplateDatetimecombo.php');

function get_body(&$ss, $vardef){
	$defaultTime = '';
	$hours = "";
	$minutes = "";
	$meridiem = "";
	$td = new TemplateDatetimecombo();
	$ss->assign('default_values', array_flip($td->dateStrings));
	
    global $timedate;
    $user_time_format = $timedate->get_user_time_format();
    $show_meridiem = preg_match('/pm$/i', $user_time_format) ? true : false;
    if($show_meridiem) {
    	$ss->assign('default_hours_values', array_flip($td->hoursStrings));
    } else {
    	$ss->assign('default_hours_values', array_flip($td->hoursStrings24));
    }

    $ss->assign('show_meridiem', $show_meridiem);
	$ss->assign('default_minutes_values', array_flip($td->minutesStrings));
	$ss->assign('default_meridiem_values', array_flip($td->meridiemStrings));
	if(isset($vardef['display_default']) && strstr($vardef['display_default'] , '&')){
		$dt = explode("&", $vardef['display_default']); //+1 day&06:00pm
		$date = $dt[0];
		$defaultTime = $dt[1];

        preg_match('/([\d]+):([\d]{2})(am|pm)$/', $defaultTime, $time); //+1 day&06:00pm
        $hours = $time[1];
		$minutes = $time[2];
		$meridiem = $time[3];
		if(!$show_meridiem) {
		   if($meridiem == 'am' && $hours == 12) {
		   	  $hours = '00';
		   } else if ($meridiem == 'pm' && $hours != 12) {
		   	  $hours += 12;
		   }
		}
        $hours = strlen($hours) === 1 ? '0'.$hours : $hours;
		$ss->assign('default_date', $date);
	}
	$ss->assign('default_hours', $hours);
	$ss->assign('default_minutes', $minutes);
	$ss->assign('default_meridiem', $meridiem);
	$ss->assign('defaultTime', $defaultTime);
	return $ss->fetch('modules/DynamicFields/templates/Fields/Forms/datetimecombo.tpl');
}

?>
