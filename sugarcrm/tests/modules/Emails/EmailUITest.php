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
 
require_once('modules/Emails/EmailUI.php');

class EmailUITest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_folders = null;
    
    public function setUp()
    {
        global $current_user;
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->_user;
        $this->eui = new EmailUIMock();

        $this->_folders = array();
		
		$beanList = array();
		$beanFiles = array();
		require('include/modules.php');
		$GLOBALS['beanList'] = $beanList;
		$GLOBALS['beanFiles'] = $beanFiles;
    }
    
    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM folders_subscriptions WHERE assigned_user_id='{$GLOBALS['current_user']->id}'");
        foreach ($this->_folders as $f) {
            $GLOBALS['db']->query("DELETE FROM folders_subscriptions WHERE folder_id='{$f}'");
            $GLOBALS['db']->query("DELETE FROM folders WHERE id='{$f}'");
        }
        
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
        $GLOBALS['db']->query("DELETE FROM folders_subscriptions WHERE assigned_user_id='{$this->_user->id}'");
            
        foreach ($this->_folders as $f) {
            $GLOBALS['db']->query("DELETE FROM folders_subscriptions WHERE folder_id='{$f}'");
            $GLOBALS['db']->query("DELETE FROM folders WHERE id='{$f}'");
        }
    }

    /**
     * Save a SugarFolder 
     */
    public function testSaveNewFolder()
    {
        $newFolderName = "UNIT_TEST";
        $rs = $this->eui->saveNewFolder($newFolderName,'Home',0);
        $newFolderID = $rs['id'];
        $this->_folders[] = $newFolderID;
        
        $sf = new SugarFolder();
        $sf->retrieve($newFolderID);
        $this->assertEquals($newFolderName, $sf->name);
        
    }
    
    /**
     * Save the user preference for list view order per IE account.
     *
     */
    public function testSaveListViewSortOrder()
    {
        $tmpId = create_guid();
        $folderName = "UNIT_TEST";
        $sortBy = 'last_name';
        $dir = "DESC";
        $rs = $this->eui->saveListViewSortOrder($tmpId,$folderName,$sortBy,$dir);
        
        //Check against the saved preferences.
        $prefs = unserialize($GLOBALS['current_user']->getPreference('folderSortOrder', 'Emails'));
        $this->assertEquals($sortBy, $prefs[$tmpId][$folderName]['current']['sort']);
        $this->assertEquals($dir, $prefs[$tmpId][$folderName]['current']['direction']);
        
        
    }
    public function testGetRelatedEmail()
    {
    	
    	$account = new Account();
    	$account->name = "emailTestAccount";
    	$account->save(false);
    	
    	$relatedBeanInfo = array('related_bean_id' => $account->id,  "related_bean_type" => "Accounts");
    	
    	//First pass should return a blank query as are no related items
    	$qArray = $this->eui->getRelatedEmail("LBL_DROPDOWN_LIST_ALL", array(), $relatedBeanInfo);
    	$this->assertEquals("", $qArray['query']);
        
    	//Now create a related Contact
    	$contact = new Contact();
    	$contact->name = "emailTestContact";
    	$contact->account_id = $account->id;
    	$contact->account_name = $account->name;
    	$contact->email1 = "test@test.com";
    	$contact->save(false);
    	
    	//Now we should get a result
        $qArray = $this->eui->getRelatedEmail("LBL_DROPDOWN_LIST_ALL", array(), $relatedBeanInfo);
        $r = $account->db->limitQuery($qArray['query'], 0, 25, true);
        $person = array();
        $a = $account->db->fetchByAssoc($r);
        $person['bean_id'] = $a['id'];
        $person['bean_module'] = $a['module'];
        $person['email'] = $a['email_address'];
        
        //Cleanup
    	$GLOBALS['db']->query("DELETE FROM accounts WHERE id= '{$account->id}'");
    	$GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$contact->id}'");
        
        $this->assertEquals("test@test.com", $person['email']);
    }
    
    /**
     * @ticket 29521
     */
    public function testLoadQuickCreateModules()
    {
        $qArray = $this->eui->_loadQuickCreateModules();

        $this->assertEquals(array('Bugs','Cases','Contacts', 'Leads', 'Tasks'), $qArray);
    }
    
    /**
     * @ticket 29521
     */
    public function testLoadCustomQuickCreateModulesCanMergeModules()
    {
        if (file_exists('custom/modules/Emails/metadata/qcmodulesdefs.php')) {
            copy('custom/modules/Emails/metadata/qcmodulesdefs.php','custom/modules/Emails/metadata/qcmodulesdefs.php.test.bak');
        }
        sugar_mkdir("custom/modules/Emails/metadata/",null,true);
        file_put_contents(
            'custom/modules/Emails/metadata/qcmodulesdefs.php',
            '<?php $QCModules[] = "Users"; ?>'
            );
        
        $qArray = $this->eui->_loadQuickCreateModules();

        if (file_exists('custom/modules/Emails/metadata/qcmodulesdefs.php.test.bak')) {
            copy('custom/modules/Emails/metadata/qcmodulesdefs.php.test.bak','custom/modules/Emails/metadata/qcmodulesdefs.php');
            unlink('custom/modules/Emails/metadata/qcmodulesdefs.php.test.bak');
        }
        else {
            unlink('custom/modules/Emails/metadata/qcmodulesdefs.php');
        }
        
        $this->assertEquals(array('Bugs','Cases','Contacts', 'Leads', 'Tasks', 'Users'), $qArray);
    }
    
    /**
     * @ticket 29521
     */
    public function testLoadQuickCreateModulesInvalidModule()
    {
        if (file_exists('custom/modules/Emails/metadata/qcmodulesdefs.php')) {
            copy('custom/modules/Emails/metadata/qcmodulesdefs.php','custom/modules/Emails/metadata/qcmodulesdefs.php.test.bak');
        }
        sugar_mkdir("custom/modules/Emails/metadata/",null,true);
        file_put_contents(
            'custom/modules/Emails/metadata/qcmodulesdefs.php',
            '<?php $QCModules[] = "EmailUIUnitTest"; ?>'
            );
        
        $qArray = $this->eui->_loadQuickCreateModules();

        if (file_exists('custom/modules/Emails/metadata/qcmodulesdefs.php.test.bak')) {
            copy('custom/modules/Emails/metadata/qcmodulesdefs.php.test.bak','custom/modules/Emails/metadata/qcmodulesdefs.php');
            unlink('custom/modules/Emails/metadata/qcmodulesdefs.php.test.bak');
        }
        else {
            unlink('custom/modules/Emails/metadata/qcmodulesdefs.php');
        }
        
        $this->assertEquals(array('Bugs','Cases','Contacts', 'Leads', 'Tasks'), $qArray);
    }
    
    /**
     * @ticket 29521
     */
    public function testLoadQuickCreateModulesCanOverrideDefaultModules()
    {
        if (file_exists('custom/modules/Emails/metadata/qcmodulesdefs.php')) {
            copy('custom/modules/Emails/metadata/qcmodulesdefs.php','custom/modules/Emails/metadata/qcmodulesdefs.php.test.bak');
        }
        sugar_mkdir("custom/modules/Emails/metadata/",null,true);
        file_put_contents(
            'custom/modules/Emails/metadata/qcmodulesdefs.php',
            '<?php $QCModules = array("Users"); ?>'
            );
        
        $qArray = $this->eui->_loadQuickCreateModules();

        if (file_exists('custom/modules/Emails/metadata/qcmodulesdefs.php.test.bak')) {
            copy('custom/modules/Emails/metadata/qcmodulesdefs.php.test.bak','custom/modules/Emails/metadata/qcmodulesdefs.php');
            unlink('custom/modules/Emails/metadata/qcmodulesdefs.php.test.bak');
        }
        else {
            unlink('custom/modules/Emails/metadata/qcmodulesdefs.php');
        }
        
        $this->assertEquals(array("Users"), $qArray);
    }
}

class EmailUIMock extends EmailUI
{
    public function _loadQuickCreateModules()
    {
        return parent::_loadQuickCreateModules();
    }
}