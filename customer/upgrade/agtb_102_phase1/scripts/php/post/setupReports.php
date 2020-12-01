<?php

$logger = LoggerManager::getLogger();

$reportDef = '{"display_columns":[],"module":"gtb_matches","group_defs":[{"name":"stage","label":"Stage","table_key":"self","type":"enum","force_label":"Stage"}],"summary_columns":[{"name":"stage","label":"Stage","table_key":"self"},{"name":"count","label":"Count","field_type":"","group_function":"count","table_key":"self"}],"report_name":"Open Matches","chart_type":"vBarF","do_round":1,"chart_description":"","numerical_chart_column":"self:count","numerical_chart_column_type":"","assigned_user_id":"1","report_type":"summary","full_table_list":{"self":{"value":"gtb_matches","module":"gtb_matches","label":"gtb_matches"}},"filters_def":{"Filter_1":{"operator":"AND","0":{"name":"stage","table_key":"self","qualifier_name":"is_not","input_name0":["Closed"]}}}}';

$sqlCommands = [
    "INSERT INTO saved_reports (id, name, date_entered, date_modified, modified_user_id, created_by, description, deleted, module, report_type, content, is_published, chart_type, schedule_type, favorite, sync_key, assigned_user_id, team_id, team_set_id, acl_team_set_id) VALUES('5e6fd772-2526-11eb-a4e0-0242ac140007', 'Open Matches', '2020-11-12 20:33:48', '2020-11-12 20:33:48', '1', '1', '', 0, 'gtb_matches', 'summary', '".$reportDef."', 0, 'vBarF', 'pro', 0, NULL, '1', '1', '1', NULL);",
];

$db = DBManagerFactory::getInstance();
foreach ($sqlCommands as $query) {
    $logger->debug('Executing SQL Command: ' . $query);
    $db->query($query);
}

$logger->debug('Finished Executing SQL Commands');
