<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once('include/SugarFields/Fields/Base/SugarFieldBase.php');

class SugarFieldPhone extends SugarFieldBase {
    
	/**
     * This should be called when the bean is saved. The bean itself will be passed by reference
     * 
     * @param SugarBean bean - the bean performing the save
     * @param array params - an array of paramester relevant to the save, most likely will be $_REQUEST
     */
	public function save($bean, $params, $field, $properties, $prefix = ''){
		 parent::save($bean, $params, $field, $properties, $prefix);
		 //BEGIN SUGARCRM flav=int ONLY
		 /*
         if(isset($params[$prefix.$field]))
         {


             if(!empty($properties['validate_usa_format']) && preg_match('/^([\+])?([1])?[- .]?[\(]?([2-9]\d{2})[\)]?[- .]?([0-9]{3})[- .]?([0-9]{4})$/', $params[$prefix.$field], $matches))
         	 {
	         	 $international_sign = !empty($matches[1]) ? $matches[1] : '';
	         	 $country_code = !empty($matches[2]) ? $matches[2] . ' ' : '';
	             $bean->$field = $international_sign . $country_code . '(' . $matches[3] . ') ' . $matches[4] . '-' . $matches[5];
         	 } else {

         	 //END SUGARCRM flav=int ONLY
         	 	if (isset($params[$prefix.$field]))
		            $bean->$field = $params[$prefix.$field];
         	 //BEGIN SUGARCRM flav=int ONLY
         	 }

         }
         */
		 //END SUGARCRM flav=int ONLY
    }    
    
}
