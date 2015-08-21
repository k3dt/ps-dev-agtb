<?php
if (!defined('sugarEntry')) {
    define('sugarEntry', true);
}
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

define('ENTRY_POINT_TYPE', 'api');
require_once 'include/entryPoint.php';

$authClass = \SugarAutoLoader::customClass('Sugarcrm\\Sugarcrm\\Dav\\Base\\Auth\\SugarAuth');
$principalClass = \SugarAutoLoader::customClass('Sugarcrm\\Sugarcrm\\Dav\\Base\\Principal\\SugarPrincipal');
$calendarClass = \SugarAutoLoader::customClass('\Sugarcrm\\Sugarcrm\\Dav\\Cal\\Backend\\CalendarData');

$authBackend = new $authClass();
$principalBackend = new $principalClass();
$calendarBackend = new $calendarClass();

$tree = array (
    new Sabre\CalDAV\Principal\Collection($principalBackend),
    new Sabre\CalDAV\CalendarRoot($principalBackend, $calendarBackend),
);

$server = new Sabre\DAV\Server($tree);
$server->setBaseUri($server->getBaseUri());

$authPlugin = new Sabre\DAV\Auth\Plugin($authBackend, 'SugarCRM DAV Server');
$server->addPlugin($authPlugin);

$aclPlugin = new Sabre\DAVACL\Plugin();
$server->addPlugin($aclPlugin);

$caldavPlugin = new Sabre\CalDAV\Plugin();
$server->addPlugin($caldavPlugin);

//@todo Should be deleted in future. Using for browse server
$browser = new Sabre\DAV\Browser\Plugin();
$server->addPlugin($browser);

$server->exec();
