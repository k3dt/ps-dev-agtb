<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

/**
 * Class SugarACLTeamBased
 * Check access to the ACL options:
 * ACL_READ_SELECTED_TEAMS_WRITE
 * ACL_SELECTED_TEAMS_READ_OWNER_WRITE
 * ACL_SELECTED_TEAMS_READ_WRITE
 * ACL_ALLOW_SELECTED_TEAMS
 */
class SugarACLTeamBased extends SugarACLStrategy
{
    /**
     * {@inheritDoc}
     */
    public function checkAccess($module, $action, $context)
    {
        if (!$this->isAclApplied($context)) {
            return true;
        }
        $user = $this->getCurrentUser($context);
        $bean = $context['bean'];
        $isOwner = !empty($context['owner_override']) ?
            $context['owner_override'] :
            $bean->isOwner($user->id);
        if ($isOwner || !$bean->bean_implements('ACL')) {
            // Owner has access.
            return true;
        }

        $action = $this->fixUpActionName($action);
        // Field level.
        if ($action == 'field') {
            return $this->fieldACL($bean, $user, $context, $isOwner);
        }

        // Module level.
        if ($this->getModuleAccess($user, $module, $action, $bean->acltype) == ACL_ALLOW_SELECTED_TEAMS &&
            !$this->isUserInSelectedTeams($user, $bean)
        ) {
            return false;
        }
        return true;
    }

    /**
     * Check access for the options:
     * ACL_READ_SELECTED_TEAMS_WRITE - Read/(Owner & Selected Teams) Write.
     * ACL_SELECTED_TEAMS_READ_OWNER_WRITE - (Owner & Selected Teams) Read/Owner Write.
     * ACL_SELECTED_TEAMS_READ_WRITE - (Owner & Selected Teams) Read/(Owner & Selected Teams) Write.
     * @param SugarBean $bean
     * @param User $user
     * @param array $context
     * @return bool
     */
    protected function fieldACL($bean, $user, $context)
    {
        // Loaded in bean's constructor.
        $access = !empty(ACLField::$acl_fields[$user->id][$bean->module_dir][$context['field']]) ?
            ACLField::$acl_fields[$user->id][$bean->module_dir][$context['field']] :
            null;
        if (!$access) {
            return true;
        }

        switch ($context['action']) {
            case 'read':
            case 'detail':
            case 'list':
                if (($access == ACL_SELECTED_TEAMS_READ_OWNER_WRITE || $access == ACL_SELECTED_TEAMS_READ_WRITE) &&
                    !$this->isUserInSelectedTeams($user, $bean)
                ) {
                    return false;
                }
                break;
            case 'write':
            case 'edit':
                if ($access == ACL_SELECTED_TEAMS_READ_OWNER_WRITE) {
                    // Not owner.
                    return false;
                }
                if (($access == ACL_READ_SELECTED_TEAMS_WRITE || $access == ACL_SELECTED_TEAMS_READ_WRITE) &&
                    !$this->isUserInSelectedTeams($user, $bean)
                ) {
                    return false;
                }
                break;
        }
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getFieldListAccess($module, $field_list, $context)
    {
        $user = $this->getCurrentUser($context);
        if (!$user ||
            $user->isAdmin() ||
            !ACLField::hasACLs($user->id, $module)
        ) {
            return array();
        }
        return parent::getFieldListAccess($module, $field_list, $context);
    }

    /**
     * Handle the ACL_ALLOW_SELECTED_TEAMS option.
     * {@inheritDoc}
     */
    public function getUserAccess($module, $accessList, $context)
    {
        if (!$this->isAclApplied($context)) {
            return $accessList;
        }
        foreach ($accessList as $action => $val) {
            $accessList[$action] = $this->checkAccess($module, $action, $context);
        }
        return $accessList;
    }

    /**
     * Check if the TBA can be applied.
     * @param $context
     * @return bool
     */
    protected function isAclApplied($context)
    {
        $user = $this->getCurrentUser($context);
        if (!$user ||
            $user->isAdmin() ||
            empty($context['bean']) ||
            !$context['bean']->id
        ) {
            return false;
        }
        return true;
    }

    /**
     * Get module's access code.
     * @param User $user
     * @param string $module
     * @param string $action
     * @param string $aclType
     * @return int|null
     */
    protected function getModuleAccess($user, $module, $action, $aclType)
    {
        $actions = ACLAction::getUserActions($user->id, false, $module, $aclType);
        return !empty($actions[$action]['aclaccess']) ? $actions[$action]['aclaccess'] : null;
    }

    /**
     * Check if a user presents in bean's selected teams.
     * @param User $user
     * @param SugarBean $bean
     * @return bool
     */
    protected function isUserInSelectedTeams($user, $bean)
    {
        $tbaConfigurator = new TeamBasedACLConfigurator();
        if (!$tbaConfigurator->isImplementTBA($bean->module_dir)) {
            // Does not implement TBA. Has access.
            return true;
        }
        $sq = new SugarQuery();
        $sq->select()->setCountQuery();
        $sq->from(BeanFactory::getBean('Teams'), array('alias' => 'teams', 'team_security' => false));
        $sq->joinRaw(
            "INNER JOIN team_memberships tm ON tm.team_id = teams.id AND tm.user_id = '{$user->id}' AND tm.deleted = 0"
        );
        $sq->joinRaw(
            "INNER JOIN (
                SELECT tst.team_id
                FROM team_sets_teams tst
		        INNER JOIN {$bean->table_name} bean ON tst.team_set_id = bean.team_set_selected_id
                    AND bean.id = '{$bean->id}' AND tst.deleted = 0
            ) teams_selected ON teams_selected.team_id = teams.id"
        );
        $result = $sq->execute();

        return (bool)$result[0]['record_count'];
    }
}
