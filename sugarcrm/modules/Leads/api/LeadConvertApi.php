<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
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

require_once('include/api/ModuleApi.php');

class LeadConvertApi extends ModuleApi {

    protected $fileName = "modules/Leads/metadata/base/layouts/convert.php";
    protected $modules;
    protected $lead;
    protected $contact;
    protected $account;

    public function __construct()
    {
        $this->medataDataFile = $this->fileName;
        if (file_exists("custom/$this->fileName"))
        {
            $this->medataDataFile = "custom/$this->fileName";
        }
        $this->loadDefs();
    }

    public function registerApiRest()
    {
        //Extend with test method
        $api= array (
            'convertLead' => array(
                'reqType' => 'POST',
                'path' => array('Leads', '?', 'convert'),
                'pathVars' => array('','leadId',''),
                'method' => 'convertLead',
                'shortHelp' => 'Convert Lead to Account/Contact/Opportunity',
                'longHelp' => 'include/api/html/modules/Leads/LeadsConversionsApi.html#conversions',
            ),
        );

        return $api;
    }

    /**
     * This method handles the /Lead/:id/convert REST endpoint
     *
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
     * @param $args array The arguments array passed in from the API
     * @return Array of worksheet data entries
     * @throws SugarApiExceptionNotAuthorized
     */
    public function convertLead($api, $args)
    {
        $GLOBALS['log']->debug("vardef called: " . var_export($this->defs, true));
        $GLOBALS['log']->debug("convertLead called: " . var_export($args, true));


        //This is just till the opp form is created

        if (isset($args['Opportunities'])) {
            $args['Opportunities']['curreny_id']='-99';
            $args['Opportunities']['amount']='50000';
            $args['Opportunities']['amount_usdollar']='50000';
        }

        $leadRecord = array(
            'module' => 'Leads',
            'record' => $args['leadId']
         );

        $this->lead = $this->loadBean($api, $leadRecord);
        $this->modules = $this->loadModulesFromVardef($api, $args);

        $this->contact = $this->modules['Contacts'];

        foreach ($this->defs['Leads']['base']['layout']['convert'] as $module => $vdef) {
            if (!isset($this->modules[$module])) {
                continue;
            }

            if($module != "Contacts") {
                //$this->copyAddressFields($this->modules[$module], $this->modules['Contacts']);
                $this->setRelationshipsForModulesToContacts($module);
            }


            if($module == 'Opportunity' && empty($this->modules[$module])){
                if (isset($this->modules['Accounts'])) {
                    $this->modules[$module]->account_id = $this->modules['Accounts']->id;
                    $this->modules[$module]->account_name = $this->modules['Accounts']->name;
                }
            }

            $this->setRelationshipForModulesToLeads($module);

            $this->modules[$module]->save();
        }

        $this->contact->save();
        $this->updateLeadCampaignWithContact();



        /*
        $this->handleActivities($this->lead, $this->modules);
        // Bug 39268 - Add the lead's activities to the selected beans
        $this->handleActivities($lead, $selectedBeans);

        //link selected account to lead if it exists
        if (!empty($this->modules['Accounts']))
        {
            $this->lead->account_id = $this->modules['Accounts']->id;
        }
        */

        $this->lead->status = "Converted";
        $this->lead->converted = '1';
        $this->lead->in_workflow = true;
        $this->lead->save();

        return array(
            'status' => 200,
            'modules'=> $this->modules
        );
    }

    protected function setRelationshipsForModulesToContacts($module) {
        $contactRel = "";

        $relate = $this->defs['Leads']['base']['layout']['convert'][$module]['contactRelateField'];
        if (!empty($relate))
        {
            $fieldDef = $this->contact->field_defs[$relate];
            if (!empty($fieldDef['id_name']))
            {
                $this->contact->$fieldDef['id_name'] = $this->modules[$module]->id ;
                if ($fieldDef['id_name'] != $relate) {
                    $rname = isset($fieldDef['rname']) ? $fieldDef['rname'] : "";
                    if (!empty($rname) && isset($this->modules[$module]->$rname))
                        $this->contact->$relate = $this->modules[$module]->$rname;
                    else
                        $this->contact->$relate = $this->modules[$module]->name;
                }
            }
        }
        else {
            $contactRel = $this->findRelationship($this->contact, $this->modules[$module]);
            if (!empty($contactRel))
            {
                $this->contact->load_relationship ($contactRel) ;
                $relObject = $this->contact->$contactRel->getRelationshipObject();
                if ($relObject->relationship_type == "one-to-many" && $this->contact->$contactRel->_get_bean_position())
                {
                    $id_field = $relObject->rhs_key;
                    $this->modules[$module]->$id_field = $this->contact->id;
                } else {
                    $this->contact->$contactRel->add($this->modules[$module]);
                }

            }

        }
    }

