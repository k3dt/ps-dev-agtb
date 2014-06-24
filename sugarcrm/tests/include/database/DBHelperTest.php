<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
 
require_once 'include/database/DBManagerFactory.php';
require_once 'modules/Contacts/Contact.php';
require_once 'modules/Cases/Case.php';

class DBHelperTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_db;
    private $_helper;

    static public function setUpBeforeClass()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
    }

    static public function tearDownAfterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['app_strings']);
    }

    public function setUp()
    {
        $this->_db = DBManagerFactory::getInstance();
        $this->_helper = $this->_db;
    }

    public function tearDown()
    {
        $this->_db->disconnect();
    }

    public function testCreateTableSQL()
    {
        $sql = $this->_helper->createTableSQL(new Contact);

        $this->assertRegExp('/create\s*table\s*contacts/i',$sql);
    }

    public function testCreateTableSQLParams()
    {
        $bean = BeanFactory::getBean('Contacts');

        $sql = $this->_helper->createTableSQLParams(
            $bean->getTableName(),
            $bean->getFieldDefinitions(),
            $bean->getIndices());

        $this->assertRegExp('/create\s*table\s*contacts/i',$sql);
    }

    public function testInsertSQL()
    {
        $sql = $this->_helper->insertSQL(new Contact);

        $this->assertRegExp('/insert\s*into\s*contacts/i',$sql);
    }

    /**
     * ticket 38216
     */
    public function testInsertSQLProperlyDecodesHtmlEntities()
    {
        $bean = BeanFactory::getBean('Contacts');
        $bean->last_name = '&quot;Test&quot;';

        $sql = $this->_helper->insertSQL($bean);

        $this->assertNotContains("&quot;",$sql);
    }

    public function testUpdateSQL()
    {
        $sql = $this->_helper->updateSQL(new Contact, array("id" => "1"));

        $this->assertRegExp('/update\s*contacts\s*set/i',$sql);
        $this->assertRegExp('/where\s*contacts.id\s*=\s*\'1\'/i',$sql);
    }

    /**
     * ticket 38216
     */
    public function testUpdateSQLProperlyDecodesHtmlEntities()
    {
        $bean = BeanFactory::getBean('Contacts');
        $bean->last_name = '&quot;Test&quot;';

        $sql = $this->_helper->updateSQL($bean, array("id" => "1"));

        $this->assertNotContains("&quot;",$sql);
    }

    public function testDeleteSQL()
    {
        $sql = $this->_helper->deleteSQL(new Contact, array("id" => "1"));

        $this->assertRegExp('/update\s*contacts\s*set\s*deleted\s*=\s*1/i',$sql);
        $this->assertRegExp('/where\s*contacts.id\s*=\s*\'1\'/i',$sql);
    }

    public function testRetrieveSQL()
    {
        $sql = $this->_helper->retrieveSQL(new Contact, array("id" => "1"));

        $this->assertRegExp('/select\s*\*\s*from\s*contacts/i',$sql);
        $this->assertRegExp('/where\s*contacts.id\s*=\s*\'1\'/i',$sql);
    }

    public function testRetrieveViewSQL()
    {
        // TODO: write this test
    }

    public function testCreateIndexSQL()
    {
        $sql = $this->_helper->createIndexSQL(
            new Contact,
            array('id' => array('name'=>'id')),
            'idx_id');

        $this->assertRegExp('/create\s*unique\s*index\s*idx_id\s*on\s*contacts\s*\(\s*id\s*\)/i',$sql);

        $sql = $this->_helper->createIndexSQL(
            new Contact,
            array('id' => array('name'=>'id')),
            'idx_id',
            false);

        $this->assertRegExp('/create\s*index\s*idx_id\s*on\s*contacts\s*\(\s*id\s*\)/i',$sql);

        $sql = $this->_helper->createIndexSQL(
            new Contact,
            array('id' => array('name'=>'id'),'deleted' => array('name'=>'deleted')),
            'idx_id');

        $this->assertRegExp('/create\s*unique\s*index\s*idx_id\s*on\s*contacts\s*\(\s*id\s*,\s*deleted\s*\)/i',$sql);
    }

    public function testGetFieldType()
    {
        $fieldDef = array(
            'dbType'    => 'varchar',
            'dbtype'    => 'int',
            'type'      => 'char',
            'Type'      => 'bool',
            'data_type' => 'email',
            );

        $this->assertEquals($this->_helper->getFieldType($fieldDef),'varchar');
        unset($fieldDef['dbType']);
        $this->assertEquals($this->_helper->getFieldType($fieldDef),'int');
        unset($fieldDef['dbtype']);
        $this->assertEquals($this->_helper->getFieldType($fieldDef),'char');
        unset($fieldDef['type']);
        $this->assertEquals($this->_helper->getFieldType($fieldDef),'bool');
        unset($fieldDef['Type']);
        $this->assertEquals($this->_helper->getFieldType($fieldDef),'email');
    }
    public function testGetAutoIncrement()
    {
        $case = BeanFactory::getBean('Cases');
        $case->name = "foo";
        $case->save();
        $case->retrieve($case->id);
        $lastAuto = $case->case_number;
        $helperResult = $this->_helper->getAutoIncrement("cases", "case_number");

//        $GLOBALS['db']->query("DELETE FROM cases WHERE id= '{$case->id}'");

        $this->assertEquals($lastAuto + 1, $helperResult);
    }
    //BEGIN SUGARCRM flav=ent ONLY
    public function testGetAutoIncrementSQL()
    {
        if( $this->_db->dbType != 'oci8') {
            $this->markTestSkipped('Only applies to Oracle');
        }

        $sql = $this->_helper->getAutoIncrementSQL('cases', 'case_number');
        $this->assertRegExp('/cases_case_number_seq\.nextval/i',$sql);
    }
    //END SUGARCRM flav=ent ONLY
    public function testSetAutoIncrementStart()
    {
        $case = BeanFactory::getBean('Cases');
        $case->name = "foo";
        $case->save();
        $case->retrieve($case->id);
        $lastAuto = $case->case_number;
        $case->deleted = true;
        $case->save();
    	$newAuto = $lastAuto + 5;
        $this->_helper->setAutoIncrementStart("cases", "case_number", $newAuto);
        $case2 = BeanFactory::getBean('Cases');
        $case2->name = "foo2";
        $case2->save();
        $case2->retrieve($case2->id);
        $case_number = $case2->case_number;

        $GLOBALS['db']->query("DELETE FROM cases WHERE id= '{$case->id}'");
        $GLOBALS['db']->query("DELETE FROM cases WHERE id= '{$case2->id}'");

        $this->assertEquals($newAuto, $case_number);
    }
    public function testAddColumnSQL()
    {
        $sql = $this->_helper->addColumnSQL(
            'contacts',
            array('foo' => array('name'=>'foo','type'=>'varchar'))
            );

        $this->assertRegExp('/alter\s*table\s*contacts/i',$sql);
    }

    public function testAlterColumnSQL()
    {
        $sql = $this->_helper->alterColumnSQL(
            'contacts',
            array('foo' => array('name'=>'foo','type'=>'varchar'))
            );

        // Generated SQL may be a sequence of statements
		switch(gettype($sql)){
			case 'array':
				$sql = $sql[0];
			case 'string':
				$this->assertRegExp('/alter\s*table\s*contacts/i',$sql);
				break;
			}
    }

    public function testDropTableSQL()
    {
        $sql = $this->_helper->dropTableSQL(new Contact);

        $this->assertRegExp('/drop\s*table.*contacts/i',$sql);
    }

    public function testDropTableNameSQL()
    {
        $sql = $this->_helper->dropTableNameSQL('contacts');

        $this->assertRegExp('/drop\s*table.*contacts/i',$sql);
    }

    public function testDeleteColumnSQL()
    {
        $sql = $this->_helper->deleteColumnSQL(
            new Contact,
            array('foo' => array('name'=>'foo','type'=>'varchar'))
            );
        //BEGIN SUGARCRM flav=ent ONLY
        if ( $this->_db->dbType == 'oci8' )
            $this->assertRegExp('/alter\s*table\s*contacts\s*drop\s*column\s*\(\s*foo\s*\)/i',$sql);
        else
        //END SUGARCRM flav=ent ONLY
            $this->assertRegExp('/alter\s*table\s*contacts\s*drop\s*column\s*foo/i',$sql);
    }

    public function testDropColumnSQL()
    {
        $sql = $this->_helper->dropColumnSQL(
            'contacts',
            array('foo' => array('name'=>'foo','type'=>'varchar'))
            );
        //BEGIN SUGARCRM flav=ent ONLY
        if ( $this->_db->dbType == 'oci8' )
            $this->assertRegExp('/alter\s*table\s*contacts\s*drop\s*column\s*\(\s*foo\s*\)/i',$sql);
        else
        //END SUGARCRM flav=ent ONLY
            $this->assertRegExp('/alter\s*table\s*contacts\s*drop\s*column\s*foo/i',$sql);
    }

    public function testMassageValue()
    {
        $this->assertEquals(
            $this->_helper->massageValue(123,array('name'=>'foo','type'=>'int')),
            123
            );
        if (in_array($this->_db->dbType, array('mssql'
            //BEGIN SUGARCRM flav=ent ONLY
            ,'oci8', 'ibm_db2'
            //END SUGARCRM flav=ent ONLY
            )))
            $this->assertEquals(
                $this->_helper->massageValue("'dog'",array('name'=>'foo','type'=>'varchar')),
                "'''dog'''"
                );
        else
            $this->assertEquals(
                $this->_helper->massageValue("'dog'",array('name'=>'foo','type'=>'varchar')),
                "'\\'dog\\''"
                );
    }

    public function testGetColumnType()
    {
        switch($this->_db->dbType){
        //BEGIN SUGARCRM flav=ent ONLY
            case 'oci8': $expected_type = 'number'; break;
            case 'ibm_db2': $expected_type = 'integer'; break;
        //END SUGARCRM flav=ent ONLY
            default:
                $expected_type = 'int';
        }

        $this->assertEquals($expected_type, $this->_helper->getColumnType('int'));
    }

    public function testIsFieldArray()
    {
        $this->assertTrue(
            $this->_helper->isFieldArray(array('name'=>'foo','type'=>array('int')))
            );

        $this->assertFalse(
            $this->_helper->isFieldArray(array('name'=>'foo','type'=>'int'))
            );

        $this->assertTrue(
            $this->_helper->isFieldArray(array('name'=>'foo'))
            );

        $this->assertFalse(
            $this->_helper->isFieldArray(1)
            );
    }

    public function testSaveAuditRecords()
    {
        // TODO: write this test
    }

    public function testGetDataChanges()
    {
        // TODO: write this test
    }

    public function testQuoted()
    {
        $this->assertEquals(
            "'".$this->_db->quote('foobar')."'",
            $this->_db->quoted('foobar')
            );
    }

    public function testGetIndices()
    {
        $indices = $this->_helper->get_indices('contacts');

        foreach ( $indices as $index ) {
            $this->assertTrue(!empty($index['name']));
            $this->assertTrue(!empty($index['type']));
            $this->assertTrue(!empty($index['fields']));
        }
    }

    public function testAddDropConstraint()
    {
        $tablename = 'test' . date("YmdHis");
        $sql = $this->_helper->add_drop_constraint(
            $tablename,
            array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
                ),
            false
            );

        $this->assertRegExp("/idx_foo/i",$sql);
        $this->assertRegExp("/foo/i",$sql);

        $tablename = 'test' . date("YmdHis");
        $sql = $this->_helper->add_drop_constraint(
            $tablename,
            array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
                ),
            true
            );

        $this->assertRegExp("/idx_foo/i",$sql);
        $this->assertRegExp("/foo/i",$sql);
        $this->assertRegExp("/drop/i",$sql);
    }

    public function testRenameIndex()
    {
        // TODO: write this test
    }

    public function testNumberOfColumns()
    {
        $tablename = 'test' . date("YmdHis");
        $this->_db->createTableParams($tablename,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );

        $this->assertEquals($this->_helper->number_of_columns($tablename),1);

        $this->_db->dropTableName($tablename);
    }

    public function testGetColumns()
    {
        $vardefs = $this->_helper->get_columns('contacts');

        $this->assertTrue(isset($vardefs['id']));
        $this->assertTrue(isset($vardefs['id']['name']));
        $this->assertTrue(isset($vardefs['id']['type']));
    }

    public function testMassageFieldDefs()
    {
        // TODO: write this test
    }

    /**
     * @ticket 22921
     */
    public function testEmptyPrecision()
    {
        //BEGIN SUGARCRM flav=ent ONLY
            if (in_array($this->_db->dbType, array('oci8', 'ibm_db2')))
                $this->markTestSkipped("Skipping on {$this->_db->dbType}, as it doesn't apply to this backend");
        //END SUGARCRM flav=ent ONLY
        $sql = $this->_helper->alterColumnSQL(
            'contacts',
            array('compensation_min' =>
                 array(
                   'required' => false,
                   'name' => 'compensation_min',
                   'vname' => 'LBL_COMPENSATION_MIN',
                   'type' => 'float',
                   'massupdate' => 0,
                   'comments' => '',
                   'help' => '',
                   'importable' => 'true',
                   'duplicate_merge' => 'disabled',
                   'duplicate_merge_dom_value' => 0,
                   'audited' => 0,
                   'reportable' => 1,
                   'len' => '18',
                   'precision' => '',
                   ),
                 )
            );

        $this->assertNotRegExp('/float\s*\(18,\s*\)/i',$sql);
        $this->assertRegExp('/float\s*\(18\)/i',$sql);
    }

    /**
     * @ticket 22921
     */
    public function testBlankSpacePrecision()
    {
        //BEGIN SUGARCRM flav=ent ONLY
        if (in_array($this->_db->dbType, array('oci8', 'ibm_db2')))
            $this->markTestSkipped("Skipping on {$this->_db->dbType}, as it doesn't apply to this backend");
        //END SUGARCRM flav=ent ONLY
        $sql = $this->_helper->alterColumnSQL(
            'contacts',
            array('compensation_min' =>
                 array(
                   'required' => false,
                   'name' => 'compensation_min',
                   'vname' => 'LBL_COMPENSATION_MIN',
                   'type' => 'float',
                   'massupdate' => 0,
                   'comments' => '',
                   'help' => '',
                   'importable' => 'true',
                   'duplicate_merge' => 'disabled',
                   'duplicate_merge_dom_value' => 0,
                   'audited' => 0,
                   'reportable' => 1,
                   'len' => '18',
                   'precision' => ' ',
                   ),
                 )
            );

        $this->assertNotRegExp('/float\s*\(18,\s*\)/i',$sql);
        $this->assertRegExp('/float\s*\(18\)/i',$sql);
    }

    /**
     * @ticket 22921
     */
    public function testSetPrecision()
    {
        //BEGIN SUGARCRM flav=ent ONLY
        if (in_array($this->_db->dbType, array('oci8', 'ibm_db2')))
            $this->markTestSkipped("Skipping on {$this->_db->dbType}, as it doesn't apply to this backend");
        //END SUGARCRM flav=ent ONLY
        $sql = $this->_helper->alterColumnSQL(
            'contacts',
            array('compensation_min' =>
                 array(
                   'required' => false,
                   'name' => 'compensation_min',
                   'vname' => 'LBL_COMPENSATION_MIN',
                   'type' => 'float',
                   'massupdate' => 0,
                   'comments' => '',
                   'help' => '',
                   'importable' => 'true',
                   'duplicate_merge' => 'disabled',
                   'duplicate_merge_dom_value' => 0,
                   'audited' => 0,
                   'reportable' => 1,
                   'len' => '18',
                   'precision' => '2',
                   ),
                 )
            );

        if ( $this->_db->dbType == 'mssql' )
			$this->assertRegExp('/float\s*\(18\)/i',$sql);
        else
        	$this->assertRegExp('/float\s*\(18,2\)/i',$sql);
    }

    /**
     * @ticket 22921
     */
    public function testSetPrecisionInLen()
    {
        //BEGIN SUGARCRM flav=ent ONLY
        if (in_array($this->_db->dbType, array('oci8', 'ibm_db2')))
            $this->markTestSkipped("Skipping on {$this->_db->dbType}, as it doesn't apply to this backend");
        //END SUGARCRM flav=ent ONLY
        $sql = $this->_helper->alterColumnSQL(
            'contacts',
            array('compensation_min' =>
                 array(
                   'required' => false,
                   'name' => 'compensation_min',
                   'vname' => 'LBL_COMPENSATION_MIN',
                   'type' => 'float',
                   'massupdate' => 0,
                   'comments' => '',
                   'help' => '',
                   'importable' => 'true',
                   'duplicate_merge' => 'disabled',
                   'duplicate_merge_dom_value' => 0,
                   'audited' => 0,
                   'reportable' => 1,
                   'len' => '18,2',
                   ),
                 )
            );
        if ( $this->_db->dbType == 'mssql' )
			$this->assertRegExp('/float\s*\(18\)/i',$sql);
        else
        	$this->assertRegExp('/float\s*\(18,2\)/i',$sql);
    }

    /**
     * @ticket 22921
     */
    public function testEmptyPrecisionMassageFieldDef()
    {
        //BEGIN SUGARCRM flav=ent ONLY
        if (in_array($this->_db->dbType, array('oci8', 'ibm_db2')))
            $this->markTestSkipped("Skipping on {$this->_db->dbType}, as it doesn't apply to this backend");
        //END SUGARCRM flav=ent ONLY
        $fielddef = array(
               'required' => false,
               'name' => 'compensation_min',
               'vname' => 'LBL_COMPENSATION_MIN',
               'type' => 'float',
               'massupdate' => 0,
               'comments' => '',
               'help' => '',
               'importable' => 'true',
               'duplicate_merge' => 'disabled',
               'duplicate_merge_dom_value' => 0,
               'audited' => 0,
               'reportable' => 1,
               'len' => '18',
               'precision' => '',
            );
        $this->_helper->massageFieldDef($fielddef,'mytable');

        $this->assertEquals("18",$fielddef['len']);
    }

    /**
     * @ticket 22921
     */
    public function testBlankSpacePrecisionMassageFieldDef()
    {
        //BEGIN SUGARCRM flav=ent ONLY
        if (in_array($this->_db->dbType, array('oci8', 'ibm_db2')))
            $this->markTestSkipped("Skipping on {$this->_db->dbType}, as it doesn't apply to this backend");
        //END SUGARCRM flav=ent ONLY
        $fielddef = array(
               'required' => false,
               'name' => 'compensation_min',
               'vname' => 'LBL_COMPENSATION_MIN',
               'type' => 'float',
               'massupdate' => 0,
               'comments' => '',
               'help' => '',
               'importable' => 'true',
               'duplicate_merge' => 'disabled',
               'duplicate_merge_dom_value' => 0,
               'audited' => 0,
               'reportable' => 1,
               'len' => '18',
               'precision' => ' ',
            );
        $this->_helper->massageFieldDef($fielddef,'mytable');

        $this->assertEquals("18",$fielddef['len']);
    }

    /**
     * @ticket 22921
     */
    public function testSetPrecisionMassageFieldDef()
    {
        //BEGIN SUGARCRM flav=ent ONLY
        if (in_array($this->_db->dbType, array('oci8', 'ibm_db2')))
            $this->markTestSkipped("Skipping on {$this->_db->dbType}, as it doesn't apply to this backend");
        //END SUGARCRM flav=ent ONLY
        $fielddef = array(
               'required' => false,
               'name' => 'compensation_min',
               'vname' => 'LBL_COMPENSATION_MIN',
               'type' => 'float',
               'massupdate' => 0,
               'comments' => '',
               'help' => '',
               'importable' => 'true',
               'duplicate_merge' => 'disabled',
               'duplicate_merge_dom_value' => 0,
               'audited' => 0,
               'reportable' => 1,
               'len' => '18',
               'precision' => '2',
            );
        $this->_helper->massageFieldDef($fielddef,'mytable');

        $this->assertEquals("18,2",$fielddef['len']);
    }

    /**
     * @ticket 22921
     */
    public function testSetPrecisionInLenMassageFieldDef()
    {
        //BEGIN SUGARCRM flav=ent ONLY
        if (in_array($this->_db->dbType, array('oci8', 'ibm_db2')))
            $this->markTestSkipped("Skipping on {$this->_db->dbType}, as it doesn't apply to this backend");
        //END SUGARCRM flav=ent ONLY
        $fielddef = array(
               'required' => false,
               'name' => 'compensation_min',
               'vname' => 'LBL_COMPENSATION_MIN',
               'type' => 'float',
               'massupdate' => 0,
               'comments' => '',
               'help' => '',
               'importable' => 'true',
               'duplicate_merge' => 'disabled',
               'duplicate_merge_dom_value' => 0,
               'audited' => 0,
               'reportable' => 1,
               'len' => '18,2',
            );
        $this->_helper->massageFieldDef($fielddef,'mytable');

        $this->assertEquals("18,2",$fielddef['len']);
    }

    public function testGetSelectFieldsFromQuery()
    {
        $i=0;
        foreach(array("", "DISTINCT ") as $distinct) {
            $fields = array();
            $expected = array();
            foreach(array("field", "''", "'data'", "sometable.field") as $data) {
                if($data[0] != "'") {
                    $data .= $i++;
                    $fields[] = "{$distinct}$data";
                    $dotfields = explode('.', $data);
                    $expected[] = $dotfields[count($dotfields)-1];
                }
                $as = "otherfield".($i++);
                $fields[] = "{$distinct}$data $as";
                $expected[] = $as;
                $as = "otherfield".($i++);
                $fields[] = "{$distinct}$data as $as";
                $expected[] = $as;
            }
            $query = "SELECT ".join(', ', $fields);
            $result = $this->_helper->getSelectFieldsFromQuery($query);
            foreach($expected as $expect) {
                $this->assertContains($expect, array_keys($result), "Result should include $expect");
            }
        }
    }
}
