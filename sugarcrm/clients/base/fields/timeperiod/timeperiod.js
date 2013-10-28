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
     * Who we should extend
     */
    extendsFrom: 'EnumField',

    /**
     * Plugins
     */
    plugins: ['EllipsisInline', 'Tooltip'],

    /**
     * The template for the tooltip
     */
    tooltipTemplate: '',

    /**
     * Collection for fetching all the Timeperiods
     */
    tpCollection: undefined,

    /**
     * Mapping of ID's with the start ane end dates formatted for use when the tooltip is displayed
     */
    tpTooltipMap: {},

    /**
     * The selector we use to find the dropdown since it's appended to the body and not the current element
     */
    cssClassSelector: '',

    /**
     * Flag to use if Select2 tries to format the tooltips before timeperiod data returns from the server
     */
    updateDefaultTooltip: false,

    /**
     * {@inheritDoc}
     */
    initialize: function(options) {
        app.view.invokeParent(this, {type: 'field', name: 'enum', method: 'initialize', args: [options]});

        // get timeperiods list
        this.tpCollection = app.data.createBeanCollection("TimePeriods");
        this.tpCollection.once('reset', this.formatTooltips, this);
        this.tpCollection.fetch({limit: 100});

        // load the tooltip template
        this.tooltipTemplate = app.template.getField('timeperiod', 'tooltip', this.module);
    },

    /**
     * Utility method to take the TimePeriod collection and parse our the start and end dates to be in the user
     * date preference and store them for when the enum is actually opened
     * @param data
     */
    formatTooltips: function(data) {
        var usersDatePrefs = app.user.getPreference('datepref');
        data.each(function(model) {
          this.tpTooltipMap[model.id] = {
              start :app.date.format(app.date.parse(model.get('start_date')), usersDatePrefs),
              end: app.date.format(app.date.parse(model.get('end_date')), usersDatePrefs)
          }
        }, this);
        // since we don't need it any more, destroy it
        this.tpCollection = undefined;

        if(this.updateDefaultTooltip) {
            this.updateDefaultTooltip = false;
            // manually update the default selected item's tooltip
            var tooltipText = app.lang.get('LBL_DROPDOWN_TOOLTIP', 'TimePeriods', this.tpTooltipMap[this.value[0]]);
            this.$('[rel="tooltip"]').attr('data-original-title', tooltipText);
        }
    },

    /**
     * {@inheritDoc}
     */
    _render: function() {
        app.view.invokeParent(this, {type: 'field', name: 'enum', method: '_render'});
        if (this.tplName == 'noaccess') {
            return this;
        }

        var $el = this.$(this.fieldTag);
        $el.on('select2-selected', _.bind(this.onSelectClear, this));
        $el.on('select2-open', _.bind(this.onSelectOpen, this));
        $el.on('select2-close', _.bind(this.onSelectClear, this));

        this.initializeAllPluginTooltips();

        return this;
    },

    /**
     * On select open, we need to bind the tool tips
     */
    onSelectOpen: function() {
        var $el = $('div.' + this.cssClassSelector);
        this.removePluginTooltips($el);
        this.addPluginTooltips($el);
    },

    /**
     * When an item is selected of or the select is closed, we need to clean up the tool tips
     */
    onSelectClear: function() {
        var $el = $('div.' + this.cssClassSelector);
        this.removePluginTooltips($el);
    },

    /**
     * {@inheritDoc}
     */
    getSelect2Options: function(optionsKeys) {
        var options = app.view.invokeParent(this, {type: 'field', name: 'enum',
            method: 'getSelect2Options', args: [optionsKeys]});

        // this is to format the results
        options.formatResult = _.bind(this.formatOption, this);

        // this is to format the currently selected option
        options.formatSelection = _.bind(this.formatOption, this);

        if (_.isEmpty(options.dropdownCssClass)) {
            options.dropdownCssClass = 'select2-timeperiod-dropdown-' + this.cid;
        }

        this.cssClassSelector = options.dropdownCssClass;

        return options;
    },

    /**
     * Format Option for the results and the selected option to bind the tool tip data into the html
     * that gets output
     *
     * @param {Object} object
     * @returns {string}
     */
    formatOption: function(object) {
        // check once if the tpTooltipMap has been built yet
        this.updateDefaultTooltip = _.isUndefined(this.tpTooltipMap[object.id]);
        return this.tooltipTemplate({tooltip: this.tpTooltipMap[object.id], value: object.text});
    }
})
