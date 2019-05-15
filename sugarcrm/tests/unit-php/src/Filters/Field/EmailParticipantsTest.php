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

namespace Sugarcrm\SugarcrmTests\Filters\Field;

use BeanFactory;
use ServiceBase;
use SugarApiExceptionInvalidParameter;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Filters\Field\EmailParticipants;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Filters\Field\EmailParticipants
 */
class EmailParticipantsTest extends TestCase
{
    /**
     * @covers ::__construct
     * @expectedException SugarApiExceptionInvalidParameter
     */
    public function testInOperandIsRequired()
    {
        $filter = [
            '$not_in' => [
                [
                    'parent_type' => 'Contacts',
                    'parent_id' => 'abc',
                ],
            ],
        ];

        $api = $this->getMockForAbstractClass(ServiceBase::class);
        $field = new EmailParticipants($api, 'from_collection', $filter);
    }

    /**
     * @covers ::__construct
     * @expectedException SugarApiExceptionInvalidParameter
     */
    public function testOnlyInOperandIsSupported()
    {
        $filter = [
            '$in' => [
                [
                    'parent_type' => 'Contacts',
                    'parent_id' => 'abc',
                ],
            ],
            '$not_in' => [
                [
                    'parent_type' => 'Contacts',
                    'parent_id' => 'def',
                ],
            ],
            '$equals' => 'ghi',
        ];

        $api = $this->getMockForAbstractClass(ServiceBase::class);
        $field = new EmailParticipants($api, 'from_collection', $filter);
    }

    /**
     * @covers ::__construct
     * @expectedException SugarApiExceptionInvalidParameter
     */
    public function testUnrecognizedFieldname()
    {
        $filter = [
            '$in' => [
                [
                    'parent_type' => 'Contacts',
                    'parent_id' => 'abc',
                ],
            ],
        ];

        $api = $this->getMockForAbstractClass(ServiceBase::class);
        $field = new EmailParticipants($api, 'foo', $filter);
    }

    public function fieldNameProvider()
    {
        return [
            'field: from_collection' => [
                'from_collection',
                'from',
            ],
            'field: to_collection' => [
                'to_collection',
                'to',
            ],
            'field: cc_collection' => [
                'cc_collection',
                'cc',
            ],
            'field: bcc_collection' => [
                'bcc_collection',
                'bcc',
            ],
        ];
    }

    /**
     * @covers ::unformat
     * @covers \Sugarcrm\Sugarcrm\Filters\Operand\EmailParticipants
     * @dataProvider fieldNameProvider
     */
    public function testUnformat(string $fieldName, string $link)
    {
        $filter = [
            '$in' => [
                [
                    '_link' => $link,
                    'parent_type' => 'Contacts',
                    'parent_id' => 'abc',
                    'parent_name' => 'Joe Walsh',
                    'parent' => [
                        'type' => 'Contacts',
                        'id' => 'abc',
                        'name' => 'Joe Walsh',
                        '_acl' => [
                            'fields' => (object)[],
                        ],
                        '_erased_fields' => [],
                    ],
                    'email_address_id' => 'ghi',
                    'email_address' => 'jwalsh@example.com',
                    'email_addresses' => [
                        'id' => 'ghi',
                        'email_address' => 'jwalsh@example.com',
                        '_acl' => [
                            'fields' => (object)[],
                        ],
                        '_erased_fields' => [],
                    ],
                ],
                [
                    '_link' => $link,
                    'parent_type' => 'Contacts',
                    'parent_id' => 'def',
                    'parent_name' => 'Tommy Hunter',
                    'parent' => [
                        'type' => 'Contacts',
                        'id' => 'def',
                        'name' => 'Tommy Hunter',
                        '_acl' => [
                            'fields' => (object)[],
                        ],
                        '_erased_fields' => [],
                    ],
                    'email_address_id' => 'jkl',
                    'email_address' => 'thunter@example.com',
                    'email_addresses' => [
                        'id' => 'jkl',
                        'email_address' => 'thunter@example.com',
                        '_acl' => [
                            'fields' => (object)[],
                        ],
                        '_erased_fields' => [],
                    ],
                ],
            ],
        ];

        $api = $this->getMockForAbstractClass(ServiceBase::class);
        $field = new EmailParticipants($api, $fieldName, $filter);

        $actual = $field->unformat();

        $expected = [
            '$in' => [
                [
                    'parent_type' => 'Contacts',
                    'parent_id' => 'abc',
                    'email_address_id' => 'ghi',
                ],
                [
                    'parent_type' => 'Contacts',
                    'parent_id' => 'def',
                    'email_address_id' => 'jkl',
                ],
            ],
        ];
        $this->assertEquals($expected, $actual);
    }
}
