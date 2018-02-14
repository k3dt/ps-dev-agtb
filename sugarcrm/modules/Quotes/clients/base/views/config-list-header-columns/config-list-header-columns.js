/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/**
 * @class View.Views.Base.Quotes.ConfigListHeaderColumnsView
 * @alias SUGAR.App.view.views.BaseQuotesConfigListHeaderColumnsView
 * @extends View.Views.Base.FlexListView
 */
({
    /**
     * @inheritdoc
     */
    extendsFrom: 'FlexListView',

    /**
     * @inheritdoc
     */
    plugins: [
        'MassCollection',
        'ReorderableColumns'
    ],

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        this.massCollection = this.collection;
        this.leftColumns = [];

        this.addMultiSelectionAction();
        this.template = app.template.getView('config-list-header-columns', 'Quotes');
    },

    /**
     * @inheritdoc
     */
    bindDataChange: function() {
        this._super('bindDataChange');

        this.on('list:reorder:columns', this.onWorksheetColumnsOrderChanged, this);
    },

    /**
     * Handles when there's a change in the order of list header columns
     *
     * @param {Object} fields The fields object sent from ReorderableColumns plugin
     * @param {Array} newFieldNameOrder The new order of field names
     */
    onWorksheetColumnsOrderChanged: function(fields, newFieldNameOrder) {
        var newFieldOrder = [];
        var headerFields = this.model.get('worksheet_columns');

        _.each(newFieldNameOrder, function(fieldName) {
            newFieldOrder.push(_.find(headerFields, function(field) {
                return field.name === fieldName;
            }));
        }, this);

        this.model.set('worksheet_columns', newFieldOrder);
    },

    /**
     * Add multi selection field to left column using Quote data fields
     *
     * @override
     */
    addMultiSelectionAction: function() {
        var _generateMeta = function(buttons, disableSelectAllAlert) {
            return {
                name: 'quote-data-mass-actions',
                type: 'fieldset',
                fields: [
                    {
                        type: 'quote-data-actionmenu',
                        buttons: buttons || [],
                        disable_select_all_alert: !!disableSelectAllAlert
                    }
                ],
                value: false,
                sortable: false
            };
        };
        var buttons = this.meta.selection.actions;
        var disableSelectAllAlert = !!this.meta.selection.disable_select_all_alert;
        this.leftColumns.push(_generateMeta(buttons, disableSelectAllAlert));
    },

    /**
     * @inheritdoc
     */
    render: function() {
        var groupBtn;
        var massDeleteBtn;

        this._super('render');

        groupBtn = _.find(this.nestedFields, function(field) {
            return field.name === 'group_button';
        });
        massDeleteBtn = _.find(this.nestedFields, function(field) {
            return field.name === 'massdelete_button';
        });

        if (groupBtn) {
            groupBtn.setDisabled(true);
        }
        if (massDeleteBtn) {
            massDeleteBtn.setDisabled(true);
        }
    },

    /**
     * Sets the List Header column field names and re-renders
     *
     * @param {Array} headerFieldList The list of field
     */
    setColumnHeaderFields: function(headerFieldList) {
        headerFieldList = _.clone(headerFieldList);
        this.meta.panels = [{
            fields: headerFieldList
        }];
        this.model.set('worksheet_columns', headerFieldList);

        this._fields = this.parseFields();

        this.render();
    },

    /**
     * Adds a column header to the list columns
     *
     * @param {Object} field The field defs of the field to add
     */
    addColumnHeaderField: function(field) {
        var columns = this.model.get('worksheet_columns');
        columns.unshift(field);

        this.meta.panels[0].fields = columns;
        this.model.set('worksheet_columns', columns);
        this._fields = this.parseFields();

        this.render();
    },

    /**
     * Removes a column header from the list columns
     *
     * @param {Object} field The field defs of the field to remove
     */
    removeColumnHeaderField: function(field) {
        var fields = this.meta.panels[0].fields;
        fields = _.reject(fields, function(headerField) {
            return headerField.name === field.name;
        });
        this.meta.panels[0].fields = fields;

        this.model.set('worksheet_columns', fields);
        this._fields = this.parseFields();

        this.render();
    }
})
