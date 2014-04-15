<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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


/*********************************************************************************

 * Description: This file handles the Data base functionality for prepared Statements
 * It acts as the prepared statement abstraction layer for the application.
 *
 * All the functions in this class will work with any bean which implements the meta interface.
 * The passed bean is passed to helper class which uses these functions to generate correct sql.
 *
 * The meta interface has the following functions:
 */



 /* to summarize what we are trying to do in Oracle:

    $stmt = oci_parse($conn,"INSERT INTO testPreparedStatement (id, col1, col2) VALUES(:p1, :p2, :p3)");
    $bound = array();
    oci_bind_by_name($stmt, ":p1", $bound[1], -1, $dataTypes[1]);
    oci_bind_by_name($stmt, ":p2", $bound[2], -1, $dataTypes[2]);
    oci_bind_by_name($stmt, ":p3", $bound[3], -1, $dataTypes[3]);
    oci_execute($stmt);

  The stages it goes through are:

    DBManager.insertParams
        Table:      testPreparedStatement
        Field_defs: { ['id']   => { ['name'] => 'id',   ['type'] => 'id',      ['required']=>true },
                    { ['col1'] => { ['name'] => 'col1', ['type'] => 'varchar', ['len'] => '100' },
                    { ['col2'] => { ['name'] => 'col2', ['type'] => 'varchar', ['len'] => '100' },
        Data:       { ['id'] => 3, ['col1'] => "col1 data for id 3", ['col2'] => "col2 data for id 3" }
        Field_map:  null

    PreparedStatement.__construct
	SQL:   INSERT INTO testPreparedStatement (id,col1,col2) VALUES (?id,?varchar,?varchar)
        Data:  { ["id"]=> "'3'", ["col1"]=> "'col1 data for id 3'", ["col2"]=> "'col2 data for id 3'" }

    OraclePreparedStatement.preparePreparedStatement
	SQL:   INSERT INTO testPreparedStatement (id,col1,col2) VALUES (?,?,?)
        Data:  { ["id"]=> "'3'", ["col1"]=> "'col1 data for id 3'", ["col2"]=> "'col2 data for id 3'" }
	Types: { [0]=> {["type"]=>"id"}, [1]=>{["type"]=>"varchar"}, [2]=>{["type"]=>"varchar"}

        $stmt = oci_parse($conn,"INSERT INTO testPreparedStatement (id, col1, col2) VALUES(:p1, :p2, :p3)");
        $bound = array();
        oci_bind_by_name($stmt, ":p1", $bound[1], -1, $dataTypes[1]);
        oci_bind_by_name($stmt, ":p2", $bound[2], -1, $dataTypes[2]);
        oci_bind_by_name($stmt, ":p3", $bound[3], -1, $dataTypes[3]);

        Data: $bound = { [1]=> "3", [2]=> "col1 data for id 3", [3]=> "col2 data for id 3" }
        oci_execute($stmt);
 */

require_once 'include/database/PreparedStatement.php';

class OraclePreparedStatement extends PreparedStatement
{


    /*
     * Maps column datatypes to MySQL bind variable types
     *
     * Oracle type defs
     *
     *     SQLT_BFILEE or OCI_B_BFILE    - for BFILEs;
     *     SQLT_CFILEE or OCI_B_CFILEE   - for CFILEs;
     *     SQLT_CLOB   or OCI_B_CLOB     - for CLOBs;
     *     SQLT_BLOB   or OCI_B_BLOB     - for BLOBs;
     *     SQLT_RDD    or OCI_B_ROWID    - for ROWIDs;
     *     SQLT_NTY    or OCI_B_NTY      - for named datatypes;
     *     SQLT_INT    or OCI_B_INT      - for integers;
     *     SQLT_CHR                      - for VARCHARs, Converts the PHP parameter to a string type and binds as a string.;
     *     SQLT_LVC                      - Used with oci_bind_array_by_name() to bind arrays of LONG VARCHAR.
     *     SQLT_STR                      - Used with oci_bind_array_by_name() to bind arrays of STRING.
     *     SQLT_BIN    or OCI_B_BIN      - for RAW columns;
     *     SQLT_LNG                      - for LONG columns;
     *     SQLT_ODT                      - for LONG columns;
     *     SQLT_LBI                      - for LONG RAW columns;
     *     SQLT_RSET   or OCI_B_CURSOR   - for cursors created with oci_new_cursor(). Used with oci_bind_by_name() when binding cursors,
     *                                     previously allocated with oci_new_descriptor().
     *     SQLT_NUM    or OCI_B_NUM      - Converts the PHP parameter to a 'C' long type, and binds to that value.
     *                                     Used with oci_bind_array_by_name() to bind arrays of NUMBER.
     *     SQLT_FLT                      - Used with oci_bind_array_by_name() to bind arrays of FLOAT.
     *                    OCI_B_CURSOR   -
     *     SQLT_AFC                      - Used with oci_bind_array_by_name() to bind arrays of CHAR.
     *     SQLT_AVC                      - Used with oci_bind_array_by_name() to bind arrays of VARCHAR2.
     *     SQLT_VCS                      - Used with oci_bind_array_by_name() to bind arrays of VARCHAR.
     */

