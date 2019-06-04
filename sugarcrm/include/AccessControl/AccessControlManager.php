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

namespace Sugarcrm\Sugarcrm\AccessControl;

// This section of code is a portion of the code referred
// to as Critical Control Software under the End User
// License Agreement.  Neither the Company nor the Users
// may modify any portion of the Critical Control Software.
use Sugarcrm\Sugarcrm\Entitlements\SubscriptionManager;

/**
 * Class AccessControlManager
 *
 * check user's access permission.
 *
 * This is a singleton class
 *
 * @package Sugarcrm\Sugarcrm\AccessControl
 */
class AccessControlManager
{
    const MODULES_KEY = 'MODULES';
    const DASHLETS_KEY = 'DASHLETS';
    const RECORDS_KEY = 'RECORDS';
    const FIELDS_KEY = 'FIELDS';

    /**
     * flag to allow admin user to override access control
     * @var bool
     */
    protected $allowAdminOverride = false;

    /**
     * the flag to indicate in admin tasks, we need to disable access control to the admin to do tasks
     * @var bool
     */
    protected $isAdminWork = false;

    /**
     * @var array
     */
    protected $voters = [];

    /**
     * instance
     * @var AccessControlManager
     */
    protected static $instance;

    /**
     * module control list
     * @var array
     */
    protected $moduleAclList = [];

    /**
     * access controlled list
     * @var array
     */
    protected $accessControlledList = [];

    /**
     * private ctor
     * AccessControlManager constructor
     */
    private function __construct()
    {
        $this->init();
    }

    /**
     * init object
     */
    protected function init()
    {
        $this->registerVoters();
        // bypassing access check during installation
        if (isset($GLOBALS['installing']) && $GLOBALS['installing'] === true) {
            $this->isAdminWork = true;
        }
    }

    /**
     * Singleton impl
     * @return AccessControlManager
     */
    public static function instance()
    {
        if (empty(self::$instance)) {
            self::$instance = new AccessControlManager();
        }

        return self::$instance;
    }

    /**
     * registers available voters
     */
    protected function registerVoters()
    {
        // MODULES_KEY and DASHLETS_KEY are shared same voter
        $this->registerVoter(self::MODULES_KEY, SugarVoter::class);
        $this->registerVoter(self::RECORDS_KEY, SugarRecordVoter::class);
        $this->registerVoter(self::FIELDS_KEY, SugarFieldVoter::class);
    }

    /**
     * Register a new Voter on the stack
     * @param string $identifier Voter identifier
     * @param string $class Classname
     */
    protected function registerVoter(string $identifier, string $class)
    {
        $this->voters[$identifier] = new $class();
    }

    /**
     * Return list of registered Voters
     * @return array
     */
    protected function getRegisteredVoter(string $key)
    {
        if ($key != self::DASHLETS_KEY && !isset($this->voters[$key])) {
            throw new \Exception("wrong section key is provided" . $key);
        }
        switch ($key) {
            case self::MODULES_KEY:
            case self::DASHLETS_KEY:
                return $this->voters[self::MODULES_KEY];
            default:
                return $this->voters[$key];
        }
    }

    /**
     *
     * check if allowed to access protected resource
     *
     * @param mixed  $subject The subject to secure, could be subject identifier, such modules, fields
     * @param array $attributes list of attributes, such as edit, view, etc
     *
     */
    protected function allowAccess(string $key, string $subject, ?string $value = null) : bool
    {
        if ($this->isAdminWork || $this->allowAdminAccess()) {
            return true;
        }
        return $this->getRegisteredVoter($key)->vote($key, $subject, $value);
    }

    /**
     * check allow module access
     *
     * @param string $module module name
     *
     * @return bool
     */
    public function allowModuleAccess(?string $module) : bool
    {
        if (empty($module)) {
            return true;
        }

        // check if it is in admin workflow
        if ($this->isAdminWork) {
            return true;
        }

        // check if it is subjected to access control
        if (!$this->isAccessControlled(self::MODULES_KEY, $module)) {
            $this->moduleAclList[$module] = true;
            return true;
        }

        // check memory cache
        if (isset($this->moduleAclList[$module])) {
            return $this->moduleAclList[$module];
        }

        $allowAccess = $this->allowAccess(self::MODULES_KEY, $module);
        $this->moduleAclList[$module] = $allowAccess;
        return $allowAccess;
    }

