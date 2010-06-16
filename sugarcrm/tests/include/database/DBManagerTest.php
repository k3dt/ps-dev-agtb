<?php
require_once 'include/database/DBManagerFactory.php';
require_once 'modules/Contacts/Contact.php';

class DBManagerTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_db;
    
    protected $backupGlobals = FALSE;
    
    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->_db = DBManagerFactory::getInstance();
		$GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
    }
    
    public function tearDown() 
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['app_strings']);
    }
    
    private function _createRecords(
        $num
        )
    {
        $beanIds = array();
        for ( $i = 0; $i < $num; $i++ ) {
            $bean = new Contact();
            $bean->id = "$i-test" . date("YmdHis");
            $bean->last_name = "foobar";
            $this->_db->insert($bean);
            $beanIds[] = $bean->id;
        }
        
        return $beanIds;
    }
    
    private function _removeRecords(
        array $ids
        )
    {
        foreach ($ids as $id)
            $this->_db->query("DELETE From contacts where id = '{$id}'");
    }
    
    public function testGetTableName()
    {
        $this->_db->createTableParams('MyTableName',array('foo'=>'foo'),array());
        
        $this->assertEquals($this->_db->getTableName(),'MyTableName');
    }
    
    public function testGetDatabase()
    {
        if ( $this->_db instanceOf MysqliManager )
            $this->assertType('Mysqli',$this->_db->getDatabase());
        else
            $this->assertTrue(is_resource($this->_db->getDatabase()));
    }
    
    public function testGetHelper()
    {
        $this->assertType('DBHelper',$this->_db->getHelper());
    }
    
    public function testCheckError()
    {
        $this->assertFalse($this->_db->checkError());
    }
    
    public function testCheckErrorNoConnection()
    {
        $this->_db->disconnect();
        $this->assertTrue($this->_db->checkError());
        $this->_db = &DBManagerFactory::getInstance();
    }
    
    public function testGetQueryTime()
    {
        $this->_db->version();
        $this->assertTrue($this->_db->getQueryTime() > 0);
    }
    
    public function testCheckConnection()
    {
        $this->_db->checkConnection();
        if ( $this->_db instanceOf MysqliManager )
            $this->assertType('Mysqli',$this->_db->getDatabase());
        else
            $this->assertTrue(is_resource($this->_db->getDatabase()));
    }
    
    public function testInsert()
    {
        $bean = new Contact();
        $bean->last_name = 'foobar' . date("YmdHis");
        $bean->id   = 'test' . date("YmdHis");
        $this->_db->insert($bean);
        
        $result = $this->_db->query("select id, last_name from contacts where id = '{$bean->id}'");
        $row = $this->_db->fetchByAssoc($result);
        $this->assertEquals($row['last_name'],$bean->last_name);
        $this->assertEquals($row['id'],$bean->id);
        
        $this->_db->query("delete from contacts where id = '{$row['id']}'");
    }
    
    public function testUpdate()
    {
        $bean = new Contact();
        $bean->last_name = 'foobar' . date("YmdHis");
        $bean->id   = 'test' . date("YmdHis");
        $this->_db->insert($bean);
        $id = $bean->id;
        
        $bean = new Contact();
        $bean->last_name = 'newfoobar' . date("YmdHis");
        $this->_db->update($bean,array('id'=>$id));
        
        $result = $this->_db->query("select id, last_name from contacts where id = '{$id}'");
        $row = $this->_db->fetchByAssoc($result);
        $this->assertEquals($row['last_name'],$bean->last_name);
        $this->assertEquals($row['id'],$id);
        
        $this->_db->query("delete from contacts where id = '{$row['id']}'");
    }
    
    public function testDelete()
    {
        $bean = new Contact();
        $bean->last_name = 'foobar' . date("YmdHis");
        $bean->id   = 'test' . date("YmdHis");
        $this->_db->insert($bean);
        $id = $bean->id;
        
        $bean = new Contact();
        $this->_db->delete($bean,array('id'=>$id));
        
        $result = $this->_db->query("select deleted from contacts where id = '{$id}'");
        $row = $this->_db->fetchByAssoc($result);
        $this->assertEquals($row['deleted'],'1');
        
        $this->_db->query("delete from contacts where id = '{$id}'");
    }
    
    public function testRetrieve()
    {
        $bean = new Contact();
        $bean->last_name = 'foobar' . date("YmdHis");
        $bean->id   = 'test' . date("YmdHis");
        $this->_db->insert($bean);
        $id = $bean->id;
        
        $bean = new Contact();
        $result = $this->_db->retrieve($bean,array('id'=>$id));
        $row = $this->_db->fetchByAssoc($result);
        $this->assertEquals($row['id'],$id);
        
        $this->_db->query("delete from contacts where id = '{$id}'");
    }
    
    public function testRetrieveView()
    {
        // TODO: Write this test
    }
    
    public function testCreateTable()
    {
        // TODO: Write this test
    }
    
    public function testCreateTableParams()
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
            array(
                array(
                    'name'   => 'idx_foo',
                    'type'   => 'index',
                    'fields' => array('foo'),
                    )
                )
            );
        $this->assertTrue(in_array($tablename,$this->_db->getTablesArray()));
        
        $this->_db->dropTableName($tablename);
    }
    
    public function testRepairTable()
    {
        // TODO: Write this test
    }
    
    public function testRepairTableParams()
    {
        // TODO: Write this test
    }
    
    public function testCompareFieldInTables()
    {
        $tablename1 = 'test1_' . date("YmdHis");
        $this->_db->createTableParams($tablename1,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );
        $tablename2 = 'test2_' . date("YmdHis");
        $this->_db->createTableParams($tablename2,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );
        
        $res = $this->_db->compareFieldInTables(
            'foo', $tablename1, $tablename2);
        
        $this->assertEquals($res['msg'],'match');
        
        $this->_db->dropTableName($tablename1);
        $this->_db->dropTableName($tablename2);
    }
    
    public function testCompareFieldInTablesNotInTable1()
    {
        $tablename1 = 'test3_' . date("YmdHis");
        $this->_db->createTableParams($tablename1,
            array(
                'foobar' => array (
                    'name' => 'foobar',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );
        $tablename2 = 'test4_' . date("YmdHis");
        $this->_db->createTableParams($tablename2,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );
        
        $res = $this->_db->compareFieldInTables(
            'foo', $tablename1, $tablename2);
        $this->assertEquals($res['msg'],'not_exists_table1');
        
        $this->_db->dropTableName($tablename1);
        $this->_db->dropTableName($tablename2);
    }
    
    public function testCompareFieldInTablesNotInTable2()
    {
        $tablename1 = 'test5_' . date("YmdHis");
        $this->_db->createTableParams($tablename1,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );
        $tablename2 = 'test6_' . date("YmdHis");
        $this->_db->createTableParams($tablename2,
            array(
                'foobar' => array (
                    'name' => 'foobar',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );
        
        $res = $this->_db->compareFieldInTables(
            'foo', $tablename1, $tablename2);
        
        $this->assertEquals($res['msg'],'not_exists_table2');
        
        $this->_db->dropTableName($tablename1);
        $this->_db->dropTableName($tablename2);
    }
    
    public function testCompareFieldInTablesFieldsDoNotMatch()
    {
        $tablename1 = 'test7_' . date("YmdHis");
        $this->_db->createTableParams($tablename1,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );
        $tablename2 = 'test8_' . date("YmdHis");
        $this->_db->createTableParams($tablename2,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'int',
                    ),
                ),
            array()
            );
        
        $res = $this->_db->compareFieldInTables(
            'foo', $tablename1, $tablename2);
        
        $this->assertEquals($res['msg'],'no_match');
        
        $this->_db->dropTableName($tablename1);
        $this->_db->dropTableName($tablename2);
    }
    
    public function testCompareIndexInTables()
    {
        //BEGIN SUGARCRM flav=ent ONLY
        if ($this->_db->dbType == 'oci8')
            $this->markTestSkipped('Skipping on Oracle; doesn\'t apply to this backend');
        //END SUGARCRM flav=ent ONLY
        $tablename1 = 'test9_' . date("YmdHis");
        $this->_db->createTableParams($tablename1,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array(
                array(
                    'name'   => 'idx_foo',
                    'type'   => 'index',
                    'fields' => array('foo'),
                    )
                )
            );
        $tablename2 = 'test10_' . date("YmdHis");
        $this->_db->createTableParams($tablename2,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array(
                array(
                    'name'   => 'idx_foo',
                    'type'   => 'index',
                    'fields' => array('foo'),
                    )
                )
            );
        
        $res = $this->_db->compareIndexInTables(
            'idx_foo', $tablename1, $tablename2);
        
        $this->assertEquals($res['msg'],'match');
        
        $this->_db->dropTableName($tablename1);
        $this->_db->dropTableName($tablename2);
    }
    
    public function testCompareIndexInTablesNotInTable1()
    {
        $tablename1 = 'test11_' . date("YmdHis");
        $this->_db->createTableParams($tablename1,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array(
                array(
                    'name'   => 'idx_foobar',
                    'type'   => 'index',
                    'fields' => array('foo'),
                    )
                )
            );
        $tablename2 = 'test12_' . date("YmdHis");
        $this->_db->createTableParams($tablename2,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array(
                array(
                    'name'   => 'idx_foo',
                    'type'   => 'index',
                    'fields' => array('foo'),
                    )
                )
            );
        
        $res = $this->_db->compareIndexInTables(
            'idx_foo', $tablename1, $tablename2);
        
        $this->assertEquals($res['msg'],'not_exists_table1');
        
        $this->_db->dropTableName($tablename1);
        $this->_db->dropTableName($tablename2);
    }
    
    public function testCompareIndexInTablesNotInTable2()
    {
        $tablename1 = 'test13_' . date("YmdHis");
        $this->_db->createTableParams($tablename1,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array(
                array(
                    'name'   => 'idx_foo',
                    'type'   => 'index',
                    'fields' => array('foo'),
                    )
                )
            );
        $tablename2 = 'test14_' . date("YmdHis");
        $this->_db->createTableParams($tablename2,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array(
                array(
                    'name'   => 'idx_foobar',
                    'type'   => 'index',
                    'fields' => array('foo'),
                    )
                )
            );
        
        $res = $this->_db->compareIndexInTables(
            'idx_foo', $tablename1, $tablename2);
        
        $this->assertEquals($res['msg'],'not_exists_table2');
        
        $this->_db->dropTableName($tablename1);
        $this->_db->dropTableName($tablename2);
    }
    
    public function testCompareIndexInTablesIndexesDoNotMatch()
    {
        //BEGIN SUGARCRM flav=ent ONLY
        if ($this->_db->dbType == 'oci8')
            $this->markTestSkipped('Skipping on Oracle; doesn\'t apply to this backend');
        //END SUGARCRM flav=ent ONLY
        $tablename1 = 'test15_' . date("YmdHis");
        $this->_db->createTableParams($tablename1,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array(
                array(
                    'name'   => 'idx_foo',
                    'type'   => 'index',
                    'fields' => array('foo'),
                    )
                )
            );
        $tablename2 = 'test16_' . date("YmdHis");
        $this->_db->createTableParams($tablename2,
            array(
                'foo' => array (
                    'name' => 'foobar',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array(
                array(
                    'name'   => 'idx_foo',
                    'type'   => 'index',
                    'fields' => array('foobar'),
                    )
                )
            );
        
        $res = $this->_db->compareIndexInTables(
            'idx_foo', $tablename1, $tablename2);
        
        $this->assertEquals($res['msg'],'no_match');
        
        $this->_db->dropTableName($tablename1);
        $this->_db->dropTableName($tablename2);
    }
    
    public function testCreateIndex()
    {
        // TODO: Write this test
    }
    
    public function testAddIndexes()
    {
        //BEGIN SUGARCRM flav=ent ONLY
        if ($this->_db->dbType == 'oci8')
            $this->markTestSkipped('Skipping on Oracle; doesn\'t apply to this backend');
        //END SUGARCRM flav=ent ONLY
        $tablename1 = 'test17_' . date("YmdHis");
        $this->_db->createTableParams($tablename1,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array(
                array(
                    'name'   => 'idx_foo',
                    'type'   => 'index',
                    'fields' => array('foo'),
                    )
                )
            );
        $tablename2 = 'test18_' . date("YmdHis");
        $this->_db->createTableParams($tablename2,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );
        
        // first test not executing the statement
        $this->_db->addIndexes(
            $tablename2,
            array(array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
                )),
            false);
        
        $res = $this->_db->compareIndexInTables(
            'idx_foo', $tablename1, $tablename2);
        
        $this->assertEquals($res['msg'],'not_exists_table2');
        
        // now, execute the statement
        $this->_db->addIndexes(
            $tablename2,
            array(array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
                ))
            );
        $res = $this->_db->compareIndexInTables(
            'idx_foo', $tablename1, $tablename2);
        
        $this->assertEquals($res['msg'],'match');
        
        $this->_db->dropTableName($tablename1);
        $this->_db->dropTableName($tablename2);
    }
    
    public function testDropIndexes()
    {
        //BEGIN SUGARCRM flav=ent ONLY
        if ($this->_db->dbType == 'oci8')
            $this->markTestSkipped('Skipping on Oracle; doesn\'t apply to this backend');
        //END SUGARCRM flav=ent ONLY
        $tablename1 = 'test19_' . date("YmdHis");
        $this->_db->createTableParams($tablename1,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array(
                array(
                    'name'   => 'idx_foo',
                    'type'   => 'index',
                    'fields' => array('foo'),
                    )
                )
            );
        $tablename2 = 'test20_' . date("YmdHis");
        $this->_db->createTableParams($tablename2,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array(
                array(
                    'name'   => 'idx_foo',
                    'type'   => 'index',
                    'fields' => array('foo'),
                    )
                )
            );
        
        $res = $this->_db->compareIndexInTables(
            'idx_foo', $tablename1, $tablename2);
        
        $this->assertEquals($res['msg'],'match');
        
        // first test not executing the statement
        $this->_db->dropIndexes(
            $tablename2,
            array(array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
                )),
            false);
        
        $res = $this->_db->compareIndexInTables(
            'idx_foo', $tablename1, $tablename2);
        
        $this->assertEquals($res['msg'],'match');
        
        // now, execute the statement
        $sql = $this->_db->dropIndexes(
            $tablename2,
            array(array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
                )),
            true
            );
        
        $res = $this->_db->compareIndexInTables(
            'idx_foo', $tablename1, $tablename2);
        
        $this->assertEquals($res['msg'],'not_exists_table2');
        
        $this->_db->dropTableName($tablename1);
        $this->_db->dropTableName($tablename2);
    }
    
    public function testModifyIndexes()
    {
        //BEGIN SUGARCRM flav=ent ONLY
        if ($this->_db->dbType == 'oci8')
            $this->markTestSkipped('Skipping on Oracle; doesn\'t apply to this backend');
        //END SUGARCRM flav=ent ONLY
        $tablename1 = 'test21_' . date("YmdHis");
        $this->_db->createTableParams($tablename1,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                'foobar' => array (
                    'name' => 'foobar',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array(
                array(
                    'name'   => 'idx_foo',
                    'type'   => 'index',
                    'fields' => array('foo'),
                    )
                )
            );
        $tablename2 = 'test22_' . date("YmdHis");
        $this->_db->createTableParams($tablename2,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                'foobar' => array (
                    'name' => 'foobar',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array(
                array(
                    'name'   => 'idx_foo',
                    'type'   => 'index',
                    'fields' => array('foobar'),
                    )
                )
            );
        
        $res = $this->_db->compareIndexInTables(
            'idx_foo', $tablename1, $tablename2);
        
        $this->assertEquals($res['msg'],'no_match');
        
        $this->_db->modifyIndexes(
            $tablename2,
            array(array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
                )),
            false);
        
        $res = $this->_db->compareIndexInTables(
            'idx_foo', $tablename1, $tablename2);
        
        $this->assertEquals($res['msg'],'no_match');
        
        $this->_db->modifyIndexes(
            $tablename2,
            array(array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
                ))
            );
        
        $res = $this->_db->compareIndexInTables(
            'idx_foo', $tablename1, $tablename2);
        
        $this->assertEquals($res['msg'],'match');
        
        $this->_db->dropTableName($tablename1);
        $this->_db->dropTableName($tablename2);
    }
    
    public function testAddColumn()
    {
        $tablename1 = 'test23_' . date("YmdHis");
        $this->_db->createTableParams($tablename1,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                'foobar' => array (
                    'name' => 'foobar',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );
        $tablename2 = 'test24_' . date("YmdHis");
        $this->_db->createTableParams($tablename2,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );
        
        $res = $this->_db->compareFieldInTables(
            'foobar', $tablename1, $tablename2);
        
        $this->assertEquals($res['msg'],'not_exists_table2');
        
        $this->_db->addColumn(
            $tablename2,
            array(
                'foobar' => array (
                    'name' => 'foobar',
                    'type' => 'varchar',
                    'len' => '255',
                    )
                )
            );
        
        $res = $this->_db->compareFieldInTables(
            'foobar', $tablename1, $tablename2);
        
        $this->assertEquals($res['msg'],'match');
        
        $this->_db->dropTableName($tablename1);
        $this->_db->dropTableName($tablename2);
    }
    
    public function testAlterColumn()
    {
        $tablename1 = 'test25_' . date("YmdHis");
        $this->_db->createTableParams($tablename1,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                'foobar' => array (
                    'name' => 'foobar',
                    'type' => 'varchar',
                    'len' => '255',
                    'required' => true,
                    ),
                ),
            array()
            );
        $tablename2 = 'test26_' . date("YmdHis");
        $this->_db->createTableParams($tablename2,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                'foobar' => array (
                    'name' => 'foobar',
                    'type' => 'int',
                    ),
                ),
            array()
            );
        
        $res = $this->_db->compareFieldInTables(
            'foobar', $tablename1, $tablename2);
        
        $this->assertEquals($res['msg'],'no_match');
        
        $this->_db->alterColumn(
            $tablename2,
            array(
                'foobar' => array (
                    'name' => 'foobar',
                    'type' => 'varchar',
                    'len' => '255',
                    'required' => true,
                    )
                )
            );
        
        $res = $this->_db->compareFieldInTables(
            'foobar', $tablename1, $tablename2);
        
        $this->assertEquals($res['msg'],'match');
        
        $this->_db->dropTableName($tablename1);
        $this->_db->dropTableName($tablename2);
    }
    
    public function testDropTable()
    {
        // TODO: Write this test
    }
    
    public function testDropTableName()
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
        $this->assertTrue(in_array($tablename,$this->_db->getTablesArray()));
        
        $this->_db->dropTableName($tablename);
        
        $this->assertFalse(in_array($tablename,$this->_db->getTablesArray()));
    }
    
    public function testDeleteColumn()
    {
        // TODO: Write this test
    }
    
    public function testDisconnectAll()
    {
        $this->_db->disconnectAll();
        $this->assertTrue($this->_db->checkError());
        $this->_db = &DBManagerFactory::getInstance();
    }
        
    public function testQuote()
    {
        $string = "'dog eat ";
        
        if ( $this->_db->dbType == 'mysql')
            $this->assertEquals($this->_db->quoteForEmail($string),"\'dog eat ");
        else
            $this->assertEquals($this->_db->quoteForEmail($string),"''dog eat ");
    }
    
    public function testQuoteForEmail()
    {
        $string = "'dog eat ";
        
        if ( $this->_db->dbType == 'mysql')
            $this->assertEquals($this->_db->quoteForEmail($string),"\'dog eat ");
        else
            $this->assertEquals($this->_db->quoteForEmail($string),"''dog eat ");
    }
    
    public function testArrayQuote()
    {
        $string = array("'dog eat ");
        $this->_db->arrayQuote($string);
        if ( $this->_db->dbType == 'mysql')
            $this->assertEquals($string,array("\'dog eat "));
        else
            $this->assertEquals($string,array("''dog eat "));
    }
    
    public function testQuery()
    {
        $beanIds = $this->_createRecords(5);
        
        $result = $this->_db->query("SELECT id From contacts where last_name = 'foobar'");
        if ( $this->_db instanceOf MysqliManager )
            $this->assertType('Mysqli_result',$result);
        else
            $this->assertTrue(is_resource($result));
        
        while ( $row = $this->_db->fetchByAssoc($result) ) 
            $this->assertTrue(in_array($row['id'],$beanIds),"Id not found '{$row['id']}'");
        
        $this->_removeRecords($beanIds);
    }
    
    public function disabledLimitQuery()
    {
        $beanIds = $this->_createRecords(5);
        $_REQUEST['module'] = 'contacts';
        $result = $this->_db->limitQuery("SELECT id From contacts where last_name = 'foobar'",1,3);
        if ( $this->_db instanceOf MysqliManager )
            $this->assertType('Mysqli_result',$result);
        else
            $this->assertTrue(is_resource($result));
        
        while ( $row = $this->_db->fetchByAssoc($result) ) {
            if ( $row['id'][0] > 3 || $row['id'][0] < 0 )
                $this->assertFalse(in_array($row['id'],$beanIds),"Found {$row['id']} in error");
            else
                $this->assertTrue(in_array($row['id'],$beanIds),"Didn't find {$row['id']}");
        }
        unset($_REQUEST['module']);
        $this->_removeRecords($beanIds);
    }
    
    public function testGetOne()
    {
        $beanIds = $this->_createRecords(1);
        
        $id = $this->_db->getOne("SELECT id From contacts where last_name = 'foobar'");
        $this->assertEquals($id,$beanIds[0]);
        
        $this->_removeRecords($beanIds);
    }
    
    public function testGetFieldsArray()
    {
        $beanIds = $this->_createRecords(1);
        
        $result = $this->_db->query("SELECT id From contacts where id = '{$beanIds[0]}'");
        $fields = $this->_db->getFieldsArray($result,true);
        
        $this->assertEquals(array("id"),$fields);
        
        $this->_removeRecords($beanIds);
    }
    
    public function testGetRowCount()
    {
        //BEGIN SUGARCRM flav=ent ONLY
        if ($this->_db->dbType == 'oci8')
            $this->markTestSkipped('Skipping on Oracle; doesn\'t apply to this backend');
        //END SUGARCRM flav=ent ONLY
        $beanIds = $this->_createRecords(1);
        
        $result = $this->_db->query("SELECT id From contacts where id = '{$beanIds[0]}'");
        
        $this->assertEquals($this->_db->getRowCount($result),1);
        
        $this->_removeRecords($beanIds);
    }
    
    public function testGetAffectedRowCount()
    {
        if ( ($this->_db instanceOf MysqliManager) )
            $this->markTestSkipped('Skipping on Mysqli; doesn\'t apply to this backend');
        //BEGIN SUGARCRM flav=ent ONLY
        if ($this->_db->dbType == 'oci8')
            $this->markTestSkipped('Skipping on Oracle; doesn\'t apply to this backend');
        //END SUGARCRM flav=ent ONLY
        
        $beanIds = $this->_createRecords(1);
        $result = $this->_db->query("DELETE From contacts where id = '{$beanIds[0]}'");
        $this->assertEquals($this->_db->getAffectedRowCount(),1);
    }
    
    public function testFetchByAssoc()
    {
        $beanIds = $this->_createRecords(1);
        
        $result = $this->_db->query("SELECT id From contacts where id = '{$beanIds[0]}'");
        
        $row = $this->_db->fetchByAssoc($result);
        
        $this->assertTrue(is_array($row));
        $this->assertEquals($row['id'],$beanIds[0]);
        
        $this->_removeRecords($beanIds);
    }
    
    public function testConnect()
    {
        // TODO: Write this test
    }
    
    public function testDisconnect()
    {
        $this->_db->disconnect();
        $this->assertTrue($this->_db->checkError());
        $this->_db = &DBManagerFactory::getInstance();
    }
    
    public function testGetTablesArray()
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
        
        $this->assertTrue($this->_db->tableExists($tablename));
        
        $this->_db->dropTableName($tablename);
    }
    
    public function testVersion()
    {
        $ver = $this->_db->version();
        
        $this->assertTrue(is_string($ver));
    }
    
    public function testTableExists()
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
        
        $this->assertTrue(in_array($tablename,$this->_db->getTablesArray()));
        
        $this->_db->dropTableName($tablename);
    }
    
    public function testCompareVarDefs()
    {
        $this->assertTrue($this->_db->compareVarDefs(
                array(
                    'foo' => array (
                        'name' => 'foo',
                        'type' => 'varchar',
                        'len' => '255',
                        ),
                    ),
                array(
                    'foo' => array (
                        'name' => 'foo',
                        'type' => 'varchar',
                        'len' => '255',
                        ),
                )
            ));
        
        $this->assertFalse($this->_db->compareVarDefs(
                array(
                    'foo' => array (
                        'name' => 'foo',
                    'type' => 'char',
                        'len' => '255',
                        ),
                    ),
                array(
                    'foo' => array (
                        'name' => 'foo',
                        'type' => 'varchar',
                        'len' => '255',
                        ),
                )
            ));
        
        $this->assertFalse($this->_db->compareVarDefs(
                array(
                    'foo' => array (
                    'name' => 'foo',
                        'type' => 'char',
                        'len' => '255',
                        ),
                    ),
                array(
                    'foo' => array (
                        'name' => 'foo',
                        'len' => '255',
                        ),
                )
            ));
        
        $this->assertFalse($this->_db->compareVarDefs(
                array(
                    'foo' => array (
                        'name' => 'foo',
                        'len' => '255',
                        ),
                    ),
                array(
                    'foo' => array (
                        'name' => 'foo',
                    'type' => 'varchar',
                        'len' => '255',
                        ),
                )
            ));
    }
    
    public function providerConvert()
    {
        $db = DBManagerFactory::getInstance();
        
        $returnArray = array(
            array(
                array('foo','nothing'),
                'foo'
                )
            );
        if ( $db instanceOf MysqlManager )
            $returnArray += array(
                array(
                    array('foo','today'),
                    'CURDATE()'
                    ),
                array(
                    array('foo','left'),
                    'LEFT(foo)'
                ),
            array(
                    array('foo','left',array('1','2','3')),
                    'LEFT(foo,1,2,3)'
                    ),
                array(
                    array('foo','date_format'),
                    'DATE_FORMAT(foo)'
                        ),
                array(
                    array('foo','date_format',array('1','2','3')),
                    'DATE_FORMAT(foo,1,2,3)'
                    ),
                array(
                    array('foo','datetime',array("'%Y-%m'")),
                    'DATE_FORMAT(foo, \'%Y-%m-%d %H:%i:%s\')'
                        ),
                array(
                    array('foo','IFNULL'),
                    'IFNULL(foo)'
                    ),
                array(
                    array('foo','IFNULL',array('1','2','3')),
                    'IFNULL(foo,1,2,3)'
                    ),
                array(
                    array('foo','CONCAT',array('1','2','3')),
                    'CONCAT(foo,1,2,3)'
                    ),
                array(
                    array('foo','text2char'),
                    'foo'
                ),
            );
        if ( $db instanceOf MssqlManager )
            $returnArray += array(
                array(
                    array('foo','today'),
                    'GETDATE()'
                    ),
                array(
                    array('foo','left'),
                    'LEFT(foo)'
                    ),
                array(
                    array('foo','left',array('1','2','3')),
                    'LEFT(foo,1,2,3)'
                    ),
                array(
                    array('foo','date_format'),
                    'CONVERT(varchar(10),foo,120)'
                    ),
                array(
                    array('foo','date_format',array('1','2','3')),
                    'CONVERT(varchar(10),foo,120)'
                    ),
                array(
                    array('foo','date_format',array("'%Y-%m'")),
                    'CONVERT(varchar(7),foo,120)'
                    ),
                array(
                    array('foo','IFNULL'),
                    'ISNULL(foo)'
                    ),
                array(
                    array('foo','IFNULL',array('1','2','3')),
                    'ISNULL(foo,1,2,3)'
                    ),
                array(
                    array('foo','CONCAT',array('1','2','3')),
                    'foo+1+2+3'
                    ),
                array(
                    array('foo','text2char'),
                    'CAST(foo AS varchar(8000))'
                    ),
                );
        if ( $db instanceOf SqlsrvManager )
            $returnArray += array(
                array(
                    array('foo','datetime'),
                    'CONVERT(varchar(20),foo,120)'
                    ),
                );
        //BEGIN SUGARCRM flav=ent ONLY 
        if ( $db instanceOf OracleManager )
            $returnArray += array(
                array(
                    array('foo','date'),
                    "to_date(foo, 'YYYY-MM-DD')"
                    ),
                array(
                    array('foo','time'),
                    "to_date(foo, 'HH24:MI:SS')"
                    ),
                array(
                    array('foo','datetime'),
                    "to_date(foo, 'YYYY-MM-DD HH24:MI:SS')"
                    ),
                array(
                    array('foo','datetime',array(),array(1,2,3)),
                    "to_date(foo, 'YYYY-MM-DD HH24:MI:SS',1,2,3)"
                    ),
                array(
                    array('foo','today'),
                    'sysdate'
                    ),
                array(
                    array('foo','left'),
                    "LTRIM(foo)"
                    ),
                array(
                    array('foo','left',array(),array(1,2,3)),
                    "LTRIM(foo,1,2,3)"
                    ),
                array(
                    array('foo','date_format'),
                    "TO_CHAR(foo)"
                    ),
                array(
                    array('foo','date_format',array(),array(1,2,3)),
                    "TO_CHAR(foo,1,2,3)"
                    ),
                array(
                    array('foo','time_format'),
                    "TO_CHAR(foo)"
                    ),
                array(
                    array('foo','time_format',array(),array(1,2,3)),
                    "TO_CHAR(foo,1,2,3)"
                    ),
                array(
                    array('foo','IFNULL'),
                    "NVL(foo)"
                    ),
                array(
                    array('foo','IFNULL',array(),array(1,2,3)),
                    "NVL(foo,1,2,3)"
                    ),
                array(
                    array('foo','CONCAT'),
                    "CONCAT(foo)"
                    ),
                array(
                    array('foo','CONCAT',array(),array(1,2,3)),
                    "CONCAT(foo,1,2,3)"
                    ),
                array(
                    array('foo','text2char'),
                    "to_char(foo)"
                    ),
                );
        //END SUGARCRM flav=ent ONLY 
        
        return $returnArray;
    }
    
    /**
      * @group bug33283
      * @dataProvider providerConvert
     */
     public function testConvert(
         array $parameters,
         $result
        )
    {
         if ( count($parameters) < 3 )
             $this->assertEquals(
                 $this->_db->convert($parameters[0],$parameters[1]),
                 $result);
         elseif ( count($parameters) < 4 )
             $this->assertEquals(
                 $this->_db->convert($parameters[0],$parameters[1],$parameters[2]),
                 $result);
        else
            $this->assertEquals(
                 $this->_db->convert($parameters[0],$parameters[1],$parameters[2],$parameters[3]),
                 $result);
     }
     
     /**
      * @group bug33283
      */
     public function testConcat()
     {
         $ret = $this->_db->concat('foo',array('col1','col2','col3'));
         
         if ( $this->_db instanceOf MysqlManager )
             $this->assertEquals($ret,
                 "CONCAT(IFNULL(foo.col1,''),' ',IFNULL(foo.col2,''),' ',IFNULL(foo.col3,''))"
                 );
         if ( $this->_db instanceOf MssqlManager )
             $this->assertEquals($ret,
                 "CONCAT(IFNULL(foo.col1,''),' ',IFNULL(foo.col2,''),' ',IFNULL(foo.col3,''))"
                 );
         if ( $this->_db instanceOf OracleManager )
             $this->assertEquals($ret,
                 "CONCAT(IFNULL(foo.col1,''),' ',IFNULL(foo.col2,''),' ',IFNULL(foo.col3,''))"
                 );
     }
     
     public function providerFromConvert()
     {
         $returnArray = array(
             array(
                 array('foo','nothing'),
                 'foo'
                 )
             );
         if ( $this->_db instanceOf MssqlManager 
                || $this->_db instanceOf OracleManager )
             $returnArray += array(
                 array(
                     array('2009-01-01 12:00:00','date'),
                     '2009-01-01'
                     ),
                 array(
                     array('2009-01-01 12:00:00','time'),
                     '12:00:00'
                     )
                 );
         
         return $returnArray;
     }
     
     /**
      * @group bug33283
      * @dataProvider providerFromConvert
      */
     public function testFromConvert(
         array $parameters,
         $result
         )
     {
         $this->assertEquals(
             $this->_db->fromConvert($parameters[0],$parameters[1]),
             $result);
    }
    
    /**
     * @group bug34892
     */
    public function testMssqlNotClearingErrorResults()
    {
        if ( get_class($this->_db) != 'MssqlManager' )
            $this->markTestSkipped('Skipping; only applies with php_mssql driver');
        
        // execute a bad query
        $this->_db->query("select dsdsdsdsdsdsdsdsdsd");
        // assert it found an error
        $this->assertTrue($this->_db->checkError());
        // now, execute a good query
        $this->_db->query("select * from config");
        // and make no error messages are asserted
        $this->assertFalse($this->_db->checkError());
    }
}
