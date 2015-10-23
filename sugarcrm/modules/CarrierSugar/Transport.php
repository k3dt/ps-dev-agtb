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

use Sugarcrm\Sugarcrm\Notification\Carrier\TransportInterface;

require_once('include/api/SugarApi.php');

/**
 * Class CarrierSugarTransport.
 * Save Notification.
 */
class CarrierSugarTransport implements TransportInterface
{
    /**
     * Save bean Notification.
     *
     * @param string $recipient Sugar User id.
     * @param array $message message pack for delivery.
     * @return bool true if message was saved, otherwise false.
     */
    public function send($recipient, $message)
    {
        $isSent = false;

        if (!empty($message['title']) || !empty($message['text']) || !empty($message['html'])) {
            $notification = $this->newNotification();

            $notification->severity = 'information';
            $notification->name = $message['title'];
            $notification->description = $message['html'];
            if (empty($message['html']) && !empty($message['text'])) {
                $notification->description = to_html($message['text']);
            }

            $notification->assigned_user_id = $recipient;
            $isSent = (bool)$notification->save();
        }
        return $isSent;
    }

    /**
     * Create new empty notification bean
     * @return Notifications
     */
    protected function newNotification()
    {
        $notification = BeanFactory::newBean('Notifications');
        return $notification;
    }
}
