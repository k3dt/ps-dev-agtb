<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTestUnit\inc\utils;

/**
 *
 * SugarAutoLoader unit tests
 * @coversDefaultClass \SugarAutoLoader
 *
 */
class SugarAutoLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * List of test files created
     * @var array
     */
    protected $testFiles = array();

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        foreach (array_unique($this->testFiles) as $file) {
            unlink($file);
        }
    }

    /**
     * @covers ::validateFilePath
     * @dataProvider providerTestValidFilePath
     */
    public function testValidFilePath($file)
    {
        $result = \SugarAutoLoader::validateFilePath($file);
        $this->assertSame($result, $file);
    }

    public function providerTestValidFilePath()
    {
        return array(
            array(SUGAR_BASE_DIR . '/modules/Accounts/Account.php'),
        );
    }

    /**
     * @covers ::validateFilePath
     * @dataProvider providerTestInvalidFilePath
     */
    public function testInvalidFilePath($file, $msg)
    {
        $this->setExpectedException('\Exception', $msg);
        \SugarAutoLoader::validateFilePath($file);
    }

    public function providerTestInvalidFilePath()
    {
        return array(
            array(
                '/etc/passwd',
                'File name violation: file outside basedir'
            ),
            array(
                '/etc/passwd' . chr(0),
                'File name violation: null bytes detected'
            ),
            array(
                SUGAR_BASE_DIR . '/modules/Accounts/FooBar.php',
                'File name violation: file not found'
            ),
            array(
                SUGAR_BASE_DIR . '/modules/../modules/Accounts/Account.php',
                'File name violation: directory traversal detected'
            ),
        );
    }

    /**
     * @covers ::varsFromInclude
     * @dataProvider providerTestVarsFromInclude
     */
    public function testVarsFromInclude($content, array $vars, array $expected)
    {
        $file = 'SugarAutoLoaderTestVarsFromInclude.php';
        $this->createPhpTestFile($file, $content);
        $actual = \SugarAutoLoader::varsFromInclude($file, $vars);
        $this->assertSame($expected, $actual);
    }

    public function providerTestVarsFromInclude()
    {
        return array(
            array(
                array(
                    'vardef1' => array('foo' => 'bar'),
                    'vardef2' => array('beer' => 'buzz'),
                ),
                array('vardef'),
                array('vardef' => null),
            ),
            array(
                array(
                    'vardef1' => array('happy' => 'joy'),
                    'vardef2' => array('sad' => 'bugs'),
                ),
                array('vardef1'),
                array('vardef1' => array('happy' => 'joy')),
            ),
            array(
                array(
                    'vardef1' => array('happy' => 'joy'),
                    'vardef2' => array('sad' => 'bugs'),
                ),
                array('vardef2'),
                array('vardef2' => array('sad' => 'bugs')),
            ),
            array(
                array(
                    'vardef1' => array('happy' => 'joy'),
                    'vardef2' => array('sad' => 'bugs'),
                ),
                array('vardef1', 'vardef2'),
                array(
                    'vardef1' => array('happy' => 'joy'),
                    'vardef2' => array('sad' => 'bugs'),
                ),
            ),
            array(
                array(
                    'vardef1' => array('happy' => 'joy'),
                    'vardef2' => array('sad' => 'bugs'),
                ),
                array('vardef1', 'bogus', 'vardef2'),
                array(
                    'vardef1' => array('happy' => 'joy'),
                    'bogus' => null,
                    'vardef2' => array('sad' => 'bugs'),
                ),
            ),
        );
    }

    /**
     * @covers ::varFromInclude
     * @dataProvider providerTestVarFromInclude
     */
    public function testVarFromInclude($content, $var, $expected)
    {
        $file = 'SugarAutoLoaderTestVarFromInclude.php';
        $this->createPhpTestFile($file, $content);
        $actual = \SugarAutoLoader::varFromInclude($file, $var);
        $this->assertSame($expected, $actual);
    }


    public function providerTestVarFromInclude()
    {
        return array(
            array(
                array(
                    'vardef' => array('foo' => 'bar'),
                ),
                'vardef',
                array('foo' => 'bar'),
            ),
            array(
                array(
                    'vardef1' => array('foo' => 'bar'),
                    'vardef2' => array('beer' => 'buzz'),
                ),
                'vardef',
                null,
            ),
            array(
                array(
                    'vardef1' => array('happy' => 'joy'),
                    'vardef2' => array('sad' => 'bugs'),
                ),
                'vardef1',
                array('happy' => 'joy'),
            ),
            array(
                array(
                    'vardef1' => array('happy' => 'joy'),
                    'vardef2' => array('sad' => 'bugs'),
                ),
                'vardef2',
                array('sad' => 'bugs'),
            ),
        );
    }

    /**
     * Create PHP file with variables
     * @param string $file
     * @param array $vars
     */
    protected function createPhpTestFile($file, array $vars)
    {
        $content = '<?php' . PHP_EOL;
        foreach ($vars as $varName => $varContent) {
            $content .= '$' . $varName . ' = ' . var_export($varContent, true) . ';' . PHP_EOL;
        }
        $this->createFile($file, $content);
    }

    /**
     * Create test file which is cleaned up after every test
     * @param string $file
     * @param string $content
     */
    protected function createFile($file, $content)
    {
        $this->testFiles[] = $file;
        file_put_contents($file, $content);
    }
}
