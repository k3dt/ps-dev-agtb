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
(function(app) {
    /**
     * Backwards compatibility (Bwc) class manages all required methods for BWC
     * modules.
     *
     * A BWC module is defined in the metadata by the `isBwcEnabled` property.
     *
     * @class Sugar.Bwc
     * @singleton
     * @alias SUGAR.App.bwc
     */
    var Bwc = {
        /**
         * Performs backward compatibility login.
         *
         * The OAuth token is passed and we do automatic in bwc mode by
         * getting a cookie with the PHPSESSIONID.
         */
        
        /**
         * Logs into sugar in BWC mode. Allows for use of current OAuth token as
         * a session id for backward compatible modules.
         * 
         * @param  {String} redirectUrl A URL to redirect to after logging in
         * @param  {Function} callback A function to call after logging in
         * @return {Void}
         */
        login: function(redirectUrl, callback) {
            var url = app.api.buildURL('oauth2', 'bwc/login');
            return app.api.call('create', url, {}, {
                success: function(data) {
                    // Set the session name into the cache so that certain bwc
                    // modules can access it as needed (studio)
                    if (data && data.name) {
                        app.cache.set("SessionName", data.name);
                    }

                    // If there was a callback, call it. This will almost always
                    // be used exlusively by studio when trying to refresh the 
                    // session after it expires.
                    if (callback) {
                        callback();
                    }

                    // If there was a redirectUrl passed, go there. This will 
                    // almost always be the case, except in studio when a login
                    // is simply updating the session id
                    if (redirectUrl) {
                        app.router.navigate('#bwc/' + redirectUrl, {trigger: true});
                    }
                }
            });
        },

        /**
         * Translates an action to a BWC action.
         *
         * If the action wasn't found to be translated, the given action is
         * returned.
         *
         * @param {String} action The action to translate to a BWC one.
         * @return {String} The BWC equivalent action.
         */
        getAction: function(action) {
            var bwcActions = {
                'create': 'EditView',
                'edit': 'EditView',
                'detail': 'DetailView'
            };

            return bwcActions[action] || action;
        },

        /**
         * Builds a backwards compatible route. For example:
         * bwc/index.php?module=MyModule&action=DetailView&record12345
         *
         * @param {String} module The name of the module.
         * @param {String} [id] The model's ID.
         * @param {String} [action] Backwards compatible action name.
         * @param {Object} [params] Extra params to be sent on the bwc link.
         * @return {String} The built route.
         */
        buildRoute: function(module, id, action, params) {

            /**
             * app.bwc.buildRoute is for internal use and we control its callers, so we're
             * assuming callers will provide the module param which is marked required!
             */
            var href = 'bwc/index.php?',
                params = _.extend({}, { module: module }, params);

            if (!action && !id || action==='DetailView' && !id) {
                params.action = 'index';
            } else {
                if (action) {
                    params.action = action;
                } else {
                    //no action but we do have id
                    params.action = 'DetailView';
                }
                if (id) {
                    params.record = id;
                }
            }
            return href + $.param(params);
        },
        /**
         * For BWC modules, we need to get URL params for creating the related record
         * @returns {Object} BWC URL parameters
         * @private
         */
        _createRelatedRecordUrlParams: function(parentModel, link) {
            var params = {
                parent_type: parentModel.module,
                parent_name: parentModel.get('name') || parentModel.get('full_name'),
                parent_id: parentModel.get("id"),
                return_module: parentModel.module,
                return_id: parentModel.get("id"),
                return_name: parentModel.get('name') || parentModel.get('full_name')
            };

            //Handle special cases
            params = this._handleRelatedRecordSpecialCases(params, parentModel, link);

            //Set relate field values as part of URL so they get pre-filled
            var fields = app.data.getRelateFields(parentModel.module, link);
            _.each(fields, function(field){
                params[field.name] = parentModel.get(field.rname);
                params[field.id_name] = parentModel.get("id");
                if(field.populate_list) {
                    // We need to populate fields from parent record into new related record
                    _.each(field.populate_list, function (target, source) {
                        source = _.isNumber(source) ? target : source;
                        if (!_.isUndefined(parentModel.get(source))) {
                            params[target] = parentModel.get(source);
                        }
                    }, this);
                }
            });
            return params;
        },
        /**
         * Handles special cases when building the related record URL.
         * @returns {Object} BWC URL parameters taking edge cases in to consideration
         * @private
         */
        _handleRelatedRecordSpecialCases: function(params, parentModel, link) {
            //Special case for Contacts->meetings. The parent should be the account rather than the contact
            if (parentModel.module == "Contacts" && parentModel.get("account_id") && (link == "meetings" || link == 'calls')) {
                params = _.extend(params, {
                    parent_type: "Accounts",
                    parent_id: parentModel.get("account_id"),
                    account_id: parentModel.get("account_id"),
                    account_name: parentModel.get("account_name"),
                    parent_name: parentModel.get("account_name"),
                    contact_id: parentModel.get("id"),
                    contact_name: parentModel.get("full_name")
                });
            }
            //SP-1600: Account information is not populated during Quote creation via Opportunity Quote Subpanel
            if (parentModel.module === 'Opportunities' && parentModel.get('account_id') && link == 'quotes') {
                //Note that the bwc view will automagically give us billing/shipping and only
                //expects us to set account_id and account_name here
                params = _.extend(params, {
                    account_id: parentModel.get("account_id"),
                    account_name: parentModel.get("account_name")
                });
            }
            return params;
        },

        /**
         * Route to Create Related record UI for a BWC module
         */
        createRelatedRecord: function(module, parentModel, link) {
            var params = this._createRelatedRecordUrlParams(parentModel, link);
            var route = app.bwc.buildRoute(module, null, "EditView", params);
            app.router.navigate("#" + route, {trigger: true}); // Set route so that we switch over to BWC mode
        },

        /**
         * Enables the ability to share a record from a BWC module.
         *
         * This will trigger the sharing action already defined in the
         * {@link BaseShareactionField#share()}.
         *
         * @param {String} module The module that we are sharing.
         * @param {String} id The record id that we are sharing.
         * @param {String} name The record name that we are sharing.
         */
        shareRecord: function(module, id, name) {
            var tempMailTo,
                shareField = app.view.createField({
                    def: {
                        type: 'shareaction'
                    },
                    module: module,
                    model: app.data.createBean(module, {
                        id: id,
                        name: name
                    }),
                    view: app.view.createView({})
                });
            if (shareField.def.href) {
                // Yes, this is a hack, but strategically placed in BWC so it will go away.
                // If you have a better solution, please fix this.
                // Note: doing window.location.href = 'mailto:'; window.close(); has timing problems.
                // Also, reworking Smarty sugar_button function code has its own set of challenges.
                tempMailTo = $('<a href="' + shareField.def.href + '"></a>').appendTo('body');
                tempMailTo.get(0).click();
                tempMailTo.remove();
            } else {
                shareField.share();
            }
        },

        /**
         * Revert bwc model attributes in order to skip warning unsaved changes.
         */
        revertAttributes: function() {
            var view = app.controller.layout.getComponent('bwc');
            if (!view) {
                return;
            }
            view.revertBwcModel();
        }
    };
    app.augment('bwc', Bwc, false);
})(SUGAR.App);
