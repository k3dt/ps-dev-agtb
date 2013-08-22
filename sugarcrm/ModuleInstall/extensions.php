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


    $extensions = array(
        "actionviewmap" =>   array("section" => "action_view_map","extdir" => "ActionViewMap",  "file" => 'action_view_map.ext.php'),
        "actionfilemap" =>   array("section" => "action_file_map","extdir" => "ActionFileMap",  "file" => 'action_file_map.ext.php'),
        "actionremap" =>     array("section" => "action_remap",   "extdir" => "ActionReMap",    "file" => 'action_remap.ext.php'),
    	"administration" =>  array("section" => "administration", "extdir" => "Administration", "file" => 'administration.ext.php', "module" => "Administration"),
    //BEGIN SUGARCRM flav=pro ONLY
    	"dependencies" =>    array("section" => "dependencies",   "extdir" => "Dependencies",   "file" => 'deps.ext.php'),
    //END SUGARCRM flav=pro ONLY
    	"entrypoints" =>     array("section" => "entrypoints",	  "extdir" => "EntryPointRegistry",	"file" => 'entry_point_registry.ext.php', "module" => "application"),
    	"exts"         =>    array("section" => "extensions",	  "extdir" => "Extensions",		"file" => 'extensions.ext.php', "module" => "application"),
    	"file_access" =>     array("section" => "file_access",    "extdir" => "FileAccessControlMap", "file" => 'file_access_control_map.ext.php'),
    	"languages" =>       array("section" => "language",	      "extdir" => "Language",    	"file" => '' /* custom rebuild */),
    	"layoutdefs" =>      array("section" => "layoutdefs", 	  "extdir" => "Layoutdefs",     "file" => 'layoutdefs.ext.php'),
        "links" =>           array("section" => "linkdefs",       "extdir" => "GlobalLinks",    "file" => 'links.ext.php', "module" => "application"),
    	"logichooks" =>      array("section" => "hookdefs", 	  "extdir" => "LogicHooks",     "file" => 'logichooks.ext.php'),
        "menus" =>           array("section" => "menu",    	      "extdir" => "Menus",          "file" => "menu.ext.php"),
        "modules" =>         array("section" => "beans", 	      "extdir" => "Include", 	    "file" => 'modules.ext.php', "module" => "application"),
        "schedulers" =>      array("section" => "scheduledefs",	  "extdir" => "ScheduledTasks", "file" => 'scheduledtasks.ext.php', "module" => "Schedulers"),
        "userpage" =>        array("section" => "user_page",      "extdir" => "UserPage",       "file" => 'userpage.ext.php', "module" => "Users"),
        "utils" =>           array("section" => "utils",          "extdir" => "Utils",          "file" => 'custom_utils.ext.php', "module" => "application"),
    	"vardefs" =>         array("section" => "vardefs",	      "extdir" => "Vardefs",    	"file" => 'vardefs.ext.php'),
        "jsgroupings" =>     array("section" => "jsgroups",	      "extdir" => "JSGroupings",    "file" => 'jsgroups.ext.php'),
        //BEGIN SUGARCRM flav=pro ONLY
        "wireless_modules" => array("section" => "wireless_modules","extdir" => "WirelessModuleRegistry", "file" => 'wireless_module_registry.ext.php'),
        "wireless_subpanels" => array("section" => "wireless_subpanels", "extdir" => "WirelessLayoutdefs",     "file" => 'wireless.subpaneldefs.ext.php'),
    //END SUGARCRM flav=pro ONLY
        'tabledictionary' => array("section" => '', "extdir" => "TableDictionary", "file" => "tabledictionary.ext.php", "module" => "application"),

        // sidecar subpanel layouts
        "sidecarsubpanelbaselayout" => array("section"=>'sidecarsubpanelbaselayout', 'extdir' => 'clients/base/layouts/subpanels', 'file' => 'subpanels.ext.php'),

        //BEGIN SUGARCRM flav=ent ONLY
        "sidecarsubpanelportallayout" => array("section"=>'sidecarsubpanelportallayout', 'extdir' => 'clients/portal/layouts/subpanels', 'file' => 'subpanels.ext.php'),
        //END SUGARCRM flav=ent ONLY

        //BEGIN SUGARCRM flav=pro ONLY
        "sidecarsubpanelmobilelayout" => array("section"=>'sidecarsubpanelmobilelayout', 'extdir' => 'clients/mobile/layouts/subpanels', 'file' => 'subpanels.ext.php'),

        "sidecarmenubaseheader" => array("section" => "sidecarmenubaseheader", "extdir" => "clients/base/menus/header", "file" => "header.ext.php"),

        //BEGIN SUGARCRM flav=ent ONLY
        "sidecarmenuportalheader" => array("section" => "sidecarmenuportalheader", "extdir" => "clients/portal/menus/header", "file" => "header.ext.php"),
        //END SUGARCRM flav=ent ONLY

        //BEGIN SUGARCRM flav=pro ONLY
        "sidecarmenumobileheader" => array("section" => "sidecarmenumobileheader", "extdir" => "clients/mobile/menus/header", "file" => "header.ext.php"),
        //END SUGARCRM flav=pro ONLY
);
if(SugarAutoLoader::existing("custom/application/Ext/Extensions/extensions.ext.php")) {
    include("custom/application/Ext/Extensions/extensions.ext.php");
}


