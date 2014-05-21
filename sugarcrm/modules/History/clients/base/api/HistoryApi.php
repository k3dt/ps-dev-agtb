<?php
/*
* By installing or using this file, you are confirming on behalf of the entity
* subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
* the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
* http://www.sugarcrm.com/master-subscription-agreement
*
* If Company is not bound by the MSA, then by installing or using this file
* you are agreeing unconditionally that Company will be bound by the MSA and
* certifying that you have authority to bind Company accordingly.
*
* Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
*/
require_once('clients/base/api/RelateApi.php');

class HistoryApi extends RelateApi
{
    /**
     * This is the list of allowed History Modules
     * @var array
     */
    protected $moduleList = array(
        'meetings' => 'Meetings',
        'calls' => 'Calls',
        'notes' => 'Notes',
        'tasks' => 'Tasks',
        'emails' => 'Emails',
    );

    /**
     * This is the list of valid fields that should be on each select
     * @var array
     */
    protected $validFields = array(
        'name',
        'status',
        'date_entered',
        'date_modified',
        'related_contact',
    );

    public function registerApiRest()
    {
        return array(
            'recordListView' => array(
                'reqType' => 'GET',
                'path' => array('<module>', '?', 'link', 'history'),
                'pathVars' => array('module', 'record', ''),
                'method' => 'filterModuleList',
                'jsonParams' => array('filter'),
                'shortHelp' => 'Get the history records for a specific record',
                'longHelp' => 'include/api/help/history_filter.html',
                'exceptions' => array(
                    // Thrown in filterList
                    'SugarApiExceptionInvalidParameter',
                    // Thrown in filterListSetup and parseArguments
                    'SugarApiExceptionNotAuthorized',
                ),
            ),
        );
    }

    public function filterModuleList(ServiceBase $api, array $args, $acl = 'list')
    {
        if (!empty($args['module_list'])) {
            $module_list = explode(',', $args['module_list']);
            foreach ($this->moduleList as $link_name => $module) {
                if (!in_array($module, $module_list)) {
                    unset($this->moduleList[$link_name]);
                }
            }
        }

        // if the module list is empty then someone passed in bad modules for the history
        if (empty($this->moduleList)) {
            throw new SugarApiExceptionInvalidParameter("Module List is empty, must contain: Meetings, Calls, Notes, Tasks, or Emails");
        }

        $query = new SugarQuery();
        $api->action = 'list';
        $seeds = array();
        $orderBy = array();

        // modules is a char field used for sorting on module name
        // it is added to the select below, it can be sorted on but needs to be removed from
        // the arguments to allow it to be maintained throughout the code
        $removedModuleDirection = false;
        if (!empty($args['order_by'])) {
            $orderBy = explode(',', $args['order_by']);
            foreach ($orderBy as $key => $list) {
                list($field, $direction) = explode(':', $list);
                if ($field == 'module') {
                    unset($orderBy[$key]);
                    $removedModuleDirection = !empty($direction) ? $direction : 'DESC';
                }
            }
            $args['order_by'] = implode(',', $orderBy);
            $orderBy[] = "module:{$removedModuleDirection}";
        }

        if (!empty($args['fields'])) {
            $args['fields'] .= "," . implode(',', $this->validFields);
        } else {
            $args['fields'] = implode(',', $this->validFields);
        }
        
        if (!empty($args['order_by']) || !empty($args['fields'])) {
            $args = $this->scrubFields($args);
        }

        unset($args['order_by']);
        foreach ($this->moduleList as $link_name => $module) {
            $savedFields = $args['fields'];
            $args['link_name'] = $link_name;

            $fields = explode(',', $args['fields']);

            foreach ($fields as $k => $field) {
                if (isset($args['placeholder_fields'][$module][$field])) {
                    unset($fields[$k]);
                }
            }

            $args['fields'] = implode(',', $fields);

            list($args, $q, $options, $linkSeed) = $this->filterRelatedSetup($api, $args);
            $q->select()->selectReset();
            if (!empty($args['placeholder_fields'])) {
                $newFields = array_merge($args['placeholder_fields'][$module], $fields);
            } else {
                $newFields = $fields;
            }

            sort($newFields);
            foreach ($newFields as $field) {
                if ($field == 'module') {
                    continue;
                }
                if (isset($args['placeholder_fields'][$module][$field])) {
                    $q->select()->fieldRaw("'' {$args['placeholder_fields'][$module][$field]}");
                } else {
                    $q->select()->field($field);
                }
            }
            $q->select()->field('id');
            $q->select()->field('assigned_user_id');
            $q->limit = $q->offset = null;
            $q->select()->fieldRaw("'{$module}'", 'module');
            $query->union($q);
            $query->limit($options['limit'] + 1);
            $query->offset($options['offset']);
            $args['fields'] = $savedFields;
        }

        if (!empty($orderBy)) {
            if ($removedModuleDirection !== false) {
                $orderBy[] = "module:{$removedModuleDirection}";
            }
            foreach ($orderBy as $order) {
                $ordering = explode(':', $order);
                if (count($ordering) > 1) {
                    $query->orderByRaw("{$ordering[0]}", "{$ordering[1]}");
                } else {
                    $query->orderByRaw("{$ordering[0]}");
                }
            }
        } else {
            $query->orderByRaw('date_modified');
        }

        return $this->runQuery($api, $args, $query, $options);
    }

