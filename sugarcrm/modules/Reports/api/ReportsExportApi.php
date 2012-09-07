<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
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

require_once('data/BeanFactory.php');
require_once('include/download_file.php');
require_once('modules/Reports/ReportCache.php');
/**
 * @api
 */
class ReportsExportApi extends SugarApi {
    // how long the cache is ok, in minutes
    private $cacheLength = 10;

    public function registerApiRest() {
        return array(
            'exportRecord' => array(
                'reqType' => 'GET',
                'path' => array('Reports', '?', '?'),
                'pathVars' => array('module', 'record', 'export_type'),
                'method' => 'exportRecord',
                'shortHelp' => 'This method exports a record in the specified type',
                'longHelp' => '',
            ),
        );
    }

    /**
     * Export Report records into various files, now just pdf
     * @param ServiceBase $api The service base
     * @param array $args Arguments array built by the service base
     * @return binary file
     */
    public function exportRecord($api, $args)
    {

        $this->requireArgs($args,array('record', 'export_type'));
        $args['module'] = 'Reports';
        $GLOBALS['disable_date_format'] = FALSE;

        // first lets check thecache  and see if this user already has a report for the export type we want
        
        $filename = sugar_cache_retrieve($args['record'] . '-' . $GLOBALS['current_user']->id);


        if(empty($filename)) {
            $saved_report = $this->loadBean($api, $args, 'view');

            $method = 'export' . ucwords($args['export_type']);

            if(!method_exists($this, $method)) {
                throw new SugarApiExceptionNoMethod('Export Type Does Not Exists');
            }

            $filename = $this->$method($saved_report);

        }


        $dl = new DownloadFile();
        $contents = $dl->getFileByFilename($filename);
        if(empty($contents)) {
            throw new SugarApiException('File contents empty.');
        }
        return array('file_contents' => base64_encode($contents));
    }

    /**
     * Export a PDF Report
     * @param SugarBean report
     * @return file contents
     */
    protected function exportPdf(SugarBean $report)
    {
        global  $beanList, $beanFiles;
        global $sugar_config,$current_language;
        require_once('modules/Reports/templates/templates_pdf.php');
        $report_filename = false;
        if($report->id != null)
        {
            //Translate pdf to correct language
            $mod_strings = return_module_language($current_language, 'Reports');

            //Generate actual pdf
            // TODO: Add caching here
            $report_filename = template_handle_pdf($report, false);
            sugar_cache_put($report->id . '-' . $GLOBALS['current_user']->id, $report_filename, $this->cacheLength * 60);
        }
        return $report_filename;
    }
}