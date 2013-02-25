<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * This is the base object for compiling SugarQueries
 * ************ WARNING**********************************************
 * THIS CLASS AND ALL RELATED CLASSES WILL BE FUNDAMENTALLY CHANGING
 * DO NOT USE THIS TO BUILD YOUR QUERIES.
 * ******************************************************************
 * TODO:
 * Move all bean/vardef functionality out of here and into sugarquery
 * This will allow compilers to be strictly object->desired output without
 * using beans or anything.
 *
 * This can be accomplished by expanding Sugar query to almost do a preCompile
 * and check fields and verify prefixes before it even pushes to the compiler
 */
require_once('include/SugarQuery/SugarQuery.php');
class SugarQuery_Compiler_SQL
{
    /**
     * @var SugarBean
     */
    protected $from_bean;
    /**
     * @var SugarQuery
     */
    protected $sugar_query;
    /**
     * @var null|string
     */
    protected $from_alias = null;
    /**
     * @var string
     */
    protected $primary_table;
    /**
     * @var string
     */
    protected $primary_custom_table;

    /**
     * @var dbManager
     */
    protected $db;

    // TODO EXPAND THE TYPE MAP
    /**
     * @var array
     */
    protected $type_map = array(
        'module' => 'string',
        'parent_type' => 'string',
        'parent_id' => 'string',
        'name' => 'string',
        'int' => 'number',
        'double' => 'number',
        'float' => 'number',
        'uint' => 'number',
        'ulong' => 'number',
        'long' => 'number',
        'short' => 'number',
        'varchar' => 'string',
        'text' => 'string',
        'longtext' => 'string',
        'date' => 'date',
        'enum' => 'string',
        'relate' => 'string',
        'multienum' => 'string',
        'html' => 'string',
        'longhtml' => 'string',
        'datetime' => 'datetime',
        'datetimecombo' => 'datetime',
        'time' => 'time',
        'bool' => 'bool',
        'tinyint' => 'number',
        'char' => 'string',
        'blob' => 'binary',
        'longblob' => 'binary',
        'currency' => 'number',
        'decimal' => 'number',
        'decimal2' => 'number',
        'id' => 'string',
        'url' => 'string',
        'encrypt' => 'string',
        'file' => 'string',
        'decimal_tpl' => 'number',
        'phone' => 'string',
        'assigned_user_name' => 'string',
    );

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Build out the Query in SQL
     *
     * @param SugarQuery $sugar_query
     * @return string
     */
    public function compile(SugarQuery $sugar_query)
    {
        $this->sugar_query = $sugar_query;
        return $this->compileSelectQuery();
    }

    /**
     * Convert a Select SugarQuery Object into a string
     *
     * @return string
     */
    protected function compileSelectQuery()
    {
        $select_part = '*';
        $from_part = '';
        $where_part = '';
        $join_part = '';
        $distinct = '';
        $group_by_part = '';
        $order_by_part = '';
        $having_part = '';

        $select = $this->sugar_query->select;
        $from = $this->sugar_query->from;
        $join = $this->sugar_query->join;
        $where = $this->sugar_query->where;

        $group_by = $this->sugar_query->group_by;
        $having = $this->sugar_query->having;
        $order_by = $this->sugar_query->order_by;
        $limit = $this->sugar_query->limit;
        $offset = $this->sugar_query->offset;

        $union = $this->sugar_query->union;


        if ($from !== null) {
            $from_part = trim($this->compileFrom($from));
        }
        if ($select !== null) {
            $select_part = trim($this->compileSelect($select));
        }
        if ($join !== null) {
            $join_part = trim($this->compileJoin($join));
        }
        if ($where !== null) {
            $where_part = trim($this->compileWhere($where));
        }

        if ($this->sugar_query->distinct) {
            $distinct = 'DISTINCT';
        }

        if (!empty($group_by)) {
            $group_by_part = $this->compileGroupBy($group_by);
        }

        if (!empty($having)) {
            $having_part = $this->compileHaving($having);
        }

        if (!empty($order_by)) {
            $order_by_part = $this->compileOrderBy($order_by);
        }

        // DB MANAGER::limitQuerySql
        $limit_part = (!empty($limit)) ? " LIMIT {$limit} " : '';
        $offset_part = (!empty($offset)) ? " OFFSET {$offset} " : '';

        $sql = "SELECT {$distinct} {$select_part} FROM {$from_part}";
        if (!empty($join_part)) {
            $sql .= " {$join_part} ";
        }
        if (!empty($where_part)) {
            $sql .= " WHERE {$where_part} ";
        }
        if (!empty($group_by_part)) {
            $sql .= " GROUP BY {$group_by_part} ";
        }
        if (!empty($having_part)) {
            $sql .= " HAVING {$having_part} ";
        }
        if (!empty($order_by_part)) {
            $sql .= " ORDER BY {$order_by_part} ";
        }
        if (!empty($limit_part)) {
            $sql .= $limit_part;
        }
        if (!empty($offset_part)) {
            $sql .= $offset_part;
        }

        if (!empty($union)) {
            foreach ($union AS $u) {
                if (isset($u['select'])) {
                    $sql .= ' UNION ';
                    $sql .= ($u['all']) ? 'ALL ' : '';
                    $sql .= $u['select']->compileSql();
                }
            }
        }
        return trim($sql);
    }

