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
 //Request object must have these property values:
 //		Module: module name, this module should have a file called TreeData.php
 //		Function: name of the function to be called in TreeData.php, the function will be called statically.
 //		PARAM prefixed properties: array of these property/values will be passed to the function as parameter.


require_once('include/JSON.php');
require_once('include/upload_file.php');

if (!is_dir($cachedir = sugar_cached('images/')))
    mkdir_recursive($cachedir);

// cn: bug 11012 - fixed some MIME types not getting picked up.  Also changed array iterator.
$imgType = array('image/gif', 'image/png', 'image/x-png', 'image/bmp', 'image/jpeg', 'image/jpg', 'image/pjpeg');

$ret = array();

foreach($_FILES as $k => $file) {
	if(in_array(strtolower($_FILES[$k]['type']), $imgType) && $_FILES[$k]['size'] > 0) {
	    $upload_file = new UploadFile($k);
		// check the file
		if($upload_file->confirm_upload()) {
		    $dest = $cachedir.basename($upload_file->get_stored_file_name()); // target name
		    $guid = create_guid();
		    if($upload_file->final_move($guid)) { // move to uploads
		        $path = $upload_file->get_upload_path($guid);
		        // if file is OK, copy to cache
		        if(verify_uploaded_image($path) && copy($path, $dest)) {
		            $ret[] = $dest;
		        }
		        // remove temp file
		        unlink($path);
		    }
		}
	}
}

if (!empty($ret)) {
	$json = getJSONobj();
	echo $json->encode($ret);
	//return the parameters
}
