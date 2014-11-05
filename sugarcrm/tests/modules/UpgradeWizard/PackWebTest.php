<?php

require_once 'modules/UpgradeWizard/CliUpgrader.php';
require_once 'modules/UpgradeWizard/pack_web.php';

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
        $zip->expects($this->exactly(28))->method('addFile');
        $zip->expects($this->exactly(5))->method('addFromString');
        $installdefs = array();
        list($zip, $manifest, $installdefs) = packUpgradeWizardWeb($zip, $manifest, $installdefs, $params);

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

    public function testPackWebPhp()
    {
        $result = exec(CliUpgrader::getPHPBinaryPath() . ' modules/UpgradeWizard/pack_web.php');
        $this->assertEquals(
            "Use modules/UpgradeWizard/pack_web.php name.zip [sugarVersion [buildNumber [from]]]",
            $result
        );
        $zip = tempnam('/tmp', 'zip') . '.zip';
        exec(CliUpgrader::getPHPBinaryPath() . ' modules/UpgradeWizard/pack_web.php ' . $zip);
        $this->assertTrue(file_exists($zip));
        unlink($zip);
    }
}
