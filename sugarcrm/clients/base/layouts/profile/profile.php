<?php
$viewdefs['base']['layout']['profile'] = array(
    'type' => 'simple',
    'components' =>
    array(
        0 => array(
            'view' => 'subnavdetail',
        ),
        1 => array(
            'layout' =>
            array(
                'type' => 'fluid',
                'components' =>
                array(
                    0 => array(
                        'layout' =>
                        array(
                            'type' => 'simple',
                            'span' => 7,
                            'components' =>
                            array(
                                0 => array(
                                    'view' => 'profile',
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);
