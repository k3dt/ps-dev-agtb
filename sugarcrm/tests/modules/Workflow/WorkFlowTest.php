<?php
//FILE SUGARCRM flav=pro ONLY
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
require_once 'include/controller/Controller.php';
require_once 'modules/WorkFlow/WorkFlow.php';
require_once 'modules/WorkFlowActions/WorkFlowAction.php';
require_once 'modules/WorkFlowTriggerShells/WorkFlowTriggerShell.php';


class WorkFlowTest extends Sugar_PHPUnit_Framework_TestCase
{
	protected $testWFName = "WFUnitTest";
	protected $testValue = "Workflow triggred!";
	protected $testAccName = "WF Test Account";

	public function setUp()
    {
    	$this->testWFName = "WFUnitTest" . mt_rand(); 
    	$this->testAccName = "WFTestAccount" . mt_rand(); 
    	$this->wf = new WorkFlow();
    	$this->wf->name = $this->testWFName;
    	$this->wf->base_module = "Accounts";
    	$this->wf->type = "Normal";
    	$this->wf->fire_order = "alerts_actions";
    	$this->wf->record_type = "All";
    	$this->wf->save();
	}

	public function tearDown()
	{
	    $this->wf->deleted = true;
	    $this->wf->cascade_delete($this->wf);
	    $sql = "DELETE FROM workflow WHERE id='{$this->wf->id}'";
        $GLOBALS['db']->query($sql);
	}

	public function testCreate_new_list_query()
    {
        $query = $this->wf->create_new_list_query("name", "workflow.name like '{$this->testWFName}%'");
        $result = $this->wf->db->query($query);
        $count = 0;
        while ( $row = $this->wf->db->fetchByAssoc($result) ) $count++;
        $this->assertEquals(1, $count);
    }

    /* Non-functional test.
    public function testWrite_workflow()
    {
        //Build the workflow components
    	echo ("Building workflow trigger...\n");
    	$trigger = new WorkFlowTriggerShell();
        $trigger->type = "trigger_record_change";
        $trigger->frame_type = "Primary";
        $trigger->rel_module_type = "any";
        $trigger->parent_id = $this->wf->id;
        $trigger->save();

        echo ("Building workflow Action Shell...\n");
        $actionShell = new WorkFlowActionShell();
        $actionShell->action_type = "update";
        $actionShell->rel_module_type = "all";
        $actionShell->parent_id = $this->wf->id;
        $actionShell->save();

        echo ("Building workflow Action...\n");
        $action = new WorkFlowAction();
        $action->field = "description";
        $action->value = $this->testValue;
        $action->set_type = "Basic";
        $action->parent_id = $actionShell->id;
        $action->save();

        echo ("Rebuilding workflow...\n");
        //Now build the logic hook and test it
        $this->wf->check_logic_hook_file();
        $this->wf->write_workflow();

        echo ("Creating a new Account...w\n");
        $acc = new Account();
        $acc->name = $this->testAccName;
        $acc->save();

        $this->assertEquals($this->testValue, $acc->description);
    }
    */
}

