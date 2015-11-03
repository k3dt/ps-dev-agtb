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

namespace Sugarcrm\Sugarcrm\Security\Validator\Constraints\Bean;

use Symfony\Component\Validator\Constraint;

/**
 *
 * @see ModuleNameValidator
 *
 */
class ModuleName extends Constraint
{
    const ERROR_STRING = 1;
    const ERROR_UNKNOWN_MODULE = 2;

    protected static $errorNames = array(
        self::ERROR_STRING => 'ERROR_STRING',
        self::ERROR_UNKNOWN_MODULE => 'ERROR_UNKNOWN_MODULE',
    );

    public $message = 'Invalid module %module%';
}
