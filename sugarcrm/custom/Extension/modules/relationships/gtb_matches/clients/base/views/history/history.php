<?php

$coreDefs = MetaDataFiles::loadSingleClientMetadata('view','history');
$coreDefs['dashlets'][0]['filter']['module'] = array('gtb_matches');
$coreDefs['tabs'][0]['link'] = 'gtb_matches_activities_1_meetings';
$coreDefs['tabs'][2]['link'] = 'gtb_matches_activities_1_calls';
$viewdefs['gtb_matches']['base']['view']['history'] = $coreDefs;
