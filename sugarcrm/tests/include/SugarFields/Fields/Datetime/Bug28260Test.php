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
 
require_once('include/SugarFields/Fields/Datetime/SugarFieldDatetime.php');

$timedate = TimeDate::getInstance();

class Bug28260Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $user;
    
	public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->user = $GLOBALS['current_user'];
	}

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($this->user);
        unset($GLOBALS['current_user']);
    }
    
    public function _providerEmailTemplateFormat()
    {
        return array(
            array('10/11/2010 13:00','10/11/2010 13:00', 'm/d/Y', 'H:i' ), 
            array('11/10/2010 13:00','11/10/2010 13:00', 'd/m/Y', 'H:i' ), 
            array('2010-10-11 13:00:00','10/11/2010 13:00', 'm/d/Y', 'H:i' ), 
            array('2010-10-11 13:00:00','11/10/2010 13:00', 'd/m/Y', 'H:i' ), 
            array('2010-10-11 13:00:00','10-11-2010 13:00', 'm-d-Y', 'H:i' ), 
            array('2010-10-11 13:00:00','11-10-2010 13:00', 'd-m-Y', 'H:i' ), 
            array('2010-10-11 13:00:00','2010-10-11 13:00', 'Y-m-d', 'H:i' )                
        );   
    }
    
     /**
     * @dataProvider _providerEmailTemplateFormat
     */
	public function testEmailTemplateFormat($unformattedValue, $expectedValue, $dateFormat, $timeFormat)
	{
	    $GLOBALS['sugar_config']['default_date_format'] = $dateFormat;
		$GLOBALS['sugar_config']['default_time_format'] = $timeFormat;
        $GLOBALS['current_user']->setPreference('datef', $dateFormat);
		$GLOBALS['current_user']->setPreference('timef', $timeFormat);
		
        require_once('include/SugarFields/SugarFieldHandler.php');
   		$sfr = SugarFieldHandler::getSugarField('datetime');
    	$formattedValue = $sfr->getEmailTemplateValue($unformattedValue,array('type'=>'datetime'), array('notify_user' => $this->user));
    	
   	 	$this->assertSame($expectedValue, $formattedValue);
    }
}