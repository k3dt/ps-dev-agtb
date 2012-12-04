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
require_once('data/SugarACLStrategy.php');

class SugarACLUsers extends SugarACLStrategy
{
    /**
     * Check access a current user has on Users and Employees
     * @param string $module
     * @param string $view
     * @param array $context
     * @return bool|void
     */
    public function checkAccess($module, $view, $context) {
        if($module != 'Users' && $module != 'Employees') {
            // how'd you get here...
            return false;
        }


        $current_user = $this->getCurrentUser($context);

        if(is_admin($current_user)) {
            return true;
        }

        if(empty($view) || empty($current_user->id)) {
            return true;
        }

        $bean = self::loadBean($module, $context);

        // we can update ourselves
        if(!empty($bean->id) && $bean->id == $current_user->id) {
            if ( $view == 'delete' ) {
                return false;
            }
            return true;
        }

        if($view == 'view' || $view == 'ListView' || $view == 'list' || $view == 'field' || $view == 'DetailView' || $view == 'detail' || $view == 'team_security'  || $view == 'access') {
            if($view == 'field' && $context['field'] == 'password') {
                return false;
            }
            if($view == 'field' && $context['field'] == 'is_admin' && ($context['action'] == 'edit' || $context['action'] == 'massupdate' || $context['action'] == 'delete')) {
                return false;
            }
            return true;
        }

        return false;
    }

    /**
     * Load bean from context
     * @static
     * @param string $module
     * @param array $context
     * @return SugarBean
     */
    protected static function loadBean($module, $context = array()) {
        if(isset($context['bean']) && $context['bean'] instanceof SugarBean && $context['bean']->module_dir == $module) {
            $bean = $context['bean'];
        } else {
            $bean = BeanFactory::newBean($module);
        }
        return $bean;
    }

}
