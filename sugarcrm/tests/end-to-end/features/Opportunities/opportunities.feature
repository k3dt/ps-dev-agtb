# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@modules @Opportunity @job3 @ent-only
Feature: RLI module verification

  Background:
    Given I use default account
    Given I launch App

  Scenario: Opportunities >  Verify that RLIs with closed lost sales stage are not included in the Opportunity rollup total
    Given RevenueLineItems records exist:
      | *name | date_closed               | worst_case | likely_case | best_case | sales_stage | quantity |
      | RLI_1 | 2018-10-19T19:20:22+00:00 | 200        | 300         | 400       | Prospecting | 5        |
    Given Opportunities records exist related via opportunities link:
      | *name |
      | Opp_1 |
    Given I open about view and login
    When I choose RevenueLineItems in modules menu
    When I select *RLI_1 in #RevenueLineItemsList.ListView
    Then I should see #RLI_1Record view
    When I open actions menu in #RLI_1Record
    When I click Edit button on #RLI_1Record header
    When I click show more button on #RLI_1Record view
    When I provide input for #RLI_1Record.RecordView view
      | sales_stage |
      | Closed Lost |
    When I click Save button on #RLI_1Record header
    When I close alert
    # Verify that RLI's amount is not rolled into opportunity if RLI's sales stage is closed lost
    When I choose Opportunities in modules menu
    When I select *Opp_1 in #OpportunitiesList.ListView
    Then I should see #Opp_1Record view
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName  | value |
      | amount     | $0.00 |
      | best_case  | $0.00 |
      | worst_case | $0.00 |


  Scenario Outline: Opportunities > Verify that Status of the opportunity is changed to closed won/lost if all RLIs linked to the opportunity have sales stage "Close won/lost"
    Given RevenueLineItems records exist:
      | *name | date_closed               | worst_case | likely_case | best_case | sales_stage | quantity |
      | RLI_1 | 2018-10-19T19:20:22+00:00 | 200        | 300         | 400       | Prospecting | 5        |
    Given Opportunities records exist related via opportunities link:
      | *name |
      | Opp_1 |
    Given I open about view and login
    When I choose RevenueLineItems in modules menu
    When I select *RLI_1 in #RevenueLineItemsList.ListView
    Then I should see #RLI_1Record view
    When I open actions menu in #RLI_1Record
    When I click Edit button on #RLI_1Record header
    When I click show more button on #RLI_1Record view
    When I provide input for #RLI_1Record.RecordView view
      | sales_stage     |
      | <rliSalesStage> |
    When I click Save button on #RLI_1Record header
    When I close alert
      # Verify that Oportunity's status depends on sales stage of linked RLIs
    When I choose Opportunities in modules menu
    Then I should see *Opp_1 in #OpportunitiesList.ListView
    Then I verify fields for *Opp_1 in #OpportunitiesList.ListView
      | fieldName    | value       |
      | sales_status | <oppStatus> |
    Examples:
      | rliSalesStage | oppStatus   |
      | Closed Won    | Closed Won  |
      | Closed Lost   | Closed Lost |
      | Qualification | In Progress |


  Scenario Outline: Opportunities > Verify that changing account on opportunity should cascade down to all RLIs linked to this opportunity
    Given RevenueLineItems records exist:
      | *name | date_closed               | worst_case | likely_case | best_case | sales_stage | quantity |
      | RLI_1 | 2018-10-19T19:20:22+00:00 | 200        | 300         | 400       | Prospecting | 5        |
    Given Opportunities records exist related via opportunities link:
      | *name |
      | Opp_1 |
    Given Accounts records exist:
      | *name            |
      | <First_Account>  |
      | <Second_Account> |

    Given I open about view and login
    #Select account record for opportunity
    When I choose Opportunities in modules menu
    When I select *Opp_1 in #OpportunitiesList.ListView
    Then I should see #Opp_1Record view
    When I click Edit button on #Opp_1Record header
    When I provide input for #Opp_1Record.RecordView view
      | account_name    |
      | <First_Account> |
    When I click Save button on #Opp_1Record header
    When I close alert

   # Verify that RLI's account is updated
    When I choose RevenueLineItems in modules menu
    Then I should see *RLI_1 in #RevenueLineItemsList.ListView
    When I click on preview button on *RLI_1 in #RevenueLineItemsList.ListView
    Then I should see #RLI_1Preview view
    Then I verify fields on #RLI_1Preview.PreviewView
      | fieldName    | value           |
      | account_name | <First_Account> |

    #Select another account for opportunity
    When I choose Opportunities in modules menu
    When I select *Opp_1 in #OpportunitiesList.ListView
    Then I should see #Opp_1Record view
    When I click Edit button on #Opp_1Record header
    When I provide input for #Opp_1Record.RecordView view
      | account_name     |
      | <Second_Account> |
    When I click Save button on #Opp_1Record header
    When I close alert

   # Verify that RLI's account is updated
    When I choose RevenueLineItems in modules menu
    Then I should see *RLI_1 in #RevenueLineItemsList.ListView
    When I click on preview button on *RLI_1 in #RevenueLineItemsList.ListView
    Then I should see #RLI_1Preview view
    Then I verify fields on #RLI_1Preview.PreviewView
      | fieldName    | value            |
      | account_name | <Second_Account> |
    Examples:
      | First_Account | Second_Account |
      | Account_1     | #@##_acc_%^&   |


  Scenario Outline: Opportunities > Verify Opportunity cannot be deleted in the record view if sales stage of one or more RLIs is closed won
    Given RevenueLineItems records exist:
      | *name | date_closed               | worst_case | likely_case | best_case | sales_stage        | quantity |
      | RLI_1 | 2018-10-19T19:20:22+00:00 | 200        | 300         | 400       | <closedSalesStage> | 5        |
    Given Opportunities records exist related via opportunities link:
      | *name |
      | Opp_1 |
    Given Accounts records exist:
      | *name |
      | Acc_1 |

    Given I open about view and login
    # Select account record for opportunity
    When I choose Opportunities in modules menu
    When I select *Opp_1 in #OpportunitiesList.ListView
    Then I should see #Opp_1Record view
    When I click Edit button on #Opp_1Record header
    When I provide input for #Opp_1Record.RecordView view
      | account_name |
      | Acc_1        |
    When I click Save button on #Opp_1Record header
    When I close alert
    # Verify that Delete menu item is disabled
    When I open actions menu in #Opp_1Record and check:
      | menu_item | active |
      | Delete    | false  |
    # Change RLI sales stage to any but not Closed
    When I choose RevenueLineItems in modules menu
    Then I should see *RLI_1 in #RevenueLineItemsList.ListView
    When I select *RLI_1 in #RevenueLineItemsList.ListView
    Then I should see #RLI_1Record view
    When I click Edit button on #RLI_1Record header
    When I provide input for #RLI_1Record.RecordView view
      | sales_stage       |
      | <otherSalesStage> |
    When I click Save button on #RLI_1Record header
    When I close alert

    # Verify that now Delete menu item is active
    When I choose Opportunities in modules menu
    When I select *Opp_1 in #OpportunitiesList.ListView
    Then I should see #Opp_1Record view
    When I open actions menu in #Opp_1Record and check:
      | menu_item | active |
      | Delete    | true   |
    Examples:
      | closedSalesStage | otherSalesStage |
      | Closed Won       | Needs Analysis  |
      | Closed Lost      | Prospecting     |


  @opportunity_sales_stage @SS-291 @AT-343 @pr
  Scenario: Opportunities > Record view > Change Opp Sales Stage
    Given Accounts records exist:
      | *name     |
      | Account_1 |
    # Create opportunity records with linked RLIs
    And Opportunities records exist related via opportunities link to *Account_1:
      | *name | lead_source | opportunity_type  |
      | Opp_1 | Cold Call   | Existing Business |
    And RevenueLineItems records exist related via revenuelineitems link to *Opp_1:
      | *name | date_closed | likely_case | sales_stage   |
      | RLI_1 | now         | 1000        | Prospecting   |
      | RLI_2 | now         | 2000        | Qualification |
      | RLI_3 | now         | 1000        | Closed Won    |
      | RLI_4 | now         | 2000        | Closed Lost   |

    Given I open about view and login

    # Navigate to Opportunities module
    When I choose Opportunities in modules menu
    When I select *Opp_1 in #OpportunitiesList.ListView

    # Verify value of sales_stage field
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName    | value         |
      | sales_stage  | Qualification |
      | sales_status | In Progress   |

    # Edit sales_stage field of the opportunity
    When I click Edit button on #Opp_1Record header
    When I provide input for #Opp_1Record.RecordView view
      | sales_stage       |
      | Value Proposition |
    When I click Save button on #Opp_1Record header
    When I close alert

    # Verify value of sales_stage field
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName   | value             |
      | sales_stage | Value Proposition |

    # Verify value of sales_stage field in linked RLI records
    When I open the revenuelineitems subpanel on #Opp_1Record view
    Then I verify fields for *RLI_1 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value             |
      | sales_stage | Value Proposition |
    Then I verify fields for *RLI_2 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value             |
      | sales_stage | Value Proposition |
    Then I verify fields for *RLI_3 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value      |
      | sales_stage | Closed Won |
    Then I verify fields for *RLI_4 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value       |
      | sales_stage | Closed Lost |

    # Verify value of sales_stage field and sales_status
    When I choose Opportunities in modules menu
    When I click on preview button on *Opp_1 in #OpportunitiesList.ListView
    Then I verify fields on #Opp_1Preview.PreviewView
      | fieldName    | value             |
      | sales_stage  | Value Proposition |
      | sales_status | In Progress       |

    # Change 'sales_stage' field value in opportunity preview
    When I click on Edit button in #Opp_1Preview.PreviewHeaderView
    When I provide input for #Opp_1Preview.PreviewView view
      | sales_stage |
      | Closed Won  |
    When I click on Save button in #Opp_1Preview.PreviewHeaderView
    When I close alert

    # Navigate to Opportunities module
    When I choose Opportunities in modules menu
    When I select *Opp_1 in #OpportunitiesList.ListView

    # Verify value of sales_stage field
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName    | value      |
      | sales_stage  | Closed Won |
      | sales_status | Closed Won |

    # Verify value of sales_stage field in linked RLI records
    Then I verify fields for *RLI_1 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value      |
      | sales_stage | Closed Won |
    Then I verify fields for *RLI_2 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value      |
      | sales_stage | Closed Won |
    Then I verify fields for *RLI_3 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value      |
      | sales_stage | Closed Won |
    Then I verify fields for *RLI_4 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value       |
      | sales_stage | Closed Lost |

    # Add another RLI through subpanel
    When I create_new record from revenuelineitems subpanel on #Opp_1Record view
    When I provide input for #RevenueLineItemsDrawer.HeaderView view
      | *     | name  |
      | RLI_5 | RLI_5 |
    When I provide input for #RevenueLineItemsDrawer.RecordView view
      | *     | date_closed | likely_case | sales_stage        |
      | RLI_5 | 12/12/2020  | 3000        | Negotiation/Review |
    When I click Save button on #RevenueLineItemsDrawer header
    When I close alert

    # Verify value of sales_stage field
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName    | value              |
      | sales_stage  | Negotiation/Review |
      | sales_status | In Progress        |


  @opportunity_expected_close_date @SS-313 @AT-356 @pr
  Scenario: Opportunities > Record view > Ignore closed RLIs when calculate opportunity Expected Close Date
    Given Accounts records exist:
      | *name     |
      | Account_1 |
    # Create opportunity records with linked RLIs
    And Opportunities records exist related via opportunities link to *Account_1:
      | *name | lead_source | opportunity_type  |
      | Opp_1 | Cold Call   | Existing Business |
    And RevenueLineItems records exist related via revenuelineitems link to *Opp_1:
      | *name | date_closed               | likely_case | sales_stage         |
      | RLI_1 | 2020-04-18T19:20:22+00:00 | 1000        | Prospecting         |
      | RLI_2 | 2020-04-17T19:20:22+00:00 | 2000        | Qualification       |
      | RLI_3 | 2020-04-16T19:20:22+00:00 | 3000        | Perception Analysis |
      | RLI_4 | 2020-04-15T19:20:22+00:00 | 4000        | Closed Won          |
      | RLI_5 | 2020-04-19T19:20:22+00:00 | 5000        | Closed Lost         |

    Given I open about view and login

    # Navigate to Opportunities module
    When I choose Opportunities in modules menu
    When I select *Opp_1 in #OpportunitiesList.ListView

    # Verify value of date_closed field
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName   | value      |
      | date_closed | 04/18/2020 |

    # Verify value of date_close field in linked RLI records
    When I open the revenuelineitems subpanel on #Opp_1Record view
    Then I verify fields for *RLI_1 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value      |
      | date_closed | 04/18/2020 |
    Then I verify fields for *RLI_2 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value      |
      | date_closed | 04/17/2020 |
    Then I verify fields for *RLI_3 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value      |
      | date_closed | 04/16/2020 |
    Then I verify fields for *RLI_4 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value      |
      | date_closed | 04/15/2020 |
    Then I verify fields for *RLI_5 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value      |
      | date_closed | 04/19/2020 |

    # Edit sales_stage field of the opportunity
    When I click Edit button on #Opp_1Record header
    When I provide input for #Opp_1Record.RecordView view
      | sales_stage |
      | Closed Won  |
    When I click Save button on #Opp_1Record header
    When I close alert

    # Verify value of date_closed field
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName   | value      |
      | date_closed | 04/18/2020 |

    # Edit RLI record in the Revenue Line Items subpanel of opportunity record view
    When I click on Edit button for *RLI_1 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
    When I set values for *RLI_1 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value          |
      | sales_stage | Needs Analysis |
      | date_closed | 04/01/2020     |
    When I click on Save button for *RLI_1 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
    When I close alert

    # Verify value of date_closed field
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName   | value      |
      | date_closed | 04/01/2020 |

    # Edit RLI record in the Revenue Line Items subpanel of opportunity record view
    When I click on Edit button for *RLI_2 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
    When I set values for *RLI_2 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value       |
      | sales_stage | Prospecting |
      | date_closed | 04/02/2020  |
    When I click on Save button for *RLI_2 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
    When I close alert

    # Verify value of date_closed field
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName   | value      |
      | date_closed | 04/02/2020 |

    # Edit RLI record in the Revenue Line Items subpanel of opportunity record view
    When I click on Edit button for *RLI_3 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
    When I set values for *RLI_3 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value       |
      | sales_stage | Prospecting |
      | date_closed | 03/31/2020  |
    When I click on Save button for *RLI_3 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
    When I close alert

    # Verify value of date_closed field
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName   | value      |
      | date_closed | 04/02/2020 |

    # Edit RLI record in the Revenue Line Items subpanel of opportunity record view
    When I click on Edit button for *RLI_4 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
    When I set values for *RLI_4 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value       |
      | sales_stage | Prospecting |
      | date_closed | 04/03/2020  |
    When I click on Save button for *RLI_4 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
    When I close alert

    # Verify value of date_closed field
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName   | value      |
      | date_closed | 04/03/2020 |

      # Edit sales_stage field of the opportunity
    When I click Edit button on #Opp_1Record header
    When I provide input for #Opp_1Record.RecordView view
      | sales_stage |
      | Closed Lost |
    When I click Save button on #Opp_1Record header
    When I close alert

    # Verify value of date_closed field
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName   | value      |
      | date_closed | 04/19/2020 |


  @opportunity_expected_close_date @SS-302 @SS-323 @AT-362
  Scenario Outline: Opportunities > Record view > Editing Expected Close Date for all open RLIs in an Opportunity at once
    Given Accounts records exist:
      | *name     |
      | Account_1 |
    # Create opportunity records with linked RLIs
    And Opportunities records exist related via opportunities link to *Account_1:
      | *name | lead_source | opportunity_type  |
      | Opp_1 | Cold Call   | Existing Business |
    And RevenueLineItems records exist related via revenuelineitems link to *Opp_1:
      | *name | date_closed               | likely_case | sales_stage         |
      | RLI_1 | 2020-04-18T19:20:22+00:00 | 1000        | Prospecting         |
      | RLI_2 | 2020-04-17T19:20:22+00:00 | 2000        | Qualification       |
      | RLI_3 | 2020-04-16T19:20:22+00:00 | 3000        | Perception Analysis |
      | RLI_4 | 2020-04-15T19:20:22+00:00 | 4000        | Closed Won          |
      | RLI_5 | 2020-04-19T19:20:22+00:00 | 5000        | Closed Lost         |

    Given I open about view and login

    # Navigate to Opportunities module
    When I choose Opportunities in modules menu
    When I select *Opp_1 in #OpportunitiesList.ListView

    # Verify value of date_closed field
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName   | value      |
      | date_closed | 04/18/2020 |

    # Edit sales_stage field of the opportunity
    When I click Edit button on #Opp_1Record header
    When I provide input for #Opp_1Record.RecordView view
      | date_closed     |
      | <December_2020> |
    When I click Save button on #Opp_1Record header
    When I close alert

    # Verify value of date_closed field
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName   | value           |
      | date_closed | <December_2020> |

    # Verify value of date_close field in linked RLI records
    When I open the revenuelineitems subpanel on #Opp_1Record view
    Then I verify fields for *RLI_1 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value           |
      | date_closed | <December_2020> |
    Then I verify fields for *RLI_2 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value           |
      | date_closed | <December_2020> |
    Then I verify fields for *RLI_3 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value           |
      | date_closed | <December_2020> |
    Then I verify fields for *RLI_4 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value      |
      | date_closed | 04/15/2020 |
    Then I verify fields for *RLI_5 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value      |
      | date_closed | 04/19/2020 |

    When I choose Opportunities in modules menu
    When I click on preview button on *Opp_1 in #OpportunitiesList.ListView
    When I click on Edit button in #Opp_1Preview.PreviewHeaderView
    When I provide input for #Opp_1Preview.PreviewView view
      | date_closed    |
      | <January_2021> |
    When I click on Save button in #Opp_1Preview.PreviewHeaderView
    When I close alert

    When I select *Opp_1 in #OpportunitiesList.ListView
    # Verify value of date_close field in linked RLI records
    When I open the revenuelineitems subpanel on #Opp_1Record view
    Then I verify fields for *RLI_1 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value          |
      | date_closed | <January_2021> |
    Then I verify fields for *RLI_2 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value          |
      | date_closed | <January_2021> |
    Then I verify fields for *RLI_3 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value          |
      | date_closed | <January_2021> |
    Then I verify fields for *RLI_4 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value      |
      | date_closed | 04/15/2020 |
    Then I verify fields for *RLI_5 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value      |
      | date_closed | 04/19/2020 |

    Examples:
      | December_2020 | January_2021 |
      | 12/12/2020    | 01/01/2021   |


  @opportunity_service_duration @SS-728 @pr
  Scenario: Opportunities > Record view > Editing Duration for all open service RLIs in an Opportunity level
    # Create 2 product records: one with locked duration and another with unlocked duration
    Given ProductTemplates records exist:
      | *name     | discount_price | list_price | cost_price | service | service_duration_value | service_duration_unit | lock_duration |
      | Product_1 | 1000           | 2000       | 500        | true    | 18                     | month                 | true          |
      | Product_2 | 1001           | 2001       | 501        | true    | 18                     | month                 | false         |
    Given Accounts records exist:
      | *name     |
      | Account_1 |
    # Create opportunity records with linked RLIs
    And Opportunities records exist related via opportunities link to *Account_1:
      | *name | lead_source | opportunity_type  |
      | Opp_1 | Cold Call   | Existing Business |
    And RevenueLineItems records exist related via revenuelineitems link to *Opp_1:
      | *name | date_closed               | likely_case | sales_stage         |
      | RLI_1 | 2020-04-18T19:20:22+00:00 | 1000        | Prospecting         |
      | RLI_2 | 2020-04-17T19:20:22+00:00 | 2000        | Qualification       |
      | RLI_3 | 2020-04-16T19:20:22+00:00 | 3000        | Perception Analysis |
      | RLI_4 | 2020-04-15T19:20:22+00:00 | 4000        | Value Proposition   |

    Given I open about view and login

    # Navigate to Opportunities module
    When I choose Opportunities in modules menu
    When I select *Opp_1 in #OpportunitiesList.ListView

    # Verify value of service_duration field is empty
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName              | value |
      | service_duration_value |       |
      | service_duration_unit  |       |

    # Change one of the RLIs to be 90 days service
    When I update RevenueLineItems *RLI_2 with the following values:
      | service | service_duration_value | service_duration_unit |
      | true    | 90                     | Day(s)                |

    # Verify value of service-duration field is updated properly
    Then Opportunities *Opp_1 should have the following values:
      | fieldName              | value  |
      | service_duration_value | 90     |
      | service_duration_unit  | Day(s) |

    # Change one of the RLIs to be 3 Months service
    When I update RevenueLineItems *RLI_3 with the following values:
      | service | service_duration_value | service_duration_unit |
      | true    | 3                      | Month(s)              |

    # Verify value of service duration field is updated properly
    Then Opportunities *Opp_1 should have the following values:
      | fieldName              | value    |
      | service_duration_value | 3        |
      | service_duration_unit  | Month(s) |

    # Mark one of the RLIs as Closed Won
    When I open the revenuelineitems subpanel on #Opp_1Record view
    When I click on Edit button for *RLI_3 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
    When I set values for *RLI_3 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value      |
      | sales_stage | Closed Won |
    When I click on Save button for *RLI_3 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
    When I close alert

    # Verify value of service_duration field is updated properly
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName              | value  |
      | service_duration_value | 90     |
      | service_duration_unit  | Day(s) |

    # Edit service duration from opportunity record view level
    When I click Edit button on #Opp_1Record header
    When I provide input for #Opp_1Record.RecordView view
      | service_duration | service_duration_value | service_duration_unit |
      | true             | 2                      | Year(s)               |
    When I click Save button on #Opp_1Record header
    When I close alert

    # Verify service duration is updated in Opportunity record view
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName              | value   |
      | service_duration_value | 2       |
      | service_duration_unit  | Year(s) |

    # Verify value of service duration field is updated properly for open service-type RLIs
    Then I verify fields for *RLI_2 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName              | value   |
      | service_duration_value | 2       |
      | service_duration_unit  | Year(s) |

    # Verify that service duration is NOT updated for closed service -type RLIs
    Then I verify fields for *RLI_3 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName              | value    |
      | service_duration_value | 3        |
      | service_duration_unit  | Month(s) |

    # Change one of the RLIs to use product with locked duration
    When I update RevenueLineItems *RLI_1 with the following values:
      | sales_stage | product_template_name |
      | Prospecting | Product_1             |

    # Change one of the RLIs to use product with Not locked duration
    When I update RevenueLineItems *RLI_4 with the following values:
      | sales_stage | product_template_name |
      | Prospecting | Product_2             |


    # Edit service duration from opportunity preview level
    When I choose Opportunities in modules menu
    When I click on preview button on *Opp_1 in #OpportunitiesList.ListView
    When I click on Edit button in #Opp_1Preview.PreviewHeaderView
    When I provide input for #Opp_1Preview.PreviewView view
      | service_duration |service_duration_value |
      | true             |5                      |
    When I click on Save button in #Opp_1Preview.PreviewHeaderView
    When I close alert

    When I select *Opp_1 in #OpportunitiesList.ListView
    # Verify value of service duration field is updated properly for open RLI
    Then I verify fields for *RLI_1 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName              | value    |
      | service_duration_value | 18       |
      | service_duration_unit  | Month(s) |

    # Verify value of service duration field is updated properly for open RLI
    Then I verify fields for *RLI_2 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName              | value   |
      | service_duration_value | 5       |
      | service_duration_unit  | Year(s) |

    # Verify that service duration is NOT updated for closed RLIs
    Then I verify fields for *RLI_3 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName              | value    |
      | service_duration_value | 3        |
      | service_duration_unit  | Month(s) |

    # Verify value of service duration field is updated properly for open RLI
    Then I verify fields for *RLI_4 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName              | value   |
      | service_duration_value | 5       |
      | service_duration_unit  | Year(s) |

    # Perform Mass Update to change sales stage of all open service RLIs to closed lost
    When I perform mass update of RevenueLineItems [*RLI_1, *RLI_2, *RLI_4] with the following values:
      | fieldName   | value       |
      | sales_stage | Closed Lost |

    # Verify service duration is updated in Opportunity record view
    When I choose Opportunities in modules menu
    When I select *Opp_1 in #OpportunitiesList.ListView
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName              | value    |
      | service_duration_value | 3        |
      | service_duration_unit  | Month(s) |

    # Change sales stage to closed won RLI3 to be closed lost
    When I click on Edit button for *RLI_3 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
    When I set values for *RLI_3 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value       |
      | sales_stage | Closed Lost |
    When I click on Save button for *RLI_3 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
    When I close alert

    # Verify that service_duration is updated properly
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName              | value   |
      | service_duration_value | 5       |
      | service_duration_unit  | Year(s) |
