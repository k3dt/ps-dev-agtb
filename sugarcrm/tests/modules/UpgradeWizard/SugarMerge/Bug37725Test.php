<?php
require_once 'include/dir_inc.php';

class Bug37725Test extends Sugar_PHPUnit_Framework_TestCase  {

var $merge;
var $has_dir;
var $modules;

function setUp() {
   $this->modules = array('Meetings');
   $this->has_dir = array();
   
   foreach($this->modules as $module) {
	   if(!file_exists("custom/modules/{$module}/metadata")){
		  mkdir_recursive("custom/modules/{$module}/metadata", true);
	   }
	   
	   if(file_exists("custom/modules/{$module}")) {
	   	  $this->has_dir[$module] = true;
	   }
	   
	   $files = array('detailviewdefs');
	   foreach($files as $file) {
	   	   if(file_exists("custom/modules/{$module}/metadata/{$file}")) {
		   	  copy("custom/modules/{$module}/metadata/{$file}.php", "custom/modules/{$module}/metadata/{$file}.php.bak");
		   }
		   
		   if(file_exists("custom/modules/{$module}/metadata/{$file}.php.suback.php")) {
		      copy("custom/modules/{$module}/metadata/{$file}.php.suback.php", "custom/modules/{$module}/metadata/{$file}.php.suback.bak");
		   }
		   
		   if(file_exists("tests/modules/UpgradeWizard/SugarMerge/od_metadata_files/custom/modules/{$module}/metadata/{$file}.php")) {
		   	  copy("tests/modules/UpgradeWizard/SugarMerge/od_metadata_files/custom/modules/{$module}/metadata/{$file}.php", "custom/modules/{$module}/metadata/{$file}.php");
		   }
	   } //foreach
   } //foreach
}


function tearDown() {

   foreach($this->modules as $module) {
	   if(!$this->has_dir[$module]) {
	   	  rmdir_recursive("custom/modules/{$module}");
	   }  else {
	   	   $files = array('detailviewdefs');
		   foreach($files as $file) {
		      if(file_exists("custom/modules/{$module}/metadata/{$file}.php.bak")) {
		      	 copy("custom/modules/{$module}/metadata/{$file}.php.bak", "custom/modules/{$module}/metadata/{$file}.php");
	             unlink("custom/modules/{$module}/metadata/{$file}.php.bak");
		      } else if(file_exists("custom/modules/{$module}/metadata/{$file}.php")) {
		      	 unlink("custom/modules/{$module}/metadata/{$file}.php");
		      }
		      
		   	  if(file_exists("custom/modules/{$module}/metadata/{$module}.php.suback.bak")) {
		      	 copy("custom/modules/{$module}/metadata/{$file}.php.suback.bak", "custom/modules/{$module}/metadata/{$file}.php.suback.php");
	             unlink("custom/modules/{$module}/metadata/{$file}.php.suback.bak");
		      } else if(file_exists("custom/modules/{$module}/metadata/{$file}.php.suback.php")) {
		      	 unlink("custom/modules/{$module}/metadata/{$file}.php.suback.php");
		      }  
		   }
	   }
   } //foreach
}


function test_meetings_detailview_merge() {		
   require_once 'modules/UpgradeWizard/SugarMerge/DetailViewMerge.php';
   $this->merge = new DetailViewMerge();	
   $this->merge->merge('Meetings', 'tests/modules/UpgradeWizard/SugarMerge/od_metadata_files/551/modules/Meetings/metadata/detailviewdefs.php', 'modules/Meetings/metadata/detailviewdefs.php', 'custom/modules/Meetings/metadata/detailviewdefs.php');
   $this->assertTrue(file_exists('custom/modules/Meetings/metadata/detailviewdefs.php.suback.php'));
   require('custom/modules/Meetings/metadata/detailviewdefs.php');
   $fields = array();
   $panels = array();
   
   //echo var_export($viewdefs['Meetings']['DetailView']['panels'], true);
   $columns_sanitized = true;
   
   $date_entered_field = '';
   $date_modified_field = '';
   
   foreach($viewdefs['Meetings']['DetailView']['panels'] as $panel_key=>$panel) {
   	  $panels[$panel_key] = $panel_key;
   	  foreach($panel as $r=>$row) {
   	  	 $new_row = true;
   	  	 foreach($row as $col_key=>$col) {
   	  	 	if($new_row && $col_key != 0) {
   	  	 	   $columns_sanitized = false;   
   	  	 	}
   	  	 	
   	  	 	$new_row = false;
   	  	 	
   	  	 	$id = is_array($col) && isset($col['name']) ? $col['name'] : $col;
   	  	 	if(!empty($id) && !is_array($id)) {
   	  	 	   $fields[$id] = $col;
   	  	 	}
   	  	 	
   	  	 	if($id == 'date_entered') {
   	  	 	   $date_entered_field = $col;
   	  	 	} else if($id == 'date_modified') {
   	  	 	   $date_modified_field = $col;
   	  	 	}
   	  	 }
   	  }
   }
   
   $this->assertTrue(isset($fields['meetings_opportunities_name']), "Assert that meetings_opportunities_name field is preserved");
   $this->assertTrue($viewdefs['Meetings']['DetailView']['panels']['default'][0][0]['name'] == 'name', "Assert that position of name field has not changed");
   $this->assertTrue($viewdefs['Meetings']['DetailView']['panels']['default'][0][1]['name'] == 'status', "Assert that position of status field has not changed");
   $this->assertTrue(isset($fields['date_modified']), "Assert that date_modified field is added");
   $this->assertTrue(isset($fields['date_entered']), "Assert that date_entered field is added");
   $this->assertTrue(!isset($fields['created_by_name']), "Assert that the created_by_name was merged");
   $this->assertTrue(isset($date_entered_field) && $date_entered_field['customCode'] == '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}', "Assert that date_entered field is correctly merged");
   $this->assertTrue(isset($date_modified_field) && $date_modified_field['label'] == 'LBL_DATE_MODIFIED' && $date_modified_field['customCode'] == '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}', "Assert that date_modified field is correctly merged");
}


}
?>