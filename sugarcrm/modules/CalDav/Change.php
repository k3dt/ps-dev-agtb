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

/**
 * Class CalDavChange
 * Represents implementation of Sugar Bean for CalDAV backend operations with calendar history
 */
class CalDavChange extends SugarBean
{
    public $new_schema = true;
    public $module_dir = 'CalDav';
    public $module_name = 'CalDavChanges';
    public $object_name = 'CalDavChange';
    public $table_name = 'caldav_changes';

    /**
     * Object ID
     * @var string
     */
    public $id;

    /**
     * Object name
     * @var string
     */
    public $name;

    /**
     * Object creation date
     * @var string
     */
    public $date_entered;

    /**
     * Object modification date
     * @var string
     */
    public $date_modified;

    /**
     * User who modified the object
     * @var string
     */
    public $modified_user_id;

    /**
     * User who created the object
     * @var string
     */
    public $created_by;

    /**
     * Object description
     * @var string
     */
    public $description;

    /**
     * Is object deleted or not
     * @var string
     */
    public $deleted;

    /**
     * Object URI
     * @var string
     */
    public $uri;

    /**
     * CalDAV server synchronization token for user calendar
     * @var integer
     */
    public $synctoken;

    /**
     * Calendar ID
     * @var string
     */
    public $calendarid;

    /**
     * Operation with calendar object such as DELETE, MODIFY, CREATE
     * @var integer
     */
    public $operation;
}
