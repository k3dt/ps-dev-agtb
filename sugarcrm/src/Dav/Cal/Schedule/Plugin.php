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

namespace Sugarcrm\Sugarcrm\Dav\Cal\Schedule;

use Sugarcrm\Sugarcrm\Dav\Base\Constants as DavConstants;
use Sabre\CalDAV\Schedule\Plugin as DavSchedulePlugin;
use Sabre\VObject;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Reader;
use Sabre\VObject\ITip;
use Sabre\DAVACL;
use Sabre\DAV;

class Plugin extends DavSchedulePlugin
{
    /**
     * @inheritdoc
     */
    public function scheduleLocalDelivery(ITip\Message $iTipMessage)
    {
        $aclPlugin = $this->server->getPlugin('acl');

        if (!$aclPlugin) {
            return;
        }

        $caldavNS = '{' . self::NS_CALDAV . '}';

        $principalUri = $aclPlugin->getPrincipalByUri($iTipMessage->recipient);

        if (!$principalUri) {
            $iTipMessage->scheduleStatus = '3.7;Could not find principal.';

            return;
        }

        $this->server->removeListener('propFind', array($aclPlugin, 'propFind'));

        $result = $this->server->getProperties(
            $principalUri,
            array(
                '{DAV:}principal-URL',
                $caldavNS . 'calendar-home-set',
                $caldavNS . 'schedule-inbox-URL',
                $caldavNS . 'schedule-default-calendar-URL',
                '{http://sabredav.org/ns}email-address',
            )
        );

        $this->server->on('propFind', array($aclPlugin, 'propFind'), 20);

        if (!isset($result[$caldavNS . 'schedule-inbox-URL'])) {
            $iTipMessage->scheduleStatus = '5.2;Could not find local inbox';

            return;
        }
        if (!isset($result[$caldavNS . 'calendar-home-set'])) {
            $iTipMessage->scheduleStatus = '5.2;Could not locate a calendar-home-set';

            return;
        }
        if (!isset($result[$caldavNS . 'schedule-default-calendar-URL'])) {
            $iTipMessage->scheduleStatus = '5.2;Could not find a schedule-default-calendar-URL property';

            return;
        }

        $calendarPath = $result[$caldavNS . 'schedule-default-calendar-URL']->getHref();
        $homePath = $result[$caldavNS . 'calendar-home-set']->getHref();
        $inboxPath = $result[$caldavNS . 'schedule-inbox-URL']->getHref();

        if ($iTipMessage->method === 'REPLY') {
            $privilege = 'schedule-deliver-reply';
        } else {
            $privilege = 'schedule-deliver-invite';
        }

        if (!$aclPlugin->checkPrivileges($inboxPath, $caldavNS . $privilege, DAVACL\Plugin::R_PARENT, false)) {
            $iTipMessage->scheduleStatus =
                '3.8;organizer did not have the ' . $privilege . ' privilege on the attendees inbox';

            return;
        }

        $uid = $iTipMessage->uid;

        $newFileName = \Sabre\DAV\UUIDUtil::getUUID() . '.ics';

        $home = $this->server->tree->getNodeForPath($homePath);
        $inbox = $this->server->tree->getNodeForPath($inboxPath);

        $currentObject = null;
        $objectNode = null;
        $isNewNode = false;

        $result = $home->getCalendarObjectByUID($uid);
        if ($result) {
            $objectPath = $homePath . '/' . $result;
            $objectNode = $this->server->tree->getNodeForPath($objectPath);
            $oldICalendarData = $objectNode->get();
            $currentObject = Reader::read($oldICalendarData);
        } else {
            $isNewNode = true;
        }

        $broker = new ITip\Broker();
        $newObject = $broker->processMessage($iTipMessage, $currentObject);

        $inbox->createFile($newFileName, $iTipMessage->message->serialize());

        if (!$newObject) {
            $iTipMessage->scheduleStatus =
                '5.0;iTip message was not processed by the server, likely because we didn\'t understand it.';

            return;
        }

        if ($isNewNode) {
            $calendar = $this->server->tree->getNodeForPath($calendarPath);
            $calendar->createFile($newFileName, $newObject->serialize());
        } else {
            if ($iTipMessage->method === 'REPLY') {
                $this->processICalendarChange(
                    $oldICalendarData,
                    $newObject,
                    array($iTipMessage->recipient),
                    array($iTipMessage->sender)
                );
            }
            $objectNode->put($newObject->serialize());
        }
        $iTipMessage->scheduleStatus = '1.2;Message delivered locally';
    }

    /**
     * This method need to be called whenever there was a calendar object gets
     * created or updated from SugarCRM.
     *
     * @param VCalendar $vCalendar Parsed iCalendar object
     * @param string $calendarPath Path to calendar collection
     * @param string $currentData  Current event data
     * @return bool A marker to indicate that the original object modified by this process.
     */
    public function calendarObjectSugarChange(VCalendar $vCalendar, $calendarPath, $currentData)
    {
        $modified = false;
        $calendarNode = $this->server->tree->getNodeForPath($calendarPath);

        $addresses = $this->getAddressesForPrincipal(
            $calendarNode->getOwner()
        );

        if ($currentData) {
            $oldObj = Reader::read($currentData);
        } else {
            $oldObj = null;
        }

        $this->processICalendarChange($oldObj, $vCalendar, $addresses, [], $modified);

        return $modified;
    }
}
