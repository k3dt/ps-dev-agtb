<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * SugarWidgetSubPanelEditRoleButton
 *
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2005 SugarCRM, Inc.; All Rights Reserved.
 */

// $Id: SugarWidgetSubPanelEditRoleButton.php 13782 2006-06-06 17:58:55Z majed $

//FILE SUGARCRM flav=dce ONLY



class SugarWidgetDCESubPanelContactEditRoleButton extends SugarWidgetField
{
	function displayHeaderCell(&$layout_def)
	{
		return '&nbsp;';
	}

	function displayList(&$layout_def)
	{
		global $app_strings;
		$href = 'index.php?module=' . $layout_def['module']
            . '&action=' . 'ContactDCEInstanceRelationshipEdit'
            . '&record=' . $layout_def['fields']['DCEINSTANCE_ROLE_ID']
            . '&return_module=' . $_REQUEST['module']
            . '&return_action=' . 'DetailView'
            . '&return_id=' . $_REQUEST['record'];   
		$edit_icon_html = SugarThemeRegistry::current()->getImage( 'edit_inline', 'align="absmiddle" border="0"',null,null,'.gif',$app_strings['LNK_EDIT']);

	//based on listview since that lets you select records
	if($layout_def['ListView']){
		return '<a href="' . $href . '"'
			. 'class="listViewTdToolsS1">' . $edit_icon_html . '&nbsp;' . $app_strings['LNK_EDIT'] .'</a>&nbsp;';
	}else{
		return '';
	}
	}
}

?>