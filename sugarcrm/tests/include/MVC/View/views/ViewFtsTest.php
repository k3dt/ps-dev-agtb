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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


require_once('include/MVC/View/views/view.fts.php');

class ViewFtsTest extends Sugar_PHPUnit_Framework_TestCase
{

    public function testTranslateModulesList()
    {
        $view = new ViewFtsStub();
        $modules = array('Accounts', 'Bugs');
        $results = $view->translateModulesList($modules);
        $match = array(0=>array('module'=>'Accounts', 'label'=>'Accounts'),
                                           1=>array('module'=>'Bugs', 'label'=>'Bugs'));
        // Don't use array_diff, it doesn't compare in depth
        $this->assertEquals($match, $results, 'unexpected results');
    }

    public function testSendOutput()
    {
        $view = new ViewFtsStub();
        $testString = 'test string';
        $result = $view->sendOutput($testString, true, true);
        $expected = json_encode(array('results' => $testString));
        $this->assertEquals($expected, $result, "string not encoded correctly");
    }
}

class ViewFtsStub extends ViewFts
{
    public function translateModulesList($module)
    {
        return parent::translateModulesList($module);
    }

    public function sendOutput($contents, $return = false, $encode = false)
    {
        return parent::sendOutput($contents, $return, $encode);
    }
}