    protected function scrubFields($args)
    {
        $filters = !empty($args['order_by']) ? explode(',', $args['order_by']) : array();
        foreach ($filters as $filter) {
            $order_by = explode(':', $filter);
            foreach ($this->moduleList as $module_name) {
                $seed = BeanFactory::getBean($module_name);
                if (!isset($seed->field_defs[$order_by[0]])) {
                    $args['placeholder_fields'][$module_name][$order_by[0]] = $order_by[0];
                } else {
                    if (empty($args['fields'])) {
                        $args['fields'] = "{$order_by[0]}";
                    } else {
                        $args['fields'] .= ",{$order_by[0]}";
                    }
                }
            }
        }

        $fields = !empty($args['fields']) ? explode(',', $args['fields']) : array();
        foreach ($fields as $key => $field) {
            foreach ($this->moduleList as $module_name) {
                $seed = BeanFactory::getBean($module_name);
                if (!isset($seed->field_defs[$field])) {
                    $args['placeholder_fields'][$module_name][$field] = $field;                    
                }
            }
        }
        return $args;
    }

    protected function runQuery(ServiceBase $api, array $args, SugarQuery $q, array $options)
    {
        $GLOBALS['log']->info("Filter SQL: " . $q->compileSql());

        $beans = array();

        foreach ($q->execute() as $row) {
            $beans[$row['id']] = BeanFactory::getBean($row['module'], $row['id']);
            $beans['_rows'][$row['id']] = $row;
        }

        $rows = $beans['_rows'];
        unset($beans['_rows']);

        $data = array();
        $data['next_offset'] = -1;

        $i = 0;
        foreach ($beans as $bean_id => $bean) {
            if ($i == $options['limit']) {
                unset($beans[$bean_id]);
                $data['next_offset'] = (int)($options['limit'] + $options['offset']);
                continue;
            }
            $i++;

            $this->populateRelatedFields($bean, $rows[$bean_id]);
        }

        // add on the contact_id and contact_name fields so we get those
        // returned in the response
        $args['fields'] .= ',contact_id,contact_name';
        $data['records'] = $this->formatBeans($api, $args, $beans);

        foreach ($data['records'] as $id => $record) {
            $data['records'][$id]['moduleNameSingular'] = $GLOBALS['app_list_strings']['moduleListSingular'][$record['_module']];
            $data['records'][$id]['moduleName'] = $GLOBALS['app_list_strings']['moduleList'][$record['_module']];
        }

        return $data;
    }
}
