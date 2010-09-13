<?php
require_once 'include/Localization/Localization.php';

class LocalizationTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp() 
    {
        $this->_locale = new Localization();
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
    }
    
    public function tearDown()
    {
    	SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }
    
    public function testGetLocaleFormattedName()
    {
    	$this->_user->setPreference('default_locale_name_format', 't s f l');
    	$firstName = 'Mason';
    	$lastName = 'Hu';
    	$title = 'Saler';
    	$salution = 'Mr.';
    	$expectedOutput = 'Saler Mr. Mason Hu';
    	$outputName = $this->_locale->getLocaleFormattedName($firstName, $lastName, $salution, $title, '',$this->_user);
    	$this->assertEquals($expectedOutput, $outputName);
    	
    	$this->_user->setPreference('default_locale_name_format', 'l f');
    	$expectedOutput = 'Hu Mason';
    	$outputName = $this->_locale->getLocaleFormattedName($firstName, $lastName, '', '', '',$this->_user);
    	$this->assertEquals($expectedOutput, $outputName);
    	
    }
    
    public function testCurrenciesLoadingCorrectly()
    {
        global $sugar_config;
        
        $currencies = $this->_locale->getCurrencies();
        
        $this->assertEquals($currencies['-99']['name'],$sugar_config['default_currency_name']);
        $this->assertEquals($currencies['-99']['symbol'],$sugar_config['default_currency_symbol']);
        $this->assertEquals($currencies['-99']['conversion_rate'],1);
    }
    
    public function testConvertingUnicodeStringBetweenCharsets()
    {
        $string = "アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモガギグゲゴザジズゼゾダヂヅデド";
        
        $convertedString = $this->_locale->translateCharset($string,'UTF-8','EUC-CN');
        $this->assertNotEquals($string,$convertedString);
        
        // test for this working by being able to convert back and the string match
        $convertedString = $this->_locale->translateCharset($convertedString,'EUC-CN','UTF-8');
        $this->assertEquals($string,$convertedString);
    }
    
    public function testCanDetectAsciiEncoding()
    {
        $string = 'string';
        
        $this->assertEquals(
            $this->_locale->detectCharset($string),
            'ASCII'
            );
    }
    
    public function testCanDetectUtf8Encoding()
    {
        $string = 'アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモガギグゲゴザジズゼゾダヂヅデド';
        
        $this->assertEquals(
            $this->_locale->detectCharset($string),
            'UTF-8'
            );
    }
    
    public function testGetNameJsCorrectlySpecifiesMissingOrEmptyParameters()
    {
        global $app_strings;
        
        $app_strings = return_application_language($GLOBALS['current_language']);
        
        $first = 'First';
        $last = 'Last';
        $salutation = 'Sal';
        $title = 'Title';
        
        $ret = $this->_locale->getNameJs($first,$last,$salutation);
        
        $this->assertRegExp("/stuff\['s'\] = '$salutation';/",$ret);
        $this->assertRegExp("/stuff\['f'\] = '$first';/",$ret);
        $this->assertRegExp("/stuff\['l'\] = '$last';/",$ret);
        $this->assertRegExp("/stuff\['t'\] = '{$app_strings['LBL_LOCALE_NAME_EXAMPLE_TITLE']}';/",$ret);
        
        $ret = $this->_locale->getNameJs('',$last,$salutation);
        
        $this->assertRegExp("/stuff\['s'\] = '$salutation';/",$ret);
        $this->assertRegExp("/stuff\['f'\] = '{$app_strings['LBL_LOCALE_NAME_EXAMPLE_FIRST']}';/",$ret);
        $this->assertRegExp("/stuff\['l'\] = '$last';/",$ret);
        $this->assertRegExp("/stuff\['t'\] = '{$app_strings['LBL_LOCALE_NAME_EXAMPLE_TITLE']}';/",$ret);
    }
    
    public function testGetPrecedentPreferenceWithUserPreference()
    {
        $backup = $GLOBALS['sugar_config']['export_delimiter'];
        $GLOBALS['sugar_config']['export_delimiter'] = 'John is Cool';
        $this->_user->setPreference('export_delimiter','John is Really Cool');
        
        $this->assertEquals(
            $this->_locale->getPrecedentPreference('export_delimiter',$this->_user),
            $this->_user->getPreference('export_delimiter')
            );
        
        $GLOBALS['sugar_config']['export_delimiter'] = $backup;
    }
    
    public function testGetPrecedentPreferenceWithNoUserPreference()
    {
        $backup = $GLOBALS['sugar_config']['export_delimiter'];
        $GLOBALS['sugar_config']['export_delimiter'] = 'John is Cool';
        
        $this->assertEquals(
            $this->_locale->getPrecedentPreference('export_delimiter',$this->_user),
            $GLOBALS['sugar_config']['export_delimiter']
            );
        
        $GLOBALS['sugar_config']['export_delimiter'] = $backup;
    }
    
    /**
     * @ticket 33086
     */
    public function testGetPrecedentPreferenceWithUserPreferenceAndSpecifiedConfigKey()
    {
        $backup = $GLOBALS['sugar_config']['export_delimiter'];
        $GLOBALS['sugar_config']['export_delimiter'] = 'John is Cool';
        $this->_user->setPreference('export_delimiter','');
        $GLOBALS['sugar_config']['default_random_setting_for_localization_test'] = 'John is not Cool at all';
        
        $this->assertEquals(
            $this->_locale->getPrecedentPreference('export_delimiter',$this->_user,'default_random_setting_for_localization_test'),
            $GLOBALS['sugar_config']['default_random_setting_for_localization_test']
            );
        
        $backup = $GLOBALS['sugar_config']['export_delimiter'];
        unset($GLOBALS['sugar_config']['default_random_setting_for_localization_test']);
    }
    
    /**
     * @ticket 39171
     */
    public function testGetPrecedentPreferenceForDefaultEmailCharset()
    {
        $emailSettings = array('defaultOutboundCharset' => 'something fun');
        $this->_user->setPreference('emailSettings',$emailSettings, 'Emails');
        
        $this->assertEquals(
            $this->_locale->getPrecedentPreference('default_email_charset',$this->_user),
            $emailSettings['defaultOutboundCharset']
            );
    }
}
