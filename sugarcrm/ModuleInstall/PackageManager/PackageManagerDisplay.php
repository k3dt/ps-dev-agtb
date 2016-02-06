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


require_once('ModuleInstall/PackageManager/PackageManager.php');

require_once('vendor/ytree/Tree.php');
require_once('vendor/ytree/Node.php');

class PackageManagerDisplay{

   /**
     * A Static method to Build the display for the package manager
     *
     * @param String form1 - the form to display for manual downloading
     * @param String hidden_fields - the hidden fields related to downloading a package
     * @param String form_action - the form_action to be used when downloading from the server
     * @param array types - the types of objects we will request from the server
     * @param String active_form - the form to display first
     * @return String - a string of html which will be used to display the forms
     */
    public static function buildPackageDisplay(
        $form1,
        $hidden_fields,
        $form_action,
        $types = array('module'),
        $active_form = 'form1',
        $install = false
    ) {
        global $current_language, $app_strings;

        $app_strings = return_application_language($current_language);
        $ss = new Sugar_Smarty();
        $ss->assign('APP_STRINGS', $app_strings);
        $ss->assign('FORM_1_PLACE_HOLDER', $form1);
        $ss->assign('form_action', $form_action);
        $ss->assign('hidden_fields', $hidden_fields);

        $result = PackageManagerDisplay::getHeader();
        $isAlive = $result['isAlive'];

        SugarAutoLoader::requireWithCustom('ModuleInstall/ModuleInstaller.php');
        $moduleInstallerClass = SugarAutoLoader::customClass('ModuleInstaller');
        $mi = new $moduleInstallerClass();
        $mi_errors = $mi->getErrors();
        $error_html = "";
        if (!empty($mi_errors)) {
            $error_html = "<div style='margin:0px 10px 10px 10px;'>";
            foreach ($mi_errors as $error) {
                $error_html .= "<font color='red'>" . $error . "</font><br>";
            }
            $error_html .= "</div>";
        }

        $tree = PackageManagerDisplay::buildTreeView('treeview', $isAlive);
        $tree->tree_style = getVersionedPath('vendor/ytree/TreeView/css/check/tree.css');
        $ss->assign('TREEHEADER', $tree->generate_header());

        $ss->assign('installation', ($install ? 'true' : 'false'));


       $mod_strings = return_module_language($current_language, "Administration");

        $ss->assign('MOD', $mod_strings);
		$ss->assign('module_load', 'true');
        if (UploadStream::getSuhosinStatus() == false)
        {
            $ss->assign('ERR_SUHOSIN', true);
        }
        else
        {
            $ss->assign('scripts', PackageManagerDisplay::getDisplayScript($install));
        }
        $show_login = false; //hiding install from sugar
		$ss->assign('MODULE_SELECTOR', PackageManagerDisplay::buildGridOutput($tree, $mod_strings, $isAlive, $show_login));
        $ss->assign('INSTALL_ERRORS', $error_html);
        $ss->assign('MOD', $mod_strings);
        $descItemsInstalled = $mod_strings['LBL_UW_DESC_MODULES_INSTALLED'];
        $ss->assign('INSTALLED_PACKAGES_HOLDER', PackageManagerDisplay::buildInstalledGrid($mod_strings, $types));

   $str = $ss->fetch('ModuleInstall/PackageManager/tpls/PackageForm.tpl');
      return $str;
    }

