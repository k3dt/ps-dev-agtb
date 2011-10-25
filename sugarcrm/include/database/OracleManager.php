<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
* $Id: OracleManager.php 56825 2010-06-04 00:09:04Z smalyshev $
* Description: This file handles the Data base functionality for the application using oracle.
* It acts as the DB abstraction layer for the application. It depends on helper classes
* which generate the necessary SQL. This sql is then passed to PEAR DB classes.
* The helper class is chosen in DBManagerFactory, which is driven by 'db_type' in 'dbconfig' under config.php.
*
* All the functions in this class will work with any bean which implements the meta interface.
* The passed bean is passed to helper class which uses these functions to generate correct sql.
* Please see DBManager file for details
*
*
* Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
* All Rights Reserved.
* Contributor(s): ______________________________________..
********************************************************************************/

//FILE SUGARCRM flav=ent ONLY

/**
 * Oracle driver
 */
class OracleManager extends DBManager
{
    /**
     * @see DBManager::$dbType
     */
    public $dbType = 'oci8';
    public $dbName = 'Oracle';
    public $variant = 'oci8';
    public $label = 'LBL_ORACLE';

	/**
     * contains the last result set returned from query()
     */
    protected $_lastResult;

    protected $capabilities = array(
        "affected_rows" => true,
        "case_sensitive" => true,
        "fulltext" => true,
        "auto_increment_sequence" => true,
        'limit_subquery' => true,
    );

    protected $maxNameLengths = array(
        'table' => 30,
        'column' => 30,
        'index' => 30,
        'alias' => 30
    );

    protected $type_map = array(
            'int'      => 'number',
            'double'   => 'number(30,10)',
            'float'    => 'number(30,6)',
            'uint'     => 'number(15)',
            'ulong'    => 'number(38)',
            'long'     => 'number(38)',
            'short'    => 'number(3)',
            'varchar'  => 'varchar2',
            'text'     => 'clob',
            'longtext' => 'clob',
            'date'     => 'date',
            'enum'     => 'varchar2(255)',
            'relate'   => 'varchar2',
            'multienum'=> 'clob',
            'html'     => 'clob',
            'datetime' => 'date',
            'datetimecombo' => 'date',
            'time'     => 'date',
            'bool'     => 'number(1)',
            'tinyint'  => 'number(3)',
            'char'     => 'char',
            'id'       => 'varchar2(36)',
            'blob'     => 'blob',
            'longblob' => 'blob',
            'currency' => 'number(26,6)',
            'decimal'  => 'number(20,2)',
            'decimal2' => 'number(30,6)',
            'url'      => 'varchar2(255)',
            'encrypt'  => 'varchar2(255)',
            'file'     => 'varchar2(255)',
	    	'decimal_tpl' => 'number(%d, %d)',
            );
	/**
     * List of known sequences
     * @var array
     */
    protected static $sequences = null;

    /**
     * DB configuration options
     * @var array
     */
    protected $configOptions;

    public function repairTableParams($tablename, $fielddefs, $indices, $execute = true, $engine = null)
    {
        //Modules with names close to 30 characters may have index names over 30 characters, we need to clean them
        foreach ($indices as $key => $value) {
            $indices[$key]['name'] = $this->getValidDBName($value['name'], true, 'index');
        }

        return parent::repairTableParams($tablename,$fielddefs,$indices,$execute,$engine);
    }
    /**
     * @see DBManager::version()
     */
    public function version()
    {
        return $this->getOne("SELECT version FROM product_component_version WHERE product like '%Oracle%'");
    }

    /**
     * @see DBManager::checkError()
     */
    public function checkError($msg = '', $dieOnError = false, $stmt = null)
    {
        if (parent::checkError($msg, $dieOnError))
            return true;

        if(empty($stmt)) return false;

        $err = oci_error($stmt);
        if ($err){
            $error = $err['code']."-".$err['message'];
            $this->registerError($msg, $error, $dieOnError);
            return true;
        }
        return false;
    }

	/**
     * Parses and runs queries
     *
     * @param  string   $sql               SQL Statement to execute
     * @param  bool     $dieOnError        True if we want to call die if the query returns errors
     * @param  string   $msg               Message to log if error occurs
     * @param  bool     $suppress          Flag to suppress all error output unless in debug logging mode.
     * @param  bool     $keepResult		   True if we want to push this result into the $lastResult var.
     * @return resource result set
     */
    public function query($sql, $dieOnError = false, $msg = '', $suppress = false, $keepResult = false)
    {
        if(is_array($sql)) {
            return $this->queryArray($sql, $dieOnError, $msg, $suppress);
        }
        parent::countQuery($sql);
        $GLOBALS['log']->info('Query: ' . $sql);
        $this->checkConnection();
        $this->query_time = microtime(true);
        $db = $this->getDatabase();
        $result = false;

        $stmt = $suppress?@oci_parse($db, $sql):oci_parse($db, $sql);
		if(!$this->checkError("$msg Parse Failed: $sql", $dieOnError)) {
			$exec_result = $suppress?@oci_execute($stmt):oci_execute($stmt);
	        $this->query_time = microtime(true) - $this->query_time;
	        $GLOBALS['log']->info('Query Execution Time: '.$this->query_time);
	        //BEGIN SUGARCRM flav=pro ONLY
		    if($this->dump_slow_queries($sql)) {
			    $this->track_slow_queries($sql);
			}
			//END SUGARCRM flav=pro ONLY
			if($exec_result) {
			    $result = $stmt;
			}
		}

		$this->lastQuery = $sql;
		if($keepResult)
		    $this->lastResult = $result;

		if($this->checkError($msg.' Query Failed: ' . $sql, $dieOnError, $stmt)) {
		    return false;
		}
        return $result;
    }

    /**
     * @see DBManager::checkQuery()
     */
    protected function checkQuery($sql)
    {
        $name = (empty($GLOBALS['current_user']) || empty($GLOBALS['current_user']->user_name))
            ? 'generic' : $GLOBALS['current_user']->user_name;
        $id = 'sugar' .$name;
        $sql = "EXPLAIN PLAN SET statement_id='" . $id . "' FOR " . $sql ;

        $this->query($sql);

        $result = $this->query("SELECT * FROM plan_table WHERE statement_id='$id' AND object_type='TABLE' AND options='FULL'");
        $badQuery = array();
        $minCost = (!empty($GLOBALS['sugar_config']['check_query_cost']))?$GLOBALS['sugar_config']['check_query_cost']:10;
        while ($row = $this->fetchByAssoc($result)) {
            if ($row['cost'] < $minCost)
                continue;

            $table = $row['object_name'];
            $badQuery[$table] = '';
            if($row['options'] == 'FULL')
                $badQuery[$table]  .=  ' Full Table Scan[cost:' . $row['cost'] . ' cpu:' . $row['cpu_cost'] . ' io:'
                    . $row['io_cost'] . '];';
        }
        if (!empty($badQuery)) {
            foreach ($badQuery as $table=>$data ) {
                if(!empty($data)){
                    $warning = ' Table:' . $table . ' Data:' . $data;
                    if(!empty($GLOBALS['sugar_config']['check_query_log'])){
                        $GLOBALS['log']->fatal($sql);
                        $GLOBALS['log']->fatal('CHECK QUERY:' .$warning);
                    }else{
                        $GLOBALS['log']->warn('CHECK QUERY:' .$warning);
                    }
                }
            }
        }
        $this->query("DELETE FROM plan_table WHERE statement_id='$id'");
    }

