<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

require_once 'tests/rest/RestTestBase.php';
require_once 'modules/ModuleBuilder/parsers/views/SidecarGridLayoutMetaDataParser.php';

/**
 * Bug 57887 - Changes to mobile layouts do not take effect
 */
class RestBug57887Test extends RestTestBase
{
    /**
     * Test view defs
     *
     * @var array
     */
    protected $_newDefs = array(
        'LBL_PANEL_DEFAULT' => array(
            array('name', '(empty)'),
            array('phone_office', '(empty)'),
            array('date_modified', '(empty)'),
        ),
    );

    /**
     * Custom file to be checked and deleted
     * @var string
     */
    protected $_metadataFile = 'custom/modules/Accounts/clients/mobile/views/detail/detail.php';

    /**
     * List of backed up metadata caches
     *
     * @var array
     */
    protected $_backedUp = array();

    public function setUp()
    {
        parent::setUp();

        // Backup existing files if needed
        SugarTestHelper::saveFile($this->_metadataFile);
        @SugarAutoLoader::unlink($this->_metadataFile);

        $dir = $this->getMetadataCacheDir();
        $tempdir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . '/';
        $files = glob($dir . '*.php');
        foreach ($files as $file) {
            $filename = $tempdir . basename($file);
            if (rename($file, $filename)) {
                $this->_backedUp[$filename] = $file;
            }
        }
        
        // Add the current metadata file to the restore list
        if (file_exists($this->_metadataFile)) {
            $filename = $tempdir . basename($this->_metadataFile);
            $this->_backedUp[$filename] = $this->_metadataFile;
        }
    }

    public function tearDown()
    {
        // Clear the cache
        $this->_clearMetadataCache();

        // Restore the backups
        foreach ($this->_backedUp as $temp => $file) {
            rename($temp, $file);
        }

        // Wipe out the custom file if there is one

        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testCacheIsRefreshedAfterLayoutIsSaved()
    {
        // Build the cache
        $mm = MetaDataManager::getManager('mobile');
        $data = $mm->getMetadata();
        $cacheFile = $mm->getMetadataCacheFileName();

        // Confirm cache file exists
        $this->assertFileExists($cacheFile, "The cache metadata file does not exist");

        // Confirm custom file does not exist in the file map cache
        $exists = SugarAutoLoader::fileExists($this->_metadataFile);
        $this->assertFalse($exists, "The custom file was found in the file map cache");

        // Make a change to the layouts using the parsers
        $parser = new SidecarGridLayoutMetaDataParser(MB_WIRELESSDETAILVIEW, 'Accounts', '', MB_WIRELESS);
        $parser->_viewdefs['panels'] = $this->_newDefs;
        $parser->handleSave(false);

        // Confirm custom file is in the file map cache
        $exists = (bool) SugarAutoLoader::fileExists($this->_metadataFile);
        $this->assertTrue($exists, "The custom file was not found in the file map cache");

        // Confirm metadata cache is now updated and that the change was picked 
        // up and returned accordingly
        $mm = MetaDataManager::getManager('mobile');
        $data = $mm->getMetadata();
        $panels = $data['modules']['Accounts']['views']['detail']['meta']['panels'];
        $fields = $panels[0]['fields'];
        $this->assertEquals(3, count($fields), "Fields array should only contain 3 elements");
        $this->assertEquals('date_modified', $fields[2]['name'], "The third field name should be date_modified");
    }

    protected function getMetadataCacheDir()
    {
        return sugar_cached('api/metadata/');
    }
}