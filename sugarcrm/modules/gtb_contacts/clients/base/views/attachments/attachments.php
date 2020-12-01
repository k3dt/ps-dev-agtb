<?php

$coreDefs = MetaDataFiles::loadSingleClientMetadata('view','attachments');
$coreDefs['dashlets'][0]['filter']['module'] = array('gtb_contacts');
$coreDefs['dashlets'][0]['config']['link'] = 'gtb_contacts_activities_1_notes';
$coreDefs['dashlets'][0]['preview']['link'] = 'gtb_contacts_activities_1_notes';
$viewdefs['gtb_contacts']['base']['view']['attachments'] = $coreDefs;
