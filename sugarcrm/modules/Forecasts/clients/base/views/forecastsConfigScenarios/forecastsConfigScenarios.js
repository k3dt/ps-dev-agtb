/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

({
    /**
     * Holds the title section for the selected values to add to the accordion toggle
     */
    titleSelectedValues: '',

    /**
     * Holds the view's title name
     */
    titleViewNameTitle: '',

    /**
     * Holds the collapsible toggle title template
     */
    toggleTitleTpl: {},

    /**
     * Holds ALL possible different scenarios
     */
    scenarioOptions: [],

    /**
     * Holds the scenario objects that should start selected by default
     */
    selectedOptions: [],

    /**
     * Holds the option from config that users cannot change
     */
    defaultOption: {},

    /**
     * Holds the select2 instance of the default scenario that users cannot change
     */
    defaultSelect2: {},

    /**
     * Holds the select2 instance of the options that users can add/remove
     */
    optionsSelect2: {},

    /**
     * The default key used for the "Amount" value in forecasts, right now it is "likely" but users will be able to
     * change that in admin to be best or worst
     *
     * todo: eventually this will be moved to config settings where users can select their default forecasted value likely/best/worst
     */
    defaultForecastedAmountKey: 'show_worksheet_likely',

    events: {
        'click .resetLink': 'onResetLinkClicked'
    },

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        this.titleViewNameTitle = app.lang.get('LBL_FORECASTS_CONFIG_TITLE_SCENARIOS', 'Forecasts');

        // set up scenarioOptions
        _.each(options.meta.panels[0].fields, function(field) {
            var obj = {
                id: field.name,
                text: app.lang.get(field.label, 'Forecasts')
            }

            // Check if this field is the one we don't want users to delete
            if(field.name == this.defaultForecastedAmountKey) {
                obj['locked'] = true;
                this.defaultOption = obj;
            } else {
                // Push fields to all other scenario options
                this.scenarioOptions.push(obj);
            }

            // if this should be selected by default and it is not the undeletable scenario, push it to selectedOptions
            if(this.context.get('model').get(field.name) == 1 && !obj.locked) {
                //this.selectedScenariosCSV += field.name + ',';
                // push fields that should be selected to selectedOptions
                this.selectedOptions.push(obj);
            }
        }, this);

        this.toggleTitleTpl = app.template.getView('forecastsConfigHelpers.toggleTitle', 'Forecasts');
    },

    /**
     * Handles when reset to defaults link has been clicked
     * @param evt click event
     */
    onResetLinkClicked: function(evt) {
        evt.preventDefault();
        evt.stopImmediatePropagation();

        /**
         * todo implement resetting to defaults
         */
    },

    bindDataChange: function() {
        if(this.model) {
            this.model.on('change:scenarios', function(model) {
                var arr = [];

                if(model.get('show_worksheet_likely')) {
                    arr.push(app.lang.get('LBL_FORECASTS_CONFIG_WORKSHEET_SCENARIOS_LIKELY', 'Forecasts'));
                }
                if(model.get('show_worksheet_best')) {
                    arr.push(app.lang.get('LBL_FORECASTS_CONFIG_WORKSHEET_SCENARIOS_BEST', 'Forecasts'));
                }
                if(model.get('show_worksheet_worst')) {
                    arr.push(app.lang.get('LBL_FORECASTS_CONFIG_WORKSHEET_SCENARIOS_WORST', 'Forecasts'));
                }

                this.titleSelectedValues = arr.join(', ');

                this.updateTitle();
            }, this);

            // trigger the change event to set the title when this gets added
            this.model.trigger('change:scenarios', this.model);
        }
    },

    /**
     * Updates the accordion toggle title
     */
    updateTitle: function() {
        var tplVars = {
            title: this.titleViewNameTitle,
            selectedValues: this.titleSelectedValues,
            viewName: 'forecastsConfigScenarios'
        };

        this.$el.find('#scenariosTitle').html(this.toggleTitleTpl(tplVars));
    },

    _render: function() {
        app.view.View.prototype._render.call(this);

        // add accordion-group class to wrapper $el div
        this.$el.addClass('accordion-group');

        // update the accordion title
        this.updateTitle();

        // handle default/un-delete-able scenario
        this.defaultSelect2 = this.$el.find('#scenariosLocked').select2({
            data: this.defaultOption,
            multiple: false,
            dropdownCss: {width:'auto'},
            dropdownCssClass: 'search-related-dropdown',
            containerCss: "border: none",
            containerCssClass: 'select2-container-disabled',
            formatSelection: this.formatCustomSelection,
            escapeMarkup: function(m) { return m; },
            initSelection : _.bind(function (element, callback) {
                callback(this.defaultOption);
            }, this)
        });

        // set the default value
        this.defaultSelect2.select2('val', this.defaultOption);

        // disable the select2
        this.defaultSelect2.select2('disable');

        // handle setting up select2 options
        this.optionsSelect2 = this.$el.find('#scenariosSelect').select2({
            data: this.scenarioOptions,
            multiple: true,
            dropdownCss: {width:"auto"},
            dropdownCssClass: "search-filter-dropdown",
            containerCss: "border: none",
            containerCssClass: "select2-choices-pills-close",
            formatSelection: this.formatCustomSelection,
            escapeMarkup: function(m) { return m; },
            initSelection : _.bind(function (element, callback) {
                callback(this.selectedOptions);
            }, this)
        });
        this.optionsSelect2.select2('val', this.selectedOptions);

        this.optionsSelect2.on('change', _.bind(this.handleScenarioModelChange, this));
    },

    handleScenarioModelChange: function(evt) {
        var changedEnabled = [],
            changedDisabled = [],
            allOptions = [];

        // Get the options that changed and set the model
        _.each($(evt.target).val().split(','), function(option) {
            changedEnabled.push(option);
            this.model.set(option, true, {silent: true});
        }, this);

        // Convert all scenario options into a flat array of ids
        _.each(this.scenarioOptions, function(option) {
            allOptions.push(option.id);
        }, this);

        // Take all options and return an array without the ones that changed to true
        changedDisabled = _.difference(allOptions, changedEnabled);

        // Set any options that weren't changed to true to false
        _.each(changedDisabled, function(option) {
            this.model.set(option, false, {silent: true});
        }, this);

        this.model.trigger('change:scenarios', this.model);
    },

    /**
     * Formats pill selections
     *
     * @param item selected item
     */
    formatCustomSelection: function(item) {
        return '<span class="select2-choice-type"> </span><a class="select2-choice-filter" rel="'+ item.id + '" href="javascript:void(0)">'+ item.text +'</a>';
    },

    /**
     * override dispose function to remove custom listener off the window
     * @private
     */
    _dispose: function() {
        // remove event listener from select2
        this.optionsSelect2.off();
        app.view.Component.prototype._dispose.call(this);
    }
})
