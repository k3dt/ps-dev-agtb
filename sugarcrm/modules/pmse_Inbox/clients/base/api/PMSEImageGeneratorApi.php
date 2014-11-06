<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

//require_once 'clients/base/api/FileApi.php';
require_once 'clients/base/api/FileTempApi.php';
require_once 'modules/pmse_Inbox/engine/PMSEImageGenerator.php';

/**
 * API Class to handle temporary image (attachment) interactions with a field in
 * a bean that can be new, so no record id is associated yet.
 */
class PMSEImageGeneratorApi extends FileTempApi {
    /**
     * Dictionary registration method, called when the API definition is built
     *
     * @return array
     */
    public function registerApiRest() {
        return array(
            'getFileContents' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Inbox', '?', 'file', '?'),
                'pathVars' => array('module', 'record', '', 'field'),
                'method' => 'getFile',
                'rawReply' => true,
                'allowDownloadCookie' => true,
                'shortHelp' => 'Gets the contents of a single file related to a field for a module record.',
                'longHelp' => 'include/api/help/module_record_file_field_get_help.html',
            ),
            'getTempImage' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Inbox', 'temp', 'file', '?', '?'),
                'pathVars' => array('module', 'record', '', 'field', 'temp_id'),
                'method' => 'getTempImage',
                'rawReply' => true,
                'allowDownloadCookie' => true,
                'shortHelp' => 'Reads a temporary image and deletes it.',
                'longHelp' => 'include/api/help/module_temp_file_field_temp_id_get_help.html',
            ),
        );
    }
    /**
     * Gets a single file for rendering
     *
     * @param ServiceBase $api The service base
     * @param array $args Arguments array built by the service base
     * @return string
     * @throws SugarApiExceptionMissingParameter|SugarApiExceptionNotFound
     */
    public function getFile($api, $args) {
        $this->getProcessImage($api, $args);
        $args['temp_id'] = $args['record'];
        parent::getTempImage($api, $args);
    }

    /**
     * Gets a single temporary file for rendering and removes it from filesystem.
     *
     * @param ServiceBase $api The service base
     * @param array $args Arguments array built by the service base
     * @return array
     */
    public function getTempImage($api, $args)
    {
        parent::getTempImage($api, $args);
    }
    private function getProcessImage($api, $args)
    {
        $path = 'upload://tmp/';
        $image = new PMSEImageGenerator();
        $img = $image->get_image($args['record']);
        $file = new UploadStream();
        if (!$file->checkDir($path)){
            $file->createDir($path);
        }
        $file_path = UploadFile::realpath($path) . '/' . $args['record'];
        imagepng($img, $file_path);
        imagedestroy($img);
    }
}