    /**
     * A Static method to Build the display for the package manager
     *
     * @param String form1 - the form to display for manual downloading
     * @param String hidden_fields - the hidden fields related to downloading a package
     * @param String form_action - the form_action to be used when downloading from the server
     * @param array types - the types of objects we will request from the server
     * @param String active_form - the form to display first
     * @return String - a string of html which will be used to display the forms
     */
    public static function buildPatchDisplay(
        $form1,
        $hidden_fields,
        $form_action,
        $types = array('module'),
        $active_form = 'form1'
    ) {
    	global $current_language;
        $mod_strings = return_module_language($current_language, "Administration");
        $ss = new Sugar_Smarty();
        $ss->assign('FORM_1_PLACE_HOLDER', $form1);
        $ss->assign('form_action', $form_action);
        $ss->assign('hidden_fields', $hidden_fields);
        $mod_strings = return_module_language($current_language, "Administration");

        $ss->assign('MOD', $mod_strings);
        $result = PackageManagerDisplay::getHeader();
        $header_text = $result['text'];
        $isAlive = $result['isAlive'];
        $show_login = $result['show_login'];
        $display = 'block';
        $form2 = "<table  class='tabForm' width='100%'  cellpadding='0' cellspacing='0' width='100%' border='0'>";
        if(!$isAlive)
        	$form2 .= "<tr><td><span id='span_display_html'>".$header_text."</span></td></tr>";
        $form2 .= "</table>";
        $form2 .= "<table width='100%'><tr><td align='left'>";
        if($show_login){
        	$form2 .= "<input type='button' class='button' onClick='PackageManager.showLoginDialog(true);' value='".$mod_strings['LBL_MODIFY_CREDENTIALS']."'>";
        }
        $form2 .= "</td><td align='right'><div id='workingStatusDiv' style='display:none;'>".SugarThemeRegistry::current()->getImage("sqsWait","border='0' align='bottom'",null,null,'.gif',"Loading")."</div></td></tr><tr><td colspan='2'>";

        $loginViewStyle = ($isAlive ? 'none' : 'block');
		$selectViewStyle = ($isAlive ? 'block' : 'none');
		$form2 .= "<div id='selectView' style='display:".$selectViewStyle."'>";
		$form2 .= "  <div id='patch_downloads' class='ygrid-mso' style='height:205px; display: ".$display.";'></div>";
		 $form2 .= "</div>";
		 if(!$show_login)
         	$loginViewStyle = 'none';

        $form2 .= "</td></tr></table>";
        $form2 = '';
        $packages = array();
        $releases = array();
        if($isAlive){
          	$filter = array();
          	$count = count($types);
          	$index = 1;
          	$type_str = '"';
          	foreach($types as $type){
          		$type_str .= "'".$type."'";
          		if($index < $count)
          			$type_str .= ",";
          		$index++;
          	}
          	$type_str .= '"';
          	$filter = array('type' => $type_str);
          	$filter = PackageManager::toNameValueList($filter);
            $pm = new PackageManager();
        }
        $tree = PackageManagerDisplay::buildTreeView('treeview', $isAlive);
        $tree->tree_style= getVersionedPath('vendor/ytree/TreeView/css/check/tree.css');
        $ss->assign('TREEHEADER',$tree->generate_header());
		$ss->assign('module_load', 'false');
		$ss->assign('MODULE_SELECTOR', PackageManagerDisplay::buildGridOutput($tree, $mod_strings, $isAlive, $show_login));
        $ss->assign('FORM_2_PLACE_HOLDER', $form2);
        $ss->assign('scripts', PackageManagerDisplay::getDisplayScript(false, 'patch', $releases, $types, $isAlive));
        $str = $ss->fetch('ModuleInstall/PackageManager/tpls/PackageForm.tpl');
        return $str;
    }

    function buildInstalledGrid($mod_strings, $types = array('modules')){
    	  $descItemsInstalled = $mod_strings['LBL_UW_DESC_MODULES_INSTALLED'];
    	  $output = '<table width="100%" border="0" cellspacing="0" cellpadding="0" ><tr><td align="left">'.$descItemsInstalled.'</td>';
          $output .= '</td></tr></table>';
          $output .= "<table width='100%'><tr><td ><div id='installed_grid' class='ygrid-mso' style='height:205px;'></div></td></tr></table>";
          return $output;
    }

