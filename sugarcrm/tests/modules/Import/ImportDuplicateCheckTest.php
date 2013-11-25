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

require_once 'modules/Import/ImportDuplicateCheck.php';

class ImportDuplicateCheckTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp() 
    {
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $app_strings = array();
        require('include/language/en_us.lang.php');
        $GLOBALS['app_strings'] = $app_strings;
    }
    
    public function tearDown() 
    {
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['app_strings']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }
    
    public function testGetDuplicateCheckIndexesWithEmail()
    {
        $focus = BeanFactory::getBean('Contacts');
        
        $idc     = new ImportDuplicateCheck($focus);
        $indexes = $idc->getDuplicateCheckIndexes();
        
        foreach ($focus->getIndices() as $key => $index) {
            if ($key != 'id' && $key != 'date_modified') {
                $this->assertTrue(isset($indexes[$index['name']]), "{$index['name']} should be in the list");
            }
        }
        
        $this->assertTrue(isset($indexes['special_idx_email1']));
        $this->assertTrue(isset($indexes['special_idx_email2']));
    }
    
    public function testGetDuplicateCheckIndexesNoEmail()
    {
        $focus = BeanFactory::getBean('Calls');
        
        $idc     = new ImportDuplicateCheck($focus);
        $indexes = $idc->getDuplicateCheckIndexes();
        
        foreach ($focus->getIndices() as $key => $index) {
            if ($key != 'id' && $key != 'date_modified' && !in_array('date_start', $index['fields'])) {
                $this->assertTrue(isset($indexes[$index['name']]), "{$index['name']} should be in the list");
            }
        }
        
        $this->assertFalse(isset($indexes['special_idx_email1']));
        $this->assertFalse(isset($indexes['special_idx_email2']));
    }
    
    public function testIsADuplicateRecord()
    {
        $last_name = 'FooBar'.date("YmdHis");
        
        $focus = BeanFactory::getBean('Contacts');
        $focus->last_name = $last_name;
        $id = $focus->save(false);
        
        $focus = BeanFactory::getBean('Contacts');
        $focus->last_name = $last_name;
        
        $idc = new ImportDuplicateCheck($focus);
        
        $this->assertTrue($idc->isADuplicateRecord(array('idx_contacts_del_last::last_name')));
        
        $focus->mark_deleted($id);
    }
    
    public function testIsADuplicateRecordEmail()
    {
        $email = date("YmdHis").'@foobar.com';
        
        $focus = BeanFactory::getBean('Contacts');
        $focus->email1 = $email;
        $id = $focus->save(false);
        
        $focus = BeanFactory::getBean('Contacts');
        $focus->email1 = $email;
        
        $idc = new ImportDuplicateCheck($focus);
        
        $this->assertTrue($idc->isADuplicateRecord(array('special_idx_email1')));
        
        $focus->mark_deleted($id);
    }
    
    public function testIsADuplicateRecordNotFound()
    {
        $last_name = 'BadFooBar'.date("YmdHis");
        
        $focus = BeanFactory::getBean('Contacts');
        $focus->last_name = $last_name;
        
        $idc = new ImportDuplicateCheck($focus);
        
        $this->assertFalse($idc->isADuplicateRecord(array('idx_contacts_del_last::'.$last_name)));
    }
    
    public function testIsADuplicateRecordEmailNotFound()
    {
        $email = date("YmdHis").'@badfoobar.com';
        
        $focus = BeanFactory::getBean('Contacts');
        $focus->email1 = $email;
        
        $idc = new ImportDuplicateCheck($focus);
        
        $this->assertFalse($idc->isADuplicateRecord(array('special_idx_email1')));
    }

    //make sure exclusion array is respected when displaying the list of available indexes for dupe checking
    public function testExcludeIndexesFromDupeCheck()
    {
        //create the bean to test on
        $focus = BeanFactory::getBean('Contacts');

        //create the importDuplicateCheck object and get the list of duplicateCheckIndexes
        $idc = new ImportDuplicateCheck($focus);

        //get the list of importable indexes
        $indexes = $import_indexes = $focus->getIndices();


        //grab any custom indexes if they exist
        if($focus->hasCustomFields()){
            $custmIndexes = $focus->db->helper->get_indices($focus->table_name.'_cstm');
            $indexes = array_merge($custmIndexes,$indexes);
        }

        //get list indexes to be displayed
        $dupe_check_indexes = $idc->getDuplicateCheckIndexedFiles();

        //Make sure that the indexes used for dupe checking honors the exclusion array.  At a minimum, all beans will have
        //their id and teamset indexes excluded.
        $this->assertTrue(count($indexes) > count($dupe_check_indexes), 'Indexes specified for exclusion are not getting excluded from getDuplicateCheckIndexedFiles()');
    }


    //make sure only selected indexes are checked for dupes
    public function testCompareOnlySelectedIndexesFromDupeCheck()
    {
        //create a bean, values, populate and save
        $focus = BeanFactory::getBean('Contacts');
        $focus->first_name = 'first '.date("YmdHis");
        $focus->last_name = 'last '.date("YmdHis");
        $focus->assigned_user_id = '1';
        $focus->save();
        //because of fix 51264, stored beans can't be duplicates
        $focus->id = null;

        //create the importDuplicateCheck object and get the list of duplicateCheckIndexes
        $idc = new ImportDuplicateCheck($focus);

        //we are going to test agains the first name, last name, full name, and assigned to indexes
        //to prove that only selected indexes are being used.

        //lets do a straight dupe check with the same bean on first name, should return true
        $this->assertTrue($idc->isADuplicateRecord(array('idx_cont_last_first::first_name')),'simulated check against first name index (idx_cont_last_first::first_name) failed  (returned false instead of true).');

        //now lets test on full name index should also return true
        $this->assertTrue($idc->isADuplicateRecord(array('full_name::full_name')),'first simulated check against full name index (full_name::full_name) failed (returned false instead of true).  This check means BOTH first AND last name must match.');

        //now lets remove the first name and redo the check, should return false
        $focus->first_name = '';
        $idc = new ImportDuplicateCheck($focus);
        $this->assertFalse($idc->isADuplicateRecord(array('idx_cont_last_first::first_name')),'simulated check against first name index (idx_cont_last_first::first_name) failed (returned true instead of false).  This is wrong because we removed the first name so there should be no match.');

        //lets retest on full name index should return false now as first AND last do not match the original
        $this->assertFalse($idc->isADuplicateRecord(array('full_name::full_name')),'second simulated check against full name index (full_name::full_name) failed (returned true instead of false).  This check means BOTH first AND last name must match and is wrong because we removed the first name so there should be no match.');

        //now lets rename the contact and test on assigned user, should return true
        $focus->first_name = 'first '.date("YmdHis");
        $focus->last_name = 'last '.date("YmdHis");
        $idc = new ImportDuplicateCheck($focus);
        $this->assertTrue($idc->isADuplicateRecord(array('idx_del_id_user::assigned_user_id')),'simulated check against assigned user index (idx_del_id_user::assigned_user_id) failed (returned false instead of true).  This is wrong because we have not changed this field and it should remain a duplicate');

        //we're done, lets delete the focus bean now
        $focus->mark_deleted($focus->id);

    }



}
