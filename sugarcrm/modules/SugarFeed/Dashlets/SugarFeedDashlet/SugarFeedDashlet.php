<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
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
/*********************************************************************************
 * $Id$
 * Description:  Defines the English language pack for the base application.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/Dashlets/DashletGeneric.php');
require_once('include/facebook.php');

class SugarFeedDashlet extends DashletGeneric { 
var $displayRows = 15;

var $categories;

var $selectedCategories = array();

    function SugarFeedDashlet($id, $def = null) {
		global $current_user, $app_strings, $app_list_strings;
		require('modules/SugarFeed/metadata/dashletviewdefs.php');
		$this->myItemsOnly = false;
        parent::DashletGeneric($id, $def);
		$this->myItemsOnly = false;
		$this->isConfigurable = true;
		$this->hasScript = true;
        // Add in some default categories.
        $this->categories['ALL'] = translate('LBL_ALL','SugarFeed');
        // Need to get the rest of the active SugarFeed modules
        $module_list = SugarFeed::getActiveFeedModules();
        // Translate the category names
        if ( ! is_array($module_list) ) { $module_list = array(); }
        foreach ( $module_list as $module ) {
            if ( $module == 'UserFeed' ) {
                // Fake module, need to translate specially
                $this->categories[$module] = translate('LBL_USER_FEED','SugarFeed');
            } else {
                $this->categories[$module] = $app_list_strings['moduleList'][$module];
            }
        }

        if(empty($def['title'])) $this->title = translate('LBL_HOMEPAGE_TITLE', 'SugarFeed');
		if(!empty($def['rows']))$this->displayRows = $def['rows'];
		if(!empty($def['categories']))$this->selectedCategories = $def['categories'];
        $this->searchFields = $dashletData['SugarFeedDashlet']['searchFields'];
        $this->columns = $dashletData['SugarFeedDashlet']['columns'];
		$catCount = count($this->categories);
		ACLController::filterModuleList($this->categories, false);
		if(count($this->categories) < $catCount){
			if(!empty($this->selectedCategories)){
				ACLController::filterModuleList($this->selectedCategories, true);
			}else{
				$this->selectedCategories = array_keys($this->categories);
				unset($this->selectedCategories[0]);
			}
		}

        // Need to un-safe this string, facebook passes raw JSON data back.
        if ( !empty($_REQUEST['session']) ) {
            $_REQUEST['session'] = str_replace('&quot;','"',$_REQUEST['session']);
        }

        // Fire up the Facebook
        $this->fbData['appId'] = '141380979217659';
        $this->fbData['secret'] = '93b0ed5908a2b23c3e17a2cc2a22cd77';
        $this->fb = new Facebook(array(
                                     'appId'  => $this->fbData['appId'],
                                     'secret' => $this->fbData['secret'],
                                     'cookie' => true,
                                     ));
        $parsedSiteUrl = parse_url($GLOBALS['sugar_config']['site_url']);
        $host = $parsedSiteUrl['host'];
		if(empty($parsedSiteUrl['port'])) {
			$parsedSiteUrl['port'] = 80;
		}
        
		$port = ($parsedSiteUrl['port'] != 80) ? ":".$parsedSiteUrl['port'] : '';
		$path = !empty($parsedSiteUrl['path']) ? $parsedSiteUrl['path'] : "";
		$this->fbData['hostUrl']  = "{$parsedSiteUrl['scheme']}://{$host}{$port}{$path}";
        
        $this->fbData['session'] = $this->fb->getSession();
        $this->fbData['sessionJSON'] = json_encode($this->fbData['session']);
        $this->fbData['me'] = null;
        $this->fbData['lastMessages'] = array();
        if ( $this->fbData['session'] ) {
            try {
                $this->fbData['uid'] = $this->fb->getUser();
                $this->fbData['me'] = $this->fb->api('/me');
                $this->fbData['lastMessages'] = $this->fb->api('/me/home?limit=15');
            } catch ( Exception $e ) {
                echo($e);
            }
        }
        
        if ( $this->fbData['me'] ) {
            $this->fbData['logoutUrl'] = $this->fb->getLogoutUrl();
        } else {
            $this->fbData['loginUrl'] = $this->fb->getLoginUrl(array('req_params'=>'read_stream'));
        }

        
        // All hands to the twitter!

        $this->seedBean = new SugarFeed();
    }
	
	function process($lvsParams = array()) {
        global $current_user;

        $currentSearchFields = array();
        $configureView = true; // configure view or regular view
        $query = false;
        $whereArray = array();
        $lvsParams['massupdate'] = false;

        // apply filters
        if(isset($this->filters) || $this->myItemsOnly) {
            $whereArray = $this->buildWhere();
        }

        $this->lvs->export = false;
        $this->lvs->multiSelect = false;
		$this->lvs->quickViewLinks = false;
        // columns
    foreach($this->columns as $name => $val) {
                if(!empty($val['default']) && $val['default']) {
                    $displayColumns[strtoupper($name)] = $val;
                    $displayColumns[strtoupper($name)]['label'] = trim($displayColumns[strtoupper($name)]['label'], ':');
                }
            }

        $this->lvs->displayColumns = $displayColumns;

        $this->lvs->lvd->setVariableName($this->seedBean->object_name, array());
   
        $lvsParams['overrideOrder'] = true;
        $lvsParams['orderBy'] = 'date_entered';
        $lvsParams['sortOrder'] = 'DESC';
                
        // Get the real module list
        if (empty($this->selectedCategories)){
            $mod_list = $this->categories;
        } else {
            $mod_list = array_flip($this->selectedCategories);//27949, here the key of $this->selectedCategories is not module name, the value is module name, so array_flip it.
        }

        $admin_modules = array();
        $owner_modules = array();
        $regular_modules = array();
        foreach($mod_list as $module => $ignore) {
			// Handle the UserFeed differently
			if ( $module == 'UserFeed') {
				$regular_modules[] = 'UserFeed';
				continue;
			}
			if (ACLAction::getUserAccessLevel($current_user->id,$module,'view') <= ACL_ALLOW_NONE ) {
				// Not enough access to view any records, don't add it to any lists
				continue;
			}
//BEGIN SUGARCRM flav=pro ONLY
            if (ACLAction::getUserAccessLevel($current_user->id,$module,'admin') >= ACL_ALLOW_ADMIN_DEV ) {
                $admin_modules[] = $module;
				continue;
			}
//END SUGARCRM flav=pro ONLY
			if ( ACLAction::getUserAccessLevel($current_user->id,$module,'view') == ACL_ALLOW_OWNER ) {
				$owner_modules[] = $module;
            } else {
                $regular_modules[] = $module;
            }
        }

        if(!empty($this->displayTpl))
        {
        	//MFH BUG #14296
            $where = '';
            if(!empty($whereArray)){
                $where = '(' . implode(') AND (', $whereArray) . ')';
            }            

			$module_limiter = " sugarfeed.related_module in ('" . implode("','", $regular_modules) . "')";

            if ( count($owner_modules) > 0
//BEGIN SUGARCRM flav=pro ONLY
				 || count($admin_modules) > 0
//END SUGARCRM flav=pro ONLY
				) {
//BEGIN SUGARCRM flav=pro ONLY
                $this->seedBean->disable_row_level_security = true;
                $lvsParams['custom_from'] = ' LEFT JOIN team_sets_teams ON team_sets_teams.team_set_id = sugarfeed.team_set_id LEFT JOIN team_memberships ON jt0.id = team_memberships.team_id AND team_memberships.user_id = "'.$current_user->id.'" AND team_memberships.deleted = 0 ';
//END SUGARCRM flav=pro ONLY
                $module_limiter = " ((sugarfeed.related_module IN ('".implode("','", $regular_modules)."') "
//BEGIN SUGARCRM flav=pro ONLY
					."AND team_memberships.id IS NOT NULL "
//END SUGARCRM flav=pro ONLY
					.") ";
//BEGIN SUGARCRM flav=pro ONLY
				if ( count($admin_modules) > 0 ) {
					$module_limiter .= "OR (sugarfeed.related_module IN ('".implode("','", $admin_modules)."')) ";
				}
//END SUGARCRM flav=pro ONLY
				if ( count($owner_modules) > 0 ) {
					$module_limiter .= "OR (sugarfeed.related_module IN('".implode("','", $owner_modules)."') AND sugarfeed.assigned_user_id = '".$current_user->id."' "
//BEGIN SUGARCRM flav=pro ONLY
						."AND team_memberships.id IS NOT NULL "
//END SUGARCRM flav=pro ONLY
						.") ";
				}
				$module_limiter .= ")";
            }
			if(!empty($where)) { $where .= ' AND '; }
			$where .= $module_limiter;

            $this->lvs->setup($this->seedBean, $this->displayTpl, $where , $lvsParams, 0, $this->displayRows, 
                              array('name', 
                                    'description', 
                                    'date_entered', 
                                    'created_by', 
//BEGIN SUGARCRM flav=pro ONLY
                                    'team_id', 
                                    'team_name', 
                                    'team_count', 
//END SUGARCRM flav=pro ONLY
                                    'link_url', 
                                    'link_type'));

            foreach($this->lvs->data['data'] as $row => $data) {
                $this->lvs->data['data'][$row]['CREATED_BY'] = get_assigned_user_name($data['CREATED_BY']);
                $this->lvs->data['data'][$row]['NAME'] = str_replace("{this.CREATED_BY}",$this->lvs->data['data'][$row]['CREATED_BY'],$data['NAME']);
            }

            // assign a baseURL w/ the action set as DisplayDashlet
            foreach($this->lvs->data['pageData']['urls'] as $type => $url) {
            	// awu Replacing action=DisplayDashlet with action=DynamicAction&DynamicAction=DisplayDashlet
                if($type == 'orderBy')
                    $this->lvs->data['pageData']['urls'][$type] = preg_replace('/(action=.*&)/Ui', 'action=DynamicAction&DynamicAction=displayDashlet&', $url);
                else
                    $this->lvs->data['pageData']['urls'][$type] = preg_replace('/(action=.*&)/Ui', 'action=DynamicAction&DynamicAction=displayDashlet&', $url) . '&sugar_body_only=1&id=' . $this->id;
            }

            $this->lvs->ss->assign('dashletId', $this->id);

        }

        $needResort = false;
        $resortQueue = array();
        // Check if we need to resort the FB messages
        if ( isset($this->fbData['lastMessages']['data'][0]) ) {
            // We need to resort
            $needResort = true;
            
            // Put the FB messages in the resort queue.
            foreach ( $this->fbData['lastMessages']['data'] as $message ) {
                // Extract the timestamp, with passion
                $unix_time = strtotime($message['created_time']);

                $fake_record = array();
                $fake_record['sort_key'] = $unix_time;
                $fake_record['ID'] = create_guid();
                $fake_record['NAME'] = $message['message'];
                $fake_record['IMAGE_URL'] = "https://graph.facebook.com/".$message['from']['id'];
                $td = new TimeDate;
                $fake_record['DATE_ENTERED'] = $td->to_display_date_time(gmdate('m-d-Y H:i:s',$unix_time));

                $resortQueue[] = $fake_record;
            }
        }
        
        // Check if we need to resort the Twitter messages

        
        
        echo('IKEA: <pre>'.print_r($resortQueue,true).'</pre>');
        echo('IKEA: <pre>'.print_r($this->lvs->data['data'][0],true).'</pre>');
        
    }
	
	  function deleteUserFeed() {
    	if(!empty($_REQUEST['record'])) {
			$feed = new SugarFeed();
			$feed->retrieve($_REQUEST['record']);
			if(is_admin($GLOBALS['current_user']) || $feed->created_by == $GLOBALS['current_user']->id){ 
            	$feed->mark_deleted($_REQUEST['record']);

			}
        }
    }
	 function pushUserFeed() {
    	if(!empty($_REQUEST['text'])) {
			$text = htmlspecialchars($_REQUEST['text']);
			//allow for bold and italic user tags
			$text = preg_replace('/&amp;lt;(\/*[bi])&amp;gt;/i','<$1>', $text);
//BEGIN SUGARCRM flav=pro ONLY
			$team_id = $_REQUEST['team_id'];
			$team_set_id = $team_id; //For now, but if we allow for multiple team selection then we'll have to change this
//END SUGARCRM flav=pro ONLY
            SugarFeed::pushFeed($text, 'UserFeed', $GLOBALS['current_user']->id, 
//BEGIN SUGARCRM flav=pro ONLY
                                $team_id,
//END SUGARCRM flav=pro ONLY
								$GLOBALS['current_user']->id,
                                $_REQUEST['link_type'], $_REQUEST['link_url']
//BEGIN SUGARCRM flav=pro ONLY
                                ,$team_set_id
//END SUGARCRM flav=pro ONLY
                                );
        }
       
    }

	 function pushUserFeedReply( ) {
         if(!empty($_REQUEST['text'])&&!empty($_REQUEST['parentFeed'])) {
			$text = htmlspecialchars($_REQUEST['text']);
			//allow for bold and italic user tags
			$text = preg_replace('/&amp;lt;(\/*[bi])&amp;gt;/i','<$1>', $text);
//BEGIN SUGARCRM flav=pro ONLY
            // Fetch the parent, use the same team id's
            $parentFeed = new SugarFeed();
            $parentFeed->retrieve($_REQUEST['parentFeed']);
			$team_id = $parentFeed->team_id;
			$team_set_id = $team_id; //For now, but if we allow for multiple team selection then we'll have to change this
//END SUGARCRM flav=pro ONLY
            SugarFeed::pushFeed($text, 'SugarFeed', $_REQUEST['parentFeed'], 
//BEGIN SUGARCRM flav=pro ONLY
                                $team_id,
//END SUGARCRM flav=pro ONLY
								$GLOBALS['current_user']->id,
                                '', ''
//BEGIN SUGARCRM flav=pro ONLY
                                ,$team_set_id
//END SUGARCRM flav=pro ONLY
                                );
        }
       
    }

	  function displayOptions() {
        global $app_strings;
        $ss = new Sugar_Smarty();
        $ss->assign('titleLBL', translate('LBL_TITLE', 'SugarFeed'));
		$ss->assign('categoriesLBL', translate('LBL_CATEGORIES', 'SugarFeed'));
        $ss->assign('rowsLBL', translate('LBL_ROWS', 'SugarFeed'));
        $ss->assign('saveLBL', $app_strings['LBL_SAVE_BUTTON_LABEL']);
        $ss->assign('title', $this->title);
		$ss->assign('categories', $this->categories);
        if ( empty($this->selectedCategories) ) {
            $this->selectedCategories['ALL'] = 'ALL';
        }
		$ss->assign('selectedCategories', $this->selectedCategories);
        $ss->assign('rows', $this->displayRows);
        $ss->assign('id', $this->id);

        return  $ss->fetch('modules/SugarFeed/Dashlets/SugarFeedDashlet/Options.tpl');
    }  
	
	/**
	 * creats the values
	 * @return 
	 * @param $req Object
	 */
	  function saveOptions($req) {
        global $sugar_config, $timedate, $current_user, $theme;
        $options = array();
        $options['title'] = $_REQUEST['title'];
		$rows = intval($_REQUEST['rows']);
        if($rows <= 0) {
            $rows = 15;         
        }
		if($rows > 100){
			$rows = 100;
		}
        $options['rows'] = $rows;
		$options['categories'] = $_REQUEST['categories'];
		foreach($options['categories'] as $cat){
			if($cat == 'ALL'){
				unset($options['categories']);
			}
		}
        return $options;
    }
	
      
      function sugarFeedDisplayScript() {
          // Forces the quicksearch to reload anytime the dashlet gets refreshed
          return '<script type="text/javascript">
enableQS(false);
</script>';
      }
	/**
	 * 
	 * @return javascript including QuickSearch for SugarFeeds
	 */
	 function displayScript() {
	 	require_once('include/QuickSearchDefaults.php');

        $ss = new Sugar_Smarty();
        $ss->assign('saving', translate('LBL_SAVING', 'SugarFeed'));
        $ss->assign('saved', translate('LBL_SAVED', 'SugarFeed'));
        $ss->assign('id', $this->id);
        
        $str = $ss->fetch('modules/SugarFeed/Dashlets/SugarFeedDashlet/SugarFeedScript.tpl');
		//BEGIN SUGARCRM flav=pro ONLY
		$qsd = new QuickSearchDefaults();
		$json = getJSONobj();
		$sqs_objects = $qsd->getQSTeam();
		foreach($sqs_objects['populate_list'] as &$v){
			$v .= '_' . $this->id;
		}
		foreach($sqs_objects['required_list'] as &$v){
			$v .= '_' . $this->id;
		}
		$sqs_objects['form'] = "form_{$this->id}";
		$sqs_objects_encoded = $json->encode($sqs_objects);
		$quicksearch_js = <<<EOQ
		<script type="text/javascript" language="javascript">
	 		if(typeof sqs_objects == 'undefined'){var sqs_objects = new Array;}
			sqs_objects["form_{$this->id}_team_name"] = $sqs_objects_encoded;
		</script>
EOQ;
        $str .= $quicksearch_js;
		//END SUGARCRM flav=pro ONLY
        return $str; // return parent::display for title and such
    }
	
	/**
	 * 
	 * @return the fully rendered dashlet
	 */
	function display(){
		
		$listview = parent::display();
		$GLOBALS['current_sugarfeed'] = $this;
		$listview = preg_replace_callback('/\{([^\}]+)\.([^\}]+)\}/', create_function(
            '$matches',
            'if($matches[1] == "this"){$var = $matches[2]; return $GLOBALS[\'current_sugarfeed\']->$var;}else{return translate($matches[2], $matches[1]);}'
        ),$listview);
		$listview = preg_replace('/\[(\w+)\:([\w\-\d]*)\:([^\]]*)\]/', '<a href="index.php?module=$1&action=DetailView&record=$2"><img src="themes/default/images/$1.gif" border=0>$3</a>', $listview);
        
		return $listview.'</div>';
	}
	
	
	/**
	 * 
	 * @return the title and the user post form
	 * @param $text Object
	 */
	function getHeader($text='') {
		return parent::getHeader($text) . $this->getPostForm().$this->getDisabledWarning().$this->sugarFeedDisplayScript().'<div class="sugarFeedDashlet">';	
	}
	
	
	/**
	 * 
	 * @return a warning message if the sugar feed system is not enabled currently
	 */
	function getDisabledWarning(){
        /* Check to see if the sugar feed system is enabled */
        if ( ! $this->shouldDisplay() ) {
            // The Sugar Feeds are disabled, populate the warning message
            return translate('LBL_DASHLET_DISABLED','SugarFeed');
        } else {
            return '';
        }
    }

	/**
	 * 
	 * @return the form for users posting custom messages to the feed stream
	 */
	function getPostForm(){
        global $current_user;

        if ( empty($this->categories['UserFeed']) ) {
            // The user feed system isn't enabled, don't let them post notes
            return '';
        }
		$user_name = ucfirst($GLOBALS['current_user']->user_name);
		$moreimg = SugarThemeRegistry::current()->getImage('advanced_search' , 'onclick="toggleDisplay(\'more_' . $this->id . '\'); toggleDisplay(\'more_img_'.$this->id.'\'); toggleDisplay(\'less_img_'.$this->id.'\');"');
		$lessimg = SugarThemeRegistry::current()->getImage('basic_search' , 'onclick="toggleDisplay(\'more_' . $this->id . '\'); toggleDisplay(\'more_img_'.$this->id.'\'); toggleDisplay(\'less_img_'.$this->id.'\');"');
		$ss = new Sugar_Smarty();
		$ss->assign('LBL_TO', translate('LBL_TO', 'SugarFeed'));
		$ss->assign('LBL_POST', translate('LBL_POST', 'SugarFeed'));
		$ss->assign('LBL_SELECT', translate('LBL_SELECT', 'SugarFeed'));
		$ss->assign('LBL_IS', translate('LBL_IS', 'SugarFeed'));
		$ss->assign('id', $this->id);
		//BEGIN SUGARCRM flav=pro ONLY
        if ( !empty($current_user) ) {
            $team_id = $current_user->default_team;
        } else {
            $team_id = 1;
        }
		$team_name =   get_assigned_team_name($team_id);
		$ss->assign('team_id', $team_id);
		$ss->assign('team_name', $team_name);
		//END SUGARCRM flav=pro ONLY
		$ss->assign('more_img', $moreimg);
		$ss->assign('less_img', $lessimg);
        if($current_user->getPreference('use_real_names') == 'on'){
            $ss->assign('user_name', $current_user->full_name);
        }
        else {
            $ss->assign('user_name', $user_name);
        }
        $linkTypesIn = SugarFeed::getLinkTypes();
        $linkTypes = array();
        foreach ( $linkTypesIn as $key => $value ) {
            $linkTypes[$key] = translate('LBL_LINK_TYPE_'.$value,'SugarFeed');
        }
		$ss->assign('link_types', $linkTypes);
        $ss->assign('fb', $this->fb);
        $ss->assign('fbData', $this->fbData);
		return $ss->fetch('modules/SugarFeed/Dashlets/SugarFeedDashlet/UserPostForm.tpl');
	
	}
    
    // This is called from the include/MySugar/DashletsDialog/DashletsDialog.php and determines if we should display the SugarFeed dashlet as an option or not
    static function shouldDisplay() {
        
        $admin = new Administration();
        $admin->retrieveSettings();

        if ( !isset($admin->settings['sugarfeed_enabled']) || $admin->settings['sugarfeed_enabled'] != '1' ) {
            return false;
        } else {
            return true;
        }
    }
}
