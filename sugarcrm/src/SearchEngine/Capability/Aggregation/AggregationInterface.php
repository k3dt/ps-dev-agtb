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

namespace Sugarcrm\Sugarcrm\SearchEngine\Capability\Aggregation;

use Sugarcrm\Sugarcrm\SearchEngine\Capability\GlobalSearch\GlobalSearchInterface;

/**
 *
 * Aggregation (facet) support interface
 *
 */
interface AggregationInterface extends GlobalSearchInterface
{
    /**
     * Query cross module aggregations, default false
     * @param boolean $toggle
     * @return AggregationInterface
     */
    public function crossModuleAgg($toggle);

    /**
     * Query module specific aggregations, default empty
     * @param array $list
     * @return AggregationInterface
     */
    public function moduleAggs(array $list);
}
