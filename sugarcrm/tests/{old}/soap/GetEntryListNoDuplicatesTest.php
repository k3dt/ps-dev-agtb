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

/**
 * Bug #65782
 * SOAP API (v1) - get_entry_list retuning duplicates
 *
 * @author mgusev@sugarcrm.com
 * @ticked 65782
 */
class GetEntryListNoDuplicatesTest extends SOAPTestCase
{
    /** @var Contact */
    protected $contact = null;

    /** @var Meeting */
    protected $meeting1 = null;

    /** @var Meeting */
    protected $meeting2 = null;

    protected function setUp() : void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', [true, true]);

        $this->contact = SugarTestContactUtilities::createContact();
        $this->meeting1 = SugarTestMeetingUtilities::createMeeting();
        $this->meeting2 = SugarTestMeetingUtilities::createMeeting();
        SugarTestMeetingUtilities::addMeetingContactRelation($this->meeting1->id, $this->contact->id);
        SugarTestMeetingUtilities::addMeetingContactRelation($this->meeting2->id, $this->contact->id);

        $this->soapURL = $GLOBALS['sugar_config']['site_url'].'/soap.php';

        parent::setUp();

        self::$user = $GLOBALS['current_user'];
        $this->login();
    }

    protected function tearDown() : void
    {
        SugarTestMeetingUtilities::removeMeetingContacts();
        SugarTestMeetingUtilities::removeMeetingUsers();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
    }

    public function testGetEntryList()
    {
        $client = [
            'session'       => $this->sessionId,
            'module_name'   => 'Contacts',
            'query'         => 'contacts.id=' . $GLOBALS['db']->quoted($this->contact->id),
            'order_by'      => '',
            'offset'        => 0,
            'select_fields' => ['id'],
            'max_results'   => 20,
            'deleted'       => -1,
        ];

        $result = $this->soapClient->call('get_entry_list', $client);
        $data = [];
        foreach ($result['entry_list'] as $v) {
            $this->assertNotContains($v['id'], $data, 'Duplicates were found');
            $data[] = $v['id'];
        }

        $this->assertNotEmpty($data, 'Records are not found');
    }
}
