<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$module_name = 'pmse_Inbox';
$viewdefs[$module_name]['base']['view']['list'] = array(
    'panels' => array(
        array(
            'label' => 'LBL_PANEL_1',
            'fields' => array(
                array(
                    'name' => 'cas_id',
                    'label' => 'Case Id',
                    'default' => true,
                    'enabled' => true,
                    'link' => false,
                ),
                array(
                    'name' => 'cas_title',
                    'label' => 'LBL_NAME',
                    'default' => true,
                    'enabled' => true,
                    'link' => false,
                ),
                array(
                    'name' => 'task_name',
                    'label' => 'Task Name',
                    'default' => true,
                    'enabled' => true,
                    'link' => false,
                ),
                array(
                    'name' => 'pro_title',
                    'label' => 'Process Name',
                    'default' => true,
                    'enabled' => true,
                    'link' => false,
                ),
                array(
                    'name' => 'cas_status',
                    'label' => 'Status',
                    'default' => true,
                    'enabled' => true,
                    'link' => false,
                ),
                array(
                    'name' => 'case_init',
                    'label' => 'Owner',
                    'width' => 9,
                    'default' => true,
                    'enabled' => true,
                    'link' => false,
                ),
                array(
                    'label' => 'LBL_DATE_CREATED',
                    'enabled' => true,
                    'default' => true,
                    'name' => 'date_entered',
                    'readonly' => true,
                ),
            ),
        ),
    ),
    'orderBy' => array(
        'field' => 'date_modified',
        'direction' => 'desc',
    ),
);

/*$module_name = 'pmse_Inbox';
$viewdefs[$module_name]['base']['view']['list'] = array(
    'panels' => array(
        array(
            'label' => 'LBL_PANEL_1',
            'fields' => array(
                array(
                    'name' => 'cas_name',
                    'label' => 'Case Title',
                    'default' => true,
                    'enabled' => true,
                    'readonly' => true,
                    'link' => false,
                ),
                array(
                    'name' => 'pro_title',
                    'label' => 'Process',
                    'width' => 9,
                    'default' => true,
                    'readonly' => true,
                    'enabled' => true,
                    'link' => false
                ),
                array(
                    'name' => 'task_name',
                    'label' => 'Task Name',
                    'width' => 9,
                    'default' => true,
                    'enabled' => true,
                    'readonly' => true,
                    'link' => false,
                ),
                array(
                    'name' => 'cas_id',
                    'label' => 'Case ID',
                    'enabled' => true,
                    'default' => true,
                    'readonly' => true,
                ),
                array(
                    'name' => 'cas_delegate_date',
                    'label' => 'Delegated',
                    'enabled' => true,
                    'default' => true,
                    'readonly' => true,
                    'link' => false
                ),
                array(
                    'name' => 'cas_due_date',
                    'label' => 'Due Date',
                    'enabled' => true,
                    'default' => true,
                    'readonly' => true,
                    'link' => false
                ),
            ),
        ),
    ),
    'orderBy' => array(
        'field' => 'date_entered',
        'direction' => 'asc',
    ),
);*/