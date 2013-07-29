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
    extendsFrom: 'CreateView',
    currencyFields: [],

    initialize: function(options) {
        //reinitialize array on each init
        this.currencyFields = [];
        app.view.invokeParent(this, {type: 'view', name: 'create', method: 'initialize', args: [options]});
        this._parsePanelFields(this.meta.panels);
    },

    /**
     * Bind to model to make it so that it will re-render once it has loaded.
     */
    bindDataChange: function() {
        // TODO: Calling "across controllers" considered harmful .. please consider using a plugin instead.
        app.view.invokeParent(this, {type: 'view', name: 'create', method: 'bindDataChange'});
        this.model.on('change:base_rate', function() {
            _.debounce(this.convertCurrencyFields(this.model.previous("currency_id"), this.model.get("currency_id")), 500, true);
        }, this);
    },

    /**
     * convert all of the currency fields to the new currency
     * @param oldCurrencyId
     * @param newCurrencyId
     */
    convertCurrencyFields: function(oldCurrencyId, newCurrencyId) {
        //run through the editable currency fields and convert the amounts to the new currency
        _.each(this.currencyFields, function(currencyField) {
            if (!_.isUndefined(this.model.get(currencyField)) && currencyField != 'total_amount') {
                this.model.set(currencyField, app.currency.convertAmount(this.model.get(currencyField), oldCurrencyId, newCurrencyId), {silent: true});
            }
            this.model.trigger("change:" + currencyField);
        }, this);
    },

    /**
     * Parse the fields in the panel for the different requirement that we have
     *
     * @param {Array} panels
     * @protected
     */
    _parsePanelFields: function(panels) {
        _.each(panels, function(panel) {
            if (!app.metadata.getModule("Forecasts", "config").is_setup) {
                // use _.every so we can break out after we found the commit_stage field
                _.every(panel.fields, function(field, index) {
                    if (field.name == 'commit_stage') {
                        panel.fields[index] = {
                            'name': 'spacer',
                            'span': 6,
                            'readonly': true
                        };
                        return false;
                    }
                    return true;
                }, this);
            } else {
                _.each(panel.fields, function(field) {
                    if (field.type == 'currency') {
                        this.currencyFields.push(field.name);
                    }
                    if (field.name == "commit_stage") {
                        field.options = app.metadata.getModule("Forecasts", "config").buckets_dom;
                    }
                }, this);
            }
        }, this);
    }
})
