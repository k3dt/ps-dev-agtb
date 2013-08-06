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

class RestMetadataSugarFieldsTest extends RestTestBase {
    public function setUp()
    {
        parent::setUp();
        $this->oldFiles = array();

//BEGIN SUGARCRM flav=pro ONLY
        $this->_restLogin('','','mobile');
        $this->mobileAuthToken = $this->authToken;
//END SUGARCRM flav=pro ONLY
        $this->_restLogin('','','base');
        $this->baseAuthToken = $this->authToken;

    }

    /**
     * @group rest
     */
    public function testMetadataSugarFields() {
        $this->_clearMetadataCache();
        $restReply = $this->_restCall('metadata?type_filter=fields');
        $this->assertTrue(isset($restReply['reply']['fields']['_hash']),'SugarField hash is missing.');
    }

    /**
     * @group rest
     */
    public function testMetadataSugarFieldsTemplates() {
        $filesToCheck = array(
            //BEGIN SUGARCRM flav=pro ONLY
            'clients/mobile/fields/address/editView.hbs',
            'clients/mobile/fields/address/detailView.hbs',
            //END SUGARCRM flav=pro ONLY
            'clients/base/fields/address/editView.hbs',
            'clients/base/fields/address/detailView.hbs',
            //BEGIN SUGARCRM flav=pro ONLY
            'custom/clients/mobile/fields/address/editView.hbs',
            'custom/clients/mobile/fields/address/detailView.hbs',
            //END SUGARCRM flav=pro ONLY
            'custom/clients/base/fields/address/editView.hbs',
            'custom/clients/base/fields/address/detailView.hbs',
        );
        SugarTestHelper::saveFile($filesToCheck);

        $dirsToMake = array(
            //BEGIN SUGARCRM flav=pro ONLY
            'clients/mobile/fields/address',
            //END SUGARCRM flav=pro ONLY
            'clients/base/fields/address',
            //BEGIN SUGARCRM flav=pro ONLY
            'custom/clients/mobile/fields/address',
            //END SUGARCRM flav=pro ONLY
            'custom/clients/base/fields/address',
        );

        foreach ($dirsToMake as $dir ) {
            SugarAutoLoader::ensureDir($dir);
        }

        /**
         * Note that we used to return only one widget per widget name. For example, if we had a base/date
         * and a portal/date, and the current client id was portal, we'd just get the portal/date. However,
         * we have now moved to returning both of these from within the widget type (e.g. reply.<type>.<platform>.<widget>)
         */
        SugarAutoLoader::put('clients/base/fields/address/editView.hbs','BASE EDITVIEW', true);
        //BEGIN SUGARCRM flav=pro ONLY
        // Make sure we get it when we ask for mobile
        SugarAutoLoader::put('clients/mobile/fields/address/editView.hbs','MOBILE EDITVIEW', true);
        $this->_clearMetadataCache();
        $this->authToken = $this->mobileAuthToken;
        $restReply = $this->_restCall('metadata/?type_filter=fields');
        $this->assertEquals('MOBILE EDITVIEW',$restReply['reply']['fields']['address']['templates']['editView'],"Didn't get mobile code when that was the direct option");

        // Make sure we get it when we ask for mobile, even though there is base code there
        $this->_clearMetadataCache();
        $this->authToken = $this->mobileAuthToken;
        $restReply = $this->_restCall('metadata/?type_filter=fields');
        $this->assertEquals('MOBILE EDITVIEW',$restReply['reply']['fields']['address']['templates']['editView'],"Didn't get mobile code when base code was there.");
        //END SUGARCRM flav=pro ONLY

        // Make sure we get the base code when we ask for it.
        $this->_clearMetadataCache();
        $this->authToken = $this->baseAuthToken;
        $restReply = $this->_restCall('metadata/?type_filter=fields');
        $this->assertEquals('BASE EDITVIEW',$restReply['reply']['fields']['address']['templates']['editView'],"Didn't get base code when it was the direct option");

        //BEGIN SUGARCRM flav=pro ONLY
        // Delete the mobile address and make sure it falls back to base
        SugarAutoLoader::unlink('clients/mobile/fields/address/editView.hbs', true);
        $this->_clearMetadataCache();
        $this->authToken = $this->mobileAuthToken;
        $restReply = $this->_restCall('metadata/?type_filter=fields');
        $this->assertEquals('BASE EDITVIEW',$restReply['reply']['fields']['address']['templates']['editView'],"Didn't fall back to base code when mobile code wasn't there.");


        // Make sure the mobile code is loaded before the non-custom base code
        SugarAutoLoader::put('custom/clients/mobile/fields/address/editView.hbs','CUSTOM MOBILE EDITVIEW', true);
        $this->_clearMetadataCache();
        $this->authToken = $this->mobileAuthToken;
        $restReply = $this->_restCall('metadata/?type_filter=fields');
        $this->assertEquals('CUSTOM MOBILE EDITVIEW',$restReply['reply']['fields']['address']['templates']['editView'],"Didn't use the custom mobile code.");
        //END SUGARCRM flav=pro ONLY

        // Make sure custom base code works
        SugarAutoLoader::put('custom/clients/base/fields/address/editView.hbs','CUSTOM BASE EDITVIEW', true);
        $this->_clearMetadataCache();
        $this->authToken = $this->baseAuthToken;
        $restReply = $this->_restCall('metadata/?type_filter=fields');
        $this->assertEquals('CUSTOM BASE EDITVIEW',$restReply['reply']['fields']['address']['templates']['editView'],"Didn't use the custom base code.");
    }


}
