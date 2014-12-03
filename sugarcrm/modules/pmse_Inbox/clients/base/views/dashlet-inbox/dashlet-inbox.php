<?php
$module_name = 'pmse_Inbox';
$viewdefs[$module_name]['base']['view']['dashlet-inbox'] = array(
    'dashlets' => array(
        array(
            'label' => 'LBL_PMSE_PROCESSES_DASHLET',
            'description' => 'LBL_PMSE_PROCESSES_DASHLET_DESCRIPTION',
            'config' => array(
                'limit' => 10,
                'date' => 'true',
                'visibility' => 'user',
            ),
            'preview' => array(
                'limit' => 10,
                'date' => 'true',
                'visibility' => 'user',
            ),
            'filter' => array(
                'module' => array(
                    //'Accounts',
                    //'Bugs',
                    //'Cases',
                    //'Contacts',
                    'Home',
                    //'Leads',
                    //'Opportunities',
                    //'Prospects',
                ),
                'view' => 'record',
            ),
        ),
    ),
    'custom_toolbar' => array(
        'buttons' => array(
            array(
                'dropdown_buttons' => array(
                    array(
                        'type' => 'dashletaction',
                        'action' => 'editClicked',
                        'label' => 'LBL_DASHLET_CONFIG_EDIT_LABEL',
                    ),
                    array(
                        'type' => 'dashletaction',
                        'action' => 'refreshClicked',
                        'label' => 'LBL_DASHLET_REFRESH_LABEL',
                    ),
//                    array(
//                        'type' => 'dashletaction',
//                        'action' => 'toggleClicked',
//                        'label' => 'LBL_DASHLET_MINIMIZE',
//                        'event' => 'minimize',
//                    ),
                    array(
                        'type' => 'dashletaction',
                        'action' => 'removeClicked',
                        'label' => 'LBL_DASHLET_REMOVE_LABEL',
                    ),
                ),
            ),
        ),
    ),

    'panels' => array(
        array(
            'name' => 'panel_body',
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => array(
//                array(
//                    'name' => 'date',
//                    'label' => 'LBL_DASHLET_CONFIGURE_FILTERS',
//                    'type' => 'enum',
//                    'options' => 'planned_activities_filter_options',
//                ),
                array(
                    'name' => 'visibility',
                    'label' => 'LBL_DASHLET_CONFIGURE_MY_ITEMS_ONLY',
                    'type' => 'enum',
                    'options' => 'tasks_visibility_options',
                ),
                array(
                    'name' => 'limit',
                    'label' => 'LBL_DASHLET_CONFIGURE_DISPLAY_ROWS',
                    'type' => 'enum',
                    'options' => 'tasks_limit_options',
                ),
            ),
        ),
    ),
    'tabs' => array(
        array(
            'active' => true,
            'filter_applied_to' => 'in_time',
            'filters' => array(
                'act_assignment_method' => array('$equals' => 'static'),
            ),
            'label' => 'LBL_PMSE_MY_PROCESSES',
            'link' => 'pmse_Inbox',
            'module' => 'pmse_Inbox',
            'order_by' => 'date_entered:asc',
            'record_date' => 'date_entered',
            'include_child_items' => true,
        ),
        array(
            'filter_applied_to' => 'in_time',
            'filters' => array(
                //'custom_source' => 'PMSE',
                //'assignment_method' => array('$equals' => 'selfservice'),
                'act_assignment_method' => array('$equals' => array('selfservice', 'BALANCED')),
                //'assigned_user_id' => array('$not_in' => array('1')),
            ),
            //'fields' => array('cas_id','cas_enrique'),
            'label' => 'LBL_PMSE_SELF_SERVICE_PROCESSES',
            'link' => 'pmse_Inbox',
            'module' => 'pmse_Inbox',
            'order_by' => 'date_entered:asc',
            'record_date' => 'date_entered',
            'include_child_items' => true,
        ),
    ),
);
