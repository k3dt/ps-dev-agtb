<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

//FILE SUGARCRM flav=pro ONLY

require_once 'include/Sugarpdf/sugarpdf/sugarpdf.smarty.php';
 
class SugarpdfPdfmanager extends SugarpdfSmarty {

    function preDisplay(){
        
        parent::preDisplay();

        if (!empty($_REQUEST['pdf_template_id'])) {
            $this->templateLocation = $this->making_template_to_tpl($_REQUEST['pdf_template_id']);
        }        
        
        require_once 'include/DetailView/DetailView2.php';
        
        $sugarView = new SugarView();
        $sugarView->init($this->module);
        $sugarView->type = 'detail';
        
        
        $tmp_dv = new DetailView2();
        $tmp_dv->showVCRControl = false;
        
        $tmp_dv->setup($this->module, $this->bean, $sugarView->getMetaDataFile());
        $tmp_dv->process();
        $fields = $tmp_dv->fieldDefs;

        
        if ($this->module == 'Quotes') {
            global $locale;
            require_once('modules/Quotes/Quote.php');
            require('modules/Quotes/config.php');
            require_once('modules/Currencies/Currency.php');
            $currency = new Currency();
            ////    settings
            $format_number_array = array(
                'currency_symbol' => true,
                'type' => 'sugarpdf',
                'currency_id' => $this->bean->currency_id,
                'charset_convert' => true, /* UTF-8 uses different bytes for Euro and Pounds */
            );
            $currency->retrieve($this->bean->currency_id);
            $fields['currency_iso']['value'] = $currency->iso4217;
            $fields['subtotal']['value'] = format_number_sugarpdf($this->bean->subtotal, $locale->getPrecision(), $locale->getPrecision(), $format_number_array);
            $fields['total']['value'] = format_number_sugarpdf($this->bean->total, $locale->getPrecision(), $locale->getPrecision(), $format_number_array);
            
            $tmp_dv->focus->load_relationship('product_bundles');
            $product_bundle_list = $tmp_dv->focus->get_linked_beans('product_bundles','ProductBundle');
            if(is_array($product_bundle_list)){
          
              $ordered_bundle_list = array();
              for ($cnt = 0; $cnt < count($product_bundle_list); $cnt++) {
                $index = $product_bundle_list[$cnt]->get_index($tmp_dv->focus->id);
                $ordered_bundle_list[(int)$index[0]['bundle_index']] = $product_bundle_list[$cnt];
              } //for
              ksort($ordered_bundle_list);
            } //if

            $ordered_bundle_list_data = array();
            $bundles = array();
            $count = 0;
            foreach ($ordered_bundle_list as $ordered_bundle) {
                $bundle = array();
                $bundle['name']['value'] = $ordered_bundle->name;
                $bundle['subtotal']['value'] = format_number_sugarpdf($ordered_bundle->subtotal, $locale->getPrecision(), $locale->getPrecision(), $format_number_array);
                $bundle['total']['value'] = format_number_sugarpdf($ordered_bundle->total, $locale->getPrecision(), $locale->getPrecision(), $format_number_array);
                
                
                $bundle['products'] = array();
                $product_bundle_line_items = $ordered_bundle->get_product_bundle_line_items();
                foreach ($product_bundle_line_items as $product_bundle_line_item) {

                    if ($product_bundle_line_item->object_name == "Product") {
                        $bundle['products'][$count]['quantity']['value'] = format_number_sugarpdf($product_bundle_line_item->quantity, 0, 0);
                        $bundle['products'][$count]['mft_part_num']['value'] = $product_bundle_line_item->mft_part_num;
                        $bundle['products'][$count]['name']['value'] = stripslashes($product_bundle_line_item->name);
                        if(!empty($product_bundle_line_item->description)){
                            $bundle['products'][$count]['name']['value'] .= "\n" . nl2br(stripslashes($product_bundle_line_item->description));
                        }

                        $bundle['products'][$count]['list_usdollar']['value'] = format_number_sugarpdf($product_bundle_line_item->list_usdollar, $locale->getPrecision(), $locale->getPrecision(), array_merge($format_number_array, array('convert' => true)));
                        $bundle['products'][$count]['discount_usdollar']['value'] = format_number_sugarpdf($product_bundle_line_item->discount_usdollar, $locale->getPrecision(), $locale->getPrecision(), array_merge($format_number_array, array('convert' => true)));
                        $bundle['products'][$count]['ext_price']['value'] = format_number_sugarpdf($product_bundle_line_item->discount_usdollar * $product_bundle_line_item->quantity, $locale->getPrecision(), $locale->getPrecision(), array_merge($format_number_array, array('convert' => true)));
                        $bundle['products'][$count]['discount_amount']['value'] = "";
                        if(format_number($ordered_bundle->deal_tot, $locale->getPrecision(), $locale->getPrecision())!= 0.00){
                            if($product_bundle_line_item->discount_select){
                                $bundle['products'][$count]['discount_amount']['value'] = format_number($product_bundle_line_item->discount_amount, $locale->getPrecision(), $locale->getPrecision())."%";
                            } else {
                                $bundle['products'][$count]['discount_amount']['value'] = format_number_sugarpdf($product_bundle_line_item->discount_amount, $locale->getPrecision(), $locale->getPrecision(), array_merge($format_number_array, array('convert' => false)));
                            }
                        }
                    } elseif ($product_bundle_line_item->object_name == "ProductBundleNote") {
                        $bundle['products'][$count]['quantity']['value'] = "";
                        $bundle['products'][$count]['mft_part_num']['value'] = "";
                        $bundle['products'][$count]['name']['value'] = stripslashes($product_bundle_line_item->description);
                        $bundle['products'][$count]['list_usdollar']['value'] = "";
                        $bundle['products'][$count]['discount_usdollar']['value'] = "";
                        $bundle['products'][$count]['ext_price'] = "";
                        $bundle['products'][$count]['discount_amount']['value'] = "";
                    }                
                    $count++;
                }                
                $bundles[] = $bundle;
            }
            // echo '<pre>';
            // print_r($bundles);
            // echo '</pre>';
            // die;
            $this->ss->assign('product_bundles', $bundles);
        }
 
         $this->ss->assign('fields', $fields);
 
    }

