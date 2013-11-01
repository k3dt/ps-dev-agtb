<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

/**
 * Define the after_save hook that will process the hidden RevenueLineItem, if forecast is setup and
 * forecasting by Revenue Line Items
 */
$hook_array['after_save'][] = array(
    1,
    'processHiddenRevenueLineItem',
    'modules/Opportunities/OpportunityHooks.php',
    'OpportunityHooks',
    'processHiddenRevenueLineItem',
);
