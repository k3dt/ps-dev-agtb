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

include_once 'data/NestedBeanInterface.php';

class Category extends SugarBean implements NestedBeanInterface
{

    public $table_name = 'categories';
    public $object_name = 'Category';
    public $module_dir = 'Categories';
    public $new_schema = true;
    public $importable = false;
    public $root;
    public $lft;
    public $rgt;
    public $level;

    /**
     * This method creates basic SugarQuery object
     * @return SugarQuery
     */
    protected function getQuery()
    {
        $query = new SugarQuery();
        $query->from($this, array(
            'alias' => 'node',
        ));

        return $query;
    }

    /**
     * Add new node to tree.
     * @param Category $node new child node.
     * @param int $key.
     * @param int $levelUp.
     * @throws Exception
     */
    protected function addNode(Category $node, $key, $levelUp)
    {
        if (!empty($node->id)) {
            throw new Exception('The node cannot be added because it is not new.');
        }

        if ($this->deleted == 1) {
            throw new Exception('The node cannot be added because category is deleted.');
        }

        if ($node->deleted == 1) {
            throw new Exception('The node cannot be added because it is deleted.');
        }

        if (!$levelUp && $this->isRoot()) {
            throw new Exception('The node should not be root.');
        }

        $node->root = $this->root;
        $node->lft = $key;
        $node->rgt = $key + 1;
        $node->level = $this->level + $levelUp;
        $node->shiftLeftRight($key, 2);
    }

    /**
     * This method loads and return all subnodes related to $root
     * @param string $root root id
     * @return array
     */
    protected function getTreeData($root)
    {
        $db = DBManagerFactory::getInstance();
        $query = $this->getQuery();
        $query->joinRaw('INNER JOIN ' . $this->table_name . ' root ON root.id=node.root');
        $query->whereRaw('root.id = ' . $db->quoted($root) . ' AND node.lft > 1');
        $query->orderByRaw('node.lft', 'ASC');
        return $query->execute();
    }


    /**
     * This method shifting left and right indexes
     * @param int $key minimal bound of index
     * @param int $delta value of shifting relative to the current position
     */
    protected function shiftLeftRight($key, $delta)
    {
        $db = DBManagerFactory::getInstance();
        foreach (array('lft', 'rgt') AS $attribute) {
            $this->update(array(
                $attribute => $attribute . sprintf('%+d', $delta),
                    ), $attribute . ' >= :key AND (root=:root) ', array(
                ':key' => $key,
                ':root' => $this->root,
            ));
        }
    }

    /**
     * This method change position of node in a tree.
     * @param Category $target
     * @param int $key minimal bound of index
     * @param int $levelUp raise the level to which
     */
    protected function moveNode(Category $target, $key, $levelUp)
    {
        $db = DBManagerFactory::getInstance();
        $left = $this->lft;
        $right = $this->rgt;
        $levelDelta = $target->level - $this->level + $levelUp;
        $delta = $right - $left + 1;

        $this->shiftLeftRight($key, $delta);
        if ($left >= $key) {
            $left += $delta;
            $right += $delta;
        }

        $this->update(array(
            'level' => 'level' . sprintf('%+d', $levelDelta),
        ), 'lft >= :left AND rgt <= :right AND root = :root', array(
            ':left' => $left,
            ':right' => $right,
            ':root' => $this->root,
        ));

        foreach (array('lft', 'rgt') as $attribute) {
            $condition = $attribute . ' >= :left'
                . ' AND ' . $attribute . ' <= :right'
                . ' AND root = :root';

            $this->update(array(
                $attribute => $attribute . sprintf('%+d', $key - $left),
                    ), $condition, array(
                ':left' => $left,
                ':right' => $right,
                ':root' => $this->root,
            ));
        }

        $this->shiftLeftRight($right + 1, -$delta);
    }

