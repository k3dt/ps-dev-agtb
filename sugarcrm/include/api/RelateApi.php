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

require_once('include/api/ListApi.php');

class RelateApi extends ListApi {
    public function registerApiRest() {
        return array(
            'listRelatedRecords' => array(
                'reqType' => 'GET',
                'path' => array('<module>','?','link','?'),
                'pathVars' => array('module','record','','link_name'),
                'method' => 'listRelated',
                'shortHelp' => 'List related records to this module',
                'longHelp' => 'include/api/html/module_relate_help.html',
            ),
        );
    }

    public function __construct() {
        $this->defaultLimit = $GLOBALS['sugar_config']['list_max_entries_per_subpanel'];
    }
    
    public function listRelated($api, $args) {
        // Load up the bean
        $record = BeanFactory::getBean($args['module']);
        $record->retrieve($args['record']);
        if ( ! $api->security->canAccessModule($record,'view') ) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: '.$args['module']);
        }
        // Load up the relationship
        $linkName = $args['link_name'];
        if ( ! $record->load_relationship($linkName) ) {
            // The relationship did not load, I'm guessing it doesn't exist
            throw new SugarApiExceptionNotFound('Could not find a relationship named: '.$args['relationship']);
        }
        // Figure out what is on the other side of this relationship, check permissions
        $linkModuleName = $record->$linkName->getRelatedModuleName();
        $linkSeed = BeanFactory::getBean($linkModuleName);
        if ( ! $api->security->canAccessModule($linkSeed,'view') ) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: '.$linkModuleName);
        }

        $options = $this->parseArguments($api, $args, $linkSeed);

        $linkQueryParts = $record->$linkName->getSubpanelQuery(array('return_as_array'=>true));

        $listQueryParts = $linkSeed->create_new_list_query($options['orderBy'], $options['where'], $options['userFields'], $options['params'], $options['deleted'], '', true, null, false, false);
        
        $listQueryParts['from'] .= $linkQueryParts['join'];

        if ( $api->security->hasExtraSecurity($record,'relateList',$linkSeed) ) {
            $api->security->addExtraSecurityRelateList($record,$listQueryParts);
        }

        return $this->performQuery($api, $args, $linkSeed, $listQueryParts, $options['limit'], $options['offset']);
    }
}
