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

class SugarUpgradeProductMigrateToRLI extends UpgradeScript
{
    public $order = 2110;
    public $type = self::UPGRADE_DB;

    /**
     * Run the Upgrade Task
     *
     * The reason we need to do before task 2100 (where the Repair and Rebuild happens
     * is that when coming from 6.7 to 7, it will blow away the fields we added that we still need
     * data from.  There for we have to put the RLI module in-place so in another upgrade task we can
     * move/copy the data into the RLI table.
     */
    public function run()
    {
        // only run this when coming from a 6.x upgrade
        if (!version_compare($this->from_version, '7.0', "<")) {
            return;
        }

        $this->log('Migrating Products to Revenue Line Items.');

        // Only run this sql if coming from 6.5.. all Products are the result of Quotes, so we
        // need to copy over Products that are quoted and associated to an Opportunity
        if (version_compare($this->from_version, '6.7.0', "<")) {
            $this->log('Migrating 6.5 Products assigned to Quotes that have Opportunities.');
            $sql = "SELECT  p.id, " .
                           "p.name, " .
                           "p.date_entered, " .
                           "p.date_modified, " .
                           "p.modified_user_id, " .
                           "p.created_by, " .
                           "p.description, " .
                           "p.deleted, " .
                           "q.assigned_user_id, " .
                           "p.team_id, " .
                           "p.team_set_id, " .
                           "p.product_template_id, " .
                           "p.account_id, " .
                           "(p.discount_price * p.quantity) as total_amount, " . //calculate total_amount
                           "p.type_id, " .
                           "p.quote_id, " .
                           "p.manufacturer_id, " .
                           "p.category_id, " .
                           "p.mft_part_num, " .
                           "p.vendor_part_num, " .
                           "p.date_purchased, " .
                           "p.cost_price, " .
                           "p.discount_price, " .
                           "p.discount_amount, " .
                           "null as discount_rate_percent, " . //discount_rate_percent
                           "p.discount_amount_usdollar, " .
                           "p.discount_select, " .
                           "p.deal_calc, " .
                           "p.deal_calc_usdollar, " .
                           "p.list_price, " .
                           "p.cost_usdollar, " .
                           "p.discount_usdollar, " .
                           "p.list_usdollar, " .
                           "p.currency_id, " .
                           "(p.discount_usdollar / p.discount_price) as base_rate, " . //base_rate
                           "p.status, " .
                           "p.tax_class, " .
                           "p.website, " .
                           "p.weight, " .
                           "p.quantity, " .
                           "p.support_name, " .
                           "p.support_description, " .
                           "p.support_contact, " .
                           "p.support_term, " .
                           "p.date_support_expires, " .
                           "p.date_support_starts, " .
                           "p.pricing_formula, " .
                           "p.pricing_factor, " .
                           "p.serial_number, " .
                           "p.asset_number, " .
                           "p.book_value, " .
                           "p.book_value_usdollar, " .
                           "p.book_value_date, " .
                           "o.amount, " . //best_case
                           "o.amount, " . //likely_case
                           "o.amount, " . //worst_case
                           "o.date_closed, " .
                           "0 as date_closed_timestamp, " . //date_closed_timestamp -- needs to be updated later
                           "o.next_step, " .
                           "null as commit_stage, " . //commit_stage
                           "o.sales_stage, " .
                           "o.probability, " .
                           "o.lead_source, " .
                           "o.campaign_id, " .
                           "o.id, " .
                           "o.opportunity_type " .
                   "FROM products p  " .
                   "INNER JOIN quotes q  " .
                   "ON q.id = p.quote_id " .
                   "INNER JOIN quotes_opportunities qo " .
                   "ON qo.quote_id = q.id " .
                   "INNER JOIN opportunities o " .
                   "ON o.id = qo.opportunity_id";

            $results = $this->db->query($sql);
            $this->insertRows($results);

            $this->log('Done migrating 6.5 Products assigned to Quotes that have Opportunities.');
        }

        //Now we need to do some migration on the 6.7 data, which is a bit more like what we need in 7.
        if (version_compare($this->from_version, '6.7.0', ">=")) {
            $this->log('Migrating 6.7 Products with Opportunities and without Quotes.');
            $sql = "SELECT  p.id, " .
                           "p.name, " .
                           "p.date_entered, " .
                           "p.date_modified, " .
                           "p.modified_user_id, " .
                           "p.created_by, " .
                           "p.description, " .
                           "p.deleted, " .
                           "p.assigned_user_id, " .
                           "p.team_id, " .
                           "p.team_set_id, " .
                           "p.product_template_id, " .
                           "p.account_id, " .
                           "(p.discount_price * p.quantity) as total_amount, " . //calculate total amount
                           "p.type_id, " .
                           "p.quote_id, " .
                           "p.manufacturer_id, " .
                           "p.category_id, " .
                           "p.mft_part_num, " .
                           "p.vendor_part_num, " .
                           "p.date_purchased, " .
                           "p.cost_price, " .
                           "p.discount_price, " .
                           "p.discount_amount, " .
                           "null as discount_rate_percent, " . //discount_rate_percent
                           "p.discount_amount_usdollar, " .
                           "p.discount_select, " .
                           "p.deal_calc, " .
                           "p.deal_calc_usdollar, " .
                           "p.list_price, " .
                           "p.cost_usdollar, " .
                           "p.discount_usdollar, " .
                           "p.list_usdollar, " .
                           "p.currency_id, " .
                           "p.base_rate, " .
                           "p.status, " .
                           "p.tax_class, " .
                           "p.website, " .
                           "p.weight, " .
                           "p.quantity, " .
                           "p.support_name, " .
                           "p.support_description, " .
                           "p.support_contact, " .
                           "p.support_term, " .
                           "p.date_support_expires, " .
                           "p.date_support_starts, " .
                           "p.pricing_formula, " .
                           "p.pricing_factor, " .
                           "p.serial_number, " .
                           "p.asset_number, " .
                           "p.book_value, " .
                           "p.book_value_usdollar, " .
                           "p.book_value_date, " .
                           "p.best_case, " .
                           "p.likely_case, " .
                           "p.worst_case, " .
                           "p.date_closed, " .
                           "p.date_closed_timestamp, " .
                           "o.next_step, " .
                           "p.commit_stage, " .
                           "o.sales_stage, " .
                           "p.probability, " .
                           "o.lead_source, " .
                           "o.campaign_id, " .
                           "p.opportunity_id, " .
                           "o.opportunity_type " .
                   "FROM products p " .
                   "INNER JOIN opportunities o " .
                   "on o.id = p.opportunity_id " .
                   "WHERE p.opportunity_id IS NOT NULL " .
                   "AND (p.quote_id IS NULL OR p.quote_id = '')";
            $results = $this->db->query($sql);
            $this->insertRows($results);

            $this->log('Done migrating 6.7 Products with Opportunities and without Quotes.');

            $this->log('Migrating 6.7 Products assigned to Quotes that have Opportunities.');
            $sql = "SELECT  p.id, " .
                           "p.name, " .
                           "p.date_entered, " .
                           "p.date_modified, " .
                           "p.modified_user_id, " .
                           "p.created_by, " .
                           "p.description, " .
                           "p.deleted, " .
                           "q.assigned_user_id, " .
                           "p.team_id, " .
                           "p.team_set_id, " .
                           "p.product_template_id, " .
                           "p.account_id, " .
                           "(p.discount_price * p.quantity) as total_amount, " . //calculate total_amount
                           "p.type_id, " .
                           "p.quote_id, " .
                           "p.manufacturer_id, " .
                           "p.category_id, " .
                           "p.mft_part_num, " .
                           "p.vendor_part_num, " .
                           "p.date_purchased, " .
                           "p.cost_price, " .
                           "p.discount_price, " .
                           "p.discount_amount, " .
                           "null as discount_rate_percent, " . //discount_rate_percent
                           "p.discount_amount_usdollar, " .
                           "p.discount_select, " .
                           "p.deal_calc, " .
                           "p.deal_calc_usdollar, " .
                           "p.list_price, " .
                           "p.cost_usdollar, " .
                           "p.discount_usdollar, " .
                           "p.list_usdollar, " .
                           "p.currency_id, " .
                           "p.base_rate, " .
                           "p.status, " .
                           "p.tax_class, " .
                           "p.website, " .
                           "p.weight, " .
                           "p.quantity, " .
                           "p.support_name, " .
                           "p.support_description, " .
                           "p.support_contact, " .
                           "p.support_term, " .
                           "p.date_support_expires, " .
                           "p.date_support_starts, " .
                           "p.pricing_formula, " .
                           "p.pricing_factor, " .
                           "p.serial_number, " .
                           "p.asset_number, " .
                           "p.book_value, " .
                           "p.book_value_usdollar, " .
                           "p.book_value_date, " .
                           "p.best_case, " .
                           "p.likely_case, " .
                           "p.worst_case, " .
                           "p.date_closed, " .
                           "p.date_closed_timestamp, " .
                           "o.next_step, " .
                           "p.commit_stage, " .
                           "o.sales_stage, " .
                           "p.probability, " .
                           "o.lead_source, " .
                           "o.campaign_id, " .
                           "qo.opportunity_id, " .
                           "o.opportunity_type " .
                   "FROM products p  " .
                   "INNER JOIN quotes q  " .
                   "ON q.id = p.quote_id " .
                   "INNER JOIN quotes_opportunities qo " .
                   "ON qo.quote_id = q.id " .
                   "INNER JOIN opportunities o " .
                   "ON o.id = qo.opportunity_id";
            $results = $this->db->query($sql);
            $this->insertRows($results);
            $this->log('Done migrating 6.7 Products assigned to Quotes that have Opportunities.');
        }

        $this->log('Done migrating Products to Revenue Line Items.');
    }

