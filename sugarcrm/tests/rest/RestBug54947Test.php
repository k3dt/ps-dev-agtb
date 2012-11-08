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

class RestBug54947Test extends RestTestBase {
    public $createdFiles = array();

    public function tearDown()
    {
        // Cleanup
        foreach($this->createdFiles as $file)
        {
        	if (is_file($file))
        		SugarAutoLoader::unlink($file, true);
        }

        parent::tearDown();
    }
    /**
     * @group rest
     */
    public function testModuleNameSingular() {
        $restReply = $this->_restCall('metadata?type_filter=mod_strings&platform=mobile');
        foreach (SugarAutoLoader::existingCustom('include/MVC/Controller/wireless_module_registry.php') as $file) {
            require $file;
        }

        // $wireless_module_registry is defined in the file loaded above
        $enabledMobile = array_keys($wireless_module_registry);
        
        foreach($enabledMobile AS $module) {
            if(isset($restReply['reply']['mod_strings'][$module])) {
                $this->assertTrue(array_key_exists('LBL_MODULE_NAME_SINGULAR', $restReply['reply']['mod_strings'][$module]), "{$module} didn't have LBL_MODULE_NAME_SINGULAR it has: " . print_r($restReply['reply']['mod_strings'][$module], true));
            }
        }
    }

}

