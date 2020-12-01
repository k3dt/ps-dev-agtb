<?php

$coreDefs = MetaDataFiles::loadSingleClientMetadata('view','active-tasks');
$coreDefs['dashlets'][0]['filter']['module'] = array('gtb_contacts');
$coreDefs['custom_toolbar']['buttons'][0]['buttons'][0]['params']['link'] = 'gtb_contacts_activities_1_tasks';
$coreDefs['tabs'][0]['link'] = 'gtb_contacts_activities_1_tasks';
$coreDefs['tabs'][1]['link'] = 'gtb_contacts_activities_1_tasks';
$viewdefs['gtb_contacts']['base']['view']['active-tasks'] = $coreDefs;
