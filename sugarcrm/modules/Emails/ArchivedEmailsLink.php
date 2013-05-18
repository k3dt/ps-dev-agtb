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

/**
 * Link collects archived emails - both directly assigned and
 * related by email address.
 */
class ArchivedEmailsLink extends Link2
{
    /**
     * DB
     * @var DBManager
     */
    protected $db;

    public function __construct($linkName, $bean, $linkDef = false)
    {
        $this->focus = $bean;
        $this->name = $linkName;
        $this->db = DBManagerFactory::getInstance();
        if (empty($linkDef)) {
            $this->def = $bean->field_defs[$linkName];
        } else {
            $this->def = $linkDef;
        }
      }

    /**
     * Returns false if no relationship was found for this link
     * @return bool
     */
    public function loadedSuccesfully()
    {
        // this link always loads successfully
        return true;
    }

    public function query($params)
    {
        unset($params['return_as_array']);
        $query = $this->getQuery($params);
        $result = $this->db->query($query);
        $rows = Array();
        while ($row = $this->db->fetchByAssoc($result, false))
        {
            $rows[$row['id']] = $row;
        }
        return array("rows" => $rows);
    }

    public function getRelatedModuleName()
    {
        return 'Emails';
    }

    public function getRelatedModuleLinkName()
    {
        // this is one-side link, other side (Emails) won't have the link
        return false;
    }

    public function getType()
    {
        return "many";
    }

    public function getSide()
    {
        return REL_LHS;
    }

    public function is_self_relationship()
    {
        return false;
    }

    public function isParentRelationship()
    {
        return false;
    }

    function buildJoinSugarQuery($sugar_query, $options = array())
    {
         $join_type = isset($options['joinType']) ? $options['joinType'] : 'INNER';
         $sugar_query->joinTable(
                 'emails',
                 array('alias' => $options['myAlias'], 'joinType' => $join_type)
         );
        $sugar_query->joinRaw($this->getEmailsJoin($options));
        return $sugar_query->join[$options['myAlias']];
    }

    /**
     * @param $params array of parameters. Possible parameters include:
     * 'join_table_link_alias': alias the relationship join table in the query (for M2M relationships),
     * 'join_table_alias': alias for the final table to be joined against (usually a module main table)
     * @param bool $return_array if true the query is returned as a array broken up into
     * select, join, where, type, rel_key, and joined_tables
     * @return string/array join query for this link
     */
    public function getJoin($params = array(), $return_as_array=false)
    {
        $return_array['join'] = $this->getEmailsJoin($params);

        $join_type= !empty($params['join_type']) ? $params['join_type'] : ' INNER JOIN ';

        if(isset($params['join_table_alias'])) {
            $return_array['join'] = "$join_type emails {$params['join_table_alias']} ON {$params['join_table_alias']}.deleted=0 ".$return_array['join'];
        } else {
            $return_array['join'] = "$join_type emails ON emails.deleted=0 ".$return_array['join'];
        }

        if(!empty($return_as_array) || !empty($params['return_as_array'])) {
            return $return_array;
        } else {
            return $return_array['join'].$return_array['where'];
        }
    }

    protected function getEmailsJoin($params = array())
    {
        $bean_id = $this->db->quoted($this->focus->id);
        if(!empty($params['join_table_alias'])) {
            $table_name = $params['join_table_alias'];
        } else {
            $table_name = 'emails';
        }

        //$join_type= !empty($params['join_type']) ? $params['join_type'] : ' INNER JOIN ';

        return "INNER JOIN (".
                // directly assigned emails
            "select eb.email_id, 'direct' source FROM emails_beans eb where eb.bean_module = '{$this->focus->module_dir}'
                AND eb.bean_id = $bean_id AND eb.deleted=0 ".
            " UNION ".
        // Related by directly by email
            "select DISTINCT eear.email_id, 'relate' source  from emails_email_addr_rel eear INNER JOIN email_addr_bean_rel eabr
            ON eabr.bean_id = $bean_id AND eabr.bean_module = '{$this->focus->module_dir}' AND
            eabr.email_address_id = eear.email_address_id and eabr.deleted=0 where eear.deleted=0".
            ") email_ids ON $table_name.id=email_ids.email_id ";

    }

    /**
     * @param array $params optional parameters. Possible Values;
     * 'return_as_array': returns the query broken into
     * @return String/Array query to grab just ids for this relationship
     */
    public function getQuery($params = array())
    {
        $query_array['select'] = "SELECT DISTINCT emails.* ";
        $query_array['from'] = "FROM emails ";
        $query_array['join'] = $this->getEmailsJoin();

        $deleted = !empty($params['deleted'])?1:0;
        $query_array['where']=" WHERE emails.deleted=$deleted ";

        //Add any optional where clause
        if (!empty($params['where'])) {
            $query_array['where'] .= " AND ({$params['where']}) ";
        }

        if(!empty($params['enforce_teams'])) {
            $seed = BeanFactory::getBean($this->getRelatedModuleName());
            $seed->addVisibilityFrom($query_array['join']);
            $seed->addVisibilityWhere($query_array['where']);
        }


        if(!empty($params['return_as_array'])) {
            return $query_array;
        }

        $query = $query_array['select'].$query_array['from'].$query_array['join'].$query_array['where'];
        if(!empty($params['orderby'])) {
            $query .= "ORDER BY {$params['orderby']}";
        }
        if (!empty($params['limit']) && $params['limit'] > 0) {
            $offset = isset($params['offset']) ? $params['offset'] : 0;
            $query = $this->db->limitQuery($query, $offset, $params['limit'], false, "", false);
        }
        return $query;
    }

    /**
     * This function is similair getJoin except for M2m relationships it won't join agaist the final table.
     * Its used to retrieve the ID of the related beans only
     * @param $params array of parameters. Possible parameters include:
     * 'return_as_array': returns the query broken into
     * @param bool $return_array same as passing 'return_as_array' into parameters
     * @return string/array query to use when joining for subpanels
     */
    public function getSubpanelQuery($params = array(), $return_array = false)
    {
        $query_array['join'] = $this->getEmailsJoin($params);
        $query_array['select'] = "";
        $query_array['from'] = "";
        $query_array['join_tables'] = 'email_ids';

        if(!empty($params['return_as_array'])) {
            $return_array = true;
        }
        if($return_array) {
            return $query_array;
        }
        return $query_array['join'];
    }

    /**
     * If there are any relationship fields, we need to figure out the mapping from the relationship fields to the
     * fields in the module vardefs
     */
    public function getRelationshipFieldMapping(SugarBean $seed = null)
    {
        return array();
    }

    /**
     * use this function to create link between 2 objects
     */
    public function add($rel_keys,$additional_values=array())
    {
        // cannot add to this relationship as it is implicit
        return false;
    }

    /**
     * Marks the relationship deleted for this given record pair.
     */
    public function delete($id, $related_id='')
    {
        // cannot delete from this relationship as it is implicit
        return false;
    }
}
