<?php
$viewdefs['base']['layout']['search'] = array(
    'type' => 'simple',
    'components' =>
    array(
        0 => array(
            'view' => 'subnavdetail',
        ),
        1 => array(
            'layout' =>
            array(
                'type' => 'columns',
                'components' =>
                array(
                    0 => array(
                        'layout' =>
                        array(
                            'type' => 'leftside',
                            'components' =>
                            array(
                                0 => array(
                                    'view' => 'results',
                                ),
                            ),
                        ),
                    ),
                    1 => array(
                        'layout' =>
                        array(
                            'type' => 'rightside',
                            'components' =>
                            array(
                                0 => array(
                                    'view' => 'preview',
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);
