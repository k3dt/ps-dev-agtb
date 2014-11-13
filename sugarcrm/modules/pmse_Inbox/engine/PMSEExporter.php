<?php

/**
 * Exports a record of a table in the database
 *
 * This class exports a record from a table in the database to a file
 * by encrypting its contents, to be transported from one instance to another.
 * @package PMSE
 */
class PMSEExporter {
    /**
     * @var $bean
     * @access private
     */
    protected $bean;
    /**
     * @var $id
     * @access private
     */
    protected $id;
    /**
     * @var $uid
     * @access private
     */
    protected $uid;
    /**
     * @var $name
     * @access private
     */
    protected $name;
    /**
     * @var $extension
     * @access private
     */
    protected $extension;

    /**
     * Set Bean.
     * @codeCoverageIgnore
     * @param object $bean
     * @return void
     */
    public function setBean($bean)
    {
        $this->bean = $bean;
    }

    /**
     * Set UID.
     * @codeCoverageIgnore
     * @param string $uid
     * @return void
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    /**
     * Set Name.
     * @codeCoverageIgnore
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Set Extension.
     * @codeCoverageIgnore
     * @param string $extension
     * @return void
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;
    }

    /**
     * Method to download a file exported
     * @codeCoverageIgnore
     */
    public function exportProject($id , ServiceBase $api)
    {
        $projectContent = $this->getProject(array('id' => $id));
        //File Name
        $filename = str_replace(' ', '_', $projectContent['project'][$this->name]) . '.' . $this->extension;

        $api->setHeader("Content-Disposition","attachment; filename=" . $filename);
        $api->setHeader("Content-Type","application/" . $this->extension);
        $api->setHeader("Expires","Mon, 26 Jul 1997 05:00:00 GMT" );
        $api->setHeader("Last-Modified", TimeDate::httpTime() );
        $api->setHeader("Cache-Control","max-age=0");
        $api->setHeader("Pragma","public");

        return serialize($projectContent);
    }

    /**
     * Method to retrieve a record of the database to export.
     * @param array $args
     * @return array
     */
    public function getProject(array $args)
    {
        $this->bean->retrieve($args['id']);

        if ($this->bean->fetched_row != false) {
            return array("metadata" => $this->getMetadata(), "project" => $this->bean->fetched_row);
        } else {
            return array("error" => true);
        }
    }

    /**
     * Method to retrieve a metadata
     * @return object
     */
    public function getMetadata()
    {
        global $sugar_flavor;
        global $sugar_version;
        global $sugar_config;
        //$pmseSingleton = PMSE::getInstance();
        $pmseName = 'ProcessMaker'; //$pmseSingleton->getModuleName();
        $pmseVersion = '2.0'; //$pmseSingleton->getVersion();
        $metadataObject = new stdClass();
        $metadataObject->SugarCRMFlavor = $sugar_flavor;
        $metadataObject->SugarCRMVersion = $sugar_version;
        $metadataObject->SugarCRMHost = $sugar_config['host_name'];
        $metadataObject->SugarCRMUrl = $sugar_config['site_url'];
        $metadataObject->PMSEName = $pmseName;
        $metadataObject->PMSEVersion = $pmseVersion;
        $metadataObject->ExportDate = date('Y-m-d H:i:s');
        return $metadataObject;
    }
}