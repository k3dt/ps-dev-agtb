<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

use PHPUnit\Framework\TestCase;

class Bug46325Test extends TestCase
{
    public $parser;
    public $fields;
    public $accountsFile;
    public $prospectsFile;

    protected function setUp() : void
    {
        $this->fields = [
            'name' => [
                'width' => '40%',
                'label' => 'LBL_LIST_ACCOUNT_NAME',
                'link' => 1,
                'default' => 1,
                'name' => 'name',
            ],
        ];

        require 'include/modules.php';
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $this->accountsFile = 'custom/modules/Accounts/metadata/popupdefs.php';
        $this->prospectsFile = 'custom/modules/Prospects/metadata/popupdefs.php'; // Add in base/views when ready
    }

    protected function tearDown() : void
    {
        if (is_file($this->accountsFile)) {
            unlink($this->accountsFile);
        }
        if (is_file($this->prospectsFile)) {
            unlink($this->prospectsFile);
        }
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['app_list_strings']);
    }

    /**
     * @outputBuffering enabled
     */
    public function testUpdateCustomAccountMetadataPopupdefsSave()
    {
        $this->parser = new PopupMetaDataParser('popuplist', 'Accounts');
        $this->parser->_viewdefs = $this->fields;
        $this->parser->handleSave(false);
        require $this->accountsFile;
        $this->assertEquals('LNK_NEW_ACCOUNT', $popupMeta['create']['createButton']);
        unset($popupMeta);
        unset($this->parser);
    }

    /**
     * @outputBuffering enabled
     */
    public function testUpdateCustomProspectsMetadataPopupdefsSave()
    {
        $this->parser = new PopupMetaDataParser('popuplist', 'Prospects');
        $this->parser->_viewdefs = $this->fields;
        $this->parser->handleSave(false);
        require $this->prospectsFile;
        $this->assertEquals('LNK_NEW_PROSPECT', $popupMeta['create']['createButton']);
        unset($popupMeta);
        unset($this->parser);
        // this is to suppress output. Need to fix properly with a good unit test.
        $this->expectOutputRegex('//');
    }
}