    /**
     * Build tree and return all hierarchy for root
     * @return array descendants hierarchy
     */
    public function getTree()
    {
        $tree = array();
        $stackLength = 0;
        $stack = array();
        $subnodes = $this->getTreeData($this->root);

        foreach ($subnodes as $node) {
            $data = $node;
            $data['children'] = array();
            $stackLength = count($stack);

            while ($stackLength > 0 && $stack[$stackLength - 1]['level'] >= $data['level']) {
                array_pop($stack);
                $stackLength--;
            }

            if ($stackLength == 0) {
                $i = count($tree);
                $tree[$i] = $data;
                $stack[] = & $tree[$i];
            } else {
                $i = count($stack[$stackLength - 1]['children']);
                $stack[$stackLength - 1]['children'][$i] = $data;
                $stack[] = & $stack[$stackLength - 1]['children'][$i];
            }
        }

        return $tree;
    }

    /**
     * Gets root nodes.
     * @return array list of root nodes.
     */
    public function getRoots()
    {
        $query = $this->getQuery();
        $query->where()->equals('node.lft', '1');
        return $query->execute();
    }

    public function getСhildren()
    {
        return $this->getDescendants(1);
    }

    /**
     * Gets descendants for node.
     * @param int $depth the depth.
     * @return array list of descendants.
     */
    public function getDescendants($depth = null)
    {
        $db = DBManagerFactory::getInstance();
        $query = $this->getQuery();

        $condition = array(
            'node.lft > ' . intval($this->lft),
            'node.rgt < ' . intval($this->rgt),
            'node.root = ' . $db->quoted($this->root),
        );

        if ($depth) {
            $level = $this->level + $depth;
            $condition[] = 'node.level <= ' . $level;
        }

        $query->whereRaw(implode(' AND ', $condition));
        $query->orderByRaw('node.lft', 'ASC');
        return $query->execute();
    }

    /**
     * Gets next sibling of node.
     * @return array|null the next sibling node.
     */
    public function getNextSibling()
    {
        $db = DBManagerFactory::getInstance();
        $query = $this->getQuery();

        $condition = array(
            'node.lft = ' . ($this->rgt + 1),
            'node.root = ' . $db->quoted($this->root),
        );

        $query->whereRaw(implode(' AND ', $condition));
        $query->limit = 1;
        $result = $query->execute();
        return !empty($result) ? array_shift($result) : null;
    }

    /**
     * Gets previous sibling of node.
     * @return array|null the prev sibling node.
     */
    public function getPrevSibling()
    {
        $db = DBManagerFactory::getInstance();
        $query = $this->getQuery();

        $condition = array(
            'node.rgt = ' . ($this->lft - 1),
            'node.root = ' . $db->quoted($this->root),
        );

        $query->whereRaw(implode(' AND ', $condition));
        $query->limit = 1;
        $result = $query->execute();
        return !empty($result) ? array_shift($result) : null;
    }

    /**
     * Gets parent of node.
     * @return array the parent node.
     */
    public function getParent()
    {
        return array_shift($this->getParents(1));
    }

    /**
     * Gets parents of node.
     * @return array the parent nodes.
     */
    public function getParents($depth = null)
    {
        $db = DBManagerFactory::getInstance();
        $query = $this->getQuery();
        $query->joinRaw('INNER JOIN ' . $this->table_name . ' root ON root.id=node.root');

        $condition = array(
            'node.lft < ' . $this->lft,
            'node.rgt > ' . $this->rgt,
            'root.id = ' . $db->quoted($this->root),
        );

        $query->whereRaw(implode(' AND ', $condition));
        $query->orderByRaw('node.rgt', 'ASC');
        $query->limit = $depth;
        return $query->execute();
    }

    /**
     * Determines if node is descendant of target node.
     * @param Category $target the subject node.
     * @return boolean whether the node is descendant of target node.
     */
    public function isDescendantOf(NestedBeanInterface $target)
    {
        return $this->lft > $target->lft && $this->rgt < $target->rgt && $this->root === $target->root;
    }

