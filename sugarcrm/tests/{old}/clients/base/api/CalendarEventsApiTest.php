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
use Sugarcrm\Sugarcrm\SugarConnect\Configuration\Configuration as SugarConnectConfiguration;
use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * @group api
 * @group calendarevents
 * @coversDefaultClass CalendarEventsApi
 */
class CalendarEventsApiTest extends TestCase
{
    private $api;
    private $calendarEventsApi;

    private $meetingIds = [];

    protected function setUp() : void
    {
        SugarTestHelper::setUp('current_user');
        $this->meetingIds = [];

        $this->api = SugarTestRestUtilities::getRestServiceMock();
        $this->api->user = $GLOBALS['current_user'];
        $GLOBALS['current_user'] = $this->api->user;
        $this->calendarEventsApi = new CalendarEventsApi();
    }

    protected function tearDown() : void
    {
        BeanFactory::setBeanClass('Meetings');
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
        if (!empty($this->meetingIds)) {
            $ids = implode("','", $this->meetingIds);
            $GLOBALS['db']->query("DELETE FROM meetings WHERE id IN ('" . $ids . "')");
            $this->meetingIds = [];
        }
    }

    public function testCreateMeeting_EmailAddressesArgumentIsMapped()
    {
        $config = new SugarConnectConfiguration();
        $config->enable();

        $contactId = Uuid::uuid1();
        $meeting = BeanFactory::newBean('Meetings');

        $args = [
            'module' => 'Meetings',
            'name' => 'Test Meeting',
            'date_start' => $this->dateTimeAsISO('2014-12-25 13:00:00'),
            'date_end' => $this->dateTimeAsISO('2014-12-25 14:30:00'),
            'duration_hours' => 1,
            'duration_minutes' => 30,
            'email_addresses' => [
                'create' => [
                    [
                        'email_address' => 'foo@bar.com',
                    ],
                ],
            ],
        ];

        $mock = $this->getMockForCalendarEventsApi(
            [
                'getAttendees',
                'convertEmailAddressToPerson',
                'saveBean',
                'reloadBean',
                'linkRelatedRecords',
            ]
        );
        $mock->expects($this->once())->method('getAttendees')->willReturn([]);
        $mock->expects($this->once())
            ->method('convertEmailAddressToPerson')
            ->willReturn(
                [
                    'bean_module' => 'Contacts',
                    'bean_id' => $contactId,
                    'email_address' => 'foo@bar.com',
                ]
            );
        $mock->expects($this->once())
            ->method('linkRelatedRecords')
            ->with(
                $this->api,
                $this->isInstanceOf('SugarBean'),
                [
                    'contacts' => [
                        $contactId,
                    ],
                ],
                'create',
                'view'
            );
        $mock->expects($this->once())->method('saveBean');
        $mock->expects($this->once())->method('reloadBean')->willReturn($meeting);

        $bean = $mock->createBean($this->api, $args);

        $this->assertSame($meeting, $bean);
    }

    public function testCreateMeeting_EmailAddressesArgumentIsNotMapped()
    {
        $config = new SugarConnectConfiguration();
        $config->disable();

        $contactId = Uuid::uuid1();
        $meeting = BeanFactory::newBean('Meetings');

        $args = [
            'module' => 'Meetings',
            'name' => 'Test Meeting',
            'date_start' => $this->dateTimeAsISO('2014-12-25 13:00:00'),
            'date_end' => $this->dateTimeAsISO('2014-12-25 14:30:00'),
            'duration_hours' => 1,
            'duration_minutes' => 30,
            'email_addresses' => [
                'create' => [
                    [
                        'email_address' => 'foo@bar.com',
                    ],
                ],
            ],
        ];

        $mock = $this->getMockForCalendarEventsApi(
            [
                'getAttendees',
                'convertEmailAddressToPerson',
                'saveBean',
                'reloadBean',
                'linkRelatedRecords',
            ]
        );
        $mock->expects($this->never())->method('getAttendees');
        $mock->expects($this->never())->method('convertEmailAddressToPerson');
        $mock->expects($this->once())
            ->method('linkRelatedRecords')
            ->with(
                $this->api,
                $this->isInstanceOf('SugarBean'),
                [],
                'create',
                'view'
            );
        $mock->expects($this->once())->method('saveBean');
        $mock->expects($this->once())->method('reloadBean')->willReturn($meeting);

        $bean = $mock->createBean($this->api, $args);

        $this->assertSame($meeting, $bean);
    }

