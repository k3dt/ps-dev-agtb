<?php
define('sugarEntry', true);
define('ENTRY_POINT_TYPE', 'api');

require_once 'include/entryPoint.php';

$cf = new Configurator();
$cf->loadConfig();

$cf->config['default_currency_iso4217'] = 'EUR';
$cf->config['default_currency_name'] = 'Euro';
$cf->config['default_currency_symbol'] = '€';
$cf->config['default_date_format'] = 'd.m.Y';
$cf->config['default_decimal_seperator'] = ',';
$cf->config['default_number_grouping_seperator'] = '.';
$cf->config['default_time_format'] = 'H:i';

$cf->handleOverride();