    protected function setRelationshipForModulesToLeads($module) {
        if (!empty($this->lead))
        {
            //BEGIN SUGARCRM flav=pro ONLY
            if(empty($this->modules[$module]->team_name))
            {
                $this->modules[$module]->team_id = $this->lead->team_id;
                $this->modules[$module]->team_set_id = $this->lead->team_set_id;
            }
            //END SUGARCRM flav=pro ONLY
            if (empty($this->modules[$module]->assigned_user_id))
            {
                $this->modules[$module]->assigned_user_id = $this->lead->assigned_user_id;
            }
            $leadsRel = $this->findRelationship($this->modules[$module], $this->lead);
            if (!empty($leadsRel))
            {
                $this->modules[$module]->load_relationship($leadsRel);
                $relObject = $this->modules[$module]->$leadsRel->getRelationshipObject();
                if ($relObject->relationship_type == "one-to-many" && $this->modules[$module]->$leadsRel->_get_bean_position())
                {
                    $id_field = $relObject->rhs_key;
                    $this->lead->$id_field = $this->modules[$module]->id;
                }
                else
                {
                    $this->modules[$module]->$leadsRel->add($this->lead->id);
                }
            }
        }
    }

    protected function updateLeadCampaignWithContact(){
        //if campaign id exists then there should be an entry in campaign_log table for the newly created contact: bug 44522
        if (isset($this->lead->campaign_id) && $this->lead->campaign_id != null && isset($this->contact))
        {
            campaign_log_lead_or_contact_entry($this->lead->campaign_id, $this->lead, $this->contact, 'contact');
        }
    }

     protected function loadModule($api, $module, $data) {
        if ($data['id']) {
            $moduleDef = array (
                'module' => $module,
                'record' => $data['id']
            );

            $bean = $this->loadBean($api, $moduleDef);
        }
        else {
             $bean = BeanFactory::newBean($module);
             $this->updateBean($bean,$api, $data);
        }
        return $bean;
    }

    protected function loadModulesFromVardef($api, $data) {
        $beans = array();

        foreach ($this->defs['Leads']['base']['layout']['convert'] as $module => $vdef) {
            if (!isset($data[$module])) {
                continue;
            }

            $beans[$module] = $this->loadModule($api, $module, $data[$module]);
        }
        return $beans;
    }

    /**
     * Loads the var def for the convert lead
     * @return null
     */
    protected function loadDefs()
    {
        $viewdefs = array();
        include($this->medataDataFile);
        $this->defs = $viewdefs;
    }

    protected function findRelationship($from, $to) {
        global $dictionary;
        require_once("modules/TableDictionary.php");
        foreach ($from->field_defs as $field=>$def)
        {
            if (isset($def['type']) && $def['type'] == "link" && isset($def['relationship']))
            {
                $rel_name = $def['relationship'];
                $rel_def = "";
                if (isset($dictionary[$from->object_name]['relationships']) && isset($dictionary[$from->object_name]['relationships'][$rel_name]))
                {
                    $rel_def = $dictionary[$from->object_name]['relationships'][$rel_name];
                }
                else if (isset($dictionary[$to->object_name]['relationships']) && isset($dictionary[$to->object_name]['relationships'][$rel_name]))
                {
                    $rel_def = $dictionary[$to->object_name]['relationships'][$rel_name];
                }
                else if (isset($dictionary[$rel_name]) && isset($dictionary[$rel_name]['relationships'])
                    && isset($dictionary[$rel_name]['relationships'][$rel_name]))
                {
                    $rel_def = $dictionary[$rel_name]['relationships'][$rel_name];
                }
                if (!empty($rel_def)) {
                    if ($rel_def['lhs_module'] == $from->module_dir && $rel_def['rhs_module'] == $to->module_dir )
                    {
                        return $field;
                    }
                    else if ($rel_def['rhs_module'] == $from->module_dir && $rel_def['lhs_module'] == $to->module_dir )
                    {
                        return $field;
                    }
                }
            }
        }
        return false;
    }

