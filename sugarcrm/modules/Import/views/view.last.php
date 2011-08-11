<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
* The contents of this file are subject to the SugarCRM Enterprise Subscription
* Agreement ("License") which can be viewed at
* http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
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
* by SugarCRM are Copyright (C) 2004-2007 SugarCRM, Inc.; All Rights Reserved.
********************************************************************************/
/*********************************************************************************
 * $Id: view.last.php 31561 2008-02-04 18:41:10Z jmertic $
 * Description: view handler for last step of the import process
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 ********************************************************************************/
require_once('modules/Import/views/ImportView.php');
require_once('modules/Import/ImportCacheFiles.php');
require_once('modules/Import/sources/ImportFile.php');
require_once('modules/Import/views/ImportListView.php');
require_once('include/ListView/ListViewFacade.php');


class ImportViewLast extends ImportView
{
    protected $pageTitleKey = 'LBL_STEP_5_TITLE';
    
 	/** 
     * @see SugarView::display()
     */
 	public function display()
    {
        global $mod_strings, $app_strings, $current_user, $sugar_config, $current_language;
        
        
        
        $this->ss->assign("IMPORT_MODULE", $_REQUEST['import_module']);
        $this->ss->assign("TYPE", $_REQUEST['type']);
        $this->ss->assign("HEADER", $app_strings['LBL_IMPORT']." ". $mod_strings['LBL_MODULE_NAME']);
        $this->ss->assign("MODULE_TITLE", json_encode($this->getModuleTitle(false)));
        // lookup this module's $mod_strings to get the correct module name
        $module_mod_strings = 
            return_module_language($current_language, $_REQUEST['import_module']);
        $this->ss->assign("MODULENAME",$module_mod_strings['LBL_MODULE_NAME']);
        
        // read status file to get totals for records imported, errors, and duplicates
        $count        = 0;
        $errorCount   = 0;
        $dupeCount    = 0;
        $createdCount = 0;
        $updatedCount = 0;
        $fp = sugar_fopen(ImportCacheFiles::getStatusFileName(),'r');
        while (( $row = fgetcsv($fp, 8192) ) !== FALSE)
        {
            $count         += (int) $row[0];
            $errorCount    += (int) $row[1];
            $dupeCount     += (int) $row[2];
            $createdCount  += (int) $row[3];
            $updatedCount  += (int) $row[4];
        }
        fclose($fp);
    
        $showUndoButton = false;
        if($createdCount > 0)
        {
        	$showUndoButton = true;
        }

        if ($errorCount > 0 &&  ($createdCount <= 0 && $updatedCount <= 0))
            $activeTab = 2;
        else if($dupeCount > 0 &&  ($createdCount <= 0 && $updatedCount <= 0))
            $activeTab = 1;
        else
            $activeTab = 0;
        
        $this->ss->assign("JS", json_encode($this->_getJS($activeTab)));
        $this->ss->assign("errorCount",$errorCount);
        $this->ss->assign("dupeCount",$dupeCount);
        $this->ss->assign("createdCount",$createdCount);
        $this->ss->assign("updatedCount",$updatedCount);
        $this->ss->assign("errorFile",ImportCacheFiles::getErrorFileName());
        $this->ss->assign("errorrecordsFile",ImportCacheFiles::getErrorRecordsWithoutErrorFileName());
        $this->ss->assign("dupeFile",ImportCacheFiles::getDuplicateFileName());
        
        //BEGIN SUGARCRM flav!=sales ONLY
        if ( $this->bean->object_name == "Prospect" )
        {
        	$prospectlistbutton = $this->_addToProspectListButton();
        }
        else {
            $prospectlistbutton = "";
        }
        //END SUGARCRM flav!=sales ONLY

        $resultsTable = "";
        foreach ( UsersLastImport::getBeansByImport($_REQUEST['import_module']) as $beanname )
        {
            // load bean
            if ( !( $this->bean instanceof $beanname ) )
            {
                $this->bean = new $beanname;
            }
           $resultsTable .= $this->getListViewResults();
        }
        if(empty($resultsTable))
        {
            $resultsTable = $this->getListViewResults();
        }

        $this->ss->assign("RESULTS_TABLE", $resultsTable);
        $this->ss->assign("ERROR_TABLE", $this->getListViewTableFromFile(ImportCacheFiles::getErrorRecordsFileName(), 'errors') );
        $this->ss->assign("DUP_TABLE", $this->getListViewTableFromFile(ImportCacheFiles::getDuplicateFileDisplayName(), 'dup'));

        
        $content = $this->ss->fetch('modules/Import/tpls/last.tpl');
        $this->ss->assign("CONTENT",json_encode($content));
        $submitContent = "<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td align=\"right\">";
	    $submitContent .= "<input title=\"".$mod_strings['LBL_IMPORT_COMPLETE']."\" accessKey=\"\" class=\"button\" type=\"submit\" name=\"finished\" value=\"  ".$mod_strings['LBL_IMPORT_COMPLETE']."  \" id=\"finished\">";
        $submitContent .= "<input title=\"".$mod_strings['LBL_UNDO_LAST_IMPORT']."\" accessKey=\"\" class=\"button\" type=\"submit\" name=\"undo\" value=\"  ".$mod_strings['LBL_UNDO_LAST_IMPORT']."  \" id=\"undo\">";
	    $submitContent .= "<input title=\"".$mod_strings['LBL_IMPORT_MORE']."\" accessKey=\"\" class=\"button primary\" type=\"submit\" name=\"importmore\" value=\"  ".$mod_strings['LBL_IMPORT_MORE']."  \" id=\"importmore\">";



	    //BEGIN SUGARCRM flav!=sales ONLY
	    $submitContent .= "&nbsp;".$prospectlistbutton;
	    //END SUGARCRM flav!=sales ONLY
	    
	    $submitContent .= "</td></tr></table>";
        $this->ss->assign("SUBMITCONTENT",json_encode($submitContent));
        $this->ss->display('modules/Import/tpls/wizardWrapper.tpl');
    }

