<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
class SugarView
{
    /**
     * This array is meant to hold an objects/data that we would like to pass between
     * the controller and the view.  The bean will automatically be set for us, but this
     * is meant to hold anything else.
     */
    var $view_object_map = array();
    /**
     * The name of the current module.
     */
	var $module = '';
    /**
     * The name of the current action.
     */
    var $action = '';
    /**
     */
    var $bean = null;
    /**
     * Sugar_Smarty. This is useful if you have a view and a subview you can
     * share the same smarty object.
     */
    var $ss = null;
    /**
     * Any errors that occured this can either be set by the view or the controller or the model
     */
    var $errors = array();
    /**
     * Options for what UI elements to hide/show/
     */
    var $options = array('show_header' => true, 'show_title' => true, 'show_subpanels' => false, 'show_search' => true, 'show_footer' => true, 'show_javascript' => true, 'view_print' => false,);
    var $type = null;
    var $responseTime;
    var $fileResources;
    
    /**
     * Constructor which will peform the setup.
     */
    public function SugarView(
        $bean = null, 
        $view_object_map = array()
        )
    {
    }

    public function init(
        $bean = null, 
        $view_object_map = array()
        )
    {
        $this->bean = &$bean;
        $this->view_object_map = $view_object_map;
        $this->action = $GLOBALS['action'];
        $this->module = $GLOBALS['module'];
        $this->_initSmarty();
    }
    
    protected function _initSmarty()
    {
        $this->ss = new Sugar_Smarty();
        $this->ss->assign('MOD', $GLOBALS['mod_strings']);
        $this->ss->assign('APP', $GLOBALS['app_strings']);
    }

    /**
     * This method will be called from the controller and is not meant to be overridden.
     */
    public function process()
    {
        LogicHook::initialize();
        $this->_checkModule();     
        
        //trackView has to be here in order to track for breadcrumbs
        $this->_trackView();
        
        if ($this->_getOption('show_header')) {
            $this->displayHeader();
        } else {
            $this->renderJavascript();	
        }
        
        $this->_buildModuleList();
        $this->preDisplay();
        $this->displayErrors();
        $this->display();
        $GLOBALS['logic_hook']->call_custom_logic('', 'after_ui_frame');
        if ($this->_getOption('show_subpanels')) $this->_displaySubPanels();
        if ($this->action === 'Login') {
            //this is needed for a faster loading login page ie won't render unless the tables are closed
            ob_flush();      	        	          
        }
        if ($this->_getOption('show_footer')) $this->displayFooter();
        $GLOBALS['logic_hook']->call_custom_logic('', 'after_ui_footer');
        //Do not track if there is no module or if module is not a String
        $this->_track();
    }

    /**
     * This method will display the errors on the page.
     */
    public function displayErrors()
    {
        foreach($this->errors as $error) {
            echo '<span class="error">' . $error . '</span><br>';
        }
    }

    /**
     * [OVERRIDE] - This method is meant to overidden in a subclass. The purpose of this method is
     * to allow a view to do some preprocessing before the display method is called. This becomes
     * useful when you have a view defined at the application level and then within a module
     * have a sub-view that extends from this application level view.  The application level
     * view can do the setup in preDisplay() that is common to itself and any subviews
     * and then the subview can just override display(). If it so desires, can also override
     * preDisplay().
     */
    public function preDisplay()
    {
    }

    /**
     * [OVERRIDE] - This method is meant to overidden in a subclass. This method
     * will handle the actual display logic of the view.
     */
    public function display()
    {
    }


    /**
     * trackView
     */
    protected function _trackView() 
    {
    	$action = strtolower($this->action);
    	//Skip save, tracked in SugarBean instead
    	if($action == 'save') {
    	   return;
    	}

       
        $trackerManager = TrackerManager::getInstance();
        $timeStamp = gmdate($GLOBALS['timedate']->get_db_date_time_format());
        if($monitor = $trackerManager->getMonitor('tracker')){ 
	        //BEGIN SUGARCRM flav=pro ONLY
	        $monitor->setValue('team_id', $GLOBALS['current_user']->getPrivateTeamID());
	        //END SUGARCRM flav=pro ONLY
	        $monitor->setValue('action', $action);
	        $monitor->setValue('user_id', $GLOBALS['current_user']->id);
	        $monitor->setValue('module_name', $this->module);
	        $monitor->setValue('date_modified', $timeStamp);
	        $monitor->setValue('visible', (($monitor->action == 'detailview') || ($monitor->action == 'editview')
	        								//BEGIN SUGARCRM flav=pro ONLY
	        								|| ($monitor->action == 'wirelessdetail') || ($monitor->action == 'wirelessedit')
	        								//END SUGARCRM flav=pro ONLY
	        								) ? 1 : 0);
	
	        if (!empty($this->bean->id)) {
	            $monitor->setValue('item_id', $this->bean->id);
	            $monitor->setValue('item_summary', $this->bean->get_summary_text());
	        }
	
	        //If visible is true, but there is no bean, do not track (invalid/unauthorized reference)
	        //Also, do not track save actions where there is no bean id
	        if($monitor->visible && empty($this->bean->id)) {
	           $trackerManager->unsetMonitor($monitor);
	           return;
	        }
	        $trackerManager->saveMonitor($monitor, true, true);
		}
    }


