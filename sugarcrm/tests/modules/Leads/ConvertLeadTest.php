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

require_once('tests/SugarTestViewConvertLeadUtilities.php');
require_once 'modules/Leads/views/view.convertlead.php';
require_once 'tests/SugarTestViewConvertLeadUtilities.php';


class ConvertLeadTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('files');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::saveFile('custom/modules/Leads/metadata/editviewdefs.php');
        @SugarAutoLoader::unlink('custom/modules/Leads/metadata/editviewdefs.php');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestStudioUtilities::removeAllCreatedFields();
        if(!empty($this->relation_id)) {
            SugarTestMeetingUtilities::deleteMeetingLeadRelation($this->relation_id);
        }
        SugarTestMeetingUtilities::removeMeetingContacts();
        SugarTestMeetingUtilities::removeMeetingUsers();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        unset($GLOBALS['app']->controller);
        unset($_REQUEST['module']);
        unset($_REQUEST['action']);
        unset($_REQUEST['record']);
        if(!empty($this->meeting) && !empty($this->contact)) {
            $GLOBALS['db']->query("delete from meetings_contacts where meeting_id='{$this->meeting->id}' and contact_id= '{$this->contact->id}'");
        }
        if(!empty($this->contact)) {
            $GLOBALS['db']->query("delete from meetings where parent_id='{$this->contact->id}' and parent_type= 'Contacts'");
        }
        if(!empty($this->contact_id)) {
            $GLOBALS['db']->query("delete from meetings where parent_id='{$this->contact_id}' and parent_type= 'Contacts'");
        }
        if(!empty($this->lead)) {
            $GLOBALS['db']->query("delete from meetings where parent_id='{$this->lead->id}' and parent_type= 'Leads'");
        }
        if(!empty($this->new_meeting_id) && !empty($this->contact)) {
            $GLOBALS['db']->query("delete from meetings_contacts where meeting_id='{$this->new_meeting_id}' and contact_id= '{$this->contact->id}'");
        }
        if(!empty($this->new_meeting_id) && !empty($this->contact_id)) {
            $GLOBALS['db']->query("delete from meetings_contacts where meeting_id='{$this->new_meeting_id}' and contact_id= '{$this->contact_id}'");
        }
    }

    /**
    * @group bug39787
    */
    public function testOpportunityNameValueFilled()
    {
        $lead = SugarTestLeadUtilities::createLead();
        $lead->opportunity_name = 'SBizzle Dollar Store';
        $lead->save();

        $_REQUEST['module'] = 'Leads';
        $_REQUEST['action'] = 'ConvertLead';
        $_REQUEST['record'] = $lead->id;

        // Check that the opportunity name doesn't get populated when it's not in the Leads editview layout
        require_once('include/MVC/Controller/ControllerFactory.php');
        require_once('include/MVC/View/ViewFactory.php');
        $GLOBALS['app']->controller = ControllerFactory::getController($_REQUEST['module']);
        ob_start();
        $GLOBALS['app']->controller->execute();
        $output = ob_get_clean();

        $matches_one = array();
        $pattern = '/SBizzle Dollar Store/';
        preg_match($pattern, $output, $matches_one);
        $this->assertTrue(count($matches_one) == 0, "Opportunity name got carried over to the Convert Leads page when it shouldn't have.");

        // Add the opportunity_name to the Leads EditView
        SugarTestStudioUtilities::addFieldToLayout('Leads', 'editview', 'opportunity_name');

        // Check that the opportunity name now DOES get populated now that it's in the Leads editview layout
        ob_start();
        $GLOBALS['app']->controller = ControllerFactory::getController($_REQUEST['module']);
        $GLOBALS['app']->controller->execute();
        $output = ob_get_clean();
        $matches_two = array();
        $pattern = '/SBizzle Dollar Store/';
        preg_match($pattern, $output, $matches_two);
        $this->assertTrue(count($matches_two) > 0, "Opportunity name did not carry over to the Convert Leads page when it should have.");
    }

    /**
     * @group bug44033
     */
    public function testActivityMove() {
        // init
        $lead = SugarTestLeadUtilities::createLead();
        $this->contact = $contact = SugarTestContactUtilities::createContact();
        $meeting = SugarTestMeetingUtilities::createMeeting();
        SugarTestMeetingUtilities::addMeetingParent($meeting->id, $lead->id);
        $relation_id = SugarTestMeetingUtilities::addMeetingLeadRelation($meeting->id, $lead->id);
        $_REQUEST['record'] = $lead->id;

        // refresh the meeting to include parent_id and parent_type
        $meeting_id = $meeting->id;
        $this->meeting = $meeting = new Meeting();
        $meeting->retrieve($meeting_id);

        // action: move meeting from lead to contact
        $convertObj = new TestViewConvertLead();
        $convertObj->moveActivityWrapper($meeting, $contact);

        // verification 1, parent id should be contact id
        $this->assertTrue($meeting->parent_id == $contact->id, 'Meeting parent id is not converted to contact id.');

        // verification 2, parent type should be "Contacts"
        $this->assertTrue($meeting->parent_type == 'Contacts', 'Meeting parent type is not converted to Contacts.');

        // verification 3, record should be deleted from meetings_leads table
        $sql = "select id from meetings_leads where meeting_id='{$meeting->id}' and lead_id='{$lead->id}' and deleted=0";
        $result = $GLOBALS['db']->query($sql);
        $row = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertFalse($row, "Meeting-Lead relationship is not removed.");

        // verification 4, record should be added to meetings_contacts table
        $sql = "select id from meetings_contacts where meeting_id='{$meeting->id}' and contact_id='{$contact->id}' and deleted=0";
        $result = $GLOBALS['db']->query($sql);
        $row = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertFalse(empty($row), "Meeting-Contact relationship is not added.");

        // clean up
    }


    public function testActivityCopyWithParent() {
        // lets the run the activity copy again, only this time we pass in a parent account
        $this->lead = $lead = SugarTestLeadUtilities::createLead();
        $this->contact = $contact = SugarTestContactUtilities::createContact();
        $meeting = SugarTestMeetingUtilities::createMeeting();
        $account = SugarTestAccountUtilities::createAccount();
        SugarTestMeetingUtilities::addMeetingParent($meeting->id, $lead->id);
        $this->relation_id = SugarTestMeetingUtilities::addMeetingLeadRelation($meeting->id, $lead->id);
        $_REQUEST['record'] = $lead->id;

        // refresh the meeting to include parent_id and parent_type
        $meeting_id = $meeting->id;
        $this->meeting = $meeting = new Meeting();
        $meeting->retrieve($meeting_id);

        // action: copy meeting from lead to contact
        $convertObj = new TestViewConvertLead();
        $convertObj->copyActivityWrapper($meeting, $contact, array('id'=>$account->id,'type'=>'Accounts'));


        // 2a a newly created meeting with no parent info passed in, so parent id and type are empty
        //parent type=Contatcs and parent_id=$contact->id
        //$sql = "select id from meetings where parent_id='{$contact->id}' and parent_type= 'Contacts' and deleted=0";
        $sql = "select id, parent_id from meetings where name = '{$meeting->name}'";
        $result = $GLOBALS['db']->query($sql);
        while ($row = $GLOBALS['db']->fetchByAssoc($result)){
            //skip if this is the original message
            if($row['id'] == $meeting_id){
                continue;
            }

            $this->assertEquals($row['parent_id'], $account->id, 'parent id of meeting should be equal to passed in account id: '.$account->id);
        }

    }


    public function testActivityCopyWithNoParent() {
        // init
        $this->lead = $lead = SugarTestLeadUtilities::createLead();
        $this->contact = $contact = SugarTestContactUtilities::createContact();
        $meeting = SugarTestMeetingUtilities::createMeeting();
        SugarTestMeetingUtilities::addMeetingParent($meeting->id, $lead->id);
        $this->relation_id = $relation_id = SugarTestMeetingUtilities::addMeetingLeadRelation($meeting->id, $lead->id);
        $_REQUEST['record'] = $lead->id;

        // refresh the meeting to include parent_id and parent_type
        $meeting_id = $meeting->id;
        $this->meeting = $meeting = new Meeting();
        $meeting->retrieve($meeting_id);

        // action: copy meeting from lead to contact
        $convertObj = new TestViewConvertLead();
        $convertObj->copyActivityWrapper($meeting, $contact);

        // 1. the original meeting should still have the same parent_type and parent_id
        $meeting->retrieve($meeting_id);
        $this->assertEquals('Leads', $meeting->parent_type, 'parent_type of the original meeting was changed from Leads to '.$meeting->parent_type);
        $this->assertEquals($lead->id, $meeting->parent_id, 'parent_id of the original meeting was changed from '.$lead->id.' to '.$meeting->parent_id);

        // 2. a newly created meeting with no parent info passed in, so parent id and type are empty
        $new_meeting_id = '';
        $sql = "select id, parent_id from meetings where name = '{$meeting->name}'";
              $result = $GLOBALS['db']->query($sql);
              while ($row = $GLOBALS['db']->fetchByAssoc($result)){
                  //skip if this is the original message
                  if($row['id'] == $meeting_id){
                      continue;
                  }
                  $new_meeting_id = $row['id'];
                  $this->assertEmpty($row['parent_id'],'parent id of meeting should be empty as no parent was sent in ');
              }
              $this->new_meeting_id = $new_meeting_id;


        // 3. record should not be deleted from meetings_leads table
        $sql = "select id from meetings_leads where meeting_id='{$meeting->id}' and lead_id='{$lead->id}' and deleted=0";
        $result = $GLOBALS['db']->query($sql);
        $row = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertNotNull($row, "Meeting-Lead relationship was removed.");

        // 4. new meeting record should be added to meetings_contacts table
        $sql = "select id from meetings_contacts where meeting_id='{$new_meeting_id}' and contact_id='{$contact->id}' and deleted=0";
        $result = $GLOBALS['db']->query($sql);
        $row = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertFalse(empty($row), "Meeting-Contact relationship has not been added.");
    }

    /**
     * @outputBuffering enabled
     */
    public function testConversionAndMoveActivities() {
        global $sugar_config;

        // init
        $lead = SugarTestLeadUtilities::createLead();
        $account = SugarTestAccountUtilities::createAccount();
        $meeting = SugarTestMeetingUtilities::createMeeting();
        SugarTestMeetingUtilities::addMeetingParent($meeting->id, $lead->id);
        $this->relation_id = $relation_id = SugarTestMeetingUtilities::addMeetingLeadRelation($meeting->id, $lead->id);
        $_REQUEST['record'] = $lead->id;

        // set the request/post parameters before converting the lead
        $_REQUEST['module'] = 'Leads';
        $_REQUEST['action'] = 'ConvertLead';
        $_REQUEST['record'] = $lead->id;
        $_REQUEST['handle'] = 'save';
        $_REQUEST['selectedAccount'] = $account->id;
        $sugar_config['lead_conv_activity_opt'] = 'move';
        $_POST['lead_conv_ac_op_sel'] = 'Contacts';

        // call display to trigger conversion
        $vc = new ViewConvertLead();
        $vc->display();

        // refresh meeting
        $meeting_id = $meeting->id;
        $this->meeting = $meeting = new Meeting();
        $meeting->retrieve($meeting_id);

        // refresh lead
        $lead_id = $lead->id;
        $this->lead = $lead = new Lead();
        $lead->retrieve($lead_id);

        // retrieve the new contact id from the conversion
        $this->contact_id = $contact_id = $lead->contact_id;

        // 1. Lead's contact_id should not be null
        $this->assertNotNull($contact_id, 'Lead has null contact id after conversion.');

        // 2. Lead status should be 'Converted'
        $this->assertEquals('Converted', $lead->status, "Lead atatus should be 'Converted'.");

        // 3. new parent_type should be Contacts
        $this->assertEquals('Contacts', $meeting->parent_type, 'Meeting parent type has not been set to Contacts');

        // 4. new parent_id should be contact id
        $this->assertEquals($contact_id, $meeting->parent_id, 'Meeting parent id has not been set to contact id.');

        // 5. record should be deleted from meetings_leads table
        $sql = "select id from meetings_leads where meeting_id='{$meeting->id}' and lead_id='{$lead->id}' and deleted=0";
        $result = $GLOBALS['db']->query($sql);
        $row = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertFalse($row, "Meeting-Lead relationship is not removed.");

        // 6. record should be added to meetings_contacts table
        $sql = "select id from meetings_contacts where meeting_id='{$meeting->id}' and contact_id='{$contact_id}' and deleted=0";
        $result = $GLOBALS['db']->query($sql);
        $row = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertFalse(empty($row), "Meeting-Contact relationship is not added.");
    }

    /**
     * @outputBuffering enabled
     */
    public function testConversionAndCopyActivities() {
        global $sugar_config;

        // init
        $lead = SugarTestLeadUtilities::createLead();
        $account = SugarTestAccountUtilities::createAccount();
        $meeting = SugarTestMeetingUtilities::createMeeting();
        SugarTestMeetingUtilities::addMeetingParent($meeting->id, $lead->id);
        $this->relation_id = $relation_id = SugarTestMeetingUtilities::addMeetingLeadRelation($meeting->id, $lead->id);
        $_REQUEST['record'] = $lead->id;

        // set the request/post parameters before converting the lead
        $_REQUEST['module'] = 'Leads';
        $_REQUEST['action'] = 'ConvertLead';
        $_REQUEST['record'] = $lead->id;
        $_REQUEST['handle'] = 'save';
        $_REQUEST['selectedAccount'] = $account->id;
        $sugar_config['lead_conv_activity_opt'] = 'copy';
        $_POST['lead_conv_ac_op_sel'] = array('Contacts');

        // call display to trigger conversion
        $vc = new ViewConvertLead();
        $vc->display();

        // refresh meeting
        $meeting_id = $meeting->id;
        $this->meeting = $meeting = new Meeting();
        $meeting->retrieve($meeting_id);

        // refresh lead
        $lead_id = $lead->id;
        $this->lead = $lead = new Lead();
        $lead->retrieve($lead_id);

        // retrieve the new contact id from the conversion
        $this->contact_id = $contact_id = $lead->contact_id;

        // 1. Lead's contact_id should not be null
        $this->assertNotNull($contact_id, 'Lead has null contact id after conversion.');

        // 2. Lead status should be 'Converted'
        $this->assertEquals('Converted', $lead->status, "Lead atatus should be 'Converted'.");

        // 3. parent_type of the original meeting should be Leads
        $this->assertEquals('Leads', $meeting->parent_type, 'Meeting parent should be Leads');

        // 4. parent_id of the original meeting should be contact id
        $this->assertEquals($lead_id, $meeting->parent_id, 'Meeting parent id should be lead id.');

        // 5. record should NOT be deleted from meetings_leads table
        $sql = "select id from meetings_leads where meeting_id='{$meeting->id}' and lead_id='{$lead->id}' and deleted=0";
        $result = $GLOBALS['db']->query($sql);
        $row = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertFalse(empty($row), "Meeting-Lead relationship is removed.");

        // 6. record should be added to meetings_contacts table
        $sql = "select meeting_id from meetings_contacts where contact_id='{$contact_id}' and deleted=0";
        $result = $GLOBALS['db']->query($sql);
        $row = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertFalse(empty($row), "Meeting-Contact relationship is not added.");

        // 7. the parent_type of the new meeting should be empty
        $new_meeting_id = $row['meeting_id'];
        $sql = "select id, parent_type, parent_id from meetings where id='{$new_meeting_id}' and deleted=0";
        $result = $GLOBALS['db']->query($sql);
        $row = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertFalse(empty($row), "New meeting is not added for contact.");
        $this->assertEmpty($row['parent_type'], 'Parent type of the new meeting should be Empty');

        // 8. the parent_id of the new meeting should be contact id
        $this->assertEmpty($row['parent_id'], 'Parent id of the new meeting should be empty.');
    }

    /**
     * @outputBuffering enabled
     */
    public function testConversionAndDoNothing() {
        global $sugar_config;

        // init
        $lead = SugarTestLeadUtilities::createLead();
        $account = SugarTestAccountUtilities::createAccount();
        $meeting = SugarTestMeetingUtilities::createMeeting();
        SugarTestMeetingUtilities::addMeetingParent($meeting->id, $lead->id);
        $relation_id = SugarTestMeetingUtilities::addMeetingLeadRelation($meeting->id, $lead->id);
        $_REQUEST['record'] = $lead->id;

        // set the request/post parameters before converting the lead
        $_REQUEST['module'] = 'Leads';
        $_REQUEST['action'] = 'ConvertLead';
        $_REQUEST['record'] = $lead->id;
        $_REQUEST['handle'] = 'save';
        $_REQUEST['selectedAccount'] = $account->id;
        $sugar_config['lead_conv_activity_opt'] = 'none';

        // call display to trigger conversion
        $vc = new ViewConvertLead();
        $vc->display();

        // refresh meeting
        $meeting_id = $meeting->id;
        $this->meeting = $meeting = new Meeting();
        $meeting->retrieve($meeting_id);

        // refresh lead
        $lead_id = $lead->id;
        $this->lead = $lead = new Lead();
        $lead->retrieve($lead_id);

        // retrieve the new contact id from the conversion
        $this->contact_id = $contact_id = $lead->contact_id;

        // 1. Lead's contact_id should not be null
        $this->assertNotNull($contact_id, 'Lead has null contact id after conversion.');

        // 2. Lead status should be 'Converted'
        $this->assertEquals('Converted', $lead->status, "Lead atatus should be 'Converted'.");

        // 3. parent_type of the original meeting should be Leads
        $this->assertEquals('Leads', $meeting->parent_type, 'Meeting parent should be Leads');

        // 4. parent_id of the original meeting should be contact id
        $this->assertEquals($lead_id, $meeting->parent_id, 'Meeting parent id should be lead id.');

        // 5. record should NOT be deleted from meetings_leads table
        $sql = "select id from meetings_leads where meeting_id='{$meeting->id}' and lead_id='{$lead->id}' and deleted=0";
        $result = $GLOBALS['db']->query($sql);
        $row = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertFalse(empty($row), "Meeting-Lead relationship is removed.");

        // 6. record should NOT be added to meetings_contacts table
        $sql = "select meeting_id from meetings_contacts where contact_id='{$contact_id}' and deleted=0";
        $result = $GLOBALS['db']->query($sql);
        $row = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertFalse($row, "Meeting-Contact relationship should not be added.");
    }

    public function testMeetingsUsersRelationships()
    {
        global $current_user;

        $bean = SugarTestMeetingUtilities::createMeeting();
        $convert_lead = SugarTestViewConvertLeadUtilities::createViewConvertLead();

        if ($bean->object_name == "Meeting")
        {
            $convert_lead->setMeetingsUsersRelationship($bean);
        }

        $this->assertTrue(is_object($bean->users), "Relationship wasn't set.");
    }
}

class TestViewConvertLead extends ViewConvertLead
{
    public function moveActivityWrapper($activity, $bean) {
        parent::moveActivity($activity, $bean);
    }

    public function copyActivityWrapper($activity, $bean,$parent=array()) {
        parent::copyActivityAndRelateToBean($activity, $bean,$parent);
    }

    public function testMeetingsUsersRelationships()
    {
        global $current_user;

        $bean = SugarTestMeetingUtilities::createMeeting();
        $convert_lead = SugarTestViewConvertLeadUtilities::createViewConvertLead();

        if ($bean->object_name == "Meeting")
        {
            $convert_lead->setMeetingsUsersRelationship($bean);
        }

        $this->assertTrue(is_object($bean->users), "Relationship wasn't set.");
    }
}
