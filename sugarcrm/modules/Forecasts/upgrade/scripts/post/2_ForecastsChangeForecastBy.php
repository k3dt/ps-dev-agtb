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
 * Copyright 2004-2013 SugarCRM Inc. All rights reserved.
 */
 
class SugarUpgradeForecastsChangeForecastBy extends UpgradeScript
{
    public $order = 2190;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        //Only run this on ent upgrades
        if (!$this->toFlavor("pro")) {
            return;
        }
        
        $this->log('Changing Forecast by from Opportunities to Revenue Line Items');
        $sql = "UPDATE config " .
               "SET value = 'RevenueLineItems' " .
               "WHERE category = 'Forecasts' " .
               "AND name = 'forecast_by'";
        $results = $this->db->query($sql);

        $this->log('Done Changing Forecast by from Opportunities to Revenue Line Items');
    }
}
