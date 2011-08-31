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

require_once("data/Relationships/SugarRelationship.php");

/**
 * Represents a many to many relationship that is table based.
 */
class M2MRelationship extends SugarRelationship 
{
    var $type = "many-to-many";

    public function __construct($def)
    {
        global $dictionary;

        $this->def = $def;
        $this->name = $def['name'];

        $lhsModule = $def['lhs_module'];
        $this->lhsLinkDef = $this->getLinkedDefForModuleByRelationship($lhsModule);
        $this->lhsLink = $this->lhsLinkDef['name'];

        $rhsModule = $def['rhs_module'];
        $this->rhsLinkDef = $this->getLinkedDefForModuleByRelationship($rhsModule);
        $this->rhsLink = $this->rhsLinkDef['name'];

        $this->self_referencing = $lhsModule == $rhsModule;
    }

    /**
     * Find the link entry for a particular relationship and module.
     *
     * @param $module
     * @return array|bool
     */
    public function getLinkedDefForModuleByRelationship($module)
    {
        $results = VardefManager::getLinkFieldForRelationship( $module, BeanFactory::getBeanName($module), $this->name);
        //Only a single link was found
        if( isset($results['name']) )
        {
            return $results;
        }
        //Multiple links with same relationship name
        else if( is_array($results) )
        {
            $GLOBALS['log']->error("Warning: Multiple links found for relationship {$this->name} within module {$module}");
            return $this->getMostAppropriateLinkedDefinition($results);
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Find the most 'appropriate' link entry for a relationship/module in which there are multiple link entries with the
     * same relationship name.
     *
     * @param $links
     * @return bool
     */
    protected function getMostAppropriateLinkedDefinition($links)
    {
        foreach($links as $link)
        {
            if( isset($link['name']) && $link['name'] == $this->name )
            {
                return $link;
            }
        }
        //Unable to find an appropriate link, return nothing rather an invalid link.
        $GLOBALS['log']->error("Unable to determine best appropriate link for relationship {$this->name}");
        return FALSE;
    }
    /**
     * @param  $lhs SugarBean left side bean to add to the relationship.
     * @param  $rhs SugarBean right side bean to add to the relationship.
     * @param  $additionalFields key=>value pairs of fields to save on the relationship
     * @return boolean true if successful
     */
    public function add($lhs, $rhs, $additionalFields = array())
    {
        $lhsLinkName = $this->lhsLink;
        $rhsLinkName = $this->rhsLink;

        if (empty($lhs->$lhsLinkName) && !$lhs->load_relationship($lhsLinkName))
        {
            $lhsClass = get_class($lhs);
            $GLOBALS['log']->fatal("could not load LHS $lhsLinkName in $lhsClass");
            return false;
        }
        if (empty($rhs->$rhsLinkName) && !$rhs->load_relationship($rhsLinkName))
        {
            $rhsClass = get_class($rhs);
            $GLOBALS['log']->fatal("could not load RHS $rhsLinkName in $rhsClass");
            return false;
        }

        //Many to many has no additional logic, so just add a new row to the table and notify the beans.
        $dataToInsert = $this->getRowToInsert($lhs, $rhs);
        $dataToInsert = array_merge($dataToInsert, $additionalFields);

        $this->addRow($dataToInsert);

        if (empty($_SESSION['disable_workflow']) || $_SESSION['disable_workflow'] != "Yes")
        {
            $lhs->$lhsLinkName->addBean($rhs);
            $rhs->$rhsLinkName->addBean($lhs);

            $this->callAfterAdd($lhs, $rhs, $lhsLinkName);
            $this->callAfterAdd($rhs, $lhs, $rhsLinkName);
        }
    }

    protected function getRowToInsert($lhs, $rhs)
    {
        $row = array(
            "id" => create_guid(),
            $this->def['join_key_lhs'] => $lhs->id,
            $this->def['join_key_rhs'] => $rhs->id,
            'date_modified' => TimeDate::getInstance()->getNow()->asDb(),
            'deleted' => 0,
        );


        if (!empty($this->def['relationship_role_column']) && !empty($this->def['relationship_role_column_value']) && !$this->ignore_role_filter )
        {
            $row[$this->relationship_role_column] = $this->relationship_role_column_value;
        }

        if (!empty($this->def['fields']))
        {
            foreach($this->def['fields'] as $fieldDef)
            {
                if (!empty($fieldDef['name']) && !isset($row[$fieldDef['name']]) && !empty($fieldDef['default']))
                {
                    $row[$fieldDef['name']] = $fieldDef['default'];
                }
            }
        }

        return $row;
    }


    public function remove($lhs, $rhs)
    {
        $lhsLinkName = $this->lhsLink;
        $rhsLinkName = $this->rhsLink;

        if (!($lhs instanceof SugarBean)) {
            $GLOBALS['log']->fatal("LHS is not a SugarBean object");
            return false;
        }
        if (!($rhs instanceof SugarBean)) {
            $GLOBALS['log']->fatal("RHS is not a SugarBean object");
            return false;
        }
        if (empty($lhs->$lhsLinkName) && !$lhs->load_relationship($lhsLinkName))
        {
            $GLOBALS['log']->fatal("could not load LHS $lhsLinkName");
            return false;
        }
        if (empty($rhs->$rhsLinkName) && !$rhs->load_relationship($rhsLinkName))
        {
            $GLOBALS['log']->fatal("could not load RHS $rhsLinkName");
            return false;
        }

        $dataToRemove = array(
            $this->def['join_key_lhs'] => $lhs->id,
            $this->def['join_key_rhs'] => $rhs->id
        );

        $this->removeRow($dataToRemove);

        if (empty($_SESSION['disable_workflow']) || $_SESSION['disable_workflow'] != "Yes")
        {
            $lhs->$lhsLinkName->load();
            $rhs->$rhsLinkName->load();

            $this->callAfterDelete($lhs, $rhs, $lhsLinkName);
            $this->callAfterDelete($rhs, $lhs, $rhsLinkName);
        }
    }

    /**
     * @param  $link Link2 loads the relationship for this link.
     * @return void
     */
    public function load($link)
    {
        $db = DBManagerFactory::getInstance();
        $query = $this->getQuery($link);
        $result = $db->query($query);
        $beans = Array();
        $rows = Array();
        $relatedModule = $link->getSide() == REL_LHS ? $this->def['rhs_module'] : $this->def['lhs_module'];
        $idField = $link->getSide() == REL_LHS ? $this->def['join_key_rhs'] : $this->def['join_key_lhs'];
        while ($row = $db->fetchByAssoc($result))
        {
            $id = $row[$idField];
            $rows[$id] = $row;
        }
        return array("rows" => $rows);
    }

    public function getQuery($link, $params = array())
    {
        if ($link->getSide() == REL_LHS) {
            $knownKey = $this->def['join_key_lhs'];
            $targetKey = $this->def['join_key_rhs'];
        }
        else
        {
            $knownKey = $this->def['join_key_rhs'];
            $targetKey = $this->def['join_key_lhs'];
        }
        $rel_table = $this->getRelationshipTable();

        if (!$this->self_referencing)
        {
            $where = "$rel_table.$knownKey = '{$link->getFocus()->id}'";
        }
        else
        {
            $where = "($rel_table.{$this->def['join_key_rhs']} = '{$link->getFocus()->id}' OR $rel_table.{$this->def['join_key_lhs']} = '{$link->getFocus()->id}')";
        }

        if (empty($params['return_as_array'])) {
            return "SELECT $targetKey FROM $rel_table WHERE $where AND deleted=0";
        }
        else
        {
            return array(
                'select' => "SELECT $targetKey id",
                'from' => "FROM $rel_table",
                'where' => "WHERE $where AND $rel_table.deleted=0",
            );
        }
    }

    public function getJoin($link, $params = array(), $return_array = false)
    {
        $linkIsLHS = $link->getSide() == REL_LHS;
        if ($linkIsLHS) {
            $startingTable = (empty($params['left_join_table_alias']) ? $link->getFocus()->table_name : $params['left_join_table_alias']);
        } else {
            $startingTable = (empty($params['right_join_table_alias']) ? $link->getFocus()->table_name : $params['right_join_table_alias']);
        }

        $startingKey = $linkIsLHS ? $this->def['lhs_key'] : $this->def['rhs_key'];
        $startingJoinKey = $linkIsLHS ? $this->def['join_key_lhs'] : $this->def['join_key_rhs'];
        $joinTable = $this->getRelationshipTable();
        $joinTableWithAlias = $joinTable;
        $joinKey = $linkIsLHS ? $this->def['join_key_rhs'] : $this->def['join_key_lhs'];
        $targetTable = $linkIsLHS ? $this->def['rhs_table'] : $this->def['lhs_table'];
        $targetTableWithAlias = $targetTable;
        $targetKey = $linkIsLHS ? $this->def['rhs_key'] : $this->def['lhs_key'];
        $join_type= isset($params['join_type']) ? $params['join_type'] : ' INNER JOIN ';

        $join = '';

        //Set up any table aliases required
        if (!empty($params['join_table_link_alias']))
        {
            $joinTableWithAlias = $joinTable . " ". $params['join_table_link_alias'];
            $joinTable = $params['join_table_link_alias'];
        }
        if ( ! empty($params['join_table_alias']))
        {
            $targetTableWithAlias = $targetTable . " ". $params['join_table_alias'];
            $targetTable = $params['join_table_alias'];
        }

        if (!$this->self_referencing)
        {
            $join1 = "$startingTable.$startingKey=$joinTable.$startingJoinKey";
            $join2 = "$targetTable.$targetKey=$joinTable.$joinKey";
            $where = "";
        }
        else
        {
            $join1 = "($startingTable.$startingKey=$joinTable.{$this->def['join_key_rhs']} OR $startingTable.$startingKey=$joinTable.{$this->def['join_key_rhs']})";
            $join2 = "($targetTable.$targetKey=$joinTable.{$this->def['join_key_rhs']} OR $targetTable.$targetKey=$joinTable.{$this->def['join_key_rhs']})";
            $where = "(($startingTable.$startingKey=$joinTable.{$this->def['join_key_rhs']} AND $joinTable.{$this->def['join_key_lhs']}='{$link->getFocus()->$targetKey}') OR "
                   . "($startingTable.$startingKey=$joinTable.{$this->def['join_key_lhs']} AND $joinTable.{$this->def['join_key_rhs']}='{$link->getFocus()->$targetKey}'))";
        }


        //First join the relationship table
        $join .= "$join_type $joinTableWithAlias ON $join1 AND $joinTable.deleted=0\n"
        //Next add any role filters
               . $this->getRoleFilterForJoin() . "\n"
        //Then finally join the related module's table
               . "$join_type $targetTableWithAlias ON $join2 AND $targetTable.deleted=0\n";

		if($return_array){
			return array(
                'join' => $join,
                'type' => $this->type,
                'rel_key' => $joinKey,
                'join_tables' => array($joinTable, $targetTable),
                'where' => $where,
                'select' => "$targetTable.id",
            );
		}
		return $join . $where;
    }

    /**
     * Similar to getQuery or Get join, except this time we are starting from the related table and
     * searching for items with id's matching the $link->focus->id
     * @param  $link
     * @param array $params
     * @param bool $return_array
     * @return void
     */
    public function getSubpanelQuery($link, $params = array(), $return_array = false)
    {
        $targetIsLHS = $link->getSide() == REL_RHS;
        $startingTable = $targetIsLHS ? $this->def['lhs_table'] : $this->def['rhs_table'];;
        $startingKey = $targetIsLHS ? $this->def['lhs_key'] : $this->def['rhs_key'];
        $startingJoinKey = $targetIsLHS ? $this->def['join_key_lhs'] : $this->def['join_key_rhs'];
        $joinTable = $this->getRelationshipTable();
        $joinTableWithAlias = $joinTable;
        $joinKey = $targetIsLHS ? $this->def['join_key_rhs'] : $this->def['join_key_lhs'];
        $targetKey = $targetIsLHS ? $this->def['rhs_key'] : $this->def['lhs_key'];
        $join_type= isset($params['join_type']) ? $params['join_type'] : ' INNER JOIN ';

        $query = '';

        //Set up any table aliases required
        if (!empty($params['join_table_link_alias']))
        {
            $joinTableWithAlias = $joinTable . " ". $params['join_table_link_alias'];
            $joinTable = $params['join_table_link_alias'];
        }

        if (!$this->self_referencing)
        {
            $where = "$startingTable.$startingKey=$joinTable.$startingJoinKey AND $joinTable.$joinKey='{$link->getFocus()->$targetKey}'";
        }
        else
        {
            $where = "(($startingTable.$startingKey=$joinTable.{$this->def['join_key_rhs']} AND $joinTable.{$this->def['join_key_lhs']}='{$link->getFocus()->$targetKey}') OR "
                   . "($startingTable.$startingKey=$joinTable.{$this->def['join_key_lhs']} AND $joinTable.{$this->def['join_key_rhs']}='{$link->getFocus()->$targetKey}'))";
        }

        //First join the relationship table
        $query .= "$join_type $joinTableWithAlias ON $where AND $joinTable.deleted=0\n"
        //Next add any role filters
               . $this->getRoleFilterForJoin() . "\n";
        
		if (!empty($params['return_as_array'])) {
            $return_array = true;
        }
        if($return_array){
			return array(
                'join' => $query,
                'type' => $this->type,
                'rel_key' => $joinKey,
                'join_tables' => array($joinTable),
                'where' => "",
                'select' => " ",
            );
		}
		return $query;

    }

    protected function getRoleFilterForJoin()
    {
        $ret = "";
        if (!empty($this->relationship_role_column) && !$this->ignore_role_filter)
        {
            $ret .= " AND ".$this->getRelationshipTable().'.'.$this->relationship_role_column;
            //role column value.
            if (empty($this->relationship_role_column_value))
            {
                $ret.=' IS NULL';
            } else {
                $ret.= "='".$this->relationship_role_column_value."'";
            }
            $ret.= "\n";
        }
        return $ret;
    }

    /**
     * @param  $lhs
     * @param  $rhs
     * @return bool
     */
    public function relationship_exists($lhs, $rhs)
    {
        $query = "SELECT * FROM {$this->getRelationshipTable()} WHERE {$this->join_key_lhs} = {$lhs->id} AND {$this->join_key_rhs} = {$rhs->id}";

        //Roles can allow for multiple links between two records with different roles
        $query .= $this->getRoleFilterForJoin() . " and deleted = 0";

        $result = DBManagerFactory::getInstance()->query($query);
        $row = $this->_db->fetchByAssoc($result);

		if ($row == null) {
			return false;
		}
		else {
			return $row['id'];
		}
    }

    /**
     * @return Array - set of fields that uniquely identify an entry in this relationship
     */
    protected function getAlternateKeyFields()
    {
        $fields = array($this->join_key_lhs, $this->join_key_rhs);

        //Roles can allow for multiple links between two records with different roles
        if (!empty($this->def['relationship_role_column']) && !$this->ignore_role_filter)
        {
            $fields[] = $this->relationship_role_column;
        }

        return $fields;
    }

    public function getRelationshipTable()
    {
        if (!empty($this->def['table']))
            return $this->def['table'];
        else if(!empty($this->def['join_table']))
            return $this->def['join_table'];

        return false;
    }

    public function getFields()
    {
        if (!empty($this->def['fields']))
            return $this->def['fields'];
        $fields = array(
            "id" => array('name' => 'id'),
            'date_modified' => array('name' => 'date_modified'),
            'modified_user_id' => array('name' => 'modified_user_id'),
            'created_by' => array('name' => 'created_by'),
            $this->def['join_key_lhs'] => array('name' => $this->def['join_key_lhs']),
            $this->def['join_key_rhs'] => array('name' => $this->def['join_key_rhs'])
        );
        if (!empty($this->def['relationship_role_column']))
        {
            $fields[$this->def['relationship_role_column']] = array("name" => $this->def['relationship_role_column']);
        }
        $fields['deleted'] = array('name' => 'deleted');

        return $fields;
    }

}