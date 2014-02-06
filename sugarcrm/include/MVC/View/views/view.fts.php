<?php
//FILE SUGARCRM flav=pro ONLY
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

require_once('include/MVC/View/SugarView.php');
require_once('include/SugarSearchEngine/SugarSearchEngineFactory.php');
require_once('include/SugarSearchEngine/SugarSearchEngineMetadataHelper.php');

class ViewFts extends SugarView
{
    private $fullView = FALSE;
    private $templateName = '';
    private $rsTemplateName = 'fts_full_rs.tpl';

    public function __construct()
    {
        $this->fullView = !empty($_REQUEST['full']) ? TRUE : FALSE;

        if($this->fullView)
        {
            $this->options = array('show_title'=> true,'show_header'=> true,'show_footer'=> true,'show_javascript'=> true,'show_subpanels'=> false,'show_search'=> false);
            $this->templateName = 'fts_full.tpl';
        }
        else
        {
            $this->options = array('show_title'=> false,'show_header'=> false,'show_footer'=> false,'show_javascript'=> false,'show_subpanels'=> false,'show_search'=> false);
            $this->templateName = 'fts_spot.tpl';
        }
        parent::__construct();

    }
    /**
     * @see SugarView::display()
     */
    public function display($return = false, $encode = false)
    {
        $offset = isset($_REQUEST['offset']) ? $_REQUEST['offset'] : 0;
        $resultSetOnly = !empty($_REQUEST['rs_only']) ? $_REQUEST['rs_only'] : FALSE;
        $refreshModuleFilter = !empty($_REQUEST['refreshModList']) ? $_REQUEST['refreshModList'] : FALSE;

        $limit = ( !empty($GLOBALS['sugar_config']['max_spotresults_initial']) ? $GLOBALS['sugar_config']['max_spotresults_initial'] : 10 );
        $indexOffset = $offset / $limit;
        $moduleFilter = !empty($_REQUEST['m']) ? $_REQUEST['m'] : false;
        if (!empty($moduleFilter) && is_scalar($moduleFilter)) {
            $moduleFilter = str_getcsv($moduleFilter);
        }
        $disabledModules = !empty($_REQUEST['disabled_modules']) ? str_getcsv($_REQUEST['disabled_modules']) : array();
        //If no modules have been passed in then lets check user preferences.
        if ($moduleFilter === false) {
            $moduleFilter = SugarSearchEngineMetadataHelper::getUserEnabledFTSModules();
        }
        $filteredModules =  $this->getFilterModules($moduleFilter, $disabledModules);
        $append_wildcard = !empty($_REQUEST['append_wildcard']) ? $_REQUEST['append_wildcard'] : false;
        $options = array(
            'current_module' => $this->module,
            'moduleFilter' => $moduleFilter,
            'append_wildcard' => $append_wildcard,
            'sort' => array(array('module' => array('order' => 'asc'))),
        );

        if( $this->fullView || $refreshModuleFilter)
        {
            $options['apply_module_facet'] = TRUE;
        }

        $searchEngine = SugarSearchEngineFactory::getInstance();
        $queryString = !empty($_REQUEST['q']) ? $_REQUEST['q'] : '';
        $trimmed_query = trim($queryString);
        $rs = $searchEngine->search($trimmed_query, $offset, $limit, $options);
        if($rs == null)
        {
            $totalHitsFound = 0;
            $totalTime = 0;
            $hitsByModule = array();
        }
        else
        {
            $totalHitsFound = $rs->getTotalHits();
            $totalTime = $rs->getTotalTime();
            $hitsByModule = $rs->getModuleFacet();
        }
        $query_encoded = urlencode($trimmed_query);

        if (count($filteredModules['enabled']) != count($moduleFilter)) {
            // if there is a full module list we need to check "Show all"
            $this->ss->assign('moduleFilter', $moduleFilter);
        }
        $showMoreDivStyle = ($totalHitsFound > $limit) ? '' : "display:none;";
        $this->ss->assign('showMoreDivStyle', $showMoreDivStyle);
        $this->ss->assign('indexOffset', $indexOffset);
        $this->ss->assign('offset', $offset);
        $this->ss->assign('limit', $limit);
        $this->ss->assign('totalHits', $totalHitsFound);
        $this->ss->assign('totalTime', $totalTime);
        $this->ss->assign('queryEncoded', $query_encoded);
        $this->ss->assign('resultSet', $rs);
        $this->ss->assign('APP_LIST', $GLOBALS['app_list_strings']);
        $template = SugarAutoLoader::existingCustomOne("include/MVC/View/tpls/{$this->templateName}");
        $rsTemplate = SugarAutoLoader::existingCustomOne("include/MVC/View/tpls/{$this->rsTemplateName}");
        $this->ss->assign('rsTemplate', $rsTemplate);

        if( $this->fullView )
        {
            $this->ss->assign(
                'filterModules',
                $this->filterModuleListByTypes($filteredModules['enabled'], $hitsByModule, $moduleFilter)
            );
            if($resultSetOnly)
            {
                $out = array('results' => $this->ss->fetch($rsTemplate), 'totalHits' => $totalHitsFound, 'totalTime' => $totalTime);
                if( $refreshModuleFilter )
                    $out['mod_filter'] = $this->ss->fetch('include/MVC/View/tpls/fts_modfilter.tpl');

                return $this->sendOutput(json_encode($out));
            }

            $this->ss->assign('enabled_modules', json_encode($filteredModules['enabled']));
            $this->ss->assign('disabled_modules', json_encode($filteredModules['disabled']));
        }

        $contents = $this->ss->fetch($template);
        return $this->sendOutput($contents, $return, $encode);

    }

