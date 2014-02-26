<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
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

/*********************************************************************************
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/


//BEGIN SUGARCRM flav=pro && flav!=ent ONLY
// PRO/CORP only fields
$fields = array(
    array(
        'name' => 'name',
        'width' =>  30,
        'link' => true,
        'label' => 'LBL_LIST_OPPORTUNITY_NAME',
        'enabled' => true,
        'default' => true,
        //BEGIN SUGARCRM flav=ent ONLY
        'related_fields' => array(
            'total_revenue_line_items',
            'closed_revenue_line_items'
        )
        //END SUGARCRM flav=ent ONLY
    ),
    array(
        'name' => 'account_name',
        'width' =>  20,
        'link'    => true,
        'label' => 'LBL_LIST_ACCOUNT_NAME',
        'enabled' => true,
        'default' => true,
        'sortable' => false,
    ),
    array(
        'name' => 'sales_stage',
        'width' => 10,
        'label' => 'LBL_LIST_SALES_STAGE',
        'enabled' => true,
        'default' => true,
    ),
    array(
        'name' => 'amount',
        'type' => 'currency',
        'label' => 'LBL_LIKELY',
        'related_fields' => array(
            'amount',
            'currency_id',
            'base_rate',
        ),
        'currency_field' => 'currency_id',
        'base_rate_field' => 'base_rate',
        'width' => 10,
        'enabled' => true,
        'default' => true,
    ),
    array(
        'name' => 'opportunity_type',
        'width' => 15,
        'label' => 'LBL_TYPE',
        'enabled' => true,
        'default' => true,
    ),
    array(
        'name' => 'lead_source',
        'width' => 15,
        'label' => 'LBL_LEAD_SOURCE',
        'enabled' => true,
        'default' => true,
    ),
    array(
        'name' => 'next_step',
        'width' => 10,
        'label' => 'LBL_NEXT_STEP',
        'enabled' => true,
        'default' => true,
    ),
    array(
        'name' => 'probability',
        'width' => 10,
        'label' => 'LBL_PROBABILITY',
        'enabled' => true,
        'default' => true,
    ),
    array(
        'name' => 'date_closed',
        'width' => 10,
        'label' => 'LBL_DATE_CLOSED',
        'enabled' => true,
        'default' => true,
    ),
    array(
        'name' => 'created_by_name',
        'width' => 10,
        'label' => 'LBL_CREATED',
        'sortable' => false,
        'enabled' => true,
        'default' => true,
        'readonly' => true
    ),
    array(
        'name' => 'team_name',
        'type' => 'teamset',
        'width' => 5,
        'label' => 'LBL_LIST_TEAM',
        'enabled' => true,
        'default' => false,
        'sortable' => false,
    ),
    array (
        'name' => 'assigned_user_name',
        'width' => 5,
        'label' => 'LBL_LIST_ASSIGNED_USER',
        'id' => 'ASSIGNED_USER_ID',
        'enabled' => true,
        'default' => true,
        'sortable' => false,
    ),
    array(
        'name' => 'modified_by_name',
        'width' => 5,
        'label' => 'LBL_MODIFIED',
        'sortable' => false,
        'enabled' => true,
        'default' => true,
        'readonly' => true,
    ),
    array(
        'name' => 'date_entered',
        'width' => 10,
        'label' => 'LBL_DATE_ENTERED',
        'enabled' => true,
        'default' => true,
        'readonly' => true,
    ),
);
//END SUGARCRM flav=pro && flav!=ent ONLY

//BEGIN SUGARCRM flav=ent ONLY
// ENT/ULT only fields
$fields = array(
    array(
        'name' => 'name',
        'width' =>  30,
        'link' => true,
        'label' => 'LBL_LIST_OPPORTUNITY_NAME',
        'enabled' => true,
        'default' => true,
        //BEGIN SUGARCRM flav=ent ONLY
        'related_fields' => array(
            'total_revenue_line_items',
            'closed_revenue_line_items'
        )
        //END SUGARCRM flav=ent ONLY
    ),
    array(
        'name' => 'account_name',
        'width' =>  20,
        'link'    => true,
        'label' => 'LBL_LIST_ACCOUNT_NAME',
        'enabled' => true,
        'default' => true,
        'sortable' => false,
    ),
    array(
        'name' => 'sales_status',
        'enabled' => true,
        'default' => true,
        'readonly' => true,
        'css_class' => 'disabled',
    ),
    array(
        'name' => 'amount',
        'type' => 'currency',
        'label' => 'LBL_LIKELY',
        'related_fields' => array(
            'amount',
            'currency_id',
            'base_rate',
        ),
        'readonly' => true,
        'currency_field' => 'currency_id',
        'base_rate_field' => 'base_rate',
        'width' => 10,
        'enabled' => true,
        'default' => true,
    ),
    array(
        'name' => 'opportunity_type',
        'width' => 15,
        'label' => 'LBL_TYPE',
        'enabled' => true,
        'default' => true,
    ),
    array(
        'name' => 'lead_source',
        'width' => 15,
        'label' => 'LBL_LEAD_SOURCE',
        'enabled' => true,
        'default' => true,
    ),
    array(
        'name' => 'next_step',
        'width' => 10,
        'label' => 'LBL_NEXT_STEP',
        'enabled' => true,
        'default' => true,
    ),
    array (
        'name' => 'date_closed',
        'width' => 10,
        'label' => 'LBL_DATE_CLOSED',
        'enabled' => true,
        'default' => true,
        'readonly' => true
    ),
    array(
        'name' => 'created_by_name',
        'width' => 10,
        'label' => 'LBL_CREATED',
        'sortable' => false,
        'enabled' => true,
        'default' => true,
        'readonly' => true,
    ),
    array(
        'name' => 'team_name',
        'type' => 'teamset',
        'width' => 5,
        'label' => 'LBL_LIST_TEAM',
        'enabled' => true,
        'default' => false,
        'sortable' => false,
    ),
    array(
        'name' => 'assigned_user_name',
        'width' => 5,
        'label' => 'LBL_LIST_ASSIGNED_USER',
        'id' => 'ASSIGNED_USER_ID',
        'enabled' => true,
        'default' => true,
        'sortable' => false,
    ),
    array(
        'name' => 'modified_by_name',
        'width' => 5,
        'label' => 'LBL_MODIFIED',
        'sortable' => false,
        'enabled' => true,
        'default' => true,
        'readonly' => true,
    ),
    array(
        'name' => 'date_entered',
        'width' => 10,
        'label' => 'LBL_DATE_ENTERED',
        'enabled' => true,
        'default' => true,
        'readonly' => true,
    ),
);
//END SUGARCRM flav=ent ONLY


$viewdefs['Opportunities']['base']['view']['list'] = array(
    'panels' => array(
        array(
            'name' => 'panel_header',
            'label' => 'LBL_PANEL_1',
            'fields' => $fields,
        ),
    ),
);