    /**
     * Create a GroupBy statement
     *
     * @param array $group_by
     * @return string
     */
    protected function compileGroupBy($group_by)
    {
        return implode(',', $group_by);
    }

    /**
     * Create a Having statement
     *
     * @param string $having
     * @return string
     */
    protected function compileHaving($having)
    {
        $return = array();
        foreach ($having AS $have) {
            $return[] = $have[0] . $have[1] . (isset($have[2])) ? $have[2] : '';
        }

        return implode(' ', $return);
    }

    /**
     * Create an Order By Statement
     *
     * @param array $order_by
     * @return string
     */
    protected function compileOrderBy($order_by)
    {
        $return = array();
        foreach ($order_by AS $order) {
            list($field, $direction) = $order;
            $field = $this->canonicalizeField($field);
            $return[] = "{$field} {$direction}";
        }

        return implode(',', $return);
    }

    /**
     * @param $field
     * @return string
     */
    protected function canonicalizeField($field)
    {
        $bean = $this->from_bean;
        /**
         * We need to figure out if the field is prefixed with an alias.  If it is and the alias is not the from beans table,
         * we must load the relationship that the alias is referencing so that we can determine if they are using the correct alias
         * and change it around if necessary
         * An exception must be made for sugarfavorites because there could be multiple joins to different sugarfavorites tables and these aliases are
         * taken care of automatically when sugarfavorites is joined.
         */
        if (strstr($field, '.')) {
            list($table, $field) = explode('.', $field);
            if ($table != $bean->getTableName()) {
                $link_name = $this->sugar_query->join[$table]->linkName;
                if (!empty($link_name)) {
                    $bean->load_relationship($link_name);

                    $module = $bean->$link_name->getRelatedModuleName();
                    $bean = BeanFactory::newBean($module);
                } else {
                    if ($this->sugar_query->join[$table]->table == 'sugarfavorites') {
                        return "{$table}.{$field}";
                    }
                }
            }
        }
        if (isset($bean->field_defs[$field]['source']) && $bean->field_defs[$field]['source'] == 'custom') {
            return $bean->get_custom_table_name() . ".{$field}";
        }
        return $bean->getTableName() . ".{$field}";
    }

    /**
     * Create a select statement
     *
     * @param SugarQuery_Builder_Select $selectObj
     * @return string
     */
    protected function compileSelect(SugarQuery_Builder_Select $selectObj)
    {
        $return = array();
        foreach ($selectObj->select AS $field) {
            $alias = false;
            if (is_array($field)) {
                list($field, $alias) = $field;
                $alias = " AS {$alias}";
            }

            if ($field instanceof SugarQuery) {
                $return[] = '(' . $field->compileSql() . ')' . $alias;
            } else {
                $field = $this->canonicalizeField($field);
                $return[] = $field . $alias;
            }

        }

        return implode(", ", $return);

    }

