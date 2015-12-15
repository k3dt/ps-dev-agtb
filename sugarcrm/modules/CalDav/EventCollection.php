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
use Sabre\VObject;
use Sabre\VObject\Component as SabreComponent;
use Sugarcrm\Sugarcrm\Dav\Base\Helper as DavHelper;
use Sugarcrm\Sugarcrm\Dav\Base\Constants as DavConstants;
use Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status as DavStatusMapper;
use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory as CalDavAdapterFactory;
use Sugarcrm\Sugarcrm\Dav\Cal\Structures;
use Sabre\VObject\Recur\EventIterator;
use Sugarcrm\Sugarcrm\Dav\Base\Principal;
use Sugarcrm\Sugarcrm\Dav\Base\Helper\ParticipantsHelper;

/**
 * Class CalDavEventCollection
 * Represents implementation of Sugar Bean for CalDAV backend operations with calendar events
 */
class CalDavEventCollection extends SugarBean
{
    public $new_schema = true;
    public $module_dir = 'CalDav';
    public $module_name = 'CalDavEvents';
    public $object_name = 'CalDavEventCollection';
    public $table_name = 'caldav_events';

    /**
     * Event ID
     * @var string
     */
    public $id;

    /**
     * Event name
     * @var string
     */
    public $name;

    /**
     * Event creation date
     * @var string
     */
    public $date_entered;

    /**
     * Event modification date
     * @var string
     */
    public $date_modified;

    /**
     * User who modified the event
     * @var string
     */
    public $modified_user_id;

    /**
     * User who created the event
     * @var string
     */
    public $created_by;

    /**
     * Event description
     * @var string
     */
    public $description;

    /**
     * Is Event deleted or not
     * @var integer
     */
    public $deleted;

    /**
     * Calendar event data in VOBJECT format
     * @var string
     */
    public $calendar_data = '';

    /**
     * Calendar URI
     * @var string
     */
    public $uri;

    /**
     * Calendar ID for event
     * @var string
     */
    public $calendar_id;

    /**
     * Event ETag. MD5 hash from $calendar_data
     * @var string
     */
    public $etag;

    /**
     * $calendar_data size in bytes
     * @var integer
     */
    public $data_size;

    /**
     * Event component type
     * @var string
     */
    public $component_type;

    /**
     * Recurring event first occurrence
     * @var string
     */
    public $first_occurence;

    /**
     * Recurring event last occurrence
     * @var string
     */
    public $last_occurence;

    /**
     * Event's UID
     * @var string
     */
    public $event_uid;

    /**
     * Related module name
     * @var string
     */
    public $parent_type;

    /**
     * Related module id
     * @var string
     */
    public $parent_id;

    /**
     * Json with participants link Dav to Sugar
     * @var string
     */
    public $participants_links;

    /**
     * Json with bean children ids
     * @var string
     */
    public $children_order_ids;

    /**
     * Array of links email => [beanName, beanId]
     * @var string
     */
    protected $participantLinks = array();

    /**
     * Calendar event is stored here
     * @var Sabre\VObject\Component\VCalendar
     */
    protected $vCalendar = null;

    /**
     * @var Sugarcrm\Sugarcrm\Dav\Base\Helper\ServerHelper
     */
    protected $serverHelper;

    /**
     * Is mail template generating or not
     * @var bool
     */
    protected $inMailGeneration;

    /**
     * @var Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event
     */
    protected $parentEvent = null;

    /**
     * Array of Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event
     * @var Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event[]
     */
    protected $childEvents = array();

    /**
     * Recurring rule
     * @var Sugarcrm\Sugarcrm\Dav\Cal\Structures\RRule
     */
    protected $rRule = null;

    /**
     * @var \Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper
     */
    protected $dateTimeHelper;

    public function __construct()
    {
        $this->serverHelper = new DavHelper\ServerHelper();
        $this->dateTimeHelper = new DavHelper\DateTimeHelper();
        parent::__construct();
    }

