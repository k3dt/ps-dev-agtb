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
 * Relate field provides a link to a module that is set in the definition of
 * this field metadata.
 *
 * This field requires at least the follow definitions to be exist in the
 * field:
 *
 * ```
 * array(
 *     'name' => 'account_name',
 *     'rname' => 'name',
 *     'id_name' => 'account_id',
 *     'module' => 'Accounts',
 *     'link' => true,
 *     //...
 * ),
 * ```
 *
 * The field also support a `populate_list` to update other fields in the
 * current model from other fields of the selected model.
 *
 * ```
 * array(
 *     //...
 *     'populate_list' => array(
 *         'populate_list' => array(
 *         'billing_address_street' => 'primary_address_street',
 *         'billing_address_city' => 'primary_address_city',
 *         'billing_address_state' => 'primary_address_state',
 *         'billing_address_postalcode' => 'primary_address_postalcode',
 *         'billing_address_country' => 'primary_address_country',
 *         'phone_office' => 'phone_work',
 *         //...
 *
 *     ),
 * )
 * ```
 *
 * This field allows you to configure the minimum chars that trigger a search
 * when using the typeahead feature.
 *
 * ```
 * array(
 *     //...
 *     'minChars' => 3,
 * )
 * ```
 *
 * TODO: there is a conflict in the link property of `this.def.link` that
 * should be populated from the view/field metadata with the `vardefs` one
 * which needs to be addressed.
 *
 * TODO: we have a mix of properties here with camelCase and underscore.
 * Needs to be addressed.
 *
 * @class View.Fields.Base.RelateField
 * @alias SUGAR.App.view.fields.BaseRelateField
 * @extends View.Field
 */