    /**
     * Create a from statement
     *
     * @param SugarBean|array $bean
     * @return string
     */
    protected function compileFrom($bean)
    {
        $return = array();
        $alias = false;
        if (is_array($bean)) {
            list($bean, $alias) = $bean;
            $this->from_alias = $alias;
        }
        $this->from_bean = $bean;
        $table = $bean->getTableName();
        $table_cstm = '';
        $from_clause = "{$table}";

        if (isset($alias)) {
            $from_clause .= " {$alias}";
        }

        if ($bean->hasCustomFields()) {
            $table_cstm = $bean->get_custom_table_name();
            if (!empty($table_cstm)) {
                // TODO: CLEAN THIS UP
                if (isset($alias)) {
                    $sql = "LEFT JOIN {$table_cstm} {$alias}_c ON {$alias}_c.id_c = {$alias}.id";
                } else {
                    $sql = "LEFT JOIN {$table_cstm} ON {$table_cstm}.id_c = {$table}.id";
                }
                // can do a join here because we haven't got to the joins yet in the compile sequence.
                $this->sugar_query->joinRaw($sql);
            }
        }

        if (!empty($this->from_alias)) {
            $this->primary_table = $this->from_alias;
            $this->primary_custom_table = $this->from_alias . '_c';
        } else {
            $this->primary_table = $this->from_bean->getTableName();
            $this->primary_custom_table = $this->from_bean->get_custom_table_name();
        }

        $return = $from_clause;

        return $return;
    }

    /**
     * Create a where statement
     *
     * @param array of SugarQuery_Builder_Where $where
     * @return string
     */
    protected function compileWhere(array $where)
    {
        $sql = false;
        $first_object = true;
        foreach ($where AS $whereObj) {
            if ($whereObj instanceof SugarQuery_Builder_Andwhere) {
                $operator = " AND ";
            } else {
                $operator = " OR ";
            }

            if (!empty($whereObj->raw)) {
                $sql .= $whereObj->raw;
                continue;
            }
            foreach ($whereObj->conditions AS $condition) {
                if ($condition instanceof SugarQuery_Builder_Where) {
                    if (!empty($sql) && substr($sql, -1) != '(') {
                        $sql .= $operator;
                    }
                    $sql .= ' (' . $this->compileWhere(array($condition)) . ')';
                    continue;
                } elseif ($condition instanceof SugarQuery_Builder_Condition) {
                    $sql = $this->compileCondition($condition, $sql, $operator);
                } else {
                    if (is_array($condition)) {
                        $sql .= explode(' ', $condition);
                    }
                }
            }
            if ($first_object == false) {
                $sql .= ')';
            }
            $first_object = false;
            $prev_operator = $operator;
        }
        return $sql;
    }

    /**
     * Compile a condition into SQL
     *
     * @param SugarQuery_Builder_Condition $condition
     * @param string $sql
     * @param string $operator
     * @return string
     */
    public function compileCondition(SugarQuery_Builder_Condition $condition, $sql, $operator)
    {
        if (!empty($sql) && substr($sql, -1) != '(') {
            $sql .= $operator;
        }
        $field = $this->canonicalizeField($condition->field);

        if ($condition->isNull) {
            $sql .= "{$field} IS NULL";
        } elseif ($condition->notNull) {
            $sql .= "{$field} IS NOT NULL";
        } else {
            switch ($condition->operator) {
                case 'IN':
                    $valArray = array();
                    if ($condition->values instanceof SugarQuery) {
                        $sql .= "{$field} IN (" . $condition->values->compileSql() . ")";
                    } else {
                        foreach ($condition->values AS $val) {
                            $valArray[] = $this->quoteValue($condition->field, $val, $condition->bean);
                        }
                        $sql .= "{$field} IN (" . implode(',', $valArray) . ")";
                    }
                    break;
                case 'BETWEEN':
                    $value['min'] = $this->quoteValue($condition->field, $condition->values['min'], $condition->bean);
                    $value['max'] = $this->quoteValue($condition->field, $condition->values['max'], $condition->bean);
                    $sql .= "{$field} BETWEEN {$value['min']} AND {$value['max']}";
                    break;
                case 'STARTS':
                case 'CONTAINS':
                case 'ENDS':
                    $value = $this->quoteValue(
                        $condition->field,
                        $condition->values,
                        $condition->bean,
                        $condition->operator
                    );
                    $sql .= "{$field} LIKE {$value}";
                    break;
                case 'EQUALFIELD':
                    $sql .= "{$field} = {$condition->values}";
                    break;
                case 'NOTEQUALFIELD':
                    $sql .= "{$field} != {$condition->values}";
                    break;
                case '=':
                case '!=':
                case '>':
                case '<':
                case '>=':
                case '<=':
                default:
                    if ($condition->values instanceof SugarQuery) {
                        $sql .= "{$field} {$condition->operator} (" . $condition->values->compileSql() . ")";
                    } else {
                        $value = $this->quoteValue($condition->field, $condition->values, $condition->bean);
                        $sql .= "{$field} {$condition->operator} {$value}";
                    }
                    break;
            }
        }
        return $sql;
    }