    function buildLoginPanel($mod_strings, $display_cancel){
        $credentials = PackageManager::getCredentials();
    	$output = "<div id='login_panel'><div class='hd'><b>".$mod_strings['HDR_LOGIN_PANEL']."</b></div>";
        $output .= "<div class='bd'><form><table><tr><td>".$mod_strings['LBL_USERNAME']."</td><td><input type='text' name='login_panel_username' id='login_panel_username' value='".$credentials['username']."'></td><td><a href='http://www.sugarcrm.com/crm/index.php?option=com_registration&task=register' target='blank'>".$mod_strings['LNK_NEW_ACCOUNT']."</a></td>";

        $output .= "</tr><tr><td>".$mod_strings['LBL_PASSWORD']."</td><td><input type='password' name='login_panel_password' id='login_panel_password'></td><td><a href='http://www.sugarcrm.com/crm/component/option,com_registration/Itemid,0/task,lostPassword/' target='blank'>".$mod_strings['LNK_FORGOT_PASS']."</a></td>";

		$terms = PackageManager::getTermsAndConditions();
		$output .= "</tr><tr><td colspan='6' valign='top'><b>".$mod_strings['LBL_TERMS_AND_CONDITIONS']."</b><br><textarea readonly cols=80 rows=8>" . $terms['terms'] . '</textarea></td>';
       	$_SESSION['SugarDepot_TermsVersion'] = (!empty($terms['version']) ? $terms['version'] : '');

		$output .= "</td></tr><tr><td colspan='6'><input class='checkbox' type='checkbox' name='cb_terms' id='cb_terms' onclick='if(this.checked){this.form.panel_login_button.disabled=false;}else{this.form.panel_login_button.disabled=true;}'>".$mod_strings['LBL_ACCEPT_TERMS']."</td></tr><tr>";
        $output .= "<td align='left'>";
        $output .= "<input type='button' id='panel_login_button' name='panel_login_button' value='Login' class='button' onClick='PackageManager.authenticate(this.form.login_panel_username.value, this.form.login_panel_password.value, \"\",\"" . $terms['version'] . "\");' disabled>";

        if($display_cancel){
        	$output .= "&nbsp;<input type='button' id='panel_cancel_button' value='Cancel' class='button' onClick='PackageManager.showLoginDialog(false);'>";
        }
        $output .= "</td><td></td></tr>";
		$output .= "<tr></td><td></td></tr>";
		$output .= "</table></div>";
        $output .= "<div class='ft'></div></form></div>";
        return $output;
    }

    /**
     *  Build html in order to display the grids relevant for module loader
     *
     *  @param Tree tree - the tree which we are using to display the categories
     *  @param Array mod_strings - the local mod strings to display
     *  @return String - a string of html
     */
	function buildGridOutput($tree, $mod_strings, $display = true, $show_login = true){
		 $output = "<div id='catview'>";
		$loginViewStyle = ($display ? 'none' : 'block');
		$selectViewStyle = ($display ? 'block' : 'none');
		$output .= "<div id='selectView' style='display:".$selectViewStyle."'>";
    		$output .= "<table border=0 width='100%' class='moduleTitle'><tr><td width='100%' valign='top'>";
    		$output .= "<div id='treeview'>";
    		$output .= $tree->generate_nodes_array();
    		$output .= "</div>";
    		$output .= "</td></tr>";
            $output .= "<tr><td width='100%'>";
			$output .= "<div id='tabs1'></div>";
            $output .= "</td></tr>";
            $output .= "<tr><td width='100%' align='left'>";
            $output .= "<input type='button' class='button' value='Download Selected' onClick='PackageManager.download();'>";
            $output .= "</td></tr></table>";
         $output .= "</div>";
         if(!$show_login)
         	$loginViewStyle = 'none';
		$output .= "</div>";

		return $output;
	}

     /**
     * A Static method used to build the initial treeview when the page is first displayed
     *
     * @param String div_id - this div in which to display the tree
     * @return Tree - the tree that is built
     */
    function buildTreeView($div_id, $isAlive = true){
        $tree = new Tree($div_id);
        $nodes = array();
        if($isAlive)
        	$nodes = PackageManager::getCategories('');

        foreach($nodes as $arr_node){
            $node = new Node($arr_node['id'], $arr_node['label']);
            $node->dynamicloadfunction = 'PackageManager.loadDataForNodeForPackage';
            $node->expanded = false;
            $node->dynamic_load = true;
            $node->set_property('href',"javascript:PackageManager.catClick('treeview');");
            $tree->add_node($node);
            $node->set_property('description', $arr_node['description']);
        }
        return $tree;
    }

