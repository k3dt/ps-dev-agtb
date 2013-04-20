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

require_once('tests/rest/RestTestBase.php');

class RestRegisterLeadTest extends RestTestBase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        if (isset($this->lead_id)) {
            $GLOBALS['db']->query("DELETE FROM leads WHERE id = '{$this->lead_id}'");
        }
        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testCreate()
    {
        $leadProps = array(
            'first_name' => 'UNIT TEST FIRST',
            'last_name' => 'UNIT TEST LAST',
            'email' => array(
                array(
                    'email_address' => 'UT@test.com'
                ))
        );
        $restReply = $this->_restCall("Leads/register",
            json_encode($leadProps),
            'POST');

        $this->assertTrue(isset($restReply['reply']),
            "Lead was not created (or if it was, the ID was not returned)");


        $nlead = new Lead();
        $nlead->id = $restReply['reply'];
        $nlead->retrieve();
        $this->assertEquals($leadProps['first_name'],
            $nlead->first_name,
            "Submitted Lead and Lead Bean Do Not Match.");
        $this->assertEquals("UT@test.com",
            $nlead->email1,
            "Submitted Lead and Lead Bean Do Not Match.");
    }

    /**
     * @group rest
     */
    public function testCreateEmpty()
    {
        $leadProps = array(
        );
        $restReply = $this->_restCall("Leads/register",
            json_encode($leadProps),
            'POST');
        $this->assertEquals($restReply['info']['http_code'], 412);
    }

}