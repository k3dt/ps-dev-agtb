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
/**
 * @class View.Layouts.Base.CalDavConfigDrawerLayout
 * @alias SUGAR.App.view.layouts.BaseCalDavConfigDrawerLayout
 * @extends View.Layouts.Base.ConfigDrawerLayout
 */
({
    extendsFrom: 'ConfigDrawerLayout',

    /**
     * @inheritdoc
     *
     * read config data from RESTAPI and add to model
     *
     * @override
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        var self=this;

        var section = self.context.get('section');
        var url = app.api.buildURL('caldav', 'config'+(section ? '/'+section : ''), null, options.params);
        app.api.call('READ', url, options.attributes, {
            success: function (data) {
                self.model.set('caldav_module_options', data.modules, {silent: true});
                self.model.set('caldav_module', data.values.caldav_module, {silent: true});
                self.model.set('caldav_interval_options', data.intervals, {silent: true});
                self.model.set('caldav_interval', data.values.caldav_interval, {silent: true});
                self.render();
            }
        });


    },

    /**
     * @inheritdoc
     *
     * No module No bean, there is no data in the meta. Turning off the check in Metadata
     *
     * @override
     */
    _checkConfigMetadata: function() {
        return true;
    },

    /**
     * Checks if the User has access to the current module
     *
     * @returns {boolean}
     * @private
     */
    _checkUserAccess: function() {
        var section = this.context.get('section');

        if (section == 'user') {
            return true;
        } else {
            return (app.user.get('type') == 'admin');
        }
    }
})
