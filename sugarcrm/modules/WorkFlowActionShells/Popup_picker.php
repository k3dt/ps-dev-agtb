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
global $theme;

class Popup_Picker
{

    /**
     * @deprecated
     */
    public function Popup_Picker()
    {
    }

	function process_page()
	{
		global $theme;
		global $mod_strings;
		global $app_strings;
		global $currentModule;
		global $current_language;
		global $current_module_strings;
        if(!is_file(sugar_cached('jsLanguage/WorkFlow/') . $GLOBALS['current_language'] . '.js')) {
            jsLanguage::createModuleStringsCache('WorkFlow', $GLOBALS['current_language']);
        }
        $javascript_language_files = getVersionedScript("cache/jsLanguage/WorkFlow/{$GLOBALS['current_language']}.js", $GLOBALS['sugar_config']['js_lang_version']);
		$current_module_strings = return_module_language($current_language, 'WorkFlowActionShells');


		$ListView = new ListView();
		$header_text = '';

		if(isset($_REQUEST['workflow_id']))
		{
			$workflow = BeanFactory::getBean('WorkFlow', $_REQUEST['workflow_id']);
			//TODO GET ALL ALERTS HERE
			//$focus_alerts_list = $workflow->get_linked_beans('wf_alerts','WorkFlowAlertShell');
			$actions = BeanFactory::getBean('WorkFlowActionShells');

			$current_module_strings = return_module_language($current_language, $actions->module_dir);
			insert_popup_header($theme);
			$ListView->initNewXTemplate('modules/WorkFlowActionShells/Popup_picker.html', $current_module_strings);
			$ListView->xTemplateAssign("WORKFLOW_ID", $workflow->id);
			$ListView->xTemplateAssign("JAVASCRIPT_LANGUAGE_FILES", $javascript_language_files);
			$ListView->xTemplateAssign("RETURN_URL", "&return_module=".$currentModule."&return_action=DetailView&return_id={$workflow->id}");
			$ListView->xTemplateAssign("EDIT_INLINE_PNG",  SugarThemeRegistry::current()->getImage('edit_inline','align="absmiddle" border="0"',null,null,'.gif',$app_strings['LNK_EDIT']));
			$ListView->xTemplateAssign("DELETE_INLINE_PNG",  SugarThemeRegistry::current()->getImage('delete_inline','align="absmiddle" border="0"',null,null,'.gif',$app_strings['LNK_REMOVE']));
			$ListView->setHeaderTitle($current_module_strings['LBL_MODULE_NAME'] . $header_text);

			//$ListView->setQuery("workflow_actionshells.alert_type = 'Email'","","", "ALERT");
			$list = $actions->get_list("", "");
			$display_list = $this->cullFromList($list['list'], $workflow->base_module, $workflow->type);
			$ListView->processListViewTwo($display_list, "main", "ACTION");

		//	$ListView->processListView($actions, "main", "ACTION");
			insert_popup_footer();
		}
	}

	function cullFromList($list, $base_module, $type)
	{
		$return_list = array();
		foreach($list as $action)
		{
			if($action->parent_base_module == $base_module && $action->parent_type == $type)
			{
				$return_list[] = $action;
			}
		}
		return $return_list;
	}
}