    /**
     * Runs a limit query: one where we specify where to start getting records and how many to get
     *
     * @param  string   $sql
     * @param  int      $start
     * @param  int      $count
     * @param  boolean  $dieOnError
     * @param  string   $msg
     * @param  bool     $execute    optional, false if we just want to return the query
     * @return resource query result
     */
    public function limitQuery($sql, $start, $count, $dieOnError = false, $msg = '', $execute = true)
    {
        $matches = array();
        preg_match('/^(.*SELECT)(.*?FROM.*WHERE)(.*)$/is',$sql, $matches);
        $GLOBALS['log']->debug('Limit Query:' . $sql. ' Start: ' .$start . ' count: ' . $count);
        if ($start ==0 && !empty($matches[3])) {
            $sql = 'SELECT /*+ FIRST_ROWS('. $count . ') */ * FROM (' . $matches[1]. $matches[2]. $matches[3] . ') MSI WHERE ROWNUM <= '.$count;
            if(!empty($GLOBALS['sugar_config']['check_query'])){
            	$this->checkQuery($sql);
         	}
            return $this->query( $sql);
        }

        $start++; //count is 1 based.

        if($count != 1)
            $next = $start + $count -1;
        else
            $next=$start;

        if (!empty($matches[2])) {
            $sql = "SELECT /*+ FIRST_ROWS($count) */ * FROM (SELECT  ROWNUM as orc_row, MSI.* FROM (".$sql. ') MSI  WHERE ROWNUM <= '. $next . ') WHERE  orc_row >= ' . $start;
            if (!empty($GLOBALS['sugar_config']['check_query']))
                $this->checkQuery($sql);

            return $this->query($sql);
        }
        if (!empty($GLOBALS['sugar_config']['check_query']))
            $this->checkQuery($sql);

        $query = "SELECT * FROM (SELECT ROWNUM AS orc_row , MSI.* FROM ($sql) MSI where ROWNUM <= $next) WHERE orc_row >= $start";
        if ($execute)
            return $this->query($query, $dieOnError, $msg);

        return $query;
    }

    /**
     * @see DBManager::getFieldsArray()
     */
	public function getFieldsArray($result, $make_lower_case = false)
	{
		$field_array = array();

        if(! isset($result) || empty($result))
            return 0;

        $i = 1;
        $count = oci_num_fields($result);
        $count_tag = $count + 1;
        while ($i < $count_tag) {
            $meta = oci_field_name($result, $i);
            if (!$meta)
                return 0;
            if($make_lower_case==true)
                $meta = strtolower($meta);
            $field_array[] = $meta;

            $i++;
        }

        return $field_array;
    }

    /**
     * Get number of rows affected by last operation
     * @see DBManager::getAffectedRowCount()
     */
	public function getAffectedRowCount($result)
    {
        return oci_num_rows($result);
    }

    /**
     * Fetches the next row from the result set
     *
     * @param  resource $result result set
     * @return array
     */
    protected function ociFetchRow($result)
    {
        $row = oci_fetch_array($result, OCI_ASSOC|OCI_RETURN_NULLS|OCI_RETURN_LOBS);
        if ( !$row )
            return false;
        if (!$this->checkError("Fetch error", false, $result)) {
            $temp = $row;
            $row = array();
            foreach ($temp as $key => $val)
                // make the column keys as lower case. Trim the val returned
                $row[strtolower($key)] = trim($val);
        }
        else
            return false;

        return $row;
    }

	/**
	 * @see DBManager::fetchRow()
	 */
	public function fetchRow($result)
	{
		if (empty($result))	return false;

        return $this->ociFetchRow($result);
    }

    /**
     * @see DBManager::getTablesArray()
     */
    public function getTablesArray()
    {
        $GLOBALS['log']->debug('ORACLE fetching table list');

        if($this->getDatabase()) {
            $tables = array();
            $r = $this->query('SELECT TABLE_NAME FROM USER_TABLES');
            if (is_resource($r)) {
                while ($a = $this->fetchByAssoc($r))
                    $tables[] = strtolower($a['table_name']);

                return $tables;
            }
        }

        return false; // no database available
    }

    /**
     * @see DBManager::tableExists()
     */
    public function tableExists($tableName)
    {
        $GLOBALS['log']->info("tableExists: $tableName");

        if ($this->getDatabase()){
            $this->tableName = strtoupper($tableName);
            $sql = "select count(*) count from user_tables where upper(table_name) like '$this->tableName'";
            $count = $this->getOne($sql);
            return ($count == 0) ? false : true;
        }

        return false;
    }

    /**
     * Get tables like expression
     * @param $like string
     * @return array
     */
    public function tablesLike($like)
    {
        if ($this->getDatabase()) {
            $tables = array();
            $r = $this->query('SELECT TABLE_NAME tn FROM USER_TABLES WHERE TABLE_NAME LIKE '.strtoupper($this->quoted($like)));
            if (!empty($r)) {
                while ($a = $this->fetchByAssoc($r)) {
                    $row = array_values($a);
					$tables[]=$row[0];
                }
                return $tables;
            }
        }
        return false;
    }


    /**
     * @see DBManager::update()
     */
    public function update(SugarBean $bean, array $where = array())
    {
        $sql = $this->updateSQL($bean,$where);

        $ret = $this->AltlobExecute($bean, $sql);
        oci_commit($this->getDatabase()); //moved here from sugarbean
        $this->tableName = $bean->getTableName();
        $msg = "Error inserting into table: ".$this->tableName;
        $this->checkError($msg.' Query Failed: ' . $sql, true);
    }

    /**
     * @see DBManager::insert()
     */
    public function insert(SugarBean $bean)
    {
        $sql = $this->insertSQL($bean);
        $ret = $this->AltlobExecute($bean, $sql);

        oci_commit($this->getDatabase()); //moved here from sugarbean.
        $this->tableName = $bean->getTableName();
        $msg = "Error inserting into table: ".$this->tableName;
        $this->checkError($msg.' Query Failed: ' . $sql, true);
    }

