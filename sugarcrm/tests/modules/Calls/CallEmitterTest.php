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

namespace Sugarcrm\SugarcrmTests\tests\modules\Calls;

require_once 'modules/Calls/Emitter.php';

use CallEmitter;
use Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Emitter as ReminderEmitter;

/**
 * @coversDefaultClass CallEmitter
 */
class CallEmitterTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var CallEmitter */
    protected $callEmitter = null;

    /** @var ReminderEmitter|\PHPUnit_Framework_MockObject_MockObject */
    protected $reminderEmitter = null;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->reminderEmitter = $this->getMock('Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Emitter', array(
            'getEventPrototypeByString',
            'getEventStrings',
            'reminder',
        ));
        $this->callEmitter = new CallEmitter($this->reminderEmitter);
    }

    /**
     * String value of CallEmitter should be name of the module
     *
     * @covers CallEmitter::__toString
     */
    public function testToString()
    {
        $this->assertEquals('Calls', (string)$this->callEmitter);
    }

    /**
     * Data provider for testGetEventPrototypeByString
     *
     * @see CallEmitterTest::testGetEventPrototypeByString
     * @return array
     */
    public static function getEventPrototypeByStringProvider()
    {
        return array(
            array(
                'Some Event ' . rand(1000, 9999),
                'Some Result ' . rand(1000, 9999),
            ),
            array(
                'Another Event ' . rand(1000, 9999),
                'Another Result ' . rand(1000, 9999),
            ),
        );
    }

    /**
     * getEventPrototypeByString method should return result of ReminderEmitter
     *
     * @covers CallEmitter::getEventPrototypeByString
     * @dataProvider getEventPrototypeByStringProvider
     * @param string $string
     * @param string $result
     */
    public function testGetEventPrototypeByString($string, $result)
    {
        $this->reminderEmitter
            ->method('getEventPrototypeByString')
            ->with($this->equalTo($string))
            ->willReturn($result);

        $actual = $this->callEmitter->getEventPrototypeByString($string);
        $this->assertEquals($result, $actual);
    }

    /**
     * Data provider for getEventStringsProvider
     *
     * @see CallEmitterTest::getEventStringsProvider
     * @return array
     */
    public static function getEventStringsProvider()
    {
        return array(
            array(
                'Some Result ' . rand(1000, 9999),
            ),
            array(
                'Another Result ' . rand(1000, 9999),
            ),
        );
    }

    /**
     * getEventStrings method should return result of ReminderEmitter
     *
     * @covers CallEmitter::getEventStrings
     * @dataProvider getEventStringsProvider
     * @param string $result
     */
    public function testGetEventStrings($result)
    {
        $this->reminderEmitter
            ->method('getEventStrings')
            ->willReturn($result);

        $actual = $this->callEmitter->getEventStrings();
        $this->assertEquals($result, $actual);
    }

    /**
     * Data provider for testReminder
     *
     * @see CallEmitterTest::testReminder
     * @return array
     */
    public static function reminderProvider()
    {
        return array(
            'CallBean' => array(
                new \Call(),
                new \User(),
            ),
            'MeetingBean' => array(
                new \Meeting(),
                new \User(),
            ),
        );
    }
    /**
     * reminder method should call method of ReminderEmitter
     *
     * @covers CallEmitter::reminder
     * @dataProvider reminderProvider
     * @param \SugarBean $bean
     * @param \User $user
     */
    public function testReminder(\SugarBean $bean, \User $user)
    {
        $this->reminderEmitter
            ->expects($this->once())
            ->method('reminder')
            ->with($this->equalTo($bean), $this->equalTo($user));

        $this->callEmitter->reminder($bean, $user);
    }
}
