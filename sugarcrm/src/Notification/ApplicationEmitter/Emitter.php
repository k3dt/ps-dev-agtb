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

namespace Sugarcrm\Sugarcrm\Notification\ApplicationEmitter;

use Sugarcrm\Sugarcrm\Notification\EmitterInterface;

/**
 * Class Emitter.
 * Emitter that emits application-level Events.
 * @package Sugarcrm\Sugarcrm\Notification\ApplicationEmitter
 */
class Emitter implements EmitterInterface
{
    /**
     * Get an Event by a given string.
     * @param string $string Event identifier.
     * @return Event application-level Event.
     */
    public function getEventPrototypeByString($string)
    {
        return new Event($string);
    }

    /**
     * {@inheritdoc}
     */
    public function getEventStrings()
    {
        // ToDo: change to an actual logic.
        return array('event1', 'event2');
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return 'ApplicationEmitter';
    }
}