    /**
     * Displays the header on section of the page; basically everything before the content
     */
    public function displayHeader()
    {
        global $theme;
        global $max_tabs;
        global $app_strings;
        global $current_user;
        global $sugar_config;
        global $app_list_strings;
        global $mod_strings;
        global $current_language;
        
        $GLOBALS['app']->headerDisplayed = true;
        
        $themeObject = SugarThemeRegistry::current();
        $theme = $themeObject->__toString();
        
        $ss = new Sugar_Smarty();
        $ss->assign("APP", $app_strings);
        $ss->assign("THEME", $theme);
        $ss->assign("THEME_IE6COMPAT", $themeObject->ie6compat ? 'true':'false');
        $ss->assign("MODULE_NAME", $this->module);
        
        // get browser title
        $ss->assign("SYSTEM_NAME", $this->getBrowserTitle());
        
        // get css
        $css = $themeObject->getCSS();
        if ($this->_getOption('view_print')) {
            $css .= '<link rel="stylesheet" type="text/css" href="'.$themeObject->getCSSURL('print.css').'" media="all" />';
        }
        $ss->assign("SUGAR_CSS",$css);
        
        // get javascript
        ob_start();
        $this->renderJavascript();

        $ss->assign("SUGAR_JS",ob_get_contents().$themeObject->getJS());
        ob_end_clean();
        
        // get favicon
        if(isset($GLOBALS['sugar_config']['default_module_favicon'])) 
            $module_favicon = $GLOBALS['sugar_config']['default_module_favicon'];
        else
            $module_favicon = false;
        
        $favicon = '';
        if ( $module_favicon ) 
			//BEGIN SUGARCRM flav=sales || flav=pro ONLY
            $favicon = $themeObject->getImageURL($this->module.'_favico.png',false);
			//END SUGARCRM flav=sales || flav=pro ONLY
			//BEGIN SUGARCRM flav=com ONLY
            $favicon = $themeObject->getImageURL($this->module.'.gif',false);
			//END SUGARCRM flav=com ONLY
        if ( !sugar_is_file($favicon) || !$module_favicon )
            $favicon = $themeObject->getImageURL('sugar_icon.ico',false);
        $ss->assign('FAVICON_URL',getJSPath($favicon));
        
        // build the shortcut menu
        $shortcut_menu = array();
        foreach ( $this->getMenu() as $key => $menu_item )
            $shortcut_menu[$key] = array(
                "URL"         => $menu_item[0],
                "LABEL"       => $menu_item[1],
                "MODULE_NAME" => $menu_item[2],
                "IMAGE"       => $themeObject
                    ->getImage($menu_item[2],"alt='".$menu_item[1]."'  border='0' align='absmiddle'"),
                );
        $ss->assign("SHORTCUT_MENU",$shortcut_menu);
        
        // handle rtl text direction
        if(isset($_REQUEST['RTL']) && $_REQUEST['RTL'] == 'RTL'){
            $_SESSION['RTL'] = true;
        }
        if(isset($_REQUEST['LTR']) && $_REQUEST['LTR'] == 'LTR'){
            unset($_SESSION['RTL']);
        }
        if(isset($_SESSION['RTL']) && $_SESSION['RTL']){
            $ss->assign("DIR", 'dir="RTL"');
        }
        
        // handle resizing of the company logo correctly on the fly
        $companyLogoURL = $themeObject->getImageURL('company_logo.png');
		$companyLogoURL_arr = explode('?', $companyLogoURL);
		$companyLogoURL = $companyLogoURL_arr[0];		
        
        $company_logo_attributes = sugar_cache_retrieve('company_logo_attributes');
        if(!empty($company_logo_attributes)) {
            $ss->assign("COMPANY_LOGO_MD5", $company_logo_attributes[0]);
            $ss->assign("COMPANY_LOGO_WIDTH", $company_logo_attributes[1]);
            $ss->assign("COMPANY_LOGO_HEIGHT", $company_logo_attributes[2]);
        }
        else {
            // Always need to md5 the file
            $ss->assign("COMPANY_LOGO_MD5", md5_file($companyLogoURL));

            list($width,$height) = getimagesize($companyLogoURL);
            if ( $width > 212 || $height > 40 ) {
                $resizePctWidth  = ($width - 212)/212;
                $resizePctHeight = ($height - 40)/40;
                if ( $resizePctWidth > $resizePctHeight )
                    $resizeAmount = $width / 212;
                else
                    $resizeAmount = $height / 40;
                $ss->assign("COMPANY_LOGO_WIDTH", round($width * (1/$resizeAmount)));
                $ss->assign("COMPANY_LOGO_HEIGHT", round($height * (1/$resizeAmount)));
            }
            else {
                $ss->assign("COMPANY_LOGO_WIDTH", $width);
                $ss->assign("COMPANY_LOGO_HEIGHT", $height);
            }

            // Let's cache the results
            sugar_cache_put('company_logo_attributes',
                            array(
                                $ss->get_template_vars("COMPANY_LOGO_MD5"),
                                $ss->get_template_vars("COMPANY_LOGO_WIDTH"),
                                $ss->get_template_vars("COMPANY_LOGO_HEIGHT")
                                )
            );
        }
        $ss->assign("COMPANY_LOGO_URL",getJSPath($companyLogoURL)."&logo_md5=".$ss->get_template_vars("COMPANY_LOGO_MD5"));
        
        // get the global links
        $gcls = array();
        $global_control_links = array();
        require("include/globalControlLinks.php");
        
        foreach($global_control_links as $key => $value) {
            if ($key == 'users')  {   //represents logout link.
                $ss->assign("LOGOUT_LINK", $value['linkinfo'][key($value['linkinfo'])]);  
                $ss->assign("LOGOUT_LABEL", key($value['linkinfo']));//key value for first element.
                continue;
            }
            
            foreach ($value as $linkattribute => $attributevalue) {
                // get the main link info
                if ( $linkattribute == 'linkinfo' ) {
                    $gcls[$key] = array(
                        "LABEL" => key($attributevalue),
                        "URL"   => current($attributevalue),
                        "SUBMENU" => array(),
                        );
                   if(substr($gcls[$key]["URL"], 0, 11) == "javascript:") {
                       $gcls[$key]["ONCLICK"] = substr($gcls[$key]["URL"],11);
                       $gcls[$key]["URL"] = "#";
                   }
                }
                // and now the sublinks
                if ( $linkattribute == 'submenu' && is_array($attributevalue) ) {
                    foreach ($attributevalue as $submenulinkkey => $submenulinkinfo)
                        $gcls[$key]['SUBMENU'][$submenulinkkey] = array(
                            "LABEL" => key($submenulinkinfo),
                            "URL"   => current($submenulinkinfo),
                        );
                       if(substr($gcls[$key]['SUBMENU'][$submenulinkkey]["URL"], 0, 11) == "javascript:") {
                           $gcls[$key]['SUBMENU'][$submenulinkkey]["ONCLICK"] = substr($gcls[$key]['SUBMENU'][$submenulinkkey]["URL"],11);
                           $gcls[$key]['SUBMENU'][$submenulinkkey]["URL"] = "#";
                       }
                }
            }
        }
        $ss->assign("GCLS",$gcls);
        
        $ss->assign("SEARCH", isset($_REQUEST['query_string']) ? $_REQUEST['query_string'] : '');
        
        if ($this->action == "EditView" || $this->action == "Login") 
            $ss->assign("ONLOAD", 'onload="set_focus()"');

        $ss->assign("AUTHENTICATED",isset($_SESSION["authenticated_user_id"]));
        
        // get other things needed for page style popup
        if (isset($_SESSION["authenticated_user_id"])) {
            // get the current user name and id
            $ss->assign("CURRENT_USER", $current_user->full_name == '' || !showFullName()
                ? $current_user->user_name : $current_user->full_name );
            $ss->assign("CURRENT_USER_ID", $current_user->id);
            
            // get the last viewed records
            $tracker = new Tracker();
            $history = $tracker->get_recently_viewed($current_user->id);
            foreach ( $history as $key => $row ) {
                $history[$key]['item_summary_short'] = getTrackerSubstring($row['item_summary']);
                $history[$key]['image'] = SugarThemeRegistry::current()
                    ->getImage($row['module_name'],'border="0" align="absmiddle" alt="'.$row['item_summary'].'"');
            }
            $ss->assign("recentRecords",$history);
        }
        
		$bakModStrings = $mod_strings;
        if (isset($_SESSION["authenticated_user_id"]) ) {
            // get the module list
            $moduleTopMenu = array();
            
            $max_tabs = $current_user->getPreference('max_tabs');
            // Attempt to correct if max tabs count is waaay too high.
            if ( !isset($max_tabs) || $max_tabs <= 0 || $max_tabs > 10 ) {
                $max_tabs = $GLOBALS['sugar_config']['default_max_tabs'];
                $current_user->setPreference('max_tabs', $max_tabs, 0, 'global');
            }
            
            $moduleTab = $this->_getModuleTab();
            $ss->assign('MODULE_TAB',$moduleTab);
            

            // See if they are using grouped tabs or not (removed in 6.0, returned in 6.1)
            $user_navigation_paradigm = $current_user->getPreference('navigation_paradigm');
            if ( !isset($user_navigation_paradigm) ) {
                $user_navigation_paradigm = $GLOBALS['sugar_config']['default_navigation_paradigm'];
            }


            // Get the full module list for later use
            foreach ( query_module_access_list($current_user) as $module ) {
                // Bug 25948 - Check for the module being in the moduleList
                if ( isset($app_list_strings['moduleList'][$module]) ) {
                    $fullModuleList[$module] = $app_list_strings['moduleList'][$module];
                }
            }

            //BEGIN SUGARCRM flav=sales ONLY
			$ss_admin_whitelist = getSugarSalesAdminWhiteList();
            if(is_admin($GLOBALS['current_user'])){
            	foreach($fullModuleList as $mod_key => $ignore){
            		if(!in_array($mod_key, $ss_admin_whitelist['modules'])){
            			unset($fullModuleList[$mod_key]);
            		}
            	}
            }
            //END SUGARCRM flav=sales ONLY

            if(!should_hide_iframes()) {
                $iFrame = new iFrame();
                $frames = $iFrame->lookup_frames('tab');
                foreach($frames as $key => $values){
                        $fullModuleList[$key] = $values;                    
                }
            } 
            elseif (isset($fullModuleList['iFrames'])) {
                unset($fullModuleList['iFrames']);
            }

            if ( $user_navigation_paradigm == 'gm' && isset($themeObject->group_tabs) && $themeObject->group_tabs) {
                // We are using grouped tabs
                require_once('include/GroupedTabs/GroupedTabStructure.php');
                $groupedTabsClass = new GroupedTabStructure();               
                $modules = query_module_access_list($current_user);
                //handle with submoremodules
                $max_tabs = $current_user->getPreference('max_subtabs');
                // If the max_tabs isn't set incorrectly, set it within the range, to the default max sub tabs size
                if ( !isset($max_tabs) || $max_tabs <= 0 || $max_tabs > 10){
                    // We have a default value. Use it
                    if(isset($GLOBALS['sugar_config']['default_max_subtabs'])){
                        // As of 6.1, we shouldn't have a max subtabs higher than 10.
                        // If it's larger, bring it down to the max and save it in the config override
                        if($GLOBALS['sugar_config']['default_max_subtabs'] > 10){
                            require_once('modules/Configurator/Configurator.php');
                            $configurator = new Configurator();
                            $configurator->config['default_max_subtabs'] = '10';
                            $configurator->handleOverride();
                            $configurator->clearCache();
                        }
                        $max_tabs = $GLOBALS['sugar_config']['default_max_subtabs'];
                    }
                    else{
                        $max_tabs = 8;
                    }
                }
                
				$subMoreModules = false;
				$groupTabs = $groupedTabsClass->get_tab_structure(get_val_array($modules));
                // We need to put this here, so the "All" group is valid for the user's preference.
                $groupTabs[$app_strings['LBL_TABGROUP_ALL']]['modules'] = $fullModuleList;


                // Setup the default group tab.
                $allGroup = $app_strings['LBL_TABGROUP_ALL'];
                $ss->assign('currentGroupTab',$allGroup);
                $currentGroupTab = $allGroup;
                $usersGroup = $current_user->getPreference('theme_current_group');
                // Figure out which tab they currently have selected (stored as a user preference)
                if ( !empty($usersGroup) && isset($groupTabs[$usersGroup]) ) {
                    $currentGroupTab = $usersGroup;
                } else {
                    $current_user->setPreference('theme_current_group',$currentGroupTab);
                }

                $ss->assign('currentGroupTab',$currentGroupTab);
                $usingGroupTabs = true;
                
            } else {
                // Setup the default group tab.
                $ss->assign('currentGroupTab',$app_strings['LBL_TABGROUP_ALL']);

                $usingGroupTabs = false;

                $groupTabs[$app_strings['LBL_TABGROUP_ALL']]['modules'] = $fullModuleList;

            }
            

            $topTabList = array();
            
            // Now time to go through each of the tab sets and fix them up.
            foreach ( $groupTabs as $tabIdx => $tabData ) {
                $topTabs = $tabData['modules'];
                if ( ! is_array($topTabs) ) {
                    $topTabs = array();
                }
                $extraTabs = array();
                
                // Split it in to the tabs that go across the top, and the ones that are on the extra menu.
                if ( count($topTabs) > $max_tabs ) {
                    $extraTabs = array_splice($topTabs,$max_tabs);
                }
                // Make sure the current module is accessable through one of the top tabs
                if ( !isset($topTabs[$moduleTab]) ) {
                    // Nope, we need to add it.
                    // First, take it out of the extra menu, if it's there
                    if ( isset($extraTabs[$moduleTab]) ) {
                        unset($extraTabs[$moduleTab]);
                    }
                    if ( count($topTabs) >= $max_tabs - 1 ) {
                        // We already have the maximum number of tabs, so we need to shuffle the last one
                        // from the top to the first one of the extras
                        $lastElem = array_splice($topTabs,$max_tabs-1);
                        $extraTabs = $lastElem + $extraTabs;
                    }
                    $topTabs[$moduleTab] = $app_list_strings['moduleList'][$moduleTab];
                }
                
                
                /*
                // This was removed, but I like the idea, so I left the code in here in case we decide to turn it back on
                // If we are using group tabs, add all the "hidden" tabs to the end of the extra menu
                if ( $usingGroupTabs ) {
                    foreach($fullModuleList as $moduleKey => $module ) {
                        if ( !isset($topTabs[$moduleKey]) && !isset($extraTabs[$moduleKey]) ) {
                            $extraTabs[$moduleKey] = $module;
                        }
                    }
                }
                */

                // Get a unique list of the top tabs so we can build the popup menus for them
                foreach ( $topTabs as $moduleKey => $module ) {
                    $topTabList[$moduleKey] = $module;
                }
                
                $groupTabs[$tabIdx]['modules'] = $topTabs;
                $groupTabs[$tabIdx]['extra'] = $extraTabs;
            }
        }

        if ( isset($topTabList) && is_array($topTabList) ) {
            // Adding shortcuts array to menu array for displaying shortcuts associated with each module
            $shortcutTopMenu = array();
            foreach($topTabList as $module_key => $label) {
                global $mod_strings;
                $mod_strings = return_module_language($current_language, $module_key);
                foreach ( $this->getMenu($module_key) as $key => $menu_item ) {
                    $shortcutTopMenu[$module_key][$key] = array(
                        "URL"         => $menu_item[0],
                        "LABEL"       => $menu_item[1],
                        "MODULE_NAME" => $menu_item[2],
                        "IMAGE"       => $themeObject
                        ->getImage($menu_item[2],"alt='".$menu_item[1]."'  border='0' align='absmiddle'"),
                        );
                }
            }
            $ss->assign("groupTabs",$groupTabs);
            $ss->assign("shortcutTopMenu",$shortcutTopMenu);
            $ss->assign('USE_GROUP_TABS',$usingGroupTabs);

            // This is here for backwards compatibility, someday, somewhere, it will be able to be removed
            $ss->assign("moduleTopMenu",$groupTabs[$app_strings['LBL_TABGROUP_ALL']]['modules']);
            $ss->assign("moduleExtraMenu",$groupTabs[$app_strings['LBL_TABGROUP_ALL']]['extra']);

        }

		global $mod_strings;
		$mod_strings = $bakModStrings;
        //BEGIN SUGARCRM flav=sales || flav=pro ONLY
		/******************DC MENU*********************/
		if(!empty($current_user->id) && !$this->_getOption('view_print')){
			require_once('include/DashletContainer/DCFactory.php');
			$dcm = DCFactory::getContainer(null, 'DCMenu');
			$data = $dcm->getLayout();
			$dcjs = "<script src='".getJSPath('include/DashletContainer/Containers/DCMenu.js')."'></script>";
			$ss->assign('SUGAR_DCJS', $dcjs);
			$ss->assign('SUGAR_DCMENU', $data['html']);
		}
		/******************END DC MENU*********************/
        //END SUGARCRM flav=sales || flav=pro ONLY
        $headerTpl = $themeObject->getTemplate('header.tpl');
        if ( isset($GLOBALS['sugar_config']['developerMode']) && $GLOBALS['sugar_config']['developerMode'] )
            $ss->clear_compiled_tpl($headerTpl);
        $ss->display($headerTpl);
        
        $this->includeClassicFile('modules/Administration/DisplayWarnings.php');
    }
    /**
     * If the view is classic then this method will include the file and
     * setup any global variables.
     *
     * @param string $file
     */
    public function includeClassicFile(
        $file
        )
    {
        global $sugar_config, $theme, $current_user, $sugar_version, $sugar_flavor, $mod_strings, $app_strings, $app_list_strings, $action, $timezones;
        global $gridline, $request_string, $modListHeader, $dashletData, $authController, $locale, $currentModule, $import_bean_map, $image_path, $license;
        global $user_unique_key, $server_unique_key, $barChartColors, $modules_exempt_from_availability_check, $dictionary, $current_language, $beanList, $beanFiles, $sugar_build, $sugar_codename;
        global $timedate, $login_error; // cn: bug 13855 - timedate not available to classic views.
        $currentModule = $this->module;
        require_once ($file);
    }

