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
({
    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        this.initDashletComponents();
    },

    /**
     * Load dashlet preview by passing preview metadata
     *
     * @param {Object} metadata Preview metadata.
     */
    initDashletComponents: function() {
        var config = this.context.get('dashConfig');

        var metadata = {
                component: 'saved-reports-chart',
                name: 'saved-reports-chart',
                type: 'saved-reports-chart',
                label: config.label || app.lang.get('LBL_DASHLET_SAVED_REPORTS_CHART'),
                description: 'LBL_DASHLET_SAVED_REPORTS_CHART_DESC',
                // module: this.context.get('module'), // this breaks Dashlet plugin at context.parent
                module: null,
                config: [],
                preview: []
            };

        var field = {
                type: 'chart',
                name: 'chart',
                label: 'LBL_CHART',
                view: 'detail',
                module: metadata.module
            };

        var component = {
                name: metadata.component,
                type: metadata.type,
                preview: true,
                context: this.context,
                module: metadata.module,
                chart: field
            };

        component.view = _.extend({module: metadata.module}, metadata.preview, component);

        this.initComponents([{
            layout: {
                type: 'dashlet',
                css_class: 'dashlets',
                config: false,
                preview: true,
                label: metadata.label,
                module: metadata.module,
                context: this.context,
                components: [
                    component
                ]
            }
        }], this.context);
    },

    /**
     * @inheritdoc
     */
    render: function() {
        this._super('render');

        var dashlet = this.getComponent('dashlet').getComponent('saved-reports-chart');
        // var field = dashlet.getField('chart');
        var config = this.context.get('dashConfig');

        //TODO: the existing dashlet has the report data, why reload the report from server?
        dashlet.settings.set(config);
        dashlet.loadData();

        //TODO: set metadata on headerpane so we don't have to do this
        dashlet.$('.dashlet-title').text(config.label);
    }

})
