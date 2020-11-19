<?php

$coreDefs = MetaDataFiles::loadSingleClientMetadata('view','active-tasks');
$coreDefs['dashlets'][0]['filter']['module'] = array('gtb_positions');
$coreDefs['custom_toolbar']['buttons'][0]['buttons'][0]['params']['link'] = 'gtb_positions_activities_1_tasks';
$coreDefs['tabs'][0]['link'] = 'gtb_positions_activities_1_tasks';
$coreDefs['tabs'][1]['link'] = 'gtb_positions_activities_1_tasks';
$viewdefs['gtb_positions']['base']['view']['active-tasks'] = $coreDefs;