    protected function _displayLoginJS()
    {
        global $sugar_config;
        
        if(isset($this->bean->module_dir)){
            echo "<script>var module_sugar_grp1 = '{$this->bean->module_dir}';</script>";
        }
        if(isset($_REQUEST['action'])){
            echo "<script>var action_sugar_grp1 = '{$_REQUEST['action']}';</script>";
        }
        echo '<script>jscal_today = ' . (1000*strtotime($GLOBALS['timedate']->handle_offset(gmdate($GLOBALS['timedate']->get_db_date_time_format()), $GLOBALS['timedate']->get_db_date_time_format()))) . '; if(typeof app_strings == "undefined") app_strings = new Array();</script>';
        if (!is_file("include/javascript/sugar_grp1.js")) {
        	$_REQUEST['root_directory'] = ".";
        	require_once("jssource/minify_utils.php");
        	ConcatenateFiles(".");
        }
        echo '<script type="text/javascript" src="' . getJSPath('include/javascript/sugar_grp1_yui.js') . '"></script>';
        echo '<script type="text/javascript" src="' . getJSPath('include/javascript/sugar_grp1.js') . '"></script>';
		//BEGIN SUGARCRM flav=pro ONLY
		if (!is_file("cache/Expressions/functions_cache.js")) {
            $GLOBALS['updateSilent'] = true;
			include("include/Expressions/updatecache.php");
		}
		if(inDeveloperMode())
            echo '<script type="text/javascript" src="' . getJSPath('cache/Expressions/functions_cache_debug.js') . '"></script>';
		else
            echo '<script type="text/javascript" src="' . getJSPath('cache/Expressions/functions_cache.js') . '"></script>';
		//END SUGARCRM flav=pro ONLY
        echo '<script type="text/javascript" src="' . getJSPath('jscalendar/lang/calendar-' . substr($GLOBALS['current_language'], 0, 2) . '.js') . '"></script>';
        echo <<<EOQ
		<script>
			if ( typeof(SUGAR) == 'undefined' ) {SUGAR = {}};
			if ( typeof(SUGAR.themes) == 'undefined' ) SUGAR.themes = {};
		</script>
EOQ;
        if(isset( $sugar_config['disc_client']) && $sugar_config['disc_client'])
            echo '<script type="text/javascript" src="' . getJSPath('modules/Sync/headersync.js') . '"></script>';
    }