    public function setMeetingsUsersRelationship($bean)
    {
        global $current_user;
        $meetingsRel = $this->findRelationshipByName($bean, $this->defs['Meetings']['ConvertLead']['relationship']);
        if (!empty($meetingsRel))
        {
            $bean->load_relationship($meetingsRel);
            $bean->$meetingsRel->add($current_user->id);
            return $bean;
        }
        else
        {
            return false;
        }
    }



    protected function copyAddressFields($bean, $contact)
    {
        //Copy over address info from the contact to any beans with address not set
        foreach($bean->field_defs as $field => $def)
        {
            if(!isset($_REQUEST[$bean->module_dir . $field]) && strpos($field, "_address_") !== false)
            {
                $set = "primary";
                if (strpos($field, "alt_") !== false || strpos($field, "shipping_") !== false)
                    $set = "alt";
                $type = "";

                if(strpos($field, "_address_street_2") !== false)
                    $type = "_address_street_2";
                else if(strpos($field, "_address_street_3") !== false)
                    $type = "_address_street_3";
                else if(strpos($field, "_address_street_4") !== false)
                    $type = "";
                else if(strpos($field, "_address_street") !== false)
                    $type = "_address_street";
                else if(strpos($field, "_address_city") !== false)
                    $type = "_address_city";
                else if(strpos($field, "_address_state") !== false)
                    $type = "_address_state";
                else if(strpos($field, "_address_postalcode") !== false)
                    $type = "_address_postalcode";
                else if(strpos($field, "_address_country") !== false)
                    $type = "_address_country";

                $var = $set.$type;
                if (isset($contact->$var))
                    $bean->$field = $contact->$var;
            }
        }
    }

    protected function migrateActivitiesToContact($module) {
        //Set the parent of activites to the new Contact
        if (isset($this->modules[$module]->field_defs['parent_id']) && isset($this->modules[$module]->field_defs['parent_type']))
        {
            $this->modules[$module]->parent_id = $this->contact->id;
            $this->modules[$module]->parent_type = "Contacts";
        }
    }