    public function testDeleteRecord_NotRecurringMeeting_CallsDeleteMethod()
    {
        $calendarEventsApiMock = $this->getMockForCalendarEventsApi(
            ['deleteRecord', 'deleteRecordAndRecurrences']
        );
        $calendarEventsApiMock->expects($this->once())
            ->method('deleteRecord');
        $calendarEventsApiMock->expects($this->never())
            ->method('deleteRecordAndRecurrences');

        $mockMeeting = $this->createPartialMock('Meeting', ['ACLAccess']);
        $mockMeeting->expects($this->any())
            ->method('ACLAccess')
            ->will($this->returnValue(true));

        BeanFactory::setBeanClass('Meetings', get_class($mockMeeting));

        $mockMeeting->id = create_guid();
        BeanFactory::registerBean($mockMeeting);

        $args = [
            'module' => 'Meetings',
            'record' => $mockMeeting->id,
        ];

        $calendarEventsApiMock->deleteCalendarEvent($this->api, $args);

        BeanFactory::unregisterBean($mockMeeting);
    }

    public function testDeleteRecord_RecurringMeeting_CallsDeleterRecurrenceMethod()
    {
        $calendarEventsApiMock = $this->getMockForCalendarEventsApi(
            ['deleteRecord', 'deleteRecordAndRecurrences']
        );
        $calendarEventsApiMock->expects($this->never())
            ->method('deleteRecord');
        $calendarEventsApiMock->expects($this->once())
            ->method('deleteRecordAndRecurrences');

        $mockMeeting = $this->createPartialMock('Meeting', ['ACLAccess']);
        $mockMeeting->expects($this->any())
            ->method('ACLAccess')
            ->will($this->returnValue(true));

        BeanFactory::setBeanClass('Meetings', get_class($mockMeeting));

        $mockMeeting->id = create_guid();
        BeanFactory::registerBean($mockMeeting);

        $args = [
            'module' => 'Meetings',
            'record' => $mockMeeting->id,
            'all_recurrences' => 'true',
        ];

        $calendarEventsApiMock->deleteCalendarEvent($this->api, $args);

        BeanFactory::unregisterBean($mockMeeting);
    }

    public function testDeleteRecordAndRecurrences_NoAccess_ThrowsException()
    {
        $mockMeeting = $this->getMockBuilder('Meeting')->setMethods(['ACLAccess'])->getMock();
        $mockMeeting->expects($this->any())
            ->method('ACLAccess')
            ->will($this->returnValue(false));

        BeanFactory::setBeanClass('Meetings', get_class($mockMeeting));

        $mockMeeting->id = create_guid();
        BeanFactory::registerBean($mockMeeting);

        $args = [
            'module' => 'Meetings',
            'record' => $mockMeeting->id,
        ];

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->calendarEventsApi->deleteRecordAndRecurrences($this->api, $args);
    }

    public function testDeleteRecordAndRecurrences_RetrievesParentRecord_DeletesAllMeetings()
    {
        $parentMeeting = SugarTestMeetingUtilities::createMeeting('', $this->api->user);

        $meeting1 = SugarTestMeetingUtilities::createMeeting('', $this->api->user);
        $meeting1->repeat_parent_id = $parentMeeting->id;
        $meeting1->save();

        $meeting2 = SugarTestMeetingUtilities::createMeeting('', $this->api->user);
        $meeting2->repeat_parent_id = $parentMeeting->id;
        $meeting2->save();

        $args = [
            'module' => 'Meetings',
            'record' => $meeting1->id,
        ];

        $results = $this->calendarEventsApi->deleteRecordAndRecurrences($this->api, $args);

        $this->assertEquals(
            $parentMeeting->id,
            $results['id'],
            'The return id of the delete call should be the parent meeting id'
        );

        $parentMeeting = BeanFactory::getBean('Meetings', $parentMeeting->id);
        $meeting1 = BeanFactory::getBean('Meetings', $meeting1->id);
        $meeting2 = BeanFactory::getBean('Meetings', $meeting2->id);

        $this->assertEquals($parentMeeting->deleted, 0, 'The parent meeting record should be deleted');
        $this->assertEquals($meeting1->deleted, 0, 'The meeting1 record should be deleted');
        $this->assertEquals($meeting2->deleted, 0, 'The meeting2 record should be deleted');
    }