    /**
     * TODO: may want to join it with Altlobexecute
     * (non-PHPdoc)
     * @see DBManager::insertParams()
     */
    public function insertParams($table, $field_defs, $data)
    {
        $values = array();
        $lob_fields = $lobs = array();
        $lob_field_type = array();
		foreach ($field_defs as $field => $fieldDef)
		{
            if (isset($fieldDef['source']) && $fieldDef['source'] != 'db')  continue;
            //custom fields handle there save seperatley

            if(isset($data[$field])) {
                // clean the incoming value..
                $val = from_html($data[$field]);
            } else {
                continue;
            }
            $type = $this->getFieldType($fieldDef);

            //handle auto increment values
            if (!empty($fieldDef['auto_increment'])) {
                $auto = $this->getAutoIncrementSQL($table, $fieldDef['name']);
                if(!empty($auto)) {
                    $values[$field] = $auto;
                }
            } elseif ($fieldDef['name'] == 'deleted') {
                $values['deleted'] = (int)$val;
            } else {
                // need to do some thing about types of values
                $values[$field] = $this->massageValue($val, $fieldDef);
            }
            $lob_type = false;
            if ($type == 'longtext' or  $type == 'text' or $type == 'clob' or $type == 'multienum') $lob_type = OCI_B_CLOB;
            else if ($type == 'blob' || $type == 'longblob') $lob_type = OCI_B_BLOB;

            // this is not a lob, continue;
            if ($lob_type === false) continue;

            $lob_fields[$fieldDef['name']]=":".$fieldDef['name'];
            $lob_field_type[$fieldDef['name']]=$lob_type;
		}

		if ( empty($values)) return true;

        // get the entire sql
		$query = "INSERT INTO $table (".implode(",", array_keys($values)).")
                    VALUES (".implode(",", $values).")";

        if (count($lob_fields) > 0 ) {
            $query .= " RETURNING ".implode(",", array_keys($lob_fields)).' INTO '.implode(",", array_values($lob_fields));
        }

        $stmt = oci_parse($this->database, $query);
        if($this->checkError("Insert parse failed: $query", false)) {
            return false;
        }

        foreach ($lob_fields as $key=>$descriptor) {
            $newlob = oci_new_descriptor($this->database, OCI_D_LOB);
            oci_bind_by_name($stmt, $descriptor, $newlob, -1, $lob_field_type[$key]);
            $lobs[$key] = $newlob;
        }
        $result = false;
        oci_execute($stmt,OCI_DEFAULT);
        if(!$this->checkError("Insert execute failed: $query", false, $stmt)) {
            foreach ($lobs as $key=>$lob){
                $val = $data[$key];
                if (empty($val)) $val=" ";
                $lob->save($val);
            }
            oci_commit($this->database);
            $result = true;
        }

        // free all the lobs.
        foreach ($lobs as $lob){
            $lob->free();
        }
        oci_free_statement($stmt);
        return $result;
    }

    /**
     * Executes a query, with special handling for Oracle CLOB and BLOB field type
     *
     * @param  object   $bean SugarBean instance
     * @param  string   $sql  SQL statement
     * @return resource
     */
    protected function AltlobExecute(SugarBean $bean, $sql)
    {
    	$GLOBALS['log']->debug("Oracle Execute: $sql");
        $this->checkConnection();
        if(empty($sql)){
            return false;
        }

        $lob_fields=array();
        $lob_field_type=array();
        $lobs=array();
        foreach ($bean->field_defs as $fieldDef) {
            $type = $this->getFieldType($fieldDef);
            if (isset($fieldDef['source']) && $fieldDef['source']!='db') {
                continue;
            }

            //not include the field if a value is not set...
            if (!isset($bean->$fieldDef['name'])) continue;

            $lob_type = false;
            if ($type == 'longtext' or  $type == 'text' or $type == 'clob' or $type == 'multienum') $lob_type = OCI_B_CLOB;
            else if ($type == 'blob' || $type == 'longblob') $lob_type = OCI_B_BLOB;

            // this is not a lob, continue;
            if ($lob_type === false) continue;

            $lob_fields[$fieldDef['name']]=":".$fieldDef['name'];
            $lob_field_type[$fieldDef['name']]=$lob_type;
        }

        if (count($lob_fields) > 0 ) {
            $sql .= " RETURNING ".implode(",", array_keys($lob_fields)).' INTO '.implode(",", array_values($lob_fields));
        }

        $stmt = oci_parse($this->database, $sql);
        if($this->checkError("Update parse failed: $sql", false)) {
            return false;
        }

        foreach ($lob_fields as $key=>$descriptor) {
            $newlob = oci_new_descriptor($this->database, OCI_D_LOB);
            oci_bind_by_name($stmt, $descriptor, $newlob, -1, $lob_field_type[$key]);
            $lobs[$key] = $newlob;
        }
        $result = false;
        oci_execute($stmt,OCI_DEFAULT);
        if(!$this->checkError("Update execute failed: $sql", false, $stmt)) {
            foreach ($lobs as $key=>$lob){
                $val = $bean->getFieldValue($key);
                if (empty($val)) $val=" ";
                $lob->save($val);
            }
            oci_commit($this->database);
            $result = true;
        }

        // free all the lobs.
        foreach ($lobs as $lob){
            $lob->free();
        }
        oci_free_statement($stmt);

        return $result;
    }

    /**
     * @see DBManager::quote()
     */
    public function quote($string)
    {
        if(is_array($string)) {
            return $this->arrayQuote($string);
        }
        return str_replace("'", "''", $this->quoteInternal($string));
    }

	/**
     * @see DBManager::connect()
     */
    public function connect(array $configOptions = null, $dieOnError = false)
    {
        global $sugar_config;

        if(!$configOptions)
			$configOptions = $sugar_config['dbconfig'];

		$this->configOptions = $configOptions;
		if($this->getOption('persistent'))
		{
            $this->database = oci_pconnect($configOptions['db_user_name'], $configOptions['db_password'],$configOptions['db_name'], "AL32UTF8");
            $err = oci_error();
            if ($err != false) {
	            $GLOBALS['log']->debug("oci_error:".var_export($err, true));
            }
		}

        if(!$this->database){
                $this->database = oci_connect($configOptions['db_user_name'],$configOptions['db_password'],$configOptions['db_name'], "AL32UTF8");
                if (!$this->database) {
                	$err = oci_error();
                	if ($err != false) {
			            $GLOBALS['log']->debug("oci_error:".var_export($err, true));
                	}
                	$GLOBALS['log']->fatal("Could not connect to server ".$configOptions['db_name']." as ".$configOptions['db_user_name'].".");
                	if($dieOnError) {
                        if(isset($GLOBALS['app_strings']['ERR_NO_DB'])) {
                            sugar_die($GLOBALS['app_strings']['ERR_NO_DB']);
                        } else {
                            sugar_die("Could not connect to the database. Please refer to sugarcrm.log for details.");
                        }
                    } else {
                	    return false;
                	}
                }
                if($this->database && $this->getOption('persistent')){
                    $_SESSION['administrator_error'] = "<B>Severe Performance Degradation: Persistent Database Connections not working.  Please set \$sugar_config['dbconfigoption']['persistent'] to false in your config.php file</B>";
                }
        }
        //set oracle date format to be yyyy-mm-dd
        //settings for function based index.
            /* cn: This alters CREATE TABLE statements to explicitly create char-length varchar2() columns
             * at create time vs. byte-length columns.  the other option is to switch to nvarchar2()
             * which has char-length semantics by default.
             */
             $session_query = "alter session set
                nls_date_format = 'YYYY-MM-DD hh24:mi:ss'
                QUERY_REWRITE_INTEGRITY = TRUSTED
                QUERY_REWRITE_ENABLED = TRUE
                NLS_LENGTH_SEMANTICS=CHAR ";

            if(!empty($GLOBALS['sugar_config']['oracle_enable_ci'])){
            	$session_query .= "
            	NLS_COMP=LINGUISTIC
				NLS_SORT=BINARY_CI";
            }
            $this->query($session_query);

		if(!$this->checkError('Could Not Connect', $dieOnError))
			$GLOBALS['log']->info("connected to db");

        $GLOBALS['log']->info("Connect:".$this->database);
        return true;
	}

