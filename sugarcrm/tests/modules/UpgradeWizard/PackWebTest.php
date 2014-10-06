<?php

require_once __DIR__ . '/../../../modules/UpgradeWizard/pack_web.php';

class PackWebTest extends PHPUnit_Framework_TestCase
{

    public function packUpgradeWizardWebProvider()
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
     * @dataProvider packUpgradeWizardWebProvider
     * @param $params
     * @param $expect
     */
    public function testPackUpgradeWizardWeb($params, $expect)
    {
        $manifest = array();
        $zip = $this->getMock('ZipArchive');
        $versionFile = __DIR__ . '/../../../modules/UpgradeWizard/version.json';
        $zip->expects($this->exactly(27))->method('addFile');
        $zip->expects($this->exactly(5))->method('addFromString');
        $installdefs = array();
        list($zip, $manifest, $installdefs) = packUpgradeWizardWeb($zip, $manifest, $installdefs, $params);

        $this->assertEquals(json_encode($expect), file_get_contents($versionFile));
        $this->assertArrayHasKey('version', $manifest);
        $this->assertEquals($manifest['version'], $expect['version']);
        $this->assertArrayHasKey('acceptable_sugar_versions', $manifest);
        $this->assertEquals($manifest['acceptable_sugar_versions'], array($expect['from']));
        $this->assertArrayHasKey('copy', $installdefs);
        $this->assertArrayHasKey('beans', $installdefs);
        $this->assertArrayHasKey(0, $installdefs['copy']);
        $this->assertEquals($installdefs['copy'][0]['from'], '<basepath>/modules/HealthCheck/Scanner/Scanner.php');
        $this->assertEquals($installdefs['copy'][0]['to'], 'modules/HealthCheck/Scanner/Scanner.php');
        unlink($versionFile);
    }

    public function testPackWebPhp()
    {
        $result = exec(PHP_BINDIR . '/php ' . __DIR__ . '/../../../modules/UpgradeWizard/pack_web.php');
        $this->assertEquals(
            "Use " . __DIR__ . "/../../../modules/UpgradeWizard/pack_web.php name.zip [sugarVersion [buildNumber [from]]]",
            $result
        );
        $zip = tempnam('/tmp', 'zip') . '.zip';
        exec(PHP_BINDIR . '/php ' . __DIR__ . '/../../../modules/UpgradeWizard/pack_web.php ' . $zip);
        $this->assertTrue(file_exists($zip));
        unlink($zip);
    }
}