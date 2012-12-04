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
 * $Id: Delete.php 13782 2006-06-06 17:58:55 +0000 (Tue, 06 Jun 2006) majed $
 * Description:  Deletes an Account record and then redirects the browser to the
 * defined return URL.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
global $mod_strings;
global $sugar_config;

if(!isset($_REQUEST['record']))
	sugar_die($mod_strings['ERR_DELETE_RECORD']);
$focus = BeanFactory::getBean('KBDocuments', $_REQUEST['record']);
if(!$focus->ACLAccess('Delete')){
	ACLController::displayNoAccess(true);
	sugar_cleanup(true);
}

//Retrieve all related kbdocument revisions.
$kbdocrevs = KBDocument::get_kbdocument_revisions($_REQUEST['record']);
//Loop through kbdocument revisions and delete one by one.
if (!empty($kbdocrevs) && is_array($kbdocrevs)) {
	foreach($kbdocrevs as $key=>$thiskbid) {
		$thiskbversion = BeanFactory::getBean('KBDocumentRevisions', $thiskbid);
		//Check for related documentrevision and delete.
        if($thiskbversion->document_revision_id != null){
	        $docrev_id = $thiskbversion->document_revision_id;
			$thisdocrev = BeanFactory::getBean('DocumentRevisions', $docrev_id);

           	UploadFile::unlink_file($docrev_id,$thisdocrev->filename);
           	UploadFile::unlink_file($docrev_id);
			//mark version deleted
			$thisdocrev->mark_deleted($thisdocrev->id);
        }
        //Also check for related kbcontent and delete.
        if($thiskbversion->kbcontent_id != null){
			BeanFactory::deleteBean('KBContents', $thiskbversion->kbcontent_id);
        }
		//Finally delete the kbdocument revision.
	   $thiskbversion->mark_deleted($thiskbversion->id);
	}
}

//delete kbdocuments_kbtags
$deleted=1;
$q = 'UPDATE kbdocuments_kbtags SET deleted = '.$deleted.' WHERE kbdocument_id = \''.$_REQUEST['record'].'\'';
$focus->db->query($q);

$focus->mark_deleted($_REQUEST['record']);

header("Location: index.php?module=".$_REQUEST['return_module']."&action=".$_REQUEST['return_action']."&record=".$_REQUEST['return_id']);
