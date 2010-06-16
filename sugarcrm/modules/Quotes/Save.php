<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: Save.php 53545 2010-01-08 08:41:51Z rob $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/formbase.php');
require_once('modules/Quotes/config.php');
require_once('include/SugarFields/SugarFieldHandler.php');

$focus = new Quote();
$focus = populateFromPost('', $focus);
//we have to commit the teams here in order to obtain the team_set_id for use with products and product bundles.
if(empty($focus->teams)){
	$focus->load_relationship('teams');
}
$focus->teams->save();
if(!$focus->ACLAccess('Save')){
	ACLController::displayNoAccess(true);
	sugar_cleanup(true);
}
if (!empty($_POST['assigned_user_id']) && ($focus->assigned_user_id != $_POST['assigned_user_id']) && ($_POST['assigned_user_id'] != $current_user->id)) {
	$check_notify = TRUE;
}
else {
	$check_notify = FALSE;
}

$focus->tax = 0;
$focus->total = 0;
$focus->subtotal = 0;
$focus->deal_tot = 0;
$focus->new_sub = 0;
$focus->shipping = 0;
$focus->tax_usdollar = 0;
$focus->total_usdollar = 0;
$focus->subtotal_usdollar = 0;
$focus->shipping_usdollar = 0;

if(empty($_REQUEST['calc_grand_total'])){
	$focus->calc_grand_total = 0;
}else{
	$focus->calc_grand_total = 1;
}
if(empty($_REQUEST['show_line_nums'])){
	$focus->show_line_nums = 0;
}else{
	$focus->show_line_nums = 1;
}

