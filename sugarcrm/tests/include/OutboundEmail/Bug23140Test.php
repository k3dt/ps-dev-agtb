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
 
require_once('include/OutboundEmail/OutboundEmail.php');

/**
 * @ticket 23140
 */
class Bug23140Test extends Sugar_PHPUnit_Framework_TestCase
{
	var $outbound_id = null;
	var $_user = null;
	var $ob = null;
	var $userOverideAccont = null;
	
	public function setUp()
    {
        global $current_user, $currentModule ;
		$this->_user = SugarTestUserUtilities::createAnonymousUser();
		$current_user = $this->_user;
	}

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        //unset($GLOBALS['current_user']);
        
        if ($this->ob != null)
            $GLOBALS['db']->query("DELETE FROM outbound_email WHERE id= '{$this->ob->id}'");
        if ($this->userOverideAccont != null)
            $GLOBALS['db']->query("DELETE FROM outbound_email WHERE id= '{$this->userOverideAccont->id}'");
    }
    
    function testSystemAccountMailSettingsChangedUserAccessToUsername()
    {
        //User not alloweed to access system email username/password
        $GLOBALS['db']->query("INSERT INTO config (category,name,value) VALUES ('notify','allow_default_outbound','2') ");
        
        $newSystemPort = 864;
        $newSystemServer = "system.imap.com";
        $newSystemUsername = "system_user_name";
        $newSystemPassword = "SYSTEM_PASSWORD";
        
        $userID = create_guid();
        $ob = new OutboundEmail();
        $ob->id = $userID;
        $ob->new_with_id = TRUE;
        $ob->name = 'Sugar Test';
        $ob->type = 'system-override';
        $ob->user_id = $this->_user->id;
        $ob->mail_sendtype = "SMTP";
        $ob->mail_smtpuser = "Test User";
        $ob->mail_smtppass = "User Pass";
        $ob->save();
        $this->ob = $ob;
        
        
        $system = $ob->getSystemMailerSettings();
        $system->new_with_id = FALSE;
        $system->mail_smtpport = $newSystemPort;
        $system->mail_smtpserver = $newSystemServer;
        $system->mail_smtpuser = $newSystemUsername;
        $system->mail_smtppass = $newSystemPassword;
        
        $system->saveSystem();
        
        $obRetrieved = new OutboundEmail();
        $obRetrieved->retrieve($userID);
        
        $this->assertEquals($newSystemPort, $obRetrieved->mail_smtpport, "Could not update users port system-override accounts after system save.");
        $this->assertEquals($newSystemServer, $obRetrieved->mail_smtpserver, "Could not update users server system-override accounts after system save.");
        $this->assertEquals($newSystemUsername, $obRetrieved->mail_smtpuser, "Could not update users username system-override accounts after system save.");
        $this->assertEquals($newSystemPassword, $obRetrieved->mail_smtppass, "Could not update users password system-override accounts after system save.");
        
    }
    
    
    function testSystemAccountMailSettingsChangedNoUserAccessToUsername()
    {
        //User not alloweed to access system email username/password
        $GLOBALS['db']->query("DELETE FROM config WHERE category='notify' AND name='allow_default_outbound' ");
        
        $newSystemPort = 864;
        $newSystemServer = "system.imap.com";
        
        $userID = create_guid();
        $ob = new OutboundEmail();
        $ob->id = $userID;
        $ob->new_with_id = TRUE;
        $ob->name = 'Sugar Test';
        $ob->type = 'system-override';
        $ob->user_id = $this->_user->id;
        $ob->mail_sendtype = "SMTP";
        $ob->mail_smtpuser = "Test User";
        $ob->mail_smtppass = "User Pass";
        $ob->save();
        $this->ob = $ob;
        
        
        $system = $ob->getSystemMailerSettings();
        $system->new_with_id = FALSE;
        $system->mail_smtpport = $newSystemPort;
        $system->mail_smtpserver = $newSystemServer;
        $system->saveSystem();
        
        $obRetrieved = new OutboundEmail();
        $obRetrieved->retrieve($userID);
        
        $this->assertEquals($newSystemPort, $obRetrieved->mail_smtpport, "Could not update users system-override accounts after system save.");
        $this->assertEquals($newSystemServer, $obRetrieved->mail_smtpserver, "Could not update users system-override accounts after system save.");
        $this->assertEquals("Test User", $obRetrieved->mail_smtpuser, "Could not update users system-override accounts after system save.");
        $this->assertEquals("User Pass", $obRetrieved->mail_smtppass, "Could not update users system-override accounts after system save.");
    }
    
    
    function testUserMailForSystemOverrideRetrieval()
    {
        $ob = new OutboundEmail();
        $ob->name = 'Sugar Test';
        $ob->type = 'system-override';
        $ob->user_id = $this->_user->id;
        $ob->mail_sendtype = "SMTP";
        $ob->mail_smtpuser = "Test User";
        $ob->save();
        $this->ob = $ob;
        
        $retrievedOb = $ob->getUsersMailerForSystemOverride($this->_user->id);
        $this->assertEquals($ob->name, $retrievedOb->name, "Could not retrieve users system override outbound email account");
        $this->assertEquals($ob->type, $retrievedOb->type, "Could not retrieve users system override outbound email account");
        $this->assertEquals($ob->user_id, $retrievedOb->user_id, "Could not retrieve users system override outbound email account");
        $this->assertEquals($ob->mail_sendtype, $retrievedOb->mail_sendtype, "Could not retrieve users system override outbound email account");
        $this->assertEquals("Test User", $retrievedOb->mail_smtpuser, "Could not retrieve users system override outbound email account");
    }
    
    function testDuplicateSystemAccountForUser()
    {
        $oe = new OutboundEmail();
        $userOverideAccont = $oe->createUserSystemOverrideAccount($this->_user->id, "TEST USER NAME", "TEST PASSWORD");
        $this->userOverideAccont = $userOverideAccont;
        $retrievedOb = $oe->getUsersMailerForSystemOverride($this->_user->id);
        
        $this->assertEquals("TEST USER NAME", $retrievedOb->mail_smtpuser, "Could not duplicate systems outbound account for user");
        $this->assertEquals($this->_user->id, $retrievedOb->user_id, "Could not duplicate systems outbound account for user");
        $this->assertEquals("TEST PASSWORD", $retrievedOb->mail_smtppass, "Could not duplicate systems outbound account for user");
        $this->assertEquals('system-override', $userOverideAccont->type, "Could not duplicate systems outbound account for user");
    }
    
    function testIsUserAlloweedAccessToSystemOutboundEmail()
    {
        $oe = new OutboundEmail();
        $GLOBALS['db']->query("DELETE FROM config WHERE category='notify' AND name='allow_default_outbound' ");
        $emptyTest = $oe->isAllowUserAccessToSystemDefaultOutbound();
        $this->assertFalse($emptyTest, "User alloweed access to system outbound email account error");
        
        $GLOBALS['db']->query("INSERT INTO config (category,name,value) VALUES ('notify','allow_default_outbound','2') ");
        $allowTest = $oe->isAllowUserAccessToSystemDefaultOutbound();
        $this->assertTrue($allowTest, "User alloweed access to system outbound email account error");
        
        $GLOBALS['db']->query("DELETE FROM config WHERE category='notify' AND name='allow_default_outbound' ");
        $emptyTest = $oe->isAllowUserAccessToSystemDefaultOutbound();
        $this->assertFalse($emptyTest, "User alloweed access to system outbound email account error");
           
    }
    
    
    function testIsUserAuthRequiredForOverrideAccount()
    {
        $oe = new OutboundEmail();
        
        $GLOBALS['db']->query("DELETE FROM config WHERE category='notify' AND name='allow_default_outbound' ");
        $system = $oe->getSystemMailerSettings();
        
        //System does not require auth, no user overide account.
        $system->mail_smtpauth_req = 0;
        $system->save(FALSE);
        
        $notRequired = $oe->doesUserOverrideAccountRequireCredentials($this->_user->id);
        $this->assertFalse($notRequired, "Test failed for determining if user auth required.");
        
        //System does require auth, no user overide account.
        $system->mail_smtpauth_req = 1;
        $system->save(FALSE);
        $notRequired = $oe->doesUserOverrideAccountRequireCredentials($this->_user->id);
        $this->assertTrue($notRequired, "Test failed for determining if user auth required.");
        
        //System requires auth and users alloweed to use sys defaults.
        $GLOBALS['db']->query("INSERT INTO config (category,name,value) VALUES ('notify','allow_default_outbound','2') ");
        $notRequired = $oe->doesUserOverrideAccountRequireCredentials($this->_user->id);
        $this->assertFalse($notRequired, "Test failed for determining if user auth required.");
        
        
        //System requires auth but user details are empty and users are not alloweed to use system details..
        $GLOBALS['db']->query("DELETE FROM config WHERE category='notify' AND name='allow_default_outbound' ");
        $userOverideAccont = $oe->createUserSystemOverrideAccount($this->_user->id, "", "");
        $this->userOverideAccont = $userOverideAccont;
        $notRequired = $oe->doesUserOverrideAccountRequireCredentials($this->_user->id);
        $this->assertTrue($notRequired, "Test failed for determining if user auth required.");
        
        //User has provided all credentials.
        $this->userOverideAccont->mail_smtpuser = "TEST USER NAME";
        $this->userOverideAccont->mail_smtppass = "TEST PASSWORD";
        $this->userOverideAccont->new_with_id = FALSE;
        $this->userOverideAccont->save();
        $notRequired = $oe->doesUserOverrideAccountRequireCredentials($this->_user->id);
        $this->assertFalse($notRequired, "Test failed for determining if user auth required.");
        
    }
    
}
?>