    /**
     * A Static method used to obtain the div for the license
     *
     * @param String license_file - the path to the license file
     * @param String form_action - the form action when accepting the license file
     * @param String next_step - the value for the next step in the installation process
     * @param String zipFile - a string representing the path to the zip file
     * @param String type - module/patch....
     * @param String manifest - the path to the manifest file
     * @param String modify_field - the field to update when the radio button is changed
     * @return String - a form used to display the license
     */
    public static function getLicenseDisplay($license_file, $form_action, $next_step, $zipFile, $type, $manifest, $modify_field){
    	global $current_language;
        $mod_strings = return_module_language($current_language, "Administration");
        $contents = sugar_file_get_contents($license_file);
        $div_id = urlencode($zipFile);
        $display = "<form name='delete{$zipFile}' action='{$form_action}' method='POST'>";
        $display .= "<input type='hidden' name='current_step' value='{$next_step}'>";
        $display .= "<input type='hidden' name='languagePackAction' value='{$type}'>";
        $display .= "<input type='hidden' name='manifest' value='\".urlencode($manifest).\"'>";
        $display .= "<input type='hidden' name='zipFile' value='\".urlencode($zipFile).\"'>";
        $display .= "<table><tr>";
        $display .= "<td align=\"left\" valign=\"top\" colspan=2>";
        $display .= "<b><font color='red' >{$mod_strings['LBL_MODULE_LICENSE']}</font></b>";
        $display .= "</td>";
        $display .= "<td>";
        $display .= "<slot><a class=\"listViewTdToolsS1\" id='href_animate' onClick=\"PackageManager.toggleLowerDiv('span_animate_div_$div_id', 'span_license_div_$div_id', 350, 0);\"><span id='span_animate_div_$div_id'<img src='".SugarThemeRegistry::current()->getImageURL('advanced_search.gif')."' width='8' height='8' alt='Advanced' border='0'>&nbsp;Expand</span></a></slot></td>";
        $display .= "</td>";
        $display .= "</tr>";
        $display .= "</table>";
        $display .= "<div id='span_license_div_$div_id' style=\"display: none;\">";
        $display .= "<table>";
        $display .= "<tr>";
        $display .= "<td align=\"left\" valign=\"top\" colspan=2>";
        $display .= "<textarea cols=\"100\" rows=\"8\">{$contents}</textarea>";
        $display .= "</td>";
        $display .= "</tr>";
        $display .= "<tr>";
        $display .= "<td align=\"left\" valign=\"top\" colspan=2>";
        $display .= "<input type='radio' id='radio_license_agreement_accept' name='radio_license_agreement' value='accept' onClick=\"document.getElementById('$modify_field').value = 'yes';\">{$mod_strings['LBL_ACCEPT']}&nbsp;";
        $display .= "<input type='radio' id='radio_license_agreement_reject' name='radio_license_agreement' value='reject' checked onClick=\"document.getElementById('$modify_field').value = 'no';\">{$mod_strings['LBL_DENY']}";
        $display .= "</td>";
        $display .= "</tr>";
        $display .= "</table>";
        $display .= "</div>";
        $display .= "</form>";
        return $display;
    }

