<?php

require_once('modules/pmse_Inbox/engine/PMSEProjectImporter.php');

class CustomBPMProjectImporter extends PMSEProjectImporter
{
    /**
     * Function to get a data for File uploaded
     * @param $file
     * @return mixed
     */
    public function getDataFile($file)
    {
        include($file);
        return $fileContent;
    }
}
