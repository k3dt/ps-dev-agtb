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
$viewdefs['ProspectLists']['DetailView'] = array(
'templateMeta' => array('form' => array('closeFormBeforeCustomButtons' => true,'buttons'=>array('EDIT', 'DUPLICATE', 'DELETE', 
array('customCode'=> '<input title="{$APP.LBL_EXPORT}"  class="button" type="button" name="opp_to_quote_button" value="{$APP.LBL_EXPORT}" onclick="document.location.href = \'index.php?entryPoint=export&module=ProspectLists&uid={$fields.id.value}&members=1\'">'),)),
				
						'maxColumns' => '2',
                        'widths' => array(
                                        array('label' => '10', 'field' => '30'),
                                        array('label' => '10', 'field' => '30')
                                        ),
                        ),
'panels' =>array (

  array (
      'name'
  ),

  array (
      'list_type',
      'domain_name',
  ),

  array (
      'assigned_user_name',
//BEGIN SUGARCRM flav=pro ONLY
      'team_name',
//END SUGARCRM flav=pro ONLY
  ),

  array (
      array (
        'name' => 'date_modified',
        'label' => 'LBL_DATE_MODIFIED',
        'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
      ),
      array (
        'name' => 'date_entered',
        'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
      ),
    ),

  array (
      array('name'=>'entry_count','label'=>'LBL_ENTRIES'),
  ),

  array (
      'description',
  ),
)



);
?>