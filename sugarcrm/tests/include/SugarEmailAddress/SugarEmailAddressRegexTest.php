<?php
require_once 'include/SugarEmailAddress/SugarEmailAddress.php';

class SugarEmailAddressRegexTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function providerEmailAddressRegex()
	{
	    return array(
	        array('john@john.com',true),
	        array('----!john.com',false),
	        // For Bug 13765
	        array('st.-annen-stift@t-online.de',true),
	        );
	}
    
    /**
     * @group bug13765
     * @dataProvider providerEmailAddressRegex
     */
	public function testEmailAddressRegex($email, $valid) 
    {
        $sea = new SugarEmailAddress;
        
        if ( $valid ) {
            $this->assertRegExp($sea->regex,$email);
        }
        else {
            $this->assertNotRegExp($sea->regex,$email);
        }     
    }
}
