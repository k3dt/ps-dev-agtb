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

require_once 'modules/Categories/clients/base/api/TreeApi.php';

/**
 * Test for TreeApi
 */
class TreeApiTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var TreeApi
     */
    protected $treeApi;

    /**
     * @var RestService
     */
    protected $serviceMock;

    /**
     * All created bean ids.
     *
     * @var array
     */
    public static $beanIds = array();

    /**
     * Root node 
     *
     * @var CategoryMock $root 
     */
    public static $root;

    /**
     * Nested set test data 
     * @var array
     */
    public static $testData = array(
        array('lft' => '2', 'rgt' => '9', 'lvl' => '1'),
        array('lft' => '3', 'rgt' => '4', 'lvl' => '2'),
        array('lft' => '5', 'rgt' => '6', 'lvl' => '2'),
        array('lft' => '7', 'rgt' => '8', 'lvl' => '2'),
        array('lft' => '10', 'rgt' => '19', 'lvl' => '1'),
        array('lft' => '11', 'rgt' => '14', 'lvl' => '2'),
        array('lft' => '12', 'rgt' => '13', 'lvl' => '3'),
        array('lft' => '15', 'rgt' => '16', 'lvl' => '2'),
        array('lft' => '17', 'rgt' => '18', 'lvl' => '2'),
    );

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        $GLOBALS['db']->query('DELETE FROM categories WHERE id IN (\'' . implode("', '", self::$beanIds) . '\')');

        self::$beanIds = array();
    }

    public function setUp()
    {
        SugarTestHelper::setUp('current_user', array(true, true));

        $this->treeApi = new TreeApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
        $root = new Category();
        $root->name = 'SugarCategoryRoot' . mt_rand();
        self::$beanIds[] = $root->makeRoot();
        $root->rgt = (count(self::$testData) + $root->lft) * 2;
        $root->save();
        self::$root = $root;

        foreach (self::$testData as $node) {
            $bean = BeanFactory::newBean('Categories');
            $bean->name = 'SugarCategory' . mt_rand();
            $bean->lft = $node['lft'];
            $bean->rgt = $node['rgt'];
            $bean->lvl = $node['lvl'];
            $bean->root = $root->id;
            $bean->save();
            $GLOBALS['db']->commit();
            self::$beanIds[] = $bean->id;
        }
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Test tree for selected root API method.
     */
    public function testTree()
    {
        $result = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertTrue(array_key_exists('records', $result));
        $this->assertNotEmpty($result['records'][0]['children']);
        $this->assertInternalType('array', $result['records'][0]['children']);
    }

    /**
     * Test prepend node to target API method.
     */
    public function testPrepend()
    {
        $result = $this->treeApi->prepend($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'target' => self::$root->id,
            'name' => 'SugarCategory' . mt_rand(),
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertTrue(array_key_exists('id', $result));
        $this->assertTrue(array_key_exists('lvl', $result));

        $tree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $firstNode = array_shift($tree['records']);
        $this->assertEquals($firstNode['id'], $result['id']);
    }

    /**
     * Test append node to target API method.
     */
    public function testAppend()
    {
        $result = $this->treeApi->append($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'target' => self::$root->id,
            'name' => 'SugarCategory' . mt_rand(),
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertTrue(array_key_exists('id', $result));
        $this->assertTrue(array_key_exists('lvl', $result));

        $tree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $lastNode = array_pop($tree['records']);
        $this->assertEquals($lastNode['id'], $result['id']);
    }

    /**
     * Test insert node before target API method.
     */
    public function testInsertBefore()
    {
        $tree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $result = $this->treeApi->insertBefore($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'target' => $tree['records'][1]['id'],
            'name' => 'SugarCategory' . mt_rand(),
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertTrue(array_key_exists('id', $result));
        $this->assertTrue(array_key_exists('lvl', $result));

        $tree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $this->assertEquals($tree['records'][1]['id'], $result['id']);
    }

    /**
     * Test insert node before root should catch exception.
     */
    public function testInsertBeforeRoot()
    {
        $this->setExpectedException('Exception');

        $result = $this->treeApi->insertBefore($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'target' => self::$root->id,
            'name' => 'SugarCategory' . mt_rand(),
        ));
    }

    /**
     * Test insert node after target API method.
     */
    public function testInsertAfter()
    {
        $tree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $result = $this->treeApi->insertAfter($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'target' => $tree['records'][1]['id'],
            'name' => 'SugarCategory' . mt_rand(),
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertTrue(array_key_exists('id', $result));
        $this->assertTrue(array_key_exists('lvl', $result));

        $tree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $this->assertEquals($tree['records'][2]['id'], $result['id']);
    }

    /**
     * Test insert node after root should catch exception.
     */
    public function testInsertAfterRoot()
    {
        $this->setExpectedException('Exception');

        $result = $this->treeApi->insertAfter($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'target' => self::$root->id,
            'name' => 'SugarCategory' . mt_rand(),
        ));
    }

    /**
     * Test move node before target API method.
     */
    public function testMoveBefore()
    {
        $tree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $result = $this->treeApi->moveBefore($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'record' => $tree['records'][1]['id'],
            'target' => $tree['records'][0]['id'],
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertTrue(array_key_exists('id', $result));

        $tree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $this->assertEquals($tree['records'][0]['id'], $result['id']);
    }

    /**
     * Test move node after target API method.
     */
    public function testMoveAfter()
    {
        $tree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $result = $this->treeApi->moveAfter($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'record' => $tree['records'][0]['id'],
            'target' => $tree['records'][1]['id'],
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertTrue(array_key_exists('id', $result));

        $tree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $this->assertEquals($tree['records'][1]['id'], $result['id']);
    }

    /**
     * Test get node children API method.
     */
    public function testChildren()
    {
        $result = $this->treeApi->children($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'record' => self::$root->id,
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertEquals(2, count($result));

        foreach ($result['records'] as $item) {
            $itemBean = new Category;
            $itemBean->populateFromRow($item);
            $this->assertTrue($itemBean->isDescendantOf(self::$root));
        }
    }

    /**
     * Test get tree root nodes API method.
     */
    public function testRoots()
    {
        $result = $this->treeApi->roots($this->serviceMock, array(
            'module' => self::$root->module_dir,
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);

        foreach ($result['records'] as $item) {
            $this->assertEquals('1', $item['lft']);
        }
    }

    /**
     * Test get node parent API method.
     */
    public function testParent()
    {
        $tree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $result = $this->treeApi->getParent($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'record' => $tree['records'][0]['id'],
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertEquals(self::$root->id, $result['id']);
    }

    /**
     * Test get node previous sibling API method.
     */
    public function testPrev()
    {
        $tree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $result = $this->treeApi->prev($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'record' => $tree['records'][1]['id'],
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertEquals(self::$root->id, $result['root']);
        $this->assertEquals($tree['records'][0]['id'], $result['id']);
    }

    /**
     * Test get node next sibling API method.
     */
    public function testNext()
    {
        $tree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $result = $this->treeApi->next($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'record' => $tree['records'][0]['id'],
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertEquals(self::$root->id, $result['root']);
        $this->assertEquals($tree['records'][1]['id'], $result['id']);
    }

    /**
     * Test get node path API method.
     */
    public function testPath()
    {
        $tree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $testNode = array_shift($tree['records'][0]['children']['records']);

        $result = $this->treeApi->path($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'record' => $testNode['id'],
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertEquals(2, count($result));

        list($root, $parent) = $result;

        $this->assertEquals(self::$root->id, $root['id']);
        $this->assertEquals($tree['records'][0]['id'], $parent['id']);
    }

    /**
     * Test move node and set as first node API method.
     */
    public function testMoveFirst()
    {
        $tree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $expected = array($tree['records'][1]['id'], $tree['records'][0]['id']);

        $result = $this->treeApi->moveFirst($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'record' => $tree['records'][1]['id'],
            'target' => self::$root->id,
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertTrue(array_key_exists('id', $result));
        $this->assertEquals($tree['records'][1]['id'], $result['id']);

        $updatedTree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $this->assertEquals($expected, array($updatedTree['records'][0]['id'], $updatedTree['records'][1]['id']));
    }

    /**
     * Test move node and set as last node API method.
     */
    public function testMoveLast()
    {
        $tree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $expected = array($tree['records'][1]['id'], $tree['records'][0]['id']);

        $result = $this->treeApi->moveLast($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'record' => $tree['records'][0]['id'],
            'target' => self::$root->id,
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertTrue(array_key_exists('id', $result));
        $this->assertEquals($tree['records'][0]['id'], $result['id']);

        $updatedTree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $this->assertEquals($expected, array($updatedTree['records'][0]['id'], $updatedTree['records'][1]['id']));
    }
}
