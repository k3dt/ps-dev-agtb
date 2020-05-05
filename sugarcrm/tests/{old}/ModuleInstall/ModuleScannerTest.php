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

require_once 'ModuleInstall/ModuleScanner.php';

class ModuleScannerTest extends TestCase
{
    public $fileLoc = "cache/moduleScannerTemp.php";

    protected function setUp() : void
    {
        SugarTestHelper::setUp('files');
        SugarTestHelper::saveFile($this->fileLoc);
        SugarTestHelper::saveFile('files.md5');
    }

    protected function tearDown() : void
    {
        SugarTestHelper::tearDown();
        if (is_dir(sugar_cached("ModuleScannerTest"))) {
            rmdir_recursive(sugar_cached("ModuleScannerTest"));
        }
    }

    public function phpSamples()
    {
        return [
            ["<?php echo blah;", true],
            ["<? echo blah;", true],
            ["blah <? echo blah;", true],
            ["blah <?xml echo blah;", true],
            ["<?xml version=\"1.0\"></xml>", false],
            ["<?xml \n echo blah;", true],
            ["<?xml version=\"1.0\"><? blah ?></xml>", true],
            ["<?xml version=\"1.0\"><?php blah ?></xml>", true],
        ];
    }

    /**
     * @dataProvider phpSamples
     */
    public function testPHPFile($content, $is_php)
    {
        $ms = new ModuleScanner();
        $this->assertEquals($is_php, $ms->isPHPFile($content), "Bad PHP file result");
    }

    public function testFileTemplatePass()
    {
        $fileModContents = <<<EOQ
<?PHP

class testFile_sugar extends File {
	function fileT_testFiles_sugar(){
		parent::File();
		\$this->file = new File();
		\$file = "file";
	}
}
?>
EOQ;
        file_put_contents($this->fileLoc, $fileModContents);
        $ms = new ModuleScanner();
        $errors = $ms->scanFile($this->fileLoc);
        $this->assertTrue(empty($errors));
    }

    public function testFileFunctionFail()
    {
        $fileModContents = <<<EOQ
<?PHP

class testFile_sugar extends File {
	function fileT_testFiles_sugar(){
		parent::File();
		\$this->file = new File();
		\$file = file('test.php');
	}
}
?>
EOQ;
        file_put_contents($this->fileLoc, $fileModContents);
        $ms = new ModuleScanner();
        $errors = $ms->scanFile($this->fileLoc);
        $this->assertTrue(!empty($errors));
    }

    public function testCallUserFunctionFail()
    {
        $fileModContents = <<<EOQ
<?PHP
	call_user_func("sugar_file_put_contents", "test2.php", "test");
?>
EOQ;
        file_put_contents($this->fileLoc, $fileModContents);
        $ms = new ModuleScanner();
        $errors = $ms->scanFile($this->fileLoc);
        $this->assertTrue(!empty($errors));
    }

    /**
     * @param $module
     * @param $function
     * @param $expected
     *
     * @dataProvider providerTestScanVArdefFile
     */
    public function testScanVardefFile($module, $functionDef, $expected)
    {
        $fileModContents = <<<EOQ
<?php
\$dictionary['{$module}'] = array(
    'fields' => array(
        'function_field' => array(
        'name' => 'function_field',
        {$functionDef},
        ),
    ),
);
EOQ;
        $vardefFile = "cache/vardefs.php";
        file_put_contents($vardefFile, $fileModContents);
        $ms = new ModuleScanner();
        $errors = SugarTestReflection::callProtectedMethod($ms, 'scanVardefFile', [$vardefFile]);
        unlink($vardefFile);
        $this->assertSame($expected, empty($errors));
    }

    public function providerTestScanVArdefFile()
    {
        return [
            [
                'testModule_custom_function_name',
                "'function' => ['name' => 'sugarInternalFunction']",
                true,
            ],
            [
                'testModule_custom_function',
                "'function' => 'sugarInternalFunction'",
                true,
            ],
            [
                'testModule_blacklist_function_name',
                "'function' => ['name' => 'call_user_func_array']",
                false,
            ],
            [
                'testModule_blacklist_function',
                "'function' => 'call_user_func_array'",
                false,
            ],
        ];
    }

