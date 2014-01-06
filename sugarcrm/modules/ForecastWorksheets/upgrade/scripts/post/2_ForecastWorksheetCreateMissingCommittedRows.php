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

class SugarUpgradeForecastWorksheetCreateMissingCommittedRows extends UpgradeScript
{
    public $order = 2200;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        // are we coming from 6.7 but before 7.0
        if (!version_compare($this->from_version, '6.7.0', '>=') ||
            !version_compare($this->from_version, '7.0', '<')) {
            return;
        }

        $this->log('Creating Missing Committed Rows For RLIs');
        $sql = "INSERT INTO forecast_worksheets " .
               "SELECT w.id, " .
                      "rli.name, " .
                      "w.date_modified as date_entered, " .
                      "w.date_modified, " .
                      "w.modified_user_id, " .
                      "w.modified_user_id, " .
                      "null as description, " .
                      "w.deleted, " .
                      "w.user_id as assigned_user_id, " .
                      "rli.team_id, " .
                      "rli.team_set_id, " .
                      "w.related_id as parent_id, " .
                      "'RevenueLineItems' as parent_type, " .
                      "rli.likely_case, " .
                      "rli.best_case, " .
                      "rli.worst_case, " .
                      "w.base_rate, " .
                      "w.currency_id, " .
                      "rli.date_closed, " .
                      "rli.date_closed_timestamp, " .
                      "rli.sales_stage, " .
                      "w.op_probability as probability, " .
                      "w.commit_stage, " .
                      "0 as draft, " .
                      "rli.opportunity_id, " .
                      "o.name as opportunity_name, " .
                      "a.name as account_name, " .
                      "rli.account_id, " .
                      "rli.campaign_id, " .
                      "c.name as campaign_name, " .
                      "rli.product_template_id, " .
                      "pt.name as product_template_name, " .
                      "rli.category_id, " .
                      "pc.name as category_name, " .
                      "null as sales_status, " .
                      "rli.next_step, " .
                      "rli.lead_source, " .
                      "rli.product_type, " .
                      "rli.list_price, " .
                      "rli.cost_price, " .
                      "rli.discount_price, " .
                      "rli.discount_amount, " .
                      "rli.quantity, " .
                      "rli.total_amount " .
               "FROM worksheet w " .
               "INNER JOIN revenue_line_items rli " .
                    "ON rli.id = w.related_id " .
               "INNER JOIN opportunities o " .
                    "ON o.id = rli.opportunity_id " .
               "INNER JOIN accounts a " .
                    "ON a.id = rli.account_id " .
               "LEFT OUTER JOIN campaigns c " .
                    "ON c.id = rli.campaign_id " .
               "LEFT OUTER JOIN product_templates pt " .
                    "ON pt.id = rli.product_template_id " .
               "LEFT OUTER JOIN product_categories pc " .
                    "ON pc.id = rli.category_id " .
               "WHERE w.version = 1 " .
                    "AND w.forecast_type = 'Direct' " .
                    "AND w.related_forecast_type = 'Product'";

        $result = $this->db->query($sql);
        
        $this->log('Added ' . $this->db->getAffectedRowCount($result) . ' Records');
        $this->log('Creating Missing Committed Rows For RLIs');
        
        $this->log('Creating Missing Committed Rows For Opportunities');
        $sql = "INSERT INTO forecast_worksheets " .
               "SELECT o.id, " .
                      "o.name, " .
                      "w.date_modified as date_entered, " .
                      "w.date_modified, " .
                      "w.modified_user_id, " .
                      "w.modified_user_id, " .
                      "null as description, " .
                      "w.deleted, " .
                      "w.user_id as assigned_user_id, " .
                      "o.team_id, " .
                      "o.team_set_id, " .
                      "o.id as parent_id, " .
                      "'Opportunities' as parent_type, " .
                      "o.amount as likely_case, " .
                      "o.best_case, " .
                      "o.worst_case, " .
                      "w.base_rate, " .
                      "w.currency_id, " .
                      "o.date_closed, " .
                      "o.date_closed_timestamp, " .
                      "o.sales_stage, " .
                      "w.op_probability as probability, " .
                      "w.commit_stage, " .
                      "0 as draft, " .
                      "o.id, " .
                      "o.name as opportunity_name, " .
                      "a.name as account_name, " .
                      "rli.account_id, " .
                      "rli.campaign_id, " .
                      "c.name as campaign_name, " .
                      "null as product_template_id, " .
                      "null as product_template_name, " .
                      "null as category_id, " .
                      "null as category_name, " .
                      "o.sales_status, " .
                      "o.next_step, " .
                      "o.lead_source, " .
                      "null as product_type, " .
                      "null as list_price, " .
                      "null as cost_price, " .
                      "null as discount_price, " .
                      "null as discount_amount, " .
                      "null as quantity, " .
                      "null as total_amount " .
               "FROM worksheet w " .
               "INNER JOIN revenue_line_items rli " .
               "ON rli.id = w.related_id " .
               "INNER JOIN opportunities o " .
               "ON o.id = rli.opportunity_id " .
               "INNER JOIN accounts a " .
               "ON a.id = rli.account_id " .
               "LEFT OUTER JOIN campaigns c " .
               "ON c.id = rli.campaign_id " .
               "WHERE w.version = 1 " .
                     "AND w.forecast_type = 'Direct' " .
                     "AND w.related_forecast_type = 'Product'";
        
        $result = $this->db->query($sql);
        
        $this->log('Added ' . $this->db->getAffectedRowCount($result) . ' Records');
        $this->log('Creating Missing Committed Rows For Opportunities');
    }
}
