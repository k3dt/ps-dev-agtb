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

class RestMetadataRelationshipsTest extends RestTestBase {
    public function setUp()
    {
        parent::setUp();
    }
    
    public function tearDown()
    {
        parent::tearDown();
    }
    
    /**
     * @group rest
     */
    public function testMetadataGetRelationships() {
        $restReply = $this->_restCall('metadata?type_filter=relationships');

        $this->assertTrue(isset($restReply['reply']['relationships']['_hash']),'There is no relationship list');
        $this->assertTrue(isset($restReply['reply']['relationships']['opportunities_contacts']),'There is no opportunities contacts relationship');
    }

    /**
     * @group rest
     */
    public function testMetadataGetFilteredRelationships() {
        $moduleList = array('Accounts','Contacts','Cases');

        $restReply = $this->_restCall('metadata?type_filter=relationships&module_filter='.implode(',',$moduleList));

        $this->assertTrue(isset($restReply['reply']['relationships']['_hash']),'There is no relationship list');
        $this->assertTrue(isset($restReply['reply']['relationships']['opportunities_contacts']),'There is no opportunities contacts relationship');

        foreach ( $restReply['reply']['relationships'] as $relName => $relData ) {
            if ( $relName == '_hash' ) {
                continue;
            }
            $this->assertTrue(
                (in_array($relData['lhs_module'],$moduleList)||in_array($relData['rhs_module'],$moduleList)),
                "$relName does not have a LHS [$relData[lhs_module] or RHS module [$relData[rhs_module]] that is in (Accounts, Contacts or Cases)"
            );
        }
    }

}