    /**
     * Called from process(). This method will display the correct javascript.
     */
    protected function _displayJavascript()
    {
        global $locale, $sugar_config;
        
    	
        //BEGIN SUGARCRM flav=int ONLY
        //check to see if the script files need to be rebuilt, add needed variables to request array
        $_REQUEST['root_directory'] = getcwd();
        $_REQUEST['js_rebuild_concat'] = 'rebuild';
        require_once ('jssource/minify.php');
        //END SUGARCRM flav=int ONLY
        if ($this->_getOption('show_javascript')) {
            if (!$this->_getOption('show_header'))
                echo <<<EOHTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
EOHTML;
            
            echo "<script>var sugar_cache_dir = '{$GLOBALS['sugar_config']['cache_dir']}';</script>";
            echo "<script>var sugar_upload_dir = '{$GLOBALS['sugar_config']['upload_dir']}';</script>";
        
        	if(isset($this->bean->module_dir)){
                echo "<script>var module_sugar_grp1 = '{$this->bean->module_dir}';</script>";
            }
            if(isset($_REQUEST['action'])){
                echo "<script>var action_sugar_grp1 = '{$_REQUEST['action']}';</script>";
            }
            echo '<script>jscal_today = ' . (1000*strtotime($GLOBALS['timedate']->handle_offset(gmdate($GLOBALS['timedate']->get_db_date_time_format()), $GLOBALS['timedate']->get_db_date_time_format()))) . '; if(typeof app_strings == "undefined") app_strings = new Array();</script>';
	        if (!is_file("include/javascript/sugar_grp1.js") || !is_file("include/javascript/sugar_grp1_yui.js")) {
	        	$_REQUEST['root_directory'] = ".";
	        	require_once("jssource/minify_utils.php");
	        	ConcatenateFiles(".");
	        }
            echo '<script type="text/javascript" src="' . getJSPath('include/javascript/sugar_grp1_yui.js') . '"></script>';
            echo '<script type="text/javascript" src="' . getJSPath('include/javascript/sugar_grp1.js') . '"></script>';
            echo '<script type="text/javascript" src="' . getJSPath('jscalendar/lang/calendar-' . substr($GLOBALS['current_language'], 0, 2) . '.js') . '"></script>';

            // cn: bug 12274 - prepare secret guid for asynchronous calls
            if (!isset($_SESSION['asynchronous_key']) || empty($_SESSION['asynchronous_key'])) {
                $_SESSION['asynchronous_key'] = create_guid();
            }
            $image_server = (defined('TEMPLATE_URL'))?TEMPLATE_URL . '/':'';
            echo '<script type="text/javascript">var asynchronous_key = "' . $_SESSION['asynchronous_key'] . '";SUGAR.themes.image_server="' . $image_server . '";</script>'; // cn: bug 12274 - create session-stored key to defend against CSRF
            echo '<script type="text/javascript"> var name_format = "' . $locale->getLocaleFormatMacro() . '";</script>';
            echo $GLOBALS['timedate']->get_javascript_validation();
            if (!is_file($GLOBALS['sugar_config']['cache_dir'] . 'jsLanguage/' . $GLOBALS['current_language'] . '.js')) {
                require_once ('include/language/jsLanguage.php');
                jsLanguage::createAppStringsCache($GLOBALS['current_language']);
            }
            echo '<script type="text/javascript" src="' . $GLOBALS['sugar_config']['cache_dir'] . 'jsLanguage/' . $GLOBALS['current_language'] . '.js?s=' . $GLOBALS['js_version_key'] . '&c=' . $GLOBALS['sugar_config']['js_custom_version'] . '&j=' . $GLOBALS['sugar_config']['js_lang_version'] . '"></script>';
            if (!is_file($GLOBALS['sugar_config']['cache_dir'] . 'jsLanguage/' . $this->module . '/' . $GLOBALS['current_language'] . '.js')) {
                require_once ('include/language/jsLanguage.php');
                jsLanguage::createModuleStringsCache($this->module, $GLOBALS['current_language']);
            }
            echo '<script type="text/javascript" src="' . $GLOBALS['sugar_config']['cache_dir'] . 'jsLanguage/' . $this->module . '/' . $GLOBALS['current_language'] . '.js?s=' . $GLOBALS['js_version_key'] . '&c=' . $GLOBALS['sugar_config']['js_custom_version'] . '&j=' . $GLOBALS['sugar_config']['js_lang_version'] . '"></script>';
            if(isset( $sugar_config['disc_client']) && $sugar_config['disc_client'])
                echo '<script type="text/javascript" src="' . getJSPath('modules/Sync/headersync.js') . '"></script>';
            echo '<script src="' . getJSPath('include/javascript/yui3/build/yui/yui-min.js') . '" type="text/javascript"></script>';
			//BEGIN SUGARCRM flav=pro ONLY
	        if (!is_file("cache/Expressions/functions_cache.js")) {
	        	$GLOBALS['updateSilent'] = true;
	            include("include/Expressions/updatecache.php");
			}
	        if(inDeveloperMode())
	            echo '<script type="text/javascript" src="' . getJSPath('cache/Expressions/functions_cache_debug.js') . '"></script>';
	        else
	            echo '<script type="text/javascript" src="' . getJSPath('cache/Expressions/functions_cache.js') . '"></script>';
            require_once("include/Expressions/DependencyManager.php");
            echo DependencyManager::getJSUserVariables($GLOBALS['current_user'], true);
	        //END SUGARCRM flav=pro ONLY
        }
        
        if (isset($_REQUEST['popup']) && !empty($_REQUEST['popup'])) {
            // cn: bug 12274 - add security metadata envelope for async calls in popups
            echo '<script type="text/javascript">var asynchronous_key = "' . $_SESSION['asynchronous_key'] . '";</script>'; // cn: bug 12274 - create session-stored key to defend against CSRF
        }
    }