    /**
     * Disconnects from the database
     *
     * Also handles any cleanup needed
     */
    public function disconnect()
    {
    	$GLOBALS['log']->debug('Calling Oracle::disconnect()');
        if(!empty($this->database)){
            $this->freeResult();
            oci_close($this->database);
            $this->database = null;
        }
    }

    /**
     * @see DBManager::freeDbResult()
     */
    protected function freeDbResult($dbResult)
    {
        if(!empty($dbResult))
            oci_free_statement($dbResult);
    }

	protected $date_formats = array(
        '%Y-%m-%d' => 'YYYY-MM-DD',
        '%Y-%m' => 'YYYY-MM',
        '%Y' => 'YYYY',
    );

	 /**
     * @see DBManager::convert()
     */
    public function convert($string, $type, array $additional_parameters = array())
    {
        if (!empty($additional_parameters)) {
            $additional_parameters_string = ','.implode(',',$additional_parameters);
        } else {
            $additional_parameters_string = '';
        }
        $all_parameters = $additional_parameters;
        if(is_array($string)) {
            $all_parameters = array_merge($string, $all_parameters);
        } elseif (!is_null($string)) {
            array_unshift($all_parameters, $string);
        }

        switch (strtolower($type)) {
            case 'date':
                return "to_date($string, 'YYYY-MM-DD')";
            case 'time':
                return "to_date($string, 'HH24:MI:SS')";
            case 'datetime':
                return "to_date($string, 'YYYY-MM-DD HH24:MI:SS'$additional_parameters_string)";
            case 'today':
                return "sysdate";
            case 'left':
                return "LTRIM($string$additional_parameters_string)";
            case 'date_format':
                if(!empty($additional_parameters[0]) && $additional_parameters[0][0] == "'") {
                    $additional_parameters[0] = trim($additional_parameters[0], "'");
                }
                if(!empty($additional_parameters) && isset($this->date_formats[$additional_parameters[0]])) {
                    $format = $this->date_formats[$additional_parameters[0]];
                    return "TO_CHAR($string, '$format')";
                } else {
                   return "TO_CHAR($string, 'YYYY-MM-DD')";
                }
            case 'time_format':
                if(empty($additional_parameters_string)) {
                    $additional_parameters_string = ",'HH24:MI:SS'";
                }
                return "TO_CHAR($string".$additional_parameters_string.")";
            case 'ifnull':
                if(empty($additional_parameters_string)) {
                    $additional_parameters_string = ",''";
                }
                return "NVL($string$additional_parameters_string)";
            case 'concat':
                return implode("||",$all_parameters);
            case 'text2char':
                return "to_char($string)";
            case 'quarter':
                return "TO_CHAR($string, 'Q')";
            case "length":
                return "LENGTH($string)";
            case 'month':
                return "TO_CHAR($string, 'MM')";
            case 'add_date':
                switch(strtolower($additional_parameters[1])) {
                    case 'quarter':
                        $additional_parameters[0] .= "*3";
                        // break missing intentionally
                    case 'month':
                        return "ADD_MONTHS($string, {$additional_parameters[0]})";
                    case 'week':
                        $additional_parameters[0] .= "*7";
                        // break missing intentionally
                    case 'day':
                        return "($string + $additional_parameters[0])";
                    case 'year':
                        return "ADD_MONTHS($string, {$additional_parameters[0]}*12)";
                }
                break;
            case 'add_time':
                return "$string + {$additional_parameters[0]}/24 + {$additional_parameters[1]}/1440";
        }

        return $string;
    }

    /**
     * @see DBManager::fromConvert()
     */
    public function fromConvert($string, $type)
    {
        // YYYY-MM-DD HH:MM:SS
        switch($type) {
            case 'date': return substr($string, 0, 10);
            case 'time': return substr($string, 11);
		}
		return $string;
    }

    protected function isNullable($vardef)
    {
        if(!empty($vardef['type']) && $this->isTextType($vardef['type'])) {
            return false;
        }
		return parent::isNullable($vardef);
    }

    /**
     * @see DBManager::createTableSQLParams()
	 */
	public function createTableSQLParams($tablename, $fieldDefs, $indices)
    {
        $columns = $this->columnSQLRep($fieldDefs, false, $tablename);
        if(empty($columns))
 			return false;

        return "CREATE TABLE $tablename ($columns)";
	}

    /**
     * Does this type represent text (i.e., non-varchar) value?
     * @param string $type
     */
    public function isTextType($type)
    {
        $type = strtolower($type);
        return ($type == 'clob' || $this->getColumnType($type) == 'clob');
    }

    /**
     * (non-PHPdoc)
     * @see DBManager::orderByEnum()
     */
    public function orderByEnum($order_by, $values, $order_dir)
    {
		$i = 0;
        $order_by_arr = array();
        foreach ($values as $key => $value) {
			array_push($order_by_arr, $this->quoted($key).", $i");
			$i++;
		}
		return "DECODE($order_by, ".implode(',', $order_by_arr).", $i) $order_dir\n";
    }

    public function renameColumnSQL($tablename, $column, $newname)
    {
        return "ALTER TABLE $tablename RENAME COLUMN '$column' TO '$newname'";
    }

    /**
	 * @see DBManager::massageValue()
	 */
	public function massageValue($val, $fieldDef)
    {
        $type = $this->getFieldType($fieldDef);
        $ctype = $this->getColumnType($type);

        if($ctype == 'clob') {
            return "EMPTY_CLOB()";
        }

        if($ctype == 'blob') {
            return "EMPTY_BLOB()";
        }

        $qval = parent::massageValue($val, $fieldDef);
        if(empty($val)) return $qval; // do not massage empty values

        if($type == "datetimecombo") {
            $type = "datetime";
        }

        switch($type) {
            case 'date':
                $val = explode(" ", $val); // make sure that we do not pass the time portion
                $qval = parent::massageValue($val[0], $fieldDef);            // get the date portion
                // break missing intentionally
            case 'datetime':
            case 'time':
                return $this->convert($qval, $type);
		}

        return $qval;
    }

