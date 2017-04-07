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

namespace Sugarcrm\SugarcrmTests\ProcessManager\Field\Evaluator;

use Sugarcrm\Sugarcrm\ProcessManager\Field\Evaluator;

class CurrencyTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * EvaluatorInterface object
     * @var EvaluatorInterface
     */
    protected $eval;

    public function setup()
    {
        parent::setup();
        $this->eval = new Evaluator\Currency;
    }

    /**
     * Tests whether a value on a bean has changed
     * @dataProvider hasChangedProvider
     * @param SugarBean $bean SugarBean to test with
     * @param string $name Name of the field to test
     * @param array $data Data array to test
     * @param boolean $expect Expectation
     */
    public function testHasChanged($bean, $name, $data, $expect)
    {
        $this->eval->init($bean, $name, $data);
        $actual = $this->eval->hasChanged();
        $this->assertEquals($expect, $actual);
    }

    public function hasChangedProvider()
    {
        // Simple bean setup to cover all test cases
        $bean = \BeanFactory::newBean('Bugs');
        $bean->currency_id = 'test_currency_id';
        $bean->test1 = '120.000000';
        $bean->test3 = '321.000000';
        $bean->test4 = '879.000000';
        $bean->test5 = '879.000000';

        return array(
            // Tests no data value given
            array(
                'bean' => $bean,
                'name' => 'test1',
                'data' => array(),
                'expect' => false,
            ),
            // Tests no bean property set
            array(
                'bean' => $bean,
                'name' => 'test2',
                'data' => array('test2' => '12.000000', 'currency_id' => 'test_currency_id'),
                'expect' => false,
            ),
            // Tests no change of data
            array(
                'bean' => $bean,
                'name' => 'test3',
                'data' => array('test3' => '321.000000', 'currency_id' => 'test_currency_id'),
                'expect' => false,
            ),
            // Tests value change
            array(
                'bean' => $bean,
                'name' => 'test4',
                'data' => array('test4' => '213.000000', 'currency_id' => 'test_currency_id'),
                'expect' => true,
            ),
            // Test id change
            array(
                'bean' => $bean,
                'name' => 'test5',
                'data' => array('test5' => '879.000000', 'currency_id' => 'new_currency_id'),
                'expect' => true,
            ),
        );
    }
}
