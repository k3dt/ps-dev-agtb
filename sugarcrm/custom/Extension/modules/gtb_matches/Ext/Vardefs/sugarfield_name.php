<?php
 // created: 2020-11-20 15:03:57
$dictionary['gtb_matches']['fields']['name']['unified_search']=false;
$dictionary['gtb_matches']['fields']['name']['calculated']='true';
$dictionary['gtb_matches']['fields']['name']['formula']='concat(related($contacts_gtb_matches_1,"last_name")," matching ",related($gtb_positions_gtb_matches_1,"name"))';
$dictionary['gtb_matches']['fields']['name']['enforced']=true;

 ?>