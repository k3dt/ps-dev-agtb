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

require_once('include/SugarFields/SugarFieldHandler.php');
require_once('include/MetaDataManager/MetaDataManager.php');

/**
 * This class is here to provide functions to easily call in to the individual module api helpers
 */
class SugarBeanApiHelper
{
    /**
     * This is used when formatting records to do things like provide URI's for objects.
     */
    protected $api;

    function __construct(ServiceBase $api)
    {
        $this->api = $api;
    }

    /**
     * Formats the bean so it is ready to be handed back to the API's client. Certian fields will get extra processing
     * to make them easier to work with from the client end.
     *
     * @param $bean SugarBean The bean you want formatted
     * @param $fieldList array Which fields do you want formatted and returned (leave blank for all fields)
     * @param $options array Currently no options are supported
     * @return array The bean in array format, ready for passing out the API to clients.
     */
    public function formatForApi(SugarBean $bean, array $fieldList = array(), array $options = array() )
    {
        $sfh = new SugarFieldHandler();

        $data = array();
        if(!SugarACL::moduleSupportsACL($bean->module_name) || ($bean->ACLAccess('view') || $bean->ACLAccess('list'))) {
            foreach ( $bean->field_defs as $fieldName => $properties ) {
                // Prune fields before ACL check because it can be expensive (Bug58133)
                if ( !empty($fieldList) && !in_array($fieldName,$fieldList) ) {
                    // They want to skip this field
                    continue;
                }
                //BEGIN SUGARCRM flav=pro ONLY
                if ( !$bean->ACLFieldAccess($fieldName,'read') ) { 
                    // No read access to this field, skip it.
                    continue;
                }
                //END SUGARCRM flav=pro ONLY
                
                $type = !empty($properties['custom_type']) ? $properties['custom_type'] : $properties['type'];
                if ( $type == 'link' ) {
                    // There is a different API to fetch linked records, don't try to encode all of the related data.
                    continue;
                }
                $field = $sfh->getSugarField($type);
                
                if ( $field != null && isset($bean->$fieldName) ) {
                     $field->apiFormatField($data, $bean, $options, $fieldName, $properties);
                }
            }

            if (isset($bean->field_defs['email']) &&
                (empty($fieldList) || in_array('email',$fieldList))) {
                $emailsRaw = $bean->emailAddress->getAddressesByGUID($bean->id, $bean->module_name);
                $emails = array();
                $emailProps = array(
                    'email_address',
                    'opt_out',
                    'invalid_email',
                    'primary_address'
                );
                foreach($emailsRaw as $rawEmail) {
                    $formattedEmail = array();
                    foreach ($emailProps as $property) {
                        if (isset($rawEmail[$property])) {
                            $formattedEmail[$property] = $rawEmail[$property];
                        }
                    }
                    array_push($emails, $formattedEmail);
                }
                $data['email'] = $emails;
            }


            //BEGIN SUGARCRM flav=pro ONLY

            // get favorites
            // mark if its a favorite
            
            if ( empty($fieldList) || !in_array('my_favorite',$fieldList) ) {
                if(!isset($bean->my_favorite)) {
                    $bean->my_favorite = SugarFavorites::isUserFavorite($bean->module_dir, $bean->id, $GLOBALS['current_user']->id);
                }
                $data['my_favorite'] = $bean->my_favorite;
            }

            //END SUGARCRM flav=pro ONLY

            // set ACL
            // if not an admin and the hashes differ, send back bean specific acl's
            $data['_acl'] = self::getBeanAcl($bean);
        } else {
            if(isset($bean->id)) {
                $data['id'] = $bean->id;
            }
        }


        return $data;
    } 

