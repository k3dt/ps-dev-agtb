<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

/**
 * Copy custom data from old KBDocument module to new KBContent module..
 */
class SugarUpgradeRenameCustom extends UpgradeScript
{
    /**
     * {@inheritDoc}
     */
    public $order = 1002;

    /**
     * {@inheritDoc}
     */
    public $type = self::UPGRADE_CUSTOM;

    /**
     * {@inheritDoc}
     */
    public $version = '7.5';

    /**
     * Map for old fields to new fields.
     * @var array
     */
    protected $fieldsViewMap = array(
        'kbdocument_name' => array(
            'name' => 'name',
            'label' => 'LBL_NAME',
        ),
        'kbdoc_approver_name' => array(
            'name' => 'kbsapprover_name',
            'label' => 'LBL_KBSAPPROVER',
        ),
        'is_external_article' => array(
            'name' => 'is_external',
            'label' => 'LBL_IS_EXTERNAL',
        ),
        'status_id' => array(
            'name' => 'status',
            'label' => 'LBL_STATUS',
        ),
        'assigned_user_name' => array(
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO',
        ),
        'exp_date' => array(
            'name' => 'exp_date',
            'label' => 'LBL_EXP_DATE',
        ),
        'body' => array(
            'name' => 'kbdocument_body',
            'label' => 'LBL_TEXT_BODY',
        ),
        'kbdocument_revision_number' => array(
            'name' => 'revision',
            'label' => 'LBL_REVISION',
        ),
        'active_date' => array(
            'name' => 'active_date',
            'label' => 'LBL_PUBLISH_DATE',
        ),
        'views_number' => array(
            'name' => 'viewcount',
            'label' => 'LBL_VIEWED_COUNT',
        ),
        'case_name' => array(
            'name' => 'kbscase_name',
            'label' => 'LBL_KBSCASE',
        ),

    );

    /**
     * {@inheritDoc}
     */
    public function run()
    {
        if (version_compare($this->from_version, '7.7.0', '<')) {

            if (file_exists('custom/modules/KBDocuments/metadata/listviewdefs.php')) {
                $this->copyListView('custom/modules/KBDocuments/metadata/listviewdefs.php');
            }

            if (file_exists('custom/modules/KBDocuments/metadata/popupdefs.php')) {
                $this->copyPopupView('custom/modules/KBDocuments/metadata/popupdefs.php');
            }

            if (file_exists('custom/modules/KBDocuments/clients/base/views/list/list.php')) {
                sugar_mkdir('custom/modules/KBContents/clients/base/views/list', null, true);
                $this->copySViews(
                    'custom/modules/KBDocuments/clients/base/views/list/list.php',
                    'custom/modules/KBContents/clients/base/views/list/list.php'
                );
            }

            if (file_exists('custom/modules/KBDocuments/clients/base/views/selection-list/selection-list.php')) {
                sugar_mkdir('custom/modules/KBContents/clients/base/views/selection-list', null, true);
                $this->copySViews(
                    'custom/modules/KBDocuments/clients/base/views/selection-list/selection-list.php',
                    'custom/modules/KBContents/clients/base/views/selection-list/selection-list.php'
                );
            }
            sugar_mkdir('custom/Extension/modules/KBContents/Ext/Language', null, true);
            foreach (glob('custom/Extension/modules/KBDocuments/Ext/Language/*.php') as $file) {
                copy($file, str_replace('/KBDocuments/', '/KBContents/', $file));
            }


            sugar_mkdir('custom/modules/KBContents/language', null, true);
            foreach (glob('custom/modules/KBDocuments/language/*.php') as $file) {
                copy($file, str_replace('/KBDocuments/', '/KBContents/', $file));
            }

            sugar_mkdir('custom/Extension/modules/KBContents/Ext/Vardefs', null, true);
            foreach (glob('custom/Extension/modules/KBDocuments/Ext/Vardefs/*.php') as $file) {
                $this->copyDefs($file, str_replace('/KBDocuments/', '/KBContents/', $file));
            }
            $query = "UPDATE fields_meta_data set custom_module = 'KBContents' where custom_module = 'KBDocuments'";
            $this->db->query($query);
        }
    }

