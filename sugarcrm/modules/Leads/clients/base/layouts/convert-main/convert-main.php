<?php
/**
 * Convert Lead Metadata Definition
 * This file defines which modules are included in the lead conversion process.
 * Within each module we define the following properties:
 *  * module (string): module name (plural)
 *  * required (boolean): is the user required to create or associate an existing record for this module before converting
 *  * duplicateCheck (boolean): should duplicate check be performed for this module?
 *  * contactRelateField (string): field on the contact that links to this module (if set, relationship will be created to contact)
 *  * dependentModules (array): array of module names that this module is dependent on
 *                              if set, this module will be disabled until dependent modules are completed
 *  * fieldMapping (array): how should lead fields be mapped to this module left side is the module and right side is the lead
 */
$viewdefs['Leads']['base']['layout']['convert-main'] = array(
    'modules' => array(
        array(
            'module' => 'Contacts',
            'required' => true,
            'duplicateCheckOnStart' => true,
            'fieldMapping' => array(
                //contact field => lead field
                'salutation' => 'salutation',
                'first_name' => 'first_name',
                'last_name' => 'last_name',
                'title' => 'title',
                'department' => 'department',
                'description' => 'description',
                'team_id' => 'team_id',
                'do_not_call' => 'do_not_call',
                'phone_home' => 'phone_home',
                'phone_mobile' => 'phone_mobile',
                'phone_work' => 'phone_work',
                'phone_fax' => 'phone_fax',
                'primary_address_street' => 'primary_address_street',
                'primary_address_city' => 'primary_address_city',
                'primary_address_state' => 'primary_address_state',
                'primary_address_postalcode' => 'primary_address_postalcode',
                'primary_address_country' => 'primary_address_country',
            ),
            'hiddenFields' => array (
                'account_name'
            )
        ),
        array(
            'module' => 'Accounts',
            'required' => true,
            'duplicateCheckOnStart' => true,
            'contactRelateField' => 'account_name',
            'fieldMapping' => array(
                //account field => lead field
                'name' => 'account_name',
                'team_id' => 'team_id',
                'billing_address_street' => 'primary_address_street',
                'billing_address_city' => 'primary_address_city',
                'billing_address_state' => 'primary_address_state',
                'billing_address_postalcode' => 'primary_address_postalcode',
                'billing_address_country' => 'primary_address_country',
                'shipping_address_street' => 'primary_address_street',
                'shipping_address_city' => 'primary_address_city',
                'shipping_address_state' => 'primary_address_state',
                'shipping_address_postalcode' => 'primary_address_postalcode',
                'shipping_address_country' => 'primary_address_country',
                'campaign_id' => 'campaign_id',
            )
        ),
        array(
            'module'                => 'Opportunities',
            'required'              => false,
            'duplicateCheckOnStart' => false,
            'fieldMapping'          => array(
                //opportunity field => lead field
                'name'        => 'opportunity_name',
                'phone_work'  => 'phone_office',
                'team_id'     => 'team_id',
                'campaign_id' => 'campaign_id',
                'lead_source' => 'lead_source',
            ),
            'dependentModules'      => array(
                'Accounts' => array(
                    'fieldMapping' => array(
                        'id' => 'account_id',
                    )
                )
            ),
            'hiddenFields'          => array(
                'account_name'
            )
        ),
    )
);
