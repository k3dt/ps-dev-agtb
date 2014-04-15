<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/


/*********************************************************************************

 * Description: This file handles the Data base functionality for prepared Statements
 * It acts as the prepared statement abstraction layer for the application.
 *
 * All the functions in this class will work with any bean which implements the meta interface.
 * The passed bean is passed to helper class which uses these functions to generate correct sql.
 *
 * The meta interface has the following functions:
 */
require_once 'include/database/PreparedStatement.php';

class MysqliPreparedStatement extends PreparedStatement
{
    /**
     * Place to bind query output vars to
     * @var array
     */
    protected $output_vars = array();

    /**
     * Fields in the result
     * @var array
     */
    protected $resultFields = array();

    /**
     * MySQLi statement object
     * @var mysqli_stmt
     */
    protected $stmt;

    /**
     * Maps MySQL column datatypes to MySQL bind variable types
     *
     * Possible types are:
     *   b - blob
     *   d - double
     *   i - integer
     *   s - string
     *
     */
    protected $typeMap = array(
        // Sugar DataType      PHP Bind Variable data type
        'string'           => 's',
        'date'             => 's',
        'time'             => 's',
        'float'            => 'd',
        'bigint'           => 'i',
        'int'              => 'i',
        'bool'             => 'i',
    );


    /**
     * Prepare the statement for concrete database
     * @param string $this->parsedSQL SQL text of the query
     * @param array $fieldDefs Definitions for variables
     * @param string $msg Error message
     */
    protected function preparePreparedStatement($msg = '' )
    {
        if(empty($this->parsedSQL)) {
            $this->DBM->registerError($msg, "Empty SQL query");
            return false;
        }

        $GLOBALS['log']->info('QueryPrepare: ' . $this->parsedSQL);
        if (!($this->stmt = $this->dblink->prepare($this->parsedSQL))) {
            $this->DBM->registerError($msg, "Prepare failed: for sql: $this->parsedSQL (" . $this->dblink->errno . ") " . $this->dblink->error);
            return false;
        }
        $num_args = $this->stmt->param_count;

        if ($num_args > 0) {
            $this->bound_vars = $bound = array_fill(0, $num_args, null);
            $types = "";
            for($i=0; $i<$num_args;$i++) {
                if(empty($this->fieldDefs[$i]["type"])) {
                    $this->DBM->registerError($msg, "No defs entry for parameter $i");
                    return false;
                }

                $type = $this->fieldDefs[$i]["type"];
                $mappedType = $this->DBM->getTypeClass($type);
                if($this->DBM->isTextType($type)) {
                    // FIXME: add support for send_long_data
                    $mappedType = 's';
                } elseif(!empty($this->typeMap[$mappedType])) {
                    $mappedType = $this->typeMap[$mappedType];
                } else {
                    $mappedType = 's';
                }
                $types .= $mappedType;  // SugarType->type_map->ps_type_map
                $bound[$i] =& $this->bound_vars[$i];
            }
            array_unshift($bound, $types);    // puts $types in front of the data elements

            call_user_func_array(array($this->stmt, "bind_param"), $bound);

            $this->DBM->checkError("QueryPrepare Failed: $msg for sql: $this->parsedSQL :");
        }

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see PreparedStatement::executePreparedStatement()
     */
    public function executePreparedStatement(array $data, $msg = '')
    {
        if(!$this->prepareStatementData($data, !empty($this->stmt)?$this->stmt->param_count:0, $msg)) {
            return false;
        }
        $this->preparedStatementResult = null;
        $res = $this->stmt->execute();

        return $this->finishStatement($res, $msg);
    }

    /**
     * (non-PHPdoc)
     * @see PreparedStatement::preparedStatementFetch()
     */
    public function preparedStatementFetch($msg = '')
    {
        if(!$this->stmt) {
            return false;
        }

        // first time, create an array of column names from the returned data set
        if (empty($this->preparedStatementResult)) {
            $this->resultFields = null;
            $this->preparedStatementResult = $this->stmt->result_metadata();
            if (is_object($this->preparedStatementResult))  {
                $this->resultFields = $this->preparedStatementResult->fetch_fields();
            } else {
                $this->preparedStatementResult = null;
                return false;
            }

            if (!empty($this->resultFields) && is_array($this->resultFields))  {
                $this->output_vars = $bound = array();
                foreach($this->resultFields as $k => $field) {
                    $this->output_vars[$field->name] = null;
                    $bound[$k] =& $this->output_vars[$field->name];
                }
                call_user_func_array(array($this->stmt, "bind_result"), $bound);
            } else {
                $this->preparedStatementResult = null;
                return false;
            }
        }

        // Get the next results
        if($this->stmt->fetch()) {
            return $this->output_vars;
        } else {
            return false;
        }
    }

    public function preparedStatementClose()
    {
        if($this->stmt) {
            $this->stmt->close();
            $this->stmt = null;
        }
    }
}
