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

class CalendarEventsTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $calendarEventsService;

    protected $meetingIds = array();

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        $this->calendarEventsService = new CalendarEvents();
        $this->meetingIds = array();
    }

    public function tearDown()
    {
        if (!empty($this->meetingIds)) {
            $ids = implode("','", $this->meetingIds);
            $GLOBALS['db']->query("DELETE FROM meetings_users WHERE meeting_id IN ('" . $ids . "')");
            $GLOBALS['db']->query("DELETE FROM meetings WHERE id IN ('" . $ids . "')");
            $this->meetingIds = array();
        }
        SugarTestMeetingUtilities::removeMeetingUsers();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestHelper::tearDown();
    }

    public function testCalendarEvents_Meeting_EventRecurring_NoRepeatType()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->repeat_type = null;
        $meeting->date_start = '2014-12-25 18:00:00';

        $result = $this->calendarEventsService->isEventRecurring($meeting);

        $this->assertFalse($result, "Expected Meeting Event to be Non-Recurring");
    }

    public function testCalendarEvents_Meeting_EventRecurring_NoDateStart()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->repeat_type = 'Daily';
        $meeting->date_start = null;

        $result = $this->calendarEventsService->isEventRecurring($meeting);

        $this->assertFalse($result, "Expected Meeting Event to be Non-Recurring");
    }

    public function testCalendarEvents_Meeting_EventRecurring_OK()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->repeat_type = 'Daily';
        $meeting->date_start = '2014-12-25 18:00:00';

        $result = $this->calendarEventsService->isEventRecurring($meeting);

        $this->assertTrue($result, "Expected Meeting Event to be recognized as Recurring");
    }

    /**
     * @expectedException SugarException
     */
    public function testCalendarEvents_Account_EventRecurring_UnsupportedCalendarEventModule()
    {
        $account = BeanFactory::newBean('Accounts');
        $this->calendarEventsService->isEventRecurring($account);
    }

    public function testCalendarEvents_NonRecurringMeeting_NoDuration_SetStartAndEndDate_OK()
    {
        $format = TimeDate::DB_DATETIME_FORMAT;
        $timezone = new DateTimeZone('UTC');

        $sugarDateTime = SugarDateTime::createFromFormat($format, '2015-01-01 12:00:00', $timezone);

        $meeting = BeanFactory::newBean('Meetings');
        $this->calendarEventsService->setStartAndEndDateTime($meeting, $sugarDateTime);

        $datetimeStart = SugarDateTime::createFromFormat($format, $meeting->date_start, $timezone);
        $datetimeEnd = SugarDateTime::createFromFormat($format, $meeting->date_end, $timezone);

        $this->assertEquals(0, (int) $meeting->duration_hours, "Expected Duration of Zero Hours");
        $this->assertEquals(0, (int) $meeting->duration_minutes, "Expected Duration of Zero Minutes");
        $this->assertEquals($datetimeStart->asDb(), $datetimeEnd->asDb(), "Expected End Datetime = Start DateTime");
    }

    public function testCalendarEvents_NonRecurringMeeting_SetStartAndEndDate_OK()
    {
        $format = TimeDate::DB_DATETIME_FORMAT;
        $timezone = new DateTimeZone('UTC');

        $sugarDateTime = SugarDateTime::createFromFormat($format, '2015-01-01 12:00:00', $timezone);

        $meeting = BeanFactory::newBean('Meetings');
        $meeting->duration_hours = 1;
        $meeting->duration_minutes = 30;
        $this->calendarEventsService->setStartAndEndDateTime($meeting, $sugarDateTime);

        $datetimeStart = SugarDateTime::createFromFormat($format, $meeting->date_start, $timezone);
        $datetimeEnd = SugarDateTime::createFromFormat($format, $meeting->date_end, $timezone);
        $meetingInterval = date_diff ($datetimeStart, $datetimeEnd);

        $this->assertEquals(1, $meetingInterval->h, "Incorrect Duration Hours - Non Recurring Meeting");
        $this->assertEquals(30, $meetingInterval->i, "Incorrect Duration Minutes - Non Recurring Meeting");
    }

    public function testCalendarEvents_WeeklyRecurringMeeting_SetStartAndEndDate_OK()
    {
        $format = TimeDate::DB_DATETIME_FORMAT;
        $timezone = new DateTimeZone('UTC');

        $sugarDateTime = SugarDateTime::createFromFormat($format, '2015-01-01 12:00:00', $timezone);
        $dow = intval($sugarDateTime->format("w")) + 1;  // Repeat Single Day Of Week (Using Next Day DOW)

        $meeting = BeanFactory::newBean('Meetings');
        $meeting->repeat_type = 'Weekly';
        $meeting->repeat_dow  = "{$dow}";

        $this->calendarEventsService->setStartAndEndDateTime($meeting, $sugarDateTime);

        $datetimeStart = SugarDateTime::createFromFormat($format, $meeting->date_start, $timezone);
        $interval = date_diff ($sugarDateTime, $datetimeStart);

        $diffDays = ($interval->days);
        $diffMinutes = ($interval->h * 3600) + ($interval->i * 60) + $interval->s;

        $this->assertEquals( 1 ,$diffDays, "Expected 1 Day Offset from Proposed to Actual Start");
        $this->assertEquals( 0, $diffMinutes, "Expected No Hour or Minute Time Difference");
    }

    public function testCalendarEvents_SaveRecurringEvents_EventsSaved()
    {
        $args['date_start'] = '2030-08-15 13:00:00';
        $args['date_end']   = '2030-08-15 18:15:00';
        $args['name'] = "Test Meeting";
        $args['duration_hours'] = '1';
        $args['duration_minutes'] = '30';
        $args['repeat_type'] = 'Daily';
        $args['repeat_interval'] = 1;
        $args['repeat_count'] = 3;
        $args['repeat_until'] = null;
        $args['repeat_dow'] = null;

        $meeting = $this->newMeeting('', $args);

        $calEvents = new CalendarEventsTest_CalendarEvents();
        $calEvents->saveRecurringEvents($meeting);

        $eventsCreated = $calEvents->getEventsCreated();
        foreach($eventsCreated as $eventCreated) {
            $this->meetingIds[] = $eventCreated['id'];
        }
        $this->assertEquals($args['repeat_count'], count($eventsCreated) + 1, "Unexpected Number of Recurring Meetings Created");
    }

    public function testCalendarEvents_SaveRecurringEventWithTags_TagsPropagateCorrectly()
    {
        $args['date_start'] = '2030-08-15 13:00:00';
        $args['date_end']   = '2030-08-15 18:15:00';
        $args['name'] = "Test Meeting";
        $args['duration_hours'] = '1';
        $args['duration_minutes'] = '30';
        $args['repeat_type'] = 'Daily';
        $args['repeat_interval'] = 1;
        $args['repeat_count'] = 3;
        $args['repeat_until'] = null;
        $args['repeat_dow'] = null;

        $meeting = $this->newMeeting('', $args);
        $parentTags = $this->addTags($meeting, 3);

        $calEvents = new CalendarEventsTest_CalendarEvents();
        $calEvents->saveRecurringEvents($meeting);

        $eventsCreated = $calEvents->getEventsCreated();
        foreach($eventsCreated as $eventCreated) {
            $this->meetingIds[] = $eventCreated['id'];
            $meeting = BeanFactory::getBean('Meetings', $eventCreated['id']);
            $meeting->load_relationship('tag_link');
            $tags = $meeting->tag_link->get();
            $tagIds = array();
            foreach($tags AS $tagId) {
                $tagIds[$tagId] = true;
            }
            foreach($parentTags as $parentTag) {
                $this->assertTrue(isset($tagIds[$parentTag->id]), "Child Meeting Missing Tag On Parent");
                unset($tagIds[$parentTag->id]);
            }
            $this->assertTrue(empty($tagIds), "Child Meeting Has Unexpected Tag");
        }
    }

    public function testCalendarEvents_SaveRecurringEvents_CurrentAssignedUserAutoAccepted()
    {
        global $current_user;
        $args['date_start'] = '2030-08-15 13:00:00';
        $args['date_end']   = '2030-08-15 18:15:00';
        $args['name'] = "Test Meeting";
        $args['duration_hours'] = '1';
        $args['duration_minutes'] = '30';
        $args['repeat_type'] = 'Daily';
        $args['repeat_interval'] = 1;
        $args['repeat_count'] = 2;
        $args['repeat_until'] = null;
        $args['repeat_dow'] = null;
        $args['assigned_user_id'] = $current_user->id;

        $meeting = $this->newMeeting('', $args);

        $calEvents = new CalendarEventsTest_CalendarEvents();
        $calEvents->saveRecurringEvents($meeting);

        $eventsCreated = $calEvents->getEventsCreated();
        foreach($eventsCreated as $eventCreated) {
            $this->meetingIds[] = $eventCreated['id'];
        }

        $parentMeetingAcceptStatus = $meeting->users->rows[$current_user->id]['accept_status'];

        $childMeeting = BeanFactory::getBean('Meetings', $eventsCreated[0]['id']);
        $childMeeting->load_relationship('users');
        $childMeeting->users->load();
        $childMeetingAcceptStatus = $childMeeting->users->rows[$current_user->id]['accept_status'];

        $this->assertEquals($parentMeetingAcceptStatus, 'accept', 'Current user should have auto-accepted in parent meeting');
        $this->assertEquals($childMeetingAcceptStatus, 'accept', 'Current user should have auto-accepted in child meeting');
    }

    public function testInviteParent_ParentIsContact_ShouldInviteButNotReInvite()
    {
        global $current_user;
        $args['name'] = "Test Meeting";
        $args['date_start'] = '2030-08-15 13:00:00';
        $args['duration_hours'] = '1';
        $args['duration_minutes'] = '30';
        $args['assigned_user_id'] = $current_user->id;

        $meeting = $this->newMeeting('', $args);
        $contact = SugarTestContactUtilities::createContact();

        $this->calendarEventsService->inviteParent($meeting, 'Contacts', $contact->id);
        $this->assertEquals(array($contact->id), $meeting->contacts->get(), 'should be linked to the one contact');

        // try inviting again
        $this->calendarEventsService->inviteParent($meeting, 'Contacts', $contact->id);
        $this->assertEquals(array($contact->id), $meeting->contacts->get(), 'should only have one link to the contact');

        SugarTestMeetingUtilities::removeMeetingContacts();
        SugarTestContactUtilities::removeAllCreatedContacts();
    }

    public function testInviteParent_ParentIsNotContactOrLead_ShouldNotInvite()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $this->calendarEventsService->inviteParent($meeting, 'Accounts', '123');
        $this->assertNull($meeting->accounts);
    }

    public function updateAcceptStatusForInviteePrimaryEventStatusProvider()
    {
        return array(
            array('Held'),
            array('Not Held'),
        );
    }

    /**
     * The primary event is not updated because it is either held or canceled. Any child events may still be updated.
     *
     * @dataProvider updateAcceptStatusForInviteePrimaryEventStatusProvider
     * @param $status
     */
    public function testUpdateAcceptStatusForInvitee_EventIsNotScheduled_OnlyChildEventsAreUpdated($status)
    {
        BeanFactory::setBeanClass('Meetings', 'MockMeeting');

        $meeting1 = $this->getMockBuilder('Meeting')
            ->disableOriginalConstructor()
            ->setMockClassName('MockMeeting')
            ->setMethods(array('set_accept_status'))
            ->getMock();
        $meeting1->id = create_guid();
        $meeting1->module_name = 'Meetings';
        $meeting1->status = $status;
        $meeting1->expects($this->never())->method('set_accept_status');
        BeanFactory::registerBean($meeting1);

        $meeting2 = $this->getMockBuilder('Meeting')
            ->disableOriginalConstructor()
            ->setMockClassName('MockMeeting')
            ->setMethods(array('set_accept_status'))
            ->getMock();
        $meeting2->id = create_guid();
        $meeting2->module_name = 'Meetings';
        $meeting2->expects($this->once())->method('set_accept_status');
        BeanFactory::registerBean($meeting2);

        $meetings = array(
            array('id' => $meeting2->id),
        );

        $q = $this->getMockBuilder('SugarQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('execute'))
            ->getMock();
        $q->expects($this->once())->method('execute')->willReturn($meetings);

        $events = $this->getMockBuilder('CalendarEvents')
            ->disableOriginalConstructor()
            ->setMethods(array('getChildrenQuery', 'isEventRecurring', 'runCalDavUpdate'))
            ->getMock();
        $events->expects($this->once())->method('isEventRecurring')->willReturn(true);
        $events->expects($this->once())->method('getChildrenQuery')->willReturn($q);
        $events->expects($this->once())->method('runCalDavUpdate')->with($meeting1);

        $invitee = BeanFactory::getBean('Contacts', create_guid());
        $updated = $events->updateAcceptStatusForInvitee(
            $meeting1,
            $invitee,
            'tentative',
            array('disable_row_level_security' => true)
        );

        $this->assertTrue($updated);

        BeanFactory::unregisterBean($meeting1);
        BeanFactory::unregisterBean($meeting2);
        BeanFactory::setBeanClass('Meetings');
    }

    public function testUpdateAcceptStatusForInvitee_EventIsNotRecurring_OnlyParentEventIsUpdated()
    {
        BeanFactory::setBeanClass('Meetings', 'MockMeeting');

        $meeting = $this->getMockBuilder('Meeting')
            ->disableOriginalConstructor()
            ->setMockClassName('MockMeeting')
            ->setMethods(array('set_accept_status'))
            ->getMock();
        $meeting->id = create_guid();
        $meeting->module_name = 'Meetings';
        $meeting->expects($this->once())->method('set_accept_status');
        BeanFactory::registerBean($meeting);

        $events = $this->getMockBuilder('CalendarEvents')
            ->disableOriginalConstructor()
            ->setMethods(array('getChildrenQuery', 'isEventRecurring', 'runCalDavUpdate'))
            ->getMock();
        $events->expects($this->once())->method('isEventRecurring')->willReturn(false);
        $events->expects($this->never())->method('getChildrenQuery');
        $events->expects($this->once())->method('runCalDavUpdate')->with($meeting);

        $invitee = BeanFactory::getBean('Contacts', create_guid());
        $updated = $events->updateAcceptStatusForInvitee($meeting, $invitee, 'tentative');

        $this->assertTrue($updated);

        BeanFactory::unregisterBean($meeting);
        BeanFactory::setBeanClass('Meetings');
    }

    public function testUpdateAcceptStatusForInvitee_EventIsRecurring_ParentAndChildrenAreUpdated()
    {
        BeanFactory::setBeanClass('Meetings', 'MockMeeting');

        $meeting1 = $this->getMockBuilder('Meeting')
            ->disableOriginalConstructor()
            ->setMockClassName('MockMeeting')
            ->setMethods(array('set_accept_status'))
            ->getMock();
        $meeting1->id = create_guid();
        $meeting1->module_name = 'Meetings';
        $meeting1->expects($this->once())->method('set_accept_status');
        BeanFactory::registerBean($meeting1);

        $meeting2 = $this->getMockBuilder('Meeting')
            ->disableOriginalConstructor()
            ->setMockClassName('MockMeeting')
            ->setMethods(array('set_accept_status'))
            ->getMock();
        $meeting2->id = create_guid();
        $meeting2->module_name = 'Meetings';
        $meeting2->expects($this->once())->method('set_accept_status');
        BeanFactory::registerBean($meeting2);

        $meeting3 = $this->getMockBuilder('Meeting')
            ->disableOriginalConstructor()
            ->setMockClassName('MockMeeting')
            ->setMethods(array('set_accept_status'))
            ->getMock();
        $meeting3->id = create_guid();
        $meeting3->module_name = 'Meetings';
        $meeting3->expects($this->once())->method('set_accept_status');
        BeanFactory::registerBean($meeting3);

        $meetings = array(
            array('id' => $meeting2->id),
            array('id' => $meeting3->id),
        );

        $q = $this->getMockBuilder('SugarQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('execute'))
            ->getMock();
        $q->expects($this->once())->method('execute')->willReturn($meetings);

        $events = $this->getMockBuilder('CalendarEvents')
            ->disableOriginalConstructor()
            ->setMethods(array('getChildrenQuery', 'isEventRecurring', 'runCalDavUpdate'))
            ->getMock();
        $events->expects($this->once())->method('isEventRecurring')->willReturn(true);
        $events->expects($this->once())->method('getChildrenQuery')->willReturn($q);
        $events->expects($this->once())->method('runCalDavUpdate')->with($meeting1);

        $invitee = BeanFactory::getBean('Contacts', create_guid());
        $updated = $events->updateAcceptStatusForInvitee(
            $meeting1,
            $invitee,
            'tentative',
            array('disable_row_level_security' => true)
        );

        $this->assertTrue($updated);

        BeanFactory::unregisterBean($meeting1);
        BeanFactory::unregisterBean($meeting2);
        BeanFactory::unregisterBean($meeting3);
        BeanFactory::setBeanClass('Meetings');
    }

    public function testUpdateAcceptStatusForInvitee_EntireSeriesHasBeenHeld_NoEventsAreUpdated()
    {
        BeanFactory::setBeanClass('Meetings', 'MockMeeting');

        $meeting1 = $this->getMockBuilder('Meeting')
            ->disableOriginalConstructor()
            ->setMockClassName('MockMeeting')
            ->setMethods(array('set_accept_status'))
            ->getMock();
        $meeting1->id = create_guid();
        $meeting1->module_name = 'Meetings';
        $meeting1->status = 'Held';
        $meeting1->expects($this->never())->method('set_accept_status');
        BeanFactory::registerBean($meeting1);

        $q = $this->getMockBuilder('SugarQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('execute'))
            ->getMock();
        $q->expects($this->once())->method('execute')->willReturn(array());

        $events = $this->getMockBuilder('CalendarEvents')
            ->disableOriginalConstructor()
            ->setMethods(array('getChildrenQuery', 'isEventRecurring', 'runCalDavUpdate'))
            ->getMock();
        $events->expects($this->once())->method('isEventRecurring')->willReturn(true);
        $events->expects($this->once())->method('getChildrenQuery')->willReturn($q);
        $events->expects($this->never())->method('runCalDavUpdate')->with($meeting1);

        $invitee = BeanFactory::getBean('Contacts', create_guid());
        $updated = $events->updateAcceptStatusForInvitee(
            $meeting1,
            $invitee,
            'tentative',
            array('disable_row_level_security' => true)
        );

        $this->assertFalse($updated);

        BeanFactory::unregisterBean($meeting1);
        BeanFactory::setBeanClass('Meetings');
    }

    /**
     * Instantiate a new Meeting and prepopulate values from args
     * Add Meeting to meetingIds array to ensure its deletion on teardown
     * @param string $id  meeting ID to assign
     * @param array $args assign field values to newly created meeting
     * @return Meeting
     */
    protected function newMeeting($id = '', $args=array())
    {
        global $current_user;
        $meeting = SugarTestMeetingUtilities::createMeeting($id, $current_user);
        if (!empty($args)) {
            foreach ($args AS $k => $v) {
                $meeting->$k = $v;
            }
            $meeting->save();
        }
        return $meeting;
    }

    protected function newTags($numTags = 1)
    {
        $tags = array();
        while(count($tags) < $numTags) {
            $tags[] = SugarTestTagUtilities::createTag();
        }
        return $tags;
    }

    protected function addTags($bean, $numTags = 1)
    {
        $tags = $this->newTags($numTags);
        $bean->load_relationship('tag_link');
        foreach($tags as $tag) {
            $bean->tag_link->add($tag);
        }
        $tags = $bean->tag_link->getBeans();
        return $tags;
    }

    protected function removeTags($bean)
    {
        $bean->load_relationship('tag_link');
        $tags = $bean->tag_link->getBeans();
        foreach($tags as $tag) {
            $bean->tag_link->delete($bean->id, $tag);
        }
    }
}


class CalendarEventsTest_CalendarEvents extends CalendarEvents
{
    protected $eventsCreated = array();

    public function getEventsCreated()
    {
        return $this->eventsCreated;
    }

    protected function saveRecurring(SugarBean $parentBean, array $repeatDateTimeArray)
    {
        $this->eventsCreated = parent::saveRecurring($parentBean, $repeatDateTimeArray);
    }
}

