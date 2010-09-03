<?php 
require_once('modules/Contacts/Contact.php');


/**
 * @ticket 32487
 */
class ComposePackageTest extends Sugar_PHPUnit_Framework_TestCase
{
	var $c = null;
	var $a = null;
	var $ac_id = null;
	
	public function setUp()
    {
        global $current_user, $currentModule ;
		$mod_strings = return_module_language($GLOBALS['current_language'], "Contacts");
		$current_user = SugarTestUserUtilities::createAnonymousUser();
		$unid = uniqid();
		$time = date('Y-m-d H:i:s');

		$contact = new Contact();
		$contact->id = 'c_'.$unid;
        $contact->first_name = 'testfirst';
        $contact->last_name = 'testlast';
        $contact->new_with_id = true;
        $contact->disable_custom_fields = true;
        $contact->save();
		$this->c = $contact;
		
		
	}

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$this->c->id}'");
        
        unset($this->c);
    }

	function testComposeFromMethodCallNoData(){
	    
	    $_REQUEST['forQuickCreate'] = true;
	    require_once('modules/Emails/Compose.php');
	    $data = array();
	    $compose_data = generateComposeDataPackage($data,FALSE);
	    
		$this->assertEquals('', $compose_data['to_email_addrs']);
    }
    
    function testComposeFromMethodCallForContact(){
	    
	    $_REQUEST['forQuickCreate'] = true;
	    require_once('modules/Emails/Compose.php');
	    $data = array();
	    $data['parent_type'] = 'Contacts';
	    $data['parent_id'] = $this->c->id;
	    
	    $compose_data = generateComposeDataPackage($data,FALSE);

		$this->assertEquals('Contacts', $compose_data['parent_type']);
		$this->assertEquals($this->c->id, $compose_data['parent_id']);
		$this->assertEquals($this->c->name, $compose_data['parent_name']);
    }

}
?>