<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

use PHPUnit\Framework\MockObject\Generator;

require_once 'modules/HealthCheck/Scanner/Scanner.php';
require_once 'modules/UpgradeWizard/UpgradeDriver.php';

class HealthCheckScannerCasesTestMock extends HealthCheckScanner
{
    public $not = false;
    public $skip = false;
    public $md5_files = [];
    public $bwcModulesHash = [];

    /**
     * Initialize instance environment
     * @return bool False means this instance is messed up
     */
    protected function init()
    {
        $this->db = DBManagerFactory::getInstance();
        $this->bwcModulesHash = array_flip($this->bwcModules);

        /** @var UpgradeDriver $upgrade */
        $upgrade = (new Generator())->getMockForAbstractClass('UpgradeDriver');
        $this->setUpgrader($upgrade);
        return true;
    }

    protected function log($message, $tag = 'INFO')
    {
        // nothing to do
    }

    public function getVersionAndFlavor()
    {
        return ['6.5.0', 'ent'];
    }

    public function ping($data)
    {
        // nothing to do
    }

    public function tearDown()
    {
        // nothing to do
    }

    public function getVersion()
    {
        return  ['7.6.0.0','1000'];
    }

    public function getPackageManifest()
    {
        return [
            'version' => '7.6.0.1',
        ];
    }
}
