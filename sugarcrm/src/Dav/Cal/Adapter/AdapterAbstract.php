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

use Sugarcrm\Sugarcrm\Dav\Base\Helper\ParticipantsHelper as ParticipantsHelper;
use Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper as DateTimeHelper;
use Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status as CalDavStatus;
use Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event;

/**
 * Abstract class for iCal adapters common functionality
 *
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Adapter
 */
abstract class AdapterAbstract implements AdapterInterface
{
    /**
     * @param \Call|\Meeting|\SugarBean $bean
     * @param array $changedFields
     * @param array $invitesBefore
     * @param array $invitesAfter
     * @param bool $insert
     * @return mixed
     */
    public function prepareForExport(
        \SugarBean $bean,
        $changedFields = array(),
        $invitesBefore = array(),
        $invitesAfter = array(),
        $insert = false
    ) {
        $participantsHelper = $this->getParticipantHelper();
        $parentBean = null;
        $childEvents = null;
        $repeatParentId = $bean->repeat_parent_id;
        /**
         * null means nothing changed, otherwise child was changed
         */
        $childEventsId = null;

        if (!$repeatParentId) {
            if (($insert && $bean->repeat_type)
                ||
                (!$insert && $this->isRecurringChanged($changedFields))
            ) {
                $childEventsId = array();
                $calendarEvents = $this->getCalendarEvents();
                $childEvents = $calendarEvents->getChildrenQuery($bean)->execute();
                foreach ($childEvents as $event) {
                    $childEventsId[] = $event->id;
                }
            }
        }

        if (!$insert) {
            $changedFields = $this->getFieldsDiff($changedFields);
        } else {
            $changedFields = $this->getBeanFetchedRow($bean);
        }
        $changedFields = array_intersect_key($changedFields, array(
            'name' => true,
            'location' => true,
            'description' => true,
            'deleted' => true,
            'date_start' => true,
            'date_end' => true,
            'status' => true,
            'reminder_time' => true,
            'repeat_type' => true,
            'repeat_interval' => true,
            'repeat_dow' => true,
            'repeat_until' => true,
            'repeat_count' => true,
            'repeat_parent_id' => true,
        ));

        $changedInvites = $participantsHelper->getInvitesDiff($invitesBefore, $invitesAfter);

        if (!$changedFields && !$changedInvites) {
            return false;
        }

        $beanData = array(
            $bean->module_name,
            $bean->id,
            $repeatParentId,
            $childEventsId,
            $insert,
        );

        return array($beanData, $changedFields, $changedInvites);
    }

