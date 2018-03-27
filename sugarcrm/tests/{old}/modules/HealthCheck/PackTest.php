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

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../modules/HealthCheck/pack.php';

class PackTest extends TestCase
{

    public function healthCheckPackProvider()
    {
        return array(
            array(
                array(
                    'version' => '1.2.3.4'
                ),
                array(
                    'version' => '1.2.3.4',
                    'build' => '998',
                    'from' => '6.5.17',
                ),
            ),
            array(
                array(),
                array(
                    'version' => '7.5.0.0',
                    'build' => '998',
                    'from' => '6.5.17',
                ),
            ),
            array(
                array(
                    'from' => '1.2.3.4'
                ),
                array(
                    'version' => '7.5.0.0',
                    'build' => '998',
                    'from' => '1.2.3.4',
                ),
            ),
            array(
                array(
                    'build' => '1.2.3.4'
                ),
                array(
                    'version' => '7.5.0.0',
                    'build' => '1.2.3.4',
                    'from' => '6.5.17',
                ),
            )
        );
    }

    /**
     * @dataProvider healthCheckPackProvider
     * @param $params
     * @param $expect
     */
    public function testHealthCheckPack($params, $expect)
    {
        $manifest = array();
        $zip = $this->createMock('ZipArchive');
        $versionFile = __DIR__ . '/../../../modules/HealthCheck/Scanner/version.json';
        $zip->expects($this->exactly(22))->method('addFile');
        $zip->expects($this->exactly(3))->method('addFromString');
        $installdefs = array();
        list($zip, $manifest, $installdefs) = packHealthCheck($zip, $manifest, $installdefs, $params);

        $this->assertEquals(json_encode($expect), file_get_contents($versionFile));
        $this->assertArrayHasKey('version', $manifest);
        $this->assertEquals($expect['version'], $manifest['version']);
        $this->assertArrayHasKey('acceptable_sugar_versions', $manifest);
        $this->assertEquals(array($expect['from']), $manifest['acceptable_sugar_versions']);
        $this->assertArrayHasKey('copy', $installdefs);
        $this->assertArrayHasKey('beans', $installdefs);
        $this->assertArrayHasKey(0, $installdefs['copy']);
        $this->assertEquals('<basepath>/modules/HealthCheck/Scanner/Scanner.php', $installdefs['copy'][0]['from']);
        $this->assertEquals('modules/HealthCheck/Scanner/Scanner.php', $installdefs['copy'][0]['to']);
        unlink($versionFile);
    }

    public function testPackPhp()
    {
        $result = exec(PHP_BINDIR . '/php ' . __DIR__ . '/../../../modules/HealthCheck/pack.php');
        $this->assertEquals(
            "Use " . __DIR__ . "/../../../modules/HealthCheck/pack.php name.zip [sugarVersion [buildNumber [from]]]",
            $result
        );
        $zip = tempnam('/tmp', 'zip') . '.zip';
        exec(PHP_BINDIR . '/php ' . __DIR__ . '/../../../modules/HealthCheck/pack.php ' . $zip);
        $this->assertTrue(file_exists($zip));
        unlink($zip);
    }
}
