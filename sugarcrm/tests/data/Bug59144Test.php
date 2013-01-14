<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * @ticket 59144
 */
class Bug59144Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Lead
     */
    private $lead;

    /**
     * @var Call
     */
    private $call;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        $this->lead = SugarTestLeadUtilities::createLead();
        $this->call = SugarTestCallUtilities::createCall();

        $this->lead->load_relationship('oldcalls');
        $this->lead->oldcalls->add($this->call);
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    public function tearDown()
    {
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestCallUtilities::removeAllCreatedCalls();

        SugarTestHelper::tearDown();
    }

    public function testQueryIsNotBroken()
    {
        $lead = new Lead();
        $lead->retrieve($this->lead->id);
        $lead->load_relationship('oldcalls');
        $calls = $lead->oldcalls->getBeans(
            array(
                'enforce_teams' => true,
            )
        );

        $this->assertInternalType('array', $calls);
        $this->assertEquals(1, count($calls));

        $call = array_shift($calls);
        $this->assertEquals($this->call->id, $call->id);
    }
}