    /**
     * @inheritDoc
     */
    public function verifyImportAfterExport(array $exportData, array $importData, \CalDavEventCollection $collection)
    {
        list($exportBean, $exportFields, $exportInvites) = $exportData;
        list($importBean, $importFields, $importInvites) = $importData;

        if (isset($importFields['title']) && isset($exportFields['name'])) {
            if ($importFields['title'][0] == $exportFields['name'][0]) {
                unset($importFields['title']);
            }
        }
        if (isset($importFields['location']) && isset($exportFields['location'])) {
            if ($importFields['location'][0] == $exportFields['location'][0]) {
                unset($importFields['location']);
            }
        }
        if (isset($importFields['description']) && isset($exportFields['description'])) {
            if ($importFields['description'][0] == $exportFields['description'][0]) {
                unset($importFields['description']);
            }
        }
        if (isset($importFields['status']) && isset($exportFields['status'])) {
            $map = new CalDavStatus\EventMap();
            $status = $map->getCalDavValue($exportFields['status'][0], $importFields['status'][0]);
            if ($importFields['status'][0] == $status) {
                unset($importFields['status']);
            }
        }
        if (isset($importFields['date_start']) && isset($exportFields['date_start'])) {
            if ($importFields['date_start'][0] == $exportFields['date_start'][0]) {
                unset($importFields['date_start']);
            }
        }
        if (isset($importFields['date_end']) && isset($exportFields['date_end'])) {
            if ($importFields['date_end'][0] == $exportFields['date_end'][0]) {
                unset($importFields['date_end']);
            }
        }

        foreach ($importFields as $field => $diff) {
            if (isset($diff[1])) {
                continue;
            }
            if ($diff[0] === null) {
                unset($importFields[$field]);
            }
        }

        foreach ($importInvites as $action => $list) {
            if (empty($exportInvites[$action])) {
                continue;
            }
            foreach ($list as $k => $importInvitee) {
                foreach ($exportInvites[$action] as $exportInvitee) {
                    $invitee = $exportInvitee;
                    $invitee[2] = $importInvitee[2]; // we don't care about real status
                    if ($importInvitee === $invitee) {
                        unset($importInvites[$action][$k]);
                        continue;
                    }
                }
            }
            if (!$importInvites[$action]) {
                unset($importInvites[$action]);
            }
        }

        if ($importFields || $importInvites) {
            return array(
                $importBean,
                $importFields,
                $importInvites,
            );
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function verifyExportAfterImport(array $importData, array $exportData, \SugarBean $bean)
    {
        list($exportBean, $exportFields, $exportInvites) = $exportData;
        list($importBean, $importFields, $importInvites) = $importData;

        if (isset($exportFields['name']) && isset($importFields['title'])) {
            if ($exportFields['name'][0] == $importFields['title'][0]) {
                unset($exportFields['name']);
            }
        }
        if (isset($exportFields['location']) && isset($importFields['location'])) {
            if ($exportFields['location'][0] == $importFields['location'][0]) {
                unset($exportFields['location']);
            }
        }
        if (isset($exportFields['description']) && isset($importFields['description'])) {
            if ($exportFields['description'][0] == $importFields['description'][0]) {
                unset($exportFields['description']);
            }
        }
        if (isset($exportFields['status']) && isset($importFields['status'])) {
            $map = new CalDavStatus\EventMap();
            $status = $map->getSugarValue($importFields['status'][0], $exportFields['status'][0]);
            if ($exportFields['status'][0] == $status) {
                unset($exportFields['status']);
            }
        }
        if (isset($exportFields['date_start']) && isset($importFields['date_start'])) {
            if ($exportFields['date_start'][0] == $importFields['date_start'][0]) {
                unset($exportFields['date_start']);
            }
        }
        if (isset($exportFields['date_end']) && isset($importFields['date_end'])) {
            if ($exportFields['date_end'][0] == $importFields['date_end'][0]) {
                unset($exportFields['date_end']);
            }
        }

        foreach ($exportFields as $field => $diff) {
            if (count($diff) > 1) {
                continue;
            }
            if ($diff[0] === null) {
                unset($exportFields[$field]);
            }
        }

        foreach ($exportInvites as $action => $list) {
            if (empty($importInvites[$action])) {
                continue;
            }
            foreach ($list as $k => $importInvitee) {
                foreach ($importInvites[$action] as $exportInvitee) {
                    $invitee = $exportInvitee;
                    $invitee[2] = $importInvitee[2]; // we don't care about real status
                    if ($importInvitee === $invitee) {
                        unset($exportInvites[$action][$k]);
                        continue;
                    }
                }
            }
            if (!$exportInvites[$action]) {
                unset($exportInvites[$action]);
            }
        }

        if ($exportFields || $exportInvites) {
            return array(
                $exportBean,
                $exportFields,
                $exportInvites,
            );
        }

        return false;
    }

    /**
     * return true if one of rucurrig rules was changed
     * @param array $changedFields
     * @return bool
     */
    protected function isRecurringChanged($changedFields)
    {
        $fieldList = array(
            'repeat_type',
            'repeat_interval',
            'repeat_count',
            'repeat_until',
            'repeat_dow',
        );

        if (count(array_intersect(array_keys($changedFields), $fieldList))) {
            return true;
        }
        return false;
    }

    /**
     * get fields list with before (if exists) and after values of field
     * @param array $changedFields
     * @return mixed
     */
    protected function getFieldsDiff($changedFields)
    {
        $dataDiff = array();
        foreach ($changedFields as $field => $fieldValues) {
            $dataDiff[$field] = array(
                0 => $fieldValues['after'],
            );
            if ($fieldValues['before']) {
                $dataDiff[$field][1] = $fieldValues['before'];
            }
        }
        return $dataDiff;
    }

    /**
     * Retrieve bean fetched row
     * If bean not saved yet we should make array from bean
     * @param \SugarBean $bean
     * @return array
     */
    protected function getBeanFetchedRow(\SugarBean $bean)
    {
        $dataDiff = array();
        $fetchedRow = $bean->fetched_row;
        if (!$fetchedRow) {
            if ($bean->isUpdate() && $bean->retrieve($bean->id)) {
                $fetchedRow = $bean->fetched_row;
            } else {
                $fetchedRow = $bean->toArray(true);
            }
        }

        foreach ($fetchedRow as $name => $value) {
            $dataDiff[$name] = array(
                0 => $value
            );
        }
        return $dataDiff;
    }

    /**
     * Checks that title matches current one.
     *
     * @param string $value
     * @param Event $event
     * @return bool
     */
    protected function checkCalDavTitle($value, Event $event)
    {
        return $event->getTitle() == $value;
    }

    /**
     * Checks that description matches current one.
     *
     * @param string $value
     * @param Event $event
     * @return bool
     */
    protected function checkCalDavDescription($value, Event $event)
    {
        return $event->getDescription() == $value;
    }

    /**
     * Checks that location matches current one.
     *
     * @param string $value
     * @param Event $event
     * @return bool
     */
    protected function checkCalDavLocation($value, Event $event)
    {
        return $event->getLocation() == $value;
    }

    /**
     * Checks that status matches current one.
     *
     * @param string $value
     * @param Event $event
     * @return bool
     */
    protected function checkCalDavStatus($value, Event $event)
    {
        $map = new CalDavStatus\EventMap();
        return $event->getStatus() == $map->getCalDavValue($value, $event->getStatus());
    }

    /**
     * Checks that start date matches current one.
     *
     * @param string $value
     * @param Event $event
     * @return bool
     */
    protected function checkCalDavStartDate($value, Event $event)
    {
        return $event->getStartDate() == new \SugarDateTime($value, new \DateTimeZone('UTC'));
    }

    /**
     * Checks that end date matches current one.
     *
     * @param string $value
     * @param Event $event
     * @return bool
     */
    protected function checkCalDavEndDate($value, Event $event)
    {
        return $event->getEndDate() == new \SugarDateTime($value, new \DateTimeZone('UTC'));
    }

    /**
     * Checks that invites are applicable to current ones.
     *
     * @param array $value
     * @param Event $event
     * @return bool
     */
    protected function checkCalDavInvites($value, Event $event)
    {
        if (isset($value['added'])) {
            foreach ($value['added'] as $invite) {
                if ($event->findParticipantsByEmail($invite[3]) != -1) {
                    return false;
                }
            }
        }
        if (isset($value['changed'])) {
            foreach ($value['changed'] as $invite) {
                if ($event->findParticipantsByEmail($invite[3]) == - 1) {
                    return false;
                }
            }
        }
        if (isset($value['deleted'])) {
            foreach ($value['deleted'] as $invite) {
                if ($event->findParticipantsByEmail($invite[3]) == -1) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Sets title to provided event and returns true if it was changed.
     *
     * @param string $value
     * @param Event $event
     * @return bool
     */
    protected function setCalDavTitle($value, Event $event)
    {
        return $event->setTitle($value);
    }

    /**
     * Sets description to provided event and returns true if it was changed.
     *
     * @param string $value
     * @param Event $event
     * @return bool
     */
    protected function setCalDavDescription($value, Event $event)
    {
        return $event->setDescription($value);
    }

    /**
     * Sets location to provided event and returns true if it was changed.
     *
     * @param string $value
     * @param Event $event
     * @return bool
     */
    protected function setCalDavLocation($value, Event $event)
    {
        return $event->setLocation($value);
    }

    /**
     * Maps and sets sugar status to provided event and returns true if it was changed.
     *
     * @param string $value
     * @param Event $event
     * @return bool
     */
    protected function setCalDavStatus($value, Event $event)
    {
        $map = new CalDavStatus\EventMap();
        $value = $map->getCalDavValue($value, $event->getStatus());
        return $event->setStatus($value);
    }

    /**
     * Sets start date to provided event and returns true if it was changed.
     *
     * @param string $value
     * @param Event $event
     * @return bool
     */
    protected function setCalDavStartDate($value, Event $event)
    {
        $value = new \SugarDateTime($value, new \DateTimeZone('UTC'));
        return $event->setStartDate($value);
    }

    /**
     * Sets end date to provided event and returns true if it was changed.
     *
     * @param string $value
     * @param Event $event
     * @return bool
     */
    protected function setCalDavEndDate($value, Event $event)
    {
        $value = new \SugarDateTime($value, new \DateTimeZone('UTC'));
        return $event->setEndDate($value);
    }

    /**
     * Sets provided invites to specified event.
     *
     * @param array $value
     * @param Event $event
     * @return bool
     */
    protected function setCalDavInvites(array $value, Event $event)
    {
        $result = false;
        $participantHelper = $this->getParticipantHelper();

        if (isset($value['added'])) {
            foreach ($value['added'] as $invite) {
                $result |= $event->setParticipant($participantHelper->inviteToParticipant($invite));
            }
        }
        if (isset($value['changed'])) {
            foreach ($value['changed'] as $invite) {
                $result |= $event->setParticipant($participantHelper->inviteToParticipant($invite));
            }
        }
        if (isset($value['deleted'])) {
            foreach ($value['deleted'] as $invite) {
                $result |= $event->deleteParticipant($invite[3]);
            }
        }
        if (!$event->getOrganizer() && $GLOBALS['current_user'] instanceof \User) {
            $email = $GLOBALS['current_user']->emailAddress->getPrimaryAddress($GLOBALS['current_user']);
            $participant = $event->findParticipantsByEmail($email);
            if ($participant == -1) {
                $participant = $participantHelper->inviteToParticipant(array(
                    $GLOBALS['current_user']->module_name,
                    $GLOBALS['current_user']->id,
                    'accept',
                    $email,
                    $GLOBALS['current_user']->full_name,
                ));
            } else {
                $participants = $event->getParticipants();
                $participant = $participants[$participant];
            }
            $event->setOrganizer($participant);
        }

        return $result;
    }

    /**
     * Checks that name matches current one.
     *
     * @param string $value
     * @param \SugarBean|\Meeting|\Call $bean
     * @return bool
     */
    protected function checkBeanName($value, \SugarBean $bean)
    {
        return $bean->name == $value;
    }

    /**
     * Checks that description matches current one.
     *
     * @param string $value
     * @param \SugarBean|\Meeting|\Call $bean
     * @return bool
     */
    protected function checkBeanDescription($value, \SugarBean $bean)
    {
        return $bean->description == $value;
    }

    /**
     * Checks that location matches current one.
     *
     * @param string $value
     * @param \SugarBean|\Meeting $bean
     * @return bool
     */
    protected function checkBeanLocation($value, \SugarBean $bean)
    {
        return $bean->location == $value;
    }

    /**
     * Checks that status matches current one.
     *
     * @param string $value
     * @param \SugarBean|\Meeting|\Call $bean
     * @return bool
     */
    protected function checkBeanStatus($value, \SugarBean $bean)
    {
        $map = new CalDavStatus\EventMap();
        return $bean->status == $map->getSugarValue($value, $bean->status);
    }

    /**
     * Checks that start date matches current one.
     *
     * @param string $value
     * @param \SugarBean|\Meeting|\Call $bean
     * @return bool
     */
    protected function checkBeanStartDate($value, \SugarBean $bean)
    {
        $beanDate = new \SugarDateTime(
            $bean->date_start,
            new \DateTimeZone($GLOBALS['current_user']->getPreference('timezone'))
        );
        return $beanDate->asDb() == $value;
    }

    /**
     * Checks that end date matches current one.
     *
     * @param string $value
     * @param \SugarBean|\Meeting|\Call $bean
     * @return bool
     */
    protected function checkBeanEndDate($value, \SugarBean $bean)
    {
        $beanDate = new \SugarDateTime(
            $bean->date_end,
            new \DateTimeZone($GLOBALS['current_user']->getPreference('timezone'))
        );
        return $beanDate->asDb() == $value;
    }

    /**
     * Checks that invites are applicable to current ones.
     *
     * @param array $value
     * @param \SugarBean|\Meeting|\Call $bean
     * @return bool
     */
    protected function checkBeanInvites($value, \SugarBean $bean)
    {
        $definitions = \VardefManager::getFieldDefs($bean->module_name);
        if (isset($definitions['invitees']['links'])) {
            $links = $definitions['invitees']['links'];
        } else {
            $links = array();
        }

        $existingLinks = array();
        foreach ($links as $link) {
            if ($bean->load_relationship($link)) {
                foreach ($bean->$link->getBeans() as $existingBean) {
                    $existingLinks[$existingBean->module_name][$existingBean->id] = true;
                }
            }
        }

        if (isset($value['added'])) {
            foreach ($value['added'] as $invite) {
                if (isset($existingLinks[$invite[0]][$invite[1]])) {
                    return false;
                }
            }
        }
        if (isset($value['changed'])) {
            foreach ($value['changed'] as $invite) {
                if (!isset($existingLinks[$invite[0]][$invite[1]])) {
                    return false;
                }
            }
        }
        if (isset($value['deleted'])) {
            foreach ($value['deleted'] as $invite) {
                if (!isset($existingLinks[$invite[0]][$invite[1]])) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Sets name to provided bean and returns true if it was changed.
     *
     * @param string $value
     * @param \SugarBean|\Meeting|\Call $bean
     * @return bool
     */
    protected function setBeanName($value, \SugarBean $bean)
    {
        if ($value != $bean->name) {
            $bean->name = $value;
            return true;
        }
        return false;
    }

    /**
     * Sets description to provided bean and returns true if it was changed.
     *
     * @param string $value
     * @param \SugarBean|\Meeting|\Call $bean
     * @return bool
     */
    protected function setBeanDescription($value, \SugarBean $bean)
    {
        if ($value != $bean->description) {
            $bean->description = $value;
            return true;
        }
        return false;
    }

    /**
     * Sets location to provided bean and returns true if it was changed.
     *
     * @param string $value
     * @param \SugarBean|\Meeting $bean
     * @return bool
     */
    protected function setBeanLocation($value, \SugarBean $bean)
    {
        if ($value != $bean->location) {
            $bean->location = $value;
            return true;
        }
        return false;
    }

    /**
     * Sets status to provided bean and returns true if it was changed.
     *
     * @param string $value
     * @param \SugarBean|\Meeting|\Call $bean
     * @return bool
     */
    protected function setBeanStatus($value, \SugarBean $bean)
    {
        $map = new CalDavStatus\EventMap();
        $value = $map->getSugarValue($value, $bean->status);

        if ($value != $bean->status) {
            $bean->status = $value;
            return true;
        }
        return false;
    }

    /**
     * Sets start date to provided bean and returns true if it was changed.
     *
     * @param string $value
     * @param \SugarBean|\Meeting|\Call $bean
     * @return bool
     */
    protected function setBeanStartDate($value, \SugarBean $bean)
    {
        if ($value != $bean->date_start) {
            $bean->date_start = $value;
            if ($bean->date_end) {
                $beanDateStart = new \SugarDateTime(
                    $value,
                    new \DateTimeZone('UTC')
                );
                $beanDateEnd = new \SugarDateTime(
                    $bean->date_end,
                    new \DateTimeZone($GLOBALS['current_user']->getPreference('timezone'))
                );
                $diff = $beanDateEnd->diff($beanDateStart);
                $bean->duration_hours = $diff->h + (int)$diff->format('a') * 24;
                $bean->duration_minutes = $diff->i;
            }
            return true;
        }
        return false;
    }

    /**
     * Sets end date to provided bean and returns true if it was changed.
     *
     * @param string $value
     * @param \SugarBean|\Meeting|\Call $bean
     * @return bool
     */
    protected function setBeanEndDate($value, \SugarBean $bean)
    {
        if ($value != $bean->date_end) {
            $bean->date_end = $value;
            if ($bean->date_start) {
                $beanDateStart = new \SugarDateTime(
                    $bean->date_start,
                    new \DateTimeZone($GLOBALS['current_user']->getPreference('timezone'))
                );
                $beanDateEnd = new \SugarDateTime(
                    $value,
                    new \DateTimeZone('UTC')
                );
                $diff = $beanDateEnd->diff($beanDateStart);
                $bean->duration_hours = $diff->h + (int)$diff->format('a') * 24;
                $bean->duration_minutes = $diff->i;
            }
            return true;
        }
        return false;
    }

    /**
     * Sets provided invites to specified bean.
     *
     * @param array $value
     * @param \SugarBean|\Meeting|\Call $bean
     * @return bool
     */
    protected function setBeanInvites(array $value, \SugarBean $bean)
    {
        $result = false;

        $definitions = \VardefManager::getFieldDefs($bean->module_name);
        if (isset($definitions['invitees']['links'])) {
            $links = $definitions['invitees']['links'];
        } else {
            $links = array();
        }

        $existingLinks = array();
        foreach ($links as $link) {
            if ($bean->load_relationship($link)) {
                foreach ($bean->$link->getBeans() as $existingBean) {
                    if (!isset($existingLinks[$existingBean->module_name])) {
                        $existingLinks[$existingBean->module_name] = array();
                    }
                    $existingLinks[$existingBean->module_name][$existingBean->id] = true;
                }
            }
        }

        $map = new CalDavStatus\AcceptedMap();
        if (isset($value['added'])) {
            foreach ($value['added'] as $invite) {
                list($beanName, $beanId, $beanStatus, $email, $displayName) = $invite;
                $participant = \BeanFactory::getBean($beanName, $beanId, array(
                    'strict_retrieve' => true,
                ));
                if ($participant) {
                    $bean->set_accept_status($participant, $map->getSugarValue($beanStatus));
                    $existingLinks[$participant->module_name][$participant->id] = true;
                }
            }
        }
        if (isset($value['changed'])) {
            foreach ($value['changed'] as $invite) {
                list($beanName, $beanId, $beanStatus, $email, $displayName) = $invite;
                $participant = \BeanFactory::getBean($beanName, $beanId, array(
                    'strict_retrieve' => true,
                ));
                if ($participant) {
                    $bean->set_accept_status($participant, $map->getSugarValue($beanStatus));
                    $existingLinks[$participant->module_name][$participant->id] = true;
                }
            }
        }
        if (isset($value['deleted'])) {
            foreach ($value['deleted'] as $invite) {
                if (isset($existingLinks[$invite[0]][$invite[1]])) {
                    unset($existingLinks[$invite[0]][$invite[1]]);
                    $result = true;
                }
            }
            foreach ($existingLinks as $module => $ids) {
                if (method_exists($bean, 'set' . substr($module, 0, -1) . 'invitees')) {
                    call_user_func_array(array($bean, 'set' . substr($module, 0, -1) . 'invitees'), array(
                        array_keys($ids),
                        array(
                            0 => true, // trick to delete everybody if $ids is empty
                        ),
                    ));
                }
            }
        }

        return $result;
    }

    /**
     * @return \Sugarcrm\Sugarcrm\Dav\Base\Helper\ParticipantsHelper
     */
    protected function getParticipantHelper()
    {
        return new ParticipantsHelper();
    }

    /**
     * @return \CalendarEvents
     */
    protected function getCalendarEvents()
    {
        return new \CalendarEvents();
    }

    /**
     * @return DateTimeHelper
     */
    protected function getDateTimeHelper()
    {
        return new DateTimeHelper();
    }
}
