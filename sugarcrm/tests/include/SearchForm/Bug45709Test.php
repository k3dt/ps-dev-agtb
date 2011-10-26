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

require_once "modules/Tasks/Task.php";
require_once "modules/Contacts/Contact.php";
require_once "include/SearchForm/SearchForm2.php";

class Bug45709Test extends Sugar_PHPUnit_Framework_TestCase
{
	var $task = null;
	var $contact = null;
	var $requestArray = null;
	var $searchForm = null;

    public function setUp()
    {
		$GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
		$this->contact = SugarTestContactUtilities::createContact();
    	$this->task =SugarTestTaskUtilities::createTask();
    	$this->task->contact_id = $this->contact->id;
    	$this->task->save();
    }

    public function tearDown()
    {

        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestTaskUtilities::removeAllCreatedTasks();
        unset($GLOBALS['current_user']);
    }

    /**
     * @ticket 45709
     */
    public function testGenerateSearchWhereForFieldsWhenFullContactNameGiven()
    {
        //test GenerateSearchWhere for fields that have db_concat_fields set in vardefs
        //Contact in advanced search panel in Tasks module is one of those

    	//array to simulate REQUEST object
    	$this->requestArray['module'] = 'Tasks';
    	$this->requestArray['action'] = 'index';
    	$this->requestArray['searchFormTab'] = 'advanced_search';
    	$this->requestArray['contact_name_advanced'] = $this->contact->first_name. " ". $this->contact->last_name; //value of a contact name field set in REQUEST object
    	$this->requestArray['query'] = 'true';


    	$this->searchForm = new SearchForm($this->task,'Tasks');

    	require 'modules/Tasks/vardefs.php';
    	require 'modules/Tasks/metadata/SearchFields.php';
    	require 'modules/Tasks/metadata/searchdefs.php';
        $this->searchForm->searchFields = $searchFields[$this->searchForm->module];
        $this->searchForm->searchdefs = $searchdefs[$this->searchForm->module];
        $this->searchForm->fieldDefs = $this->task->getFieldDefinitions();
    	$this->searchForm->populateFromArray($this->requestArray,'advanced_search',false);
    	$whereArray = $this->searchForm->generateSearchWhere(true, $this->task->module_dir);
    	$test_query = "SELECT id FROM contacts WHERE " . $whereArray[0];
    	$result = $GLOBALS['db']->query($test_query);
    	$row = $GLOBALS['db']->fetchByAssoc($result);

    	$this->assertEquals($this->contact->id, $row['id'], "Didn't find the correct contact id");

    	$result2 = $GLOBALS['db']->query("SELECT * FROM tasks WHERE tasks.contact_id='{$this->task->contact_id}'");
        $row2 = $GLOBALS['db']->fetchByAssoc($result2);

        $this->assertEquals($this->task->id, $row2['id'], "Couldn't find the expected related task");
    }
}