    protected function handleActivities($lead, $beans) {
        global $app_list_strings;
        global $sugar_config;
        global $app_strings;
        $parent_types = $app_list_strings['record_type_display'];

        $activities = $this->getActivitiesFromLead($lead);

        //if account is being created, we will specify the account as the parent bean
        $accountParentInfo = array();

        //determine the account id info ahead of time if it is being created as part of this conversion
        if(!empty($beans['Accounts'])){
            $accountParentInfo = array('id'=>$beans['Accounts']->id,'type'=>'Accounts');
        }

        foreach($beans as $module => $bean)
        {
            if (isset($parent_types[$module]))
            {
                if( isset($_POST['lead_conv_ac_op_sel']) && $_POST['lead_conv_ac_op_sel'] != 'None')
                {
                    foreach($activities as $activity)
                    {
                        if (!isset($sugar_config['lead_conv_activity_opt']) || $sugar_config['lead_conv_activity_opt'] == 'copy') {
                            if (isset($_POST['lead_conv_ac_op_sel'])) {
                                //if the copy to module(s) are defined, copy only to those module(s)
                                if (is_array($_POST['lead_conv_ac_op_sel'])) {
                                    foreach ($_POST['lead_conv_ac_op_sel'] as $mod) {
                                        if ($mod == $module) {
                                            $this->copyActivityAndRelateToBean($activity, $bean, $accountParentInfo);
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                        else if ($sugar_config['lead_conv_activity_opt'] == 'move') {
                            // if to move activities, should be only one module selected
                            if ($_POST['lead_conv_ac_op_sel'] == $module) {
                                $this->moveActivity($lead, $activity, $bean);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Change the parent id and parent type of an activity
     * @param $activity Activity to be modified
     * @param $bean New parent bean of the activity
     */
    protected function moveActivity($lead, $activity, $bean) {
        global $beanList;

        // delete the old relationship to the old parent (lead)
        if ($rel = $this->findRelationship($activity, $lead)) {
            $activity->load_relationship ($rel) ;

            if ($activity->parent_id && $activity->id) {
                $activity->$rel->delete($activity->id, $activity->parent_id);
            }
        }

        // add the new relationship to the new parent (contact, account, etc)
        if ($rel = $this->findRelationship($activity, $bean)) {
            $activity->load_relationship ($rel) ;

            $relObj = $activity->$rel->getRelationshipObject();
            if ( $relObj->relationship_type=='one-to-one' || $relObj->relationship_type == 'one-to-many' )
            {
                $key = $relObj->rhs_key;
                $activity->$key = $bean->id;
            }
            $activity->$rel->add($bean);
        }

        // set the new parent id and type
        $activity->parent_id = $bean->id;
        $activity->parent_type = $bean->module_dir;

        $activity->save();
    }

    /**
     * Gets the list of activities related to the lead
     * @param Lead $lead Lead to get activities from
     * @return Array of Activity SugarBeans .
     */
    protected function getActivitiesFromLead(
        $lead
    )
    {
        if (!$lead) return;

        global $beanList, $db;

        $activitesList = array("Calls", "Tasks", "Meetings", "Emails", "Notes");
        $activities = array();

        foreach($activitesList as $module)
        {
            $beanName = $beanList[$module];
            $activity = new $beanName();
            $query = "SELECT id FROM {$activity->table_name} WHERE parent_id = '{$lead->id}' AND parent_type = 'Leads'";
            $result = $db->query($query,true);
            while($row = $db->fetchByAssoc($result))
            {
                $activity = new $beanName();
                $activity->retrieve($row['id']);
                $activity->fixUpFormatting();
                $activities[] = $activity;
            }
        }

        return $activities;
    }

    protected function copyActivityAndRelateToBean(
        $activity,
        $bean,
        $parentArr = array()
    )
    {
        global $beanList;

        $newActivity = clone $activity;
        $newActivity->id = create_guid();
        $newActivity->new_with_id = true;

        //set the parent id and type if it was passed in, otherwise use blank to wipe it out
        $parentID = '';
        $parentType = '';
        if(!empty($parentArr)){
            if(!empty($parentArr['id'])){
                $parentID = $parentArr['id'];
            }

            if(!empty($parentArr['type'])){
                $parentType = $parentArr['type'];
            }

        }

        //Special case to prevent duplicated tasks from appearing under Contacts multiple times
        if ($newActivity->module_dir == "Tasks" && $bean->module_dir != "Contacts")
        {
            $newActivity->contact_id = $newActivity->contact_name = "";
        }

        if ($rel = $this->findRelationship($newActivity, $bean))
        {
            if (isset($newActivity->$rel))
            {
                // this comes form $activity, get rid of it and load our own
                $newActivity->$rel = '';
            }

            $newActivity->load_relationship ($rel) ;
            $relObj = $newActivity->$rel->getRelationshipObject();
            if ( $relObj->relationship_type=='one-to-one' || $relObj->relationship_type == 'one-to-many' )
            {
                $key = $relObj->rhs_key;
                $newActivity->$key = $bean->id;
            }

            //parent (related to field) should be blank unless it is explicitly sent in
            //it is not sent in unless the account is being created as well during lead conversion
            $newActivity->parent_id =  $parentID;
            $newActivity->parent_type = $parentType;

            $newActivity->update_date_modified = false; //bug 41747
            $newActivity->save();
            $newActivity->$rel->add($bean);
            if ($newActivity->module_dir == "Notes" && $newActivity->filename) {
                UploadFile::duplicate_file($activity->id, $newActivity->id,  $newActivity->filename);
            }
        }
    }

/*
    protected function populateModuleWithContact(){
        //Copy data from the contact to new bean
        foreach($bean->field_defs as $field => $def)
        {
            if(!isset($_REQUEST[$module . $field]) && isset($lead->$field) && $field != 'id')
            {
                $bean->$field = $lead->$field;
                if($field == 'date_entered') $bean->$field = gmdate($GLOBALS['timedate']->get_db_date_time_format()); //bug 41030
            }
        }
    }
*/

}