    /**
     * Called from process(). This method will display the footer on the page.
     */
    public function displayFooter()
    {
        if (empty($this->responseTime)) {
            $this->_calculateFooterMetrics();
        }
        global $sugar_config;
        global $app_strings;
        
        //decide whether or not to show themepicker, default is to show
        $showThemePicker = true;
        if (isset($sugar_config['showThemePicker'])) {
            $showThemePicker = $sugar_config['showThemePicker'];
        }
        
        echo "<!-- crmprint -->";
        $jsalerts = new jsAlerts();
        if ( !isset($_SESSION['isMobile']) )
            echo $jsalerts->getScript();
        
        $ss = new Sugar_Smarty();
        $ss->assign("AUTHENTICATED",isset($_SESSION["authenticated_user_id"]));
        $ss->assign('MOD',return_module_language($GLOBALS['current_language'], 'Users'));
        
        if (SugarConfig::getInstance()->get('calculate_response_time', false))
            $ss->assign('STATISTICS',$this->_getStatistics());
        
        // Under the License referenced above, you are required to leave in all copyright statements in both
        // the code and end-user application.
    
		//BEGIN SUGARCRM flav=com  && dep=os ONLY

        $copyright = '&copy; 2004-2010 SugarCRM Inc. The Program is provided AS IS, without warranty.  Licensed under <a href="LICENSE.txt" target="_blank" class="copyRightLink">AGPLv3</a>.<br>This program is free software; you can redistribute it and/or modify it under the terms of the <br><a href="LICENSE.txt" target="_blank" class="copyRightLink"> GNU Affero General Public License version 3</a> as published by the Free Software Foundation, including the additional permission set forth in the source code header.<br>';

        //END SUGARCRM flav=com  && dep=os ONLY

		


        //BEGIN SUGARCRM flav=pro  && dep=od && reg=zh_cn  ONLY

        $copyright = 'é™�ä¸­å›½åœ°åŒºä½¿ç�1ￄ1�7�1�7�1�7r> &copy; 2004-2010 <a href="http://www.sugarcrm.com" target="_blank" class="copyRightLink">SugarCRM Inc.</a> ç‰ˆæ�ƒæ‰€æœ 1�7�1�7r>';

        //END SUGARCRM flav=pro  && dep=od && reg=zh_cn  ONLY

		

          //BEGIN SUGARCRM lic=sub ONLY

        $copyright = '&copy; 2004-2010 <a href="http://www.sugarcrm.com" target="_blank" class="copyRightLink">SugarCRM Inc.</a> All Rights Reserved.<br>';

          //END SUGARCRM lic=sub ONLY

        //BEGIN SUGARCRM flav=com  && dep=os ONLY

		
        // The interactive user interfaces in modified source and object code
        // versions of this program must display Appropriate Legal Notices, as
        // required under Section 5 of the GNU General Public License version
        // 3. In accordance with Section 7(b) of the GNU General Public License
        // version 3, these Appropriate Legal Notices must retain the display
        // of the "Powered by SugarCRM" logo. If the display of the logo is
        // not reasonably feasible for technical reasons, the Appropriate
        // Legal Notices must display the words "Powered by SugarCRM".
        $attribLinkImg = "<img style='margin-top: 2px' border='0' width='106' height='23' src='include/images/poweredby_sugarcrm.png' alt='Powered By SugarCRM'>\n";

        //END SUGARCRM flav=com  && dep=os ONLY

          //BEGIN SUGARCRM lic=sub ONLY
        // You are required to leave in all copyright statements in both the
        // code and end-user application as well as the the powered by image.
        // You can not change the url or the image below.
        $attribLinkImg = "<A href='http://www.sugarcrm.com' target='_blank'><img style='margin-top: 2px' border='0' width='106' height='23' src='include/images/poweredby_sugarcrm.png' alt='Powered By SugarCRM'></A>\n";

          //END SUGARCRM lic=sub ONLY
        
        // Bug 38594 - Add in Trademark wording
        $copyright .= 'SugarCRM is a trademark of SugarCRM, Inc. All other company and product names may be trademarks of the respective companies with which they are associated.<br />';

        //rrs bug: 20923 - if this image does not exist as per the license, then the proper image will be displaye regardless, so no need
		//to display an empty image here.
		if(file_exists('include/images/poweredby_sugarcrm.png')){
			$copyright .= $attribLinkImg;
		}
        // End Required Image
        $ss->assign('COPYRIGHT',$copyright);
        $ss->display(SugarThemeRegistry::current()->getTemplate('footer.tpl'));
    }