    /**
     * test isVardefFile
     * @param $fileName
     * @param $expected
     *
     * @dataProvider providerTestIsVardefFile
     */
    public function testIsVardefFile($fileName, $expected)
    {
        $vardefsInManifest = [
            'vardefs' => [
                [
                    'from' => '<basepath>/SugarModules/relationships/vardefs/this_is_a_vardefs.php',
                    'to_module' => 'Accounts',
                ],
            ],
        ];
        $ms = new ModuleScanner();
        SugarTestReflection::setProtectedValue($ms, 'installdefs', $vardefsInManifest);
        $result = SugarTestReflection::callProtectedMethod($ms, 'isVardefFile', [$fileName]);
        $this->assertSame($expected, $result);
    }

    public function providerTestIsVardefFile()
    {
        return [
            ['anydir/vardefs.php', true],
            ['anydir/vardefs.ext.php', true],
            ['anydir/Vardefs/any_file_is_vardefs.php', true],
            ['anydir/anyfile.php', false],
            ['/SugarModules/relationships/vardefs/this_is_a_vardefs.php', true],
        ];
    }

    public function testCallMethodObjectOperatorFail()
    {
        $fileModContents = <<<EOQ
<?PHP
    //doesnt matter what the class name is, what matters is use of the banned method, setlevel
	\$GlobalLoggerClass->setLevel();
?>
EOQ;
        file_put_contents($this->fileLoc, $fileModContents);
        $ms = new ModuleScanner();
        $errors = $ms->scanFile($this->fileLoc);
        $this->assertNotEmpty($errors, 'There should have been an error caught for use of "->setLevel()');
    }

    public function testCallMethodDoubleColonFail()
    {
        $fileModContents = <<<EOQ
<?PHP
    //doesnt matter what the class name is, what matters is use of the banned method, setlevel
	\$GlobalLoggerClass::setLevel();
?>
EOQ;
        file_put_contents($this->fileLoc, $fileModContents);
        $ms = new ModuleScanner();
        $errors = $ms->scanFile($this->fileLoc);
        $this->assertNotEmpty($errors, 'There should have been an error caught for use of "::setLevel()');
    }

    /**
     * Make sure if catches even when comment is used between function variable and (
     */
    public function testCallMethodVariableWithCommentFail()
    {
        $fileModContents = <<<EOQ
<?PHP
    \$dod = 'fwrite';
        \$dod/* */();
?>
EOQ;
        file_put_contents($this->fileLoc, $fileModContents);
        $ms = new ModuleScanner();
        $errors = $ms->scanFile($this->fileLoc);
        $this->assertNotEmpty($errors, 'There should have been an error caught for use of function variable');
    }

    /**
     * When ModuleScanner is enabled, validating allowed and disallowed file extension names.
     */
    public function testValidExtsAllowed()
    {
        // Allowed file names
        $allowed = [
            'php' => 'test.php',
            'htm' => 'test.htm',
            'xml' => 'test.xml',
            'hbs' => 'test.hbs',
            'less' => 'test.less',
            'config' => 'custom/config.php',
        ];

        // Disallowed file names
        $notAllowed = [
            'docx' => 'test.docx',
            'docx(2)' => '../sugarcrm.xml/../sugarcrm/test.docx',
            'java' => 'test.java',
            'phtm' => 'test.phtm',
            'md5' => 'files.md5',
            'md5(2)' => '../sugarcrm/files.md5',

        ];

        // Get our scanner
        $ms = new ModuleScanner();

        // Test valid
        foreach ($allowed as $ext => $file) {
            $valid = $ms->isValidExtension($file);
            $this->assertTrue($valid, "The $ext extension should be valid on $file but the ModuleScanner is saying it is not");
        }

        // Test not valid
        foreach ($notAllowed as $ext => $file) {
            $valid = $ms->isValidExtension($file);
            $this->assertFalse($valid, "The $ext extension should not be valid on $file but the ModuleScanner is saying it is");
        }
    }

