<?php
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
//FILE SUGARCRM flav=PRO only

require_once('modules/Administration/views/view.configureshortcutbar.php');
/**
 * Bug #57703
 * @ticket 57703
 */
class Bug57703Test extends Sugar_PHPUnit_Framework_TestCase
{
	public function setUp()
	{
        parent::setUp();
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('current_user');
    }

	public function tearDown()
	{
        parent::tearDown();

        SugarTestHelper::tearDown();
    }

    /**
    * @group 57703
    */
    public function testCheckEnabledModules()
    {
        SugarTestHelper::setUp('moduleList');
        $moduleList[] = array('module'=>'PdfManager');
        $obj = new ViewConfigureshortcutbarMock();
        $results = $obj->filterModules($moduleList);

        $this->assertEquals(0, count($results), 'Should return empty array');
    }
}

/**
 * Mock class
 */
class ViewConfigureshortcutbarMock extends ViewConfigureshortcutbar
{
    public function filterModules($moduleList)
    {
        return parent::filterModules($moduleList);
    }
}
?>
