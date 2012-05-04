<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once 'modules/Reports/Report.php';

/**
 * Bug #45374
 * Report fails when displaying "Teams" field of teams module
 *
 * @author asokol
 *
 */

class Bug45374Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $default_team_name;
    protected $team_set;
    protected $teams;
    protected $team_name_list;
    protected $team_set_list;
    protected $team_sets_ids;

    protected $report;

    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');

        $this->team_sets_ids = array();
        $this->team_name_list = array('t1', 't2', 't3', 't4', 't5');
        $this->team_set_list = array('t3', 't4', 't5');
        $this->default_team_name = 't5';

        //Create test user
        //Make sure we are an admin
        SugarTestHelper::setUp('current_user', array(true, 1));

        //Add user to his private tean set
        $this->team_set = new TeamSet();
        $this->team_set->addTeams($GLOBALS['current_user']->getPrivateTeamID());

        //Create teams list according to list
        //Add user to teams and compose id for adding to team set
        foreach ($this->team_name_list as $key => $name)
        {
            $this->teams[$name] = SugarTestTeamUtilities::createAnonymousTeam();
            $this->teams[$name]->name = $name;
            $this->teams[$name]->save();
            $this->teams[$name]->add_user_to_team($GLOBALS['current_user']->id);

            if (in_array($name, $this->team_set_list))
            {
                $this->team_sets_ids[] = $this->teams[$name]->id;
            }

        }

        //Teams from teasm set list to team set
        $this->team_set->addTeams($this->team_sets_ids);

        //Reset user default team
        $GLOBALS['current_user']->team_id = $this->teams[$this->default_team_name]->id;
        $GLOBALS['current_user']->team_set_id = $this->team_set->id;
        $GLOBALS['current_user']->save();

    }

    public function testDefaultTeam()
    {
        $default_team = array();

        //Filter: Users > Default Primary Team > Primary Team Name (is not empty)
        //Display columns: Users > Default Primary Team > Primary Team Name, Users > Default Primary Team > ID
        $report_def_str = '{"display_columns":[{"name":"name","label":"Primary Team Name","table_key":"Users:default_primary_team"}],"module":"Users","group_defs":[],"summary_columns":[],"report_name":"","do_round":1,"numerical_chart_column":"","numerical_chart_column_type":"","assigned_user_id":"1","report_type":"tabular","full_table_list":{"self":{"value":"Users","module":"Users","label":"Users"},"Users:default_primary_team":{"name":"Users  >  Default Primary Team","parent":"self","link_def":{"name":"default_primary_team","relationship_name":"users_team","bean_is_lhs":false,"link_type":"one","label":"Default Primary Team","module":"Teams","table_key":"Users:default_primary_team"},"dependents":["Filter.1_table_filter_row_2","display_cols_row_1"],"module":"Teams","label":"Default Primary Team"}}}';
        $filters_def_str = '[{"panelId":"Filter.1","name":"id","table_key":"Users:default_primary_team","qualifier_name":"not_empty","input_name0":"undefined","input_name1":"on"}]';
        $panels_def_str = '[{"id":"Filter.1","parentId":"Filter","operator":"AND","children":[]}]';
        $this->report = new Report($report_def_str, $filters_def_str, $panels_def_str);

        $this->report->run_query();
        $result = $GLOBALS['db']->fetchByAssoc($this->report->result);
        while ($row = $GLOBALS['db']->fetchByAssoc($this->report->result))
        {
            if($row['l1_name'] == $this->default_team_name)
            {
                $default_team = $row;
            }
        }

        $this->assertNotEmpty($default_team);
        $this->assertEquals($this->default_team_name, $default_team['l1_name']);
    }

    public function testTeamSet()
    {
        // Filter: Users > Team Set > ID (is not empty)
        // Display columns: Users > Default Primary Team > Primary Team Name, Users > Default Primary Team > ID
        $report_def_str = '{"display_columns":[{"name":"name","label":"Primary Team Name","table_key":"Users:team_sets"},{"name":"id","label":"ID","table_key":"Users:team_sets"}],"module":"Users","group_defs":[],"summary_columns":[],"report_name":"","do_round":1,"numerical_chart_column":"","numerical_chart_column_type":"","assigned_user_id":"1","report_type":"tabular","full_table_list":{"self":{"value":"Users","module":"Users","label":"Users"},"Users:team_sets":{"name":"Users  >  Team Set","parent":"self","link_def":{"name":"team_sets","relationship_name":"users_team_sets","bean_is_lhs":false,"link_type":"many","label":"Team Set","module":"Teams","table_key":"Users:team_sets"},"dependents":["Filter.1_table_filter_row_1","display_cols_row_1","display_cols_row_2"],"module":"Teams","label":"Team Set"}}}';
        $filters_def_str = '[{"panelId":"Filter.1","name":"id","table_key":"Users:team_sets","qualifier_name":"not_empty","input_name0":"undefined","input_name1":"on"}]';
        $panels_def_str = '[{"id":"Filter.1","parentId":"Filter","operator":"AND","children":[]}]';
        $this->report = new Report($report_def_str, $filters_def_str, $panels_def_str);
        $count = 0;

        $this->report->run_query();
        $result = $GLOBALS['db']->fetchByAssoc($this->report->result);
        while ($row = $GLOBALS['db']->fetchByAssoc($this->report->result))
        {
            if (in_array($row['l1_name'], $this->team_set_list))
            {
                $count++;
            }
        }
        $this->assertEquals(count($this->team_set_list), $count);
    }

    public function testTeamMembership()
    {
        // Filter: Users > Team Set > ID (is not empty)
        // Display columns: Users > Team Membership > Primary Team Name, Users > Team Membership > ID
        $report_def_str = '{"display_columns":[{"name":"name","label":"Primary Team Name","table_key":"Users:team_memberships"},{"name":"private","label":"Private","table_key":"Users:team_memberships"}],"module":"Users","group_defs":[],"summary_columns":[],"report_name":"","chart_type":"none","do_round":1,"numerical_chart_column":"","numerical_chart_column_type":"","assigned_user_id":"1","report_type":"tabular","full_table_list":{"self":{"value":"Users","module":"Users","label":"Users"},"Users:team_memberships":{"name":"Users  >  Team Membership","parent":"self","link_def":{"name":"team_memberships","relationship_name":"team_memberships","bean_is_lhs":false,"link_type":"many","label":"Team Membership","module":"Teams","table_key":"Users:team_memberships"},"dependents":[null,null,null,null,null,null,null,"display_cols_row_3",null,null,null,"display_cols_row_3","display_cols_row_4",null,"display_cols_row_1","display_cols_row_2",null,null,"Filter.1_table_filter_row_5"],"module":"Teams","label":"Team Membership"},"Users:default_primary_team":{"name":"Users  >  Default Primary Team","parent":"self","link_def":{"name":"default_primary_team","relationship_name":"users_team","bean_is_lhs":false,"link_type":"one","label":"Default Primary Team","module":"Teams","table_key":"Users:default_primary_team"},"dependents":[null,null,null,null,null,null,"display_cols_row_3",null,null,null],"module":"Teams","label":"Default Primary Team"}}}';
        $filters_def_str = '[{"panelId":"Filter.1","name":"id","table_key":"Users:team_memberships","qualifier_name":"not_empty","input_name0":"undefined","input_name1":"on"}]';
        $panels_def_str = '[{"id":"Filter.1","parentId":"Filter","operator":"AND","children":[]}]';
        $this->report = new Report($report_def_str, $filters_def_str, $panels_def_str);
        $count = 0;

        $this->report->run_query();
        $result = $GLOBALS['db']->fetchByAssoc($this->report->result);
        while ($row = $GLOBALS['db']->fetchByAssoc($this->report->result))
        {
            if (in_array($row['l1_name'], $this->team_name_list))
            {
                $count++;
            }
        }
        $this->assertEquals(count($this->team_name_list), $count);
    }

    public function tearDown()
    {
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        SugarTestHelper::tearDown();
    }

}
