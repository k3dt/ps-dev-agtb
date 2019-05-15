<?php declare(strict_types=1);
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

namespace Sugarcrm\Sugarcrm\Filters\Operand;

use ServiceBase;
use Sugarcrm\Sugarcrm\Filters\Filterable;
use Sugarcrm\Sugarcrm\Filters\FilterableDefaultImplementation;

/**
 * Formats or unformats a $following filter.
 */
final class Following implements Filterable
{
    use FilterableDefaultImplementation;

    /**
     * The API controller.
     *
     * @var ServiceBase
     */
    private $api;

    /**
     * Constructor.
     *
     * @param ServiceBase $api Provides the API context.
     */
    public function __construct(ServiceBase $api)
    {
        $this->api = $api;
        $this->filter = ''; // The $following value is always an empty string.
    }
}
