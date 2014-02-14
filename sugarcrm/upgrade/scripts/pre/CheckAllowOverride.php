<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/**
 * Check that AllowOverride is properly configured
 */
class SugarUpgradeCheckAllowOverride extends UpgradeScript
{
    public $order = 200;
    public $version = '7.2.0';

    public function run()
    {
        if(version_compare($this->from_version, '7.0', '>=')) {
            // no need to run this on 7, if AllowOverride doesn't work 7 wouldn't work too
            return;
        }

        if(!empty($_SERVER["SERVER_SOFTWARE"]) && strpos($_SERVER["SERVER_SOFTWARE"],'Microsoft-IIS') !== false) {
            // can't do it for IIS
            return;
        }

        $this->log("Testing .htaccess redirects");
        if(file_exists(".htaccess")) {
            $old_htaccess = file_get_contents(".htaccess");
        }
        $basePath = parse_url($this->upgrader->config['site_url'], PHP_URL_PATH);
        if(empty($basePath)) $basePath = '/';
        $htaccess_test = <<<EOT

# Upgrader test addition
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase {$basePath}
    RewriteRule ^itest.txt$ install_test.txt [N,QSA]
</IfModule>
EOT;
        if(!empty($old_htaccess)) {
            $htaccess_test = $old_htaccess.$htaccess_test;
        }
        file_put_contents(".htaccess", $htaccess_test);
        file_put_contents("install_test.txt", "SUCCESS");
        $res = file_get_contents($this->upgrader->config['site_url']."/itest.txt");
        unlink("install_test.txt");
        if(!empty($old_htaccess)) {
            file_put_contents(".htaccess", $old_htaccess);
        } else {
            unlink(".htaccess");
        }
        if($res != "SUCCESS") {
            $this->error("Could not verify .htaccess is working: $res");
        }
    }
}
