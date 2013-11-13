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
     * Link action used in Subpanels.  It needs to be sticky so that we keep things lined up nicely.
     *
     * @class View.Fields.LinkActionField
     * @alias SUGAR.App.view.fields.LinkActionField
     * @extends View.Fields.StickyRowactionField
     */
    extendsFrom: 'StickyRowactionField',
    events: {
        'click a[name=select_button]': 'openSelectDrawer'
    },
    /**
     * Event handler for the select button that opens a link selection dialog in a drawer for linking
     * an existing record
     */
    openSelectDrawer: function() {
        if(this.isDisabled()){
            return;
        }
        var parentModel = this.context.get("parentModel"),
            linkModule = this.context.get("module"),
            link = this.context.get("link"),
            self = this;

        app.drawer.open({
            layout: 'link-selection',
            context: {
                module: linkModule
            }
        }, function(model) {
            if(!model) {
                return;
            }
            var relatedModel = app.data.createRelatedBean(parentModel, model.id, link),
                options = {
                    //Show alerts for this request
                    showAlerts: true,
                    relate: true,
                    success: function(model) {
                        //We've just linked a related, however, the list of records from
                        //loadData will come back in DESC (reverse chronological order with
                        //our newly linked on top). Hence, we reset pagination here.
                        self.context.get("collection").resetPagination();
                        self.context.resetLoadFlag();
                        self.context.set('skipFetch', false);
                        self.context.loadData();
                    },
                    error: function(error) {
                        app.alert.show('server-error', {
                            level: 'error',
                            messages: 'ERR_GENERIC_SERVER_ERROR',
                            autoClose: false
                        });
                    }
                };
            relatedModel.save(null, options);
        });
    },
    /**
     * A side effect of linking an existing record is that in the process, we could be deleting an existing
     * required relationship.
     * So here we prevent user from doing this by disabling the action.
     *
     * Returns false if relationship is required otherwise calls parent for additional ACL checks
     * @return {Boolean} true if allow access, false otherwise
     * @override
     */
    isDisabled: function() {
        if (app.view.invokeParent(this, {type: 'field', name: 'sticky-rowaction', method: 'isDisabled'})) {
            return true;
        }
        var link = this.context.get("link");
        var parentModule = this.context.get("parentModule");
        var required = app.utils.isRequiredLink(parentModule, link);
        return required;
    }
})
