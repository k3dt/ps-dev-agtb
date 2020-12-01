<?php
define('sugarEntry', true);
define('ENTRY_POINT_TYPE', 'api');

require_once 'include/entryPoint.php';

$cf = new Configurator();
$cf->loadConfig();

$cf->config['activity_streams_enabled'] = true;

$cf->handleOverride();
