<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (\“MSA\”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
$viewdefs['Tasks']['base']['view']['subpanel-list'] = array(
  'panels' =>
  array(
    array(
      'name' => 'panel_header',
      'label' => 'LBL_PANEL_1',
      'fields' =>
      array(
        array(
          'label' => 'LBL_LIST_SUBJECT',
          'enabled' => true,
          'default' => true,
          'link' => true,
          'name' => 'name',
        ),
        array(
          'label' => 'LBL_LIST_STATUS',
          'enabled' => true,
          'default' => true,
          'name' => 'status',
        ),
        array(
          'target_record_key' => 'contact_id',
          'target_module' => 'Contacts',
          'label' => 'LBL_LIST_CONTACT',
          'enabled' => true,
          'default' => true,
          'name' => 'contact_name',
        ),
        array(
          'name' => 'date_start',
          'label' => 'LBL_LIST_START_DATE',
          'enabled' => true,
          'default' => true,
        ),
        array(
          'name' => 'date_due',
          'label' => 'LBL_LIST_DUE_DATE',
          'enabled' => true,
          'default' => true,
        ),
        array(
          'name' => 'assigned_user_name',
          'label' => 'LBL_LIST_ASSIGNED_TO_NAME',
          'id' => 'ASSIGNED_USER_ID',
          'enabled' => true,
          'default' => true,
          'sortable' => false,
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
        'icon' => 'icon-eye-open',
        'acl_action' => 'view',
        'allow_bwc' => false,
      ),
      array(
        'type' => 'rowaction',
        'name' => 'edit_button',
        'icon' => 'icon-pencil',
        'label' => 'LBL_EDIT_BUTTON',
        'event' => 'list:editrow:fire',
        'acl_action' => 'edit',
        'allow_bwc' => false,
      ),
      array(
        'type' => 'unlink-action',
        'icon' => 'icon-trash',
        'label' => 'LBL_UNLINK_BUTTON',
      ),
      array(
        'type' => 'closebutton',
        'name' => 'record-close',
        'label' => 'LBL_CLOSE_BUTTON_TITLE',
        'acl_action' => 'edit',
      ),
    ),
  ),
);