    /**
     * Retrieve VCalendar
     * @return Sabre\VObject\Component\VCalendar
     */
    protected function getVCalendar()
    {
        if ($this->vCalendar) {
            return $this->vCalendar;
        }
        if (!$this->calendar_data) {
            $this->vCalendar = new SabreComponent\VCalendar();
            $timezone = $this->vCalendar->createComponent('VTIMEZONE');
            $timezone->TZID = $this->getCurrentUser()->getPreference('timezone');
            $this->vCalendar->add($timezone);

            $event = $this->vCalendar->createComponent('VEVENT');
            if (!empty($event->UID)) {
                $event->UID->setValue(create_guid());
            } else {
                $uid = $event->createProperty('UID', create_guid());
                $event->add($uid);
            }
            $this->vCalendar->add($event);
        } else {
            $this->vCalendar = VObject\Reader::read($this->calendar_data);
        }

        return $this->vCalendar;
    }

    /**
     * Get class name for event
     * @return string
     */
    protected function getEventClass()
    {
        return \SugarAutoLoader::customClass('Sugarcrm\\Sugarcrm\\Dav\\Cal\\Structures\\Event');
    }

    /**
     * Get all recurring children
     * @return Structures\Event[]
     */
    protected function getAllChildren()
    {
        if ($this->childEvents) {
            return $this->childEvents;
        }

        /* @var $eventClass \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event */
        $eventClass = $this->getEventClass();
        $vCalendar = $this->getVCalendar();
        $parent = $vCalendar->getBaseComponent();

        if ($parent->DTSTART) {
            $it = new EventIterator($vCalendar, $parent->UID);
            $maxRecur = DavConstants::MAX_INFINITE_RECCURENCE_COUNT;
            $endDate = clone $parent->DTSTART->getDateTime();
            $endDate->modify('+' . $maxRecur . ' day');
            $end = $it->getDtEnd();
            while ($it->valid() && $end < $endDate) {
                $state = $eventClass::STATE_VIRTUAL;
                $child = $it->getEventObject();
                if (!$child->{'RECURRENCE-ID'}) {
                    $it->next();
                    continue;
                }
                if ($child) {

                    if ($child == $it->currentOverriddenEvent) {
                        $state = $eventClass::STATE_CUSTOM;
                    }
                    $event = new $eventClass($child, $state, $this->participantLinks);
                    $this->childEvents[$event->getRecurrenceID()->getTimestamp()] = $event;
                }
                $end = $it->getDtEnd();
                $it->next();
            }
        }

        $deletedRecurring = $this->getDeleted();

        foreach ($deletedRecurring as $recurrenceID) {
            $event = new $eventClass(null, $eventClass::STATE_DELETED);
            $event->setRecurrenceID($recurrenceID);
            $this->childEvents[$recurrenceID->getTimestamp()] = $event;
        }

        ksort($this->childEvents);

        return $this->childEvents;
    }

    /**
     * Get sugar bean children ids
     * @return array
     */
    public function getSugarChildrenOrder()
    {
        if ($this->children_order_ids) {
            return json_decode($this->children_order_ids, true);
        }

        return array();
    }

    /**
     * Set sugar bean children ids
     * @param array $ids Array with bean ids
     * @return bool
     */
    public function setSugarChildrenOrder(array $ids)
    {
        $valid = array_filter($ids, function ($id) {
            return \is_guid($id);
        });
        if ($valid == $ids) {
            $this->children_order_ids = json_encode($ids);

            return true;
        }

        return false;
    }

    /**
     * Get all recurring children id
     * @return \SugarDateTime[]
     */
    public function getAllChildrenRecurrenceIds()
    {
        $children = $this->getAllChildren();
        $result = array();
        foreach ($children as $child) {
            $recurrenceTimeStamp = $child->getRecurrenceID()->getTimestamp();
            $result[$recurrenceTimeStamp] = $child->getRecurrenceID();
        }

        return $result;
    }

