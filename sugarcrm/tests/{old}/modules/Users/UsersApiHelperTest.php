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

class UsersApiHelperTest extends TestCase
{
    protected $helper;
    protected $bean = null;

    protected function setUp() : void
    {
        SugarTestHelper::setUp('current_user');

        $this->bean = BeanFactory::newBean('Users');
        $this->bean->id = create_guid();

        $this->helper = $this->getMockBuilder('UsersApiHelper')
            ->setMethods(['checkUserAccess'])
            ->setConstructorArgs([new UsersServiceMockup()])
            ->getMock();
    }

    protected function tearDown() : void
    {
        unset($this->bean);
        SugarTestHelper::tearDown();
    }

    public function testFormatForApi_HasAccessArgumentsPassed_ReturnsHasAccessResult()
    {
        $options = [
            'args' => [
                'has_access_module' => 'Foo',
                'has_access_record' => '123',
            ],
        ];

        $this->helper->expects($this->once())
            ->method('checkUserAccess')
            ->will($this->returnValue(true));

        $data = $this->helper->formatForApi($this->bean, [], $options);
        $this->assertEquals($data['has_access'], true, "Has Access should be true");
    }

    public function testFormatForApi_NoHasAccessArgumentsPassed_DoesNotReturnHasAccessResult()
    {
        $options = [
            'args' => [],
        ];

        $this->helper->expects($this->never())
            ->method('checkUserAccess');

        $data = $this->helper->formatForApi($this->bean, [], $options);
        $this->assertEquals(array_key_exists('has_access', $data), false, "Has Access data should not exist");
    }

    public function testPopulateFromApi_newBean()
    {
        $user = BeanFactory::newBean('Users');
        $user->new_with_id = true;
        $user->id = '';

        $this->expectException(SugarApiExceptionMissingParameter::class);

        $this->helper->populateFromApi($user, [], []);
    }

    public function testPopulateFromApi_updateBean()
    {
        $test = $this->helper->populateFromApi($GLOBALS['current_user'], [], []);
        $this->assertTrue($test);
    }
}

class UsersServiceMockup extends ServiceBase
{
    public function __construct()
    {
        $this->user = $GLOBALS['current_user'];
    }
    public function execute()
    {
    }
    protected function handleException(Exception $exception)
    {
    }
}
