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

/**
 * @see https://confluence.jetbrains.com/display/PhpStorm/PhpStorm+Advanced+Metadata
 */
namespace PHPSTORM_META {
    use PHPUnit\Framework\TestCase;
    use Psr\Container\ContainerInterface;

    override(TestCase::createMock(0), map([
        '' => '@|PHPUnit_Framework_MockObject_MockObject',
    ]));
    override(TestCase::createPartialMock(0), map([
        '' => '@|PHPUnit_Framework_MockObject_MockObject',
    ]));
    override(ContainerInterface::get(0), map([
        '' => '@',
    ]));
}
