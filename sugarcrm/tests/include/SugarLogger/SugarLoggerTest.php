<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

class SugarLoggerTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        // reset the logger level
        $level = SugarConfig::getInstance()->get('logger.level');
        if (!empty($level))
            $GLOBALS['log']->setLevel($level);
    }

    public function providerWriteLogEntries()
    {
        return array(
            array('debug','debug','foo1',true,'[DEBUG] foo1'),
            array('debug','info','foo2',true,'[INFO] foo2'),
            array('debug','warn','foo3',true,'[WARN] foo3'),
            array('debug','error','foo4',true,'[ERROR] foo4'),
            array('debug','fatal','foo5',true,'[FATAL] foo5'),
            array('debug','security','foo6',true,'[SECURITY] foo6'),
            array('fatal','warn','foo7',false,'[WARN] foo7'),
            );
    }

    /**
     * @dataProvider providerWriteLogEntries
     */
    public function testWriteLogEntries(
        $currentLevel,
        $logLevel,
        $logMessage,
        $shouldMessageBeWritten,
        $messageWritten
        )
    {
        $GLOBALS['log']->setLevel($currentLevel);
        $GLOBALS['log']->$logLevel($logMessage);

        $config = SugarConfig::getInstance();
        $ext = $config->get('logger.file.ext');
        $logfile = $config->get('logger.file.name');
        $log_dir = $config->get('log_dir');
        $log_dir = $log_dir . (empty($log_dir)?'':'/');
        $file_suffix = $config->get('logger.file.suffix');
        $date_suffix = "";

        if( !empty($file_suffix) )
        {
            $date_suffix = "_" . date(str_replace("%", "", $file_suffix));
        }


        $logFile = file_get_contents($log_dir . $logfile . $date_suffix . $ext);

        if ( $shouldMessageBeWritten )
            $this->assertContains($messageWritten,$logFile);
        else
            $this->assertNotContains($messageWritten,$logFile);
    }

    public function testAssertLogging()
    {
        $GLOBALS['log']->setLevel('debug');
        $GLOBALS['log']->assert('this was asserted true',true);
        $GLOBALS['log']->assert('this was asserted false',false);

        $config = SugarConfig::getInstance();
        $ext = $config->get('logger.file.ext');
        $logfile = $config->get('logger.file.name');
        $log_dir = $config->get('log_dir');
        $log_dir = $log_dir . (empty($log_dir)?'':'/');
        $file_suffix = $config->get('logger.file.suffix');
        $date_suffix = "";

        if( !empty($file_suffix) )
        {
            $date_suffix = "_" . date(str_replace("%", "", $file_suffix));
        }

        $logFile = file_get_contents($log_dir . $logfile . $date_suffix . $ext);

        $this->assertContains('[DEBUG] this was asserted false',$logFile);
        $this->assertNotContains('[DEBUG] this was asserted true',$logFile);
    }

    /**
     * @bug#50265: Parse the file size format string in the field for log size
     */
    public function providerFileSizes()
    {
        return array(
            array("10MB", 10 * 1024 * 1024, true),
            array("3KB", 3 * 1024, true),
            array("3 kb", 3 * 1024, true),
            array(" 2Mb", 2 * 1024 * 1024, true),
            array("500 Bytes", 500 * 1, true),
            array(".5Mb", 0.5 * 1024 * 1024, true),
            array("0.7kb", 0.7 * 1024, true),
            array(".0.5Mb", 0.5 * 1024 * 1024, false),
            array("1GBtyes", 1024 * 1024 * 1024, true),
            array("1 Bytes", 1 * 1, true),
            array("1 FB", 1 * 1, false),
        );
    }

    /**
     * @dataProvider providerFileSizes
     */
    public function testFileSizes($size, $value, $assert_equal)
    {

        $units = array(
            'b' => 1,
            'k' => 1024,
            'm' => 1024 * 1024,
            'g' => 1024 * 1024 * 1024,
        );


        if( preg_match('/^\s*([0-9]+\.[0-9]+|\.?[0-9]+)\s*(k|m|g|b)(b?ytes)?/i', $size, $match) )
        {
            $file_size = $match[1] * $units[strtolower($match[2])];
            if($assert_equal)
            {
                $this->assertEquals($value, $file_size, "[DEBUG] File size parsed invalid");
            }
            else
            {
                $this->assertNotEquals($value, $file_size, "[DEBUG] File size parsed invalid");
            }

        } else {
            $this->assertFalse($assert_equal, '[DEBUG]Unitformat is out of the expression boundary.');
        }

    }

   /**
     * bug#: 50188
     * Fix the Logger to create dateformat suffix in the file name
     */
    public function testFileName() {

        $config = SugarConfig::getInstance();
        $file_name = $config->get('logger.file.name');
        $log_dir = $config->get('log_dir');
        $log_dir = $log_dir . (empty($log_dir)?'':'/');
        $ext = $config->get('logger.file.ext');

        $file_suffix = $config->get('logger.file.suffix');
        //reviewing the suffix in the global configuration stores in the valid format
        $this->assertArrayHasKey($file_suffix, SugarLogger::$filename_suffix, 'File suffix type is invalid');

        $invalid_file_suffix = "%d_y%s";
        $this->assertArrayNotHasKey($invalid_file_suffix, SugarLogger::$filename_suffix, 'invalid format is included in the SugarLogger');

        $suffix_date_part = "";
        // IF there has been a suffix manually entered, let's include it,
        // otherwise this should be empty so we get "sugarcrm.log" in the full_path
        if( !empty( $file_suffix ) )
            $suffix_date_part = "_" . date(str_replace("%", "", $file_suffix));

        $full_path = $log_dir . $file_name . $suffix_date_part . $ext;
        $logger = new SugarLogger;
        //Asserting the file format the tester expects with the file format from the SugarLogger
        $this->assertEquals($full_path, $logger->getLogFileNameWithPath(), "SugarLogger generates invalid log file format");

        //If the logger returns correct file format, the file must exist in the path.
        $this->assertFileExists($full_path, "SugarLogger generates invalid log file format");
    }

    /**
     * @dataProvider providerWriteLogEntries
     */
    public function testWouldLog(
        $currentLevel,
        $logLevel,
        $logMessage,
        $shouldMessageBeWritten,
        $messageWritten
        )
    {
        $GLOBALS['log']->setLevel($currentLevel);
        $this->assertEquals($shouldMessageBeWritten, $GLOBALS['log']->wouldLog($logLevel));

    }
}
