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
 
require_once('modules/SugarFeed/SugarFeed.php');

class SugarFeedTest extends Sugar_PHPUnit_Framework_TestCase
{
	public function testEncodeUrls()
	{
        $this->markTestSkipped('Skipping as this is not needed for Sugar7. Should be removed on SC-1312');
		$message = "This test contains a url here http://www.sugarcrm.com and not here amazon.com";
        $text = SugarFeed::parseMessage($message);
        $this->assertContains("<a href='http://www.sugarcrm.com' target='_blank'>http://www.sugarcrm.com</a>",$text, "Url encoded incorrectly");
        $this->assertNotContains("http://amazon.com", $text, "Second link should not be encoded");
	}

    public function testGetUrls()
	{
        $this->markTestSkipped('Skipping as this is not needed for Sugar7. Should be removed on SC-1312');
		$message = "This test contains a url here http://www.sugarcrm.com and not here http://amazon.com";
        $result = getUrls($message);
        $this->assertContains('http://www.sugarcrm.com', $result,' Could not find first url.');
        $this->assertContains('http://amazon.com', $result,' Could not find second url.');
     }
}
?>