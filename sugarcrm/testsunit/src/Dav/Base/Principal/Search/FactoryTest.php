<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
namespace Sugarcrm\SugarcrmTestsUnit\Dav\Base\Principal\Search;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * Class FactoryTest
 * @package            Sugarcrm\SugarcrmTestsUnit\Dav\Base\Principal\Search
 *
 * @coversDefaultClass Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Factory
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function getSearchClassNameProvider()
    {
        return array(
            array(
                'prefixPath' => '',
                'class' => '',
            ),
            array(
                'prefixPath' => 'principals/users',
                'class' => 'Users',
            ),
            array(
                'prefixPath' => 'principals',
                'class' => '',
            ),
            array(
                'prefixPath' => 'principals/contacts',
                'class' => 'Contacts',
            ),
            array(
                'prefixPath' => 'principals/leads',
                'class' => 'Leads',
            ),
        );
    }

    public function getSearchClassProvider()
    {
        return array(
            array(
                'prefixPath' => '',
                'class' => null,
            ),
            array(
                'prefixPath' => 'principals/test',
                'class' => null,
            ),
            array(
                'prefixPath' => 'principals/users',
                'class' => 'Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Users',
            ),
            array(
                'prefixPath' => 'principals/leads',
                'class' => 'Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Leads',
            ),
        );
    }

    /**
     * @param $prefixPath
     * @param $expectedClass
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Factory::getSearchClassName
     *
     * @dataProvider getSearchClassNameProvider
     */
    public function testGetSearchClassName($prefixPath, $expectedClass)
    {
        $factoryMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Factory')
                            ->disableOriginalConstructor()
                            ->setMethods(null)
                            ->getMock();

        $result = TestReflection::callProtectedMethod($factoryMock, 'getSearchClassName', array($prefixPath));

        $this->assertEquals($expectedClass, $result);
    }

    /**
     * @param string $prefixPath
     * @param string $expectedClass
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Factory::getSearchClass
     *
     * @dataProvider getSearchClassProvider
     */
    public function testGetSearchClass($prefixPath, $expectedClass)
    {
        $factoryMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Factory')
                            ->disableOriginalConstructor()
                            ->setMethods(null)
                            ->getMock();

        $result = $factoryMock->getSearchClass($prefixPath);

        if (is_null($expectedClass)) {
            $this->assertNull($result);
        } else {
            $this->assertInstanceOf($expectedClass, $result);
        }
    }
}
