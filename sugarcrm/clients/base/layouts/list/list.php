<?php

$layout = MetaDataManager::getLayout('GenericLayout', array('type' => 'list'));
$layout->push(array('view'=>'list-top'));
$layout->push(array('layout'=>'subpanel'));
$layout->push(array('view'=>'list-bottom'));
$viewdefs['base']['layout']['list'] = $layout->getLayout();
