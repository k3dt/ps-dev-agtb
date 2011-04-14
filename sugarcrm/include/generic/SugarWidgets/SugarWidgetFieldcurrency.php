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

                                                                                       
require_once('include/generic/SugarWidgets/SugarWidget.php');
                                                                                       

global $current_user;
                                                                                       
$global_currency_obj = null;
                                                                                       
function get_currency()
{
        global $current_user,$global_currency_obj;
        if (empty($global_currency_obj))
        {
        $global_currency_obj = new Currency();
      //  $global_currency_symbol = '$';
                                                                                       
        if($current_user->getPreference('currency') )
        {
                $global_currency_obj->retrieve($current_user->getPreference('currency'));
        }
        else
        {
                $global_currency_obj->retrieve('-99');
        }
        }
        return $global_currency_obj;
}


class SugarWidgetFieldCurrency extends SugarWidgetFieldInt
{
        function & displayList($layout_def)
        {
            static $currencies;

            // If it's not grouped, or if it's grouped around a system currency column, look up the currency symbol so we can display it next to the amount
            if ( empty($layout_def['group_function']) || $this->isSystemCurrency($layout_def) ) {
                if ( isset($layout_def['varname']) ) {
                    $key = $layout_def['varname'];
                } else {
                    $key = $this->_get_column_alias($layout_def);
                }
                $key = strtoupper($key);
                
                if ( $this->isSystemCurrency($layout_def) ) {
                    $currency_id = '-99';
                } else {
                    $currency_id = $layout_def['fields'][$key.'_CURRENCY'];
                }
                if ( empty($currency_id) ) {
                    $currency_id = '-99';
                }
                
                if ( !isset($currencies[$currency_id]) ) {
                    $currencies[$currency_id] = new Currency();
                    $currencies[$currency_id]->retrieve($currency_id);
                }
                if(!empty($currencies[$currency_id]->symbol)){
                    $symbol = $currencies[$currency_id]->symbol.' ';
                } else {
                    $symbol = '';
                }
            } else {
                $symbol = '';
            }
//                $global_currency_obj = get_currency();
//                  $display = format_number($this->displayListPlain($layout_def), 2, 2, array('convert' => true, 'currency_symbol' => true));
//                $display =  $global_currency_obj->symbol. round($global_currency_obj->convertFromDollar($this->displayListPlain($layout_def)),2);
            $display = $symbol.$this->displayListPlain($layout_def);
            return $display;
        }
                             
    function displayListPlain($layout_def) {
//        $value = $this->_get_list_value($layout_def);
        $value = format_number(parent::displayListPlain($layout_def), 2, 2, array('convert' => false, 'currency_symbol' => false));
        return $value;
    }                                                          
 function queryFilterEquals(&$layout_def)
 {
     return $this->_get_column_select($layout_def)."=".$GLOBALS['db']->quote(unformat_number($layout_def['input_name0']))."\n";
 }
                                                                                       
 function queryFilterNot_Equals(&$layout_def)
 {
     return $this->_get_column_select($layout_def)."!=".$GLOBALS['db']->quote(unformat_number($layout_def['input_name0']))."\n";
 }
                                                                                       
 function queryFilterGreater(&$layout_def)
 {
     return $this->_get_column_select($layout_def)." > ".$GLOBALS['db']->quote(unformat_number($layout_def['input_name0']))."\n";
 }
                                                                                       
 function queryFilterLess(&$layout_def)
 {
     return $this->_get_column_select($layout_def)." < ".$GLOBALS['db']->quote(unformat_number($layout_def['input_name0']))."\n";
 }

 function queryFilterBetween(&$layout_def){
     return $this->_get_column_select($layout_def)." > ".$GLOBALS['db']->quote(unformat_number($layout_def['input_name0'])). " AND ". $this->_get_column_select($layout_def)." < ".$GLOBALS['db']->quote(unformat_number($layout_def['input_name1']))."\n";
 }

 function isSystemCurrency(&$layout_def)
 {
     if (strpos($layout_def['name'],'_usdoll') === false) {
         return false;
     } else {
         return true;
     }
 }

 function querySelect(&$layout_def)
 {
     // We need to fetch the currency id as well
     if ( !$this->isSystemCurrency($layout_def) && empty($layout_def['group_function'])) {
         if ( !empty($layout_def['table_alias']) ) {
             $table = $layout_def['table_alias'];
         } else {
             $table = '';
         }
         
         $add_currency_id = false;
         if(!empty($table)) {
         	$cols = $GLOBALS['db']->getHelper()->get_columns($table);
            $add_currency_id = isset($cols['currency_id']) ? true : false;
            
            if(!$add_currency_id && preg_match('/.*?_cstm$/i', $table)) {
               $table = str_replace('_cstm', '', $table);
               $cols = $GLOBALS['db']->getHelper()->get_columns($table);
               $add_currency_id = isset($cols['currency_id']) ? true : false;
            }
         }
         
         if($add_currency_id) {         	
         	return $this->_get_column_select($layout_def)." ".$this->_get_column_alias($layout_def)." , ".$table.".currency_id ". $this->getTruncatedColumnAlias($this->_get_column_alias($layout_def)."_currency") . "\n";
         } else {
         	return $this->_get_column_select($layout_def)." ".$this->_get_column_alias($layout_def)."\n";
         }      
     } else {
         return $this->_get_column_select($layout_def)." ".$this->_get_column_alias($layout_def)."\n";
     }
 }


}

?>
