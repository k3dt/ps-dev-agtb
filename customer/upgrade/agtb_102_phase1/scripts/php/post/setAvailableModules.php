<?php
$modulesToShow = array(
    'Home',
    'Contacts',
    'gtb_positions',
    'gtb_matches',
    'gtb_contacts',
    'Calendar',
    'Reports',
    'Documents',
    'Emails',
    'Meetings',
    'Calls',
    'Tasks',
    'Notes',
    'Tags',
    'pmse_Project',
    'pmse_Emails_Templates',
    'pmse_Inbox',
    'pmse_Business_Rules',
);

$wirelessModulesToShow = array(
    'Contacts',
    'gtb_positions',
    'gtb_matches',
    'gtb_contacts',
    'Documents',
    'Emails',
    'Meetings',
    'Calls',
    'Tasks',
    'Notes',
    'Tags',
);

$hiddenSubpanels = array(
    'project',
    'bugs',
    'revenuelineitems',
    'contracts',
    'quotes',
    'Campaigns',
    'cases',
    'leads',
    'contacts',
    'opportunities',
    'Products',
    'CampaignLog',
    'Purchases',
    'DataPrivacy'
);

// Navigation Bar
require_once 'modules/MySettings/TabController.php';
$tabs = new TabController();
$tabs->set_system_tabs($modulesToShow);

// Hidden Subpanels
require_once('include/SubPanel/SubPanelDefinitions.php');
SubPanelDefinitions::set_hidden_subpanels($hiddenSubpanels);

// Mobile
$_REQUEST['enabled_modules'] = implode(',', $wirelessModulesToShow);
$controller = new AdministrationController();
$controller->action_updatewirelessenabledmodules();
unset($_REQUEST['enabled_modules']);
