<?php
require_once 'modules/Accounts/Account.php';
require_once 'modules/Quotes/Quote.php';

class Bug41050Test extends Sugar_PHPUnit_Framework_TestCase
{
	var $quote;
	var $account;

    
    public function setup()
    {
		 global $current_user, $currentModule ;
		 
		 //make sure we have a current user
		 if(empty($current_user) || empty($current_user->id)){
		 	$usr = new User();
		 	$usr->retrieve('1');
		 	$current_user = $usr;	
		 	
		 
		 }
		 $time = date('Y-m-d H:i:s');
		//for the purpose of this test, we need to create an account and quote object and relate them

		//create account
		$account = new Account();
        $account->name = 'quote test account name';
        $account->assigned_user_id = $current_user->id;
        $account->disable_custom_fields = true;
        $account->save();
        $this->account = $account;

		//create quote
		$timeDate = new TimeDate();
    	$quote = new Quote();
    	$quote->name = 'quote test ' . time();
    	$quote->quote_stage = 'Draft';
    	$quote->date_quote_expected_closed = $timeDate->to_display_date(date('Y')+1 .'-01-01');;
    	$quote->assigned_id = $current_user->id;
    	$quote->save();		
		$this->quote = $quote;
		
		//relate the two with different roles on relationship
		$GLOBALS['db']->query("insert into quotes_accounts ( id, quote_id, account_id, account_role, date_modified, deleted) values ( 'quo_acc_".uniqid()."', '{$quote->id}', '{$account->id}', 'Bill To', '$time', 0)");
		$GLOBALS['db']->query("insert into quotes_accounts ( id, quote_id, account_id, account_role, date_modified, deleted) values ( 'quo_acc_".uniqid()."', {$quote->id}', '{$account->id}', 'Ship To', '$time', 0)");
		
    }
    
    public function tearDown()
    {
		global $current_user;	

		//delete the account, quote and relationship table
		$GLOBALS['db']->query('DELETE FROM quotes WHERE id = \''.$this->quote->id.'\' ');
		$GLOBALS['db']->query('DELETE FROM accounts WHERE id = \''.$this->account->id.'\' ');
		$GLOBALS['db']->query('DELETE FROM quotes_accounts WHERE account_id = \''.$this->account->id.'\' ');
		unset($this->account);
        unset($this->quote);

    }
	

	public function testRetrieveQuoteByAccountName(){
		global $current_user;
		
		//We are going to mimic searching for the quotes by account name using the same api the list views do.
		require_once('include/ListView/ListViewData.php');
		$lvd = new ListViewData();

		//create a fake post/request object	used by listview	
		$_REQUEST = $_POST = array (
		    'module' => 'Quotes',
		    'action' => 'index',
		    'sugar_user_theme' => 'Sugar',
			'query' => 'true',
		    'searchFormTab' => 'advanced_search',
		    'name_advanced' => '',
		    'quote_num_advanced' => '',
		    'account_name_advanced' => $this->account->name,
		    'total_usdollar_advanced' => '',
		    'date_quote_expected_closed_advanced' => '',
		    'favorites_only_advanced' => '0',
		    'button' => 'Search',
				
			);
			
		//create a list of fields passed in to create the query in sugarbean, we are staying as close to the Out of Box list view as possible	
		$filter =  array(
			'quote_num' => 1,
            'name' => 1,
            'billing_account_name' => 1,
            'quote_stage' => 1,
            'total_usdollar' => 1,
            'currency_id' => 1,
            'date_quote_expected_closed' => 1,
            'assigned_user_name' => 1,
            'account_name' => 1,
            'favorites_only' => 1
        );
		
        //mimic querying for the listview
		$listResults = $lvd->getListViewData(new Quote(), "(jt0.name like '".$this->account->name."%')",-1, -1, $filter );

		//if there is no data returned, then an error occurred
		$this->assertFalse(empty($listResults['data']), 'List view query failed to retrieve the quote by the account name.');
		
	}
	
	

}