    // TODO: FIX THIS SO THAT WE USE MORE DBMANAGER STUFF
    // FIGURE OUT WHY db->quotes() DOESN'T RETURN QUOTED STRING FOR US TO USE
    /**
     * @param $field
     * @param $value
     * @param bool $bean
     * @param bool $operator
     * @return string
     */
    protected function quoteValue($field, $value, $bean = false, $operator = false)
    {

        if ($bean === false) {
            $bean = $this->from_bean;
        }

        /**
         * We need to check the field to determine if it is coming from the from bean or a link.
         * If it is coming from a link we need to load the bean on the other side of the relationship
         * so that we get the type of the field we are trying to quote.
         */
        if (stristr($field, '.')) {
            list($table, $field) = explode('.', $field);
            if ($table != $bean->getTableName()) {
                $link_name = $this->sugar_query->join[$table]->linkName;
                if (!empty($link_name)) {
                    // currently we only go one level deep on links,
                    // so we know that the link has to be in the from beans relationships
                    $bean->load_relationship($link_name);
                    // we have to get the module name and then override the $bean var with the new bean
                    $module = $bean->$link_name->getRelatedModuleName();
                    $bean = BeanFactory::newBean($module);
                }
            }
        }


        if (isset($bean->field_defs[$field])) {
            switch ($this->type_map[$bean->field_defs[$field]['type']]) {
                case 'number':
                    return $value;
                    break;
                case 'date':
                case 'datetime':
                case 'time':
                    $db = DBManagerFactory::getInstance();
                    if ($value == 'NOW()') {
                        return $db->now();
                    }
                    return $db->quote($value);
                    break;
                case 'bool':
                    return $value;
                    break;
                case 'binary':
                    return $value;
                    break;
                case 'string':
                default:
                    if ($operator == 'STARTS') {
                        $value = $value . '%';
                    }
                    if ($operator == 'CONTAINS') {
                        $value = '%' . $value . '%';
                    }
                    if ($operator == 'ENDS') {
                        $value = '%' . $value;
                    }
                    return "'{$value}'";
                    break;
            }
        } else {
            return "'$value'";
        }
    }

    /**
     * Creates join syntax for the query
     *
     * @param array $join
     * @return string
     */
    protected function compileJoin(array $join)
    {
        // get the related beans for everything
        $return = array();

        // check if any elements are relationships
        foreach ($join as $j) {
            if (!empty($j->raw)) {
                $return[] = $j->raw;
                continue;
            }
            if (isset($j->options['joinType'])) {
                $sql = strtoupper($j->options['joinType']) . ' JOIN';
            } else {
                $sql = 'JOIN';
            }

            $table = $j->table;

            if ($table instanceof SugarQuery) {
                $table = "(" . $table->compileSql() . ")";
            }
            // Quote the table name that is being joined
            $sql .= ' ' . $table;

            if (isset($j->options['alias']) && strtolower($j->options['alias']) != strtolower($table)) {
                $sql .= ' ' . $j->options['alias'];
            }

            $sql .= ' ON ';
            $sql .= '(' . $this->compileWhere($j->on) . ')';

            $return[] = $sql;
        }

        return implode(' ', $return);
    }
}