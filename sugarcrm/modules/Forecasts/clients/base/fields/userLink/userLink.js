/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
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
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
({
    /**
     * Attach a click event to <a class="worksheetManagerLink"> field
     */
    events : { 'click a.worksheetManagerLink' : 'linkClicked' },

    /**
     * Holds the user_id for passing into userTemplate
     */
    uid: '',

    _render:function() {
        var self = this;
        if(this.name == 'name') {
            this.uid = this.model.get('user_id');
            this.popoverTitleName = this.model.get('name');

            // setting the viewName allows us to explicitly set the template to use
            this.options.viewName = 'userLink';
        }
        app.view.Field.prototype._render.call(this);
        return this;
    },

    /**
     * Handle a user link being clicked
     * @param event
     */
    linkClicked: function(event) {
        var uid = $(event.target).data('uid');
        var self = this;
        var selectedUser = {
            id: '',
            user_name:'',
            full_name: '',
            first_name: '',
            last_name: '',
            isManager: false,
            showOpps: this.model.get("show_opps")
        };

        var options = {
            dataType: 'json',
            context: selectedUser,
            success: function(data) {
                selectedUser.id = data.id;
                selectedUser.user_name = data.user_name;
                selectedUser.full_name = data.full_name;
                selectedUser.first_name = data.first_name;
                selectedUser.last_name = data.last_name;
                selectedUser.isManager = data.isManager;

                self.context.set({selectedUser : selectedUser})
            }
        };

        myURL = app.api.buildURL('Forecasts', 'user/' + uid);
        app.api.call('read', myURL, null, options);
    },

    /**
     * Hides popover icon and removes event listener
     */
    hideIcon: function(){
        // hide icons
        $('.pull-right').hide();
    }
})
