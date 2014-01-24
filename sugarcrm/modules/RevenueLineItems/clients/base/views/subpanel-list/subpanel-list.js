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
     * Custom Subpanel Layout for Revenue Line Items
     *
     * @class RevenueLineItem.View.SubpanelListView
     * @extends View.SubpanelListView
     */

    extendsFrom: 'SubpanelListView',

    /**
     * We have to overwrite this method completely, since there is currently no way to completely disable
     * a field from being displayed
     *
     * @returns {{default: Array, available: Array, visible: Array, options: Array}}
     */
    parseFields : function() {
        var catalog = this._super('parseFields');

        // if forecast is not setup, we need to make sure that we hide the commit_stage field
        if (!app.metadata.getModule("Forecasts", "config").is_setup) {
            _.each(catalog, function (group, i) {
                catalog[i] = _.filter(group, function (fieldMeta) {
                    return (fieldMeta.name != 'commit_stage');
                });
            });
        }

        return catalog;
    }
})
