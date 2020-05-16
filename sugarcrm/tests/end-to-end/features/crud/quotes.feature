# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud_modules_quotes @job3
Feature: Quotes module verification

  Background:
    Given I use default account
    Given I launch App

  @list-preview
  Scenario: Quotes > List View > Preview
    Given Quotes records exist:
      | *name   | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country | date_quote_expected_closed |
      | Quote_1 | City 1               | Street address here    | 220051                     | CA                    | USA                     | 2020-10-19T19:20:22+00:00  |
    Given Accounts records exist related via billing_accounts link:
      | name  |
      | Acc_1 |
    # Create a product bundle (aka. group)
    Given ProductBundles records exist related via product_bundles link to *Quote_1:
      | *name   |
      | Group_1 |

    # Add Quoted Line Items records to the product bundle (aka. group)
    Given Products records exist related via products link to *Group_1:
      | *name | discount_price | discount_amount | quantity |
      | QLI_1 | 100            | 2               | 2        |
      | QLI_2 | 200            | 2               | 3        |

    Given I open about view and login
    When I choose Quotes in modules menu
    Then I should see *Quote_1 in #QuotesList.ListView
    Then I verify fields for *Quote_1 in #QuotesList.ListView
      | fieldName | value   |
      | name      | Quote_1 |
    When I click on preview button on *Quote_1 in #QuotesList.ListView
    Then I should see #Quote_1Preview view
    Then I verify fields on #Quote_1Preview.PreviewView
      | fieldName                  | value               |
      | name                       | Quote_1             |
      | billing_address_street     | Street address here |
      | billing_address_postalcode | 220051              |
      | billing_address_state      | CA                  |
      | billing_address_country    | USA                 |
      | date_quote_expected_closed | 10/19/2020          |
      | deal_tot                   | $16.00              |
      | new_sub                    | $784.00             |
      | tax                        | $0.00               |
      | shipping                   | $0.00               |
      | total                      | $784.00             |


  @list-search
  Scenario: Quotes > List View > Filter > Search main input
    Given Quotes records exist:
      | *name   | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country | date_quote_expected_closed |
      | Quote_1 | City 1               | Street address here  1 | 120051                     | WA                    | USA                     | 2020-10-19T19:20:22+00:00  |
      | Quote_2 | City 2               | Street address here  2 | 220051                     | CA                    | USA                     | 2020-11-19T19:20:22+00:00  |
      | Quote_3 | City 3               | Street address here  3 | 320051                     | NC                    | USA                     | 2020-12-19T19:20:22+00:00  |
    Given I open about view and login
    When I choose Quotes in modules menu
    Then I should see #QuotesList.ListView view
    When I search for "Quote_2" in #QuotesList.FilterView view
    Then I should not see *Quote_1 in #QuotesList.ListView
    Then I should see *Quote_2 in #QuotesList.ListView
    Then I should not see *Quote_3 in #QuotesList.ListView
    Then I verify fields for *Quote_2 in #QuotesList.ListView
      | fieldName                  | value      |
      | name                       | Quote_2    |
      | date_quote_expected_closed | 11/19/2020 |


  @delete
  Scenario: Quotes > Record View > Delete > Cancel/Confirm
    Given Quotes records exist:
      | *name   | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country | date_quote_expected_closed |
      | Quote_2 | City 1               | Street address here    | 220051                     | WA                    | USA                     | 2017-10-19T19:20:22+00:00  |
    Given Accounts records exist related via billing_accounts link:
      | name  |
      | Acc_1 |
    Given I open about view and login
    When I choose Quotes in modules menu
    When I select *Quote_2 in #QuotesList.ListView
    Then I should see #Quote_2Record view
    When I open actions menu in #Quote_2Record
    * I choose Delete from actions menu in #Quote_2Record
    # Cancel record deletion
    When I Cancel confirmation alert
    Then I should see #Quote_2Record view
    Then I verify fields on #Quote_2Record.HeaderView
      | fieldName | value   |
      | name      | Quote_2 |
    When I open actions menu in #Quote_2Record
    * I choose Delete from actions menu in #Quote_2Record
    # Confirm record deletion
    When I Confirm confirmation alert
    Then I should see #QuotesList.ListView view
    Then I should not see *Quote_2 in #QuotesList.ListView


  @edit-cancel
  Scenario: Quotes > Record View > Edit > Cancel
    Given Quotes records exist:
      | *name   | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country | date_quote_expected_closed | quote_stage |
      | Quote_3 | City 1               | Street address here    | 220051                     | WA                    | USA                     | 2017-10-19T19:20:22+00:00  | Negotiation |
    Given Accounts records exist related via billing_accounts link:
      | name  |
      | Acc_1 |
    Given Accounts records exist:
      | name  |
      | Acc_2 |
    Given I open about view and login
    When I choose Quotes in modules menu
    When I select *Quote_3 in #QuotesList.ListView
    Then I should see #Quote_3Record view
    When I open actions menu in #Quote_3Record
    When I click Edit button on #Quote_3Record header
    Then I should see #Quote_3Record view
    When I provide input for #Quote_3Record.HeaderView view
      | name     |
      | Quote_34 |
    When I toggle Billing_and_Shipping panel on #Quote_3Record.RecordView view
    When I provide input for #Quote_3Record.RecordView view
      | quote_stage | date_quote_expected_closed | billing_account_name |
      | Delivered   | 12/12/2020                 | Acc_2                |
    When I Confirm confirmation alert
    When I click Cancel button on #Quote_3Record header
    Then I verify fields on #Quote_3Record.HeaderView
      | fieldName | value   |
      | name      | Quote_3 |
    Then I verify fields on #Quote_3Record.RecordView
      | fieldName                  | value       |
      | quote_stage                | Negotiation |
      | date_quote_expected_closed | 10/19/2017  |
      | billing_account_name       | Acc_1       |


  @edit-save
  Scenario: Quotes > Record View > Edit > Save
    Given Quotes records exist:
      | *name   | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country | date_quote_expected_closed | quote_stage |
      | Quote_3 | City 1               | Street address here    | 220051                     | WA                    | USA                     | 2017-10-19T19:20:22+00:00  | Negotiation |
    Given Accounts records exist related via billing_accounts link:
      | name  |
      | Acc_1 |
    Given Accounts records exist:
      | name  |
      | Acc_2 |
    Given I open about view and login
    When I choose Quotes in modules menu
    When I select *Quote_3 in #QuotesList.ListView
    Then I should see #Quote_3Record view
    When I open actions menu in #Quote_3Record
    When I click Edit button on #Quote_3Record header
    Then I should see #Quote_3Record view
    When I provide input for #Quote_3Record.HeaderView view
      | name     |
      | Quote_34 |
    When I toggle Billing_and_Shipping panel on #Quote_3Record.RecordView view
    When I provide input for #Quote_3Record.RecordView view
      | quote_stage | date_quote_expected_closed | billing_account_name |
      | Delivered   | 12/12/2020                 | Acc_2                |
    When I Confirm confirmation alert
    When I click Save button on #Quote_3Record header
    Then I verify fields on #Quote_3Record.HeaderView
      | fieldName | value    |
      | name      | Quote_34 |
    Then I verify fields on #Quote_3Record.RecordView
      | fieldName                  | value      |
      | quote_stage                | Delivered  |
      | date_quote_expected_closed | 12/12/2020 |
      | billing_account_name       | Acc_2      |


  @create_cancel_save
  Scenario: Quotes > Create > Cancel/Save
    Given Accounts records exist:
      | *name     | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | myAccount | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    Given I open about view and login
    When I choose Quotes in modules menu
    When I click Create button on #QuotesList header
    When I toggle Billing_and_Shipping panel on #QuotesRecord.RecordView view
    When I provide input for #QuotesRecord.HeaderView view
      | *        | name  |
      | RecordID | Alex2 |
    When I provide input for #QuotesRecord.RecordView view
      | *        | date_quote_expected_closed | billing_account_name |
      | RecordID | 12/12/2017                 | myAccount            |
    When I Confirm confirmation alert
    # Cancel quote record creation
    When I click Cancel button on #QuotesRecord header
    # TODO: Remove next line after sfa-5069 is fixed
    When I Confirm confirmation alert
    When I click Create button on #QuotesList header
    When I toggle Billing_and_Shipping panel on #QuotesRecord.RecordView view
    When I provide input for #QuotesRecord.HeaderView view
      | *        | name  |
      | RecordID | Alex2 |
    When I provide input for #QuotesRecord.RecordView view
      | *        | date_quote_expected_closed | billing_account_name |
      | RecordID | 12/12/2017                 | myAccount            |
    When I Confirm confirmation alert
    When I click Save button on #QuotesRecord header
    When I toggle Billing_and_Shipping panel on #RecordIDRecord.RecordView view
    Then I verify fields on #RecordIDRecord.RecordView
      | fieldName                  | value               |
      | date_quote_expected_closed | 12/12/2017          |
      | billing_account_name       | myAccount           |
      | billing_address_city       | City 1              |
      | billing_address_street     | Street address here |
      | billing_address_postalcode | 220051              |
      | billing_address_state      | WA                  |
      | billing_address_country    | USA                 |


  @copy
  Scenario: Quotes > Record View > Edit > Save
    Given Quotes records exist:
      | *name   | date_quote_expected_closed | quote_stage |
      | Quote_3 | 2018-10-19T19:20:22+00:00  | Negotiation |
    Given Accounts records exist related via billing_accounts link to *Quote_3:
      | name  | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Acc_1 | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    # Create a product bundle
    Given ProductBundles records exist related via product_bundles link to *Quote_3:
      | *name   |
      | Group_1 |
    # Add QLI
    Given Products records exist related via products link:
      | *name | discount_price | discount_amount | quantity |
      | QLI_1 | 100            | 2               | 2        |
      | QLI_2 | 200            | 2               | 3        |
    Given I open about view and login
    When I choose Quotes in modules menu
    When I select *Quote_3 in #QuotesList.ListView
    Then I should see #Quote_3Record view
    When I open actions menu in #Quote_3Record
    * I choose Copy from actions menu in #Quote_3Record
    When I provide input for #QuotesRecord.HeaderView view
      | *        | name      |
      | RecordID | Quote_3.1 |
    When I click Save button on #QuotesRecord header
    Then I verify fields on QLI total header on #Quote_3Record view
      | fieldName | value        |
      | deal_tot  | 2.00% $16.00 |
      | new_sub   | $784.00      |
      | tax       | $0.00        |
      | shipping  | $0.00        |
      | total     | $784.00      |


  @quote @add_new_QLI
  Scenario: Quotes > Record View > QLI Table > Add QLI > Cancel/Save
    Given Quotes records exist:
      | *name   | date_quote_expected_closed | quote_stage |
      | Quote_3 | 2018-10-19T19:20:22+00:00  | Negotiation |
    Given Accounts records exist related via billing_accounts link to *Quote_3:
      | name  | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Acc_1 | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    # Create a product bundle
    Given ProductBundles records exist related via product_bundles link to *Quote_3:
      | *name   |
      | Group_1 |
    # Add QLI
    Given Products records exist related via products link:
      | *name | discount_price | discount_amount | quantity |
      | QLI_1 | 100            | 2               | 2        |
      | QLI_2 | 200            | 2               | 3        |
    Given ProductTemplates records exist:
      | *name          | discount_price | cost_price | list_price | quantity | mft_part_num                 |
      | Kamalal Gadget | 922            | 922        | 922        | 1        | B.H. Edwards Inc 72868XYZ987 |
    Given I open about view and login
    When I choose Quotes in modules menu
    When I select *Quote_3 in #QuotesList.ListView
    Then I should see #Quote_3Record view
    # Add new Line Item
    When I choose createLineItem on QLI section on #Quote_3Record view
    When I provide input for #Quote_3Record.QliTable.QliRecord view
      | quantity | product_template_name | discount_price | discount_amount |
      | 2.00     | Some random name      | 100            | 2.00            |
    # Cancel and Verify
    When I click on cancel button on QLI #Quote_3Record.QliTable.QliRecord record
    Then I verify fields on QLI total header on #Quote_3Record view
      | fieldName | value        |
      | deal_tot  | 2.00% $16.00 |
      | new_sub   | $784.00      |
      | tax       | $0.00        |
      | shipping  | $0.00        |
      | total     | $784.00      |
    # Add New Line Item
    When I choose createLineItem on QLI section on #Quote_3Record view
    When I provide input for #Quote_3Record.QliTable.QliRecord view
      | *        | quantity | product_template_name | discount_price | discount_amount |
      | RecordID | 2.00     | Kamalal Gadget        | 100            | 2.00            |
    #Save and verify
    When I click on save button on QLI #Quote_3Record.QliTable.QliRecord record
    Then I verify fields on #RecordIDQLIRecord
      | fieldName      | value   |
      | discount_price | $100.00 |
      | total_amount   | $196.00 |
    Then I verify fields on QLI total header on #Quote_3Record view
      | fieldName | value        |
      | deal_tot  | 2.00% $20.00 |
      | new_sub   | $980.00      |
      | tax       | $0.00        |
      | shipping  | $0.00        |
      | total     | $980.00      |
    # Verify that created QLI is created assigned (SFA-4763)
    When I click product_template_name field on #RecordIDQLIRecord view
    Then I should see #ProductsRecord view
    When I click show more button on #ProductsRecord view
    Then I verify fields on #ProductsRecord.RecordView
      | fieldName          | value         |
      | assigned_user_name | Administrator |


  @quote @add_new_comment
  Scenario: Quotes > Record View > QLI Table > Add Comment > Cancel/Save
    Given Quotes records exist:
      | *name   | date_quote_expected_closed | quote_stage |
      | Quote_3 | 2018-10-19T19:20:22+00:00  | Negotiation |
    Given Accounts records exist related via billing_accounts link to *Quote_3:
      | name  | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Acc_1 | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    Given I open about view and login
    When I choose Quotes in modules menu
    When I select *Quote_3 in #QuotesList.ListView
    Then I should see #Quote_3Record view
    # Add comment and cancel
    When I choose createComment on QLI section on #Quote_3Record view
    When I provide input for #Quote_3Record.QliTable.CommentRecord view
      | description   |
      | Alex Nisevich |
    When I click on cancel button on Comment #Quote_3Record.QliTable.CommentRecord record
    # Add another comment and Save
    When I choose createComment on QLI section on #Quote_3Record view
    When I provide input for #Quote_3Record.QliTable.CommentRecord view
      | description     |
      | Ruslan Golovach |
    When I click on save button on Comment #Quote_3Record.QliTable.CommentRecord record


  @quote @add_new_group
  Scenario: Quotes > Record View > QLI Table > Add Group > Cancel/Save
    Given Quotes records exist:
      | *name   | date_quote_expected_closed | quote_stage |
      | Quote_3 | 2018-10-19T19:20:22+00:00  | Negotiation |
    Given Accounts records exist related via billing_accounts link to *Quote_3:
      | name  | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Acc_1 | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    Given I open about view and login
    When I choose Quotes in modules menu
    When I select *Quote_3 in #QuotesList.ListView
    Then I should see #Quote_3Record view
    # Add new group and cancel
    When I choose createGroup on QLI section on #Quote_3Record view
    When I provide input for #Quote_3Record.QliTable.GroupRecord view
      | name            |
      | Ruslan Golovach |
    When I click on cancel button on Group #Quote_3Record.QliTable.GroupRecord record
    When I Confirm confirmation alert
    # Add new group and Save
    When I choose createGroup on QLI section on #Quote_3Record view
    When I provide input for #Quote_3Record.QliTable.GroupRecord view
      | name      |
      | New Group |
    When I click on save button on Group #Quote_3Record.QliTable.GroupRecord record


  @quote @add_QLI_with_negative_quantity @SS-391 @SS-392 @AT-354
  Scenario: Quotes > Record View > QLI Table > Add QLI with negative quantity
    Given Quotes records exist:
      | *name   | date_quote_expected_closed | quote_stage |
      | Quote_3 | 2018-10-19T19:20:22+00:00  | Negotiation |
    Given Accounts records exist related via billing_accounts link to *Quote_3:
      | *name |
      | Acc_1 |
    # Generate Tax Rate
    Given TaxRates records exist:
      | *name | list_order | status | value |
      | Tax_1 | 4          | Active | 10.00 |

    Given I open about view and login
    When I choose Quotes in modules menu
    When I select *Quote_3 in #QuotesList.ListView
    Then I should see #Quote_3Record view

    # Add first line item with negative quantity and 10% discount
    When I choose createLineItem on QLI section on #Quote_3Record view
    When I provide input for #Quote_3Record.QliTable.QliRecord view
      | *     | quantity | product_template_name | discount_price | discount_amount | discount_select |
      | QLI_1 | -2.00    | QLI_111               | 100            | 10.00           | % Percent       |
    When I click on save button on QLI #Quote_3Record.QliTable.QliRecord record
    When I close alert

    Then I verify fields on #QLI_1QLIRecord
      | fieldName    | value    |
      | total_amount | $-180.00 |

    # Add second line item with negative quantity and $10.00 discount
    When I choose createLineItem on QLI section on #Quote_3Record view
    When I provide input for #Quote_3Record.QliTable.QliRecord view
      | *     | quantity | product_template_name | discount_price | discount_amount | discount_select |
      | QLI_2 | -2.00    | QLI_222               | 100            | 10.00           | $ US Dollar     |
    When I click on save button on QLI #Quote_3Record.QliTable.QliRecord record
    When I close alert

    Then I verify fields on #QLI_2QLIRecord
      | fieldName    | value    |
      | total_amount | $-190.00 |

    Then I verify fields on QLI total header on #Quote_3Record view
      | fieldName | value         |
      | deal_tot  | 7.50% $-30.00 |
      | new_sub   | $-370.00      |
      | tax       | $0.00         |
      | shipping  | $0.00         |
      | total     | $-370.00      |

    When I click Edit button on #Quote_3Record header
    When I toggle Quote_Settings panel on #Quote_3Record.RecordView view
    When I provide input for #Quote_3Record.RecordView view
      | taxrate_name |
      | Tax_1        |
    When I click Save button on #QuotesRecord header
    When I close alert

    Then I verify fields on QLI total header on #Quote_3Record view
      | fieldName | value         |
      | deal_tot  | 7.50% $-30.00 |
      | new_sub   | $-370.00      |
      | tax       | $-37.00       |
      | shipping  | $0.00         |
      | total     | $-407.00      |

