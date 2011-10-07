<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

require_once('include/SearchForm/SearchForm2.php');

class EmployeesSearchForm extends SearchForm {
    /**
     * This builds an EmployeesSearchForm from a classic search form.
     */
    function __construct( SearchForm $oldSearchForm ) {
        parent::SearchForm($oldSearchForm->seed, $oldSearchForm->module, $oldSearchForm->action);
        $this->setup(
            // $searchdefs
            array($oldSearchForm->module => $oldSearchForm->searchdefs),
            // $searchFields
            array($oldSearchForm->module => $oldSearchForm->searchFields),
            // $tpl
            $oldSearchForm->tpl,
            // $displayView
            $oldSearchForm->displayView,
            // listViewDefs
            $oldSearchForm->listViewDefs);
        
        $this->lv = $oldSearchForm->lv;
                     
    }
    
    public function generateSearchWhere($add_custom_fields = false, $module = '') {
        $where_clauses = parent::generateSearchWhere($add_custom_fields, $module);
        
        // Add in code to remove portal/group/terminated users
        $where_clauses[] = "portal_only = 0";
        $where_clauses[] = "is_group = 0";
        $where_clauses[] = "status <> 'Reserved'";
        
        return $where_clauses;
    }
}