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
 * @class View.Views.Base.NotificationCenterConfigCarriersView
 * @alias SUGAR.App.view.layouts.BaseNotificationCenterConfigCarriersView
 * @extends View.Views.Base.NotificationCenterConfigPanelView
 */
({
    extendsFrom: 'NotificationCenterConfigPanelView',

    /**
     * Array of known system carriers.
     */
    carriers: [],

    /**
     * Populate carrier fields from the model before render.
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        this.meta.description = (this.model.get('configMode') === 'user') ?
            'LBL_CARRIER_DELIVERY_USER_DESC' :
            'LBL_CARRIER_DELIVERY_ADMIN_DESC';

        this.before('render', this.populateCarriers, this);
    },

    /**
     * Extracts all carriers from model and prepares them to be rendered by a field.
     * @private
     */
    populateCarriers: function() {
        var carriersData;

        if (this.model.get('configMode') === 'user') {
            carriersData = this.model.get('personal') ? this.model.get('personal')['carriers'] : null;
        } else {
            carriersData = this.model.get('carriers');
        }

        this.carriers = [];
        _.each(carriersData, function(value, key) {
            this.carriers.push({
                name: key,
                type: 'carrier',
                label: app.lang.get('LBL_TITLE', key),
                view: 'default'
            });
            if (value.selectable) {
                this.carriers.push({
                    name: key + '-address',
                    type: 'address',
                    view: 'edit',
                    options: value.options,
                    carrier: key,
                    css_class: 'span12'
                });
            }
        }, this);
    }
})
