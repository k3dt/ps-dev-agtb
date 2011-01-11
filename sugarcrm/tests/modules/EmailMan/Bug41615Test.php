<?php 
//FILE SUGARCRM flav=pro ONLY
require_once('modules/EmailMan/EmailMan.php');

class Bug41615Test extends Sugar_PHPUnit_Framework_TestCase
{
	function testCreateNewListQuery(){
		$emailMan = new EmailMan();
		$filter = array();
		$filter['campaign_name'] = 1;
		$filter['recipient_name'] = 1;
		$filter['recipient_email'] = 1;
		$filter['message_name'] = 1;
		$filter['send_date_time'] = 1;
		$filter['send_attempts'] = 1;
		$filter['in_queue'] = 1;
		
		$params = array();
		$params['massupdate'] = 1;
		
		$query = $emailMan->create_new_list_query('date_entered DESC', '', $filter, $params);
		preg_match('/ORDER\sBY\semailman\.date_entered/', $query, $matches);	
		$this->assertTrue(!empty($matches[0]));
		$this->assertEquals($matches[0], 'ORDER BY emailman.date_entered', 'Assert that the ORDER BY clause includes the table name'); 
    }
}
?>