    /**
     * Given the enable module list and a facet result set for the last query, add
     * a count to the filter module list.
     *
     * @param $modulelist
     * @param $facetResults
     * @param $moduleFilter array list of searched modules
     * @return mixed
     */
    protected function filterModuleListByTypes($modulelist, $facetResults, $moduleFilter)
    {
        if($facetResults === FALSE)
            return $modulelist;

        foreach($modulelist as &$moduleEntry)
        {
            if( isset($facetResults[$moduleEntry['module']]) )
                $moduleEntry['count'] = $facetResults[$moduleEntry['module']];
            else
            {
                if (empty($moduleFilter) || in_array($moduleEntry['module'], $moduleFilter)) {
                    $moduleEntry['count'] = 0;
                }
                else
                {
                    $moduleEntry['count'] = '';
                }
            }
        }

        return $modulelist;
    }


    protected function sendOutput($contents, $return = false, $encode = false)
    {
        if($encode)
            $contents = json_encode(array('results' => $contents));
        if($return)
            return $contents;
        else
            echo $contents;
    }

    /**
     * Get the enabled and disabled modules for the datatable
     *
     * @param $moduleFilter array Requested modules for search
     * @param $disabledModules array Requested modules for disable in search
     * @return array
     */
    protected function getFilterModules($moduleFilter, $disabledModules)
    {
        $filteredEnabled = SugarSearchEngineMetadataHelper::getUserEnabledFTSModules();
        $userDisabled = $GLOBALS['current_user']->getPreference('fts_disabled_modules');
        $userDisabled = explode(",", $userDisabled);

        // Filter by System enabled FTS modules
        $systemEnabledModules = SugarSearchEngineMetadataHelper::getSystemEnabledFTSModules();
        $userDisabled = array_intersect_key($systemEnabledModules, array_flip($userDisabled));
        $filteredEnabled = array_intersect_key($systemEnabledModules, array_flip($filteredEnabled));

        $userDisabled = $this->translateModulesList($userDisabled);
        $filteredEnabled = $this->translateModulesList($filteredEnabled);
        sort($filteredEnabled);

        if (!empty($moduleFilter)) {
            foreach ($filteredEnabled as $key => $info) {
                if (!in_array($info['module'], $moduleFilter) && in_array($info['module'], $disabledModules)) {
                    unset($filteredEnabled[$key]);
                    // its not enabled, its disabled
                    $userDisabled = $info;
                }
            }
        }

        return array('enabled' => $filteredEnabled, 'disabled' => $userDisabled);
    }

    /**
     * Translate a list of modules to the format expected by our YUI datatables.
     *
     * @param $module
     * @return array
     */
    protected function translateModulesList($module)
    {
        $modulesTranslated = array();
        asort($module);
        foreach($module as $m)
        {
            $moduleName = isset($GLOBALS['app_list_strings']['moduleList'][$m]) ? $GLOBALS['app_list_strings']['moduleList'][$m] : $m;
            $modulesTranslated[] = array('module'=> $m, 'label' => $moduleName);
        }
        return $modulesTranslated;
    }
}

