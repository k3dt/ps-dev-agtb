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
 
require_once('include/SugarFields/Fields/Bool/SugarFieldBool.php');
require_once('include/SugarFields/SugarFieldHandler.php');

class SugarFieldBoolTest extends Sugar_PHPUnit_Framework_TestCase
{
	public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        $this->meeting = BeanFactory::newBean('Meetings');
        $this->meeting->name = "Awesome Test Meeting " . create_guid();
        $this->meeting->reminder_time = 500;
        $this->meeting->email_reminder_time = 1;
        $id = $this->meeting->save();
        // need to reload the bean
        $this->meeting = new Meeting();
        $this->meeting->retrieve($id);

        $this->sf = SugarFieldHandler::getSugarField('bool');

	}

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        $GLOBALS['db']->query("DELETE FROM meetings WHERE id = '{$this->meeting->id}'");
        unset($this->meeting);
    }
    
	public function testTrueBoolFieldFormatting() {
        $data = array();
        $this->sf->apiFormatField($data, $this->meeting, array(), 'reminder_checked',array());
        
        $this->assertTrue($data['reminder_checked']);

        $this->sf->apiFormatField($data, $this->meeting, array(), 'email_reminder_checked',array());
        $this->assertTrue($data['reminder_checked']);
    }
    public function testFalseboolFieldFormatting() {
        // make'em false
        $this->meeting->reminder_time = -1;
        $this->meeting->email_reminder_time = -1;
        $this->meeting->reminder_checked = false;
        $this->meeting->email_reminder_checked = false;

        $data = array();
        $this->sf->apiFormatField($data, $this->meeting, array(), 'reminder_checked',array());
        
        $this->assertFalse($data['reminder_checked']);

        $this->sf->apiFormatField($data, $this->meeting, array(), 'email_reminder_checked',array());
        $this->assertFalse($data['email_reminder_checked']);

    }

}