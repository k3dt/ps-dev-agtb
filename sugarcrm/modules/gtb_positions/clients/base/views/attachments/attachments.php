<?php

$coreDefs = MetaDataFiles::loadSingleClientMetadata('view','attachments');
$coreDefs['dashlets'][0]['filter']['module'] = array('gtb_positions');
$coreDefs['dashlets'][0]['config']['link'] = 'gtb_positions_activities_1_notes';
$coreDefs['dashlets'][0]['preview']['link'] = 'gtb_positions_activities_1_notes';
$viewdefs['gtb_positions']['base']['view']['attachments'] = $coreDefs;
