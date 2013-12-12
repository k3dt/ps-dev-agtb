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

/**
 * This class is used to enforce ACLs on modules that are restricted to admins only.
 */
class SugarACLParentModule extends SugarACLStatic
{
    protected $parentModule = '';

    public function __construct($aclOptions)
    {
        if (is_array($aclOptions) && !empty($aclOptions['parentModule'])) {
            $this->parentModule = $aclOptions['parentModule'];
        }
    }

    /**
     * Only allow access to users with the user admin setting
     * @param string $module
     * @param string $view
     * @param array $context
     * @return bool|void
     */
    public function checkAccess($module, $view, $context)
    {
        //Can't check fields since we are not mapping to the same module
        if($view == "field") {
            return true;
        }
        if (!empty($this->parentModule)) {
            //Don't pass the context since the bean won't match the module.
            //We also can't check owner at this level since we don't have the bean so owner_override must be true
            return SugarACL::checkAccess($this->parentModule, $view, array('owner_override' => true));
        }
        return true;
    }

}
