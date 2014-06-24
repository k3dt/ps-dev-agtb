<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

/**
 * FormBase.php
 *
 * @author Collin Lee
 *
 * This is an abstract class to provide common functionality across the form base code used in the application.
 *
 * @see LeadFormBase.php, ContactFormBase.php, MeetingFormBase, CallFormBase.php
 */

abstract class FormBase {


/**
 * isSaveFromDCMenu
 *
 * This is a function to help assist in determining if a save operation has been performed from the DCMenu (the shortcut bar
 * up top available for most themes).
 *
 * @return bool Boolean value indicating whether or not the save operation was triggered from DCMenu
 */
protected function isSaveFromDCMenu()
{
    return (isset($_POST['from_dcmenu']) && $_POST['from_dcmenu']);
}


/**
 * isEmptyReturnModuleAndAction
 *
 * This is a function to help assist in determining if a save operation has been performed without a return module and action specified.
 * This will likely be the case where we use AJAX to change the state of a record, but wish to keep the user remaining on the same view.
 * For example, this is true when closing Calls and Meetings from dashlets or from from subpanels.
 *
 * @return bool Boolean value indicating whether or not a return module and return action are specified in request
 */
protected function isEmptyReturnModuleAndAction()
{
    return empty($_POST['return_module']) && empty($_POST['return_action']);
}


}
 
