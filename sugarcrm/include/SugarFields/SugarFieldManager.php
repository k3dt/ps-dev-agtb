<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dtam
 * Date: 2/21/12
 * Time: 9:20 AM
 * To change this template use File | Settings | File Templates.
 */
ini_set('display_errors', '1');
/*
class SugarFieldManager
{

    function buildFields()
    {
        $fieldFileTypes2meta = array('hbt'=>'template','js'=>'js');
        $result = array();
        $fieldsDirectory = "PortalFields/";
        $portalFiles = getFiles($fieldsDirectory);
        //var_dump($portalFiles);
        foreach ($portalFiles as $fname) {
            $build = false;
            $fieldMeta = '';

            // get file info
            $namePieces = explode('/', $fname);
            $fieldName = "";
            if ($namePieces[1]) {
                $fieldName = $namePieces[1];
            }

            $action = "";
            $fileExtension = "";
            if ($namePieces[2]) {
                $filePieces = explode(".", $namePieces[2]);
                if ($filePieces[0]) {
                    $action = $filePieces[0];
                }
                if ($filePieces[1]) {
                    $fileExtension = $filePieces[1];
                }
            }

            if (in_array($fileExtension, array_keys($fieldFileTypes2meta))) {
                $build = true;
                $fieldMeta = $fieldFileTypes2meta[$fileExtension];
            }

            if ($build) {
                if (!isset($result[$fieldName])) {
                    $result[$fieldName] = array();
                }
                if (!isset($result[$fieldName][$action])) {
                    $result[$fieldName][$action] = array();
                }
                $fieldFragmentArray = array($fieldMeta=>file_get_contents($fname));
                $result[$fieldName][$action] = array_merge($result[$fieldName][$action], $fieldFragmentArray) ;
            }

            $result['md5'] = md5(serialize($result));
        }
        //var_dump($result);
        return $result;
    }

}

function getFiles($directory, $exempt = array('.', '..', '.ds_store', '.svn'), &$files = array(), $exempt_extensions = array('tpl', 'php'))
{
    $handle = opendir($directory);
    while (false !== ($resource = readdir($handle))) {
        if (!in_array(strtolower($resource), $exempt)) {
            if (is_dir($directory . $resource . '/')) {
                array_merge($files,
                    getFiles($directory . $resource . '/', $exempt, $files));
            }
            else {
                $resourceParts = explode('.', $resource);
                $extension = end($resourceParts);
                if ($extension && !in_array($extension, $exempt_extensions)) {
                    $files[] = $directory . $resource;
                }
            }
        }
    }
    closedir($handle);
    return $files;
}
**/
//var_dump(json_decode($target)->fieldsData);
echo getcwd() . PHP_EOL;
chdir('../../');
echo getcwd();
include('include/MetaDataManager/MetaDataManager.php');
$s = new MetaDataManager();
$fields = $s->getSugarFields();
var_dump($fields);
//echo json_encode($fields);