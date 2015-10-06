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

namespace Sugarcrm\Sugarcrm\Dav\Cal\Adapter;

use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\AdapterAbstract as CalDavAbstractAdapter;
use Sugarcrm\Sugarcrm\JobQueue\Exception\InvalidArgumentException as AdapterInvalidArgumentException;

/**
 * Class for processing Calls by iCal protocol
 *
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Adapter
 */
class Calls extends CalDavAbstractAdapter implements AdapterInterface
{
    public function export(\SugarBean $sugarBean, \CalDavEvent $calDavBean)
    {
        if (!($sugarBean instanceof \Call)) {
            throw new AdapterInvalidArgumentException('Bean must be an instance of Call. Instance of '. get_class($sugarBean) .' given');
        }
        $dateTimeHelper = $this->getDateTimeHelper();
        $isEventChanged = false;
        $dateStart = $dateEnd = '';
        $sugarBean = $this->getNotCachedBean($sugarBean);
        if (!$calDavBean->calendarid) {
            $calendars = $this->getUserCalendars();
            if ($calendars !== null) {
                $calDavBean->setCalendarId(key($calendars));
            } else {
                return false;
            }
        }

        $calendarEvent = $calDavBean->getVCalendarEvent();

        $calendarComponent = $calDavBean->setComponent($calDavBean->getComponentTypeName());
        foreach ($this->exportBeanDataMap as $functionName => $field) {
            if ($calDavBean->$functionName($sugarBean->$field, $calendarComponent)) {
                $isEventChanged = true;
            }
        }

        if ($sugarBean->date_start) {
            $dateStart = $dateTimeHelper->sugarDateToUTC($sugarBean->date_start)->format(\TimeDate::DB_DATETIME_FORMAT);
        }
        if ($calDavBean->setStartDate($dateStart, $calendarComponent)) {
            $isEventChanged = true;
        }

        if ($sugarBean->date_end) {
            $dateEnd = $dateTimeHelper->sugarDateToUTC($sugarBean->date_end)->format(\TimeDate::DB_DATETIME_FORMAT);
        }
        if ($calDavBean->setEndDate($dateEnd, $calendarComponent)) {
            $isEventChanged = true;
        }

        if ($calDavBean->setDuration($sugarBean->duration_hours, $sugarBean->duration_minutes, $calendarComponent)) {
            $isEventChanged = true;
        }
        if ($calDavBean->setOrganizer($calendarComponent)) {
            $isEventChanged = true;
        }
        if ($this->setExportReminders($sugarBean, $calDavBean, $calendarComponent)) {
            $isEventChanged = true;
        }
        if ($calDavBean->setParticipants($calendarComponent)) {
            $isEventChanged = true;
        }
        if ($this->setRecurringRulesToCalDav($sugarBean, $calDavBean)) {
            $isEventChanged = true;
        }
        $calDavBean->setCalendarEventData($calendarEvent->serialize());

        return $isEventChanged;
    }

    /**
     * set meeting bean property
     * @param \Call $sugarBean
     * @param \CalDavEvent $calDavBean
     * @return bool
     */
    public function import(\SugarBean $sugarBean, \CalDavEvent $calDavBean)
    {
        if (!($sugarBean instanceof \Call)) {
            throw new AdapterInvalidArgumentException('Bean must be an instance of Call. Instance of '. get_class($sugarBean) .' given');
        }

        $isBeanChanged = false;
        $oldAttributes = $this->getCurrentAttributes($sugarBean);
        /**@var \CalDavEvent $calDavBean */
        $calDavBean = $this->getNotCachedBean($calDavBean);

        if (!$sugarBean->assigned_user_id) {
            $sugarBean->assigned_user_id = $this->getCurrentUserId();
            $isBeanChanged = true;
        }

        /**@var \Call $sugarBean */
        if ($this->setBeanProperties($sugarBean, $calDavBean, $this->importBeanDataMap)) {
            $isBeanChanged = true;
        }

        $participants = $calDavBean->getParticipants();

        if ($participants) {
            if (!empty($participants['Users'])) {
                $usersParticipants = $participants['Users'];
                $sugarBean->users_arr = array_keys($usersParticipants);
                if (!$sugarBean->id) {
                    $sugarBean->id = create_guid();
                    $sugarBean->new_with_id = true;
                    $isBeanChanged = true;
                }
                $meetingUsers = $this->arrayIndex('id', $sugarBean->get_call_users());
                foreach ($usersParticipants as $userId => $partipientInfo) {
                    if ($partipientInfo['accept_status']) {
                        if (!array_key_exists($userId, $meetingUsers) ||
                            $meetingUsers[$userId]->accept_status != $partipientInfo['accept_status']
                        ) {
                            $user = \BeanFactory::getBean('Users', $userId);
                            $sugarBean->set_accept_status($user, $partipientInfo['accept_status']);
                            $isBeanChanged = true;
                        }
                    }
                }
            }

            if (!empty($participants['Contacts'])) {
                $isBeanChanged |= $this->addNonUsersParticipants(
                    $participants['Contacts'],
                    $sugarBean,
                    'contacts',
                    'setContactInvitees'
                );
            }

            if (!empty($participants['Leads'])) {
                $isBeanChanged |= $this->addNonUsersParticipants(
                    $participants['Leads'],
                    $sugarBean,
                    'leads',
                    'setLeadInvitees'
                );
            }
        }

        $reminders = $calDavBean->getReminders();

        if ($reminders) {
            if ($this->setReminders($reminders, $sugarBean)) {
                $isBeanChanged = true;
            }
        }

        $recurringRule = $calDavBean->getRRule();
        if ($recurringRule) {
            if ($this->setRecurring($recurringRule, $sugarBean, $calDavBean)) {
                $isBeanChanged = true;
            }
        }

        if (!$isBeanChanged) {
            if (array_diff_assoc($oldAttributes, $this->getCurrentAttributes($sugarBean))) {
                $isBeanChanged = true;
            }
        }

        return $isBeanChanged;

    }
}
