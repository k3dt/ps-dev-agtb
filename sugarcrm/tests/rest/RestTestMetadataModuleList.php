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

class RestTestMetadataModuleList extends RestTestBase {
    public $oppTestPath = 'modules/Opportunities/metadata/portal/views/list.php';
    public function setUp()
    {
        //Create an anonymous user for login purposes/
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->_user;
        $this->_restLogin($this->_user->user_name,$this->_user->user_name);
    }
    
    public function tearDown()
    {
        $unitTestFiles = array($this->oppTestPath,
                               'custom/include/MVC/Controller/wireless_module_registry.php');
        foreach($unitTestFiles as $unitTestFile ) {
            if ( file_exists($unitTestFile) ) {
                // Ignore the warning on this, the file stat cache causes the file_exist to trigger even when it's not really there
                @unlink($this->oppTestPath);
            }
        }
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function testMetadataGetModuleListPortal() {
        $restReply = $this->_restCall('metadata?typeFilter=moduleList&platform=portal');

        $this->assertTrue(isset($restReply['reply']['moduleList']['_hash']),'There is no portal module list');
        // There should only be the following modules by default: Bugs, Cases, KBDocuments, Leads
        $enabledPortal = array('Bugs','Cases','KBDocuments','Leads');
        $restModules = $restReply['reply']['moduleList'];
        unset($restModules['_hash']);
        foreach ( $enabledPortal as $module ) {
            $this->assertTrue(in_array($module,$restModules),'Module '.$module.' missing from the portal module list.');
        }
        $this->assertEquals(4,count($restModules),'There are extra modules in the portal module list');
        
        // Now add an extra file and make sure it gets picked up
        if (is_dir($dir = dirname($this->oppTestPath)) === false) {
            sugar_mkdir($dir, null, true);
        }
        sugar_file_put_contents($this->oppTestPath, "<?php\n\$viewdefs['Opportunities'] = array();");
        $restReply = $this->_restCall('metadata?typeFilter=moduleList&platform=portal');

        $this->assertTrue(in_array('Opportunities',$restReply['reply']['moduleList']),'The new Opportunities module did not appear in the portal list');
        
    }
    
    public function testMetadataGetModuleListMobile() {
        $restReply = $this->_restCall('metadata?typeFilter=moduleList&platform=mobile');

        foreach ( array ( '','custom/') as $prefix) {
            if(file_exists($prefix.'include/MVC/Controller/wireless_module_registry.php')){
                require($prefix.'include/MVC/Controller/wireless_module_registry.php');
            }
        }
        
        // $wireless_module_registry is defined in the file loaded above
        $enabledMobile = array_keys($wireless_module_registry);


        $this->assertTrue(isset($restReply['reply']['moduleList']['_hash']),'There is no mobile module list');
        $restModules = $restReply['reply']['moduleList'];
        unset($restModules['_hash']);
        foreach ( $enabledMobile as $module ) {
            $this->assertTrue(in_array($module,$restModules),'Module '.$module.' missing from the mobile module list.');
        }
        $this->assertEquals(count($enabledMobile),count($restModules),'There are extra modules in the mobile module list');
        
        // Create a custom set of wireless modules to test if it is loading those properly
        if ( !is_dir('custom/include/MVC/Controller/') ) {
            mkdir('custom/include/MVC/Controller',0755,true);
        }
        file_put_contents('custom/include/MVC/Controller/wireless_module_registry.php','<'."?php\n".'$wireless_module_registry = array("Accounts"=>"Accounts","Contacts"=>"Contacts","Opportunities"=>"Opportunities");');
        
        $enabledMobile = array('Accounts','Contacts','Opportunities');

        $restReply = $this->_restCall('metadata?typeFilter=moduleList&platform=mobile');
        $this->assertTrue(isset($restReply['reply']['moduleList']['_hash']),'There is no mobile module list on the second pass');
        $restModules = $restReply['reply']['moduleList'];
        unset($restModules['_hash']);
        foreach ( $enabledMobile as $module ) {
            $this->assertTrue(in_array($module,$restModules),'Module '.$module.' missing from the mobile module list on the second pass');
        }
        $this->assertEquals(count($enabledMobile),count($restModules),'There are extra modules in the mobile module list on the second pass');

        
    }

    public function testMetadataGetModuleListBase() {
        $restReply = $this->_restCall('metadata?typeFilter=moduleList');

        $this->assertTrue(isset($restReply['reply']['moduleList']['_hash']),'There is no base module list');
        $restModules = $restReply['reply']['moduleList'];
        unset($restModules['_hash']);
        require_once("modules/MySettings/TabController.php");
        $tc = new TabController();
        $enabledModules = $tc->get_user_tabs($this->_user);
        $enabledBase = array_keys($enabledModules);
        foreach ( $enabledBase as $module ) {
            $this->assertTrue(in_array($module,$restModules),'Module '.$module.' missing from the base module list.');
        }
        $this->assertEquals(count($enabledBase),count($restModules),'There are extra modules in the base module list');
        
    }

}