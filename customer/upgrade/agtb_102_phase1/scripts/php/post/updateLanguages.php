<?php

define('sugarEntry', true);
define('ENTRY_POINT_TYPE', 'api');

require_once 'include/entryPoint.php';

$logger = LoggerManager::getLogger();

$logger->debug(sprintf('%s: Starting upgrade script', $_SERVER['argv'][0]));

try {
    $cf = new Configurator();
    $cf->loadConfig();
    $cf->config['disabled_languages'] = 'bg_BG,cs_CZ,el_EL,fi_FI,da_DK,hu_HU,ro_RO,ja_JP,he_IL,ko_KR,sr_RS,sk_SK,es_LA,en_UK,sq_AL,et_EE,uk_UA,ar_SA,lv_LV,lt_LT,pt_BR,hr_HR,zh_TW,ca_ES,tr_TR,th_TH,ru_RU,sv_SE,nl_NL,nb_NO';

    $cf->handleOverride();
} catch (Exception $e) {
    $logger->fatal(
            sprintf(
                    '%s: Upgrade failed: calling UpdateConfigFile failed with error: %s', $_SERVER['argv'][0], $e->getMessage()
            )
    );
}

$logger->debug(sprintf('%s: finished UpdateConfigFile call', $_SERVER['argv'][0]));
