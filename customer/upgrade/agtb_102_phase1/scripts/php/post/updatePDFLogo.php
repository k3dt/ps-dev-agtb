<?php

define('sugarEntry', true);
define('ENTRY_POINT_TYPE', 'api');
require_once 'include/entryPoint.php';
$logger = LoggerManager::getLogger();

//Updating aud_vk field type from bool to varchar
$sqlCommands = array(
    "INSERT INTO config (category,name,value,platform) VALUES ('sugarpdf','pdf_small_header_logo','sugarpdf_small_header_logo.png','');"
);
$db = DBManagerFactory::getInstance();
foreach ($sqlCommands as $query) {
    $logger->debug('Executing SQL Command: ' . $query);
    $db->query($query);
}
$logger->debug('Finished Executing SQL Commands');