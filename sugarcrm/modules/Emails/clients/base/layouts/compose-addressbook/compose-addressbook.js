/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
({
    /**
     * @class ComposeAddressbookLayout
     * @extends Layout
     */
    initialize: function(options) {
        app.view.Layout.prototype.initialize.call(this, options);
        this.collection.sync = this.sync;
        this.collection.allowed_modules = ['Accounts', 'Contacts', 'Leads', 'Prospects', 'Users'];
        this.context.on('compose:addressbook:search', this.search, this);
    },
    /**
     * Calls the custom Mail API endpoint to search for email addresses.
     *
     * @param method
     * @param model
     * @param options
     */
    sync: function(method, model, options) {
        var callbacks,
            url;

        options = options || {};

        // only fetch from the approved modules
        if (_.isEmpty(options.module_list)) {
            options.module_list = ['all'];
        } else {
            options.module_list = _.intersection(this.allowed_modules, options.module_list);
        }

        // this is a hack to make pagination work while trying to minimize the affect on existing configurations
        // there is a bug that needs to be fixed before the correct approach (config.maxQueryResult vs. options.limit)
        // can be determined
        app.config.maxQueryResult = app.config.maxQueryResult || 20;
        options.limit = options.limit || app.config.maxQueryResult;

        options = app.data.parseOptionsForSync(method, model, options);

        callbacks = app.data.getSyncCallbacks(method, model, options);
        this.trigger('data:sync:start', method, model, options);

        url = app.api.buildURL('Mail', 'recipients/find', null, options.params);
        app.api.call('read', url, null, callbacks);
    },
    /**
     * Adds the set of modules and term that should be used to search for recipients.
     *
     * @param {Array} modules
     * @param {String} term
     */
    search: function(modules, term) {
        // reset offset to 0 on a search. make sure that it resets and does not update.
        this.collection.fetch({query: term, module_list: modules, offset: 0, update: false});
    }
})
