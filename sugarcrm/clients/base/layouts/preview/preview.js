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
    events: {
        "click .closeSubdetail": "hidePreviewPanel"
    },
    initialize: function(opts) {
        app.view.Layout.prototype.initialize.call(this, opts);
        app.events.on("preview:open", this.showPreviewPanel, this);
        app.events.on("preview:close", this.hidePreviewPanel, this);
        app.events.on("preview:pagination:hide", this.hidePagination, this);
    },

    /**
     * Show the preview panel, if it is part of the active drawer
     * @param event (optional) DOM event
     */
    showPreviewPanel: function(event) {
        if(_.isUndefined(app.drawer) || app.drawer.isActive(this.$el)){
            var layout = this.$el.parents(".sidebar-content");
            layout.find(".side-pane").removeClass("active");
            layout.find(".dashboard-pane").hide();
            layout.find(".preview-pane").addClass("active");
        }
    },

    /**
     * Hide the preview panel, if it is part of the active drawer
     * @param event (optional) DOM event
     */
    hidePreviewPanel: function(event) {
        if(_.isUndefined(app.drawer) || app.drawer.isActive(this.$el)){
            var layout = this.$el.parents(".sidebar-content");
            layout.find(".side-pane").addClass("active");
            layout.find(".dashboard-pane").show();
            layout.find(".preview-pane").removeClass("active");
        }
    },

    hidePagination: function() {
        if(_.isUndefined(app.drawer) || app.drawer.isActive(this.$el)) {
            this.hideNextPrevious = true;
            this.trigger('preview:pagination:update');
        }
    }
})
