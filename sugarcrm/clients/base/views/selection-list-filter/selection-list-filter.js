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
 * @class View.Views.Base.SelectionListFilterView
 * @alias SUGAR.App.view.views.BaseSelectionListFilterView
 * @extends View.Views.Base.SelectionListView
 */
({
    extendsFrom: 'SelectionListView',

    initialize: function(options) {
        this.oneToMany = options.context.get('multiple');
        this._super('initialize', [options]);
    },

    /**
     * Override to setup events for subclasses
     */
    initializeEvents: function() {
        if (this.oneToMany) {
            this.context.on('selection:select:fire', this._selectMultipleAndClose, this);
        } else {
            this.context.on('change:selection_model', this._selectAndClose, this);
        }
    },

    /**
     * Select multiple models to link and fire the mass link event
     * @private
     */
    _selectMultipleAndClose: function() {
        var selections = this.context.get('mass_collection');
        if (selections) {
            app.drawer.close(this._getCollectionAttributes(selections));
        }
    },

    _getCollectionAttributes: function(collection) {
        var attributes = [];
        _.each(collection.models, _.bind(function(model) {
            attributes.push(this._getModelAttributes(model));
        }, this));

        return attributes;
    }
})
