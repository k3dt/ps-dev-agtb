<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

require_once 'include/SugarQuery/Builder/Field/Condition.php';
require_once 'include/SugarQuery/Builder/Field/Groupby.php';
require_once 'include/SugarQuery/Builder/Field/Having.php';
require_once 'include/SugarQuery/Builder/Field/Orderby.php';
require_once 'include/SugarQuery/Builder/Field/Raw.php';
require_once 'include/SugarQuery/Builder/Field/Select.php';

/**
 * SugarQuery_Builder_Field
 * @api
 */

class SugarQuery_Builder_Field
{
    /**
     * @var string Field
     */
    public $field;

    /**
     * @var string table/table alias
     */
    public $table;
    /**
     * @var bool|string field alias
     */
    public $alias = false;
    /**
     * @var string the fields bean table
     */
    public $bean_table;
    /**
     * @var bool custom field
     */
    public $custom = false;
    /**
     * @var string custom bean table
     */
    public $custom_bean_table = '';
    /**
     * @var array the field defs
     */
    public $def = array();

    /**
     * @var bool future use, is this field FTS enabled
     */
    public $ftsEnabled = false;

    /**
     * @var string module name
     */
    public $moduleName = false;

    /**
     * @var bool is this field a non-db field
     */
    public $nonDb = 0;

    /**
     * Makin' the magic in the sugar field
     * @param $field
     * @param SugarQuery $query
     */
    public function __construct($field, SugarQuery $query)
    {
        if (is_array($field)) {
            $this->field = $field[0];
            $this->alias = $field[1];
        } else {
            $this->field = $field;
        }

        if ($query->getFromBean()) {
            $this->setupField($query);
            $this->shouldMarkNonDb();
            }
        }

    /**
     * Setup the field parts
     * @param SugarQuery $query
     */
    public function setupField($query)
    {
        $this->query = $query;

        $this->def = $this->getFieldDef();

        // if its a linking table let it slide
        if (!empty($this->query->join[$this->table]->options['linkingTable'])){
            $this->nonDb = 0;
        } elseif (empty($this->def) && $this->field != '*') {
            $this->nonDb = 1;
            return;
        }

        $this->jta = $this->getJoin();
        if ((!empty($this->def) && $this->field != 'id_c') || $this->field == '*') {
            $this->expandField();
        }
    }

    /**
     * Get the field def from the correct bean
     * @return array
     */
    public function getFieldDef()
    {
        $bean = $this->query->getFromBean();
        $this->table = $bean->getTableName();

        $def = array();

        if (strstr($this->field, '.')) {
            list($this->table, $this->field) = explode('.', $this->field);
        }

        if ($bean &&
            ($bean->getTableName() == $this->table || $this->table == $this->query->getFromAlias()) &&
            !empty($bean->field_defs[$this->field])
        ) {
            $this->table = $this->query->getFromAlias();
            $def = $bean->field_defs[$this->field];
            $this->moduleName = $bean->module_name;
        } else {
            $bean = $this->query->getTableBean($this->table);

            if (!empty($bean->field_defs[$this->field])) {
                $this->moduleName = $bean->module_name;
                $def = $bean->field_defs[$this->field];
            }
        }

        $this->checkCustomField($bean);

        return $def;
    }

    /**
     * Check if the current field needs to join the custom table
     * @param SugarBean|null $bean
     *
     */
    public function checkCustomField($bean = null)
    {
        if (empty($bean)) {
            $bean = BeanFactory::getBean($this->moduleName);
        }
        if (!empty($bean)) {
            // Initialize def for now, in case $this->field isn't in field_defs
            $def = array();
            if (isset($bean->field_defs[$this->field])) {
                $def = $bean->field_defs[$this->field];
            }

            if ((isset($def['source']) && $def['source'] == 'custom_fields') || $this->field == 'id_c') {
                $this->custom = true;
                $this->custom_bean_table = $bean->get_custom_table_name();
                $this->bean_table = $bean->getTableName();
                if (substr($this->table, -5) != "_cstm") {
                    $this->standardTable = $this->table;
                    $this->table = $this->table . "_cstm";
                }
            }
        }
    }


    /**
     * Determine if the field needs a join to make the query succeed.  Return either the join table alias or false
     * @return bool| string
     * @throws SugarQueryException
     */
    public function getJoin()
    {
        $jta = false;
        if(!isset($this->def['source']) || $this->def['source'] == 'db') {
            return false;
        }
        if (isset($this->def['type']) && $this->def['type'] == 'relate'
            || (isset($this->def['source']) && $this->def['source'] == 'non-db'
                // For some reason the full_name field has 'link' => true
                && isset($this->def['link']) && $this->def['link'] !== true)
        ) {
            $params = array(
                'joinType' => 'LEFT',
            );
            if (!isset($this->def['link'])) {
                if (!isset($this->def['id_name']) || !isset($this->def['module'])) {
                    throw new SugarQueryException("No ID field Name or Module Name");
                }
                // we may need to put on our detective hat and see if we can
                // hunt down a relationship
                $farBean = BeanFactory::newBean($this->def['module']);

                // check and see if we need to do the join, it may already be done.
                if (!$this->query->getJoinAlias($farBean->table_name) && !$this->query->getJoinAlias(
                        $this->def['name']
                    )
                ) {
                    //Custom relate fields may have the id field on the custom table, need to check for that.
                    $idField = new SugarQuery_Builder_Field($this->def['id_name'], $this->query);
                    $idField->setupField($this->query);
                    $idField->checkCustomField();
                    if ($idField->custom) {
                        $this->custom = true;
                        $this->query->joinCustomTable($this->query->getFromBean());
                    }
                    //Now actually join the related table
                    $jta = $this->query->getJoinTableAlias($this->def['name']);
                    $join = $this->query->joinRaw(
                        " LEFT JOIN {$farBean->table_name} {$jta} ON {$idField->table}.{$this->def['id_name']} = {$jta}.id ",
                        array('alias' => $jta)
                    );
                    $join->bean = $farBean;
                }
            }
            if (!empty($this->def['link']) && !$this->query->getJoinAlias($this->def['link'])) {

                $join = $this->query->join($this->def['link'], $params);

                $jta = $join->joinName();
            } elseif(!empty($this->def['link']) && $this->query->getJoinAlias($this->def['link'])) {
                $jta = $this->query->getJoinAlias($this->def['link']);
            }

            if (!empty($this->def['rname_link'])) {
                $jta = $this->query->getJoinAlias($this->def['link']);
                $this->table = !empty($this->query->join[$jta]->relationshipTableAlias) ? $this->query->join[$jta]->relationshipTableAlias : $jta;
            }
        }
        return $jta;
    }

    /**
     * Mark a field as non-db
     */
    public function markNonDb()
    {
        $this->nonDb = 1;
    }

    /**
     * check if a field is non-db
     * @return bool
     */
    public function isNonDb()
    {
        return $this->nonDb;
    }

    /**
     * Determines if a field should be marked nonDb and calls markNondb if so
     */
    public function shouldMarkNonDb()
    {
        if ((isset($this->def['source']) && $this->def['source'] == 'non-db') && empty($this->def['rname_link'])) {
            $this->markNonDb();
        }
    }

    /**
     * Will clean the field by using the vardefs to determine if the field can be added to the query
     * it will also add additional fields to the query or modify the table, field variables of the object
     * so that on compilation the field is a correct db field.
     */
    public function expandField()
    {
    }

}
