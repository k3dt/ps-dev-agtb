<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/




global $timedate;

class CalendarGrid {

	var $args;
	var $real_today_unix; // timestemp of today
	var $weekday_names; // string array of names of week days
	var $startday; // first day of week
	var $scrollable; // srolling in calendar
	var $time_step = 30; // time step
	
	function __construct(&$args){
		global $current_user;
		$this->args = &$args;		
		$this->real_today_unix = CalendarUtils::to_timestamp($GLOBALS['timedate']->get_gmt_db_date());
		
		$weekday_names = array();
		
		$this->startday = $current_user->get_first_day_of_week();
		
		for($i = 0; $i < 7; $i++){
			$j = $i + $this->startday;
			if($j >= 7)
				$j = $j - 7;
			$weekday_names[$i] = $GLOBALS['app_list_strings']['dom_cal_day_short'][$j+1];
		}		
			
		$this->weekday_names = $weekday_names;	
		
		$this->scrollable = false;		
		if(in_array($this->args['cal']->view,array('day','week'))){
			$this->scrollable = true;
		}
		
		$this->time_step = $this->args['cal']->time_step;	
	}
	
	
	/** Returns html of calendar grid
	 * @return string
	 */
	function display(){
		$action = "display_".strtolower($this->args['cal']->view);		
		return $this->$action();
	}
	
	/** Returns html of time column
	 * @param integer $start timestamp	 
	 * @return string
	 */
	function get_time_column($start){		
		$str = "";			
		$head_content = "&nbsp;";	
		if($this->args['cal']->view == 'month'){
			if($this->startday == 0)
				$wf = 1;
			else
				$wf = 0;				
			$head_content = "<a href='".ajaxLink("index.php?module=Calendar&action=index&view=week&hour=0&day=".CalendarUtils::timestamp_to_string($start,'j')."&month=".CalendarUtils::timestamp_to_string($start,'n')."&year=".CalendarUtils::timestamp_to_string($start,'Y'))."'>".CalendarUtils::timestamp_to_string($start + $wf*3600*24,'W')."</a>";
		}			
		$str .= "<div class='left_time_col'>";
			if(!$this->scrollable)			
				$str .= "<div class='day_head'>".$head_content."</div>";
			for($i = 0; $i < 24; $i++){
				for($j = 0; $j < 60; $j += $this->time_step){
					if($j == 0) 
						$innerText = CalendarUtils::timestamp_to_string($start + $i * 3600 ,$GLOBALS['timedate']->get_time_format());
					else
						$innerText = "&nbsp;";						
					if($this->scrollable || !CalendarUtils::check_owt($i,$j,$this->args['cal']->d_start_minutes,$this->args['cal']->d_end_minutes))											
						$str .= "<div class='left_cell'>".$innerText."</div>";
				}
			}	
		$str .= "</div>";		
		return $str;
	}
	
	/** Returns html of day slots column
	 * @param integer $start timestamp
	 * @param integer $day number of day in week
	 * @param string $prefix prefix for id of timeslot used in shared view	 
	 * @return string
	 */
	function get_day_column($start,$day = 0,$prefix = ""){
	
		$curr_time = $start;		
		
		$str = "";
		$str .= "<div class='day_col'>";
		$str .= $this->get_day_head($start,$day);
		for($i = 0; $i < 24; $i++){
			for($j = 0; $j < 60; $j += $this->time_step){																	
				$timestr = CalendarUtils::timestamp_to_string($curr_time,$GLOBALS['timedate']->get_time_format());
				if($this->scrollable || !CalendarUtils::check_owt($i,$j,$this->args['cal']->d_start_minutes,$this->args['cal']->d_end_minutes))
					$str .= "<div id='t_".$curr_time.$prefix."' class='slot' dur='".$timestr."' datetime='".CalendarUtils::timestamp_to_string($curr_time)."'></div>";
				$curr_time += $this->time_step*60;
			}
		}
		$str .= "</div>";
		
		return $str;	
	}
	
