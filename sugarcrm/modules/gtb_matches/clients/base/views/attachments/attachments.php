<?php

$coreDefs = MetaDataFiles::loadSingleClientMetadata('view','attachments');
$coreDefs['dashlets'][0]['filter']['module'] = array('gtb_matches');
$coreDefs['dashlets'][0]['config']['link'] = 'gtb_matches_activities_1_notes';
$coreDefs['dashlets'][0]['preview']['link'] = 'gtb_matches_activities_1_notes';
$viewdefs['gtb_matches']['base']['view']['attachments'] = $coreDefs;
