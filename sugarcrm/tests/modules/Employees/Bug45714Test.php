<?php

require_once('modules/Users/User.php');

class Bug45714Test extends Sugar_PHPUnit_Framework_TestCase 
{
	public function setUp()
	{
		 $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
		 //$this->useOutputBuffering = true;
	}	
	
	public function tearDown()
	{
		 SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
	}
	
	public function testViewAsAdminUser()
	{
		$GLOBALS['current_user']->is_admin = true;
		$output = $this->getEmployeeListViewOutput();
		$output = $this->getEmployeeListViewOutput();
		$this->assertRegExp('/utilsLink/', $output, 'Assert that the links are shown for admin user');		
		$output = $this->getEmployeeListViewOutput();
		$this->assertRegExp('/utilsLink/', $output, 'Assert that the links are shown for module admin user');
	}
	
	public function testViewAsNonAdminUser()
	{
		$output = $this->getEmployeeListViewOutput();
		$this->assertNotRegExp('/utilsLink/', $output, 'Assert that the links are not shown for normal user');
		$output = $this->getEmployeeDetailViewOutput();
		$this->assertNotRegExp('/utilsLink/', $output, 'Assert that the links are not shown for normal user');
	}
	
	//BEGIN SUGARCRM flav=pro ONLY
	public function testViewAsModuleAdmin()
	{
		$GLOBALS['current_user'] = new Bug45714UserMock();
		$output = $this->getEmployeeListViewOutput();
		$this->assertRegExp('/utilsLink/', $output, 'Assert that the links are shown for module admin user');
		$output = $this->getEmployeeDetailViewOutput();
		$this->assertRegExp('/utilsLink/', $output, 'Assert that the links are shown for module admin user');	
	}
	//END SUGARCRM flav=pro ONLY
	
	private function getEmployeeListViewOutput()
	{
		require_once('modules/Employees/views/view.list.php');
		$employeeViewList = new EmployeesViewList();
		$employeeViewList->module = 'Employees';
		return $employeeViewList->getModuleTitle(true);
	}
	
	private function getEmployeeDetailViewOutput()
	{
		require_once('modules/Employees/views/view.detail.php');
		$employeeViewDetail = new EmployeesViewDetail();
		$employeeViewDetail->module = 'Employees';
		return $employeeViewDetail->getModuleTitle(true);
	}	
}

class Bug45714UserMock extends User
{
    public function isDeveloperForModule($module) {
		return true;
    }
}

?>