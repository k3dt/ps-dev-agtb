<?php
//FILE SUGARCRM flav!=com ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
require_once('include/SugarFields/Fields/Base/SugarFieldBase.php');

class SugarFieldImage extends SugarFieldBase {

    function getEditViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex, $searchView = false) {
    	$displayParams['bean_id']='id';
    	$this->setup($parentFieldArray, $vardef, $displayParams, $tabindex);
		return $this->fetch($this->findTemplate('EditView'));
    }

	function getDetailViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex, $searchView = false) {
		$displayParams['bean_id']='id';
		$this->setup($parentFieldArray, $vardef, $displayParams, $tabindex);
		return $this->fetch($this->findTemplate('DetailView'));
	}

    function getUserEditView($parentFieldArray, $vardef, $displayParams, $tabindex, $searchView = false) {
    	$displayParams['bean_id']='id';
    	$this->setup($parentFieldArray, $vardef, $displayParams, $tabindex, false);
		return $this->fetch($this->findTemplate('UserEditView'));
    }

    function getUserDetailView($parentFieldArray, $vardef, $displayParams, $tabindex, $searchView = false) {
    	$displayParams['bean_id']='id';
    	$this->setup($parentFieldArray, $vardef, $displayParams, $tabindex, false);
		return $this->fetch($this->findTemplate('UserDetailView'));
    }

	public function save(&$bean, $params, $field, $properties, $prefix = ''){
		require_once('include/upload_file.php');
		$upload_file = new UploadFile($field);

		//remove file
		if (isset($_REQUEST['remove_imagefile_' . $field]) && $_REQUEST['remove_imagefile_' . $field] == 1)
		{
			$upload_file->unlink_file($bean->$field);
			$bean->$field="";
		}

		//uploadfile
		if (isset($_FILES[$field]))
		{
			//confirm only image file type can be uploaded
			if (verify_image_file($_FILES[$field]['tmp_name']))
			{
				if ($upload_file->confirm_upload())
				{
                    // for saveTempImage API
                    if (isset($params['temp']) && $params['temp'] === true) {

                        // Create the new field value
                        $bean->$field = create_guid();

                        // Move to temporary folder
                        if (!$upload_file->final_move($bean->$field, true)) {
                            // If this was a fail, reset the bean field to original
                            $this->error = $upload_file->getErrorMessage();
                        }
                    } else {

                        // Capture the old value in case of error
                        $oldvalue = $bean->$field;

                        // Create the new field value
                        $bean->$field = create_guid();

                        // Add checking for actual file move for reporting to consumers
                        if (!$upload_file->final_move($bean->$field)) {
                            // If this was a fail, reset the bean field to original
                            $bean->$field = $oldvalue;
                            $this->error = $upload_file->getErrorMessage();
                        }
                    }
				}
                else
                {
                    // Added error reporting
                    $this->error = $upload_file->getErrorMessage();
                }
			}
            else {
                // Invalid format
                $this->error = $GLOBALS['app_strings']["LBL_UPLOAD_IMAGE_FILE_INVALID"];
            }
		}

		//Check if we have the duplicate value set and use it if $bean->$field is empty
		if(empty($bean->$field) && !empty($_REQUEST[$field . '_duplicate'])) {
           $bean->$field = $_REQUEST[$field . '_duplicate'];
		}
	}

}
?>
