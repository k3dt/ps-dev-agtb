<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
$module_name = 'pmse_Project';
$viewdefs[$module_name ]['base']['view']['recordlist'] = array(
    'favorite' => true,
    'following' => true,
    'selection' => array(
        'type' => 'multi',
        'actions' => array(
//            array(
//                'name' => 'edit_button',
//                'type' => 'button',
//                'label' => 'LBL_MASS_UPDATE',
//                'primary' => true,
//                'events' => array(
//                    'click' => 'list:massupdate:fire',
//                ),
//                'acl_action' => 'massupdate',
//            ),
//            array(
//                'name' => 'calc_field_button',
//                'type' => 'button',
//                'label' => 'LBL_UPDATE_CALC_FIELDS',
//                'events' => array(
//                    'click' => 'list:updatecalcfields:fire',
//                ),
//                'acl_action' => 'massupdate',
//            ),
//            array(
//                'name' => 'merge_button',
//                'type' => 'button',
//                'label' => 'LBL_MERGE',
//                'primary' => true,
//                'events' => array(
//                    'click' => 'list:mergeduplicates:fire',
//                ),
//                'acl_action' => 'edit',
//            ),
            array(
                'name' => 'delete_button',
                'type' => 'button',
                'label' => 'LBL_DELETE',
                'acl_action' => 'delete',
                'primary' => true,
                'events' => array(
                    'click' => 'list:massdelete:fire',
                ),
            ),
            array(
                'name' => 'export_button',
                'type' => 'button',
                'label' => 'LBL_EXPORT',
                'acl_action' => 'export',
                'primary' => true,
                'events' => array(
                    'click' => 'list:massexport:fire',
                ),
            ),
        ),
    ),
    'rowactions' => array(
        'actions' => array(
            array(
                'type' => 'rowaction',
                'css_class' => 'btn',
                'tooltip' => 'LBL_PREVIEW',
                'event' => 'list:preview:fire',
                'icon' => 'fa-eye',
                'acl_action' => 'view',
            ),
            array(
                'type' => 'rowaction',
                'name' => 'designer_button',
                'label' => 'LBL_PMSE_LABEL_DESIGN',
                'event' => 'list:opendesigner:fire',
                'acl_action' => 'view',
            ),
            array(
                'type' => 'rowaction',
                'name' => 'edit_button',
                'label' => 'LBL_EDIT_BUTTON',
                'event' => 'list:editrow:fire',
                'acl_action' => 'edit',
            ),
//            array(
//                'type' => 'follow',
//                'name' => 'follow_button',
//                'event' => 'list:follow:fire',
//                'acl_action' => 'view',
//            ),
            array(
                'type' => 'rowaction',
                'name' => 'export_button',
                'label' => 'LBL_PMSE_LABEL_EXPORT',
                'event' => 'list:exportprocess:fire',
                'acl_action' => 'view',
            ),
            array(
                'type' => 'rowaction',
                'name' => 'delete_button',
                'event' => 'list:deleterow:fire',
                'label' => 'LBL_DELETE_BUTTON',
                'acl_action' => 'delete',
            ),
            array(
                'type' => 'enabled',
                'name' => 'enabled_button',
                'event' => 'list:enabledRow:fire',
                'label' => 'LBL_PMSE_LABEL_ENABLE',
                'acl_action' => 'delete',
            ),
            array(
                'type' => 'disabled',
                'name' => 'disabled_button',
                'event' => 'list:disabledRow:fire',
                'label' => 'LBL_PMSE_LABEL_DISABLE',
                'acl_action' => 'delete',
            ),
        ),
    ),
    'last_state' => array(
        'id' => 'record-list',
    ),
);
