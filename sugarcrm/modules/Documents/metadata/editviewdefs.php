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
$viewdefs['Documents']['EditView'] = array(
    'templateMeta' => array('form' => array('enctype'=>'multipart/form-data',
                                            'hidden'=>array('<input type="hidden" name="old_id" value="{$fields.document_revision_id.value}">',
                                            				'<input type="hidden" name="contract_id" value="{$smarty.request.contract_id}">'),
                            			    ),       
                            'maxColumns' => '2', 
                            'widths' => array(
                                            array('label' => '10', 'field' => '30'), 
                                            array('label' => '10', 'field' => '30')
                                            ),
'javascript' => '<script type="text/javascript" src="include/javascript/popup_parent_helper.js?s={$SUGAR_VERSION}&c={$JS_CUSTOM_VERSION}"></script>
<script type="text/javascript" src="include/jsolait/init.js?s={$SUGAR_VERSION}&c={$JS_CUSTOM_VERSION}"></script>
<script type="text/javascript" src="include/jsolait/lib/urllib.js?s={$SUGAR_VERSION}&c={$JS_CUSTOM_VERSION}"></script>
<script type="text/javascript" src="include/javascript/jsclass_base.js"></script>
<script type="text/javascript" src="include/javascript/jsclass_async.js"></script>
<script type="text/javascript" src="include/JSON.js?s={$SUGAR_VERSION}"></script>
<script type="text/javascript" src="modules/Documents/documents.js?s={$SUGAR_VERSION}&c={$JS_CUSTOM_VERSION}"></script>',
),
 'panels' =>array (
  'lbl_document_information' => 
  array (
    array (
      'doc_type',
    ),
    array (
      'filename',
      array (
            'name' => 'status_id',
            'label' => 'LBL_DOC_STATUS',
      ),          
    ),
    
    array (
      'document_name',
    
      array('name'=>'revision',
            'customCode' => '<input name="revision" type="text" value="{$fields.revision.value}" {$DISABLED}>'
           ),
      
    ),
    
    array (
	    array (
	      'name' => 'template_type',
	      'label' => 'LBL_DET_TEMPLATE_TYPE',
	    ),
    	array (
	      'name' => 'is_template',
	      'label' => 'LBL_DET_IS_TEMPLATE',
	    ),
    ),
    
    array (
      array('name'=>'active_date'),
       'category_id',

    ),    
    
    array (
      'exp_date',
      'subcategory_id',
    ),
    
    array (
      array('name'=>'description'),
    ),
    
    //BEGIN SUGARCRM flav!=sales ONLY
    array (
      array('name'=>'related_doc_name',
            'customCode' => '<input name="related_document_name" type="text" size="30" maxlength="255" value="{$RELATED_DOCUMENT_NAME}" readonly>' .
            		        '<input name="related_doc_id" type="hidden" value="{$fields.related_doc_id.value}"/>&nbsp;' .
            		        '<input title="{$APP.LBL_SELECT_BUTTON_TITLE}" accessKey="{$APP.LBL_SELECT_BUTTON_KEY}" type="{$RELATED_DOCUMENT_BUTTON_AVAILABILITY}" class="button" value="{$APP.LBL_SELECT_BUTTON_LABEL}" name="btn2" onclick=\'open_popup("Documents", 600, 400, "", true, false, {$encoded_document_popup_request_data}, "single", true);\'/>'),
      array('name'=>'related_doc_rev_number',
            'customCode' => '<select name="related_doc_rev_id" id="related_doc_rev_id" {$RELATED_DOCUMENT_REVISION_DISABLED}>{$RELATED_DOCUMENT_REVISION_OPTIONS}</select>',
           ),
    ),
    //END SUGARCRM flav!=sales ONLY
    
    
     //BEGIN SUGARCRM flav=pro ONLY
    array (
      array('name'=>'team_name','displayParams'=>array('required'=>true)),
    ),
    //END SUGARCRM flav=pro ONLY
  ),
)


);
?>