     /**
     * A Static method used to generate the javascript for the page
     *
     * @return String - the javascript required for the page
     */
    function getDisplayScript($install = false, $type = 'module', $releases = null, $types = array(), $isAlive = true){
        global $sugar_version, $sugar_config;
        global $current_language;

        $mod_strings = return_module_language($current_language, "Administration");
        $ss = new Sugar_Smarty();
        $ss->assign('MOD', $mod_strings);
        if(!$install){
            $install = 0;
        }
		$ss->assign('INSTALLATION', $install);
        $ss->assign('WAIT_IMAGE', SugarThemeRegistry::current()->getImage("loading","border='0' align='bottom'",null,null,'.gif',"Loading"));

        $ss->assign('sugar_version', $sugar_version);
        $ss->assign('js_custom_version', $sugar_config['js_custom_version']);
         $ss->assign('IS_ALIVE', $isAlive);
        if($type == 'patch'){
            $ss->assign('module_load', 'false');
            $patches = PackageManagerDisplay::createJavascriptPackageArray($releases);
            $ss->assign('PATCHES', $patches);
             $ss->assign('GRID_TYPE', implode(',', $types));
        }else{
           	$pm = new PackageManager();
           	$releases = $pm->getPackagesInStaging();
           	$patches = PackageManagerDisplay::createJavascriptModuleArray($releases);
            $ss->assign('PATCHES', $patches);
            $installeds = $pm->getinstalledPackages();
           	$patches = PackageManagerDisplay::createJavascriptModuleArray($installeds, 'mti_installed_data');
            $ss->assign('INSTALLED_MODULES', $patches);
			 $ss->assign('UPGARDE_WIZARD_URL', 'index.php?module=UpgradeWizard&action=index');
            $ss->assign('module_load', 'true');
        }
        if(!empty($GLOBALS['ML_STATUS_MESSAGE']))
        	$ss->assign('ML_STATUS_MESSAGE',$GLOBALS['ML_STATUS_MESSAGE']);

        //Bug 24064. Checking and Defining labels since these might not be cached during Upgrade
        if(!isset($mod_strings['LBL_ML_INSTALL']) || empty($mod_strings['LBL_ML_INSTALL'])){
			$mod_strings['LBL_ML_INSTALL'] = 'Install';
    	}
		if(!isset($mod_strings['LBL_ML_ENABLE_OR_DISABLE']) || empty($mod_strings['LBL_ML_ENABLE_OR_DISABLE'])) {
			$mod_strings['LBL_ML_ENABLE_OR_DISABLE'] = 'Enable/Disable';
		}
		if(!isset($mod_strings['LBL_ML_DELETE'])|| empty($mod_strings['LBL_ML_DELETE'])){
			$mod_strings['LBL_ML_DELETE'] = 'Delete';
		}
        //Add by jchi 6/23/2008 to fix the bug 21667
		$filegrid_column_ary = array(
			'Name' => $mod_strings['LBL_ML_NAME'],
			'Install' => $mod_strings['LBL_ML_INSTALL'],
			'Delete' => $mod_strings['LBL_ML_DELETE'],
			'Type' => $mod_strings['LBL_ML_TYPE'],
			'Version' => $mod_strings['LBL_ML_VERSION'],
			'Published' => $mod_strings['LBL_ML_PUBLISHED'],
			'Uninstallable' => $mod_strings['LBL_ML_UNINSTALLABLE'],
			'Description' => $mod_strings['LBL_ML_DESCRIPTION']
		);

		$filegridinstalled_column_ary = array(
			'Name' => $mod_strings['LBL_ML_NAME'],
			'Install' => $mod_strings['LBL_ML_INSTALL'],
			'Action' => $mod_strings['LBL_ML_ACTION'],
			'Enable_Or_Disable' => $mod_strings['LBL_ML_ENABLE_OR_DISABLE'],
			'Type' => $mod_strings['LBL_ML_TYPE'],
			'Version' => $mod_strings['LBL_ML_VERSION'],
			'Date_Installed' => $mod_strings['LBL_ML_INSTALLED'],
			'Uninstallable' => $mod_strings['LBL_ML_UNINSTALLABLE'],
			'Description' => $mod_strings['LBL_ML_DESCRIPTION']
		);

		$ss->assign('ML_FILEGRID_COLUMN',$filegrid_column_ary);
		$ss->assign('ML_FILEGRIDINSTALLED_COLUMN',$filegridinstalled_column_ary);
		//end

		$ss->assign('SHOW_IMG', SugarThemeRegistry::current()->getImage('advanced_search', 'border="0"', 8, 8, '.gif', 'Show'));
		$ss->assign('HIDE_IMG', SugarThemeRegistry::current()->getImage('basic_search', 'border="0"', 8, 8, '.gif', 'Hide'));
        $str = $ss->fetch('ModuleInstall/PackageManager/tpls/PackageManagerScripts.tpl');
        return $str;
    }

    function createJavascriptPackageArray($releases){
        $output = "var mti_data = [";
        $count = count($releases);
        $index = 1;
        if(!empty($releases['packages'])){
	        foreach($releases['packages'] as $release){
	            $release = PackageManager::fromNameValueList($release);
	            $output .= "[";
	            $output .= "'".$release['description']."', '".$release['version']."', '".$release['build_number']."', '".$release['id']."'";
	            $output .= "]";
	            if($index < $count)
	                $output .= ",";
	            $index++;
	        }
        }
        $output .= "]\n;";
        return $output;
    }

    function createJavascriptModuleArray($modules, $variable_name = 'mti_data'){
        $output = "var ".$variable_name." = [";
        $count = count($modules);
        $index = 1;
        if(!empty($modules)){
	        foreach($modules as $module){
	            $output .= "[";
	            $output .= "'".$module['name']."', '".$module['file_install']."', '".$module['file']."', '";
	            if(!empty($module['enabled']))
	            	$output .= $module['enabled'].'_'.$module['file']."', '";

 				$description = js_escape($module['description']);
	            $output .= $module['type']."', '".$module['version']."', '".$module['published_date']."', '".$module['uninstallable']."', '".$description."'".(isset($module['upload_file'])?" , '".$module['upload_file']."']":"]");
	            if($index < $count)
	                $output .= ",";
	            $index++;
	        }

        }
        $output .= "]\n;";
        return $output;
    }

