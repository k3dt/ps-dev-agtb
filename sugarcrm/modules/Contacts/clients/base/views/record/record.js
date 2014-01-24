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
//FILE SUGARCRM flav=ent ONLY
(function() {
    /**
     * ListEditable plugin is for fields that use a list-edit template instead of the standard edit
     * during inline editing on list views
     */
    app.plugins.register('ContactsPortalMetadataFilter', ['view'], {
        /**
         * Check if portal is active. If not, will remove the portal fields from the metadata
         * @param {Object} meta metadata to filter.
         */
        removePortalFieldsIfPortalNotActive: function(meta) {
            if (!_.isObject(meta)) {
                return;
            }
            //Portal specific fields to hide if portal is disabled
            var portalFields = ['portal_name', 'portal_active', 'portal_password'];
            var serverInfo = app.metadata.getServerInfo();
            if (!serverInfo.portal_active) {
                _.each(meta.panels, function(panel) {
                    panel.fields = _.reject(panel.fields, function(field) {
                        var name = _.isObject(field) ? field.name : field;
                        return _.contains(portalFields, name);
                    });
                });
            }
        }
    });


    /***
     * @class App.view.views.BaseContactsRecordView
     * @extends App.view.views.BaseRecordView
     */
    return {
        extendsFrom: "RecordView",
        /**
         * Gets the portal status from metadata to know if we render portal specific fields.
         * @override
         * @param options
         */
        initialize: function(options) {
            this.plugins = _.union(this.plugins || [], ["ContactsPortalMetadataFilter"]);
            this._super("initialize", [options]);
            this.removePortalFieldsIfPortalNotActive(this.meta);
        }
    }
})()