    /**
     * Get deleted recurring children id
     * @return \SugarDateTime[]
     */
    public function getDeletedChildrenRecurrenceIds()
    {
        $children = $this->getAllChildren();
        $result = array();
        foreach ($children as $child) {
            if ($child->isDeleted()) {
                $recurrenceTimeStamp = $child->getRecurrenceID()->getTimestamp();
                $result[$recurrenceTimeStamp] = $child->getRecurrenceID();
            }
        }

        return $result;
    }

    /**
     * Get recurring children id that was customized
     * @return \SugarDateTime[]
     */
    public function getCustomizedChildrenRecurrenceIds()
    {
        $children = $this->getAllChildren();
        $result = array();
        foreach ($children as $child) {
            if ($child->isCustomized()) {
                $recurrenceTimeStamp = $child->getRecurrenceID()->getTimestamp();
                $result[$recurrenceTimeStamp] = $child->getRecurrenceID();
            }
        }

        return $result;
    }

    /**
     * Get parent event
     * @return Structures\Event
     */
    public function getParent()
    {
        if ($this->parentEvent) {
            return $this->parentEvent;
        }

        /* @var $eventClass \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event */
        $vCalendar = $this->getVCalendar();
        $parent = $vCalendar->getBaseComponent();
        $eventClass = $this->getEventClass();
        $this->parentEvent = new $eventClass($parent, $eventClass::STATE_PARENT, $this->participantLinks);

        return $this->parentEvent;
    }

    /**
     * Get deleted recurring children
     * @return \SugarDateTime[]
     */
    protected function getDeleted()
    {
        $result = array();
        $event = $this->getParent()->getObject();
        if ($event->EXDATE) {
            foreach ($event->EXDATE as $deleted) {
                $deletedDate = $this->dateTimeHelper->davDateToSugar($deleted);
                $result[$deletedDate->getTimestamp()] = $deletedDate;
            }
        }

        return $result;
    }

