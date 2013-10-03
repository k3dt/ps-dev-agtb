<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
$dictionary['RevenueLineItem'] = array(
    'table' => 'revenue_line_items',
    'audited' => true,
    //BEGIN SUGARCRM flav=ent ONLY
    'unified_search' => true,
    'full_text_search' => true,
    'unified_search_default_enabled' => true,
    //END SUGARCRM flav=ent ONLY
    'comment' => 'The user (not Admin)) view of a RevenueLineItem definition; an instance of a product used in the worksheets and opportunities',
    'fields' => array(
        'product_template_id' => array(
            'name' => 'product_template_id',
            'type' => 'id',
            'vname' => 'LBL_PRODUCT_TEMPLATE_ID',
            'required' => false,
            'reportable' => false,
            'comment' => 'Product (in Admin Products) from which this product is derived (in user Products)'
        ),
        'product_template_name' => array(
            'name' => 'product_template_name',
            'rname' => 'name',
            'id_name' => 'product_template_id',
            'vname' => 'LBL_PRODUCT',
            'join_name' => 'templates',
            'type' => 'relate',
            'link' => 'revenuelineitem_templates_link',
            'table' => 'product_templates',
            'isnull' => 'true',
            'module' => 'ProductTemplates',
            'dbType' => 'varchar',
            'len' => '255',
            'source' => 'non-db',
            'studio' => array('editview' => false, 'detailview' => false, 'quickcreate' => false),
            'auto_populate' => true,
            'populate_list' => array(
                'category_id' => 'category_id',
                'category_name' => 'category_name',
                'mft_part_num' => 'mft_part_num',
                'list_price' => 'list_price',
                'cost_price' => 'cost_price',
                'discount_price' => 'discount_price',
                'list_usdollar' => 'list_usdollar',
                'cost_usdollar' => 'cost_usdollar',
                'discount_usdollar' => 'discount_usdollar',
                'currency_id' => 'currency_id',
                'base_rate' => 'base_rate',
                'tax_class' => 'tax_class',
                'weight' => 'weight',
                'currency_id' => 'currency_id',
                'base_rate' => 'base_rate',
            ),
        ),
        'account_id' => array(
            'name' => 'account_id',
            'type' => 'id',
            'vname' => 'LBL_ACCOUNT_ID',
            'required' => false,
            'reportable' => false,
            'audited' => true,
            'comment' => 'Account this product is associated with',
            'formula' => 'related($opportunities, "account_id")',
            'enforced' => true,
            'calculated' => true,
        ),
        'total_amount' => array(
            'name' => 'total_amount',
            'formula' => 'subtract(multiply(ifElse(isNumeric($discount_price), $discount_price, 0), ifElse(isNumeric($quantity), $quantity, 1)), ifElse(isNumeric($discount_amount), $discount_amount, 0))',
            'calculated' => true,
            'enforced' => true,
            'vname' => 'LBL_CALCULATED_LINE_ITEM_AMOUNT',
            'reportable' => false,
            'type' => 'currency'
        ),
        'type_id' => array(
            'name' => 'type_id',
            'vname' => 'LBL_TYPE',
            'type' => 'id',
            'required' => false,
            'reportable' => false,
            'function' => array(
                'name' => 'getProductTypes',
                'returns' => 'html',
                'include' => 'modules/ProductTemplates/ProductTemplate.php'
            ),
            'comment' => 'Product type (ex: hardware, software)'
        ),
        'quote_id' => array(
            'name' => 'quote_id',
            'type' => 'id',
            'vname' => 'LBL_QUOTE_ID',
            'required' => false,
            'reportable' => false,
            'comment' => 'If product created via Quote, this is quote ID'
        ),
        'manufacturer_id' => array(
            'name' => 'manufacturer_id',
            'vname' => 'LBL_MANUFACTURER',
            'type' => 'id',
            'required' => false,
            'reportable' => false,
            'function' => array(
                'name' => 'getManufacturers',
                'returns' => 'html',
                'include' => 'modules/ProductTemplates/ProductTemplate.php'
            ),
            'comment' => 'Manufacturer of product'
        ),
        'manufacturer_name' =>
        array (
            'name' => 'manufacturer_name',
            'rname'=> 'name',
            'id_name'=> 'manufacturer_id',
            'type' => 'relate',
            'vname' =>'LBL_MANUFACTURER_NAME',
            'join_name' => 'manufacturers',
            'link' => 'manufacturer_link',
            'table' => 'manufacturers',
            'isnull' => 'true',
            'source'=>'non-db',
            'module' => 'Manufacturers',
            'dbType' => 'varchar',
            'len' => '255',
        ),
        'category_id' => array(
            'name' => 'category_id',
            'vname' => 'LBL_CATEGORY',
            'type' => 'id',
            'group' => 'category_name',
            'required' => false,
            'reportable' => true,
            'comment' => 'Product category'
        ),
        'category_name' => array(
            'name' => 'category_name',
            'rname' => 'name',
            'id_name' => 'category_id',
            'vname' => 'LBL_CATEGORY_NAME',
            'join_name' => 'categories',
            'type' => 'relate',
            'link' => 'revenuelineitem_categories_link',
            'table' => 'product_categories',
            'isnull' => 'true',
            'module' => 'ProductCategories',
            'dbType' => 'varchar',
            'len' => '255',
            'source' => 'non-db',
            'required' => false,
            'studio' => array('editview' => false, 'detailview' => false, 'quickcreate' => false),
        ),
        'name' => array(
            'name' => 'name',
            'vname' => 'LBL_NAME',
            'dbType' => 'varchar',
            'type' => 'name',
            'len' => '50',
            'unified_search' => true,
            'full_text_search' => array('boost' => 2),
            'comment' => 'Name of the product',
            'reportable' => true,
            'importable' => 'required',
            'required' => true,
            'audited' => true,
        ),
        'mft_part_num' => array(
            'name' => 'mft_part_num',
            'vname' => 'LBL_MFT_PART_NUM',
            'type' => 'varchar',
            'len' => '50',
            'comment' => 'Manufacturer part number'
        ),
        'vendor_part_num' => array(
            'name' => 'vendor_part_num',
            'vname' => 'LBL_VENDOR_PART_NUM',
            'type' => 'varchar',
            'len' => '50',
            'comment' => 'Vendor part number'
        ),
        'date_purchased' => array(
            'name' => 'date_purchased',
            'vname' => 'LBL_DATE_PURCHASED',
            'type' => 'date',
            'comment' => 'Date product purchased'
        ),
        'cost_price' => array(
            'name' => 'cost_price',
            'vname' => 'LBL_COST_PRICE',
            'type' => 'currency',
            'len' => '26,6',
            'audited' => true,
            'comment' => 'Product cost ("Cost" in Quote)'
        ),
        'discount_price' => array(
            'name' => 'discount_price',
            'vname' => 'LBL_DISCOUNT_PRICE',
            'type' => 'currency',
            'len' => '26,6',
            'audited' => true,
            'comment' => 'Discounted price ("Unit Price" in Quote)',
            'related_fields' => array(
                'currency_id',
                'base_rate'
            )
        ),
        'discount_amount' => array(
            'name' => 'discount_amount',
            'vname' => 'LBL_TOTAL_DISCOUNT_AMOUNT',
            'type' => 'currency',
            'len' => '26,6',
            'precision' => 6,
            'comment' => 'Discounted amount',
            'related_fields' => array(
                'currency_id',
                'base_rate'
            )
        ),
        'discount_rate_percent' => array(
            'name' => 'discount_rate_percent',
            'formula' => 'ifElse(isNumeric($discount_amount),ifElse(equal($discount_amount,0),0,multiply(divide($discount_amount,ifElse(equal(add($discount_amount,$total_amount), 0), $discount_amount, add($discount_amount,$total_amount))),100)),0)',
            'calculated' => true,
            'enforced' => true,
            'vname' => 'LBL_DISCOUNT_AS_PERCENT',
            'reportable' => false,
            'type' => 'decimal',
            'precision' => 2,
            'len' => '27,2'
        ),
        'discount_amount_usdollar' => array(
            'name' => 'discount_amount_usdollar',
            'vname' => 'LBL_DISCOUNT_RATE_USDOLLAR',
            'type' => 'decimal',
            'len' => '26,6',
            'studio' => array(
                'editview' => false,
                'mobile' => false,
            ),
            'readonly' => true,
            'is_base_currency' => true,
            'formula' => 'divide($discount_amount,$base_rate)',
            'calculated' => true,
            'enforced' => true,
        ),
        'discount_select' => array(
            'name' => 'discount_select',
            'vname' => 'LBL_SELECT_DISCOUNT',
            'type' => 'bool',
            'reportable' => false,
            'importable' => false,
        ),
        'deal_calc' => array(
            'name' => 'deal_calc',
            'vname' => 'LBL_DISCOUNT_TOTAL',
            'type' => 'currency',
            'len' => '26,6',
            'group' => 'deal_calc',
            'comment' => 'deal_calc',
            'customCode' => '{$fields.currency_symbol.value}{$fields.deal_calc.value}&nbsp;',
            'related_fields' => array(
                'currency_id',
                'base_rate'
            )
        ),
        'deal_calc_usdollar' => array(
            'name' => 'deal_calc_usdollar',
            'vname' => 'LBL_DISCOUNT_TOTAL_USDOLLAR',
            'type' => 'currency',
            'len' => '26,6',
            'group' => 'deal_calc',
            'comment' => 'deal_calc_usdollar',
            'studio' => array(
                'editview' => false,
                'mobile' => false,
            ),
            'readonly' => true,
            'is_base_currency' => true,
            'related_fields' => array(
                'currency_id',
                'base_rate'
            ),
            'formula' => 'divide($deal_calc,$base_rate)',
            'calculated' => true,
            'enforced' => true,
        ),
        'list_price' => array(
            'name' => 'list_price',
            'vname' => 'LBL_LIST_PRICE',
            'type' => 'currency',
            'len' => '26,6',
            'audited' => true,
            'comment' => 'List price of product ("List" in Quote)',
            'related_fields' => array(
                'currency_id',
                'base_rate'
            )
        ),
        'cost_usdollar' => array(
            'name' => 'cost_usdollar',
            'vname' => 'LBL_COST_USDOLLAR',
            'group' => 'cost_price',
            'type' => 'currency',
            'len' => '26,6',
            'comment' => 'Cost expressed in USD',
            'studio' => array(
                'editview' => false,
                'mobile' => false,
            ),
            'readonly' => true,
            'is_base_currency' => true,
            'related_fields' => array(
                'currency_id',
                'base_rate'
            ),
            'formula' => 'divide($cost_price,$base_rate)',
            'calculated' => true,
            'enforced' => true,
        ),
        'discount_usdollar' => array(
            'name' => 'discount_usdollar',
            'vname' => 'LBL_DISCOUNT_USDOLLAR',
            'group' => 'discount_price',
            'type' => 'currency',
            'len' => '26,6',
            'comment' => 'Discount price expressed in USD',
            'studio' => array(
                'editview' => false,
                'mobile' => false,
            ),
            'readonly' => true,
            'is_base_currency' => true,
            'related_fields' => array(
                'currency_id',
                'base_rate'
            ),
            'formula' => 'divide($discount_price,$base_rate)',
            'calculated' => true,
            'enforced' => true,
        ),
        'list_usdollar' => array(
            'name' => 'list_usdollar',
            'vname' => 'LBL_LIST_USDOLLAR',
            'type' => 'currency',
            'group' => 'list_price',
            'len' => '26,6',
            'comment' => 'List price expressed in USD',
            'studio' => array(
                'editview' => false,
                'mobile' => false,
            ),
            'readonly' => true,
            'is_base_currency' => true,
            'related_fields' => array(
                'currency_id',
                'base_rate'
            ),
            'formula' => 'divide($list_price,$base_rate)',
            'calculated' => true,
            'enforced' => true,
        ),
        'currency_id' => array(
            'name' => 'currency_id',
            'dbType' => 'id',
            'vname' => 'LBL_CURRENCY_ID',
            'type' => 'currency_id',
            'function' => array('name' => 'getCurrencyDropDown', 'returns' => 'html'),
            'required' => false,
            'reportable' => false,
            'default' => '-99',
            'comment' => 'Currency of the product'
        ),
        'base_rate' => array(
            'name' => 'base_rate',
            'vname' => 'LBL_CURRENCY_RATE',
            'type' => 'decimal',
            'len' => '26,6',
            'required' => true,
            'studio' => false
        ),
        'status' => array(
            'name' => 'status',
            'vname' => 'LBL_STATUS',
            'type' => 'enum',
            'options' => 'product_status_dom',
            'default' => '',
            'len' => 100,
            'audited' => true,
            'comment' => 'Product status (ex: Quoted, Ordered, Shipped)'
        ),
        'tax_class' => array(
            'name' => 'tax_class',
            'vname' => 'LBL_TAX_CLASS',
            'type' => 'enum',
            'options' => 'tax_class_dom',
            'len' => 100,
            'comment' => 'Tax classification (ex: Taxable, Non-taxable)'
        ),
        /*
          'tax_class' =>
          array (
            'name' => 'tax_class',
            'vname' => 'LBL_TAX_CLASS',
            'type' => 'varchar',
            'len' => 100,
            'comment' => 'Tax classification (ex: Taxable, Non-taxable)'
          ),
        */
        'website' => array(
            'name' => 'website',
            'vname' => 'LBL_URL',
            'type' => 'varchar',
            'len' => '255',
            'comment' => 'Product URL'
        ),
        'weight' =>  array(
            'name' => 'weight',
            'vname' => 'LBL_WEIGHT',
            'type' => 'decimal',
            'len' => '12,2',
            'precision' => 2,
            'comment' => 'Weight of the product'
        ),
        'quantity' => array(
            'name' => 'quantity',
            'vname' => 'LBL_QUANTITY',
            'type' => 'int',
            'len' => 5,
            'comment' => 'Quantity in use',
            'default' => 1
        ),
        'support_name' => array(
            'name' => 'support_name',
            'vname' => 'LBL_SUPPORT_NAME',
            'type' => 'varchar',
            'len' => '50',
            'comment' => 'Name of product for support purposes'
        ),
        'support_description' => array(
            'name' => 'support_description',
            'vname' => 'LBL_SUPPORT_DESCRIPTION',
            'type' => 'varchar',
            'len' => '255',
            'comment' => 'Description of product for support purposes'
        ),
        'support_contact' => array(
            'name' => 'support_contact',
            'vname' => 'LBL_SUPPORT_CONTACT',
            'type' => 'varchar',
            'len' => '50',
            'comment' => 'Contact for support purposes'
        ),
        'support_term' => array(
            'name' => 'support_term',
            'vname' => 'LBL_SUPPORT_TERM',
            'type' => 'varchar',
            'len' => 100,
            'function' => array(
                'name' => 'getSupportTerms',
                'returns' => 'html',
                'include' => 'modules/ProductTemplates/ProductTemplate.php'
            ),
            'comment' => 'Term (length) of support contract'
        ),
        'date_support_expires' => array(
            'name' => 'date_support_expires',
            'vname' => 'LBL_DATE_SUPPORT_EXPIRES',
            'type' => 'date',
            'comment' => 'Support expiration date'
        ),
        'date_support_starts' => array(
            'name' => 'date_support_starts',
            'vname' => 'LBL_DATE_SUPPORT_STARTS',
            'type' => 'date',
            'comment' => 'Support start date'
        ),
        'pricing_formula' => array(
            'name' => 'pricing_formula',
            'vname' => 'LBL_PRICING_FORMULA',
            'type' => 'varchar',
            'len' => 100,
            'comment' => 'Pricing formula (ex: Fixed, Markup over Cost)'
        ),
        'pricing_factor' => array(
            'name' => 'pricing_factor',
            'vname' => 'LBL_PRICING_FACTOR',
            'type' => 'int',
            'group' => 'pricing_formula',
            'len' => '4',
            'comment' => 'Variable pricing factor depending on pricing_formula'
        ),
        'serial_number' => array(
            'name' => 'serial_number',
            'vname' => 'LBL_SERIAL_NUMBER',
            'type' => 'varchar',
            'len' => '50',
            'comment' => 'Serial number of product in use'
        ),
        'asset_number' => array(
            'name' => 'asset_number',
            'vname' => 'LBL_ASSET_NUMBER',
            'type' => 'varchar',
            'len' => '50',
            'comment' => 'Asset tag number of product in use'
        ),
        'book_value' => array(
            'name' => 'book_value',
            'vname' => 'LBL_BOOK_VALUE',
            'type' => 'currency',
            'len' => '26,6',
            'comment' => 'Book value of product in use',
            'related_fields' => array(
                'currency_id',
                'base_rate'
            )
        ),
        'book_value_usdollar' => array(
            'name' => 'book_value_usdollar',
            'vname' => 'LBL_BOOK_VALUE_USDOLLAR',
            'group' => 'book_value',
            'type' => 'currency',
            'len' => '26,6',
            'comment' => 'Book value expressed in USD',
            'studio' => array(
                'editview' => false,
                'mobile' => false,
            ),
            'readonly' => true,
            'is_base_currency' => true,
            'related_fields' => array(
                'currency_id',
                'base_rate'
            ),
            'formula' => 'divide($book_value,$base_rate)',
            'calculated' => true,
            'enforced' => true,
        ),
        'book_value_date' => array(
            'name' => 'book_value_date',
            'vname' => 'LBL_BOOK_VALUE_DATE',
            'type' => 'date',
            'comment' => 'Date of book value for product in use'
        ),
        'quotes' => array(
            'name' => 'quotes',
            'type' => 'link',
            'relationship' => 'quote_revenuelineitems',
            'vname' => 'LBL_QUOTE',
            'source' => 'non-db',
        ),
//BEGIN SUGARCRM flav=pro ONLY
        'best_case' => array(
            'formula' => '$total_amount',
            'calculated' => true,
            'name' => 'best_case',
            'vname' => 'LBL_BEST',
            'type' => 'currency',
            'len' => '26,6',
            'audited' => true,
            'showTransactionalAmount' => true,
            'related_fields' => array(
                'currency_id',
                'base_rate'
            ),
            //END SUGARCRM flav=pro ONLY
            //BEGIN SUGARCRM flav=pro && flav!=ent ONLY
            'studio' => false,
            //END SUGARCRM flav=pro && flav!=ent ONLY
            //BEGIN SUGARCRM flav=pro ONLY
        ),
        'likely_case' => array(
            'formula' => '$total_amount',
            'calculated' => true,
            'name' => 'likely_case',
            'vname' => 'LBL_LIKELY',
            'required' => true,
            'type' => 'currency',
            'len' => '26,6',
            'audited' => true,
            'showTransactionalAmount' => true,
            'related_fields' => array(
                'currency_id',
                'base_rate'
            ),
            //END SUGARCRM flav=pro ONLY
            //BEGIN SUGARCRM flav=pro && flav!=ent ONLY
            'studio' => false,
            //END SUGARCRM flav=pro && flav!=ent ONLY
            //BEGIN SUGARCRM flav=pro ONLY
        ),
        'worst_case' => array(
            'formula' => '$total_amount',
            'calculated' => true,
            'name' => 'worst_case',
            'vname' => 'LBL_WORST',
            'type' => 'currency',
            'len' => '26,6',
            'audited' => true,
            'showTransactionalAmount' => true,
            'related_fields' => array(
                'currency_id',
                'base_rate'
            ),
            //END SUGARCRM flav=pro ONLY
            //BEGIN SUGARCRM flav=pro && flav!=ent ONLY
            'studio' => false,
            //END SUGARCRM flav=pro && flav!=ent ONLY
            //BEGIN SUGARCRM flav=pro ONLY
        ),
        'date_closed' => array(
            'name' => 'date_closed',
            'vname' => 'LBL_DATE_CLOSED',
            'required' => true,
            'type' => 'date',
            'audited' => true,
            'comment' => 'Expected or actual date the product (for opportunity) will close',
            'importable' => 'required',
            'enable_range_search' => true,
            'options' => 'date_range_search_dom',
            'related_fields' => array(
                'date_closed_timestamp'
            )
        ),
        'date_closed_timestamp' => array(
            'name' => 'date_closed_timestamp',
            'vname' => 'LBL_DATE_CLOSED_TIMESTAMP',
            'type' => 'int',
            'studio' => false,
            'reportable' => false,
            'audited' => true,
            'activity_enabled' => false,
            'formula' => 'timestamp($date_closed)',
            'calculated' => true,
            'enforced' => true
        ),
        'next_step' => array(
            'name' => 'next_step',
            'vname' => 'LBL_NEXT_STEP',
            'type' => 'varchar',
            'len' => '100',
            'comment' => 'The next step in the sales process',
            'merge_filter' => 'enabled',
        ),
        'commit_stage' => array(
            'name' => 'commit_stage',
            'vname' => 'LBL_COMMIT_STAGE_FORECAST',
            'type' => 'enum',
            'default' => 'exclude',
            'len' => '50',
            'comment' => 'Forecast commit category: Include, Likely, Omit etc.',
        ),
        'sales_stage' => array(
            'name' => 'sales_stage',
            'vname' => 'LBL_SALES_STAGE',
            'type' => 'enum',
            'options' => 'sales_stage_dom',
            'len' => '255',
            'audited' => true,
            'comment' => 'Indication of progression towards closure',
            'merge_filter' => 'enabled',
            'importable' => 'required',
            'required' => true,
        ),
        'probability' => array(
            'name' => 'probability',
            'vname' => 'LBL_PROBABILITY',
            'type' => 'int',
            'dbType' => 'double',
            'audited' => true,
            'comment' => 'The probability of closure',
            'validation' => array('type' => 'range', 'min' => 0, 'max' => 100),
            'merge_filter' => 'enabled',
            'formula' => 'getDropdownValue("sales_probability_dom",$sales_stage)',
            'calculated' => true,
        ),
        'lead_source' => array(
            'name' => 'lead_source',
            'vname' => 'LBL_LEAD_SOURCE',
            'type' => 'enum',
            'options' => 'lead_source_dom',
            'len' => '50',
            'comment' => 'Source of the product',
            'merge_filter' => 'enabled',
        ),
        'campaign_id' => array(
            'name' => 'campaign_id',
            'comment' => 'Campaign that generated lead',
            'vname' => 'LBL_CAMPAIGN_ID',
            'rname' => 'id',
            'type' => 'id',
            'dbType' => 'id',
            'table' => 'campaigns',
            'isnull' => 'true',
            'module' => 'Campaigns',
            'reportable' => false,
            'massupdate' => false,
            'duplicate_merge' => 'disabled',
        ),
        'campaign_name' => array(
            'name' => 'campaign_name',
            'rname' => 'name',
            'id_name' => 'campaign_id',
            'vname' => 'LBL_CAMPAIGN',
            'type' => 'relate',
            'link' => 'campaign_revenuelineitems',
            'isnull' => 'true',
            'table' => 'campaigns',
            'module' => 'Campaigns',
            'source' => 'non-db',
        ),
        'campaign_revenuelineitems' => array(
            'name' => 'campaign_revenuelineitems',
            'type' => 'link',
            'vname' => 'LBL_CAMPAIGN_PRODUCT',
            'relationship' => 'campaign_revenuelineitems',
            'source' => 'non-db',
        ),
//END SUGARCRM flav=pro ONLY
        'notes' => array(
            'name' => 'notes',
            'type' => 'link',
            'relationship' => 'revenuelineitem_notes',
            'source' => 'non-db',
            'vname' => 'LBL_NOTES',
        ),
        'documents' => array(
            'name' => 'documents',
            'type' => 'link',
            'relationship' => 'documents_revenuelineitems',
            'source' => 'non-db',
            'vname' => 'LBL_DOCUMENTS_SUBPANEL_TITLE',
        ),
        // Added for Meta-Data framework
        'currency_name' => array(
            'name' => 'currency_name',
            'rname' => 'name',
            'id_name' => 'currency_id',
            'vname' => 'LBL_CURRENCY_NAME',
            'type' => 'relate',
            'link' => 'currencies',
            'isnull' => true,
            'table' => 'currencies',
            'module' => 'Currencies',
            'source' => 'non-db',
            'function' => array('name' => 'getCurrencyNameDropDown', 'returns' => 'html'),
            'studio' => false,
            'duplicate_merge' => 'disabled',
            'massupdate' => false
        ),
        'currency_symbol' => array(
            'name' => 'currency_symbol',
            'rname' => 'symbol',
            'id_name' => 'currency_id',
            'vname' => 'LBL_CURRENCY_SYMBOL',
            'type' => 'relate',
            'link' => 'currencies',
            'isnull' => true,
            'table' => 'currencies',
            'module' => 'Currencies',
            'source' => 'non-db',
            'function' => array('name' => 'getCurrencySymbolDropDown', 'returns' => 'html'),
            'studio' => false,
            'duplicate_merge' => 'disabled',
            'massupdate' => false
        ),
        'quote_name' => array(
            'name' => 'quote_name',
            'rname' => 'name',
            'id_name' => 'quote_id',
            'join_name' => 'quotes',
            'type' => 'relate',
            'link' => 'quotes',
            'table' => 'quotes',
            'isnull' => 'true',
            'module' => 'Quotes',
            'dbType' => 'varchar',
            'len' => '255',
            'vname' => 'LBL_QUOTE_NAME',
            'source' => 'non-db',
            'comment' => 'Quote Name'
        ),
//BEGIN SUGARCRM flav=pro ONLY
        'assigned_user_id' => array(
            'name' => 'assigned_user_id',
            'vname' => 'LBL_ASSIGNED_USER_ID',
            'type' => 'id',
        ),
        'opportunity_id' => array(
            'name' => 'opportunity_id',
            'type' => 'id',
            'vname' => 'LBL_OPPORTUNITY_ID',
            'required' => true,
            'reportable' => false,
            'comment' => 'The opportunity id for the line item entry'
        ),
        'opportunity_name' => array(
            'name' => 'opportunity_name',
            'rname' => 'name',
            'id_name' => 'opportunity_id',
            'vname' => 'LBL_OPPORTUNITY_NAME',
            'required' => true,
            'join_name' => 'opportunities',
            'type' => 'relate',
            'link' => 'opportunities',
            'table' => 'opportunities',
            'isnull' => 'true',
            'module' => 'Opportunities',
            'source' => 'non-db',
            'comment' => 'The opportunity name associated with the opportunity_id',
            'auto_populate' => true,
            'populate_list' => array(
                'account_id' => 'account_id',
                'account_name' => 'account_name'
            ),
        ),
        'product_type' => array(
            'name' => 'product_type',
            'vname' => 'LBL_TYPE',
            'type' => 'enum',
            'options' => 'opportunity_type_dom',
            'len' => '255',
            'audited' => true,
            'comment' => 'Type of product ( from opportunities opportunity_type ex: Existing, New)',
            'merge_filter' => 'enabled',
        ),
        'opportunities' => array(
            'name' => 'opportunities',
            'type' => 'link',
            'relationship' => 'revenuelineitems_opportunities',
            'source' => 'non-db',
            'link_type' => 'one',
            'module' => 'Opportunities',
            'bean_name' => 'Opportunity',
            'vname' => 'LBL_OPPORTUNITIES',
        ),
        'assigned_user_link' => array(
            'name' => 'assigned_user_link',
            'type' => 'link',
            'relationship' => 'revenuelineitems_assigned_user',
            'vname' => 'LBL_USERS',
            'link_type' => 'one',
            'module' => 'Users',
            'bean_name' => 'User',
            'source' => 'non-db',
        ),
//END SUGARCRM flav=pro ONLY
        'type_name' => array(
            'name' => 'type_name',
            'rname' => 'name',
            'id_name' => 'type_id',
            'vname' => 'LBL_PRODUCT_TYPE',
            'join_name' => 'types',
            'type' => 'relate',
            'link' => 'revenuelineitem_types_link',
            'table' => 'product_types',
            'isnull' => 'true',
            'module' => 'ProductTypes',
            'importable' => 'false',
            'dbType' => 'varchar',
            'len' => '255',
            'source' => 'non-db',
        ),
        'account_link' => array(
            'name' => 'account_link',
            'type' => 'link',
            'relationship' => 'revenuelineitems_accounts',
            'vname' => 'LBL_ACCOUNT',
            'link_type' => 'one',
            'module' => 'Accounts',
            'bean_name' => 'Account',
            'source' => 'non-db',
        ),
        'revenuelineitem_categories_link' => array(
            'name' => 'revenuelineitem_categories_link',
            'type' => 'link',
            'relationship' => 'revenuelineitem_categories',
            'vname' => 'LBL_PRODUCT_CATEGORIES',
            'link_type' => 'one',
            'module' => 'ProductCategories',
            'bean_name' => 'ProductCategory',
            'source' => 'non-db',
        ),
        'revenuelineitem_templates_link' => array(
            'name' => 'revenuelineitem_templates_link',
            'type' => 'link',
            'relationship' => 'revenuelineitem_templates',
            'vname' => 'LBL_PRODUCT_TEMPLATES',
            'link_type' => 'one',
            'module' => 'ProductTemplates',
            'bean_name' => 'ProductTemplate',
            'source' => 'non-db',
        ),
        'revenuelineitem_types_link' => array(
            'name' => 'revenuelineitem_types_link',
            'type' => 'link',
            'relationship' => 'revenuelineitem_types',
            'vname' => 'LBL_PRODUCT_TYPES',
            'link_type' => 'one',
            'module' => 'ProductTypes',
            'bean_name' => 'ProductType',
            'source' => 'non-db',
        ),
        'products' => array(
            'name' => 'products',
            'type' => 'link',
            'relationship' => 'products_revenuelineitems',
            'vname' => 'LBL_PRODUCTS',
            'source' => 'non-db',
        ),
        'account_name' => array(
            'name' => 'account_name',
            'rname' => 'name',
            'id_name' => 'account_id',
            'vname' => 'LBL_ACCOUNT_NAME',
            'join_name' => 'accounts',
            'type' => 'relate',
            'link' => 'account_link',
            'table' => 'accounts',
            'module' => 'Accounts',
            'source' => 'non-db',
        ),
    ),
    'indices' => array(
        array('name' => 'idx_revenuelineitem', 'type' => 'index', 'fields' => array('name', 'deleted')),
        array(
            'name' => 'idx_user_dateclosed_timestamp',
            'type' => 'index',
            'fields' => array('id', 'assigned_user_id', 'date_closed_timestamp')
        )
    ),
    'relationships' => array(
        'revenuelineitem_notes' => array(
            'lhs_module' => 'RevenueLineItems',
            'lhs_table' => 'revenue_line_items',
            'lhs_key' => 'id',
            'rhs_module' => 'Notes',
            'rhs_table' => 'notes',
            'rhs_key' => 'parent_id',
            'relationship_type' => 'one-to-many',
            'relationship_role_column' => 'parent_type',
            'relationship_role_column_value' => 'RevenueLineItems'
        ),
        'revenuelineitems_opportunities' => array(
            'lhs_module' => 'Opportunities',
            'lhs_table' => 'opportunities',
            'lhs_key' => 'id',
            'rhs_module' => 'RevenueLineItems',
            'rhs_table' => 'revenue_line_items',
            'rhs_key' => 'opportunity_id',
            //BEGIN SUGARCRM flav=pro && flav != ent ONLY
            'relationship_type' => 'one-to-one',
            //END SUGARCRM  flav=pro && flav != ent ONLY
            //BEGIN SUGARCRM flav=ent ONLY
            'relationship_type' => 'one-to-many',
            //END SUGARCRM flav=ent ONLY
        ),
        'revenuelineitems_accounts' => array(
            'lhs_module' => 'Accounts',
            'lhs_table' => 'accounts',
            'lhs_key' => 'id',
            'rhs_module' => 'RevenueLineItems',
            'rhs_table' => 'revenue_line_items',
            'rhs_key' => 'account_id',
            'relationship_type' => 'one-to-many'
        ),
        'revenuelineitem_categories' => array(
            'lhs_module' => 'ProductCategories',
            'lhs_table' => 'product_categories',
            'lhs_key' => 'id',
            'rhs_module' => 'RevenueLineItems',
            'rhs_table' => 'revenue_line_items',
            'rhs_key' => 'category_id',
            'relationship_type' => 'one-to-many'
        ),
        'revenuelineitem_templates' => array(
            'lhs_module' => 'ProductTemplates',
            'lhs_table' => 'product_templates',
            'lhs_key' => 'id',
            'rhs_module' => 'RevenueLineItems',
            'rhs_table' => 'revenue_line_items',
            'rhs_key' => 'product_template_id',
            'relationship_type' => 'one-to-many'
        ),
        'revenuelineitem_types' => array(
            'lhs_module' => 'ProductTypes',
            'lhs_table' => 'product_types',
            'lhs_key' => 'id',
            'rhs_module' => 'RevenueLineItems',
            'rhs_table' => 'revenue_line_items',
            'rhs_key' => 'type_id',
            'relationship_type' => 'one-to-many'
        ),
        'revenuelineitems_modified_user' => array(
            'lhs_module' => 'Users',
            'lhs_table' => 'users',
            'lhs_key' => 'id',
            'rhs_module' => 'RevenueLineItems',
            'rhs_table' => 'revenue_line_items',
            'rhs_key' => 'modified_user_id',
            'relationship_type' => 'one-to-many'
        ),
    ),
    'duplicate_check' => array(
        'enabled' => true,
        'FilterDuplicateCheck' => array(
            'filter_template' => array(
                array(
                    '$and' => array(
                        array('opportunity_id' => array('$equals' => '$opportunity_id')),
                        array('name' => array('$starts' => '$name'))
                    )
                ),
            ),
            'ranking_fields' => array(
                array('in_field_name' => 'opportunity_id', 'dupe_field_name' => 'opportunity_id'),
                array('in_field_name' => 'name', 'dupe_field_name' => 'name'),
            )
        )
    ),

);

VardefManager::createVardef(
    'RevenueLineItems',
    'RevenueLineItem',
    array(
        'default',
        'assignable',
//BEGIN SUGARCRM flav=pro ONLY
        'team_security',
//END SUGARCRM flav=pro ONLY
    )
);
