<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/**
 * @ticket 36564
 */
class Bug36564Test extends SOAPTestCase
{
    /**
     * Create test user
     */
    protected function setUp() : void
    {
        $this->soapURL = $GLOBALS['sugar_config']['site_url'].'/service/v2/soap.php';
        parent::setUp();
    }

    public function testBadQuery()
    {
        $this->login();
        $result = $this->soapClient->call('get_entry_list', ['session'=>$this->sessionId,"module_name" => 'Accounts', "query" => "bad query"]);
        $this->assertNotNull($result["faultstring"], "Result does not contain (expected) faultstring");
        $this->assertContains("Unknown error", $result["faultstring"]);
    } // fn
}
