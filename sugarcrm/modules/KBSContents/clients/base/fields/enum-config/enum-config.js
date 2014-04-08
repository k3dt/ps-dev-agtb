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
 * Copyright  2004-2014 SugarCRM Inc.  All rights reserved.
 */

({
    extendsFrom: 'EnumField',

    /**
     * {@inheritDoc}
     */
    loadEnumOptions: function(fetch, callback) {
        var module = this.def.module || this.module,
            optKey = this.def.key || this.name,
            config = app.metadata.getModule(module, 'config') || {};
        this._setItems(config[optKey]);
        fetch = fetch || false;

        if (fetch || !this.items) {
            var url = app.api.buildURL(module, 'config', null, {});
            app.api.call('read', url, null, {
                success: _.bind(function(data) {
                    this._setItems(data[optKey]);
                    callback.call(this);
                }, this)
            });
        }
    },

    /**
     * {@inheritDoc}
     */
    _loadTemplate: function() {
        this.type = 'enum';
        this._super('_loadTemplate');
        this.type = this.def.type;
    },

    /**
     * Sets current items.
     * @param {Array} values Values to set into items.
     */
    _setItems: function(values) {
        var result = {},
            def = null;
        _.each(values, function(val) {
            var tmp = _.omit(val, 'primary');
            _.extend(result, tmp);
            if (val.primary) {
                def = _.first(_.keys(tmp));
            }
        });
        this.items = result;
        if (def && _.isUndefined(this.model.get(this.name))) {
            this.defaultOnUndefined = false;
            // call with {silent: true} on, so it won't re-render the field, since we haven't rendered the field yet
            this.model.set(this.name, def, {silent: true});
            //Forecasting uses backbone model (not bean) for custom enums so we have to check here
            if (_.isFunction(this.model.setDefaultAttribute)) {
                this.model.setDefaultAttribute(this.name, def);
            }
        }
    },

    /**
     * {@inheritDoc}
     * Filters language items for different modes.
     * Disable edit mode for editing revision and for creating new revision.
     * Displays only available langs for creating localization.
     */
    setMode: function(mode) {
        if (mode == 'edit') {
            if (this.model.has('id')) {
                this.setDisabled(true);
            } else if (this.model.has('related_languages')) {
                if (this.model.has('kbsarticle_id')) {
                    this.setDisabled(true);
                } else {
                    _.each(this.model.get('related_languages'), function(lang) {
                        delete this.items[lang];
                    }, this);
                    this.model.set(this.name, _.first(_.keys(this.items)), {silent: true});
                }
            }
        }
        this._super('setMode', [mode]);
    }
})
