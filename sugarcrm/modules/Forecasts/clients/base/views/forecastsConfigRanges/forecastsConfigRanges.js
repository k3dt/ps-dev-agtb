/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
({
    /**
     * used to hold the label string from metadata to get rendered in the template.
     */
    label: '',

    /**
     * used to hold the metadata for the forecasts_ranges field, used to manipulate and render out as the radio buttons
     * that correspond to the fieldset for each bucket type.
     */
    forecast_ranges_field: {},

    /**
     * Used to hold the buckets_dom field metadata, used to retrieve and set the proper bucket dropdowns based on the
     * selection for the forecast_ranges
     */
    buckets_dom_field: {},

    /**
     * Used to hold the category_ranges field metadata, used for rendering the sliders that correspond to the range
     * settings for each of the values contained in the selected buckets_dom dropdown definition.
     */
    category_ranges_field: {},

    /**
     * Used to keep track of the selection as it changes so that it can be used to determine how to hide and show the
     * sub-elements that contain the fields for setting the category ranges
     */
    selection: '',

    /**
     * a placeholder for the individual range sliders that will be used to build the range setting
     */
    fieldRanges: {},

    //TODO-sfa remove this once the ability to map buckets when they get changed is implemented (SFA-215).
    /**
     * This is used to determine whether we need to lock the module or not, based on whether forecasts has been set up already
     */
    disableRanges: false,

    /**
     * Initializes the view, and then initializes up the parameters for the field metadata holder parameters that get
     * used to render the fields in the view, since they are not rendered in a standard way.
     * @param options
     */
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        this.label = _.first(this.meta.panels).label;

        // sets this.<array_item>_field to the corresponding field metadata, which gets used by the template to render these fields later.
        _.each(['forecast_ranges', 'buckets_dom', 'category_ranges'], function(item){
            var fields = _.first(this.meta.panels).fields;

            this[item + '_field'] = function(fieldName, fieldMeta) {
                return _.find(fieldMeta, function(field) { return field.name == this; }, fieldName);
            }(item, fields);

        }, this);

        // set the values for forecast_ranges_field and buckets_dom_field from the model, so it can be set to selected properly when rendered
        this.forecast_ranges_field.value = this.model.get('forecast_ranges');
        this.buckets_dom_field.value = this.model.get('buckets_dom');

        if(!_.isUndefined(options.meta.registerLabelAsBreadCrumb) && options.meta.registerLabelAsBreadCrumb == true) {
            this.layout.registerBreadCrumbLabel(options.meta.panels[0].label);
        }
    },

    _render: function() {
        //TODO-sfa remove this once the ability to map buckets when they get changed is implemented (SFA-215).
        // This will be set to true if the forecasts ranges setup should be disabled
        this.disableRanges = this.context.config.get('has_commits');
        this.selection = this.context.config.get('forecast_ranges');

        app.view.View.prototype._render.call(this);

        this._addForecastRangesSelectionHandler();

        return this;
    },

    /**
     * Adds the selection event handler on the forecast ranges radio which sets on the model the value of the bucket selection, the
     * correct dropdown list based on that selection, as well as opens up the element to show the range setting sliders
     * @private
     */
    _addForecastRangesSelectionHandler: function (){
        // finds all radiobuttons with this name
        var elements = this.$el.find(':radio[name="' + this.forecast_ranges_field.name + '"]');

        // apply the change handler to each of the ranges radio button elements.
        _.each(elements, function(el) {
            $(el).change({
                view:this
            }, this.selectionHandler);
            // of the elements find the one that is checked
            if($(el).prop('checked')) {
                // manually trigger the handler on the checked element so that it will render
                // for the default/previously set value
                $(el).triggerHandler("change");
            }
        }, this);
    },

    selectionHandler: function(event) {
        var view = event.data.view,
            oldValue,
            bucket_dom,
            hideElement,
            showElement,
            ranges_options;

        // get the value of the previous selection so that we can hide that element
        oldValue = view.selection;
        // now set the new selection, so that if they change it, we can later hide the things we are about to show.
        view.selection = this.value;

        bucket_dom = view.buckets_dom_field.options[this.value];

        hideElement = view.$el.find('#' + oldValue + '_ranges');
        showElement = view.$el.find('#' + this.value + '_ranges');

        if (showElement.children().length == 0) {

            this.value == 'show_custom_buckets' ? view._customSelectionHandler(this, showElement) : view._selectionHandler(this, showElement);

            // use call to set context back to the view for connecting the sliders
            view.connectSliders.call(view, this.value, view.fieldRanges);
        }

        if (hideElement) {
            hideElement.toggleClass('hide', true);
        }
        if (showElement){
            showElement.toggleClass('hide', false);
        }

        // set the forecast ranges and associated dropdown dom on the model
        view.model.set(this.name, this.value);
        view.model.set(view.buckets_dom_field.name, bucket_dom);
    },

    /**
     * selection handler for standard ranges (two and three ranges)
     *
     * @param element
     * @param showElement
     * @private
     */
    _selectionHandler : function(element, showElement) {
        var bucket_dom = this.buckets_dom_field.options[element.value];

        // add the things here...
        this.fieldRanges[element.value] = {};
        showElement.append('<p>' + app.lang.get('LBL_FORECASTS_CONFIG_' + element.value.toUpperCase() + '_RANGES_DESCRIPTION', 'Forecasts') + '</p>');

        _.each(app.lang.getAppListStrings(bucket_dom), function(label, key) {
            if (key != 'exclude') {

                var rangeField,
                    model = new Backbone.Model(),
                    fieldSettings;

                // get the value in the current model and use it to display the slider
                model.set(key, this.view.model.get(this.category + '_ranges')[key]);

                // build a range field
                fieldSettings = {
                    view: this.view,
                    def: _.find(
                        _.find(
                            _.first(this.view.meta.panels).fields,
                            function(field) {
                                return field.name == 'category_ranges';
                            }
                        ).ranges,
                        function(range) {
                            return range.name == this.key
                        },
                        {key: key}
                    ),
                    viewName:'edit',
                    context: this.view.context,
                    module: this.view.module,
                    model: model,
                    meta: app.metadata.getField('range')
                };

                //TODO-sfa remove this once the ability to map buckets when they get changed is implemented (SFA-215).
                if(this.view.disableCategories) {
                    fieldSettings.viewName = 'detail';
                    fieldSettings.def.view = 'detail';
                }

                rangeField = app.view.createField(fieldSettings);
                this.showElement.append('<b>'+ label +':</b>').append(rangeField.el);
                rangeField.render();

                // now give the view a way to get at this field's model, so it can be used to set the value on the
                // real model.
                this.view.fieldRanges[this.category][key] = rangeField;

                // this gives the field a way to save to the view's real model. It's wrapped in a closure to allow us to
                // ensure we have everything when switching contexts from this handler back to the view.
                rangeField.sliderDoneDelegate = function(category, key, view) {
                    return function (value) {
                        this.view.updateRangeSettings(category, key, value);
                    };
                }(this.category, key, this.view);
            }
        }, {view: this, showElement:showElement, category: element.value});
        showElement.append($('<p>' + app.lang.get("LBL_FORECASTS_CONFIG_RANGES_EXCLUDE_INFO", "Forecasts")+ '</p>'));
    },

    /**
     * selection handler for custom ranges
     * @param element
     * @param showElement
     * @private
     */
    _customSelectionHandler : function(element, showElement) {
        var bucket_dom = this.buckets_dom_field.options[element.value],
            bucket_dom_options = [], rangeField;

        // add the things here...
        this.fieldRanges[element.value] = {};
        showElement.append('<p>' + app.lang.get('LBL_FORECASTS_CONFIG_' + element.value.toUpperCase() + '_RANGES_DESCRIPTION', 'Forecasts') + '</p>');

        // if custom bucket isn't defined seve default values
        if ( !this.model.has(element.value + '_ranges') ) {
            this.model.set(element.value + '_ranges', {});
        }
        _.each(app.lang.getAppListStrings(bucket_dom), function(label, key) {
            if (_.isUndefined(this.view.model.get(this.category + '_ranges')[key]) ) {
                var _ranges = this.view.model.get(this.category + '_ranges');
                _ranges[key] = {min: 0, max: 100};
                this.view.model.set(this.category + '_ranges', _ranges);
            }
            bucket_dom_options.push([key, label]);
        }, {view: this, category: element.value});

        // save key and label of custom range from the language file to model
        // then we can add or remove ranges and save it on backend side
        // bind handler on change to validate data
        this.model.set(element.value + '_options', bucket_dom_options);
        this.model.on('change:' + element.value + '_options', function(event) {
            this.view.validateCustomRangeLabels(this.category);
        }, {view: this, category: element.value});


        // create layout, create pleceholders for different types of custom ranges
        this._renderCustomRangesLayout(showElement);

        // render custom ranges
        _.each(app.lang.getAppListStrings(bucket_dom), function(label, key) {
            rangeField = this.view._renderCustomRange(key, label, showElement, element.value);
            // now give the view a way to get at this field's model, so it can be used to set the value on the
            // real model.
            this.view.fieldRanges[element.value][key] = rangeField;
        }, { view: this, showElement:showElement, category: element.value });

        // bind handler of add custom range buttons
        this.$el.find('#btnAddCustomRange a').on('click', { view: this, category: element.value, customType: 'custom' }, this.addCustomRange);
        this.$el.find('#btnAddCustomRangeWithoutProbability a').on('click', { view: this, category: element.value, customType: 'custom_without_probability' }, this.addCustomRange);
        // if there are custom ranges not based on probability hide add button on the top of block
        if ( this._getLastCustomRangeIndex(element.value, 'custom_without_probability') ) {
            this.$el.find('#btnAddCustomRangeWithoutProbability').hide();
        }
    },

    /**
     * render layout for custom ranges, add placeholders for different types of ranges
     * @param showElement
     * @private
     */
    _renderCustomRangesLayout : function(showElement)
    {
        var plhCustomProbabilityRanges,
            plhCustomWithoutProbability,
            plhExclude;

        showElement.append('<p><b>Ranges based on probabilities</b></p>');
        showElement.append('<div id="plhCustomProbabilityRanges"></div>');

        // main placeholder
        plhCustomProbabilityRanges = this.$el.find('#plhCustomProbabilityRanges');
        // placeholder to render include and upside ranges
        plhCustomProbabilityRanges.append('<div id="plhCustomDefault"></div>');
        plhCustomProbabilityRanges.append('<p><b>Custom Ranges based on probabilities</b></p>');
        // placeholder to render custom ranges based on probability
        plhCustomProbabilityRanges.append('<div id="plhCustom"></div>');
        // placeholder to render exclude range
        plhCustomProbabilityRanges.append('<div id="plhExclude"></div>');

        showElement.append('<p><b>Ranges not based on probabilities</b></p>');
        // placeholder to render custom ranges not based on probability
        showElement.append('<div id="plhCustomWithoutProbability"></div>');

        // add button to add new custom range based on probability
        plhExclude = this.$el.find('#plhExclude');
        plhExclude.append('<div class="btn-group" id="btnAddCustomRange"><a class="btn" href="javascript:void(0)"><i class="icon-plus"></i></a></div>');
        // add button to add new custom range not based on probability
        plhCustomWithoutProbability = this.$el.find('#plhCustomWithoutProbability');
        plhCustomWithoutProbability.append('<div class="btn-group" id="btnAddCustomRangeWithoutProbability"><a class="btn" href="javascript:void(0)"><i class="icon-plus"></i></a></div>');
    },

    /**
     * create new custom range field and render it in showElement
     * @param key
     * @param label
     * @param showElement
     * @param category
     * @private
     * @return View.field new created field
     */
    _renderCustomRange : function(key, label, showElement, category) {
        var customType = key,
            customIndex = 0,
            // placeholder to insert custom range
            currentPlh = showElement,
            rangeField,
            model = new Backbone.Model(),
            fieldSettings,
            lastCustomRange;

        // define type of new custom range based on name of range and choose placeholder to insert
        // custom_default: include, upside or exclude
        // custom - based on probability
        // custom_without_probability - not based on probability
        if ( key.substring(0,26) == 'custom_without_probability' ) {
            customType = 'custom_without_probability';
            customIndex = key.substring(27);
            currentPlh = this.$el.find('#plhCustomWithoutProbability');
        } else if ( key.substring(0,6) == 'custom' ) {
            customType = 'custom';
            customIndex = key.substring(7);
            currentPlh = this.$el.find('#plhCustom');
        } else if ( key.substring(0,7) == 'exclude' ) {
            customType = 'custom_default';
            currentPlh = this.$el.find('#plhExclude');
        } else {
            customType = 'custom_default';
            currentPlh = this.$el.find('#plhCustomDefault');
        }

        // get the value in the current model and use it to display the slider
        model.set(key, this.model.get(category + '_ranges')[key]);

        // build a range field
        fieldSettings = {
            view: this,
            def: _.clone(_.find(
                _.find(
                    _.first(this.meta.panels).fields,
                    function(field) {
                        return field.name == 'category_ranges';
                    }
                ).ranges,
                function(range) {
                    return range.name == this.key
                },
                {key: customType}
            )),
            viewName:'forecastsCustomRange',
            context: this.context,
            module: this.module,
            model: model,
            meta: app.metadata.getField('range')
        };
        // set up real range name
        fieldSettings.def.name = key;
        // set up view
        fieldSettings.def.view = 'forecastsCustomRange';
        // enable slider
        fieldSettings.def.enabled = true;

        //TODO-sfa remove this once the ability to map buckets when they get changed is implemented (SFA-215).
        if(this.disableCategories) {
            fieldSettings.viewName = 'detail';
            fieldSettings.def.view = 'detail';
        }

        rangeField = app.view.createField(fieldSettings);
        currentPlh.append(rangeField.el);
        rangeField.label = label;
        rangeField.customType = customType;
        rangeField.customIndex = customIndex;
        rangeField.render();

        // enable slider after render
        rangeField.$el.find(rangeField.fieldTag).noUiSlider('enable');

        // handlers to add,remove or change custom range
        rangeField.$el.find(".addCustomRange").on('click', { view: this, category: category, customType: customType }, this.addCustomRange);
        rangeField.$el.find(".removeCustomRange").on('click', {view: this, range: rangeField, category: category}, this.removeCustomRange);
        rangeField.$el.find("input").on('keyup', {view: this, range: rangeField, category: category}, this.updateCustomRangeLabel);

        // hide add button for previous custom range not based on probability
        lastCustomRange = this._getLastCustomRange(category, rangeField.customType);
        if ( !_.isUndefined(lastCustomRange) ) {
            lastCustomRange.$el.find('.addCustomRange').parent().hide();
        }

        _.isEmpty(rangeField.label) ? rangeField.$el.find('.control-group').addClass('error') : rangeField.$el.find('.control-group').removeClass('error');

        // this gives the field a way to save to the view's real model. It's wrapped in a closure to allow us to
        // ensure we have everything when switching contexts from this handler back to the view.
        rangeField.sliderDoneDelegate = function(category, key, view) {
            return function (value) {
                this.view.updateRangeSettings(category, key, value);
            };
        }(category, key, this);

        return rangeField;
    },

    /**
     * return index of last custom range or 0
     * @param category
     * @param customType
     * @return {Number}
     * @private
     */
    _getLastCustomRangeIndex : function( category, customType ) {
        // find all custom ranges with type that should be created
        var lastCustomRange = _.last(_.sortBy(_.filter(
            this.fieldRanges[category],
            function(item) { return item.customType == this.key }, {key: customType}
        ), function(item) { return parseInt(item.customIndex, 10); }
        ));

        if ( _.isUndefined(lastCustomRange) ) return 0;

        return parseInt(lastCustomRange.customIndex, 10);
    },

    /**
     * return object of last created custom range
     * if there isn't range return upside/include for custom type and exclude for custom_without_probability type
     * @param category
     * @param customType
     * @return {*}
     * @private
     */
    _getLastCustomRange : function( category, customType ) {
        // find all custom ranges with type that should be created
        var lastCustomRange = _.last(_.sortBy(_.filter(
            this.fieldRanges[category],
            function(item) { return item.customType == this.key }, {key: customType}
        ), function(item) { return parseInt(item.customIndex, 10); }
        ));

        if ( _.isUndefined(lastCustomRange) ) {
            // there is not custom range - use default ranges
            if ( customType == 'custom' ) {
                // use upside or include
                lastCustomRange = !_.isUndefined(this.fieldRanges[category].upside) ? this.fieldRanges[category].upside : this.fieldRanges[category].include;
            } else {
                // use exclude
                lastCustomRange = this.fieldRanges[category].exclude;
            }
        }

        return lastCustomRange;
    },

    /**
     * add new custom cange field and render it in specific placeholder
     * @param event
     */
    addCustomRange : function(event) {
        var view = event.data.view,
            customType = event.data.customType,
            category = event.data.category,
            ranges = view.model.get(category + '_ranges'),
            bucket_dom_options = view.model.get(category + '_options'),
            showElement = ( key == 'custom' ) ? view.$el.find('#plhCustom') : view.$el.find('#plhCustomWithoutProbability'),
            model = new Backbone.Model(),
            label = app.lang.get('LBL_FORECASTS_CUSTOM_RANGES_DEFAULT_NAME', 'Forecasts'),
            key,
            fieldSettings,
            rangeField,
            lastCustomRange,
            lastCustomRangeIndex,
            lastOptionIndex;

        lastCustomRange = view._getLastCustomRange(category, customType);
        lastCustomRangeIndex = view._getLastCustomRangeIndex(category, customType);
        lastCustomRangeIndex++;

        // setup key for the new range
        key = customType + '_' + lastCustomRangeIndex;

        // set up min/max values for new custom range
        if ( customType != 'custom' ) {
            // if range is without probability setup min and max values to 0
            ranges[key] = {min: 0, max: 0};
        } else if ( ranges.exclude.max - ranges.exclude.min > 3 ) {
            // decrement exclude range to insert new range
            ranges[key] = {min: parseInt(ranges.exclude.max, 10) - 1, max: parseInt(ranges.exclude.max, 10)};
            ranges.exclude.max = parseInt(ranges.exclude.max, 10) - 2;
            if ( !_.isUndefined(view.fieldRanges[category].exclude.$el) ) {
                view.fieldRanges[category].exclude.$el.find(view.fieldRanges[category].exclude.fieldTag).noUiSlider('move', {handle: 'upper', to: ranges.exclude.max});
            }
        } else if ( ranges[lastCustomRange.name].max - ranges[lastCustomRange.name].min > 3 ) {
            // decrement previous range to insert new range
            ranges[key] = {min: parseInt(ranges[lastCustomRange.name].min, 10), max: parseInt(ranges[lastCustomRange.name].min, 10) + 1};
            ranges[lastCustomRange.name].min = parseInt(ranges[lastCustomRange.name].min, 10) + 2;
            if ( !_.isUndefined(lastCustomRange.$el) ) {
                lastCustomRange.$el.find(lastCustomRange.fieldTag).noUiSlider('move', {handle: 'lower', to: ranges[lastCustomRange.name].min});
            }
        } else {
            // TODO
            ranges[key] = {min: parseInt(ranges[lastCustomRange.name].min, 10) - 2, max: parseInt(ranges[lastCustomRange.name].min, 10) - 1};
        }

        view.model.unset(category + '_ranges', {silent: true});
        view.model.set(category + '_ranges', ranges);

        rangeField = view._renderCustomRange(key, label, showElement, category);
        if ( !_.isUndefined(rangeField) && !_.isNull(rangeField) ) {
            view.fieldRanges[category][key] = rangeField;
        }

        // add range to options
        _.each(bucket_dom_options, function(item, key){ if (item[0] == this.value) { lastOptionIndex = key; } }, {value: lastCustomRange.name});
        bucket_dom_options.splice(lastOptionIndex+1, 0, [key, label]);
        view.model.unset(category + '_options', {silent: true});
        view.model.set(category + '_options', bucket_dom_options);

        if ( customType == 'custom' ) {
            // use call to set context back to the view for connecting the sliders
            view.connectSliders.call(view, category, view.fieldRanges);
        } else {
            // haide add button form top of block and for previous ranges not based on probability
            view.$el.find('#btnAddCustomRangeWithoutProbability').hide();
            _.each(_.filter(
                view.fieldRanges[category],
                function(item) { return item.customType == this.key && item.customIndex < this.index; }, {key: customType, index: lastCustomRangeIndex}
            ), function(item) {
                if ( !_.isUndefined(item.$el) ) {
                    item.$el.find('.addCustomRange').parent().hide();
                }
            });
        }
    },

    /**
     * remove custom range from model and view
     * @param event
     * @return void
     */
    removeCustomRange : function(event) {
        var view = event.data.view,
            range = event.data.range,
            category = event.data.category,
            ranges = view.model.get(category + '_ranges'),
            bucket_dom_options = view.model.get(category + '_options'),
            previosCustomRange,
            lastCustomRangeIndex,
            lastCustomRange,
            optionIndex;

        if ( _.indexOf(['include', 'upside', 'exclude'], range.name) != -1 ) {
            return false;
        }

        if ( range.customType == 'custom' ) {
            // find previous renge and reassign range values form removed to it
            previosCustomRange = _.last(_.sortBy(_.filter(
                view.fieldRanges[category],
                function(item) { return item.customType == 'custom' && parseInt(item.customIndex, 10) < parseInt(this.index, 10); }, {index: range.customIndex}
            ), function(item) { return parseInt(item.customIndex, 10); }
            ));
            if ( _.isUndefined(previosCustomRange) ) {
                previosCustomRange = !_.isUndefined(view.fieldRanges[category].upside) ? view.fieldRanges[category].upside : view.fieldRanges[category].include;
            }
            ranges[previosCustomRange.name].min = parseInt(ranges[range.name].min);
            if ( !_.isUndefined(previosCustomRange.$el) ) {
                previosCustomRange.$el.find(previosCustomRange.fieldTag).noUiSlider('move', {handle: 'lower', to: ranges[previosCustomRange.name].min});
            }
        }

        // remove view for the range
        view.fieldRanges[category][range.name].remove();

        delete ranges[range.name];
        delete view.fieldRanges[category][range.name];

        // remove from bucket_dom_options
        _.each(bucket_dom_options, function(item, key){ if (item[0] == this.value) { optionIndex = key; } }, {value: range.name});
        bucket_dom_options.splice(optionIndex, 1);
        view.model.unset(category + '_options', {silent: true});
        view.model.set(category + '_options', bucket_dom_options);

        view.model.unset(category + '_ranges', {silent: true});
        view.model.set(category + '_ranges', ranges);

        if ( range.customType == 'custom' ) {
            // use call to set context back to the view for connecting the sliders
            view.connectSliders.call(view, category, view.fieldRanges);
        } else {
            // show add button for custom range not based on probability
            lastCustomRangeIndex = view._getLastCustomRangeIndex(category, range.customType);
            if ( lastCustomRangeIndex == 0 ) {
                view.$el.find('#btnAddCustomRangeWithoutProbability').show();
            } else {
                lastCustomRange = view._getLastCustomRange(category, range.customType);
                if ( !_.isUndefined(lastCustomRange.$el) ) {
                    lastCustomRange.$el.find('.addCustomRange').parent().show();
                }
            }
        }
    },

    /**
     * change label for custom range in model
     * @param event
     */
    updateCustomRangeLabel : function(event) {
        var view = event.data.view,
            range = event.data.range,
            category = event.data.category,
            ranges = view.model.get(category + '_ranges'),
            bucket_dom_options = view.model.get(category + '_options'),
            optionIndex;

        _.each(bucket_dom_options, function(item, key){ if (item[0] == this.value) { optionIndex = key; } }, {value: range.name});
        bucket_dom_options[optionIndex][1] = this.value;
        view.model.unset(category + '_options', {silent: true});
        view.model.set(category + '_options', bucket_dom_options);
    },

    /**
     * validate labels for custom ranges, if it is invalid add error style for input
     * @param category
     */
    validateCustomRangeLabels : function(category) {
        _.each(this.model.get(category + '_options'), function(item, key){
            if ( _.isEmpty(item[1]) ) {
                this.view.fieldRanges[category][item[0]].$el.find('.control-group').addClass('error');
                this.view.layout.$el.find('[name=save_button]').addClass('disabled');
            } else {
                this.view.fieldRanges[category][item[0]].$el.find('.control-group').removeClass('error')
            }
        }, {view: this});
    },

    /**
     * updates the setting in the model for the specific range types.
     * This gets triggered when the range after the user changes a range slider
     * @param category - the selected category: `show_buckets` or `show_binary`
     * @param range - the range being set, i. e. `include`, `exclude` or `upside` for `show_buckets` category
     * @param value - the value being set
     */
    updateRangeSettings: function(category, range, value) {
        var catRange = category + '_ranges',
            setting = this.model.get(catRange);
        setting[range] = value;
        this.model.unset(catRange, {silent: true});
        this.model.set(catRange, setting);
    },

    /**
     * Graphically connects the sliders to the one below, so that they move in unison when changed, based on category.
     * @param ranges - the forecasts category that was selected, i. e. 'show_binary' or 'show_buckets'
     * @param sliders - an object containing the sliders that have been set up in the page.  This is created in the
     * selection handler when the user selects a category type.
     */
    connectSliders: function(ranges, sliders) {
        var rangeSliders = sliders[ranges];

        if(ranges == 'show_binary') {
            rangeSliders.include.sliderChangeDelegate = function (value) {
                // lock the upper handle to 100, as per UI/UX requirements to show a dual slider
                rangeSliders.include.$el.find(rangeSliders.include.fieldTag).noUiSlider('move', {handle: 'upper', to: rangeSliders.include.def.maxRange});
                // set the excluded range based on the lower value of the include range
                this.view.setExcludeValueForLastSlider(value, ranges, rangeSliders.include);
            };
        } else if (ranges == 'show_buckets') {
            rangeSliders.include.sliderChangeDelegate = function (value) {
                // lock the upper handle to 100, as per UI/UX requirements to show a dual slider
                rangeSliders.include.$el.find(rangeSliders.include.fieldTag).noUiSlider('move', {handle: 'upper', to: rangeSliders.include.def.maxRange});

                rangeSliders.upside.$el.find(rangeSliders.upside.fieldTag).noUiSlider('move', {handle: 'upper', to: value.min-1});
                if(value.min <= rangeSliders.upside.$el.find(rangeSliders.upside.fieldTag).noUiSlider('value')[0] + 1) {
                    rangeSliders.upside.$el.find(rangeSliders.upside.fieldTag).noUiSlider('move', {handle: 'lower', to: value.min-2});
                }
            };
            rangeSliders.upside.sliderChangeDelegate = function (value) {
                rangeSliders.include.$el.find(rangeSliders.include.fieldTag).noUiSlider('move', {handle: 'lower', to: value.max+1});
                // set the excluded range based on the lower value of the upside range
                this.view.setExcludeValueForLastSlider(value, ranges, rangeSliders.upside);
            };
        } else if (ranges == 'show_custom_buckets') {
            var i, max,
                customSliders = _.sortBy(_.filter(
                    rangeSliders,
                    function(item) { return item.customType == 'custom' }
                ), function(item) { return parseInt(item.customIndex, 10); }
                ),
                probabilitySliders = _.union(rangeSliders.include, rangeSliders.upside, customSliders, rangeSliders.exclude);

            if ( probabilitySliders.length ) {
                for ( i = 0, max = probabilitySliders.length; i < max; i++ ) {
                    probabilitySliders[i].connectedSlider = ( !_.isUndefined(probabilitySliders[i+1]) ) ? probabilitySliders[i+1] : null;
                    probabilitySliders[i].connectedToSlider = ( !_.isUndefined(probabilitySliders[i-1]) ) ? probabilitySliders[i-1] : null;
                    probabilitySliders[i].sliderChangeDelegate = function (value, populateEvent) {
                        // lock the upper handle to 100, as per UI/UX requirements to show a dual slider
                        if ( this.name == 'include' ) {
                            this.$el.find(this.fieldTag).noUiSlider('move', {handle: 'upper', to: this.def.maxRange});
                        } else if ( this.name == 'exclude' ) {
                            this.$el.find(this.fieldTag).noUiSlider('move', {handle: 'lower', to: this.def.minRange});
                        }

                        if (!_.isUndefined(this.connectedSlider) && !_.isNull(this.connectedSlider) ) {
                            this.connectedSlider.$el.find(this.connectedSlider.fieldTag).noUiSlider('move', {handle: 'upper', to: value.min-1});
                            if(value.min <= this.connectedSlider.$el.find(this.connectedSlider.fieldTag).noUiSlider('value')[0] + 1) {
                                this.connectedSlider.$el.find(this.connectedSlider.fieldTag).noUiSlider('move', {handle: 'lower', to: value.min-2});
                            }
                            if ( _.isUndefined(populateEvent) || populateEvent == 'down' ) {
                                this.connectedSlider.sliderChangeDelegate.call(this.connectedSlider, {
                                    min:this.connectedSlider.$el.find(this.connectedSlider.fieldTag).noUiSlider('value')[0],
                                    max:this.connectedSlider.$el.find(this.connectedSlider.fieldTag).noUiSlider('value')[1]
                                }, 'down');
                            }
                        }
                        if (!_.isUndefined(this.connectedToSlider) && !_.isNull(this.connectedToSlider) ) {
                            this.connectedToSlider.$el.find(this.connectedToSlider.fieldTag).noUiSlider('move', {handle: 'lower', to: value.max+1});
                            if(value.max >= this.connectedToSlider.$el.find(this.connectedToSlider.fieldTag).noUiSlider('value')[1] - 1) {
                                this.connectedToSlider.$el.find(this.connectedToSlider.fieldTag).noUiSlider('move', {handle: 'upper', to: value.max+2});
                            }
                            if ( _.isUndefined(populateEvent) || populateEvent == 'up' ) {
                                this.connectedToSlider.sliderChangeDelegate.call(this.connectedToSlider, {
                                    min:this.connectedToSlider.$el.find(this.connectedToSlider.fieldTag).noUiSlider('value')[0],
                                    max:this.connectedToSlider.$el.find(this.connectedToSlider.fieldTag).noUiSlider('value')[1]
                                }, 'up');
                            }
                        }
                    };
                }
            }
        }
    },

    /**
     * Provides a way for the last of the slider fields in the view, to set the value for the exclude range.
     * @param value the range value of the slider
     * @param ranges the selected config range
     * @param slider the slider
     */
    setExcludeValueForLastSlider: function(value, ranges, slider) {
        var excludeRange = {
            min: 0,
            max: 100
        },
        settingName = ranges + '_ranges',
        setting = this.model.get(settingName);

        excludeRange.max = value.min - 1;
        excludeRange.min = slider.def.minRange;
        setting.exclude = excludeRange;
        this.model.set(settingName, setting);
    }
})
