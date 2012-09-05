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




// User is used to store Forecast information.
class Worksheet extends SugarBean {

    var $id;
    var $user_id;
    var $timeperiod_id;
    var $forecast_type;
    var $related_id;
    var $related_forecast_type;
    var $currency_id;
    var $base_rate;
    var $best_case;
    var $likely_case;
    var $worst_case;
    var $date_modified;
    var $modified_user_id;
    var $deleted;
    var $forecast;
    var $commit_stage;
    var $op_probability;
    var $quota;
    var $version;

    var $table_name = "worksheet";

    var $object_name = "Worksheet";
    var $disable_custom_fields = true;

    // This is used to retrieve related fields from form posts.
    var $additional_column_fields = Array('');



    var $new_schema = true;
    var $module_dir = 'Forecasts';
    function Worksheet() {
        parent::SugarBean();
        $this->disable_row_level_security =true;
    }

    function save($check_notify = false){
        require_once 'include/SugarCurrency.php';
        if(empty($this->currency_id)) {
            // use user preferences for currency
            $currency = SugarCurrency::getUserLocaleCurrency();
            $this->currency_id = $currency->id;
        } else {
            $currency = SugarCurrency::getCurrencyByID($this->currency_id);
        }
        $this->base_rate = $currency->conversion_rate;
        
        parent::save($check_notify);
    }

    function get_summary_text() {
        return "$this->id";
    }

    function retrieve($id, $encode=false, $deleted=true){
        $ret = parent::retrieve($id, $encode, $deleted);

        return $ret;
    }

    function is_authenticated()
    {
        return $this->authenticated;
    }

}
?>
