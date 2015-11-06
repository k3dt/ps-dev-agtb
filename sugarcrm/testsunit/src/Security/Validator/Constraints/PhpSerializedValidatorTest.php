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

namespace Sugarcrm\SugarcrmTestsUnit\Security\Validator\Constraints;

use Sugarcrm\Sugarcrm\Security\Validator\Constraints\PhpSerialized;
use Sugarcrm\Sugarcrm\Security\Validator\Constraints\PhpSerializedValidator;
use Sugarcrm\SugarcrmTests\Security\Validator\Constraints\AbstractConstraintValidatorTest;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Validator\Constraints\PhpSerializedValidator
 *
 */
class PhpSerializedValidatorTest extends AbstractConstraintValidatorTest
{
    /**
     * {@inheritdoc}
     */
    protected function createValidator()
    {
        return new PhpSerializedValidator();
    }

    /**
     * @covers ::validate
     */
    public function testNullIsValid()
    {
        $this->validator->validate(null, new PhpSerialized());
        $this->assertNoViolation();
    }

    /**
     * @covers ::validate
     */
    public function testEmptyStringIsValid()
    {
        $this->validator->validate('', new PhpSerialized());
        $this->assertNoViolation();
    }

    /**
     * @covers ::validate
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testExpectsStringCompatibleType()
    {
        $this->validator->validate(new \stdClass(), new PhpSerialized());
    }

    /**
     * @covers ::validate
     * @covers \Sugarcrm\Sugarcrm\Security\Validator\ConstraintReturnValue::getFormattedReturnValue
     * @covers \Sugarcrm\Sugarcrm\Security\Validator\ConstraintReturnValue::setFormattedReturnValue
     * @dataProvider providerTestValidValues
     */
    public function testValidValues($value, $base64Encoded, $unserialized)
    {
        $constraint = new PhpSerialized();
        $constraint->base64Encoded = $base64Encoded;
        $this->validator->validate($value, $constraint);
        $this->assertNoViolation();
        $this->assertSame($unserialized, $constraint->getFormattedReturnValue());
    }

    public function providerTestValidValues()
    {
        return array(

            // plain serialized strings
            array('N;', false, null),
            array('b:0;', false, false),
            array('b:1;', false, true),
            array('i:10;', false, 10),
            array('d:12.199999999999999;', false, 12.2),
            array('s:6:"String";', false, 'String'),
            array('a:1:{s:3:"foo";s:3:"bar";}', false, array('foo' => 'bar')),

            // base64 encoded tests
            array(
                'Tjs=',
                true,
                null,
            ),
            array(
                'YjowOw==',
                true,
                false,
            ),
            array(
                'YjoxOw==',
                true,
                true,
            ),
            array(
                'aToxMDs=',
                true,
                10,
            ),
            array(
                'ZDoxMi4xOTk5OTk5OTk5OTk5OTk7',
                true,
                12.2,
            ),
            array(
                'czo2OiJTdHJpbmciOw==',
                true,
                'String',
            ),
            array(
                'YToxOntzOjM6ImZvbyI7czozOiJiYXIiO30=',
                true,
                array('foo' => 'bar'),
            ),
        );
    }

    /**
     * @covers ::validate
     * @dataProvider providerTestInvalidValues
     */
    public function testInvalidValues($value, $code, $msg)
    {
        $constraint = new PhpSerialized(array(
            'message' => 'testMessage',
        ));

        $this->validator->validate($value, $constraint);

        $this->buildViolation('testMessage')
            ->setParameter('%msg%', $msg)
            ->setCode($code)
            ->setInvalidValue($value)
            ->assertRaised();
    }

    public function providerTestInvalidValues()
    {
        return array(
            array(
                'O:8:"stdClass":1:{s:3:"foo";s:3:"bar";}',
                PhpSerialized::ERROR_OBJECT_NOT_ALLOWED,
                'object(s) not allowed',
            ),
            array(
                'a:2:{s:3:"foo";s:3:"bar";s:3:"baz";O:8:"stdClass":1:{s:3:"foo";s:3:"bar";}}',
                PhpSerialized::ERROR_OBJECT_NOT_ALLOWED,
                'object(s) not allowed',
            ),
            array(
                'O:8:',
                PhpSerialized::ERROR_OBJECT_NOT_ALLOWED,
                'object(s) not allowed',
            ),
            array(
                'mambojambo',
                PhpSerialized::ERROR_UNSERIALIZE,
                'unserialize error',
            ),
            array(
                'C:6:"FooBar":3:{baz}',
                PhpSerialized::ERROR_OBJECT_NOT_ALLOWED,
                'object(s) not allowed',
            ),
        );
    }
}
