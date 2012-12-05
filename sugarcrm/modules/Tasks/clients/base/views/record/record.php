<?php
//FILE SUGARCRM flav=pro || flav=sales ONLY
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
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
/*********************************************************************************
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

$viewdefs['Tasks']['base']['view']['record'] = array(
    'buttons' => array(
        array(
            'type' => 'button',
            'label' => 'LBL_SAVE_BUTTON_LABEL',
            'css_class' => 'hide btn-primary record-save',
        ),
        array(
            'type' => 'button',
            'label' => 'LBL_CANCEL_BUTTON_LABEL',
            'css_class' => 'hide record-cancel',
        ),
        array(
            'type' => 'button',
            'label' => 'LBL_EDIT_BUTTON_LABEL',
            'css_class' => 'record-edit',
        ),
        array(
            'type' => 'button',
            'label' => 'LBL_DELETE_BUTTON_LABEL',
            'css_class' => 'record-delete',
        ),
        array(
            'type' => 'button',
            'label' => 'LBL_CLOSE_BUTTON_TITLE',
            'events' => array(
                'click' => 'function(e){
                var self = this; 
                app.alert.show("close_task", {level: "process", title: app.lang.getAppString("LBL_PROCESSING_REQUEST")});
                this.model.set("status", "Completed", {silent:true});
                this.model.save({}, {
                    success: function() {
                        app.alert.dismiss("close_task");            
                        self.render();
                    },
                    error:function(error) {
                        app.alert.dismiss("close_task");                     
                        app.alert.show("close_task_error", {level: "error", auto_close:true, title: app.lang.getAppString("ERR_AJAX_LOAD")});                    
                        app.logger.error("Failed to close a task. " + error);
                    }                    
                });                    
            }'),                
        ),                       
        array(
            'name' => 'sidebar_toggle',
            'type' => 'sidebartoggle',
        ),
    ),
    'panels' => array(
        array(
            'name' => 'panel_header',
            'header' => true,
            'fields' => array(
                'name',
            )
        ),
        array(
            'name' => 'panel_body',
            'columns' => 2,
            'labels' => false,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => array(
                'date_start', 'priority',
                'date_due', 'status',
                'assigned_user_name', 'parent_name',
            ),
        ),
        array(
            'name' => 'panel_hidden',
            'hide' => true,
            'columns' => 2,
            'labelsOnTop' => true,
            'fields' => array(
                array('name' => 'description', 'span' => 12),
                'contact_name',
                array('label' => 'Created By', 'type' => 'fieldset', 'name' => 'created_by_date', 'fields' => array('created_by_name', 'date_entered')),
                'team_sets',
                'date_modified',
            )
        )
    ),
);