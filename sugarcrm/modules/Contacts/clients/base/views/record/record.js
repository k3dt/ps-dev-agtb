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
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

({
    extendsFrom: 'RecordView',

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this.plugins = _.union(this.plugins || [], ['HistoricalSummary']);
        //BEGIN SUGARCRM flav=ent ONLY
        this.plugins.push('ContactsPortalMetadataFilter');
        //END SUGARCRM flav=ent ONLY
        this._super('initialize', [options]);
        //BEGIN SUGARCRM flav=ent ONLY
        this.removePortalFieldsIfPortalNotActive(this.meta);
        //END SUGARCRM flav=ent ONLY
    }
})
