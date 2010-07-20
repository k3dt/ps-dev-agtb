<?php 
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
function build_argument_string($arguments=array()) {
   if(!is_array($arguments)) {
   	  return '';
   }
   
   $argument_string = '';
   $count = 0;
   foreach($arguments as $arg) {
   	   if($count != 0) {
          $argument_string .= ' ' . escapeshellarg($arg);	 
   	   } 
   	   $count++;
   }
   
   return $argument_string;
}

$php_path = '';
$run_dce_upgrade = false;
if(isset($argv[3]) && is_dir($argv[3]) && file_exists($argv[3]."/ini_setup.php")) {
                //this is a dce call, set the dce flag
                chdir($argv[3]);
                $run_dce_upgrade = true;
                //set the php path if found
                if(is_file($argv[7].'dce_config.php')){
                   include($argv[7].'dce_config.php');
                   $php_path = $dce_config['client_php_path'].'/';
                }
}

$php_file = $argv[0];
$p_info = pathinfo($php_file);
$php_dir = (isset($p_info['dirname']) && $p_info['dirname'] != '.') ?  $p_info['dirname'] . '/' : '';

$step1 = $php_path."php -f {$php_dir}silentUpgrade_step1.php " . build_argument_string($argv);
exec($step1, $output=array());

$has_error = false;
$run_dce_upgrade = false;

if(empty($output)) {
   $has_error = true;
} else {
	foreach($output as $line) {
	    if(preg_match('/ERROR\:/', $line) || preg_match('/FAILURE/', $line) || preg_match('/Fatal\serror\:/', $line)) {
	       $has_error = true;
	    } else if(preg_match('/RUNNING DCE UPGRADE/', $line)) {
	       $run_dce_upgrade = true;
	    }
	}
}

foreach($output as $line) {
	echo $line . "\n";
}	

if(!$has_error) {
	if($run_dce_upgrade) {
		$output = array();
		$step2 = $php_path."php -f {$php_dir}silentUpgrade_dce_step1.php " . build_argument_string($argv);
		exec($step2, $output);
	} else {
		$step2 = "php -f {$php_dir}silentUpgrade_step2.php " . build_argument_string($argv);
		system($step2);	
	}
}


if($run_dce_upgrade) {
	$has_error = false;
	if(empty($output)) {
	   $has_error = true;
	} else {
		foreach($output as $line) {
		   if(preg_match('/ERROR\:/', $line) || preg_match('/FAILURE/', $line) || preg_match('/Fatal\serror\:/', $line)) {
		      $has_error = true;
		   }
		}
	}
	
	if(!$has_error) {
	   $step3 = $php_path."php -f {$php_dir}silentUpgrade_dce_step2.php " . build_argument_string($argv);
	   system($step3);	
	}
}
?>