	/** Returns html of day head
	 * @param integer $start timestamp
	 * @param integer $day number of day in week 
	 * @param bulean $force force display header 
	 * @return string
	 */	
	function get_day_head($start,$day = 0,$force = false){
		$str = "";
		if(!$this->scrollable || $force){
			$headstyle = ""; 
			if($this->real_today_unix == $start)
				$headstyle = " today";
			$str .= "<div class='day_head".$headstyle."'><a href='".ajaxLink("index.php?module=Calendar&action=index&view=day&hour=0&day=".CalendarUtils::timestamp_to_string($start,'j')."&month=".CalendarUtils::timestamp_to_string($start,'n')."&year=".CalendarUtils::timestamp_to_string($start,'Y'))."'>".$this->weekday_names[$day]." ".CalendarUtils::timestamp_to_string($start,'d')."</a></div>";
		}
		return $str;
	}	
	
	
	function display_week(){
		
		$today_unix = $this->args['cal']->today_unix;
		
		$Tw = date("w",$today_unix - date('Z',$today_unix));
		$Ti = date("i",$today_unix - date('Z',$today_unix));
		$Ts = date("s",$today_unix - date('Z',$today_unix));		
		$Th = date("H",$today_unix - date('Z',$today_unix));
		
		$week_start_unix = $today_unix - $Ts - 60*$Ti - 60*60*$Th - 60*60*24*($Tw);
		$week_start_unix = $week_start_unix + $this->startday * 60*60*24;
		$week_start = date("m/d/Y H:i:s",$week_start_unix);
		
		$str = "";		
		
		$str .= "<div id='cal-grid' style='visibility: hidden;'>";
				
			$str .= "<div style='overflow-y: hidden;'>";						
				$str .= "<div class='left_time_col'>";
					$str .= "<div class='day_head'>&nbsp;</div>";		
				$str .= "</div>";
				$str .= "<div class='week_block'>";
				for($d = 0; $d < 7; $d++){
					$curr_time = $week_start_unix + $d*86400;
					$str .= "<div class='day_col'>";
					$str .= $this->get_day_head($curr_time,$d,true);
					$str .= "</div>";			
				}
				$str .= "</div>";		
			$str .= "</div>";		
		
			$str .= "<div id='cal-scrollable' style='overflow-y: scroll; clear: both; height: 479px;'>";			
				$str .= $this->get_time_column($week_start_unix);			
				$str .= "<div class='week_block'>";
				for($d = 0; $d < 7; $d++){
					$curr_time = $week_start_unix + $d*86400;				
					$str .= $this->get_day_column($curr_time);
				}	
				$str .= "</div>";
		
			$str .= "</div>";
				
		$str .= "</div>";
		
		return $str;
	}		
	
	
	function display_day(){
	
		$today_unix = $this->args['cal']->today_unix;
	
		$Ti = date("i",$today_unix - date('Z',$today_unix));
		$Ts = date("s",$today_unix - date('Z',$today_unix));
		$Th = date("H",$today_unix - date('Z',$today_unix));

		$day_start_unix = $today_unix - $Ts - 60*$Ti - 60*60*$Th;
		$day_start = date("m/d/Y H:i:s",$day_start_unix);

		$str = "";
		$str .= "<div id='cal-grid' style=' min-width: 300px; visibility: hidden;'>";		
		
			$str .= "<div id='cal-scrollable' style='overflow-y: scroll; height: 479px;'>";			
				$str .= $this->get_time_column($day_start_unix);
				$d = 0;
				$curr_time = $day_start_unix + $d*86400;
				$str .= "<div class='week_block'>";				
				$str .= $this->get_day_column($curr_time);
				$str .= "</div>";
			$str .= "</div>";
		
		$str .= "</div>";
		
		return $str;	
	}	
	
	
	function display_month(){
	
		$today_unix = $this->args['cal']->today_unix;
	
		$Tw = date("w",$today_unix - date('Z',$today_unix));
		$Ti = date("i",$today_unix - date('Z',$today_unix));
		$Ts = date("s",$today_unix - date('Z',$today_unix));
		$Th = date("H",$today_unix - date('Z',$today_unix));		
		$Td = date("d",$today_unix - date('Z',$today_unix));
		$Tt = date("t",$today_unix - date('Z',$today_unix));

		$month_start_unix = $today_unix - $Ts - 60*$Ti - 60*60*$Th - 60*60*24*($Td - 1);
		$month_end_unix = $month_start_unix + 60*60*24*($Tt);

		$Tw = date("w",$month_start_unix - date('Z',$month_start_unix));
		$week_start_unix = $month_start_unix - 60*60*24*($Tw);		
		$week_start_unix = $week_start_unix + $this->startday * 60*60*24;
		
		$day_num = date("j",$week_start_unix - date('Z',$week_start_unix));
		if($day_num <= 7 && $day_num > 1)
			$week_start_unix = $week_start_unix - 7*60*60*24;		

		if($this->startday == 0)
			$wf = 1;
		else
			$wf = 0;
	
		$str = "";
		$str .= "<div id='cal-grid' style='visibility: hidden;'>";
			$curr_time_global = $week_start_unix;
			$w = 0;
			while($curr_time_global < $month_end_unix){
				$str .= $this->get_time_column($curr_time_global);				
				$str .= "<div class='week_block'>";	
				for($d = 0; $d < 7; $d++){
					$curr_time = $week_start_unix + $d*86400 + $w*60*60*24*7;
					$str .= $this->get_day_column($curr_time,$d);		
				}
				$str .= "</div>";
				$str .= "<div style='clear: left;'></div>";
				$curr_time_global += 60*60*24*7;
				$w++;
			}
		$str .= "</div>";
		
		return $str;
	}
	
	
	function display_shared(){
	
		$today_unix = $this->args['cal']->today_unix;
	
		$Tw = date("w",$today_unix - date('Z',$today_unix));
		$Ti = date("i",$today_unix - date('Z',$today_unix));
		$Ts = date("s",$today_unix - date('Z',$today_unix));
		$Th = date("H",$today_unix - date('Z',$today_unix));

		$week_start_unix = $today_unix - $Ts - 60*$Ti - 60*60*$Th - 60*60*24*($Tw);
		$week_start_unix = $week_start_unix + $this->startday * 60*60*24;
		$week_start = date("m/d/Y H:i:s",$week_start_unix);

		$str = "";
		$str .= "<div id='cal-grid' style='visibility: hidden;'>";
		$un = 0;
		
		$shared_user = new User();
		foreach($this->args['cal']->shared_ids as $member_id){

			$un_str = "_".$un;
		
			$shared_user->retrieve($member_id);
			$str .= "<div style='clear: both;'></div>";			
			$str .= "<div class='monthCalBody'><h5 class='calSharedUser'>".$shared_user->full_name."</h5></div>";	
			$str .= "<div user_id='".$member_id."' user_name='".$shared_user->user_name."'>";			
			
			$str .= $this->get_time_column($week_start_unix);
				$str .= "<div class='week_block'>";
				for($d = 0; $d < 7; $d++){
					$curr_time = $week_start_unix + $d*86400;
					$str .= $this->get_day_column($curr_time,$d,$un_str);
				}
				$str .= "</div>";		
			$str .= "</div>";
			$un++;
		}
		$str .= "</div>";
		
		return $str;
	}	
	
	
	function display_year(){	

		$today_unix = $this->args['cal']->today_unix;

		$weekEnd1 = 0 - $this->startday; 
		$weekEnd2 = -1 - $this->startday; 
		if($weekEnd1 < 0)
			$weekEnd1 += 7;		
		if($weekEnd2 < 0)
			$weekEnd2 += 7;	

		$Tw = date("w",$today_unix - date('Z',$today_unix));
		$Ti = date("i",$today_unix - date('Z',$today_unix));
		$Ts = date("s",$today_unix - date('Z',$today_unix));
		$Th = date("H",$today_unix - date('Z',$today_unix));
		$Td = date("d",$today_unix - date('Z',$today_unix));
		$Tm = date("m",$today_unix - date('Z',$today_unix));
		$Ty = date("Y",$today_unix - date('Z',$today_unix));
		$Tt = date("t",$today_unix - date('Z',$today_unix));
		$Tt = date("z",$today_unix - date('Z',$today_unix));
		$TL = date("L",$today_unix - date('Z',$today_unix));

		$diy = 365;
		if($TL == 1)
			$diy++;	

		$Tz = 0;
		$month_start_unix = 0;
		$year_start_unix = $today_unix - $Ts - 60*$Ti - 60*60*$Th - 60*60*24*($Tz);
		$year_end_unix = $month_start_unix + 60*60*24*($diy);		

		$Tw = date("w",$year_start_unix - date('Z',$year_start_unix));

		$week_start_unix = $year_start_unix - 60*60*24*($Tw);
		$week_start_unix = $week_start_unix + $this->startday * 60*60*24;
	

		$str = "";
		$str .= '<table id="daily_cal_table" cellspacing="1" cellpadding="0" border="0" width="100%">';
		$curr_time_global = $year_start_unix;

		for($m = 0; $m < 12; $m++){
	
			$gmt_g = CalendarUtils::timestamp_to_string($this->args['cal']->today_unix,'Y'). "-" . str_pad($m + 1,2,"0",STR_PAD_LEFT) . "-" . "01";
			$g_parsed = date_parse($gmt_g);
			$g_unix = gmmktime($g_parsed['hour'],$g_parsed['minute'],$g_parsed['second'],$g_parsed['month'],$g_parsed['day'],$g_parsed['year']);
			$Tw = date("w",$g_unix - date('Z',$g_unix));
			$Ti = date("i",$g_unix - date('Z',$g_unix));
			$Ts = date("s",$g_unix - date('Z',$g_unix));
			$Th = date("H",$g_unix - date('Z',$g_unix));
			$Td = date("d",$g_unix - date('Z',$g_unix));
			$Tm = date("m",$g_unix - date('Z',$g_unix));
			$Ty = date("Y",$g_unix - date('Z',$g_unix));
			$Tt = date("t",$g_unix - date('Z',$g_unix));
			$Tz = date("z",$g_unix - date('Z',$g_unix));
			$TL = date("L",$g_unix - date('Z',$g_unix));

			$month_start_unix = $g_unix - $Ts - 60*$Ti - 60*60*$Th - 60*60*24*($Td - 1);
			$month_end_unix = $month_start_unix + 60*60*24*($Tt);
			$Tw = date("w",$month_start_unix - date('Z',$month_start_unix));	
			$week_start_unix = $month_start_unix - 60*60*24*($Tw);
			$week_start_unix = $week_start_unix + $this->startday * 60*60*24;			
			$day_num = date("j",$week_start_unix - date('Z',$week_start_unix));
			if($day_num <= 7 && $day_num > 1)
				$week_start_unix = $week_start_unix - 7*60*60*24;
						
						
			if($m % 3 == 0)
				$str .= "<tr>";		
					$str .= '<td class="yearCalBodyMonth" align="center" valign="top" scope="row">';
						$str .= '<a class="yearCalBodyMonthLink" href="'.ajaxLink('index.php?module=Calendar&action=index&view=month&&hour=0&day=1&month='.($m+1).'&year='.CalendarUtils::timestamp_to_string($month_start_unix,'Y')).'">'.$GLOBALS['app_list_strings']['dom_cal_month_long'][$m+1].'</a>';
						$str .= '<table id="daily_cal_table" cellspacing="1" cellpadding="0" border="0" width="100%">';	
							$str .= '<tr class="monthCalBodyTH">';
								for($d = 0; $d < 7; $d++)
									$str .= '<th width="14%">'.$this->weekday_names[$d].'</th>';			
							$str .= '</tr>';				
							$curr_time_global = $week_start_unix;
							$w = 0;
							while($curr_time_global < $month_end_unix){
								$str .= '<tr class="monthViewDayHeight yearViewDayHeight">';
									for($d = 0; $d < 7; $d++){
										$curr_time = $week_start_unix + $d*86400 + $w*60*60*24*7;

										if($curr_time < $month_start_unix || $curr_time >= $month_end_unix)
											$monC = "";
										else
											$monC = '<a href="'.ajaxLink('index.php?module=Calendar&action=index&view=day&hour=0&day='.CalendarUtils::timestamp_to_string($curr_time,'j').'&month='.CalendarUtils::timestamp_to_string($curr_time,'n').'&year='.CalendarUtils::timestamp_to_string($curr_time,'Y')) .'">'.CalendarUtils::timestamp_to_string($curr_time,'j').'</a>';
								
									
										if($d == $weekEnd1 || $d == $weekEnd2)	
											$str .= "<td class='weekEnd monthCalBodyWeekEnd'>"; 
										else
											$str .= "<td class='monthCalBodyWeekDay'>";				
								
												$str .= $monC;
											$str .= "</td>";
									}
								$str .= "</tr>";
								$curr_time_global += 60*60*24*7;
								$w++;
							}				
						$str .= '</table>';	
						
					$str .= '</td>';	
	
			if(($m - 2) % 3 == 0)
				$str .= "</tr>";	
		}
		$str .= "</table>";
		
		return $str;			
	}
}

?>
