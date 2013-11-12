/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
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
     * @class View.ResolveConflictsListView
     * @alias SUGAR.App.view.views.ResolveConflictsListView
     * @extends View.FlexListView
     */
    extendsFrom: 'FlexListView',
    plugins: ['ListColumnEllipsis', 'ListRemoveLinks'],

    displayFirstNColumns: 4,

    initialize: function (options) {
        // set as single select list
        options.meta = options.meta || {};
        options.meta.selection = {type: 'single', label: 'LBL_LINK_SELECT'};

        this._super('initialize', [options]);

        // do not fetch on initial load
        this.context._fetchCalled = true;

        this._buildList();
    },

    /**
     * Do not build default list columns.
     */
    parseFields: function () {},

    /**
     * Populate the list with data from the client and the server.
     * @private
     */
    _buildList: function() {
        var dataInDb = this.context.get('dataInDb'),
            modelToSave = this.context.get('modelToSave'),
            modelInDb, copyOfModelToSave, originalId;

        if (!_.isEmpty(dataInDb) && !_.isEmpty(modelToSave)) {
            modelInDb = app.data.createBean(modelToSave.module, dataInDb);
            copyOfModelToSave = app.data.createBean(modelToSave.module);
            originalId = modelToSave.get('id');

            copyOfModelToSave.copy(modelToSave);

            this._buildFieldDefinitions(copyOfModelToSave, modelInDb);

            // set IDs to be different so that backbone collection can recognize that they're not the same
            copyOfModelToSave.set('id', originalId + '-client');
            modelInDb.set('id', originalId + '-database');

            // indicate which model is from the client and the server
            copyOfModelToSave.set('_dataOrigin', 'client');
            modelInDb.set('_dataOrigin', 'database');

            // set the person who modified the data
            copyOfModelToSave.set('_modified_by', app.lang.get('LBL_YOU'));
            modelInDb.set('_modified_by', modelInDb.get('modified_by_name'));

            this._populateMissingDataFromDatabase(copyOfModelToSave, modelInDb);
            this.collection.add([copyOfModelToSave, modelInDb]);
        }
    },

    /**
     * Build columns to be displayed to the user.
     * @param {Bean} modelToSave
     * @param {Bean} modelInDb
     * @private
     */
    _buildFieldDefinitions: function(modelToSave, modelInDb) {
        var visibleColumns = [],
            availableColumns = [],
            fieldsThatDiffer,
            fieldDefinition,
            modifiedByColumnDef = {
                name: '_modified_by',
                type: 'base',
                label: 'LBL_MODIFIED',
                sortable: false
            };

        // determine which fields have different values
        fieldsThatDiffer = app.utils.compareBeans(modelToSave, modelInDb);

        // get field view definitions
        fieldDefinition = this._getFieldViewDefinition(fieldsThatDiffer);

        // insert modified by column
        fieldDefinition = _.union([modifiedByColumnDef], fieldDefinition);

        _.each(fieldDefinition, function(field) {
            field.sortable = false; //set all columns to not sort
            if (field.name === 'date_modified') {
                field.selected = false;
                availableColumns.push(field);
            } else {
                field.selected = true;
                visibleColumns.push(field);
            }
        });

        this._fields = {
            'default': fieldDefinition, //all available fields shown to the user
            'available': availableColumns, //hidden by default
            'visible': visibleColumns, //visible by default
            'options': fieldDefinition
        };
    },

    /**
     * Get field view definition from the record view, given field names.
     * @param fieldNames
     * @returns {Array}
     * @private
     */
    _getFieldViewDefinition: function(fieldNames) {
        var fieldDefs = [],
            moduleViewDefs = app.metadata.getView(this.module, 'record'),
            addFieldDefinition = function(definition) {
                if (definition.name && (_.indexOf(fieldNames, definition.name) !== -1)) {
                    fieldDefs.push(app.utils.deepCopy(definition));
                }
            };

        _.each(moduleViewDefs.panels, function(panel) {
            _.each(panel.fields, function(field) {
                if (field.fields && _.isArray(field.fields)) {
                    // iterate through fieldsets to get the field view definition
                    _.each(field.fields, function(field) {
                        addFieldDefinition(field);
                    });
                } else {
                    addFieldDefinition(field);
                }
            });
        });

        return fieldDefs;
    },

    /**
     * Populate missing values on the client's bean from the database data.
     * @param {Bean} modelToSave
     * @param {Bean} modelInDb
     * @private
     */
    _populateMissingDataFromDatabase: function(modelToSave, modelInDb) {
        _.each(modelInDb.attributes, function(value, attribute) {
            if (!modelToSave.has(attribute)) {
                modelToSave.set(attribute, value);
            }
        })
    },

    /**
     * Trigger preview event when the preview is clicked. Preview needs to render without activity
     * stream and pagination.
     */
    addPreviewEvents: function () {
        app.view.invokeParent(this, {type: 'view', name: 'flex-list', method: 'addPreviewEvents'});

        this.context.off('list:preview:fire', null, this);
        this.context.on('list:preview:fire', function (model) {
            app.events.trigger('preview:render', model, this.collection, false, undefined, false);
            app.events.trigger('preview:pagination:hide');
        }, this);
    },

    /**
     * Add Preview button on the actions column on the right.
     */
    addActions: function() {
        app.view.invokeParent(this, {type: 'view', name: 'flex-list', method: 'addActions'});

        this.rightColumns.push({
            type: 'rowaction',
            css_class: 'btn',
            tooltip: 'LBL_PREVIEW',
            event: 'list:preview:fire',
            icon: 'icon-eye-open'
        });
    }
})
