<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

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

require_once('include/SubPanel/SubPanelDefinitions.php');

class ConfiguratorViewHistoryContactsEmails extends SugarView
{
    public function preDisplay()
    {
        if (!is_admin($GLOBALS['current_user'])) {
            sugar_die($GLOBALS['app_strings']['ERR_NOT_ADMIN']);
        }
    }

    public function display()
    {
        $modules = array();
        foreach ($GLOBALS['beanList'] as $moduleName => $objectName) {
            $bean = BeanFactory::getBean($moduleName);

            if (!($bean instanceof SugarBean)) {
                continue;
            }
            if (empty($bean->field_defs)) {
                continue;
            }

            // these are the specific modules we care about
            if (!in_array($moduleName, array('Opportunities','Accounts','Cases'))) {
                continue;
            }

            $bean->load_relationships();
            foreach ($bean->get_linked_fields() as $fieldName => $fieldDef) {
                if ($bean->$fieldName->getRelatedModuleName() == 'Contacts') {
                    $modules[$moduleName] = array(
                        'module' => $moduleName,
                        'label' => translate($moduleName),
                        'enabled' => empty($fieldDef['hide_history_contacts_emails'])
                    );
                    break;
                }
            }
        }

        if (!empty($GLOBALS['sugar_config']['hide_history_contacts_emails'])) {
            foreach ($GLOBALS['sugar_config']['hide_history_contacts_emails'] as $moduleName => $flag) {
                $modules[$moduleName]['enabled'] = !$flag;
            }
        }

        $this->ss->assign('modules', $modules);
        $this->ss->display('modules/Configurator/tpls/historyContactsEmails.tpl');
    }
}