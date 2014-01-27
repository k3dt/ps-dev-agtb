<?php
 if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once 'clients/base/api/ListApi.php';
require_once 'data/BeanFactory.php';

class AccountsApi extends ListApi
{
    public function registerApiRest()
    {
        return array(
            'opportunity_stats' => array(
                'reqType' => 'GET',
                'path' => array('Accounts','?', 'opportunity_stats'),
                'pathVars' => array('module', 'record'),
                'method' => 'opportunityStats',
                'shortHelp' => 'Get opportunity statistics for current record',
                'longHelp' => '',
            ),
        );
    }

    public function opportunityStats($api, $args)
    {
        // TODO make all APIs wrapped on tries and catches
        // TODO: move this to own module (in this case accounts)

        // TODO: Fix information leakage if user cannot list or view records not
        // belonging to them. It's hard to tell if the user has access if we
        // never get the bean.

        // Check for permissions on both Accounts and opportunities.
        // Load up the bean
        $record = BeanFactory::getBean($args['module'], $args['record']);
        if (!$record->ACLAccess('view')) {
            return;
        }

        // Load up the relationship
        if (!$record->load_relationship('opportunities')) {
            // The relationship did not load, I'm guessing it doesn't exist
            return;
        }

        // Figure out what is on the other side of this relationship, check permissions
        $linkModuleName = $record->opportunities->getRelatedModuleName();
        $linkSeed = BeanFactory::newBean($linkModuleName);
        if (!$linkSeed->ACLAccess('view')) {
            return;
        }

        // BEGIN SUGARCRM flav!=ent ONLY
        // in pro versions, we need sales_stage
        $status_field = 'sales_stage';
        // END SUGARCRM flav!=ent ONLY
        // BEGIN SUGARCRM flav=ent ONLY
        // in ent versions, we need sales_status
        $status_field = 'sales_status';
        // END SUGARCRM flav=ent ONLY

        $query = new SugarQuery();
        $query->select(array($status_field, 'amount_usdollar'));
        $query->from($linkSeed);
        // making this more generic so we can use this on contacts also as soon
        // as we move it to a proper module
        $query->join('accounts', array('alias' => 'record'));
        $query->where()->equals('record.id', $record->id);
        // FIXME add the security query here!!!
        // TODO: When we can sum on the database side through SugarQuery, we can
        // use the group by statement.

        $results = $query->execute();

        // TODO this can't be done this way since we can change the status on
        // studio and add more
        $data = array(
            'won' => array('amount_usdollar' => 0, 'count' => 0),
            'lost' => array('amount_usdollar' => 0, 'count' => 0),
            'active' => array('amount_usdollar' => 0, 'count' => 0)
        );

        foreach ($results as $row) {
            $map = array(
                'Closed Lost' => 'lost',
                'Closed Won' => 'won',
            );
            if (array_key_exists($row[$status_field], $map)) {
                $status = $map[$row[$status_field]];
            } else {
                $status = 'active';
            }
            $data[$status]['amount_usdollar'] += $row['amount_usdollar'];
            $data[$status]['count']++;
        }
        return $data;
    }
}
