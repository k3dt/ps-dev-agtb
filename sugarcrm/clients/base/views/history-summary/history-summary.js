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
 * @class View.Views.Base.HistorySummaryView
 * @alias SUGAR.App.view.views.BaseHistorySummaryView
 * @extends View.Views.Base.FlexListView
 */
({
    extendsFrom: 'FlexListView',

    /**
     * Array of module names to fetch history
     */
    activityModules: [],

    /**
     * An array of default activity modules to fetch
     */
    allActivityModules: [
        'Calls',
        'Emails',
        'Meetings',
        'Notes',
        'Tasks'
    ],

    /**
     * Module name of the record we're coming from
     */
    baseModule: '',

    /**
     * Record ID of the record we're coming from
     */
    baseRecord: '',

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this.plugins = _.union(this.plugins, ['ReorderableColumns', 'ListColumnEllipsis']);

        if (options.context.parent) {
            this.baseModule = options.context.parent.get('module');
            this.baseRecord = options.context.parent.get('modelId');
        }

        this.setActivityModulesToFetch();

        var HistoryCollection = app.BeanCollection.extend({
            module: 'history',
            activityModules: this.activityModules,
            buildURL: _.bind(function(params) {
                params = params || {};

                var url = app.api.serverUrl + '/'
                    + this.baseModule + '/'
                    + this.baseRecord + '/'
                    + 'link/history';

                params.module_list = this.activityModules.join(',');
                params = $.param(params);
                if (params.length > 0) {
                    url += '?' + params;
                }
                return url;
            }, this),
            sync: function(method, model, options) {
                options = app.data.parseOptionsForSync(method, model, options);
                if (options.params.fields) {
                    delete options.params.fields;
                }
                var url = this.buildURL(options.params),
                    callbacks = app.data.getSyncCallbacks(method, model, options);

                app.api.call(method, url, options.attributes, callbacks);
            }
        });

        options.collection = new HistoryCollection();

        this._super('initialize', [options]);

        //override the flex-list template
        this.template = app.template.getView(this.meta.template);

        this.context.set({
            collection: this.collection
        });
    },

    /**
     * Sets the activityModules array which the collection sends to the endpoint
     * Override this function in child views to set a custom list of modules to fetch
     */
    setActivityModulesToFetch: function() {
        this.activityModules = this.allActivityModules;
    },

    /**
     * @inheritdoc
     * @override
     *
     * Overridden to use the collection's fetch, not the context
     */
    loadData: function(options) {
        if (this.collection.dataFetched) {
            return;
        }
        this.collection.fetch(options);
    },

    /***
     * @inheritdoc
     *
     * Sets the field properly depending on the field name
     */
    _renderField: function(field) {
        var fieldName = field.name,
            fieldModule = field.model.get('_module'),
            fieldType = field.def.type || 'default';

        // check the fieldName and set the proper values
        if (fieldName === 'name') {
            // set the model's module to be the field's model's module
            // for the name link to be the proper ID
            field.model.module = fieldModule;
        } else if (fieldName === 'module') {
            field.model.set({
                module: field.model.get('moduleNameSingular')
            });
        } else if (fieldName === 'related_contact') {
            var contact,
                contactId;
            field.model.module = 'Contacts';
            switch (fieldModule) {
                case 'Emails':
                    // Emails does not have a related Contact/ID
                    contact = '';
                    contactId = '';
                    break;

                case 'Notes':
                case 'Calls':
                case 'Meetings':
                case 'Tasks':
                    contact = field.model.get('contact_name');
                    contactId = field.model.get('contact_id');
                    break;
            }
            field.model.set({
                related_contact: contact,
                related_contact_id: contactId
            });
        } else if (fieldName === 'status' && fieldModule === 'Emails') {
            // if this is the Status field and an Emails row,
            // translate the email status
            var emailStatusDom = app.lang.getAppListStrings('dom_email_status');
            field.model.set({
                status: emailStatusDom[field.model.get('status')]
            });
        } else if (fieldType === 'preview-button') {
            // set the field module to the model's module for preview button
            field.model.module = fieldModule;
        }

        this._super("_renderField", [field]);
    },

    /**
     * @inheritdoc
     * @override
     *
     * Overriding to fetch using the collection, not the context
     */
    _setOrderBy: function(options) {
        if (this.orderByLastStateKey) {
            app.user.lastState.set(this.orderByLastStateKey, this.orderBy);
        }

        options.orderBy = this.orderBy;

        this.collection.dataFetched = false;
        this.collection.skipFetch = false;
        this.loadData(options);
    }
})
