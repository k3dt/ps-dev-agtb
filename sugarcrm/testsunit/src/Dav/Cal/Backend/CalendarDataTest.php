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

namespace Sugarcrm\SugarcrmTestsUnit\Dav\Cal\Backend;

use Sabre\CalDAV;

/**
 * Class DataTest
 * @package            Sugarcrm\SugarcrmTestsUnit\Dav\Cal\Backend
 *
 * @coversDefaultClass Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData
 */
class CalendarDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Mock for \CalDavScheduling
     * @var
     */
    protected $schedulingMock;

    /**
     * Mock for \User
     * @var
     */
    protected $userMock;

    public function createCalendarObjectProvider()
    {
        return array(
            array(
                'calendarUri' => 'uri.isc',
                'calendarID' => 1,
                'content' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
uid:test
DTSTART;VALUE=DATE:20160101
END:VEVENT
END:VCALENDAR',
                'ETag' => '"c3d48c3c99615a99a764be4fc95c9ca9"',
                'parentModule' => null,

            ),
            array(
                'calendarUri' => 'uri.isc',
                'calendarID' => 1,
                'content' => 'BEGIN:VCALENDAR
X-PARENT-UID:asdf
BEGIN:VEVENT
uid:test
DTSTART;VALUE=DATE:20160101
END:VEVENT
END:VCALENDAR',
                'ETag' => '"3d3b3262af858a955b7591d972706ff5"',
                'parentModule' => 'CalDavEvents',

            ),
        );
    }

    public function createUnsupportedCalendarObjectProvider()
    {
        return array(
            array(
                'content' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
uid:test
RRULE:FREQ=MONTHLY;BYMONTHDAY=17,18,19,22,27,30,-1
DTSTART;VALUE=DATE:20160101
END:VEVENT
END:VCALENDAR',
            ),
        );
    }

    public function updateCalendarObjectProvider()
    {
        return array(
            array(
                'calendarUri' => 'uri.isc',
                'calendarID' => 1,
                'content' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
uid:test
DTSTART;VALUE=DATE:20160101
END:VEVENT
END:VCALENDAR',
            ),
        );
    }

    public function createSchedulingObjectProvider()
    {
        return array(
            array(
                'principal' => 'principals/user',
                'objectUri' => 'uri.isc',
                'calendarData' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
uid:test
DTSTART;VALUE=DATE:20160101
END:VEVENT
END:VCALENDAR',
                'save' => 1,
            ),
            array(
                'principal' => 'principals/user',
                'objectUri' => 'uri.isc',
                'calendarData' => '',
                'save' => 0,
            ),
        );
    }

    public function getSchedulingObjectsProvider()
    {
        return array(
            array(
                'principal' => 'principals/user',
                'found' => array(
                    'a1' => array('id' => 'a1'),
                    'a2' => array('id' => 'a2'),
                )
            ),
        );
    }

    public function getSchedulingObjectProvider()
    {
        return array(
            array(
                'principal' => 'principals/user',
                'uri' => 'test.ics',
                'found' => array('id' => 'a1'),
            )
        );
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::deleteCalendar
     *
     * @expectedException \Sabre\DAV\Exception\Forbidden
     */
    public function testDeleteCalendar()
    {
        $calendarMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData')
                              ->disableOriginalConstructor()
                              ->setMethods(null)
                              ->getMock();

        $calendarMock->deleteCalendar(1);
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::createCalendar
     *
     * @expectedException \Sabre\DAV\Exception\Forbidden
     */
    public function testCreateCalendar()
    {
        $calendarMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData')
                              ->disableOriginalConstructor()
                              ->setMethods(null)
                              ->getMock();


        $calendarMock->createCalendar('principals/testuser', 'testcalendar', array());
    }

    /**
     * @param string $calendarId
     * @param string $objectUri
     * @param string $calendarData
     * @param string $expectedETag
     * @param string $expectedParentModule
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::createCalendarObject
     *
     * @dataProvider createCalendarObjectProvider
     */
    public function testCreateCalendarObject(
        $calendarId,
        $objectUri,
        $calendarData,
        $expectedETag,
        $expectedParentModule
    ) {
        $calendarMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData')
                             ->disableOriginalConstructor()
                             ->setMethods(array('getEventsBean'))
                             ->getMock();

        $eventMock = $this->getMockBuilder('CalDavEventCollection')
                          ->disableOriginalConstructor()
                          ->setMethods(array('save', 'setCalendarEventURI', 'setCalendarId', 'getSynchronizationObject'))
                          ->getMock();

        $eventMock->expects($this->once())->method('setCalendarEventURI')->with($objectUri);
        $eventMock->expects($this->once())->method('setCalendarId')->with($calendarId);

        $calendarMock->expects($this->once())->method('getEventsBean')->willReturn($eventMock);

        $result = $calendarMock->createCalendarObject($calendarId, $objectUri, $calendarData);

        $this->assertEquals($expectedETag, $result);
        $this->assertEquals($expectedParentModule, $eventMock->parent_type);
    }

    /**
     * @param string $calendarData
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::createCalendarObject
     *
     * @dataProvider createUnsupportedCalendarObjectProvider
     *
     * @expectedException \Sabre\DAV\Exception\NotImplemented
     */
    public function testCreateUnsupportedCalendarObject($calendarData)
    {
        $calendarMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData')
                             ->disableOriginalConstructor()
                             ->setMethods(null)
                             ->getMock();

        $calendarMock->createCalendarObject(1, 'uri', $calendarData);
    }

    /**
     * @param string $calendarData
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::updateCalendarObject
     *
     * @dataProvider createUnsupportedCalendarObjectProvider
     *
     * @expectedException \Sabre\DAV\Exception\NotImplemented
     */
    public function testUpdateUnsupportedCalendarObject($calendarData)
    {
        $calendarMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData')
                             ->disableOriginalConstructor()
                             ->setMethods(null)
                             ->getMock();

        $calendarMock->updateCalendarObject(1, 'uri', $calendarData);
    }

    /**
     * @param $calendarId
     * @param $objectUri
     * @param $calendarData
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::updateCalendarObject
     *
     * @dataProvider updateCalendarObjectProvider
     */
    public function testUpdateCalendarObject($calendarId, $objectUri, $calendarData)
    {
        $calendarMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData')
                              ->disableOriginalConstructor()
                              ->setMethods(array('getEventsBean'))
                              ->getMock();

        $eventMock = $this->getMockBuilder('CalDavEventCollection')
                          ->disableOriginalConstructor()
                          ->setMethods(array('save', 'getByURI', 'getSynchronizationObject'))
                          ->getMock();

        $eventMock->id = $calendarId;

        $calendarMock->expects($this->once())->method('getEventsBean')->willReturn($eventMock);

        $eventMock->expects($this->once())->method('save');
        $eventMock->expects($this->once())->method('getByURI')->willReturn($eventMock);

        $calendarMock->updateCalendarObject($calendarId, $objectUri, $calendarData);
    }

    /**
     * @param string $principalUri
     * @param string $objectUri
     * @param string $calendarData
     * @param int $saveCallCount
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::createSchedulingObject
     *
     * @dataProvider createSchedulingObjectProvider
     */
    public function testCreateSchedulingObject($principalUri, $objectUri, $calendarData, $saveCallCount)
    {
        $calendarMock = $this->setUpSchedulingMocks($principalUri);

        $this->schedulingMock->expects($this->exactly($saveCallCount))->method('save');

        $calendarMock->createSchedulingObject($principalUri, $objectUri, $calendarData);
    }

    /**
     * @param string $principalUri
     * @param array $foundBeans
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::getSchedulingObjects
     *
     * @dataProvider getSchedulingObjectsProvider
     */
    public function testGetSchedulingObjects($principalUri, array $foundBeans)
    {
        $calendarMock = $this->setUpSchedulingMocks($principalUri);

        $beans = array();
        foreach ($foundBeans as $key => $value) {
            $tmpMock = $this->getMockBuilder('\CalDavScheduling')
                            ->disableOriginalConstructor()
                            ->setMethods(array('toCalDavArray'))
                            ->getMock();
            $tmpMock->id = $value['id'];
            $tmpMock->expects($this->once())->method('toCalDavArray');
            $beans[$key] = $tmpMock;

        }

        $this->schedulingMock->expects($this->once())->method('getByAssigned')->with($this->userMock->id)
                             ->willReturn($beans);

        $calendarMock->getSchedulingObjects($principalUri);
    }

    /**
     * @param string $principalUri
     * @param string $objectUri
     * @param array $foundBean
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::getSchedulingObject
     *
     * @dataProvider getSchedulingObjectProvider
     */
    public function testGetSchedulingObject($principalUri, $objectUri, array $foundBean)
    {
        $calendarMock = $this->setUpSchedulingMocks($principalUri);

        $tmpMock = $this->getMockBuilder('\CalDavScheduling')
                        ->disableOriginalConstructor()
                        ->setMethods(array('toCalDavArray'))
                        ->getMock();
        $tmpMock->id = $foundBean['id'];
        $tmpMock->expects($this->once())->method('toCalDavArray');

        $this->schedulingMock->expects($this->once())
                             ->method('getByUri')
                             ->with($objectUri, $this->userMock->id)
                             ->willReturn($tmpMock);

        $calendarMock->getSchedulingObject($principalUri, $objectUri);
    }

    /**
     * @param string $principalUri
     * @param string $objectUri
     * @param array $foundBean
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::deleteSchedulingObject
     *
     * @dataProvider getSchedulingObjectProvider
     */
    public function testDeleteSchedulingObject($principalUri, $objectUri, array $foundBean)
    {
        $calendarMock = $this->setUpSchedulingMocks($principalUri);

        $tmpMock = $this->getMockBuilder('\CalDavScheduling')
                        ->disableOriginalConstructor()
                        ->setMethods(array('toCalDavArray', 'mark_deleted'))
                        ->getMock();
        $tmpMock->id = $foundBean['id'];
        $tmpMock->expects($this->once())->method('mark_deleted')->with($tmpMock->id);

        $this->schedulingMock->expects($this->once())
                             ->method('getByUri')
                             ->with($objectUri, $this->userMock->id)
                             ->willReturn($tmpMock);

        $calendarMock->deleteSchedulingObject($principalUri, $objectUri);
    }

    /**
     * Setup base mock for scheduling
     * @param string $principalUri
     * @return \Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData
     */
    public function setUpSchedulingMocks($principalUri)
    {
        $calendarMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData')
                             ->disableOriginalConstructor()
                             ->setMethods(array('getUserHelper', 'getSchedulingBean'))
                             ->getMock();

        $userHelperMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\UserHelper')
                               ->disableOriginalConstructor()
                               ->setMethods(array('getUserByPrincipalString'))
                               ->getMock();

        $this->userMock = $this->getMockBuilder('\User')
                               ->disableOriginalConstructor()
                               ->setMethods(null)
                               ->getMock();

        $userHelperMock->expects($this->once())
                       ->method('getUserByPrincipalString')
                       ->with($principalUri)
                       ->willReturn($this->userMock);

        $this->userMock->id = 1;

        $this->schedulingMock = $this->getMockBuilder('\CalDavScheduling')
                                     ->disableOriginalConstructor()
                                     ->setMethods(array('save', 'getByUri', 'getByAssigned', 'mark_deleted'))
                                     ->getMock();

        $calendarMock->expects($this->once())->method('getUserHelper')->willReturn($userHelperMock);
        $calendarMock->expects($this->once())->method('getSchedulingBean')->willReturn($this->schedulingMock);

        return $calendarMock;
    }
}
