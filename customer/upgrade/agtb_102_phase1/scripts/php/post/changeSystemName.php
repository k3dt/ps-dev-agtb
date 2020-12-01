<?php

define('sugarEntry', true);
define('ENTRY_POINT_TYPE', 'api');

require_once 'include/entryPoint.php';

$logger = LoggerManager::getLogger();

$sqlCommands = [
    "UPDATE config SET value='Global Talent Brokerage CRM' WHERE category='system' AND name='name';",
];
$db = DBManagerFactory::getInstance();
foreach ($sqlCommands as $query) {
    $logger->debug('Executing SQL Command: ' . $query);
    $db->query($query);
}

$logger->debug('Finished Executing SQL Commands');