    /**
     * Maps SQL column datatypes to OCI bind variable types
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
            'string'           => SQLT_CHR,
            'date'             => SQLT_CHR,
            'time'             => SQLT_CHR,
            'float'            => SQLT_FLT,
            'bigint'           => SQLT_LNG,
            'int'              => SQLT_INT,
            'bool'             => OCI_B_BOL,
    );

    /**
     * List of LOB fields
     * index => name
     * @var array
     */
    protected $lobFields = array();

    public function preparePreparedStatement( $msg = '' )
    {
        if(empty($this->parsedSQL)) {
            $this->DBM->registerError($msg, "Empty SQL query");
            return false;
        }

        $GLOBALS['log']->info('QueryPrepare: ' . $this->parsedSQL);

        $i = 0;
        $db = $this->DBM;
        $defs = $this->fieldDefs;
        $oSQL = preg_replace_callback('#\?#',
            function() use(&$i, $db, $defs) {
                ++$i;
                if($db->isTextType($defs[$i]["type"]) || !empty($this->lobFields[$i])) {
                    // BLOBS should have empty(...) in the query
                    return $db->emptyValue($defs[$i]["type"]);
                }
                return $db->convert(":p$i", $defs[$i]["type"]);
            },
            $this->parseSQL);

        if(!empty($this->lobFields)) {
            $oSQL .= ' RETURNING '.implode(",", array_values($this->lobFields)).
                ' INTO :p'.implode(",p:", array_keys($this->lobFields));
        }

        // do the prepare
        $this->stmt = oci_parse($this->dblink, $oSQL);
        if($this->DBM->checkError(" QueryPrepare Failed: $msg for sql: $oSQL ::")) {
            return false;
        }

        // bind the array elements
        $num_args = count($this->fieldDefs);
        $this->bound_vars = array_fill(0, $num_args, null);

        for($i=0; $i<$num_args;$i++) {
            if(empty($this->fieldDefs[$i]["type"])) {
                $this->DBM->registerError($msg, "No defs entry for parameter $i");
                return false;
            }

            $type = $this->fieldDefs[$i]["type"];
            if($this->DBM->isTextType($type)) {
                if($this->DBM->getColumnType($type) == 'clob') {
                    $mappedType = OCI_B_CLOB;
                } else {
                    $mappedType = OCI_B_BLOB;
                }
            } else {
                $mappedType = $this->DBM->getTypeClass($type);
                if(!empty($this->typeMap[$mappedType])) {
                    $mappedType = $this->typeMap[$mappedType];
                } else {
                    $mappedType = SQLT_CHR;
                }
            }

            oci_bind_by_name($this->stmt, ":p$i", $this->bound_vars[$i], -1, $mappedType);
            if($this->DBM->checkError("$msg: QueryPrepare Failed for parameter $i")) {
                oci_free_statement($this->stmt);
                $this->stmt = null;
                return false;
            }
        }

        return $this;
    }

    /**
     * Set LOB fields
     * @param array $lob_fields
     */
    public function setLobs(array $lob_fields)
    {
        $this->lobFields = $lob_fields;
        return $this;
    }

    public function executePreparedStatement(array $data,  $msg = '')
    {
        if(!$this->prepareStatementData($data, count($this->fieldDefs), $msg)) {
            return false;
        }
        foreach($this->lobFields as $idx => $name) {
            $this->bound_vars[$idx] = oci_new_descriptor($this->dblink, OCI_D_LOB);
        }

        $res = oci_execute($this->stmt, OCI_NO_AUTO_COMMIT);
        foreach($this->lobFields as $idx => $name) {
            if(!$this->bound_vars[$idx]->save($data[$idx])) {
                $this->DBM->checkError("$msg: Saving BLOB for {$data[$idx]}");
                oci_rollback($this->dblink);
                return false;
            }
        }
        oci_commit($this->dblink);

        return $this->finishStatement($res, $msg);
    }

    public function preparedStatementFetch($msg = '')
    {
        if(!$this->stmt) {
            return false;
        }
        // Just go to regular fetch
        return $this->DBM->fetchRow($this->stmt);
    }

    public function preparedStatementClose()
    {
        foreach($this->lobFields as $idx => $name) {
            if(!empty($this->bound_vars[$idx])) {
                $this->bound_vars[$idx]->free();
            }
        }
    }
}
