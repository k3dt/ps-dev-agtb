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

require_once 'modules/Accounts/Account.php';
require_once 'modules/Meetings/Meeting.php';
require_once 'include/SearchForm/SearchForm2.php';


class Bug45287Test extends Sugar_PHPUnit_Framework_TestCase
{
    var $meetingsArr;
    var $searchDefs;
    var $searchFields;
    
    public function setup()
    {
        global $current_user, $timedate;
        // Create Anon User setted on GMT+2 TimeZone
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        $current_user->setPreference('datef', "d/m/Y");
        $current_user->setPreference('timef', "H:i:s");
        $current_user->setPreference('timezone', "Europe/Rome");

        // new object to avoid TZ caching
        $timedate = new TimeDate();

        $this->meetingsArr = array();

        // Create a Bunch of Meetings
        $d = 12;
        $cnt = 0;
        while ($d < 15)
        {
          $this->meetingsArr[$cnt] = new Meeting();
          $this->meetingsArr[$cnt]->name = 'Bug45287 Meeting ' . ($cnt + 1);
          $this->meetingsArr[$cnt]->date_start = $timedate->to_display_date_time(gmdate("Y-m-d H:i:s", mktime(10+$cnt, 30, 00, 7, $d, 2011)));
          $this->meetingsArr[$cnt]->save();
          $d++;
          $cnt++;
        }

        $this->searchDefs = array("Meetings" => array("layout" => array("basic_search" => array("name" => array("name" => "name",
                                                                                                                "default" => true,
                                                                                                                "width" => "10%",
                                                                                                               ),
                                                                                                "date_start" => array("name" => "date_start",
                                                                                                                      "default" => true,
                                                                                                                      "width" => "10%",
                                                                                                                      "type" => "datetimecombo",
                                                                                                                     ), 
                                                                                               ),
                                                                       ),
                                                     ),
                                 );

        $this->searchFields = array("Meetings" => array("name" => array("query_type" => "default"),
                                                        "date_start" => array("query_type" => "default"),
                                                        "range_date_start" => array("query_type" => "default",
                                                                                    "enable_range_search" => 1,
                                                                                    "is_date_field" => 1),
                                                        "range_date_start" => array("query_type" => "default",
                                                                                    "enable_range_search" => 1,
                                                                                    "is_date_field" => 1),
                                                        "start_range_date_start" => array("query_type" => "default",
                                                                                          "enable_range_search" => 1,
                                                                                          "is_date_field" => 1),
                                                        "end_range_date_start" => array("query_type" => "default",
                                                                                        "enable_range_search" => 1,
                                                                                        "is_date_field" => 1),
                                                       ),
                                   );
    }
    
    public function tearDown()
    {

        foreach ($this->meetingsArr as $m)
        {
            $GLOBALS['db']->query('DELETE FROM meetings WHERE id = \'' . $m->id . '\' ');
        }

        unset($m);
        unset($this->meetingsArr);
        unset($this->searchDefs);
        unset($this->searchFields);

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }
	
    
    public function testRetrieveByExactDate()
    {
        global $current_user, $timedate;

        $_REQUEST = $_POST = array("module" => "Meetings",
                                   "action" => "index",
                                   "searchFormTab" => "basic_search",
                                   "query" => "true",
                                   "name_basic" => "",
                                   "current_user_only_basic" => "0",
                                   "favorites_only_basic" => "0",
                                   "open_only_basic" => "0",
                                   "date_start_basic_range_choice" => "=",
                                   "range_date_start_basic" => "14/07/2011",
                                   "start_range_date_start_basic" => "",
                                   "end_range_date_start_basic" => "", 
                                   "button" => "Search",
                                  );

        $srch = new SearchForm(new Meeting(), "Meetings");
        $srch->setup($this->searchDefs, $this->searchFields, "");
        $srch->populateFromRequest();
        $w = $srch->generateSearchWhere();

        // Due to daylight savings, I cannot hardcode intervals...
        $GMTDates = $timedate->getDayStartEndGMT("2011-07-14");

        // Current User is on GMT+2.
        // Asking for meeting of 14 July 2011, I expect to search (GMT) from 13 July at 22:00 until 14 July at 22:00 (excluded)
        $expectedWhere = "meetings.date_start >= '" . $GMTDates['start'] . "' AND meetings.date_start <= '" . $GMTDates['end'] . "'";
        $this->assertEquals($w[0], $expectedWhere);
    }
	

    public function testRetrieveByDaterange()
    {
        global $current_user, $timedate;

        $_REQUEST = $_POST = array("module" => "Meetings",
                                   "action" => "index",
                                   "searchFormTab" => "basic_search",
                                   "query" => "true",
                                   "name_basic" => "",
                                   "current_user_only_basic" => "0",
                                   "favorites_only_basic" => "0",
                                   "open_only_basic" => "0",
                                   "date_start_basic_range_choice" => "between",
                                   "range_date_start_basic" => "",
                                   "start_range_date_start_basic" => "13/07/2011",
                                   "end_range_date_start_basic" => "14/07/2011", 
                                   "button" => "Search",
                                  );


        $srch = new SearchForm(new Meeting(), "Meetings");
        $srch->setup($this->searchDefs, $this->searchFields, "");
        $srch->populateFromRequest();
        $w = $srch->generateSearchWhere();

        // Due to daylight savings, I cannot hardcode intervals...
        $GMTDatesStart = $timedate->getDayStartEndGMT("2011-07-13");
        $GMTDatesEnd = $timedate->getDayStartEndGMT("2011-07-14");

        // Current User is on GMT+2.
        // Asking for meeting between 13 and 14 July 2011, I expect to search (GMT) from 12 July at 22:00 until 14 July at 22:00 (excluded)
        $expectedWhere = "meetings.date_start >= '" . $GMTDatesStart['start'] . "' AND meetings.date_start <= '" . $GMTDatesEnd['end'] . "'";
        $this->assertEquals($w[0], $expectedWhere);
   }
	

}
