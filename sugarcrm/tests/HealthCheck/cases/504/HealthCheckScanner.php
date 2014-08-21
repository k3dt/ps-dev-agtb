<?php

class S_504_HealthCheckScannerCasesTestWrapper extends HealthCheckScannerCasesTestWrapper
{
    public function init()
    {
        if (parent::init()) {
            $this->tearDown();
            $GLOBALS['dictionary']['Account']['fields']['broken']['type'] = '';
            return true;
        }
        return false;
    }

    public function tearDown()
    {
        unset($GLOBALS['dictionary']['Account']);
        $GLOBALS['reload_vardefs'] = true;
        new Account();
        $GLOBALS['reload_vardefs'] = null;
    }
}
