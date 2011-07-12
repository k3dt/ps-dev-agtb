<?php

require_once "modules/Accounts/Account.php";
require_once "include/Popups/PopupSmarty.php";
require_once "include/SearchForm/SearchForm2.php";

class Bug44858Test extends Sugar_PHPUnit_Framework_TestCase
{
   
    
    public function tearDown()
    {
        
        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }
    
    /**
     * @ticket 44858
     */
    public function testGeneratedWhereClauseDoesNotHaveValueOfFieldNotSetInSearchForm()
    {
        //test to check that if value of a dropdown field is already set in REQUEST object (from any form such as mass update form instead of search form)
        //i.e. search is made on empty string, but REQUEST object gets value of that dropdown field from some other form on the same page
        //then on clicking serach button, value of that field should not be used as filter in where clause
        
    	//array to simulate REQUEST object
    	$requestArray['module'] = 'Accounts';
    	$requestArray['action'] = 'index';
    	$requestArray['searchFormTab'] = 'basic_search';
    	$requestArray['account_type'] = 'Analyst'; //value of a dropdown field set in REQUEST object
    	$requestArray['query'] = 'true';
    	$requestArray['button']  = 'Search';
    	$requestArray['globalLinksOpen']='true';
    	$requestArray['current_user_only_basic'] = 0;
    	
    	$account = SugarTestAccountUtilities::createAccount();
    	$searchForm = new SearchForm($account,'Accounts');
    	
    	require 'modules/Accounts/vardefs.php';
    	require 'modules/Accounts/metadata/SearchFields.php';
    	require 'modules/Accounts/metadata/searchdefs.php';
        $searchForm->searchFields = $searchFields[$searchForm->module]; 
        $searchForm->searchdefs = $searchdefs[$searchForm->module];                          
    	$searchForm->populateFromArray($requestArray,'basic_search',false);
    	$whereArray = $searchForm->generateSearchWhere(true, $account->module_dir);
    	$this->assertEquals(0, count($whereArray));

    }
}
