<?php
require_once('clients/summer/SideBarLayout.php');
$layout = new SideBarLayout();
$layout->push('main', array('view'=>'countrychart', 'context'=>array('source'=>'SalesByCountry')));

$viewdefs['Accounts']['summer']['layout']['listsidebar'] = $layout->getLayout();
