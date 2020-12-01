<?php

$coreDefs = MetaDataFiles::loadSingleClientMetadata('view','history');
$coreDefs['dashlets'][0]['filter']['module'] = array('gtb_contacts');
$coreDefs['tabs'][0]['link'] = 'gtb_contacts_activities_1_meetings';
$coreDefs['tabs'][1]['link'] = 'gtb_contacts_activities_1_emails';
$coreDefs['tabs'][2]['link'] = 'gtb_contacts_activities_1_calls';
$coreDefs['custom_toolbar']['buttons'][0]['buttons'][0]['params']['link'] = 'gtb_contacts_activities_1_emails';
$viewdefs['gtb_contacts']['base']['view']['history'] = $coreDefs;
