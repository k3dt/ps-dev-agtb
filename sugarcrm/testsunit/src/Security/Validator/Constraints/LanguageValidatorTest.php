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

use Sugarcrm\Sugarcrm\Security\Validator\Constraints\Language;
use Sugarcrm\Sugarcrm\Security\Validator\Constraints\LanguageValidator;
use Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Validator\Constraints\LanguageValidator
 *
 */
class LanguageValidatorTest extends AbstractConstraintValidatorTest
{
    /**
     * {@inheritdoc}
     */
    protected function createValidator()
    {
        return new LanguageValidator(array(
            'en_US' => 'English (US)',
            'cs_CZ' => ' Czech language',
        ));
    }

    /**
     * @covers ::validate
     * @dataProvider providerTestValidValues
     */
    public function testValidValues($value)
    {
        $constraint = new Language();
        $this->validator->validate($value, $constraint);
        $this->assertNoViolation();
    }

    public function providerTestValidValues()
    {
        return array(
            array(
                'en_US',
            ),
            array(
                'cs_CZ',
            ),
        );
    }

    /**
     * @covers ::validate
     * @dataProvider providerTestInvalidValues
     */
    public function testInvalidValues($value, $code, $msg)
    {
        $constraint = new Language(array(
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
                array('blah'),
                Language::ERROR_STRING_REQUIRED,
                'string expected',
            ),
            array(
                'en-US',
                Language::ERROR_LANGUAGE_NOT_FOUND,
                'language not found',
            ),
            array(
                'en-us',
                Language::ERROR_LANGUAGE_NOT_FOUND,
                'language not found',
            ),
            array(
                "en_us",
                Language::ERROR_LANGUAGE_NOT_FOUND,
                'language not found',
            ),
        );
    }
}