    public function dataProviderForCheckRequiredParams_ApiMethods_ExceptionThrownIfMissing()
    {
        $dateStart = $this->dateTimeAsISO('2014-08-01 14:30:00');
        return [
            [
                "createRecord",
                [
                    'duration_hours' => '9',
                    'duration_minutes' => '9',
                ],
            ],
            [
                "createRecord",
                [
                    'date_start' => $dateStart,
                    'duration_minutes' => '9',
                ],
            ],
            [
                "createRecord",
                [
                    'date_start' => $dateStart,
                    'duration_hours' => '9',
                ],
            ],
            [
                "updateCalendarEvent",
                [
                    'duration_hours' => '9',
                    'duration_minutes' => '9',
                ],
            ],
            [
                "updateCalendarEvent",
                [
                    'date_start' => $dateStart,
                    'duration_minutes' => '9',
                ],
            ],
            [
                "updateCalendarEvent",
                [
                    'date_start' => $dateStart,
                    'duration_hours' => '9',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForCheckRequiredParams_ApiMethods_ExceptionThrownIfMissing
     * @param $args
     */
    public function testRequiredArgsPresent_MissingArgument_ExceptionThrown($apiMethod, $args)
    {
        $this->expectException(SugarApiExceptionMissingParameter::class);
        $this->calendarEventsApi->$apiMethod($this->api, $args);
    }

    public function testCreateRecord_NotRecurringMeeting_CallsCreateMethod()
    {
        $calendarEventsApiMock = $this->getMockForCalendarEventsApi(
            ['createRecord', 'generateRecurringCalendarEvents']
        );
        $calendarEventsApiMock->expects($this->once())
            ->method('createRecord');
        $calendarEventsApiMock->expects($this->never())
            ->method('generateRecurringCalendarEvents');

        $args = [
            'module' => 'Meetings',
            'date_start' => $this->dateTimeAsISO('2014-12-25 13:00:00'),
            'duration_hours' => '1',
            'duration_minutes' => '30',
        ];

        $calendarEventsApiMock->createRecord($this->api, $args);
    }

    public function testCreateRecord_RecurringMeeting_NoTerminationArgs_ThrowsException()
    {
        $id = create_guid();
        $this->meetingIds[] = $id;

        $args = [];
        $args['module'] = 'Meetings';
        $args['id'] = $id;
        $args['name'] = 'Test Meetings';
        $args['repeat_type'] = 'Daily';
        $args['date_start'] = $this->dateTimeAsISO('2014-12-25 13:00:00');
        $args['date_end'] = $this->dateTimeAsISO('2014-12-25 14:30:00');
        $args['duration_hours'] = 1;
        $args['duration_minutes'] = 30;

        /* Neither repeat_count nor repeat_until specified  - Error */

        $calendarEventsApiMock = $this->getMockForCalendarEventsApi(
            ['generateRecurringCalendarEvents']
        );
        $calendarEventsApiMock->expects($this->never())
            ->method('generateRecurringCalendarEvents');

        $this->expectException(SugarApiException::class);
        $calendarEventsApiMock->createBean($this->api, $args);
    }

    public function testCreateRecord_RecurringMeeting_ScheduleMeetingSeries_OK()
    {
        $id = create_guid();
        $this->meetingIds[] = $id;

        $args = [];
        $args['module'] = 'Meetings';
        $args['id'] = $id;
        $args['name'] = 'Test Meeting';
        $args['repeat_type'] = 'Daily';
        $args['repeat_interval'] = '1';
        $args['repeat_count'] = '3';
        $args['repeat_until'] = '';
        $args['repeat_dow'] = '';
        $args['repeat_parent_id'] = '';
        $args['date_start'] = $this->dateTimeAsISO('2014-12-25 13:00:00');
        $args['date_end'] = $this->dateTimeAsISO('2014-12-25 14:30:00');
        $args['duration_hours'] = 1;
        $args['duration_minutes'] = 30;

        $calendarEvents = new CalendarEventsApiTest_CalendarEvents();
        $calendarEventsApiMock = $this->getMockForCalendarEventsApi(['getCalendarEvents'], $calendarEvents);

        $result = $calendarEventsApiMock->createBean($this->api, $args);

        $this->assertFalse(empty($result->id), "createRecord API Failed to Create Meeting");

        $eventsCreated = $calendarEvents->getEventsCreated();
        $this->meetingIds = array_merge($this->meetingIds, array_keys($eventsCreated));

        $this->assertEquals($args['repeat_count'], count($eventsCreated) + 1, "Unexpected Number of Recurring Meetings");
    }

    public function testUpdateCalendarEvent_RecurringAndAllRecurrences_UpdatesAllRecurrences()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();

        $args = [
            'module' => 'Meetings',
            'record' => $meeting->id,
            'all_recurrences' => 'true',
            'date_start' => $this->dateTimeAsISO('2014-12-25 13:00:00'),
        ];

        $calendarEvents = $this->getMockForCalendarEventsIsEventRecurring(true);

        $calendarEventsApiMock = $this->getMockForCalendarEventsApiUpdate($calendarEvents);
        $calendarEventsApiMock->expects($this->any())
            ->method('loadBean')
            ->will($this->returnValue($meeting));
        $calendarEventsApiMock->expects($this->never())
            ->method('updateRecord');
        $calendarEventsApiMock->expects($this->once())
            ->method('updateRecurringCalendarEvent');

        $calendarEventsApiMock->updateCalendarEvent($this->api, $args);
    }

    public function testUpdateCalendarEvent_RecurringAndNotAllRecurrences_UpdatesSingleEventNoRecurrenceFields()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();

        $argsExpected = [
            'module' => 'Meetings',
            'record' => $meeting->id,
            'date_start' => $this->dateTimeAsISO('2014-12-25 13:00:00'),
        ];
        $args = array_merge($argsExpected, [
            'date_start' => $this->dateTimeAsISO('2014-12-25 13:00:00'),
            'repeat_type' => 'Daily',
            'repeat_interval' => '1',
            'repeat_count' => '5',
            'repeat_dow' => '',
            'repeat_until' => '',

        ]);

        $calendarEvents = $this->getMockForCalendarEventsIsEventRecurring(true);

        $calendarEventsApiMock = $this->getMockForCalendarEventsApiUpdate($calendarEvents);
        $calendarEventsApiMock->expects($this->any())
            ->method('loadBean')
            ->will($this->returnValue($meeting));
        $calendarEventsApiMock->expects($this->once())
            ->method('updateRecord')
            ->with($this->api, $argsExpected);
        $calendarEventsApiMock->expects($this->never())
            ->method('updateRecurringCalendarEvent');

        $calendarEventsApiMock->updateCalendarEvent($this->api, $args);
    }

    public function testUpdateCalendarEvent_NonRecurring_UpdatesSingleEvent()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();

        $args = [
            'module' => 'Meetings',
            'record' => $meeting->id,
            'date_start' => $this->dateTimeAsISO('2014-12-25 13:00:00'),
        ];

        $calendarEvents = $this->getMockForCalendarEventsIsEventRecurring(false);

        $calendarEventsApiMock = $this->getMockForCalendarEventsApiUpdate($calendarEvents);
        $calendarEventsApiMock->expects($this->any())
            ->method('loadBean')
            ->will($this->returnValue($meeting));
        $calendarEventsApiMock->expects($this->once())
            ->method('updateRecord');
        $calendarEventsApiMock->expects($this->never())
            ->method('updateRecurringCalendarEvent');
        $calendarEventsApiMock->expects($this->never())
            ->method('generateRecurringCalendarEvents');

        $calendarEventsApiMock->updateCalendarEvent($this->api, $args);
    }

    public function testUpdateCalendarEvent_NonRecurringChangedToRecurring_UpdatesEventGeneratesRecurring()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();

        $args = [
            'module' => 'Meetings',
            'record' => $meeting->id,
            'date_start' => $this->dateTimeAsISO('2014-12-25 13:00:00'),
        ];

        $calendarEvents = $this->getMockForCalendarEventsIsEventRecurring(false);
        //second time called will return true
        $calendarEvents->expects($this->at(1))
            ->method('isEventRecurring')
            ->will($this->returnValue(true));

        $calendarEventsApiMock = $this->getMockForCalendarEventsApiUpdate($calendarEvents);
        $calendarEventsApiMock->expects($this->any())
            ->method('loadBean')
            ->will($this->returnValue($meeting));
        $calendarEventsApiMock->expects($this->once())
            ->method('updateRecord');
        $calendarEventsApiMock->expects($this->never())
            ->method('updateRecurringCalendarEvent');
        $calendarEventsApiMock->expects($this->once())
            ->method('generateRecurringCalendarEvents');

        $calendarEventsApiMock->updateCalendarEvent($this->api, $args);
    }