    /**
     * Called from process(). This method will display subpanels.
     */
    protected function _displaySubPanels()
    {
        if (isset($this->bean) && !empty($this->bean->id) && (file_exists('modules/' . $this->module . '/metadata/subpaneldefs.php') || file_exists('custom/modules/' . $this->module . '/metadata/subpaneldefs.php') || file_exists('custom/modules/' . $this->module . '/Ext/Layoutdefs/layoutdefs.ext.php'))) {
            $GLOBALS['focus'] = $this->bean;
            require_once ('include/SubPanel/SubPanelTiles.php');
            $subpanel = new SubPanelTiles($this->bean, $this->module);
            echo $subpanel->display();
        }
    }
    
    protected function _buildModuleList()
    {
        if (!empty($GLOBALS['current_user']) && empty($GLOBALS['modListHeader'])) 
            $GLOBALS['modListHeader'] = query_module_access_list($GLOBALS['current_user']);
    }

    /**
     * private method used in process() to determine the value of a passed in option
     *
     * @param string option - the option that we want to know the valye of
     * @param bool default - what the default value should be if we do not find the option
     *
     * @return bool - the value of the option
     */
    protected function _getOption(
        $option, 
        $default = false
        )
    {
        if (!empty($this->options) && isset($this->options['show_all'])) {
            return $this->options['show_all'];
        } elseif (!empty($this->options) && isset($this->options[$option])) {
            return $this->options[$option];
        } else return $default;
    }
    
    /**
     * track
     * Private function to track information about the view request
     */
    private function _track()
    {
        if (empty($this->responseTime)) {
            $this->_calculateFooterMetrics();
        }
        if (empty($GLOBALS['current_user']->id)) {
            return;
        }

        
        $trackerManager = TrackerManager::getInstance();
        //BEGIN SUGARCRM flav=pro ONLY
        if(!$trackerManager->isPaused())
        {
	        $timeStamp = gmdate($GLOBALS['timedate']->get_db_date_time_format());
	        //Track to tracker_perf
	        if($monitor2 = $trackerManager->getMonitor('tracker_perf')){ 
		        $monitor2->setValue('server_response_time', $this->responseTime);
		        $dbManager = &DBManagerFactory::getInstance();
		        $monitor2->db_round_trips = $dbManager->getQueryCount();
		        $monitor2->setValue('date_modified', $timeStamp);
		        $monitor2->setValue('db_round_trips', $dbManager->getQueryCount());
		        $monitor2->setValue('files_opened', $this->fileResources);
		        if (function_exists('memory_get_usage')) {
		            $monitor2->setValue('memory_usage', memory_get_usage());
		        }
			}
		    
			// Track to tracker_sessions
		    if($monitor3 = $trackerManager->getMonitor('tracker_sessions')){
		        $monitor3->setValue('date_end', $timeStamp);
		        if ( !isset($monitor3->date_start) ) $monitor3->setValue('date_start', $timeStamp);
		        $seconds = strtotime($monitor3->date_end) -strtotime($monitor3->date_start);
		        $monitor3->setValue('seconds', $seconds);
		        $monitor3->setValue('user_id', $GLOBALS['current_user']->id);
			}
        }
	    //END SUGARCRM flav=pro ONLY
	    $trackerManager->save();
		
    }

    /**
     * Checks to see if the module name passed is valid; dies if it is not
     */
    protected function _checkModule()
    {
    	if(!empty($this->module) && !file_exists('modules/'.$this->module)){
    		$error = str_replace("[module]", "$this->module", $GLOBALS['app_strings']['ERR_CANNOT_FIND_MODULE']);
        	$GLOBALS['log']->fatal($error);
        	echo $error;
        	die();
    	}
    }

    public function renderJavascript() 
    {
        if ($this->action !== 'Login')
            $this->_displayJavascript();
        else
            $this->_displayLoginJS();  	
    }    
    
    private function _calculateFooterMetrics()
    {
        $endTime = microtime(true);
        $deltaTime = $endTime - $GLOBALS['startTime'];
        $this->responseTime = number_format(round($deltaTime, 2), 2);
        // Print out the resources used in constructing the page.
        $included_files = get_included_files();
        // take all of the included files and make a list that does not allow for duplicates based on case
        // I believe the full get_include_files result set appears to have one entry for each file in real
        // case, and one entry in all lower case.
        $list_of_files_case_insensitive = array();
        foreach($included_files as $key => $name) {
            // preserve the first capitalization encountered.
            $list_of_files_case_insensitive[mb_strtolower($name) ] = $name;
        }
        $this->fileResources = sizeof($list_of_files_case_insensitive);
    }
    