    protected function getListViewResults()
    {
        global $mod_strings, $current_language;
        // build listview to show imported records
        $lvf = new ListViewFacade($this->bean, $this->bean->module_dir, 0);

        $params = array();
        if(!empty($_REQUEST['orderBy']))
        {
            $params['orderBy'] = $_REQUEST['orderBy'];
            $params['overrideOrder'] = true;
            if(!empty($_REQUEST['sortOrder'])) $params['sortOrder'] = $_REQUEST['sortOrder'];
        }
        $beanname = ($this->bean->object_name == 'Case' ? 'aCase' : $this->bean->object_name);
        // add users_last_import joins so we only show records done in this import
        $params['custom_from']  = ', users_last_import';
        $params['custom_where'] = " AND users_last_import.assigned_user_id = '{$GLOBALS['current_user']->id}'
                AND users_last_import.bean_type = '{$beanname}'
                AND users_last_import.bean_id = {$this->bean->table_name}.id
                AND users_last_import.deleted = 0
                AND {$this->bean->table_name}.deleted = 0";
        $where = " {$this->bean->table_name}.id IN (
                        SELECT users_last_import.bean_id
                            FROM users_last_import
                            WHERE users_last_import.assigned_user_id = '{$GLOBALS['current_user']->id}'
                                AND users_last_import.bean_type = '{$beanname}'
                                AND users_last_import.deleted = 0 )";

        $lvf->lv->mergeduplicates = false;
        $lvf->lv->showMassupdateFields = false;
        if ( $lvf->type == 2 )
            $lvf->template = 'include/ListView/ListViewNoMassUpdate.tpl';