({
    fieldTag: 'input.select2',
    plugins: ['QuickSearchFilter', 'EllipsisInline'],

    /**
     * Initializes field and binds all function calls to this
     * @param {Object} options
     */
    initialize: function(options) {
        /**
         * Boolean used for the 'allowClear' select2 option.
         *
         * @property {boolean}
         */
        this.allow_single_deselect = true;
        /**
         * Minimum input characters to trigger the search. Used for
         * `minimumInputLength` select2 option.
         *
         * @property {number}
         */
        this.minChars = options.def.minChars || 1;
        /**
         * Separator used by select2 to separate values. Used for `separator`
         * select2 option.
         *
         * @property {string}
         */
        this.separator = '|';
        /**
         * Maximum number of records the user can select.
         *
         * @property {number}
         */
        this.maxSelectedRecords = 20;
        /**
         * Maximum number of items we display in the field.
         *
         * @property {number}
         */
        app.view.Field.prototype.initialize.call(this, options);
        /**
         * The template used for a pill in case of multiselect field.
         *
         * @property {Function}
         * @private
         */
        this._select2formatSelectionTemplate = app.template.get("f.relate.pill");

        var populateMetadata = app.metadata.getModule(this.getSearchModule());

        if (_.isEmpty(populateMetadata)) {
            return;
        }
        _.each(this.def.populate_list, function(target, source) {
            if (_.isUndefined(populateMetadata.fields[source])) {
                app.logger.error('Fail to populate the related attributes: attempt to access undefined key - ' +
                this.getSearchModule() + '::' + source);
            }
        }, this);

        this.model.on('change', function() {
            this.getFilterOptions(true);
        }, this);

        this._createFiltersCollection();
        this._createSearchCollection();
    },

    /**
     * Creates a Filters BeanCollection to easily apply filters.
     * The user must have `list` access to the search module to create a
     * {@link Data.Base.FiltersBeanCollection}.
     *
     * @protected
     */
    _createFiltersCollection: function() {
        var searchModule = this.getSearchModule();

        if (!app.acl.hasAccess('list', searchModule)) {
            app.logger.debug('No "list" access to ' + searchModule + ' so skipping the creation of filter.');
            return;
        }

        if (app.metadata.getModule('Filters') && searchModule) {
            this.filters = app.data.createBeanCollection('Filters');
            this.filters.setModuleName(searchModule);
            this.filters.setFilterOptions(this.getFilterOptions());
            this.filters.load();
        }
    },
    /**
     * Creates a {@link Data.BeanCollection} for the search results pertaining
     * to the search module.
     *
     * @protected
     */
    _createSearchCollection: function() {
        var searchModule = this.getSearchModule();
        if (searchModule && app.metadata.getModule(searchModule)) {
            this.searchCollection = app.data.createBeanCollection(searchModule);
        } else {
            this.searchCollection = null;
        }
    },

    /**
     * Bind the additional keydown handler on select2
     * search element (affected by version 3.4.3).
     *
     * Invoked from {@link app.plugins.Editable}.
     * @param {Function} callback Callback function for keydown.
     */
    bindKeyDown: function(callback) {
        this.$(this.fieldTag).on('keydown.record', {field: this}, callback);
        var plugin = this.$(this.fieldTag).data('select2');
        if (plugin) {
            plugin.focusser.on('keydown.record', {field: this}, callback);
            plugin.search.on('keydown.record', {field: this}, callback);
        }
    },

    /**
     * Unbind the additional keydown handler.
     *
     * Invoked from {@link app.plugins.Editable}.
     * @param {Function} callback Callback function for keydown.
     */
    unbindKeyDown: function(callback) {
        this.$(this.fieldTag).off('keydown.record', callback);
        var plugin = this.$(this.fieldTag).data('select2');
        if (plugin) {
            plugin.search.off('keydown.record', callback);
        }
    },

    focus: function () {
        var self = this;
        if(this.action !== 'disabled') {
            //Need to defer to ensure that all the related elements have finished
            //rendering before attempting to open the dropdown.
            _.defer(function(){self.$(self.fieldTag).select2('open')});
        }
    },

    /**
     * Creates the css classes to set to the select2 plugin.
     *
     * @return {string}
     * @private
     */
    _buildCssClasses: function() {
        var cssClasses = [];
        if (this.view.name === 'recordlist') {
            cssClasses.push('select2-narrow');
        }
        if (this.type === 'parent') {
            cssClasses.push('select2-parent');
        }
        if (this.def.isMultiSelect) {
            cssClasses.push('select2-choices-pills-close same-size-pills');
        }
        return cssClasses.join(' ');
    },

    /**
     * Renders relate field
     */
    _render: function() {
        var self = this;
        var searchModule = this.getSearchModule();
        var loadingLabel = app.lang.get('LBL_LOADING', self.module);

        //Do not render if the related module is invalid
        if (searchModule && !_.contains(app.metadata.getModuleNames(), searchModule)) {
            return this;
        }

        var result = this._super('_render');

        //FIXME remove check for tplName SC-2608
        if (this.tplName === 'edit' || this.tplName === 'massupdate') {

            var inList = this.view.name === 'recordlist';
            this.$(this.fieldTag).select2({
                width: inList ? 'off' : '100%',
                dropdownCssClass: _.bind(this._buildCssClasses, this),
                multiple: !!this.def.isMultiSelect,
                containerCssClass: _.bind(this._buildCssClasses, this),
                separator: self.separator,
                initSelection: function(el, callback) {
                    var $el = $(el),
                        id = $el.val(),
                        text = $el.data('rname');

                    if (!self.def.isMultiSelect) {
                        return callback({id: id, text: text});
                    }
                    var ids = id.split(self.separator);
                    text = text.split(self.separator);
                    callback(_.map(ids, function(value, index) {
                        return {id: value, text: text[index]};
                    }));
                },
                formatInputTooShort: function() {
                    return '';
                },
                formatSelection: function(obj) {
                    var ctx = {};
                    //TODO We should investigate why it's sometimes `text` and
                    //sometimes `id` and make it always same if possible.
                    ctx.text = obj.text || obj.id;
                    return self._select2formatSelectionTemplate(ctx);
                },
                formatSearching: loadingLabel,
                placeholder: this.getPlaceHolder(),
                allowClear: self.allow_single_deselect,
                minimumInputLength: self.minChars,
                maximumSelectionSize: 20,
                query: _.bind(this.search, this)
            }).on('select2-open', function() {
                var plugin = $(this).data('select2');
                if (!plugin.searchmore) {
                    var $content = $('<li class="select2-result">').append(
                            $('<div/>').addClass('select2-result-label')
                                .html(app.lang.get('LBL_SEARCH_AND_SELECT_ELLIPSIS', self.module))
                        ).mousedown(function() {
                            plugin.opts.element.trigger($.Event('searchmore'));
                            plugin.close();
                        });
                    plugin.searchmore = $('<ul class="select2-results">').append($content);
                    plugin.dropdown.append(plugin.searchmore);
                }
            }).on('searchmore', function() {
                $(this).select2('close');
                self.openSelectDrawer();
            }).on('change', function(e) {
                var plugin = $(this).data('select2'),
                    id = e.val;

                if (_.isUndefined(id)) {
                    return;
                }

                // For multiselect fields, we update the data-rname attributes
                // so it stays in sync with the id list, and allows us to use
                // 'setValue' method. The use of 'setValue' method is required
                // to re-render the field.
                if (self.def.isMultiSelect) {
                    var dataRname = plugin.opts.element.data('rname');
                    dataRname = dataRname ? dataRname.split(self.separator) : [];
                    var ids = $(this).select2('val');

                    if (e.added) {
                        dataRname.push(e.added.text);
                    } else if (e.removed) {
                        dataRname = _.without(dataRname, e.removed.text);
                    } else {
                        return;
                    }
                    plugin.opts.element.data('rname', dataRname.join(self.separator));
                    var models = _.map(ids, function(id, index) {
                        return {id: id, value: dataRname[index]};
                    });

                    self.setValue(models);
                    return;
                }

                var value = (id) ? plugin.selection.find("span").text() : $(this).data('rname'),
                    collection = plugin.context,
                    attributes = {};
                //Update the source element or else reverting back to the original value will not trigger a change event.
                plugin.opts.element.data('rname', id);
                if (collection && !_.isEmpty(id)) {
                    // if we have search results use that to set new values
                    var model = collection.get(id);
                    attributes.id = model.id;
                    attributes.value = model.get('name');
                    _.each(model.attributes, function (value, field) {
                        if (app.acl.hasAccessToModel('view', model, field)) {
                            attributes[field] = attributes[field] || model.get(field);
                        }
                    });
                } else if (e.currentTarget.value && value) {
                    // if we have previous values keep them
                    attributes.id = value;
                    attributes.value = e.currentTarget.value;
                } else {
                    // default to empty
                    attributes.id = '';
                    attributes.value = '';
                }

                self.setValue(attributes);
            });
            var plugin = this.$(this.fieldTag).data('select2');
            if (plugin && plugin.focusser) {
                plugin.focusser.on('select2-focus', _.bind(_.debounce(this.handleFocus, 0), this));
            }
        } else if (this.tplName === 'disabled') {
            this.$(this.fieldTag).select2({
                width: '100%',
                initSelection: function(el, callback) {
                    var $el = $(el),
                        id = $el.val(),
                        text = $el.data('rname');
                    callback({id: id, text: text});
                },
                formatInputTooShort: function() {
                    return '';
                },
                formatSearching: function() {
                    return app.lang.get('LBL_LOADING', self.module);
                },
                placeholder: this.getPlaceHolder(),
                allowClear: self.allow_single_deselect,
                minimumInputLength: self.minChars,
                query: _.bind(this.search, this)
            });
            this.$(this.fieldTag).select2('disable');
        }
        return result;
    },

    /**
     * Builds the route for the relate module's record.
     * @param {String} module The related module.
     * @param {String} id The record id to link to.
     *
     * TODO since base.js has a build href, we should try to reuse code or
     * extend this one from other "link" field
     */
    buildRoute: function (module, id) {
        var oldModule = module;
        // This is a workaround until bug 61478 is resolved to keep parity with 6.7
        if (module === 'Users' && this.context.get('module') !== 'Users') {
            module = 'Employees';
        }

        if (_.isEmpty(module) || (!_.isUndefined(this.def.link) && !this.def.link)) {
            return;
        }
        var action = (this.def.link && this.def.route)? this.def.route.action :"view";
        if(app.acl.hasAccess(action, oldModule)) {
            this.href = '#' + app.router.buildRoute(module, id);
        }
    },
    //Derived controllers can override these if related module and id in another place
    _buildRoute: function () {
        this.buildRoute(this.getSearchModule(), this._getRelateId());
    },
    _getRelateId: function () {
        return this.model.get(this.def.id_name);
    },

    /**
     * {@inheritDoc}
     *
     * When there is no value set and we are in a create view, we try to check
     * if the parent context module matches this relate field. If it matches,
     * we pre-populate with that data.
     *
     * FIXME: the relate field should use this method to pre-populate the
     * values without touching the model or else we need to use silent to
     * prevent the warning of unsaved changes, consequently we can't bind
     * events like `change` to it.
     *
     * TODO: the model might not have the field that we are relating to. On
     * those corner cases, we need to fetch from the server that information.
     *
     * @return {String} This field's value. Need to change to object with all
     *   data that we need to render the field.
     */
    format: function(value) {

        var parentCtx = this.context && this.context.parent,
            setFromCtx;

        if (value) {
            /**
             * Flag to indicate that the value has been set from the context
             * once, so if later the value is unset, we don't set it again on
             * {@link #format}.
             *
             * @type {boolean}
             * @protected
             */
            this._valueSetOnce = true;
        }
        setFromCtx = value === null && !this._valueSetOnce && parentCtx &&
            this.view instanceof app.view.views.BaseCreateView &&
            parentCtx.get('module') === this.def.module &&
            this.module !== this.def.module;

        if (setFromCtx) {
            this._valueSetOnce = true;
            var model = parentCtx.get('model');
            // FIXME we need a method to prevent us from doing this
            this.def.auto_populate = true;
            // FIXME the setValue receives a model but not a backbone model...
            this.setValue(model.toJSON());
            // FIXME we need to iterate over the populated_ that is causing
            // unsaved warnings when doing the auto populate.
        }
        if (!this.def.isMultiSelect) {
            this._buildRoute();
        }

        if (_.isArray(value)) {
            this.formattedRname = value.join(this.separator);
            this.formattedIds = this.model.get(this.def.id_name).join(this.separator);
        } else {
            this.formattedRname = value;
            this.formattedIds = this.model.get(this.def.id_name);
        }
        return value;
    },

    /**
     * Sets the value in the field.
     *
     * @param {Array} models The source models attributes.
     */
    setValue: function(models) {

        if (!models) {
            return;
        }
        var updateRelatedFields = true,
            values = {};
        if (_.isArray(models)) {
            // Does not make sense to update related fields if we selected
            // multiple models
            updateRelatedFields = false;
        } else {
            models = [models];
        }

        values[this.def.id_name] = [];
        values[this.def.name] = [];

        _.each(models, _.bind(function(model) {
            values[this.def.id_name].push(model.id);
            values[this.def.name].push(model[this.getRelatedModuleField()] || model.value);
        }, this));

        // If there is only one value, we get rid of the array before setting
        // the value.
        if (values[this.def.id_name].length === 1) {
            values[this.def.id_name] = values[this.def.id_name][0];
            values[this.def.name] = values[this.def.name][0];
        }
        this.model.set(values);

        if (updateRelatedFields) {
            // TODO: move this to SidecarExpressionContext
            // check if link field is currently populated
            if (this.model.get(this.def.link)) {
                // unset values of related bean fields in order to make the model load
                // the values corresponding to the currently selected bean
                this.model.unset(this.def.link);
            } else {
                // unsetting what is not set won't trigger "change" event,
                // we need to trigger it manually in order to notify subscribers
                // that another related bean has been chosen.
                // the actual data will then come asynchronously
                this.model.trigger('change:' + this.def.link);
            }
            this.updateRelatedFields(models[0]);
        }
    },

    /**
     * Handles update of related fields.
     *
     * @param {Object} model The source model attributes.
     */
    updateRelatedFields: function(model) {
        var newData = {},
            self = this;
        _.each(this.def.populate_list, function(target, source) {
            source = _.isNumber(source) ? target : source;
            if (!_.isUndefined(model[source]) && app.acl.hasAccessToModel('edit', this.model, target)) {
                var before = this.model.get(target),
                    after = model[source];

                if (before !== after) {
                    newData[target] = model[source];
                }
            }
        }, this);

        if (_.isEmpty(newData)) {
            return;
        }

        // if this.def.auto_populate is true set new data and doesn't show alert message
        if (!_.isUndefined(this.def.auto_populate) && this.def.auto_populate == true) {
            // if we have a currency_id, set it first to trigger the currency conversion before setting
            // the values to the model, this prevents double conversion from happening
            if (!_.isUndefined(newData.currency_id)) {
                this.model.set({currency_id: newData.currency_id});
                delete newData.currency_id;
            }
            this.model.set(newData);
            return;
        }

        // load template key for confirmation message from defs or use default
        var messageTplKey = this.def.populate_confirm_label || 'TPL_OVERWRITE_POPULATED_DATA_CONFIRM',
            messageTpl = Handlebars.compile(app.lang.get(messageTplKey, this.getSearchModule())),
            fieldMessageTpl = app.template.getField(
                this.type,
                'overwrite-confirmation',
                this.model.module),
            messages = [],
            relatedModuleSingular = app.lang.getModuleName(this.def.module);

        _.each(newData, function(value, field) {
            var before = this.model.get(field),
                after = value;

            if (before !== after) {
                var def = this.model.fields[field];
                messages.push(fieldMessageTpl({
                    before: before,
                    after: after,
                    field_label: app.lang.get(def.label || def.vname || field, this.module)
                }));
            }
        }, this);

        app.alert.show('overwrite_confirmation', {
            level: 'confirmation',
            messages: messageTpl({
                values: new Handlebars.SafeString(messages.join(', ')),
                moduleSingularLower: relatedModuleSingular.toLowerCase()
            }),
            onConfirm: function() {
                // if we have a currency_id, set it first to trigger the currency conversion before setting
                // the values to the model, this prevents double conversion from happening
                if (!_.isUndefined(newData.currency_id)) {
                    self.model.set({currency_id: newData.currency_id});
                    delete newData.currency_id;
                }
                self.model.set(newData);
            }
        });

    },

    /**
     * Opens the selection drawer.
     *
     * Note that if the field definitions have a `filter_relate` property, it
     * will open the drawer and filter by this relate field.
     *
     *     @example a Revenue Line Item is associated to an account and to an
     *      opportunity. If I want to open a drawer to select an opportunity
     *      with an initial filter that filters opportunities by the account
     *      associated to the revenue line item, in the field definitions I can
     *      specify:
     *      ```
     *      'filter_relate' => array(
     *          'account_id' => 'account_id',
     *      ),
     *      ```
     *      The key is the field name in the Revenue Line Items record,
     *      the value is the field name in the Opportunities record.
     */
    openSelectDrawer: function() {
        var layout, context;
        if (!!this.def.isMultiSelect) {
            layout = 'multi-selection-list';
            context = {
                module: this.getSearchModule(),
                fields: this.getSearchFields(),
                filterOptions: this.getFilterOptions(),
                preselectedModelIds: _.clone(this.model.get(this.def.id_name)),
                maxSelectedRecords: this.maxSelectedRecords,
                isMultiSelect: true,
                independentMassCollection: true
            };
        } else {
            layout = 'selection-list';
            context = {
                module: this.getSearchModule(),
                fields: this.getSearchFields(),
                filterOptions: this.getFilterOptions()
            };
        }
        app.drawer.open({
            layout: layout,
            context: context
        }, _.bind(this.setValue, this));
    },

    /**
     * Gets the list of fields to search by in the related module.
     *
     * @return {Array} The list of fields.
     */
    getSearchFields: function() {
        return _.union(['id', this.getRelatedModuleField()], _.keys(this.def.populate_list || {}));
    },

    /**
     * Gets the related field name in the related module record.
     *
     * Falls back to `name` if not defined.
     *
     * @return {String} The field name.
     */
    getRelatedModuleField: function() {
        return this.def.rname || 'name';
    },

    /**
     * {@inheritdoc}
     *
     * We need this empty so it won't affect refresh the select2 plugin
     */
    bindDomChange: function () {
    },

    /**
     * Gets the correct module to search based on field/link defs.
     *
     * If both `this.def.module` and `link.module` are empty, fall back onto the
     * metadata manager to get the proper module as a last resort.
     *
     * @return {String} The module to search on.
     */
    getSearchModule: function () {
        // If we have a module property on this field, use it
        if (this.def.module) {
            return this.def.module;
        }

        // No module in the field def, so check if there is a module in the def
        // for the link field
        var link = this.def.link && this.model.fields && this.model.fields[this.def.link] || {};
        if (link.module) {
            return link.module;
        }

        // At this point neither the def nor link field def have a module... let
        // metadata manager try find it
        return app.data.getRelatedModule(this.model.module, this.def.link);
    },
    getPlaceHolder: function () {
        var searchModule = this.getSearchModule(),
            searchModuleLower = searchModule.toLocaleLowerCase(),
            module = app.lang.getModuleName(searchModule, {defaultValue: searchModuleLower});

        return app.lang.get('LBL_SEARCH_SELECT_MODULE', this.module, {
            module: new Handlebars.SafeString(module)
        });
    },

    /**
     * Formats the filter options.
     *
     * @param {Boolean} force `true` to force retrieving the filter options
     *   whether or not it is available in memory.
     * @return {Object} The filter options.
     */
    getFilterOptions: function(force) {
        if (this._filterOptions && !force) {
            return this._filterOptions;
        }
        this._filterOptions = new app.utils.FilterOptions()
            .config(this.def)
            .setInitialFilter(this.def.initial_filter || '$relate')
            .populateRelate(this.model)
            .format();
        return this._filterOptions;
    },

    /**
     * Builds the filter definition to pass to the request when doing a quick
     * search.
     *
     * It will combine the filter definition for the search term with the
     * initial filter definition. Both are optional, so this method may return
     * an empty filter definition (empty `array`).
     *
     * @param {String} searchTerm The term typed in the quick search field.
     * @return {Array} The filter definition.
     */
    buildFilterDefinition: function(searchTerm) {
        if (!app.metadata.getModule('Filters') || !this.filters) {
            return [];
        }
        var filterBeanClass = app.data.getBeanClass('Filters').prototype,
            filterOptions = this.getFilterOptions() || {},
            filter = this.filters.collection.get(filterOptions.initial_filter),
            filterDef,
            populate,
            searchTermFilter,
            searchModule;

        if (filter) {
            populate = filter.get('is_template') && filterOptions.filter_populate;
            filterDef = filterBeanClass.populateFilterDefinition(filter.get('filter_definition') || {}, populate);
            searchModule = filter.moduleName;
        }

        searchTermFilter = filterBeanClass.buildSearchTermFilter(searchModule || this.getSearchModule(), searchTerm);

        return filterBeanClass.combineFilterDefinitions(filterDef, searchTermFilter);
    },

    /**
     * Searches for related field.
     * @param event
     */
    search: _.debounce(function(query) {
        var term = query.term || '',
            self = this,
            searchModule = this.getSearchModule(),
            params = {},
            limit = self.def.limit || 5,
            relatedModuleField = this.getRelatedModuleField();

        if (query.context) {
            params.offset = this.searchCollection.next_offset;
        }
        params.filter = this.buildFilterDefinition(term);

        this.searchCollection.fetch({
            //Don't show alerts for this request
            showAlerts: false,
            update: true,
            remove: _.isUndefined(params.offset),
            fields: this.getSearchFields(),
            context: self,
            params: params,
            limit: limit,
            success: function(data) {
                var fetch = {results: [], more: data.next_offset > 0, context: data};
                if (fetch.more) {
                    var fieldEl = self.$(self.fieldTag),
                    //For teamset widget, we should specify which index element to be filled in
                        plugin = (fieldEl.length > 1) ? $(fieldEl.get(self.currentIndex)).data("select2") : fieldEl.data("select2"),
                        height = plugin.searchmore.children("li:first").children(":first").outerHeight(),
                    //0.2 makes scroll not to touch the bottom line which avoid fetching next record set
                        maxHeight = height * (limit - .2);
                    plugin.results.css("max-height", maxHeight);
                }
                _.each(data.models, function (model, index) {
                    if (params.offset && index < params.offset) {
                        return;
                    }
                    fetch.results.push({
                        id: model.id,
                        text: model.get(relatedModuleField) + ''
                    });
                });
                if (query.callback && _.isFunction(query.callback)) {
                    query.callback(fetch);
                }
            },
            error: function() {
                if (query.callback && _.isFunction(query.callback)) {
                    query.callback({results: []});
                }
                app.logger.error("Unable to fetch the bean collection.");
            }
        });
    }, app.config.requiredElapsed || 500),

    /**
     * {@inheritDoc}
     * Avoid rendering process on select2 change in order to keep focus.
     */
    bindDataChange: function() {
        if (this.model) {
            this.model.on('change:' + this.name, function() {
                if (!_.isEmpty(this.$(this.fieldTag).data('select2'))) {
                    // Just setting the value on select2 doesn't cause the label to show up
                    // so we need to render the field next after setting this value
                    this.$(this.fieldTag).select2('val', this.model.get(this.name));
                }
                // double-check field isn't disposed before trying to render
                if (!this.disposed) {
                    this.render();
                }
            }, this);
        }
    },

    unbindDom: function() {
        this.$(this.fieldTag).select2('destroy');
        app.view.Field.prototype.unbindDom.call(this);
    }

})
