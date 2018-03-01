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

namespace Sugarcrm\SugarcrmTests\Security\Subject\Formatter;

use Localization;
use Sugarcrm\Sugarcrm\Security\Subject\Formatter\BeanFormatter;
use SugarTestAccountUtilities;
use SugarTestHelper;
use SugarTestUserUtilities;

/**
 * @covers \Sugarcrm\Sugarcrm\Security\Subject\Formatter\BeanFormatter
 */
class BeanFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BeanFormatter
     */
    private $formatter;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('current_user');
    }

    protected function setUp()
    {
        parent::setUp();

        $this->formatter = new BeanFormatter(
            Localization::getObject()
        );
    }

    public static function tearDownAfterClass()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        parent::tearDownAfterClass();
    }

    /**
     * @test
     */
    public function formatPersons()
    {
        $user = SugarTestUserUtilities::createAnonymousUser(true, false, [
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $subjects = $this->formatter->formatBatch([
            [
                '_type' => 'user',
                'id' => $user->id,
                '_module' => 'Users',
            ],
            [
                '_type' => 'user',
                'id' => 'unknown-id',
                '_module' => 'Users',
            ],
        ]);

        $this->assertSame([
            [
                '_type' => 'user',
                'id' => $user->id,
                '_module' => 'Users',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'name' => 'John Doe',
            ],
            [
                '_type' => 'user',
                'id' => 'unknown-id',
                '_module' => 'Users',
            ],
        ], $subjects);
    }

    /**
     * @test
     */
    public function formatNonPerson()
    {
        $account = SugarTestAccountUtilities::createAccount(null, [
            'name' => 'SugarCRM',
        ]);

        $subjects = $this->formatter->formatBatch([
            [
                '_type' => 'account',
                'id' => $account->id,
                '_module' => 'Accounts',
            ],
        ]);

        $this->assertSame([
            [
                '_type' => 'account',
                'id' => $account->id,
                '_module' => 'Accounts',
                'name' => 'SugarCRM',
            ],
        ], $subjects);
    }

    /**
     * @test
     */
    public function formatArbitraryStructure()
    {
        $jim = SugarTestUserUtilities::createAnonymousUser(true, false, [
            'first_name' => 'Jim',
            'last_name' => 'Brennan',
        ]);

        $max = SugarTestUserUtilities::createAnonymousUser(true, false, [
            'first_name' => 'Max',
            'last_name' => 'Jensen',
        ]);

        $subjects = $this->formatter->formatBatch([
            [
                '_type' => 'pair-of-users',
                'user-1' => [
                    'id' => $max->id,
                    '_module' => 'Users',
                ],
                'user-2' => [
                    'id' => $jim->id,
                    '_module' => 'Users',
                ],
            ],
        ]);

        $this->assertSame([
            [
                '_type' => 'pair-of-users',
                'user-1' => [
                    'id' => $max->id,
                    '_module' => 'Users',
                    'first_name' => 'Max',
                    'last_name' => 'Jensen',
                    'name' => 'Max Jensen',
                ],
                'user-2' => [
                    'id' => $jim->id,
                    '_module' => 'Users',
                    'first_name' => 'Jim',
                    'last_name' => 'Brennan',
                    'name' => 'Jim Brennan',
                ],
            ],
        ], $subjects);
    }
}