    /**
     * Determines if node is root.
     * @return boolean whether the node is root.
     */
    public function isRoot()
    {
        return $this->lft == 1;
    }

    /**
     * The method makes a current bean as root node.
     * @return string id of current bean.
     * @throws Exception
     */
    public function makeRoot()
    {
        if (!empty($this->id)) {
            throw new Exception('The node cannot be makes root because it is not new.');
        }
        $this->lft = 1;
        $this->rgt = 2;
        $this->level = 0;

        if (empty($this->id)) {
            $this->new_with_id = true;
            $this->id = create_guid();
        }

        $this->root = $this->id;
        return parent::save();
    }

    /**
     * Creates and executes an UPDATE SQL statement.
     * @param array $fields the fields data (name=>value) to be updated.
     * @param mixed $conditions the conditions that will be put in the WHERE part.
     * @param array $params the parameters to be bound to the query.
     * @return boolean db query result
     */
    public function update($fields, $condition = '1', $params = array())
    {
        $db = DBManagerFactory::getInstance();
        $fieldSet = array();

        foreach ($fields as $name => $value) {
            $fieldSet[] = $name . '=' . $value;
        }

        $sql = 'UPDATE ' . $this->table_name . ''
                . ' SET ' . implode(', ', $fieldSet)
                . ' WHERE ' . strtr($condition, array_map(array($db, 'quoted'), $params));

        return $db->query($sql, true, 'Error updating table:' . $this->table_name . ':');
    }

    /**
     * This method marking as deleted current record and all descendant records
     * @inheritDoc
     */
    public function mark_deleted($id)
    {
        $this->retrieve($id);
        $hasChild = ($this->rgt - $this->lft) !== 1;
        if ($hasChild) {
            $descendants = $this->getDescendants();
            while ($record = array_shift($descendants)) {
                parent::mark_deleted($record['id']);
            }
        }

        parent::mark_deleted($id);
        $this->shiftLeftRight($this->rgt + 1, ($this->lft - $this->rgt) - 1);
    }

    /**
     * Append node as last child.
     * @param NestedBeanInterface $node.
     */
    public function append(NestedBeanInterface $node)
    {
        $this->addNode($node, $this->rgt, 1);
    }

    /**
     * Prepends node as first child.
     * @param NestedBeanInterface $node.
     */
    public function prepend(NestedBeanInterface $node)
    {
        $this->addNode($node, $this->lft + 1, 1);
    }

    /**
     * Inserts node as previous sibling of target.
     * @param NestedBeanInterface $node.
     */
    public function insertBefore(NestedBeanInterface $target)
    {
        $target->addNode($this, $target->lft, 0);
    }

    /**
     * Inserts node as next sibling of target.
     * @param NestedBeanInterface $node.
     */
    public function insertAfter(NestedBeanInterface $target)
    {
        $target->addNode($this, $target->rgt + 1, 0);
    }

    /**
     * Move node as first child of target.
     * @param NestedBeanInterface $target the target.
     */
    public function moveAsFirst(NestedBeanInterface $target)
    {
        $this->moveNode($target, $target->lft + 1, 1);
    }

    /**
     * Move node as last child of target.
     * @param NestedBeanInterface $target the target.
     */
    public function moveAsLast(NestedBeanInterface $target)
    {
        $this->moveNode($target, $target->rgt, 1);
    }

    /**
     * Move node as next sibling of target.
     * @param NestedBeanInterface $target the target.
     */
    public function moveAfter(NestedBeanInterface $target)
    {
        $this->moveNode($target, $target->rgt + 1, 0);
    }

    /**
     * Move node as previous sibling of target.
     * @param NestedBeanInterface $target the target.
     */
    public function moveBefore(NestedBeanInterface $target)
    {
        $this->moveNode($target, $target->lft, 0);
    }

}
