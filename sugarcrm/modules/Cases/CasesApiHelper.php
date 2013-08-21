<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

require_once('data/SugarBeanApiHelper.php');

class CasesApiHelper extends SugarBeanApiHelper
{
    /**
     * This function adds the contact and account relationships for new cases submitted via portal users
     * @param SugarBean $bean
     * @param array $submittedData
     * @param array $options
     * @return array
     */
    public function populateFromApi(SugarBean $bean, array $submittedData, array $options = array())
    {
        $data = parent::populateFromApi($bean, $submittedData, $options);

        // Any process that itself needs to be Read-Only such as the DupicateCheckApi can disallow database
        // update activity by setting the 'database_updates_not_allowed' option to true.
        if (empty($options['database_updates_not_allowed']) && isset($_SESSION['type']) && $_SESSION['type'] == 'support_portal') {
            if (empty($bean->id)) {
                $bean->id = create_guid();
                $bean->new_with_id = true;
            }
            $contact = BeanFactory::getBean('Contacts', $_SESSION['contact_id']);
            $account = $contact->account_id;
            
            $bean->assigned_user_id = $contact->assigned_user_id;

            $support_portal_user = BeanFactory::getBean('Users', $_SESSION['authenticated_user_id']);

            //BEGIN SUGARCRM flav=pro ONLY
            $bean->team_id = $contact->fetched_row['team_id'];
            $bean->team_set_id = $contact->fetched_row['team_set_id'];
            //END SUGARCRM flav=pro ONLY

            $bean->account_id = $account;
            $bean->load_relationship('contacts');
            $bean->contacts->add($contact->id);
        }
        return $data;
    }
}
