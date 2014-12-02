<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'data/BeanFactory.php';
require_once 'clients/base/api/vCardApi.php';
require_once 'modules/pmse_Inbox/engine/PMSEProjectImporter.php';
require_once 'modules/pmse_Inbox/engine/PMSEProjectExporter.php';

class PMSEProjectImportExportApi extends vCardApi
{
    /**
     *
     * @return type
     */
    public function registerApiRest()
    {
        return array(
            'projectImportPost' => array(
                'reqType' => 'POST',
                'path' => array('pmse_Project', 'file', 'project_import'),
                'pathVars' => array('module', '', ''),
                'method' => 'projectImport',
                'rawPostContents' => true,
                'shortHelp' => 'Imports a project record from a bpm file',
                'longHelp' => 'modules/ProcessMaker/api/help/file_project_import_post_help.html',
            ),
            'projectDownload' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Project', '?', 'dproject'),
                'pathVars' => array('module', 'record', ''),
                'method' => 'projectDownload',
                'rawReply' => true,
                'allowDownloadCookie' => true,
                'shortHelp' => 'An API to download a contact as a vCard.',
                'longHelp' => 'modules/ProcessMaker/api/help/module_projectdownload_get_help.html',
            ),
        );
    }

    public function projectDownload($api, $args)
    {
        $projectBean = new PMSEProjectExporter();
        $requiredFields = array('record', 'module');
        foreach ($requiredFields as $fieldName) {
            if (!array_key_exists($fieldName, $args)) {
                throw new SugarApiExceptionMissingParameter('Missing parameter: ' . $fieldName);
            }
        }

        return $projectBean->exportProject($args['record'], $api);
    }

    public function projectImport($api, $args)
    {
        $this->requireArgs($args, array('module'));

        $bean = BeanFactory::getBean($args['module']);
        if (!$bean->ACLAccess('save') || !$bean->ACLAccess('import')) {
            throw new SugarApiExceptionNotAuthorized('EXCEPTION_NOT_AUTHORIZED');
        }

        if (isset($_FILES) && count($_FILES) === 1) {
            reset($_FILES);
            $first_key = key($_FILES);
            if (isset($_FILES[$first_key]['tmp_name'])
                && $this->isUploadedFile($_FILES[$first_key]['tmp_name'])
                && isset($_FILES[$first_key]['size'])
                && isset($_FILES[$first_key]['size']) > 0
            ) {
                try {
                    //here do the projectImport calls
                    $importerObject = new PMSEProjectImporter();
                    $data = $importerObject->importProject($_FILES[$first_key]['tmp_name']);
                    $results = array('project_import' => $data);
                } catch (Exception $e) {
                    throw new SugarApiExceptionRequestMethodFailure('ERR_VCARD_FILE_PARSE');
                }

                return $results;
            }
        } else {
            throw new SugarApiExceptionMissingParameter('ERR_VCARD_FILE_MISSING');
        }
    }
}