    /**
     * Copy definition for list view.
     * @param String $file File with view definition.
     */
    protected function copyListView($file)
    {
        $listViewDefs = array();
        sugar_mkdir('custom/modules/KBContents/metadata', null, true);
        include $file;
        $listViewDefs['KBContents'] = $this->checkDefs($listViewDefs['KBDocuments']);
        write_array_to_file(
            "listViewDefs['KBContents']",
            $listViewDefs['KBContents'],
            'custom/modules/KBContents/metadata/listviewdefs.php'
        );
    }

    /**
     * Copy definition for popup view.
     * @param String $file File with view definition.
     */
    protected function copyPopupView($file)
    {
        $popupMeta = array();
        sugar_mkdir('custom/modules/KBContents/metadata', null, true);
        include $file;
        $popupMeta['moduleMain'] = 'KBContents';
        $popupMeta['varName'] = 'KBContents';
        $popupMeta['orderBy'] = 'kbcontents.name';
        $popupMeta['whereClauses'] = array('name' => 'kbcontents.name');
        $popupMeta['listviewdefs'] = $this->checkDefs($popupMeta['listviewdefs']);
        if (!empty($popupMeta['searchdefs'])) {
            $popupMeta['searchdefs'] = $this->checkDefs($popupMeta['searchdefs']);
        }
        if (!empty($popupMeta['searchInputs'])) {
            $popupMeta['searchInputs'] = $this->checkDefs($popupMeta['searchInputs']);
        }
        write_array_to_file(
            "popupMeta",
            $popupMeta,
            'custom/modules/KBContents/metadata/popupdefs.php'
        );
    }

    /**
     * @param String $file File with view definition.
     * @param String $dest New file destination.
     */
    protected function copySViews($file, $dest)
    {
        $viewdefs = array();
        $cname = str_replace('custom/', '', $dest);
        list($modules, $module_name, $clients, $platform, $views, $viewname) = explode('/', $cname);
        include $file;
        foreach ($viewdefs['KBDocuments'][$platform]['view'][$viewname]['panels'] as &$panel) {
            $panel['fields'] = $this->checkDefs($panel['fields']);
        }
        write_array_to_file(
            "viewdefs['KBContents']",
            $viewdefs['KBDocuments'],
            $dest
        );
        //need to merge
        $this->upgrader->state['for_merge'][$cname] = $viewdefs['KBDocuments'][$platform]['view'][$viewname];
        $cname = str_replace('custom/', '', $file);
        if (!empty($this->upgrader->state['for_merge'][$cname])) {
            unset($this->upgrader->state['for_merge'][$cname]);
        }
    }

    /**
     * Copy fields definitions.
     * @param String $file File with variable definition.
     * @param String $dest New destination.
     */
    protected function copyDefs($file, $dest)
    {
        $content = file_get_contents($file);
        $content = str_replace(
            array("'KBDocument'", '"KBDocument"'),
            array("'KBContent'", '"KBContent"'),
            $content
        );
        file_put_contents($dest, $content);
    }

    /**
     * Replace old field definitions with new one.
     * @param array $defs View definition.
     * @return array
     */
    protected function checkDefs($defs)
    {
        $result = array();
        foreach ($defs as $key => $def) {
            $name = strtolower(!empty($def['name']) ? $def['name'] : $key);
            if (!empty($this->fieldsViewMap[$name])) {
                $def = array_merge($def, $this->fieldsViewMap[$name]);
                $name = strtolower($this->fieldsViewMap[$name]['name']);
            }
            $key = strtoupper($name);
            $result[$key] = $def;
        }
        return $result;
    }
}