    /**
     * Process all the results and insert them back into the db
     *
     * @param resource $results
     */
    protected function insertRows($results)
    {
        $insertSQL = 'INSERT INTO revenue_line_items VALUES';
        $productToRliMapping = array();

        /* @var $rli RevenueLineItem */
        $rli = BeanFactory::getBean('RevenueLineItems');

        while ($row = $this->db->fetchByAssoc($results)) {
            $productToRliMapping[$row['id']] = create_guid();
            $row['id'] = $productToRliMapping[$row['id']];
            foreach ($row as $key => $value) {
                $row[$key] = $this->db->massageValue($value, $rli->getFieldDefinition($key));
            }

            $this->db->query($insertSQL . ' (' . join(',', $row) . ');');
        }

        $this->relateProductToRevenueLineItem($productToRliMapping);
    }

    /**
     * Link the RLI to the Product that it was created from
     *
     * @param array $mapping
     */
    protected function relateProductToRevenueLineItem($mapping)
    {
        foreach ($mapping as $key => $value) {
            // set the link in the db
            $this->db->query(
                "UPDATE products SET revenuelineitem_id = " . $this->db->quoted($value) . " " .
                "WHERE id = " . $this->db->quoted($key)
            );
            // update the forecast worksheet record if one exists for it
            $this->db->query(
                "UPDATE forecast_worksheets SET parent_type = 'RevenueLineItems',
                 parent_id = " . $this->db->quoted($value) . " " .
                "WHERE parent_id = " . $this->db->quoted($key)
            );
        }
    }
}