    private function making_template_to_tpl($syno_pdfTemplateId) {
        $pdfTemplate = BeanFactory::newBean('PdfManager');

        if ($pdfTemplate->retrieve($syno_pdfTemplateId) !== null) {
            
            if ( ! file_exists($GLOBALS['sugar_config']['cache_dir'] . 'modules/PdfManager/tpls') ) { 
                mkdir_recursive($GLOBALS['sugar_config']['cache_dir'] . 'modules/PdfManager/tpls'); 
            }
            $tpl_filename = $GLOBALS['sugar_config']['cache_dir'] . 'modules/PdfManager/tpls/' . $syno_pdfTemplateId . '.tpl';
            
            $pdfTemplate->body_html = from_html($pdfTemplate->body_html);
            
            if ($pdfTemplate->base_module == 'Quotes') {

                $pdfTemplate->body_html = str_replace(
                    '$fields.product_bundles', 
                    '$bundle',
                    $pdfTemplate->body_html
                );

                $pdfTemplate->body_html = str_replace(
                    '$fields.products', 
                    '$product',
                    $pdfTemplate->body_html
                );                
            
                $pdfTemplate->body_html = str_replace(
                    '<p>{START_BUNDLE::P}</p>', 
                    '{foreach from=$product_bundles item="bundle"}',
                    $pdfTemplate->body_html
                );
                $pdfTemplate->body_html = str_replace(
                    '<p>{END_BUNDLE::P}</p>', 
                    '{/foreach}',
                    $pdfTemplate->body_html
                );
            
                $pdfTemplate->body_html = str_replace(
                    "<tr>\r\n<td width=\"60%\">{START_PRODUCT::TR}", 
                    '{foreach from=$bundle.products item="product"}<tr><td width="60%">',
                    $pdfTemplate->body_html
                );
                $pdfTemplate->body_html = str_replace(
                    "{END_PRODUCT::TR}</td>\r\n</tr>", 
                    '</td></tr>{/foreach}',
                    $pdfTemplate->body_html
                );
            }
            
            
            sugar_file_put_contents($tpl_filename, $pdfTemplate->body_html);

            return $tpl_filename;
        }

        return '';        
    }

}

?>