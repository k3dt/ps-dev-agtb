<?php
//FILE SUGARCRM flav=ent ONLY
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

use Sugarcrm\Sugarcrm\ProcessManager;
use PHPUnit\Framework\TestCase;

class PMSERecordValidatorTest extends TestCase
{
    public function testValidateRequest()
    {
        $loggerMock = $this->getMockBuilder('PMSELogger')
                ->disableOriginalConstructor()
                ->setMethods(array('info', 'debug'))
                ->getMock();
        
        $recordValidatorMock = $this->getMockBuilder('PMSERecordValidator')
                ->disableOriginalConstructor()
                ->setMethods(NULL)
                ->getMock();
        $request = ProcessManager\Factory::getPMSEObject('PMSERequest');
        $recordValidatorMock->setLogger($loggerMock);
        $recordValidatorMock->validateRequest($request);
        $this->assertEquals(true, $request->isValid());
    }
}
