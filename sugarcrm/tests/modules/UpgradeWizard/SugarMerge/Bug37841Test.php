<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

require_once 'include/dir_inc.php';

class Bug37841Test extends Sugar_PHPUnit_Framework_TestCase  
{

    var $merge;
    var $has_dir;
    var $modules;
    
    function setUp() {
       $this->modules = array('Accounts');
       $this->has_dir = array();
       
       foreach($this->modules as $module) {
    	   if(!file_exists("custom/modules/{$module}/metadata")){
    		  mkdir_recursive("custom/modules/{$module}/metadata", true);
    	   }
    	   
    	   if(file_exists("custom/modules/{$module}")) {
    	   	  $this->has_dir[$module] = true;
    	   }
       } //foreach
       $this->clearFilesInDirectory('custom/modules/Accounts/metadata');
       $this->clearFilesInDirectory('custom/history/modules/Accounts/metadata');
    }
    
    
    function tearDown() {
       $this->clearFilesInDirectory('custom/history/modules/Accounts/metadata');
       foreach($this->modules as $module) {
    	   if(!$this->has_dir[$module]) {
    	   	  rmdir_recursive("custom/modules/{$module}");
    	   }  else {
    	   	   $files = array('editviewdefs','detailviewdefs');
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
    
    
    
    /**
     * Ensure that no custom metadata is created and no history item created.
     *
     */
    function testHistoryCreationForNonUpgradedMetadataFiles() 
    {		
       $this->clearFilesInDirectory('custom/modules/Accounts/metadata');
       $this->clearFilesInDirectory('custom/history/modules/Accounts/metadata');
       require_once('modules/UpgradeWizard/SugarMerge/SugarMerge.php');
       $sugar_merge = new SugarMerge('tests/modules/UpgradeWizard/SugarMerge/od_metadata_files/610/oob');
       
       //Using oob defs make sure nothing is merged
       $mergedFiles = $sugar_merge->mergeModule('Accounts');
       $this->assertFalse(file_exists('custom/modules/Accounts/metadata/detailviewdefs.php'));
       $this->assertFalse($this->checkForHistoryRecords('Accounts'));
    }
    
    /**
     * Ensure that a history item is created when SugarMerge executes and that the file contents are identical.
     *
     */
    function testHistoryCreationForUpgradedMetadataFiles() 
    {	
        
        $accountsHistoryMetadataLocation = 'custom/history/modules/Accounts/metadata';
        $this->clearFilesInDirectory('custom/modules/Accounts/metadata');
        $this->clearFilesInDirectory($accountsHistoryMetadataLocation);
        $customFile = "tests/modules/UpgradeWizard/SugarMerge/od_metadata_files/610/custom/modules/Accounts/metadata/detailviewdefs.php";
        $customFileTo = "custom/modules/Accounts/metadata/detailviewdefs.php";
        copy($customFile, $customFileTo);
        require_once('modules/UpgradeWizard/SugarMerge/SugarMerge.php');
        $sugar_merge = new SugarMerge('tests/modules/UpgradeWizard/SugarMerge/od_metadata_files/610/custom');
        $mergedFiles = $sugar_merge->mergeModule('Accounts');
    
        $this->assertTrue(file_exists('custom/modules/Accounts/metadata/detailviewdefs.php'), "Custom metadata file not created.");
        $this->assertTrue($this->checkForHistoryRecords('Accounts'));
        //Ensure history file and custom file are the same.
        $oldCustomFile = file_get_contents($customFile);
        $newHistoryFile = $this->getFirstFileContentsInDirectory($accountsHistoryMetadataLocation);
        $this->assertEquals($oldCustomFile, $newHistoryFile, "Error previous custom file before merge and new history record are not identical.");
    }
    
    
    private function clearFilesInDirectory($path)
    {
        $dir_handle = @opendir($path);
        if ($dir_handle === false) 
            return;
        while (($filename = readdir($dir_handle)) !== false) 
        {
            if ($filename == '.' || $filename == '..' || is_file("{$path}/{$filename}") == false)
            {
                continue;
            }
            unlink("{$path}/{$filename}");
        }
    }
    
    private function getFirstFileContentsInDirectory($path)
    {
        $results = "";
        $dir_handle = opendir($path);
        if ($dir_handle === false)
            return "";
        while (($filename = readdir($dir_handle)) !== false)
        {
            if ($filename == '.' || $filename == '..')
                continue;
            else
               return file_get_contents("{$path}/{$filename}");
        }
        return $results;
    }
    private function checkForHistoryRecords($module_dir)
    {
        $parth = "custom/history/modules/$module_dir/metadata";
        $dir_handle = @opendir($parth);
        if ($dir_handle === false) 
            return FALSE;
        $found = FALSE;
        while (($filename = readdir($dir_handle)) !== false) 
        {
            if ($filename == '.' || $filename == '..')
                continue;
            else 
                return TRUE;
        }
        return $found;
    }
}
?>