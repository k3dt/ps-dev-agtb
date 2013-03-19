<?php
$layout = MetaDataManager::getLayout(
    'DashboardLayout',
    array(
        'columns' => 1,
        'name' => 'My Dashboard',
    )
);
$layout->push(
    0,
    array(
        array(
            'name' => 'My Contacts',
            'view' => 'dashablelist',
            'context' => array(
                'module' => 'Contacts',
                'dashlet' => array(
                    'type' => 'dashablelist',
                    'module' => 'Contacts',
                    'display_columns' => array(
                        'full_name',
                        'title',
                        'phone_work',
                        'date_entered',
                        'assigned_user_name',
                    ),
                    'my_items' => '1',
                ),
            ),
        ),
    )
);
$viewdefs['Products']['base']['layout']['list-dashboard'] = $layout->getLayout();