    private function _getStatistics()
    {
        $endTime = microtime(true);
        $deltaTime = $endTime - $GLOBALS['startTime'];
        $response_time_string = $GLOBALS['app_strings']['LBL_SERVER_RESPONSE_TIME'] . " " . number_format(round($deltaTime, 2), 2) . " " . $GLOBALS['app_strings']['LBL_SERVER_RESPONSE_TIME_SECONDS'];
        $return = $response_time_string;
        $return .= '<br />';
        //BEGIN SUGARCRM flav=int ONLY
        // Output the DB instances only ifthere is more than one actually created(the error case)
        $checkDB = DBManagerFactory::getInstance();
        if ($checkDB->count_id > 1) {
            $return .= '<b>(Internal Only)DB Instances: ' . $checkDB->count_id . ' references:' . $checkDB->references . '</b><br />';
        }

        //END SUGARCRM flav=int ONLY
        //BEGIN SUGARCRM flav=int ONLY
        // Internally, ifthey have not turned off page rosources set them to true
        if (!isset($GLOBALS['sugar_config']['show_page_resources'])) {
            $GLOBALS['sugar_config']['show_page_resources'] = true;
        }

        //END SUGARCRM flav=int ONLY
        if (!empty($GLOBALS['sugar_config']['show_page_resources'])) {
            // Print out the resources used in constructing the page.
            $included_files = get_included_files();

            // take all of the included files and make a list that does not allow for duplicates based on case
            // I believe the full get_include_files result set appears to have one entry for each file in real
            // case, and one entry in all lower case.
            $list_of_files_case_insensitive = array();
            foreach($included_files as $key => $name) {
                // preserve the first capitalization encountered.
                $list_of_files_case_insensitive[mb_strtolower($name) ] = $name;
            }
            $return .= $GLOBALS['app_strings']['LBL_SERVER_RESPONSE_RESOURCES'] . '(' . DBManager::getQueryCount() . ',' . sizeof($list_of_files_case_insensitive) . ')<br>';
            // Display performance of the internal and external caches....
            $return .= "External cache (hits/total=ratio) local ({$GLOBALS['external_cache_request_local_hits']}/{$GLOBALS['external_cache_request_local_total']}=" . round($GLOBALS['external_cache_request_local_hits']*100/$GLOBALS['external_cache_request_local_total'], 0) . "%)";
            if ($GLOBALS['external_cache_request_external_total']) {
                // Only display out of process cache results if there was at least one attempt to retrieve from the out of process cache (this signifies that it was enabled).
                $return .= " external ({$GLOBALS['external_cache_request_external_hits']}/{$GLOBALS['external_cache_request_external_total']}=" . round($GLOBALS['external_cache_request_external_hits']*100/$GLOBALS['external_cache_request_external_total'], 0) . "%)<br />";
            }
        }
        
        return $return;
    }
    
    /**
     * Loads the module shortcuts menu
     *
     * @param  $module string optional, can specify module to retrieve menu for if not the current one
     * @return array module menu
     */
    public function getMenu(
        $module = null
        )
    {
        global $current_language, $current_user, $mod_strings, $app_strings;
        
        if ( empty($module) )
            $module = $this->module;
        
        $module_menu = sugar_cache_retrieve("{$current_user->id}_{$module}_module_menu_{$current_language}");
        if ( !is_array($module_menu) ) {
            $final_module_menu = array();
            
            if (file_exists('modules/' . $module . '/Menu.php')) {
                $GLOBALS['module_menu'] = $module_menu = array();
                require('modules/' . $module . '/Menu.php');
                $final_module_menu = array_merge($final_module_menu,$GLOBALS['module_menu'],$module_menu);
            }
            if (file_exists('custom/modules/' . $module . '/Ext/Menus/menu.ext.php')) {
                $GLOBALS['module_menu'] = $module_menu = array();
                require('custom/modules/' . $module . '/Ext/Menus/menu.ext.php');
                $final_module_menu = array_merge($final_module_menu,$GLOBALS['module_menu'],$module_menu);
            }
            if (!file_exists('modules/' . $module . '/Menu.php') 
                    && !file_exists('custom/modules/' . $module . '/Ext/Menus/menu.ext.php') 
                    && !empty($GLOBALS['mod_strings']['LNK_NEW_RECORD'])) {
                $final_module_menu[] = array("index.php?module=$module&action=EditView&return_module=$module&return_action=DetailView",
                    $GLOBALS['mod_strings']['LNK_NEW_RECORD'],"{$GLOBALS['app_strings']['LBL_CREATE_BUTTON_LABEL']}$module" ,$module );
                $final_module_menu[] = array("index.php?module=$module&action=index", $GLOBALS['mod_strings']['LNK_LIST'], 
                    $module, $module);
                if ( ($this->bean instanceOf SugarBean) && !empty($this->bean->importable) )
                    if ( !empty($mod_strings['LNK_IMPORT_'.strtoupper($module)]) )
                        $final_module_menu[] = array("index.php?module=Import&action=Step1&import_module=$module&return_module=$module&return_action=index", 
                            $mod_strings['LNK_IMPORT_'.strtoupper($module)], "Import", $module);
                    else
                        $final_module_menu[] = array("index.php?module=Import&action=Step1&import_module=$module&return_module=$module&return_action=index", 
                            $app_strings['LBL_IMPORT'], "Import", $module);
            }
            if (file_exists('custom/application/Ext/Menus/menu.ext.php')) {
                $GLOBALS['module_menu'] = $module_menu = array();
                require('custom/application/Ext/Menus/menu.ext.php');
                $final_module_menu = array_merge($final_module_menu,$GLOBALS['module_menu'],$module_menu);
            }
            $module_menu = $final_module_menu;
            sugar_cache_put("{$current_user->id}_{$module}_module_menu_{$current_language}",$module_menu);
        }
        
        return $module_menu;
	}
    
	/**
	 * Returns the module name which should be highlighted in the module menu
     */
    protected function _getModuleTab()
    {
        global $app_list_strings, $moduleTabMap;
        
        // Need to figure out what tab this module belongs to, most modules have their own tabs, but there are exceptions.
        if ( !empty($_REQUEST['module_tab']) )
            return $_REQUEST['module_tab'];
        elseif ( isset($moduleTabMap[$this->module]) )
            return $moduleTabMap[$this->module];
        // Special cases
        elseif ( $this->module == 'MergeRecords' )
            return $_REQUEST['return_module'];
        elseif ( $this->module == 'Users' && $this->action == 'SetTimezone' )
            return 'Home';
        // Default anonymous pages to be under Home
        elseif ( !isset($app_list_strings['moduleList'][$this->module]) )
            return 'Home';
        else
            return $this->module;
    }
    
   /**
    * Return the "breadcrumbs" to display at the top of the page
    *
    * @param  bool $show_help optional, true if we show the help links
    * @return HTML string containing breadcrumb title
    */
    public function getModuleTitle(
        $show_help = true
        )
    {
    	global $sugar_version, $sugar_flavor, $server_unique_key, $current_language, $action;
    	
    	$theTitle = "<div class='moduleTitle'>\n<h2>";
    	
    	$module = preg_replace("/ /","",$this->module);
        
        $params = $this->_getModuleTitleParams();
        $count = count($params);
        $index = 0;
       
        foreach($params as $parm){
            $index++;
            $theTitle .= $parm;
            if($index < $count){
                $theTitle .= "<span class='pointer'>&raquo;</span>";
            }
        }
        $theTitle .= "</h2>\n";
        
    	if ($show_help) {
            $theTitle .= "<span class='utils'>";
            if (isset($this->action) && $this->action != "EditView") {
                $printImageURL = SugarThemeRegistry::current()->getImageURL('print.gif');
                $theTitle .= <<<EOHTML
<a href="javascript:void window.open('index.php?{$GLOBALS['request_string']}','printwin','menubar=1,status=0,resizable=1,scrollbars=1,toolbar=0,location=1')" class='utilsLink'>
<img src="{$printImageURL}" alt="{$GLOBALS['app_strings']['LNK_PRINT']}"></a>
<a href="javascript:void window.open('index.php?{$GLOBALS['request_string']}','printwin','menubar=1,status=0,resizable=1,scrollbars=1,toolbar=0,location=1')" class='utilsLink'>
{$GLOBALS['app_strings']['LNK_PRINT']}
</a>
EOHTML;
            }
            $helpImageURL = SugarThemeRegistry::current()->getImageURL('help.gif');
            $theTitle .= <<<EOHTML
&nbsp;
<a href="index.php?module=Administration&action=SupportPortal&view=documentation&version={$sugar_version}&edition={$sugar_flavor}&lang={$current_language}&help_module={$module}&help_action={$this->action}&key={$server_unique_key}" class="utilsLink" target="_blank">
<img src='{$helpImageURL}' alt='{$GLOBALS['app_strings']['LNK_HELP']}'></a>
<a href="index.php?module=Administration&action=SupportPortal&view=documentation&version={$sugar_version}&edition={$sugar_flavor}&lang={$current_language}&help_module={$module}&help_action={$this->action}&key={$server_unique_key}" class="utilsLink" target="_blank">
{$GLOBALS['app_strings']['LNK_HELP']}
</a>
EOHTML;
        }
        
        $theTitle .= "</span></div>\n";
    	return $theTitle;
    }