    /**
     * Get the beans ACL's to pass back any that differ
     * @param type SugarBean $bean 
     * @return array
     */
    public function getBeanAcl(SugarBean $bean) {
        $acl = array('fields' => (object) array());
        if(SugarACL::moduleSupportsACL($bean->module_dir)) {
            $mm = MetaDataManager::getManager();
            $moduleAcl = $mm->getAclForModule($bean->module_dir, $GLOBALS['current_user']);

            $beanAcl = $mm->getAclForModule($bean->module_dir, $GLOBALS['current_user'], $bean);
            if($beanAcl['_hash'] != $moduleAcl['_hash']) {
                // diff the fields separately, they are usually empty anyway so we won't diff these often.
                $moduleAclFields = $moduleAcl['fields'];
                $beanAclFields = $beanAcl['fields'];
                // dont' need the fields here will append at the end
                unset($moduleAcl['fields']);
                unset($beanAcl['fields']);

                // don't need the hashes anymore
                unset($moduleAcl['_hash']);
                unset($beanAcl['_hash']);

                $acl = array_diff_assoc($beanAcl, $moduleAcl);
                $fieldAcls = array();

                /**
                 * Fields are different than module level acces
                 * if fields is empty that means all access is granted
                 * beanAclFields is empty and moduleAclFields is empty -> all access -> return empty
                 * beanAclFields is empty and moduleAclFields is !empty -> all access -> return yes's
                 * beanAclFields is !empty and moduleAclFields is empty -> beanAclFields access restrictions -> return beanAclFields
                 * beanAclFields is !empty and moduleAclFields is !empty -> return all access = "Yes" from moduleAcl and unset any in beanAcl that is in ModuleAcl [don't dupe data]
                 */

                if(!empty($beanAclFields) && empty($moduleAclFields)) {
                    $fieldAcls = $beanAclFields;
                }
                elseif(!empty($beanAclFields) && !empty($moduleAclFields)) {
                    // we need the ones that are moduleAclFields but not in beanAclFields
                    foreach($moduleAclFields AS $field => $aclActions) {
                        foreach($aclActions AS $action => $access) {
                            if(!isset($beanAclFields[$field][$action])) {
                                $beanAclFields[$field][$action] = "yes";
                            }
                            // if the bean action is set and it matches the access from module, we do not need to send it down
                            if(isset($beanAclFields[$field][$action]) && $beanAclFields[$field][$action] == $access) {
                                unset($beanAclFields[$field][$action]);
                            }
                        }
                    }

                    // cleanup BeanAclFields, we don't want to pass a field that doens't have actions
                    foreach($beanAclFields AS $field => $actions) {
                        if(empty($actions)) {
                            unset($beanAclFields[$field]);
                        }
                    }

                    $fieldAcls = $beanAclFields;   
                }
                elseif(empty($beanAclFields) && !empty($moduleAclFields)) {
                    // it is different because we now have access...
                    foreach($moduleAclFields AS $field => $aclActions) {
                        foreach($aclActions AS $action => $access) {
                            $fieldAcls[$field][$action] = "yes";
                        }
                    }
                }

                $acl['fields'] = (object)$fieldAcls;
            }

        }
        return $acl;        
    }

    /**
     * This function 
     *
     * @param $bean SugarBean The bean you want populated from the $submittedData array, this function will modify this
     *                        record
     * @param $submittedData array The data that was passed in from the client to update/create this record
     * @param $options array Options to pass in to the populateFromApi function, look at SugarBeanApiHelper:populateFromApi
     *                       for more information
     * @return array An array of validation errors, or true if the submitted data appeared to be correct
     */
    public function populateFromApi(SugarBean $bean, array $submittedData, array $options = array() )
    {
        $sfh = new SugarFieldHandler();

        foreach ( $bean->field_defs as $fieldName => $properties ) {
            if ( !isset($submittedData[$fieldName]) ) {
                // They aren't trying to modify this field
                continue;
            }

            //BEGIN SUGARCRM flav=pro ONLY
            if ( !$bean->ACLFieldAccess($fieldName,'save') ) { 
                // No write access to this field, but they tried to edit it
                throw new SugarApiExceptionNotAuthorized('Not allowed to edit field '.$fieldName.' in module: '.$submittedData['module']);
            }
            //END SUGARCRM flav=pro ONLY
            
            $type = !empty($properties['custom_type']) ? $properties['custom_type'] : $properties['type'];
            $field = $sfh->getSugarField($type);
            
            if ( $field != null ) {
                $field->apiSave($bean, $submittedData, $fieldName, $properties);
            }
        }

        return true;
    }
}
