<?php

$coreDefs = MetaDataFiles::loadSingleClientMetadata('view','active-tasks');
$coreDefs['dashlets'][0]['filter']['module'] = array('gtb_matches');
$coreDefs['custom_toolbar']['buttons'][0]['buttons'][0]['params']['link'] = 'gtb_matches_activities_1_tasks';
$coreDefs['tabs'][0]['link'] = 'gtb_matches_activities_1_tasks';
$coreDefs['tabs'][1]['link'] = 'gtb_matches_activities_1_tasks';
$viewdefs['gtb_matches']['base']['view']['active-tasks'] = $coreDefs;