    /**
     * Return the metadata file that will be used by this view.
     *
     * @return string File location of the metadata file.
     */
    public function getMetaDataFile(){
        
        $metadataFile = null;
 		$foundViewDefs = false;
 		$viewDef = strtolower($this->type) . 'viewdefs';
 		$coreMetaPath = 'modules/'.$this->module.'/metadata/' . $viewDef . '.php';
 		if(file_exists('custom/' .$coreMetaPath )){
 			$metadataFile = 'custom/' . $coreMetaPath;
 			$foundViewDefs = true;
 		}else{
	 		if(file_exists('custom/modules/'.$this->module.'/metadata/metafiles.php')){
				require_once('custom/modules/'.$this->module.'/metadata/metafiles.php');
				if(!empty($metafiles[$this->module][$viewDef])){
					$metadataFile = $metafiles[$this->module][$viewDef];
					$foundViewDefs = true;
				}
			}elseif(file_exists('modules/'.$this->module.'/metadata/metafiles.php')){
				require_once('modules/'.$this->module.'/metadata/metafiles.php');
				if(!empty($metafiles[$this->module][$viewDef])){
					$metadataFile = $metafiles[$this->module][$viewDef];
					$foundViewDefs = true;
				}
			}
 		}
 		
		if(!$foundViewDefs && file_exists($coreMetaPath)){
				$metadataFile = $coreMetaPath;
 		}
 		$GLOBALS['log']->debug("metadatafile=". $metadataFile);
 		
 		return $metadataFile;
    }

    
    /**
     * Returns an array composing of the breadcrumbs to use for the module title
     *
     * @return array
     */
    protected function _getModuleTitleParams($bTitle=false)
    {
    	$params = array($this->_getModuleTitleListParam($bTitle));
    	
    	if (isset($this->action)){
    	    switch ($this->action) {
    	    case 'EditView':
                if(!empty($this->bean->id)) {
                    $params[] = "<a href='index.php?module={$this->module}&action=DetailView&record={$this->bean->id}'>".$this->bean->get_summary_text()."</a>";
                    $params[] = $GLOBALS['app_strings']['LBL_EDIT_BUTTON_LABEL'];
                }
                else
                    $params[] = $GLOBALS['app_strings']['LBL_CREATE_BUTTON_LABEL'];
                break;
            case 'DetailView':
                $beanName = $this->bean->get_summary_text();
                //BEGIN SUGARCRM flav=pro ONLY
                if($this->bean->isFavoritesEnabled())
                    $beanName .= '&nbsp;' . SugarFavorites::generateStar(SugarFavorites::isUserFavorite($this->module, $this->bean->id), $this->module, $this->bean->id);
                //END SUGARCRM flav=pro ONLY
                //BEGIN SUGARCRM flav=following ONLY
                if($this->bean->canFollow()){
                    //get the following icon
                    $beanName .= '&nbsp;' . SugarFollowing::generateIcon(SugarFollowing::isUserFollowing($this->module, $this->bean->id), $this->module, $this->bean->id);
                }
                //END SUGARCRM flav=following ONLY
                $params[] = $beanName;
                break;
    		}
    	}
 		
    	return $params;
    }
    
    /**
     * Returns the portion of the array that will represent the listview in the breadcrumb
     *
     * @return string
     */
    protected function _getModuleTitleListParam($bTitle=false)
    {
    	global $current_user;
    	global $app_strings;
    	
    	if(!empty($GLOBALS['app_list_strings']['moduleList'][$this->module]))
    		$firstParam = $GLOBALS['app_list_strings']['moduleList'][$this->module];
    	else
    		$firstParam = $this->module;
    	
    	$iconPath = $this->getModuleTitleIconPath($this->module);
    	if($this->action == "ListView" || $this->action == "index") 
    	{
    	    if (!empty($iconPath) && !$bTitle) {
				return "<a href='index.php?module={$this->module}&action=index'>" 
				     . "<img src='{$iconPath}' alt='".$this->module."' title='".$this->module."' align='absmiddle'></a>" 
				     . "<span class='pointer'>&raquo;</span>".$app_strings['LBL_SEARCH'];
			} else {
				return $firstParam;
			}
    	} else 
    	{
		    if (!empty($iconPath) && !$bTitle) {
				return "<a href='index.php?module={$this->module}&action=index'>" 
				     . "<img src='{$iconPath}' alt='".$this->module."' title='".$this->module."' align='absmiddle'></a>";
			} else {
				return "<a href='index.php?module={$this->module}&action=index'>{$firstParam}</a>";
			}
    	}
    }
    
    protected function getModuleTitleIconPath($module) {
    	$iconPath = "";
    	if(is_file(SugarThemeRegistry::current()->getImageURL('icon_'.$module.'_32.png',false)))
    	{
    		$iconPath = SugarThemeRegistry::current()->getImageURL('icon_'.$module.'_32.png');
    	} else if (is_file(SugarThemeRegistry::current()->getImageURL('icon_'.ucfirst($module).'_32.png',false)))
    	{
    		$iconPath = SugarThemeRegistry::current()->getImageURL('icon_'.ucfirst($module).'_32.png');
    	}
    	return $iconPath;
    }
    
    /**
     * Returns the string which will be shown in the browser's title; defaults to using the same breadcrumb
     * as in the module title
     *
     * @return string
     */
    public function getBrowserTitle()
    {
        global $app_strings;
        
        $browserTitle = $app_strings['LBL_BROWSER_TITLE'];
        if ( $this->module == 'Users' && ($this->action == 'SetTimezone' || $this->action == 'Login') )
            return $browserTitle;
        $params = $this->_getModuleTitleParams(true);
        foreach ($params  as $value )
            $browserTitle = strip_tags($value) . ' &raquo; ' . $browserTitle;
        
        return $browserTitle;
    }
}