if(empty($focus->id)) {
	//bug 14323, add this to create products firsts, and create the quotes at last, so the workflow can manipulate the products.
    $focus->id = create_guid();
    $focus->new_with_id = true;
}


	global $beanFiles;
	require_once($beanFiles['Product']);
	$GLOBALS['log']->debug("Saving associated products");

	$i = 0;

	if(isset($_POST['product_count'])){
		$product_count = $_POST['product_count'];
	}
	//totals keys is a list of tables for the products bundles
	if(isset($_REQUEST['total'])){
	$total_keys = array_keys($_REQUEST['total']);
	}else{
		$total_keys = array();
	}
	$product_bundels = array();

	for($k = 0; $k < sizeof($total_keys); $k++){
		$pb = new ProductBundle();

		if(substr_count($total_keys[$k], 'group_') == 0){
				$pb->id = $total_keys[$k];
		}
		
		//BEGIN SUGARCRM flav=pro ONLY
		$pb->team_id = $focus->team_id;
        $pb->team_set_id = $focus->team_set_id;
        //END SUGARCRM flav=pro ONLY
        
		$pb->tax = unformat_number($_REQUEST['tax'][$total_keys[$k]]);
		$pb->shipping = unformat_number($_REQUEST['shipping'][$total_keys[$k]]);
		$pb->subtotal = unformat_number($_REQUEST['subtotal'][$total_keys[$k]]);
		$pb->deal_tot = unformat_number($_REQUEST['deal_tot'][$total_keys[$k]]);
		$pb->new_sub = unformat_number($_REQUEST['new_sub'][$total_keys[$k]]);
		$pb->total = unformat_number($_REQUEST['total'][$total_keys[$k]]);
		$pb->currency_id = $focus->currency_id;
		$pb->bundle_stage = $_REQUEST['bundle_stage'][$total_keys[$k]];
		$pb->name = $_REQUEST['bundle_name'][$total_keys[$k]];

		if(key_exists($pb->bundle_stage, $in_total_group_stages)){
			$focus->tax += $pb->tax;
			$focus->shipping += $pb->shipping;
			$focus->subtotal += $pb->subtotal;
			$focus->deal_tot += $pb->deal_tot;
			$focus->new_sub += $pb->new_sub;
			$focus->total += $pb->total;
		}
		$product_bundels[$total_keys[$k]] = $pb->save();
		if(substr_count($total_keys[$k], 'group_') > 0){
			$pb->set_productbundle_quote_relationship($focus->id, $pb->id, $k);
		}

		//clear the old relationships out
		$pb->clear_productbundle_product_relationship($product_bundels[$total_keys[$k]]);
		$pb->clear_product_bundle_note_relationship($product_bundels[$total_keys[$k]]);
	}

	$pb = new ProductBundle();
	$deletedGroups = array();
	if(isset($_POST['delete_table'])){
	foreach($_POST['delete_table'] as $todelete){
		if($todelete != 1){
			$pb->mark_deleted($todelete);
			$deletedGroups[$todelete] = $todelete;
		}
	}
	}
	//Fix bug 25509
	$focus->process_save_dates = true;

	$pb = new ProductBundle();
	for($i = 0; $i < $product_count; $i++) {

		if((isset($_POST['delete'][$i]) && $_POST['delete'][$i] != '1')){
			$product = new Product();
			$GLOBALS['log']->debug("deleting product id ".$_POST['delete'][$i]);
			$product->mark_deleted($_POST['delete'][$i]);
		// delete a comment row
		} else if (isset($_POST['comment_delete'][$i]) && $_POST['comment_delete'][$i] != '1') {
			$product_bundle_note = new ProductBundleNote();
			$GLOBALS['log']->debug("Deleting Product Bundle Note Id: ".$_POST['comment_delete'][$i]);
			$product_bundle_note->mark_deleted($_POST['comment_delete'][$i]);
		}
		// insert/update a product into products table
		else if (!empty($_POST['product_name'][$i]) && !empty($_POST['parent_group'][$i])) {
		$product = new Product();
		$the_product_template_id = '-1';
		if (!empty($_POST['product_id'][$i])) {
			$product->retrieve($_POST['product_id'][$i]);
			$the_product_template_id = $product->product_template_id;
		}
		$GLOBALS['log']->debug("product id is $product->id");
		$GLOBALS['log']->debug("product template id is $product->product_template_id");

		foreach($product->column_fields as $field)
		{
			if ($field == 'name') $j = 'product_name';
			elseif ($field == 'description') $j = 'product_description';
			else $j = $field;
			if(isset($_POST[$j][$i]))
			{
				$value = $_POST[$j][$i];
                if ( isset($product->field_defs[$field]['type']) ) {
                    $sugarField = SugarFieldHandler::getSugarField($product->field_defs[$field]['type']);
                    $sugarField->save($product,array($field=>$value),$field,$product->field_defs[$field]);
                } else {
                    $product->$field = $value;
                }
			}
		}
		if (!empty($_POST['product_template_id'][$i]) && $_POST['product_template_id'][$i] != $the_product_template_id ) {
			require_once($beanFiles['ProductTemplate']);
			$template = new ProductTemplate();
			$template->retrieve($_POST['product_template_id'][$i]);
			foreach($product->template_fields as $temp_field)
			{
				if(isset($template->$temp_field))
				{
					$GLOBALS['log']->debug("$temp_field is ".$template->$temp_field);
					$product->$temp_field = $template->$temp_field;

				}
			}
		}
		$product->currency_id = $focus->currency_id;
		
		//BEGIN SUGARCRM flav=pro ONLY
		$product->team_id = $focus->team_id;
		$product->team_set_id = $focus->team_set_id;
		//END SUGARCRM flav=pro ONLY
		
		$product->quote_id=$focus->id;
        $product->account_id=$focus->billing_account_id;  //<--------------
        $product->contact_id=$focus->billing_contact_id;
		$product->status=$focus->quote_type;
		if ($focus->quote_stage == 'Closed Accepted') $product->status='Orders';
    		$product->save();
    		$pb->set_productbundle_product_relationship($product->id,$_POST['parent_group_position'][$i], $product_bundels[$_REQUEST['parent_group'][$i]] );
		}

		// insert comment row
		else if (!empty($_POST['comment_index'][$i]) && !empty($_POST['parent_group'][$i])) {
			$product_bundle_note = new ProductBundleNote();
			if (!empty($_POST['comment_id'][$i])) {
				$product_bundle_note->id = $_POST['comment_id'][$i];
			}
			$product_bundle_note->description = $_POST['comment_description'][$i];
			$product_bundle_note->save();
			$pb->set_product_bundle_note_relationship($_POST['parent_group_position'][$i], $product_bundle_note->id, $product_bundels[$_REQUEST['parent_group'][$i]]);
	}
}

if (isset($GLOBALS['check_notify'])) {
	$check_notify = $GLOBALS['check_notify'];
}
else {
	$check_notify = FALSE;
}
$focus->save($check_notify);

$return_id = $focus->id;

$GLOBALS['log']->debug("Saved record with id of ".$return_id);
$return_module = 'Quotes';
if (!empty($_REQUEST['return_module']))
{
	$return_module = $_REQUEST['return_module'];
}
handleRedirect($return_id,$return_module);


?>
