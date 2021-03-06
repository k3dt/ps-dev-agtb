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

use PHPUnit\Framework\TestCase;

require_once 'include/SearchForm/SearchForm2.php';

class Bug47537Test extends TestCase
{
    public $module = 'Quotes';
    public $action = 'index';
    public $seed;
    public $form;
    public $array;

    protected function setUp() : void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        require "modules/".$this->module."/metadata/searchdefs.php";
        require "modules/".$this->module."/metadata/SearchFields.php";
        require "modules/".$this->module."/metadata/listviewdefs.php";

        $this->seed = BeanFactory::newBean($this->module);
        $this->form = new SearchForm($this->seed, $this->module, $this->action);
        $this->form->setup($searchdefs, $searchFields, 'include/SearchForm/tpls/SearchFormGeneric.tpl', "advanced_search", $listViewDefs);

        $this->array = [
            'module'=>$this->module,
            'action'=>$this->action,
            'searchFormTab'=>'advanced_search',
            'query'=>'true',
            'quote_num_advanced_range_choice'=>'',
            'range_quote_num_advanced' => '',
            'start_quote_num_entered_advanced' => '',
            'end_quote_num_entered_advanced' => '',
        ];
    }

    protected function tearDown() : void
    {
        unset($this->array);
        unset($this->form);
        unset($this->seed);
        SugarTestHelper::tearDown();
    }

    /**
     * Data provider for single integer range searches
     * @return array data for tests
     */
    public function singleIntRangeProvider()
    {
        return [
            ["=", "1", [strtolower($this->module).".quote_num = 1"]],
            ["not_equal", "1", ["(". strtolower($this->module).".quote_num IS NULL OR " . strtolower($this->module) . ".quote_num != 1)"]],
            ["greater_than", "1", [strtolower($this->module).".quote_num > 1"]],
            ["greater_than_equals", "1", [strtolower($this->module).".quote_num >= 1"]],
            ["less_than", "1", [strtolower($this->module).".quote_num < 1"]],
            ["less_than_equals", "1", [strtolower($this->module).".quote_num <= 1"]],
        ];
    }

    /**
     * Tests single integer advanced searches
     * @dataProvider singleIntRangeProvider
     * @param $op operator from dataProvider
     * @param $val values from dataProvider
     * @param $expected expected result from dataProvider
     */
    public function testAdvancedSearchForInt($op, $val, $expected)
    {
        $this->array['quote_num_advanced_range_choice'] = $op;
        $this->array['range_quote_num_advanced'] = $val;

        $this->form->populateFromArray($this->array);
        $query = $this->form->generateSearchWhere($this->seed, $this->module);
        $this->assertSame($expected, $query);
    }


    public function testAdvancedSearchForIntBetween()
    {
        $this->array['quote_num_advanced_range_choice'] = 'between';
        $this->array['start_range_quote_num_advanced'] = '1';
        $this->array['end_range_quote_num_advanced'] = '3';
        $expected = ["(". strtolower($this->module).".quote_num >= 1 AND ".strtolower($this->module).".quote_num <= 3)"];

        $this->form->populateFromArray($this->array);
        $query = $this->form->generateSearchWhere($this->seed, $this->module);
        $this->assertSame($expected, $query);
    }
}