    /**
     * check allow dashlet access
     *
     * @param string $label dashlet name
     * @return bool
     */
    public function allowDashletAccess(?string $label) : bool
    {
        if (empty($label)) {
            return true;
        }
        return $this->allowAccess(self::DASHLETS_KEY, $label);
    }

    /**
     * check allow record access
     * @param null|string $module module name
     * @param null|string $id id for the object
     * @return bool
     */
    public function allowRecordAccess(?string $module, ?string $id) : bool
    {
        if (empty($module) || empty($id)) {
            return true;
        }

        if (!$this->isAccessControlled(self::RECORDS_KEY, $module)) {
            return true;
        }

        // regular workflow, we need to check record access
        if (!$this->isAdminWork) {
            $old = $this->allowAdminOverride;
            $this->allowAdminOverride = false;
            $allowed = $this->allowAccess(self::RECORDS_KEY, $module, $id);
            $this->allowAdminOverride = $old;
            return $allowed;
        }

        return $this->allowAccess(self::RECORDS_KEY, $module, $id);
    }

    /**
     * check allow module field access
     *
     * @param string $module module name
     * @param string $field field name
     * @param array $attributes
     * @return bool
     */
    public function allowFieldAccess(?string $module, ?string $field)
    {
        if (empty($module) || empty($field)) {
            return true;
        }

        if (!$this->isAccessControlled(self::FIELDS_KEY, $module)) {
            return true;
        }

        return $this->allowAccess(self::FIELDS_KEY, $module, $field);
    }

    /**
     * allow admin override access control
     * @param bool $override
     */
    public function allowAdminOverride(bool $override)
    {
        $this->allowAdminOverride = $override;
    }

    /**
     * allow admin access
     * @return bool
     */
    protected function allowAdminAccess() : bool
    {
        global $current_user;
        // admin override
        if ($this->allowAdminOverride && !empty($current_user) && is_admin($current_user)) {
            return true;
        }
        return false;
    }

    /**
     * set is isAdminWork flag, we need to disable access control for admin work
     * @param bool $adminWork
     * @param bool $forceChange Forces update even if current user is not admin
     * @return $this
     */
    public function setAdminWork(bool $adminWork, bool $forceChange = false)
    {
        global $current_user;

        // Admin override, will only change the flag if user is an admin
        // unless the $forceChange flag is true, in which case it will force
        // the flag to update no matter what. This is used by the BPM engine.
        if ($forceChange || (!empty($current_user) && is_admin($current_user))) {
            $this->isAdminWork = $adminWork;
        }
        return $this;
    }

    /**
     * Gets the current state of the `isAdminWork` flag.
     * @return bool
     */
    public function getAdminWork() : bool
    {
        return $this->isAdminWork;
    }

    /**
     * check if the module is subjected to access control
     *
     * @param string $key
     * @param string $module
     * @return bool
     */
    protected function isAccessControlled(string $key, string $module) : bool
    {
        if (!isset($this->accessControlledList[$key])) {
            $this->accessControlledList[$key] = $this->getAccessControlledList($key);
        }
        return isset($this->accessControlledList[$key][$module]);
    }

    /**
     * get access controlled list
     * @param $key
     * @return array
     */
    protected function getAccessControlledList(string $key) : array
    {
        return AccessConfigurator::instance()->getAccessControlledList($key);
    }

    /**
     * get inaccessible records for the given $module
     * @param null|string $module
     * @return array|mixed
     */
    public function getNotAccessibleRecords(?string $module)
    {
        global $current_user;
        if (empty($current_user) || empty($module)) {
            return [];
        }
        $userLicenseTypes = SubscriptionManager::instance()->getUserSubscriptions($current_user);
        $inaccessibleList = AccessConfigurator::instance()->getNotAccessibleRecordListByLicenseTypes($userLicenseTypes);
        if (isset($inaccessibleList[$module])) {
            return $inaccessibleList[$module];
        }
        return [];
    }
}
//END REQUIRED CODE DO NOT MODIFY
