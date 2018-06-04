<?php
if(!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');

}
/**
 * This script executes after the files are copied during the install.
 *
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
 * by SugarCRM are Copyright (C) 2005 SugarCRM, Inc.; All Rights Reserved.
 */

require_once(clean_path($unzip_dir.'/scripts/upgrade_utils.php'));
function status_post_install_action($action){
	$currProg = post_install_progress();
	$currPostInstallStep = '';
	$postInstallQuery = '';
	if(is_array($currProg) && $currProg != null){
		foreach($currProg as $key=>$val){
			if($key==$action){
				return $val;
			}
		}
	}
	return '';
}

function post_install()
{
	global $unzip_dir;
	global $sugar_config;
	global $sugar_version;
	global $path;
	global $db;
	global $_SESSION;
        if(!isset($_SESSION['sqlSkippedQueries'])){
	 	$_SESSION['sqlSkippedQueries'] = array();
	 }
	initialize_session_vars();

    $unzip_dir	= $_SESSION['unzip_dir'];
	$self_dir = "$unzip_dir/scripts";
	//_logThis('Start Upgrade falvor.', $path);
	$log =& $GLOBALS['log'];
	//echo 'Upgrade flavor begin';
	//upgrade_Flavors5();
	//echo 'Upgrade flavor End';
	//_logThis('End Upgrade falvor.', $path);
	$schemaFile = '';
    if($sugar_config['dbconfig']['db_type'] == 'mysql') {
	   $log->info('Running SQL file 610_pro_to_corp_mysql.sql');
	   $schemaFile = "$self_dir/610_pro_to_corp_mysql.sql";
    } else if ($sugar_config['dbconfig']['db_type'] == 'mssql') {
	   $schemaFile = "$self_dir/610_pro_to_corp_mssql.sql";
	   if(in_array(get_class($db),array('SqlsrvManager','FreeTDSManager')) && file_exists("$self_dir/610_pro_to_corp_mssql_freetds.sql")){
	       $schemaFile = "$self_dir/610_pro_to_corp_mssql_freetds.sql";
	   }	   
	   $log->info("Running SQL file $schemaFile");
    }

    $post_action = status_post_install_action('sql_query');
	if($post_action != null){
	   if($post_action != 'done'){
			//continue from where left in previous run
			@parseAndExecuteSqlFile($schemaFile,'sql_query',$post_action);
		  	$currProg['sql_query'] = 'done';
		  	post_install_progress($currProg,'set');
		}
	 }
	 else{
		//never ran before
		@parseAndExecuteSqlFile($schemaFile,'sql_query');
	  	$currProg['sql_query'] = 'done';
	  	post_install_progress($currProg,'set');
	  }

    if(isset($_SESSION['sugar_version_file']) && !empty($_SESSION['sugar_version_file'])) {
		if(!copy($_SESSION['sugar_version_file'], clean_path(getcwd().'/sugar_version.php'))) {
			$log->info('*** ERROR: sugar_version.php could not be copied to destination! Cannot complete upgrade');
			return false;
		}
		else {
			$log->info('sugar_version.php successfully updated!');
		}
	}
    //set license date.
	$_SESSION['LICENSE_EXPIRES_IN'] = 'valid';
	$_SESSION['VALIDATION_EXPIRES_IN'] = 'valid';

	///////////////////////////////////////////////////////////////////////////
	////	FILESYSTEM SECURITY FIX (Bug 9365)
	_logThis("Applying .htaccess update security fix.", $path);
	include_once("modules/Administration/UpgradeAccess.php");
	
    ///////////////////////////////////////////////////////////////////////////
    ////    REBUILD DASHLETS
    _logThis("Rebuilding Dashlets", $path);
    rebuild_dashlets();	
}

function rebuild_dashlets(){
    if(is_file('cache/dashlets/dashlets.php')) {
        unlink('cache/dashlets/dashlets.php');
    }
    require_once('include/Dashlets/DashletCacheBuilder.php');

    $dc = new DashletCacheBuilder();
    $dc->buildCache();
}

function rebuild_teams(){
	require_once('modules/Teams/Team.php');
    require_once('modules/Administration/RepairTeams.php');
    process_team_access(false, false,true,'1');
}

function rebuild_roles(){
    global $ACLActions, $beanList, $beanFiles;
    include('modules/ACLActions/actiondefs.php');
    include('include/modules.php'); 
	require_once('modules/ACLFields/ACLField.php');
    include("modules/ACL/install_actions.php");
}
function upgrade_Flavors5() {
	//echo 'running flavors script ';
	require_once ('modules/Relationships/Relationship.php');
	include_once ('include/database/DBManagerFactory.php');
	global $current_user, $beanFiles,$dictionary;
	set_time_limit(3600);
	$db = & DBManagerFactory :: getInstance();
	$sql = '';
	VardefManager::clearVardef();
	$execute = false;
	foreach ($beanFiles as $bean => $file) {
		require_once ($file);
		$focus = new $bean ();
		$sql .= $db->repairTable($focus, $execute);
	}
	$olddictionary = $dictionary;
	unset ($dictionary);
	include ('modules/TableDictionary.php');
	foreach ($dictionary as $meta) {
		$tablename = $meta['table'];
		$fielddefs = $meta['fields'];
		$indices = $meta['indices'];
		$sql .= $db->repairTableParams($tablename, $fielddefs, $indices, $execute);
	}

	if (isset ($sql) && !empty ($sql)) {
		$qry_str = "";
		foreach (explode("\n", $sql) as $line) {
			if (!empty ($line) && substr($line, -2) != "*/") {
				$line .= ";";
			}
			$qry_str .= $line . "\n";
		 }
	}

	$dictionary = $olddictionary;
	$qry_str = str_replace(
		array(
			"\n",
			'&#039;',
		),
		array(
			'',
			"'",
		),
		preg_replace('#(/\*.+?\*/\n*)#', '', $qry_str)
	);
	foreach (explode(";", $qry_str) as $stmt) {
		$stmt = trim($stmt);
		if (!empty ($stmt)) {
			$db->executeQuery($stmt, 'Executing repair query: ');
		}
	}

 //echo $qry_str;
 //echo 'done running flavors script ';
}
?>
