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

require_once('include/MVC/View/views/view.classic.php');

class ViewClassicTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function testConstructor()
	{
        $view = new ViewClassic();

        $this->assertEquals('',$view->type);
	}

	public function testDisplayWithClassicView()
	{
	    $view = $this->getMock('ViewClassic',array('includeClassicFile'));

	    $view->module = 'testmodule'.mt_rand();
	    $view->action = 'testaction'.mt_rand();

	    sugar_mkdir("modules/{$view->module}",null,true);
	    SugarAutoLoader::touch("modules/{$view->module}/{$view->action}.php", false);

	    $return = $view->display();

	    rmdir_recursive("modules/{$view->module}");
	    SugarAutoLoader::delFromMap("modules/{$view->module}");

	    $this->assertTrue($return);
	}

	public function testDisplayWithClassicCustomView()
	{
	    $view = $this->getMock('ViewClassic',array('includeClassicFile'));

	    $view->module = 'testmodule'.mt_rand();
	    $view->action = 'testaction'.mt_rand();

	    sugar_mkdir("custom/modules/{$view->module}",null,true);
	    SugarAutoLoader::touch("custom/modules/{$view->module}/{$view->action}.php", false);

	    $return = $view->display();

	    rmdir_recursive("custom/modules/{$view->module}");
	    SugarAutoLoader::delFromMap("custom/modules/{$view->module}");

	    $this->assertTrue($return);
	}

	public function testDisplayWithNoClassicView()
	{
	    $view = $this->getMock('ViewClassic',array('includeClassicFile'));

	    $view->module = 'testmodule'.mt_rand();
	    $view->action = 'testaction'.mt_rand();

	    $this->assertFalse($view->display());
	}
}