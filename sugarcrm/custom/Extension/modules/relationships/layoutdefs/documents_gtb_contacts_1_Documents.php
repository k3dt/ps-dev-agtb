<?php
 // created: 2020-11-19 20:56:33
$layout_defs["Documents"]["subpanel_setup"]['documents_gtb_contacts_1'] = array (
  'order' => 100,
  'module' => 'gtb_contacts',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_DOCUMENTS_GTB_CONTACTS_1_FROM_GTB_CONTACTS_TITLE',
  'get_subpanel_data' => 'documents_gtb_contacts_1',
  'top_buttons' => 
  array (
    0 => 
    array (
      'widget_class' => 'SubPanelTopButtonQuickCreate',
    ),
    1 => 
    array (
      'widget_class' => 'SubPanelTopSelectButton',
      'mode' => 'MultiSelect',
    ),
  ),
);
