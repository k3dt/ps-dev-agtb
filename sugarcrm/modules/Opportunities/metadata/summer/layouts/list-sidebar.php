<?php

require_once('clients/summer/SideBarLayout.php');
$layout = new SideBarLayout();
//$layout->push('top', array('view'=>'subnav'));
$layout->push('main',array('view'=>'activitystream'));
$viewdefs['Opportunities']['summer']['layout']['list-sidebar'] = $layout->getLayout();
