<?php
    $extensions = array(
        "actionviewmap" =>   array("section" => "action_view_map","extdir" => "ActionViewMap",  "file" => 'action_view_map.ext.php'),
        "actionfilemap" =>   array("section" => "action_file_map","extdir" => "ActionFileMap",  "file" => 'action_file_map.ext.php'),
        "actionremap" =>     array("section" => "action_remap",   "extdir" => "ActionReMap",    "file" => 'action_remap.ext.php'),
    	"administration" =>  array("section" => "administration", "extdir" => "Administration", "file" => 'administration.ext.php', "module" => "Administration"),
    //BEGIN SUGARCRM flav=pro ONLY
    	"dependencies" =>    array("section" => "dependencies",   "extdir" => "Dependencies",   "file" => 'deps.ext.php'),
    //END SUGARCRM flav=pro ONLY
    	"entrypoints" =>     array("section" => "entrypoints",	  "extdir" => "EntryPointRegistry",	"file" => 'entry_point_registry.ext.php', "module" => "application"),
    	"extensions" =>      array("section" => "extensions",	  "extdir" => "Extensions",		"file" => 'extensions.ext.php', "module" => "application"),
    	"file_access" =>     array("section" => "file_access",    "extdir" => "FileAccessControlMap", "file" => 'file_access_control_map.ext.php'),
    	"languages" =>       array("section" => "language",	      "extdir" => "Languages",    	"file" => '' /* custom rebuild */),
    	"layoutdefs" =>      array("section" => "layoutdefs", 	  "extdir" => "Layoutdefs",     "file" => 'layoutdefs.ext.php'),
        "links" =>           array("section" => "linkdefs",       "extdir" => "GlobalLinks",    "file" => 'links.ext.php', "module" => "application"),
    	"logichooks" =>      array("section" => "hookdefs", 	  "extdir" => "LogicHooks",     "file" => 'logichooks.ext.php'),
        "menus" =>           array("section" => "menu",    	      "extdir" => "Menus",          "file" => "menu.ext.php"),
        "modules" =>         array("section" => "beans", 	      "extdir" => "Include", 	    "file" => 'modules.ext.php', "module" => "application"),
        "schedulers" =>      array("section" => "scheduledefs",	  "extdir" => "ScheduledTasks", "file" => 'scheduledtasks.ext.php', "module" => "Schedulers"),
        "userpage" =>        array("section" => "user_page",      "extdir" => "UserPage",       "file" => 'userpage.ext.php', "module" => "Users"),
        "utils" =>           array("section" => "utils",          "extdir" => "Utils",          "file" => 'custom_utils.ext.php', "module" => "application"),
    	"vardefs" =>         array("section" => "vardefs",	      "extdir" => "Vardefs",    	"file" => 'vardefs.ext.php'),
        "wireless_modules" => array("section" => "wireless_modules","extdir" => "WirelessModuleRegistry", "file" => 'wireless_module_registry.ext.php'),
);
if(file_exists("custom/application/Ext/Extensions/extensions.ext.php")) {
    include("custom/application/Ext/Extensions/extensions.ext.php");
}