    public function testUpdateRecurringCalendarEvent_RecurringAfterUpdate_SavesRecurringEvents()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();
        $meeting->repeat_parent_id = '';

        $args = [
            'module' => 'Meetings',
            'record' => $meeting->id,
            'date_start' => $this->dateTimeAsISO('2014-12-25 13:00:00'),
            'duration_hours' => '1',
            'duration_minutes' => '30',
        ];

        $calendarEvents = $this->getMockForCalendarEvents(
            ['isEventRecurring', 'saveRecurringEvents']
        );
        $calendarEvents->expects($this->any())
            ->method('isEventRecurring')
            ->will($this->returnValue(true));
        $calendarEvents->expects($this->once())
            ->method('saveRecurringEvents');

        $calendarEventsApiMock = $this->getMockForCalendarEventsApi(
            ['updateRecord', 'getLoadedAndFormattedBean'],
            $calendarEvents
        );
        $calendarEventsApiMock->expects($this->once())
            ->method('updateRecord');
        $calendarEventsApiMock->expects($this->once())
            ->method('getLoadedAndFormattedBean')
            ->will($this->returnValue([]));

        $calendarEventsApiMock->updateRecurringCalendarEvent($meeting, $this->api, $args);
    }

    public function testUpdateRecurringCalendarEvent_NonRecurringAfterUpdate_RemovesRecurringEvents()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();
        $meeting->repeat_parent_id = '';

        $args = [
            'module' => 'Meetings',
            'record' => $meeting->id,
            'date_start' => $this->dateTimeAsISO('2014-12-25 13:00:00'),
            'duration_hours' => '1',
            'duration_minutes' => '30',
        ];

        $calendarEvents = $this->getMockForCalendarEvents(
            ['isEventRecurring', 'saveRecurringEvents']
        );

        $calendarEvents->expects($this->any())
            ->method('isEventRecurring')
            ->will($this->returnValue(false));
        $calendarEvents->expects($this->never())
            ->method('saveRecurringEvents');

        $calendarEventsApiMock = $this->getMockForCalendarEventsApi(
            ['updateRecord', 'deleteRecurrences', 'getLoadedAndFormattedBean'],
            $calendarEvents
        );
        $calendarEventsApiMock->expects($this->once())
            ->method('updateRecord');
        $calendarEventsApiMock->expects($this->once())
            ->method('deleteRecurrences');
        $calendarEventsApiMock->expects($this->once())
            ->method('getLoadedAndFormattedBean')
            ->will($this->returnValue([]));

        $calendarEventsApiMock->updateRecurringCalendarEvent($meeting, $this->api, $args);
    }

    public function testUpdateRecurringCalendarEvent_UsingChildRecord_ThrowsException()
    {
        $meeting                   = BeanFactory::newBean('Meetings');
        $meeting->id               = create_guid();
        $meeting->repeat_parent_id = 'foo';

        $args = [
            'module'           => 'Meetings',
            'record'           => $meeting->id,
            'date_start'       => $this->dateTimeAsISO('2014-12-25 13:00:00'),
            'duration_hours'   => '1',
            'duration_minutes' => '30',
        ];

        $calendarEventsApiMock = $this->getMockForCalendarEventsApi(
            ['updateRecord']
        );
        $calendarEventsApiMock->expects($this->never())
            ->method('updateRecord');

        $this->expectException(SugarApiException::class);
        $calendarEventsApiMock->updateRecurringCalendarEvent(
            $meeting,
            $this->api,
            $args
        );
    }

    public function testCreateRecord_CreateRecordFails_rebuildFBCacheNotInvoked()
    {
        $calendarEventsApiMock = $this->getMockForCalendarEventsApi(
            ['createRecord',]
        );
        $calendarEventsApiMock->expects($this->once())
            ->method('createRecord')
            ->will($this->returnValue([]));

        $args = [
            'module' => 'Meetings',
            'date_start' => $this->dateTimeAsISO('2014-12-25 13:00:00'),
            'duration_hours' => '1',
            'duration_minutes' => '30',
        ];

        $calendarEventsApiMock->createRecord($this->api, $args);
    }

    public function testCreateRecord_NotRecurring_rebuildFBCacheInvoked()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();

        $this->meetingIds[] = $meeting->id;

        $calendarEvents = $this->getMockForCalendarEvents(
            ['isEventRecurring', 'rebuildFreeBusyCache']
        );

        $calendarEvents->expects($this->any())
            ->method('isEventRecurring')
            ->will($this->returnValue(false));
        $calendarEvents->expects($this->once())
            ->method('rebuildFreeBusyCache');

        $calendarEventsApiMock = $this->getMockForCalendarEventsApi(
            ['loadBean', 'generateRecurringCalendarEvents'],
            $calendarEvents
        );
        $calendarEventsApiMock->method('loadBean')
                              ->will($this->returnValue($meeting));
        $calendarEventsApiMock->expects($this->never())
            ->method('generateRecurringCalendarEvents');

        $args = [
            'module' => 'Meetings',
            'date_start' => $this->dateTimeAsISO('2014-12-25 13:00:00'),
            'duration_hours' => '1',
            'duration_minutes' => '30',
        ];
        $calendarEventsApiMock->createRecord($this->api, $args);
    }

    public function testCreateRecord_Recurring_rebuildFBCacheNotInvoked()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();

        $this->meetingIds[] = $meeting->id;

        $calendarEvents = $this->getMockForCalendarEvents(
            ['isEventRecurring', 'rebuildFreeBusyCache']
        );

        $calendarEvents->expects($this->any())
            ->method('isEventRecurring')
            ->will($this->returnValue(true));
        $calendarEvents->expects($this->never())
            ->method('rebuildFreeBusyCache');

        $calendarEventsApiMock = $this->getMockForCalendarEventsApi(
            ['loadBean', 'generateRecurringCalendarEvents'],
            $calendarEvents
        );
        $calendarEventsApiMock->method('loadBean')
                              ->will($this->returnValue($meeting));
        $calendarEventsApiMock->expects($this->once())
            ->method('generateRecurringCalendarEvents');

        $args = [
            'module' => 'Meetings',
            'date_start' => $this->dateTimeAsISO('2014-12-25 13:00:00'),
            'duration_hours' => '1',
            'duration_minutes' => '30',
        ];
        $calendarEventsApiMock->createRecord($this->api, $args);
    }

    public function testUpdateCalendarEvent_EventIdMissing_rebuildFBCacheNotInvoked()
    {
        $this->expectException(SugarApiExceptionMissingParameter::class);
        $this->calendarEventsApi->updateCalendarEvent($this->api, []);
    }

    public function testUpdateCalendarEvent_EventNotFound_rebuildFBCacheNotInvoked()
    {
        $args = [];
        $args['module'] = 'Meetings';
        $args['record'] = create_guid();
        $args['date_start'] = $this->dateTimeAsISO('2014-12-25 13:00:00');

        $this->expectException(SugarApiExceptionNotFound::class);
        $this->calendarEventsApi->updateCalendarEvent($this->api, $args);
    }

    public function testUpdateCalendarEvent_isRecurringAndAllRecurrences_rebuildFBCacheNotInvoked()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();

        $args = [
            'module' => 'Meetings',
            'record' => $meeting->id,
            'date_start' => $this->dateTimeAsISO('2014-12-25 13:00:00'),
            'all_recurrences' => 'true',
        ];

        $calendarEvents = $this->getMockForCalendarEvents(
            ['isEventRecurring', 'rebuildFreeBusyCache']
        );

        $calendarEvents->expects($this->any())
            ->method('isEventRecurring')
            ->will($this->returnValue(true));
        $calendarEvents->expects($this->never())
            ->method('rebuildFreeBusyCache');

        $calendarEventsApiMock = $this->getMockForCalendarEventsApiUpdate($calendarEvents);
        $calendarEventsApiMock->expects($this->any())
            ->method('loadBean')
            ->will($this->returnValue($meeting));
        $calendarEventsApiMock->expects($this->once())
            ->method('updateRecurringCalendarEvent')
            ->will($this->returnValue([]));

        $calendarEventsApiMock->updateCalendarEvent($this->api, $args);
    }

    public function testUpdateCalendarEvent_isRecurringAndNotAllRecurrences_rebuildFBCacheInvoked()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();

        $args = [
            'module' => 'Meetings',
            'record' => $meeting->id,
            'date_start' => $this->dateTimeAsISO('2014-12-25 13:00:00'),
            'all_recurrences' => 'false',
        ];

        $calendarEvents = $this->getMockForCalendarEvents(
            ['isEventRecurring', 'rebuildFreeBusyCache']
        );

        $calendarEvents->expects($this->any())
            ->method('isEventRecurring')
            ->will($this->returnValue(true));
        $calendarEvents->expects($this->once())
            ->method('rebuildFreeBusyCache');

        $calendarEventsApiMock = $this->getMockForCalendarEventsApiUpdate($calendarEvents);
        $calendarEventsApiMock->expects($this->any())
            ->method('loadBean')
            ->will($this->returnValue($meeting));
        $calendarEventsApiMock->expects($this->once())
            ->method('updateRecord')
            ->will($this->returnValue([]));

        $calendarEventsApiMock->updateCalendarEvent($this->api, $args);
    }

    public function testUpdateCalendarEvent_NonRecurringChangedToRecurring_rebuildFBCacheNotInvoked()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();

        $args = [
            'module' => 'Meetings',
            'record' => $meeting->id,
            'date_start' => $this->dateTimeAsISO('2014-12-25 13:00:00'),
        ];

        $calendarEvents = $this->getMockForCalendarEvents(
            ['isEventRecurring', 'rebuildFreeBusyCache']
        );

        //first time called will return false
        $calendarEvents->expects($this->at(0))
            ->method('isEventRecurring')
            ->will($this->returnValue(false));
        //second time called will return true
        $calendarEvents->expects($this->at(1))
            ->method('isEventRecurring')
            ->will($this->returnValue(true));
        $calendarEvents->expects($this->never())
            ->method('rebuildFreeBusyCache');

        $calendarEventsApiMock = $this->getMockForCalendarEventsApiUpdate($calendarEvents);
        $calendarEventsApiMock->expects($this->any())
            ->method('loadBean')
            ->will($this->returnValue($meeting));
        $calendarEventsApiMock->expects($this->once())
            ->method('updateRecord');
        $calendarEventsApiMock->expects($this->never())
            ->method('updateRecurringCalendarEvent');
        $calendarEventsApiMock->expects($this->once())
            ->method('generateRecurringCalendarEvents');

        $calendarEventsApiMock->updateCalendarEvent($this->api, $args);
    }

    public function testUpdateCalendarEvent_NonRecurring_rebuildFBCacheInvoked()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();

        $args = [
            'module' => 'Meetings',
            'record' => $meeting->id,
            'date_start' => $this->dateTimeAsISO('2014-12-25 13:00:00'),
        ];

        $calendarEvents = $this->getMockForCalendarEvents(
            ['isEventRecurring', 'rebuildFreeBusyCache']
        );

        //first time called will return false
        $calendarEvents->expects($this->exactly(2))
            ->method('isEventRecurring')
            ->will($this->returnValue(false));
        $calendarEvents->expects($this->once())
            ->method('rebuildFreeBusyCache');

        $calendarEventsApiMock = $this->getMockForCalendarEventsApiUpdate($calendarEvents);
        $calendarEventsApiMock->expects($this->exactly(2))
            ->method('loadBean')
            ->will($this->returnValue($meeting));
        $calendarEventsApiMock->expects($this->once())
            ->method('updateRecord');
        $calendarEventsApiMock->expects($this->never())
            ->method('updateRecurringCalendarEvent');
        $calendarEventsApiMock->expects($this->never())
            ->method('generateRecurringCalendarEvents');

        $calendarEventsApiMock->updateCalendarEvent($this->api, $args);
    }

    public function testDeleteRecord_SingleOccurrence_rebuildFBCacheNotInvoked()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();

        $args = [
            'module' => 'Meetings',
            'record' => $meeting->id,
            'all_recurrences' => 'false',
        ];

        $calendarEvents = $this->getMockForCalendarEvents(
            ['rebuildFreeBusyCache']
        );

        $calendarEvents->expects($this->never())
            ->method('rebuildFreeBusyCache');

        $calendarEventsApiMock = $this->getMockForCalendarEventsApi(
            ['deleteRecord', 'deleteRecordAndRecurrences'],
            $calendarEvents
        );
        $calendarEventsApiMock->expects($this->once())
            ->method('deleteRecord');
        $calendarEventsApiMock->expects($this->never())
            ->method('deleteRecordAndRecurrences');

        $calendarEventsApiMock->deleteCalendarEvent($this->api, $args);
    }

    public function testDeleteRecord_AllOccurrences_rebuildFBCacheNotInvoked()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();

        $args = [
            'module' => 'Meetings',
            'record' => $meeting->id,
            'all_recurrences' => 'true',
        ];

        $calendarEvents = $this->getMockForCalendarEvents(
            ['rebuildFreeBusyCache']
        );

        $calendarEvents->expects($this->never())
            ->method('rebuildFreeBusyCache');

        $calendarEventsApiMock = $this->getMockForCalendarEventsApi(
            ['deleteRecord', 'deleteRecordAndRecurrences'],
            $calendarEvents
        );
        $calendarEventsApiMock->expects($this->never())
            ->method('deleteRecord');
        $calendarEventsApiMock->expects($this->once())
            ->method('deleteRecordAndRecurrences');

        $calendarEventsApiMock->deleteCalendarEvent($this->api, $args);
    }

    public function dataProviderForShouldAutoInviteParent()
    {
        $meetingId = '123';
        $parentType = 'Contacts';
        $parentId1 = '456';
        $parentId2 = '789';

        return [
            [
                [
                    'id' => $meetingId,
                ],
                [
                    'auto_invite_parent' => false,
                ],
                false,
                'should be false when auto_invite_parent flag is false on create',
            ],
            [
                [
                    'id' => $meetingId,
                    'parent_type' => $parentType,
                    'parent_id' => $parentId1,
                ],
                [
                    'id' => $meetingId,
                    'auto_invite_parent' => false,
                    'parent_type' => $parentType,
                    'parent_id' => $parentId2,
                ],
                false,
                'should be false when auto_invite_parent flag is false on update',
            ],
            [
                [
                    'id' => $meetingId,
                    'parent_type' => $parentType,
                    'parent_id' => $parentId1,
                ],
                [
                    'id' => $meetingId,
                    'parent_type' => $parentType,
                ],
                false,
                'should be false when parent id not set',
            ],
            [
                [
                    'id' => $meetingId,
                    'parent_type' => $parentType,
                    'parent_id' => $parentId1,
                ],
                [
                    'parent_type' => $parentType,
                    'parent_id' => $parentId1,
                ],
                true,
                'should be true when parent set on create',
            ],
            [
                [
                    'id' => $meetingId,
                    'parent_type' => $parentType,
                    'parent_id' => $parentId1,
                ],
                [
                    'id' => $meetingId,
                    'parent_type' => $parentType,
                    'parent_id' => $parentId2,
                ],
                true,
                'should be true when parent changed on update',
            ],
            [
                [
                    'id' => $meetingId,
                    'parent_type' => $parentType,
                    'parent_id' => $parentId1,
                ],
                [
                    'id' => $meetingId,
                    'parent_type' => $parentType,
                    'parent_id' => $parentId1,
                ],
                false,
                'should be false when parent not changed on update',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForShouldAutoInviteParent
     */
    public function testShouldAutoInviteParent($beanValues, $args, $expected, $message)
    {
        $bean = BeanFactory::newBean('Meetings');
        foreach ($beanValues as $field => $value) {
            $bean->$field = $value;
        }

        $actual = SugarTestReflection::callProtectedMethod($this->calendarEventsApi, 'shouldAutoInviteParent', [$bean, $args]);
        $this->assertEquals($expected, $actual, $message);
    }

    public function repeatUntilProvider()
    {
        return [
            ['Meetings'],
            ['Calls'],
        ];
    }

    /**
     * This test guarantees that meetings and calls can be created with a date (xxxx-xx-xx) as a repeat_until argument.
     * There should not be any exceptions, the parent event should be saved, and the repeat_until date should be
     * returned just as it was submitted.
     *
     * @covers ::createRecord
     * @covers ::createBean
     * @covers ::generateRecurringCalendarEvents
     * @dataProvider repeatUntilProvider
     */
    public function testRepeatUntil($module)
    {
        $timezone = new DateTimeZone('America/Los_Angeles');
        $GLOBALS['current_user']->setPreference('timezone', $timezone->getName());

        $start = new DateTime('2017-01-01 10:00:00', $timezone);
        $end = new DateTime('2017-01-01 10:30:00', $timezone);
        $until = new DateTime('2017-01-05 00:00:00', $timezone);

        // Stub CalendarEvents::saveRecurring() to avoid actually saving the beans. The return value isn't used, so
        // $repeatDateTimeArray is returned so that we return something that resembles the return value of
        // CalendarUtils::saveRecurring().
        $events = $this->getMockBuilder('CalendarEvents')
            ->setMethods(['saveRecurring'])
            ->getMock();
        $events->method('saveRecurring')
            ->willReturnArgument(1);

        $service = SugarTestRestUtilities::getRestServiceMock();
        $api = $this->getMockBuilder("{$module}Api")
            ->setMethods(['getCalendarEvents', 'loadBean', 'saveBean'])
            ->getMock();
        $api->method('getCalendarEvents')
            ->willReturn($events);
        $api->method('loadBean')
            ->willReturnCallback(function ($api, $args, $aclToCheck = 'view', $options = []) {
                $options['use_cache'] = true;
                return BeanFactory::retrieveBean($args['module'], $args['record'], $options);
            });

        $args = [
            'module' => $module,
            'name' => 'foo',
            'assigned_user_id' => $GLOBALS['current_user']->id,
            'duration_hours' => 0,
            'duration_minutes' => 30,
            'status' => 'Planned',
            'type' => 'Sugar',
            'reminder_time' => '-1',
            'email_reminder_time' => -1,
            'email_reminder_sent' => false,
            'sequence' => 0,
            'repeat_interval' => 1,
            'date_start' => $start->format('c'),
            'date_end' => $end->format('c'),
            'repeat_type' => 'Daily',
            'repeat_selector' => 'None',
            'repeat_ordinal' => 'first',
            'repeat_unit' => 'Sun',
            'auto_invite_parent' => false,
            'repeat_until' => $until->format('Y-m-d'),
            'team_id' => '1',
            'team_set_id' => '1',
        ];
        $record = $api->createRecord($service, $args);

        $this->assertEquals($args['repeat_until'], $record['repeat_until'], 'repeat_until should be the same date');
    }

    private function dateTimeAsISO($dbDateTime)
    {
        global $timedate;
        return $timedate->asIso($timedate->fromDB($dbDateTime));
    }

    private function getMockForCalendarEventsApiUpdate(CalendarEvents $calendarEvents)
    {
        return $this->getMockForCalendarEventsApi(
            [
                'updateRecord',
                'updateRecurringCalendarEvent',
                'loadBean',
                'generateRecurringCalendarEvents',
            ],
            $calendarEvents
        );
    }

    private function getMockForCalendarEventsApi(array $methodsArray = [], CalendarEvents $calendarEvents = null)
    {
        if (empty($calendarEvents)) {
            $calendarEvents = new CalendarEvents();
        }

        if (!in_array('getCalendarEvents', $methodsArray)) {
            $methodsArray[] = 'getCalendarEvents';
        }
        $calendarEventsApiMock = $this->getMockBuilder('CalendarEventsApi')
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->setMethods($methodsArray)
            ->getMock();

        $calendarEventsApiMock->expects($this->any())
            ->method('getCalendarEvents')
            ->will($this->returnValue($calendarEvents));

        return $calendarEventsApiMock;
    }

    private function getMockForCalendarEventsIsEventRecurring($isRecurring)
    {
        $calendarEvents = $this->getMockForCalendarEvents(
            ['isEventRecurring']
        );

        $calendarEvents->expects($this->at(0))
            ->method('isEventRecurring')
            ->will($this->returnValue($isRecurring));

        return $calendarEvents;
    }

    private function getMockForCalendarEvents($methodsArray = [])
    {
        $calendarEvents = $this->createPartialMock(
            'CalendarEvents',
            $methodsArray
        );

        return $calendarEvents;
    }
}

class CalendarEventsApiTest_CalendarEvents extends CalendarEvents
{
    protected $eventsCreated = [];

    public function getEventsCreated()
    {
        return $this->eventsCreated;
    }

    protected function saveRecurring(SugarBean $parentBean, array $repeatDateTimeArray)
    {
        $this->eventsCreated = parent::saveRecurring($parentBean, $repeatDateTimeArray);
    }
}
