<?php

$layout = MetaDataManager::getLayout('GenericLayout');
$layout->push(array('view'=>'opportunity-metrics'));
$layout->push(array('view'=>'crunchbase'));
$layout->push(array('view'=>'news'));
//$layout->push(array('view'=>'maps'));

$viewdefs['Accounts']['base']['layout']['sidebar'] = $layout->getLayout();