   /**
    *  This method is meant to be used to display the license agreement inline on the page
    *  if the system would like to perform the installation on the same page via an Ajax call
    */
    public static function buildLicenseOutput($file)
    {
    	global $current_language;

        $mod_strings = return_module_language($current_language, "Administration");
        $contents = '';
        $pm = new PackageManager();
        $contents = $pm->getLicenseFromFile($file);
        $ss = new Sugar_Smarty();
        $ss->assign('MOD', $mod_strings);
        $ss->assign('LICENSE_CONTENTS', $contents);
        $ss->assign('FILE', $file);
        $str = $ss->fetch('ModuleInstall/PackageManagerLicense.tpl');
        $GLOBALS['log']->debug('LICENSE OUTPUT: '.$str);
        return $str;
    }

    public static function getHeader()
    {
    	global $current_language;

        $mod_strings = return_module_language($current_language, "Administration");
        $header_text = '';
        $isAlive = false;
        $show_login = false;
        if(!function_exists('curl_init') && $show_login){
        	$header_text = "<font color='red'><b>".$mod_strings['ERR_ENABLE_CURL']."</b></font>";
        	$show_login = false;
        }else{
            $credentials = PackageManager::getCredentials();
            if (!empty($credentials['username']) && !empty($credentials['password'])) {
            	$result = PackageManagerComm::login();
            	if((is_array($result) && !empty($result['faultcode'])) || $result == false){
            		$header_text = "<font color='red'><b>".$result['faultstring']."</b></font>";
            	}else{
            		$header_text = PackageManager::getPromotion();
            		$isAlive = true;
            	}
            }
        }
        return array('text' => $header_text, 'isAlive' => $isAlive, 'show_login' => $show_login);
    }

    function buildInstallGrid($view){
    	$uh = new UpgradeHistory();
    	$installeds = $uh->getAll();
		$upgrades_installed = 0;
		$installed_objects = array();
		foreach($installeds as $installed)
		{
			$filename = from_html($installed->filename);
			$date_entered = $installed->date_entered;
			$type = $installed->type;
			$version = $installed->version;
			$upgrades_installed++;
			$link = "";

			switch($type)
			{
				case "theme":
				case "langpack":
				case "module":
				case "patch":
				$manifest_file = extractManifest($filename);
				require_once($manifest_file);

				$name = empty($manifest['name']) ? $filename : $manifest['name'];
				$description = empty($manifest['description']) ? $mod_strings['LBL_UW_NONE'] : $manifest['description'];
				if(($upgrades_installed==0 || $uh->UninstallAvailable($installeds, $installed))
					&& is_file($filename) && !empty($manifest['is_uninstallable']))
				{
					$link = urlencode( $filename );
				}
				else
				{
					$link = 'false';
				}

				break;
				default:
					break;
			}

			if($view == 'default' && $type != 'patch')
			{
				continue;
			}

			if($view == 'module'
				&& $type != 'module' && $type != 'theme' && $type != 'langpack')
			{
				continue;
			}

			$target_manifest = remove_file_extension( $filename ) . "-manifest.php";
			require_once( "$target_manifest" );

			if(isset($manifest['icon']) && $manifest['icon'] != "")
			{
				$manifest_copy_files_to_dir = isset($manifest['copy_files']['to_dir']) ? clean_path($manifest['copy_files']['to_dir']) : "";
				$manifest_copy_files_from_dir = isset($manifest['copy_files']['from_dir']) ? clean_path($manifest['copy_files']['from_dir']) : "";
				$manifest_icon = clean_path($manifest['icon']);
				$icon = "<img src=\"" . $manifest_copy_files_to_dir . ($manifest_copy_files_from_dir != "" ? substr($manifest_icon, strlen($manifest_copy_files_from_dir)+1) : $manifest_icon ) . "\">";
			}
			else
			{
				$icon = getImageForType( $manifest['type'] );
			}
			$installed_objects[] = array('icon' => $icon, 'name' => $name, 'type' => $type, 'version' => $version, 'date_entered' => $date_entered, 'description' => $description, 'file' => $link);
		}
    }
 }