	/**
     * @see DBManager::oneColumnSQLRep()
     */
    protected function oneColumnSQLRep($fieldDef, $ignoreRequired = false, $table = '', $return_as_array = false)
    {
		//Bug 25814
		if(isset($fieldDef['name'])){
        	if(stristr($this->getFieldType($fieldDef), 'decimal') && isset($fieldDef['len'])){
				$fieldDef['len'] = min($fieldDef['len'],38);
			}
		}

		return parent::oneColumnSQLRep($fieldDef, $ignoreRequired, $table, $return_as_array);
	}

	/**
	 * returns true if the field is nullable
	 *
	 * @param  string $tableName
	 * @param  string $fieldName
	 * @return bool
	 */
	protected function _isNullableDb($tableName, $fieldName)
	{
		return $this->getOne("SELECT nullable FROM user_tab_columns
				WHERE TABLE_NAME = '".strtoupper($tableName)."'
					AND COLUMN_NAME = '".strtoupper($fieldName)."'") == 'Y';
	}

	/**
	 * Generate modify statement for one column
	 * @param string $tablename
	 * @param array $fieldDef Vardef definition for field
	 * @param string $action
	 * @param bool $ignoreRequired
	 */
	protected function changeOneColumnSQL($tablename, $fieldDef, $action, $ignoreRequired = false)
	{
	    switch($action) {
	    	case 'DROP':
	    		return $fieldDef['name'];
	    		break;
	    	case 'ADD':
	    	    $colArray = $this->oneColumnSQLRep($fieldDef, $ignoreRequired, $tablename, true);
	    	    return "{$colArray['name']} {$colArray['colType']} {$colArray['default']} {$colArray['required']} {$colArray['auto_increment']}";
	    	case 'MODIFY':
	    		$colArray = $this->oneColumnSQLRep($fieldDef, $ignoreRequired, $tablename, true);
	    		$isNullable = $this->_isNullableDb($tablename,$colArray['name']);
	    		$nowCol = $this->describeField($colArray['name'], $tablename);
	    		if($colArray['colType'] == 'blob' || $colArray['colType'] == 'clob') {
	    			// Bug 42467: prevent Oracle from modifying *LOB fields
	    			if($colArray['colType'] != $nowCol['type']) {
                        // we can't change type from lob, sorry
                        return '';
	    			}
	    			$colArray['colType'] = ''; // we don't change type, so omit it
	    		}
	            if(isset($nowCol['default']) && !isset($fieldDef['default'])) {
                    // removing default is allowed by changing to "DEFAULT NULL"
                    $colArray['default'] = "DEFAULT NULL";
                    //$colArray['required'] = '';
                }
	    		if ( !$ignoreRequired && ( $isNullable == ( $colArray['required'] == 'NULL' ) ) )
	    			$colArray['required'] = '';
	    		return "{$colArray['name']} {$colArray['colType']} {$colArray['default']} {$colArray['required']} {$colArray['auto_increment']}";
	    }
        return '';
	}

	/**
     * @see DBManager::changeColumnSQL()
     *
     * Oracle's ALTER TABLE syntax is a bit different from the other rdbmss
     */
    protected function changeColumnSQL($tablename, $fieldDefs, $action, $ignoreRequired = false)
    {
        $tablename = strtoupper($tablename);
        $action = strtoupper($action);

        $columns = "";
        if ($this->isFieldArray($fieldDefs)) {
            /**
             *jc: if we are dropping columns we do not need the
             * column definition data provided with the oneColumnSQLRep
             * method. instead we only need the column names.
             */
        	$addColumns = array();
			foreach($fieldDefs as $def) {
			    $col = $this->changeOneColumnSQL($tablename, $def, $action, $ignoreRequired);
			    if(!empty($col)) {
			        $addColumns[] = $col;
			    }
			}
			if(!empty($addColumns)) {
        	    $columns = "(" . implode(",", $addColumns) . ")";
			} else {
			    $columns = '';
			}
        } else {
            $columns = $this->changeOneColumnSQL($tablename, $fieldDefs, $action, $ignoreRequired);
        }
        if ( $action == 'DROP' )
            $action = 'DROP COLUMN';
        return ($columns == '' || empty($columns))
            ? ""
            : "ALTER TABLE $tablename $action $columns";
    }

	/**
     * @see DBManager::dropTableNameSQL()
     */
    public function dropTableNameSQL($name)
    {
		return parent::dropTableNameSQL(strtoupper($name));
    }

    /**
     * Truncate table
     * @param  $name
     * @return string
     */
    public function truncateTableSQL($name)
    {
        return "TRUNCATE TABLE $name";
    }

    /**
     * Fixes an Oracle index name
     *
     * Oracle has a strict limit on the size of object names (30 characters). errors will
     * occur if this is not checked. indexes should follow the naming convention as follows
     *
     *   idx_[table name]_[column_](_[column2] ...)
     *
     * and columns should be abbreviated by the first three letters or the following abbreviation
     * chart
     *
     * 		u = assigned user
     *		t = assigned team
     * 		d = deleted
     * 		n = name
     *
     * @param  string $name index name
     * @return string
     */
    protected function fixIndexName($name)
    {
    	$result = $this->query(
            "SELECT COUNT(*) CNT
                FROM USER_INDEXES
                WHERE INDEX_NAME = '$name'
                    OR INDEX_NAME = '".strtoupper($name)."'");
		$row = $this->fetchByAssoc($result);
		return ($row['cnt'] > 1) ? $name . (intval($row['cnt']) + 1) : $name;
    }

    /**
     * Generates an index name for the repair table
     *
     * If the last character is not an 'r', make it that; else make it '1'
     *
     * @param  string $index_name
     * @return string
     */
	protected function repair_index_name($index_name)
    {
		$last_char='r';
		if (substr($index_name,strlen($index_name) -1,1) =='r')
			$last_char='1';

		return substr($index_name,0,strlen($index_name)-1). $last_char;
	}

    /**
     * @see DBManager::getAutoIncrement()
     */
    public function getAutoIncrement($table, $field_name)
    {
	    $currval = $this->getOne("SELECT max($field_name) currval FROM $table");
        if (!empty($currval))
            return $currval + 1 ;

        return "";
    }

	/**
     * @see DBManager::getAutoIncrementSQL()
     */
    public function getAutoIncrementSQL($table, $field_name)
    {
        return $this->_getSequenceName($table, $field_name, true) . '.nextval';
    }

    /**
     * @see DBManager::setAutoIncrement()
     */
    protected function setAutoIncrement($table, $field_name)
    {
      	$this->deleteAutoIncrement($table, $field_name);
      	$this->query(
            'CREATE SEQUENCE ' . $this->_getSequenceName($table, $field_name, true) .
                ' START WITH 0 increment by 1 nomaxvalue minvalue 0');
		$this->query(
            'SELECT ' . $this->_getSequenceName($table, $field_name, true) .
                '.NEXTVAL FROM DUAL');

        return "";
    }

    /**
     * Sets the next auto-increment value of a column to a specific value.
     *
     * @param  string $table tablename
     * @param  string $field_name
     */
    public function setAutoIncrementStart($table, $field_name, $start_value)
    {
    	$sequence_name = $this->_getSequenceName($table, $field_name, true);
    	$result = $this->query("SELECT {$sequence_name}.NEXTVAL currval FROM DUAL");
    	$row = $this->fetchByAssoc($result);
    	$current = $row['currval'];
    	$change = $start_value - $current - 1;
    	$this->query("ALTER SEQUENCE {$sequence_name} INCREMENT BY $change");
        $this->query("SELECT {$sequence_name}.NEXTVAL FROM DUAL");
        $this->query("ALTER SEQUENCE {$sequence_name} INCREMENT BY 1");

    	return true;
    }

	/**
     * @see DBManager::deleteAutoIncrement()
     */
    public function deleteAutoIncrement($table, $field_name)
    {
	  	$sequence_name = $this->_getSequenceName($table, $field_name, true);
	  	if ($this->_findSequence($sequence_name)) {
            $this->query('DROP SEQUENCE ' .$sequence_name);
        }
    }

    /**
     * @see DBManager::updateSQL()
     */
    public function ___updateSQL(SugarBean $bean, array $where = array())
    {
		// get field definitions
        $primaryField = $bean->getPrimaryFieldDefinition();
        $columns = array();

		// get column names and values
		foreach ($bean->getFieldDefinitions() as $fieldDef) {
            $name = $fieldDef['name'];
            if ($name == $primaryField['name'])
                continue;
            if (isset($bean->$name)
                    && (!isset($fieldDef['source']) || $fieldDef['source'] == 'db')) {
                $val = $bean->getFieldValue($name);
			   	// clean the incoming value..
			   	$val = from_html($val);

                if (strlen($val) <= 0)
                    $val = null;

		        // need to do some thing about types of values
		        $val = $this->massageValue($val, $fieldDef);
		        $columns[] = "$name=$val";
            }
		}

		if (sizeof ($columns) == 0)
            return ""; // no columns set

		$where = $this->updateWhereArray($bean, $where);
        $where = $this->getWhereClause($bean, $where);

        // get the entire sql
		return "UPDATE ".$bean->getTableName()." SET ".implode(",", $columns)." $where ";
	}

    /**
     * @see DBManager::get_indices()
     */
    public function get_indices($tablename,$indexname = null)
    {
		$tablename = strtoupper($tablename);
		$indexname = strtoupper($this->getValidDBName($indexname, true, 'index'));

        //find all unique indexes and primary keys.
		$query = <<<EOQ
select a.index_name, c.column_name, b.constraint_type, c.column_position
    from user_indexes a
        inner join user_ind_columns c
            on c.index_name = a.index_name
        left join user_constraints b
            on b.constraint_name = a.index_name
                and b.table_name='$tablename'
    where a.table_name='$tablename'
        and a.index_type='NORMAL'
EOQ;
        if (!empty($indexname)) {
            $query .= " and a.index_name='$indexname'";
        }
        $query .= " order by a.index_name,c.column_position";
        $result = $this->query($query);

        $indices = array();
		while (($row=$this->fetchByAssoc($result)) !=null) {
            $index_type='index';
            if ($row['constraint_type'] =='P')
                $index_type='primary';
            if ($row['constraint_type'] =='U')
                $index_type='unique';

            $name = strtolower($row['index_name']);
            $indices[$name]['name']=$name;
            $indices[$name]['type']=$index_type;
            $indices[$name]['fields'][]=strtolower($row['column_name']);
        }

        return $indices;
	}

    /**
     * Get list of DB column definitions
     */
    public function get_columns($tablename)
    {
        //find all unique indexes and primary keys.
        $result = $this->query(
            "SELECT * FROM user_tab_columns WHERE TABLE_NAME = '".strtoupper($tablename)."'");

        $columns = array();
        while (($row=$this->fetchByAssoc($result)) !=null) {
            $name = strtolower($row['column_name']);
            $columns[$name]['name']=$name;
            $columns[$name]['type']=strtolower($row['data_type']);
            if ( $columns[$name]['type'] == 'number' ) {
                $columns[$name]['len']=
                    ( !empty($row['data_precision']) ? $row['data_precision'] : '3');
                if ( !empty($row['data_scale']) )
                    $columns[$name]['len'].=','.$row['data_scale'];
            }
            elseif ( in_array($columns[$name]['type']
                ,array('date','clob','blob')) ) {
                // do nothing
            }
            else
                $columns[$name]['len']=strtolower($row['char_length']);
            if ( !empty($row['data_default']) ) {
                $matches = array();
                $row['data_default'] = html_entity_decode($row['data_default'],ENT_QUOTES);
                if ( preg_match("/^'(.*)'$/i",$row['data_default'],$matches) )
                    $columns[$name]['default'] = $matches[1];
            }

            $sequence_name = $this->_getSequenceName($tablename, $row['column_name'], true);
            if ($this->_findSequence($sequence_name))
                $columns[$name]['auto_increment'] = '1';
            elseif ( $row['nullable'] == 'N' )
                $columns[$name]['required'] = 'true';
        }
        return $columns;
    }

    /**
     * Returns true if the sequence name given is found
     *
     * @param  string $name
     * @return bool   true if the sequence is found, false otherwise
     * TODO: check if some caching here makes sense, keeping in mind bug 43148
     */
    protected function _findSequence($name)
    {
        $db_user_name = strtoupper(isset($this->configOptions['db_user_name'])?$this->configOptions['db_user_name']:'');

        $uname = strtoupper($name);
        $row = $this->fetchOne(
                "SELECT SEQUENCE_NAME FROM ALL_SEQUENCES WHERE SEQUENCE_OWNER='$db_user_name' AND SEQUENCE_NAME = '$uname'");
        return !empty($row);
    }

	/**
     * @see DBManager::add_drop_constraint()
     */
    public function add_drop_constraint($table, $definition, $drop = false)
    {
        $type         = $definition['type'];
        $fields       = is_array($definition['fields'])?implode(',',$definition['fields']):$definition['fields'];
        $name         = $this->getValidDBName($definition['name'], true, 'index');
        $sql          = '';

        /**
         * Oracle requires indices to be defined as ALTER TABLE statements except for PRIMARY KEY
         * and UNIQUE (which can defined inline with the CREATE TABLE)
         */
        switch ($type){
        // generic indices
        case 'index':
        case 'alternate_key':
        case 'clustered':
            if ($drop)
                $sql = "DROP INDEX {$name}";
            else
                $sql = "CREATE INDEX {$name} ON {$table} ({$fields})";
            break;
        // constraints as indices
        case 'unique':
            if ($drop)
                $sql = "ALTER TABLE {$table} DROP UNIQUE ({$fields})";
            else
                $sql = "ALTER TABLE {$table} ADD CONSTRAINT {$name} UNIQUE ({$fields})";
            break;
        case 'primary':
            if ($drop)
                $sql = "ALTER TABLE {$table} DROP PRIMARY KEY CASCADE";
            else
                $sql = "ALTER TABLE {$table} ADD CONSTRAINT {$name} PRIMARY KEY ({$fields})";
            break;
        case 'foreign':
            if ($drop)
                $sql = "ALTER TABLE {$table} DROP FOREIGN KEY ({$fields})";
            else
                $sql = "ALTER TABLE {$table} ADD CONSTRAINT {$name} FOREIGN KEY ({$fields}) REFERENCES {$definition['foreignTable']}({$definition['foreignField']})";
            break;
        case 'fulltext':
                if($drop) {
                    $sql = "DROP INDEX {$name}";
                } else {
                    $indextype=$definition['indextype'];
                    $parameters="";
                    //add parameters attribute if oracle version of 10 or more.
                    $ver = $this->version();
                    $tok = strtok($ver, '.');
                    if ($tok !== false && $tok > 9) {
                        $parameters = isset($definition['parameters'])
                            ? "parameters ('". $definition['parameters']. "')" : "";
                    }
                   $sql = "CREATE INDEX {$name} ON $table($fields) INDEXTYPE IS $indextype $parameters";
                }
                break;
        }
        return $sql;
	}

    /**
     * @see DBManager::renameIndexDefs()
     */
    public function renameIndexDefs($old_definition, $new_definition, $table_name)
    {
        $old_definition = $this->getValidDBName($old_definition, true, 'index');
        $new_definition = $this->getValidDBName($new_definition, true, 'index');
        return "ALTER INDEX {$old_definition['name']} RENAME TO {$new_definition['name']}";
    }

    /**
     * @see DBManager::massageFieldDef()
     */
    public function massageFieldDef(&$fieldDef, $tablename)
    {
        parent::massageFieldDef($fieldDef,$tablename);

        if ($fieldDef['name'] == 'id')
            $fieldDef['required'] = 'true';
        if ($fieldDef['dbType'] == 'decimal')
            $fieldDef['len'] = '20,2';
        if ($fieldDef['dbType'] == 'decimal2')
            $fieldDef['len'] = '30,6';
        if ($fieldDef['dbType'] == 'double')
            $fieldDef['len'] = '30,10';
        if ($fieldDef['dbType'] == 'float')
            $fieldDef['len'] = '30,6';
        if ($fieldDef['dbType'] == 'uint')
            $fieldDef['len'] = '15';
        if ($fieldDef['dbType'] == 'ulong')
            $fieldDef['len'] = '38';
        if ($fieldDef['dbType'] == 'long')
            $fieldDef['len'] = '38';
        if ($fieldDef['dbType'] == 'enum')
            $fieldDef['len'] = '255';
        if ($fieldDef['dbType'] == 'bool')
            $fieldDef['len'] = '1';
        if ($fieldDef['dbType'] == 'id')
            $fieldDef['len'] = '36';
        if ($fieldDef['dbType'] == 'currency')
            $fieldDef['len'] = '26,6';
        if ($fieldDef['dbType'] == 'short')
            $fieldDef['len'] = '3';
        if ($fieldDef['dbType'] == 'tinyint')
            $fieldDef['len'] = '3';
        if ($fieldDef['dbType'] == 'int')
            $fieldDef['len'] = '3';
        if ($fieldDef['type'] == 'int' && empty($fieldDef['len']) )
            $fieldDef['len'] = '';
        if ($fieldDef['dbType'] == 'enum')
            $fieldDef['len'] = '255';
        if ($fieldDef['type'] == 'varchar2' && empty($fieldDef['len']) )
            $fieldDef['len'] = '255';

    }

    /**
     * Generate an Oracle SEQUENCE name. If the length of the sequence names exceeds a certain amount
     * we will use an md5 of the field name to shorten.
     *
     * @param string $table
     * @param string $field_name
     * @param boolean $upper_case
     * @return string
     */
    protected function _getSequenceName($table, $field_name, $upper_case = true)
    {
        $sequence_name = $this->getValidDBName($table. '_' .$field_name . '_seq', true, 'index');
        if($upper_case)
            $sequence_name = strtoupper($sequence_name);
        return $sequence_name;
    }

    public function emptyValue($type)
    {
        $ctype = $this->getColumnType($type);
        if($ctype == "datetime") {
            return $this->convert($this->quoted("1970-01-01 00:00:00"), "datetime");
        }
        if($ctype == "date") {
            return $this->convert($this->quoted("1970-01-01"), "date");
        }
        if($ctype == "time") {
            return $this->convert($this->quoted("00:00:00"), "time");
        }
        if($ctype == "clob") {
            return "EMPTY_CLOB()";
        }
        if($ctype == "blob") {
            return "EMPTY_BLOB()";
        }
        return parent::emptyValue($type);
    }

    /**
     * (non-PHPdoc)
     * @see DBManager::lastDbError()
     */
    public function lastDbError()
    {
        $err = oci_error($this->database);
        if(is_array($err)) {
            return sprintf("Oracle ERROR %d: %s in %d of [%s]", $err['code'], $err['message'], $err['offset'], $err['sqltext']);
        }
        return false;
    }

    protected $oracle_privs = array(
        "CREATE TABLE" => "CREATE TABLE",
        "DROP TABLE" => "DROP ANY TABLE",
        "INSERT" => "INSERT ANY TABLE",
        "UPDATE" => "UPDATE ANY TABLE",
        "SELECT" => "SELECT ANY TABLE",
        "DELETE" => "DELETE ANY TABLE",
        "ADD COLUMN" => "ALTER ANY TABLE",
        "CHANGE COLUMN" => "ALTER ANY TABLE",
        "DROP COLUMN" => "ALTER ANY TABLE",
    );

    protected $is_express;

    /**
     * Check if we're running Oracle Express edition
     * @return bool
     */
    protected function isExpress()
    {
        if(!is_null($this->is_express)) return $this->is_express;
        $express = $this->getOne('SELECT BANNER AS B FROM V$VERSION WHERE BANNER LIKE \'%Express%\'');
        $this->is_express = !empty($express);
        return $this->is_express;
    }

    /**
     * Check if connecting user has certain privilege
     * @param string $privilege
     */
    public function checkPrivilege($privilege)
    {
        if($this->isExpress()) {
            return parent::checkPrivilege($privilege);
        }
        if(!isset($this->oracle_privs[$privilege])) {
            return parent::checkPrivilege($privilege);
        }

        $oracle_priv = $this->oracle_privs[$privilege];
        $res = $this->getOne("SELECT PRIVILEGE p FROM SESSION_PRIVS WHERE PRIVILEGE = '$oracle_priv'", false);
        return !empty($res);
    }

    public function getDbInfo()
    {
        return array(
            "Server version" => @oci_server_version($this->database),
            "Express" => $this->isExpress(),
        );
    }

    public function validateQuery($query)
    {
        $stmt = oci_parse($this->database, $query);
        if(!$stmt) {
            return false;
        }
        if(oci_statement_type($stmt) != "SELECT") {
            return false;
        }
        $valid = false;
        // try query, but don't generate result set and do not commit
        $res = oci_execute($stmt, OCI_DESCRIBE_ONLY|OCI_DEFAULT);
        if(!empty($res)) {
            // check that we got good metadata
            $name = oci_field_name($stmt, 1);
            if(!empty($name)) {
                $valid = true;
            }
        }
        // just in case, rollback all changes
        oci_rollback($this->database);
        return $valid;
    }

    /**
     * Quote Oracle search term
     * @param string $term
     * @return string
     */
    protected function quoteTerm($term)
    {
        $term = str_replace("*", "%", $term); // Oracle's wildcard is %
        return '{'.$term.'}';
    }

    /**
     * Generate fulltext query from set of terms
     * @param string $fields Field to search against
     * @param array $terms Search terms that may be or not be in the result
     * @param array $must_terms Search terms that have to be in the result
     * @param array $exclude_terms Search terms that have to be not in the result
     */
    public function getFulltextQuery($field, $terms, $must_terms = array(), $exclude_terms = array(), $label = 1)
    {
        $condition = $or_condition = array();
        foreach($must_terms as $term) {
            $condition[] = $this->quoteTerm($term);
        }

        foreach($terms as $term) {
            $or_condition[] = $this->quoteTerm($term);
        }

        if(!empty($or_condition)) {
            $condition[] = " & (".join(" | ", $or_condition).")";
        }

        foreach($exclude_terms as $term) {
            $condition[] = "~".$this->quoteTerm($term);
        }
        $condition = $this->quoted(join(" & ",$condition));
        return "CONTAINS($field, $condition, $label) > 0";
    }

    /**
     * (non-PHPdoc)
     * @see DBManager::getScriptName()
     */
    public function getScriptName()
    {
        return "oracle";
    }

    /**
     * Execute data manipulation statement, then roll it back
     * @param  $type Statement type
     * @param  $table Table name
     * @param  $query Query to validate
     * @return string|bool String will be not empty if there's any
     */
    protected function verifyGenericQueryRollback($type, $table, $query)
    {
        $this->log->debug("verifying $type statement");
        $stmt = oci_parse($this->database, $query);
        if(!$stmt) {
            return 'Cannot parse statement';
        }
        if(oci_statement_type($stmt) != "SELECT") {
            return 'Wrong statement type';
        }
        // try query, but don't generate result set and do not commit
        $res = oci_execute($stmt, OCI_DESCRIBE_ONLY|OCI_DEFAULT);
        // just in case, rollback all changes
        $error = $this->lastError();
        oci_rollback($this->database);
        if(empty($res)) {
            return 'Query failed to execute';
        }
        return $error;
    }

    /**
     * Tests an INSERT INTO query
     * @param string table The table name to get DDL
     * @param string query The query to test.
     * @return string Non-empty if error found
     */
    public function verifyInsertInto($table, $query)
    {
        return $this->verifyGenericQueryRollback("INSERT", $table, $query);
    }

    /**
     * Tests an UPDATE query
     * @param string table The table name to get DDL
     * @param string query The query to test.
     * @return string Non-empty if error found
     */
    public function verifyUpdate($table, $query)
    {
        return $this->verifyGenericQueryRollback("UPDATE", $table, $query);
    }

    /**
     * Tests an DELETE FROM query
     * @param string table The table name to get DDL
     * @param string query The query to test.
     * @return string Non-empty if error found
     */
    public function verifyDeleteFrom($table, $query)
    {
        return $this->verifyGenericQueryRollback("DELETE", $table, $query);
    }

    /**
     * Check if certain database exists
     * @param string $dbname
     */
    public function dbExists($dbname)
    {
        // We don't check DB in Oracle, admin creates it
        return true;
    }

    /**
     * Check if certain database exists
     * @param string $dbname
     */
    public function userExists($dbname)
    {
        // We don't check DB in Oracle, admin creates it
        return true;
    }

    /**
     * Create DB user
     * @param string $database_name
     * @param string $host_name
     * @param string $user
     * @param string $password
     */
    public function createDbUser($database_name, $host_name, $user, $password)
    {
        // We don't create users in Oracle, admin does that
        return true;
    }

    /**
     * Create a database
     * @param string $dbname
     */
    public function createDatabase($dbname)
    {
        // We don't create DBs in Oracle, admin does that
        return true;
    }

    /**
     * Drop a database
     * @param string $dbname
     */
    public function dropDatabase($dbname)
    {
        // // We don't create DBs in Oracle, admin does that
        return true;
    }

    /**
     * Check if this driver can be used
     * @return bool
     */
    public function valid()
    {
        return function_exists("ocilogon");
    }

    public function full_text_indexing_installed()
    {
        return true;
    }

    /**
     * Check if this DB name is valid
     *
     * @param string $name
     * @return bool
     */
    public function isDatabaseNameValid($name)
    {
        // No funny chars
        return preg_match('/[\#\"\'\*\/\\?\:\\<\>\-\ \&\!\(\)\[\]\{\}\;\,\.\`\~\|\\\\]+/', $name)==0;
    }

    /**
     * Check DB version
     * @see DBManager::canInstall()
     */
    public function canInstall()
    {
        $version = $this->version();
        if(empty($version)) {
            return array('ERR_DB_VERSION_FAILURE');
        }
        if(!preg_match("/^(9|10|11)\\./i", $version)) {
            return array('ERR_DB_OCI8_VERSION', $version);
        }
        return true;
    }

    public function installConfig()
    {
        return array(
        	'LBL_DBCONFIG_ORACLE' =>  array(
                "setup_db_database_name" => array("label" => 'LBL_DBCONF_DB_NAME', "required" => true),
                "setup_db_host_name" => false,
                'setup_db_create_sugarsales_user' => false,
            ),
			'LBL_DBCONFIG_B_MSG1' => array(
				"setup_db_admin_user_name" => array("label" => 'LBL_DBCONF_DB_ADMIN_USER', "required" => true),
				"setup_db_admin_password" => array("label" => 'LBL_DBCONF_DB_ADMIN_PASSWORD', "type" => "password"),
			),
        );
    }
}
