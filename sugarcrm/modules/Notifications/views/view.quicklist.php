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

class ViewQuicklist extends SugarView{
	function display()
	{
		global $current_user;
		
	    $query_fields = array('is_read' => 0,'assigned_user_id' => $current_user->id);
	    $n = BeanFactory::getBean('Notifications');
	    $where = "is_read = '0'";
	    //$data = $n->get_list('date_entered',$where);
	   $n1 = BeanFactory::getBean('Notifications');
	   $n1->name = 'Roger';
	   $data['list'][] = $n1;
		echo $this->_formatNotificationsForQuickDisplay($data['list'], "modules/Notifications/tpls/quickView.tpl");
	}
	function _formatNotificationsForQuickDisplay($notifications, $tplFile)
    {
        global $app_strings;
        $this->ss->assign('APP', $app_strings);
        $this->ss->assign('data', $notifications);
        return $this->ss->display($tplFile);
    }
}