        $module_mod_strings = return_module_language($current_language, $this->bean->module_dir);
        $lvf->setup('', $where, $params, $module_mod_strings, 0, -1, '', strtoupper($beanname), array(), 'id');
        global $app_list_strings;
        return $lvf->display($app_list_strings['moduleList'][$this->bean->module_dir], 'main', TRUE);

    }

    protected function getListViewTableFromFile($fileName, $tableName)
    {
        $has_header = $_REQUEST['has_header'] == 'on' ? TRUE : FALSE;
        $if = new ImportFile($fileName, ",", '"', FALSE, FALSE);
        $if->setHeaderRow($has_header);
        $lv = new ImportListView($if,array('offset'=> 0), $tableName);
        return $lv->display(TRUE);
    }

    /**
     * Returns JS used in this view
     */
    private function _getJS($activeTab)
    {
        return <<<EOJAVASCRIPT

document.getElementById('undo').onclick = function(){
    
       	var success = function(data) {		
			var response = YAHOO.lang.JSON.parse(data.responseText);
			importWizardDialogDiv = document.getElementById('importWizardDialogDiv');
			importWizardDialogTitle = document.getElementById('importWizardDialogTitle');
			submitDiv = document.getElementById('submitDiv');
			importWizardDialogDiv.innerHTML = response['html'];
			importWizardDialogTitle.innerHTML = response['title'];
			submitDiv.innerHTML = response['submitContent'];
			eval(response['script']);
		} 
        var formObject = document.getElementById('importlast');
		YAHOO.util.Connect.setForm(formObject);
		var cObj = YAHOO.util.Connect.asyncRequest('POST', "index.php", {success: success, failure: success});
		
}


document.getElementById('importmore').onclick = function(){
    document.getElementById('importlast').action.value = 'Step1';
    
       	var success = function(data) {		
			var response = YAHOO.lang.JSON.parse(data.responseText);
			importWizardDialogDiv = document.getElementById('importWizardDialogDiv');
			importWizardDialogTitle = document.getElementById('importWizardDialogTitle');
			submitDiv = document.getElementById('submitDiv');
			importWizardDialogDiv.innerHTML = response['html'];
			importWizardDialogTitle.innerHTML = response['title'];
			submitDiv.innerHTML = response['submitContent'];
			eval(response['script']);
		} 
        var formObject = document.getElementById('importlast');
		YAHOO.util.Connect.setForm(formObject);
		var cObj = YAHOO.util.Connect.asyncRequest('POST', "index.php", {success: success, failure: success});
		
}

document.getElementById('finished').onclick = function(){
    document.getElementById('importlast').module.value = document.getElementById('importlast').import_module.value;
    document.getElementById('importlast').action.value = 'index';
	SUGAR.importWizard.closeDialog();
		
}

if ( typeof(SUGAR) == 'undefined' )
    SUGAR = {};
if ( typeof(SUGAR.IV) == 'undefined' )
    SUGAR.IV = {};

SUGAR.IV = {

    getTable : function(tableID, offset) {
        var callback = {
            success: function(o)
            {
                var tableKey = tableID + '_table';
                document.getElementById(tableKey).innerHTML = o.responseText;
            },
            failure: function(o) {},
        };
        var has_header = document.getElementById('importlast').has_header.value
        var url = 'index.php?action=RefreshTable&module=Import&offset=' + offset + '&tableID=' + tableID + '&has_header=' + has_header;
        YAHOO.util.Connect.asyncRequest('GET', url, callback);
    },
    togglePages : function(activePage)
    {
        var num_tabs = 3;
        var pageId = 'pageNumIW_' + activePage;
        activeDashboardPage = activePage;
        activeTab = activePage;

        //hide all pages first for display purposes
        for(var i=0; i < num_tabs; i++)
        {
            var pageDivId = 'pageNumIW_'+i+'_div';
            var pageDivElem = document.getElementById(pageDivId);
            pageDivElem.style.display = 'none';
        }

        for(var i=0; i < num_tabs; i++)
        {
            var tabId = 'pageNumIW_'+i;
            var anchorId = 'pageNumIW_'+i+'_anchor';
            var pageDivId = 'pageNumIW_'+i+'_div';

            var tabElem = document.getElementById(tabId);
            var anchorElem = document.getElementById(anchorId);
            var pageDivElem = document.getElementById(pageDivId);

            if(tabId == pageId)
            {
                tabElem.className = 'active';
                anchorElem.className = 'current';
                pageDivElem.style.display = '';
            }
            else
            {
                tabElem.className = '';
                anchorElem.className = '';
            }
        }
    }
}

SUGAR.IV.togglePages('$activeTab');


EOJAVASCRIPT;
    }
    //BEGIN SUGARCRM flav!=sales ONLY
    /**
     * Returns a button to add this list of prospects to a Target List
     *
     * @return string html code to display button
     */
    private function _addToProspectListButton() 
    {
        global $app_strings, $sugar_version, $sugar_config, $current_user;
        
        $query = "SELECT distinct prospects.id, prospects.assigned_user_id, prospects.first_name, prospects.last_name, prospects.phone_work, prospects.title,
				email_addresses.email_address email1, users.user_name as assigned_user_name
				FROM users_last_import,prospects
                LEFT JOIN users ON prospects.assigned_user_id=users.id
				LEFT JOIN email_addr_bean_rel on prospects.id = email_addr_bean_rel.bean_id and email_addr_bean_rel.bean_module='Prospect' and email_addr_bean_rel.primary_address=1 and email_addr_bean_rel.deleted=0
				LEFT JOIN email_addresses on email_addresses.id = email_addr_bean_rel.email_address_id
				WHERE users_last_import.assigned_user_id = '{$current_user->id}' AND users_last_import.bean_type='Prospect' AND users_last_import.bean_id=prospects.id
				AND users_last_import.deleted=0 AND prospects.deleted=0";
        
        $popup_request_data = array(
            'call_back_function' => 'set_return_and_save_background',
            'form_name' => 'DetailView',
            'field_to_name_array' => array(
                'id' => 'subpanel_id',
            ),
            'passthru_data' => array(
                'child_field' => 'notused',
                'return_url' => 'notused',
                'link_field_name' => 'notused',
                'module_name' => 'notused',
                'refresh_page'=>'1',
                'return_type'=>'addtoprospectlist',
                'parent_module'=>'ProspectLists',
                'parent_type'=>'ProspectList',
                'child_id'=>'id',
                'link_attribute'=>'prospects',
                'link_type'=>'default',	 //polymorphic or default
            )				
        );
    
        $popup_request_data['passthru_data']['query'] = urlencode($query);
    
        $json = getJSONobj();
        $encoded_popup_request_data = $json->encode($popup_request_data);	
    
        return <<<EOHTML
<script type="text/javascript" src="include/SubPanel/SubPanelTiles.js?s={$sugar_version}&c={$sugar_config['js_custom_version']}"></script>
<input align=right" type="button" name="select_button" id="select_button" class="button"
     title="{$app_strings['LBL_ADD_TO_PROSPECT_LIST_BUTTON_LABEL']}"
     accesskey="{$app_strings['LBL_ADD_TO_PROSPECT_LIST_BUTTON_KEY']}"
     value="{$app_strings['LBL_ADD_TO_PROSPECT_LIST_BUTTON_LABEL']}"
     onclick='open_popup("ProspectLists",600,400,"",true,true,$encoded_popup_request_data,"Single","true");' />
EOHTML;
    
    }
    //END SUGARCRM flav!=sales ONLY
}
?>
