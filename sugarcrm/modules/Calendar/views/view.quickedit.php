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
require_once('include/EditView/EditView2.php');


class CalendarViewQuickEdit extends SugarView
{
	public $ev;
	protected $editable;
	
	public function preDisplay()
	{
		$this->bean = $this->view_object_map['currentBean'];

		if ($this->bean->ACLAccess('Save')) {
			$this->editable = 1;
		} else {
			$this->editable = 0;
		}
	}

	public function display()
	{
		require_once("modules/Calendar/CalendarUtils.php");

		$module = $this->view_object_map['currentModule'];

		$_REQUEST['module'] = $module;

		$base = 'modules/' . $module . '/metadata/';
		$source = SugarAutoLoader::existingCustomOne($base . 'editviewdefs.php', $base.'quickcreatedefs.php');

		$GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], $module);
        $tpl = SugarAutoLoader::existingCustomOne('include/EditView/EditView.tpl');

		$this->ev = new EditView();
		$this->ev->view = "QuickCreate";
		$this->ev->ss = new Sugar_Smarty();
		$this->ev->formName = "CalendarEditView";
		$this->ev->setup($module,$this->bean,$source,$tpl);
		$this->ev->defs['templateMeta']['form']['headerTpl'] = "modules/Calendar/tpls/editHeader.tpl";
		$this->ev->defs['templateMeta']['form']['footerTpl'] = "modules/Calendar/tpls/empty.tpl";
		$this->ev->process(false, "CalendarEditView");
		
		if (!empty($this->bean->id)) {
		    require_once('include/json_config.php');
		    $jsonConfig = new json_config();
		    $grJavascript = $jsonConfig->getFocusData($module, $this->bean->id);
        } else {
            $grJavascript = "";
        }	
	
		$jsonArr = array(
				'access' => 'yes',
				'module_name' => $this->bean->module_dir,
				'record' => $this->bean->id,
				'edit' => $this->editable,
				'html'=> $this->ev->display(false, true),
				'gr' => $grJavascript,
		);
		
		if (!empty($this->view_object_map['repeatData'])) {
			$jsonArr = array_merge($jsonArr, array("repeat" => $this->view_object_map['repeatData']));
		}
			
		ob_clean();
		echo json_encode($jsonArr);
	}
}

?>
