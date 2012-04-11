<?php
$viewdefs ['Bugs']['portal']['view']['edit'] =
    array(
        'templateMeta' =>
        array(
            'maxColumns' => '2',
            'widths' =>
            array(
                array(
                    'label' => '10',
                    'field' => '30',
                ),
                array(
                    'label' => '10',
                    'field' => '30',
                ),
            ),
            'formId' => 'BugEditView',
            'formName' => 'BugEditView',
            'hiddenInputs' =>
            array(
                'module' => 'Bugs',
                'returnmodule' => 'Bugs',
                'returnaction' => 'DetailView',
                'action' => 'Save',
            ),
            'hiddenFields' =>
            array(
                array(
                    'name' => 'portal_viewable',
                    'operator' => '=',
                    'value' => '1',
                ),
            ),
            'useTabs' => false,
        ),
        'panels' =>
        array(
            array(
                'label' => 'default',
                'fields' =>
                array(
                    'priority',
                    'status',
                    'source',
                    'product_category',
                    array(
                        'name' => 'resolution',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'name',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'description',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'work_log',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                ),
            ),
        ),
    );
?>
