<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
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

/**
 * ACL-driven visibility
 * @api
 */
class ACLVisibility extends SugarVisibility
{
    /**
     * (non-PHPdoc)
     * @see SugarVisibility::addVisibilityWhere()
     */
    public function addVisibilityWhere(&$query)
    {
        $action = $this->getOption('action', 'list');
        if($this->bean->bean_implements('ACL') && ACLController::requireOwner($this->bean->module_dir, $action)) {
            $owner_where = $this->bean->getOwnerWhere($GLOBALS['current_user']->id, $this->getOption('table_alias'));
            if(!empty($query)) {
                $query .= " AND $owner_where";
            } else {
                $query = $owner_where;
            }
        }
        return $query;
    }

    public function addVisibilityWhereQuery(SugarQuery $sugarQuery, $options = array()) {
        $where = null;
        $this->addVisibilityWhere($where, $options);
        if(!empty($where)) {
            $sugarQuery->where()->addRaw($where);
        }
        
        return $sugarQuery;
    }

    public function addSseVisibilityFilter($engine, $filter)
    {
        if ($this->bean->bean_implements('ACL') && ACLController::requireOwner($this->bean->module_dir, 'list'))
        {
            if($engine instanceof SugarSearchEngineElastic) {
                $filter->addMust($engine->getOwnerTermFilter());
            }
        }
        return $filter;
    }
}
