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

require_once('data/SugarBean.php');
require_once('modules/Contacts/Contact.php');
require_once('include/SubPanel/SubPanel.php');
require_once('include/SubPanel/SubPanelDefinitions.php');

/**
 * @itr 27836
 */
class ITR27836Test extends Sugar_PHPUnit_Framework_TestCase
{   	
    protected $bean;

	public function setUp()
	{
	    global $moduleList, $beanList, $beanFiles;
        require('include/modules.php');
	    $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->bean = new Contact();
	}

	public function tearDown()
	{
		SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);

  		require_once('ModuleInstall/ModuleInstaller.php');
  		$moduleInstaller = new ModuleInstaller();
  		$moduleInstaller->silent = true; // make sure that the ModuleInstaller->log() function doesn't echo while rebuilding the layoutdefs
  		$moduleInstaller->rebuild_layoutdefs();
	}


    public function subpanelProvider()
    {
        return array(
            //Hidden set to true

            array(
                'data' => array(
                    'testpanel' => array(
                        'order' => 20,
                        'sort_order' => 'desc',
                        'sort_by' => 'date_entered',
                        'type' => 'collection',
                        'top_buttons' => array(),
                    ),
                    'default_hidden' => true,
                    'subpanel_name' => 'history',
                    'module' => $this->bean->module_dir
                ),
            ),

            //Hidden set to false
            array
            (
                'data' => array(
                    'testpanel' => array(
                        'order' => 20,
                        'sort_order' => 'desc',
                        'sort_by' => 'date_entered',
                        'type' => 'collection',
                        'top_buttons' => array(),
                    ),
                    'default_hidden' => false,
                    'subpanel_name' => 'history',
                    'module' => $this->bean->module_dir
                ),
            ),

            //Hidden not set
            array(
                'data' => array(
                    'testpanel' => array(
                        'order' => 20,
                        'sort_order' => 'desc',
                        'sort_by' => 'date_entered',
                        'type' => 'collection',
                        'top_buttons' => array(),
                    ),
                    'subpanel_name' => 'history',
                    'module' => $this->bean->module_dir
                ),
            ),
        );
    }
    
    /**
     * testSubpanelDisplay
     *
     * @dataProvider subpanelProvider
     */
    public function testSubPanelDisplay($subpanel)
    {
        $subpanel_def = new aSubPanel("testpanel", $subpanel, $this->bean);

        if(isset($subpanel['default_hidden']) && $subpanel['default_hidden'] === true)
        {
            $this->assertTrue($subpanel_def->isDefaultHidden());
        } else {
            $this->assertFalse($subpanel_def->isDefaultHidden());
        }
    }

}