    public function testValidLicenseFileMissingExtension()
    {
        $ms = new ModuleScanner();
        $valid = $ms->isValidExtension('LICENSE');

        $this->assertTrue($valid);
    }

    public function testConfigChecks()
    {
        $isconfig = [
            'config.php',
            'config_override.php',
            'custom/../config_override.php',
            'custom/.././config.php',
        ];

        // Disallowed file names
        $notconfig = [
            'custom/config.php',
            'custom/modules/config.php',
            'cache/config_override.php',
            'modules/Module/config.php',
        ];

        // Get our scanner
        $ms = new ModuleScanner();

        // Test valid
        foreach ($isconfig as $file) {
            $valid = $ms->isConfigFile($file);
            $this->assertTrue($valid, "$file should be recognized as config file");
        }

        // Test not valid
        foreach ($notconfig as $ext => $file) {
            $valid = $ms->isConfigFile($file);
            $this->assertFalse($valid, "$file should not be recognized as config file");
        }
    }

    /**
     * @group bug58072
     */
    public function testLockConfig()
    {
        $fileModContents = <<<EOQ
<?PHP
	\$GLOBALS['sugar_config']['moduleInstaller']['test'] = true;
    	\$manifest = array();
    	\$installdefs = array();
?>
EOQ;
        file_put_contents($this->fileLoc, $fileModContents);
        $ms = new MockModuleScanner();
        $ms->config['test'] = false;
        $ms->lockConfig();
        MSLoadManifest($this->fileLoc);
        $errors = $ms->checkConfig($this->fileLoc);
        $this->assertTrue(!empty($errors), "Not detected config change");
        $this->assertFalse($ms->config['test'], "config was changed");
    }

    /**
     * @dataProvider normalizePathProvider
     * @param string $path
     * @param string $expected
     */
    public function testNormalize($path, $expected)
    {
        $ms = new ModuleScanner();
        $this->assertEquals($expected, $ms->normalizePath($path));
    }

    public function normalizePathProvider()
    {
        return [
            ['./foo', 'foo'],
            ['foo//bar///baz/', 'foo/bar/baz'],
            ['./foo/.//./bar/foo', 'foo/bar/foo'],
            ['foo/../bar', false],
            ['../bar/./', false],
            ['./', ''],
            ['.', ''],
            ['', ''],
            ['/', ''],
        ];
    }

    /**
     * @dataProvider scanCopyProvider
     * @param string $from
     * @param string $to
     * @param bool $ok is it supposed to be ok?
     */
    public function testScanCopy($file, $from, $to, $ok)
    {
        copy(__DIR__."/../upgrade/files.md5", "files.md5");
        // ensure target file exists
        $from = sugar_cached("ModuleScannerTest/$from");
        $file = sugar_cached("ModuleScannerTest/$file");
        mkdir_recursive(dirname($file));
        SugarTestHelper::saveFile($file);
        sugar_touch($file);

        $ms = new ModuleScanner();
        $ms->scanCopy($from, $to);
        if ($ok) {
            $this->assertEmpty($ms->getIssues(), "Issue found where it should not be");
        } else {
            $this->assertNotEmpty($ms->getIssues(), "Issue not detected");
        }
        // check with dir
        $ms->scanCopy(dirname($from), $to);
        if ($ok) {
            $this->assertEmpty($ms->getIssues(), "Issue found where it should not be");
        } else {
            $this->assertNotEmpty($ms->getIssues(), "Issue not detected");
        }
    }

    public function scanCopyProvider()
    {
        return [
            [
                'copy/modules/Audit/Audit.php',
                'copy/modules/Audit/Audit.php',
                "modules/Audit",
                false,
            ],
            [
                'copy/modules/Audit/Audit.php',
                'copy/modules/Audit/Audit.php',
                "modules/Audit/Audit.php",
                false,
            ],
            [
                'copy/modules/Audit/Audit.php',
                'copy',
                ".",
                false,
            ],
            [
                'copy/modules/Audit/SomeFile.php',
                'copy',
                ".",
                true,
            ],
        ];
    }
}

class MockModuleScanner extends ModuleScanner
{
    public $config;
    public function isPHPFile($contents)
    {
        return parent::isPHPFile($contents);
    }
}
