<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
$viewdefs['Employees']['EditView'] = array(
    'templateMeta' => array('maxColumns' => '2', 
                            'widths' => array(
                                            array('label' => '10', 'field' => '30'), 
                                            array('label' => '10', 'field' => '30')
                                            ),
                            ),
 'panels' =>array (

  'default'=>array (
	    array (
	      'employee_status',
	  		//BEGIN SUGARCRM flav!=com ONLY
            array (
              'name'=>'picture',
              'label'=>'LBL_PICTURE_FILE',
            ),
	  		//END SUGARCRM flav!=com ONLY
	    ),
	    array (
	      'first_name',
	      array('name'=>'last_name', 'displayParams'=>array('required'=>true)),
	    ),
	    array (
          'title',
	      array('name'=>'phone_work','label'=>'LBL_OFFICE_PHONE'),
	    ),
	    array (
	      'department', 
	      'phone_mobile',
	    ),
	    array (
	      'reports_to_name',
	      'phone_other',
	    ),
	    array (
	      '',
	      array('name'=>'phone_fax', 'label'=>'LBL_FAX'),
	    ),
	    array (
	      '',
	      'phone_home',
	    ),
	    array (
	      'messenger_type',
	    ),
	    array (
	      'messenger_id',
	    ),
	    array (
	      array('name'=>'description', 'label'=>'LBL_NOTES'),
	    ),
	    array (
	      array('name'=>'address_street', 'type'=>'text', 'label'=>'LBL_PRIMARY_ADDRESS', 'displayParams'=>array('rows'=>2, 'cols'=>30)),
	      array('name'=>'address_city', 'label'=>'LBL_CITY'),
	    ),
	    array (
	      array('name'=>'address_state', 'label'=>'LBL_STATE'),
	      array('name'=>'address_postalcode', 'label'=>'LBL_POSTAL_CODE'),
	    ),
	    array (
	      array('name'=>'address_country', 'label'=>'LBL_COUNTRY'),
	    ),
        array(
          array (
              'name' => 'email1',
              'label' => 'LBL_EMAIL',
            ),
  		),
   ),
),

);
?>