    /**
     * Remove child from EXDATE
     * @param \SugarDateTime $recurrenceId
     * @return array
     */
    protected function removeFromDeleted(\SugarDateTime $recurrenceId)
    {
        $event = $this->getParent()->getObject();

        if ($event->EXDATE) {
            foreach ($event->EXDATE as $deleted) {
                if ($recurrenceId == $deleted->getDateTime()) {
                    $event->remove($deleted);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get recurring event rules
     * @return Structures\RRule
     */
    public function getRRule()
    {
        if ($this->rRule) {
            return $this->rRule;
        }

        $event = $this->getParent()->getObject();

        if ($event->RRULE) {
            $recurringClass = \SugarAutoLoader::customClass('Sugarcrm\\Sugarcrm\\Dav\\Cal\\Structures\\RRule');
            $this->rRule = new $recurringClass($event->RRULE);
        }

        return $this->rRule;
    }

    /**
     * Set recurring rules of event
     * @param Structures\RRule $rRule
     * @return bool
     */
    public function setRRule(Structures\RRule $rRule = null)
    {
        $event = $this->getParent()->getObject();

        if (!$rRule) {
            $event->remove('RRULE');
            $this->childEvents = array();
            $this->rRule = null;

            return true;
        }

        $currentRule = $this->getRRule();

        if (!$currentRule) {
            $event->add($rRule->getObject());
            $this->rRule = $rRule;
            $this->childEvents = array();

            return true;
        }

        if ($currentRule->getObject()->getParts() != $rRule->getObject()->getParts()) {
            $currentRule->getObject()->setParts($rRule->getObject()->getParts());
            $this->childEvents = array();

            return true;
        }

        return false;
    }

    /**
     * Get recurring children by RECURRENCE-ID
     * @param SugarDateTime $recurringId
     * @param bool $restoreDeleted Restore deleted child or not
     * @return Structures\Event | null
     */
    public function getChild(\SugarDateTime $recurringId, $restoreDeleted = false)
    {
        $children = $this->getAllChildren();
        $recurringTimeStamp = $recurringId->getTimestamp();
        $child = isset($children[$recurringTimeStamp]) ? $children[$recurringTimeStamp] : null;

        if ($child) {

            if (!$child->isDeleted()) {
                return $child;
            }

            if ($child->isDeleted() && $restoreDeleted) {
                return $this->addChild($recurringId, $restoreDeleted);
            }
        }

        return null;
    }

    /**
     * Add new child a custom recurring item
     * If  $recurringId was deleted we can not add new child with this recurring id
     * @param SugarDateTime $recurringId
     * @param bool $restoreDeleted Restore deleted child or not
     * @return null | Structures\Event
     */
    protected function addChild(\SugarDateTime $recurringId, $restoreDeleted = false)
    {
        $vCalendar = $this->getVCalendar();
        /* @var $eventClass \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event */
        $eventClass = $this->getEventClass();

        $event = $vCalendar->createComponent('VEVENT');
        if (!empty($event->UID)) {
            $event->UID->setValue($this->getParent()->getUID());
        } else {
            $uid = $event->createProperty($this->getParent()->getUID());
            $event->add($uid);
        }
        $vCalendar->add($event);

        $child = new $eventClass($event, $eventClass::STATE_CUSTOM);
        $child->setRecurrenceID($recurringId);

        $recurringTimeStamp = $recurringId->getTimestamp();
        $this->childEvents[$recurringTimeStamp] = $child;

        if ($restoreDeleted) {
            $this->removeFromDeleted($recurringId);
        }

        return $child;
    }

    /**
     * Calculate and set the size of the event data in bytes
     */
    protected function calculateSize()
    {
        $this->data_size = strlen($this->calendar_data);
    }

    /**
     * Calculate and set calendar event ETag hash
     */
    protected function calculateETag()
    {
        $this->etag = md5($this->calendar_data);
    }

    /**
     * Retrieve component type from vobject
     * Component type can be VEVENT, VTODO or VJOURNAL
     * @param string $data Calendar event text data
     * @return bool True if component type found and valid
     */
    protected function calculateComponentType($data)
    {
        $vObject = VObject\Reader::read($data);
        $component = $vObject->getBaseComponent();
        if ($component) {
            $this->component_type = $component->name;
            $this->event_uid = $component->UID;

            return true;
        }

        return false;
    }

    /**
     * Calculate firstoccurence and lastoccurence of event
     * @param string $data Calendar event text data
     */
    protected function calculateTimeBoundaries()
    {
        if (!$this->calendar_data) {
            return;
        }
        $vObject = VObject\Reader::read($this->calendar_data);
        $component = $vObject->getBaseComponent();
        if ($component->name === 'VEVENT' && $component->DTSTART) {
            $this->first_occurence = $component->DTSTART->getDateTime()->getTimestamp();

            if (!isset($component->RRULE)) {
                if (isset($component->DTEND)) {
                    $this->last_occurence = $component->DTEND->getDateTime()->getTimestamp();
                } elseif (isset($component->DURATION)) {
                    $endDate = clone $component->DTSTART->getDateTime();
                    $endDate->add(VObject\DateTimeParser::parse($component->DURATION->getValue()));
                    $this->last_occurence = $endDate->getTimestamp();
                } elseif (!$component->DTSTART->hasTime()) {
                    $endDate = clone $component->DTSTART->getDateTime();
                    $endDate->modify('+1 day');
                    $this->last_occurence = $endDate->getTimestamp();
                } else {
                    $this->last_occurence = $this->first_occurence;
                }
            } else {
                $it = new VObject\Recur\EventIterator($vObject, $component->UID);
                $maxRecur = DavConstants::MAX_INFINITE_RECCURENCE_COUNT;

                $endDate = clone $component->DTSTART->getDateTime();
                $endDate->modify('+' . $maxRecur . ' day');
                if ($it->isInfinite()) {
                    $this->last_occurence = $endDate->getTimestamp();
                } else {
                    $end = $it->getDtEnd();
                    while ($it->valid() && $end < $endDate) {
                        $end = $it->getDtEnd();
                        $it->next();
                    }
                    $this->last_occurence = $end->getTimestamp();
                }
            }
        }
    }

    /**
     * Add Change to CalDav changes table
     * @param $operation
     */
    protected function addChange($operation)
    {
        $calendar = $this->getRelatedCalendar();
        if ($calendar) {
            $changes = $this->getChangesBean();
            $changes->add($calendar, $this->uri, $operation);

            $calendar->synctoken ++;
            $calendar->save();
        }
    }

    /**
     * Retrieve current_user
     * @return \User
     */
    protected function getCurrentUser()
    {
        return $GLOBALS['current_user'];
    }

    /**
     * Parse text calendar event data to database fields
     * @param string $data Calendar event text data
     * @return bool True - if all data are correct and were set, false in otherwise
     */
    public function setData($data)
    {
        if (empty($data)) {
            return false;
        }

        if (!$this->calculateComponentType($data)) {
            return false;
        }

        $this->calendar_data = $data;

        $this->vCalendar = null;
        $this->parentEvent = null;
        $this->childEvents = array();
        $this->rRule = null;

        return true;
    }

    /**
     * Set calendar id
     * @param string $calendarId
     */
    public function setCalendarId($calendarId)
    {
        $this->calendar_id = $calendarId;
    }

    /**
     * Set event URI
     * @param string $eventURI
     */
    public function setCalendarEventURI($eventURI)
    {
        $this->uri = $eventURI;
    }

    /**
     * Convert bean to array which used by CalDav backend
     * @return array
     */
    public function toCalDavArray()
    {
        return array(
            'id' => $this->id,
            'uri' => $this->uri,
            'lastmodified' => strtotime($this->date_modified),
            'etag' => '"' . $this->etag . '"',
            'calendarid' => $this->calendar_id,
            'size' => $this->data_size,
            'calendardata' => $this->calendar_data,
            'component' => strtolower($this->component_type),
        );
    }

    /**
     * Get instance of CalDavChange
     * @return null|CalDavChange
     */
    public function getChangesBean()
    {
        return \BeanFactory::getBean('CalDavChanges');
    }

    /**
     * Get scheduling bean
     * @return \CalDavScheduling
     */
    protected function getSchedulingBean()
    {
        return \BeanFactory::getBean('CalDavSchedulings');
    }

    /**
     * Retrieve Calendar for event
     * @return null|SugarBean
     */
    protected function getRelatedCalendar()
    {
        if ($this->load_relationship('events_calendar')) {
            return array_shift($this->events_calendar->getBeans());
        }

        return null;
    }

    /**
     * Find scheduling object by uri and set parent_type of event if object found
     * @return bool
     */
    protected function setCalDavParent()
    {
        if (!$this->uri) {
            return false;
        }

        $calendar = $this->getRelatedCalendar();
        if ($calendar) {
            $scheduling = $this->getSchedulingBean()->getByUri($this->uri, $calendar->assigned_user_id);
            if ($scheduling) {
                $this->parent_type = $this->module_name;
                return true;
            }
        }

        return false;
    }

    /**
     * Event can be imported to sugar module or not
     * @return bool
     */
    public function isImportable()
    {
        return $this->parent_type !== $this->module_name;
    }

    /**
     * Retrieve set of events by calendarID and URI
     * @param string $calendarId
     * @param array $uri
     * @param int $limit
     * @return CalDavEventCollection[]
     * @throws SugarQueryException
     */
    public function getByURI($calendarId, array $uri, $limit = 0)
    {
        $result = array();
        if ($this->load_relationship('events_calendar')) {
            $query = new \SugarQuery();
            $query->from($this);
            $query->where()->equals('calendar_id', $calendarId);
            $query->where()->in('caldav_events.uri', $uri);
            $query->limit($limit);
            $result = $this->fetchFromQuery($query);
        }

        return $result;
    }

    /**
     * Get search manager
     * @return \Sugarcrm\Sugarcrm\Dav\Base\Principal\Manager
     */
    protected function getPrincipalManager()
    {
        return new Principal\Manager();
    }

    /**
     * Links all dav participants to sugar beans and return array with links
     * @return array
     */
    protected function mapParticipantsToBeans()
    {
        $participantsList = $this->getParent()->getParticipants();
        $customChildrenId = $this->getCustomizedChildrenRecurrenceIds();
        foreach ($customChildrenId as $recurrenceId) {
            $participantsList = array_merge($participantsList, $this->getChild($recurrenceId)->getParticipants());
        }

        foreach ($participantsList as $participant) {
            $email = $participant->getEmail();
            if (!isset($this->participantLinks[$email])) {
                if ($participant->getBeanName() && $participant->getBeanId()) {
                    $link = array('beanName' => $participant->getBeanName(), 'beanId' => $participant->getBeanId());
                } else {
                    $link = $this->getPrincipalManager()
                                 ->setOutputFormat(new Principal\Search\Format\ArrayStrategy())
                                 ->findSugarLinkByEmail($email);
                }
                if ($link) {
                    $this->participantLinks[$email] = $link;
                }
            }
        }

        return $this->participantLinks;
    }

    /**
     * Populate bean fields according calendar_data or vCalendar
     */
    public function sync()
    {
        $isUpdate = $this->isUpdate();
        if ($this->vCalendar) {
            $this->calendar_data = $this->getVCalendar()->serialize();
        }

        $this->calculateTimeBoundaries();
        $this->calculateSize();
        $this->calculateETag();

        if (empty($this->uri) && !empty($this->event_uid)) {
            $this->uri = $this->event_uid . '.ics';
        }

        $this->participants_links = json_encode($this->mapParticipantsToBeans());

        if (!$isUpdate) {
            $this->setCalDavParent();
        }
    }

    /**
     * @inheritdoc
     */
    public function save($check_notify = false)
    {
        $isUpdate = $this->isUpdate();
        $currentETag = isset($this->fetched_row['etag']) ? $this->fetched_row['etag'] : null;
        $this->sync();

        $result = parent::save($check_notify);

        if ($result && $currentETag != $this->etag) {
            $operation = $isUpdate ? DavConstants::OPERATION_MODIFY : DavConstants::OPERATION_ADD;
            $this->addChange($operation);
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function mark_deleted($id)
    {
        if (!$this->deleted) {
            $this->addChange(DavConstants::OPERATION_DELETE);
        }
        parent::mark_deleted($id);
    }

    /**
     * @inheritdoc
     */
    public function mark_undeleted($id)
    {
        if ($this->deleted) {
            $this->addChange(DavConstants::OPERATION_ADD);
        }

        parent::mark_undeleted($id);
    }

    /**
     * @inheritdoc
     */
    public function retrieve($id = '-1', $encode = true, $deleted = true)
    {
        $bean = parent::retrieve($id, $encode, $deleted);
        if ($bean && $bean->participants_links) {
            $bean->participantLinks = json_decode($bean->participants_links, true);
        }

        return $bean;
    }

    /**
     * Get Event VTIMEZONE section and return timezone in string representation
     * @return string
     */
    public function getTimeZone()
    {
        $event = $this->getVCalendar();
        if ($event->VTIMEZONE) {
            return $event->VTIMEZONE->TZID->getValue();
        }

        return 'UTC';
    }

    /**
     * Handler for the 'schedule' event.
     * This method should be called from adapter if any participant was changed.
     * This method should be called before saving caldav event
     *
     * This handler attempts to look at local accounts to deliver the
     * scheduling object from sugar to caldav.
     * @return void
     */
    public function scheduleLocalDelivery()
    {
        if ($this->inMailGeneration) {
            return;
        }

        $currentUser = $this->getCurrentUser();
        $server = $this->serverHelper->setUp();

        if (!$server || !$currentUser) {
            return;
        }

        $calendarUri = DavConstants::DEFAULT_CALENDAR_URI;

        $schedulePlugin = $server->getPlugin('caldav-schedule');
        $caldavPlugin = $server->getPlugin('caldav');

        $calendarPath =
            $caldavPlugin::CALENDAR_ROOT . '/users/' . $currentUser->user_name . '/' . $calendarUri;

        $schedulePlugin->calendarObjectSugarChange($this->getVCalendar(), $calendarPath, $this->calendar_data);
    }

    /**
     * Returns related bean
     * @return null|SugarBean
     */
    public function getBean()
    {
        if ($this->parent_type && $this->parent_id) {
            return BeanFactory::getBean($this->parent_type, $this->parent_id);
        }

        return null;
    }

    /**
     * Set related bean
     * Return false if any errors occurred
     * @param SugarBean $bean
     * @return bool
     */
    public function setBean(\SugarBean $bean)
    {
        if ($bean->id) {
            $this->parent_type = $bean->module_name;
            $this->parent_id = $bean->id;

            return true;
        }

        return false;
    }

    /**
     * Retrive CalDavEventCollection by parent bean
     * @param SugarBean $bean
     * @return CalDavEventCollection | null
     * @throws SugarQueryException
     */
    public function findByBean(\SugarBean $bean)
    {
        $query = new \SugarQuery();
        $query->from($this);
        $query->where()->equals('parent_type', $bean->module_name);
        $query->where()->equals('parent_id', $bean->id);
        $query->limit(1);
        $result = $this->fetchFromQuery($query);
        if ($result) {
            return array_shift($result);
        }

        return null;
    }

    /**
     * Create text representation of event for email
     * @param SugarBean $bean
     * @return string
     */
    public function prepareForInvite(\SugarBean $bean)
    {
        $this->inMailGeneration = true;
        $adapterFactory = $this->getAdapterFactory();
        $adapter = $adapterFactory->getAdapter($bean->module_name);

        $result = '';

        if ($adapter) {
            $this->calendar_id = \create_guid();
            $this->setBean($bean);

            if ($adapter->export($bean, $this)) {
                $vCalendarEvent = $this->getVCalendar();
                $method = $vCalendarEvent->createProperty('METHOD', 'REQUEST');
                $vCalendarEvent->add($method);
                $result = $this->getVCalendar()->serialize();
            }
        }
        $this->inMailGeneration = false;

        return $result;
    }

    /**
     * @return \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory
     */
    protected function getAdapterFactory()
    {
        return CalDavAdapterFactory::getInstance();
    }

    /**
     * Gets synchronization object for operation with counters
     * @return null|CalDavSynchronization
     */
    public function getSynchronizationObject()
    {
        $this->load_relationship('synchronization');

        BeanFactory::clearCache();
        $this->synchronization->resetLoaded();
        $result = $this->synchronization->getBeans();
        if ($result) {
            return array_shift($result);
        } else {
            if (!$this->id) {
                $this->new_with_id = true;
                $this->id = create_guid();
            }

            $syncBean = BeanFactory::getBean('CalDavSynchronizations');
            $syncBean->event_id = $this->id;
            $syncBean->save();

            return $syncBean;
        }
    }

    /**
     * @param string $data
     * @return array
     */
    public function getDiffStructure($data)
    {
        $changedFields = array();
        $invites = array();
        $parentEvent = $this->getParent();
        $participantHelper = new ParticipantsHelper();
        if ($data) {
            $oldCalendar = new static();
            $oldCalendar->setData($data);
            $oldCalendar->participantLinks = $this->participantLinks;
            $oldCalendar->sync();
            $oldParent = $oldCalendar->getParent();
            if ($parentEvent->getTitle() != $oldParent->getTitle()) {
                $changedFields['title'] = array($parentEvent->getTitle(), $oldParent->getTitle());
            }
            if ($parentEvent->getDescription() != $oldParent->getDescription()) {
                $changedFields['description'] = array($parentEvent->getDescription(), $oldParent->getDescription());
            }
            if ($parentEvent->getLocation() != $oldParent->getLocation()) {
                $changedFields['location'] = array($parentEvent->getLocation(), $oldParent->getLocation());
            }
            if ($parentEvent->getStatus() != $oldParent->getStatus()) {
                $changedFields['status'] = array($parentEvent->getStatus(), $oldParent->getStatus());
            }
            if ($parentEvent->getStartDate() != $oldParent->getStartDate()) {
                $changedFields['date_start'] = array($parentEvent->getStartDate(), $oldParent->getStartDate());
            }
            if ($parentEvent->getEndDate() != $oldParent->getEndDate()) {
                $changedFields['date_end'] = array($parentEvent->getEndDate(), $oldParent->getEndDate());
            }
            $currentParticipants = $parentEvent->getParticipants();
            $participantsBefore = $oldParent->getParticipants();
            foreach ($currentParticipants as $participant) {
                /**@var Structures\Participant $participant*/
                $fountIndex = $oldParent->findParticipantsByEmail($participant->getEmail());
                $participantType = $participant->getBeanName();
                if ($fountIndex != -1) {
                    $participantBefore = $participantsBefore[$fountIndex];
                    if ($participantBefore->getEmail() != $participant->getEmail() ||
                        $participantBefore->getStatus() != $participant->getStatus() ||
                        $participantBefore->getDisplayName() != $participant->getDisplayName()
                    ) {
                        $participant->getType();
                        $participant->getBeanName();
                        if (!isset($invites['changed'][$participantType])) {
                            $invites['changed'][$participantType] = array();
                        }
                        $invites['changed'][$participantType][] = $participantHelper->participantToInvite($participant);
                    }
                } else {
                    if (!isset($invites['added'][$participantType])) {
                        $invites['added'][$participantType] = array();
                    }
                    $invites['added'][$participantType][] = $participantHelper->participantToInvite($participant);
                }
            }
            foreach ($participantsBefore as $participant) {
                /**@var Structures\Participant $participant*/
                if ($parentEvent->findParticipantsByEmail($participant->getEmail()) == -1) {
                    $participantType = $participant->getBeanName();
                    if (!isset($invites['deleted'][$participantType])) {
                        $invites['deleted'][$participantType] = array();
                    }
                    $invites['deleted'][$participantType][] = $participant;
                }
            }
        } else {
            $changedFields['title'] = array($parentEvent->getTitle());
            $changedFields['description'] = array($parentEvent->getDescription());
            $changedFields['location'] = array($parentEvent->getLocation());
            $changedFields['status'] = array($parentEvent->getStatus());
            $changedFields['date_start'] = array($parentEvent->getStartDate());
            $changedFields['date_end'] = array($parentEvent->getEndDate());
            $participants = $parentEvent->getParticipants();
            foreach ($participants as $participant) {
                $participantType = $participant->getBeanName();
                if (!isset($invites['added'][$participantType])) {
                    $invites['added'][$participantType] = array();
                }
                $invites['added'][$participantType][] = $participantHelper->participantToInvite($participant);
            }
        }

        $beanData = array(
            $this->id,
            $this->getAllChildrenRecurrenceIds(),
            $this->isUpdate(),
        );

        $importData = array($beanData, $changedFields, $invites);
        return $importData;
    }
}
