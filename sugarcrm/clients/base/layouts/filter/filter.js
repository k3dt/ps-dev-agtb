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
    /**
     * Layout for filtering a collection.
     * Composed of a module dropdown(optional), a filter dropdown and an input
     *
     * @class BaseFilterLayout
     * @extends Layout
     */
    className: 'filter-view search',

    plugins: ['QuickSearchFilter'],

    events: {
        'click .add-on.icon-remove': function() { this.trigger('filter:clear:quicksearch'); }
    },

    /**
     * @override
     * @param {Object} opts
     */
    initialize: function(opts) {
        var filterLayout = app.view._getController({type:'layout',name:'filter'});
        filterLayout.loadedModules = filterLayout.loadedModules || {};
        app.view.Layout.prototype.initialize.call(this, opts);

        this.layoutType = app.utils.deepCopy(this.options.meta.layoutType) || this.context.get('layout') || this.context.get('layoutName') || app.controller.context.get('layout');

        this.aclToCheck = (this.layoutType === 'record')? 'view' : 'list';
        this.filters = app.data.createBeanCollection('Filters');
        this.filters.comparator = _.bind(this.filterCollectionSorting, this);

        this.emptyFilter = app.data.createBean('Filters', {
            id: 'all_records',
            name: '',
            filter_definition: [],
            editable: false
        });

        // Can't use getRelevantContextList here, because the context may not
        // have all the children we need.
        if (this.layoutType === 'records') {
            // filters will handle data fetching so we skip the standard data fetch
            this.context.set('skipFetch', true);
        } else {
            if(this.context.parent) {
                this.context.parent.set('skipFetch', true);
            }
            this.context.on('context:child:add', function(childCtx) {
                if (childCtx.get('link')) {
                    // We're in a subpanel.
                    childCtx.set('skipFetch', true);
                }
            }, this);
        }

        /**
         * bind events
         */
        this.on('filter:apply', this.applyFilter, this);

        this.on('filter:create:close', function() {
            this.clearFilterEditState();
            // When canceling creating a new filter, we want to go back to the `all_records` filter
            if (this.getLastFilter(this.layout.currentModule, this.layoutType) === 'create') {
                // For that we need to remove the last state key and trigger reinitialize
                this.clearLastFilter(this.layout.currentModule, this.layoutType);
                this.layout.trigger("filter:reinitialize");
            }
            this.context.editingFilter = null;
            this.layout.trigger('filter:create:close');
        }, this);

        this.on('filter:create:open', function(filterModel) {
            this.context.editingFilter = filterModel;
            this.layout.trigger('filter:create:open', filterModel);
        }, this);

        this.on('subpanel:change', function(linkName) {
            this.layout.trigger('subpanel:change', linkName);
        }, this);

        this.on('filter:get', this.initializeFilterState, this);

        this.on('filter:change:filter', this.handleFilterChange, this);

        this.layout.on('filter:apply', function(query, def) {
            this.trigger('filter:apply', query, def);
        }, this);

        this.layout.on('filterpanel:change', this.handleFilterPanelChange, this);
        this.layout.on('filterpanel:toggle:button', this.toggleFilterButton, this);

        //When a filter is saved, update the cache and set the filter to be the currently used filter
        this.context.on('filter:add', this.addFilter, this);

        // When a filter is deleted, update the cache and set the default filter
        // to be the currently used filter.
        this.layout.on('filter:remove', this.removeFilter, this);

        this.layout.on('filter:reinitialize', function() {
            var currentFilter = this.context.get('currentFilterId');
            this.initializeFilterState(this.layout.currentModule, this.layout.currentLink, currentFilter);
        }, this);

        this.listenTo(app.events, 'dashlet:filter:save', this.refreshDropdown);
    },

    /**
     * This function refreshes the list of filters in the filter dropdown, and
     * is invoked when a filter is saved on a dashlet (`dashlet:filter:save`).
     * It triggers a `filter:reinitialize` event and resets the cached
     * module in `loadedModules` on the filter layout if the dashlet module
     * matches the `currentModule` on the filter layout.
     *
     * @param {String} module
     */
    refreshDropdown: function(module) {
        if (module === this.layout.currentModule) {
            var filterLayout = app.view._getController({type:'layout', name:'filter'});
            filterLayout.loadedModules[module] = false;
            this.layout.trigger('filter:reinitialize');
        }
    },

    /**
     * handles filter removal
     * @param model
     */
    removeFilter: function(model) {
        this.filters.remove(model);
        this.context.set('currentFilterId', null);
        this.clearFilterEditState();
        this.clearLastFilter(this.layout.currentModule, this.layoutType);
        app.user.lastState.set(app.user.lastState.key('saved-' + this.layout.currentModule, this), this.filters.toJSON());
        this.layout.trigger('filter:reinitialize');
    },
    /**
     * saves last filter to app cache
     * @param {String} baseModule
     * @param {String} filterModule
     * @param {String} layoutName
     * @param {*} value
     * @returns {*}
     */
    setLastFilter: function(filterModule, layoutName, value) {
        var filterOptions = this.context.get('filterOptions') || {};
        if (filterOptions.stickiness !== false) {
            var key = app.user.lastState.key('last-' + filterModule + '-' + layoutName, this);
            return app.user.lastState.set(key, value);
        }
    },
    /**
     * gets last filter from cache
     * @param {String} baseModule
     * @param {String} filterModule
     * @param {String} layoutName
     * @returns {*}
     */
    getLastFilter: function(filterModule, layoutName) {
        var filterOptions = this.context.get('filterOptions') || {};
        if (filterOptions.stickiness !== false) {
            var key = app.user.lastState.key('last-' + filterModule + '-' + layoutName, this),
            value = app.user.lastState.get(key);
            this.context.set('currentFilterId', value);
            return value;
        }
    },
    /**
     * clears last filter from cache
     * @param {String} baseModule
     * @param {String} filterModule
     * @param {String} layoutName
     * @returns {*}
     */
    clearLastFilter:function(filterModule, layoutName) {
        var filterOptions = this.context.get('filterOptions') || {};
        if (filterOptions.stickiness !== false) {
            var key = app.user.lastState.key('last-' + filterModule + '-' + layoutName, this);
            return app.user.lastState.remove(key);
        }
    },

    /**
     * Saves the current edit state into the cache
     *
     * @param {Object} filter
     */
    retrieveFilterEditState: function() {
        var filterOptions = this.context.get('filterOptions') || {};
        if (filterOptions.stickiness !== false) {
            var key = app.user.lastState.key('edit-' + this.layout.currentModule + '-' + this.layoutType, this);
            return app.user.lastState.get(key);
        }
    },

    /**
     * Saves the current edit state into the cache
     *
     * @param {Object} filter
     */
    saveFilterEditState: function(filter) {
        var filterOptions = this.context.get('filterOptions') || {};
        if (filterOptions.stickiness !== false) {
            var key = app.user.lastState.key('edit-' + this.layout.currentModule + '-' + this.layoutType, this);
            app.user.lastState.set(key, filter);
        }
    },

    /**
     * Removes the edit state from the cache
     */
    clearFilterEditState: function() {
        var filterOptions = this.context.get('filterOptions') || {};
        if (filterOptions.stickiness !== false) {
            var key = app.user.lastState.key('edit-' + this.layout.currentModule + '-' + this.layoutType, this);
            app.user.lastState.remove(key);
        }
    },

    /**
     * handles filter additionF
     * @param model
     */
    addFilter: function(model){
        var id = model.get('id');
        this.filters.add(model, { merge: true });
        app.user.lastState.set(app.user.lastState.key('saved-' + this.layout.currentModule, this), this.filters.toJSON());
        this.setLastFilter(this.layout.currentModule, this.layoutType, id);
        this.context.set('currentFilterId', id);
        this.clearFilterEditState();
        this.layout.trigger('filter:reinitialize');
    },

    /**
     * Enables or disables a filter toggle button (e.g. activity or subpanel toggle buttons)
     * @param {String} toggleDataView the string used in `data-view` attribute for that toggle element (e.g. 'subpanels', 'activitystream')
     * @param {Boolean} on pass true to enable, false to disable
     */
    toggleFilterButton: function (toggleDataView, on) {
        var toggleButtons = this.layout.$('.toggle-actions a.btn');

        // Loops toggle buttons for 'data-view' that corresponds to `toggleDataView` and enables/disables per `on`
        _.each(toggleButtons, function(btn) {
            if($(btn).data('view') === toggleDataView) {
                if(on) {
                    $(btn).removeAttr('disabled').removeClass('disabled');
                } else {
                    $(btn).attr('disabled', 'disabled').addClass('disabled');
                    $(btn).attr('title', app.lang.get('LBL_NO_DATA_AVAILABLE'));
                }
            }
        });
    },

    /**
     * Handles filter panel changes between activity and subpanels
     * @param {String} name Name of panel
     * @param {Boolean} silent Whether to trigger filter events
     * @param {Boolean} setLastViewed Whether to set last viewed to `name` panel
     */
    handleFilterPanelChange: function(name, silent, setLastViewed) {
        this.showingActivities = name === 'activitystream';
        var module = this.showingActivities ? "Activities" : this.module;
        var link;

        this.$el.css('visibility', app.acl.hasAccess(this.aclToCheck, module) ? 'visible' : 'hidden');
        if(this.layoutType === 'record' && !this.showingActivities) {
            module = link = app.user.lastState.get(app.user.lastState.key("subpanels-last", this)) || 'all_modules';
            if (link !== 'all_modules') {
                module = app.data.getRelatedModule(this.module, link);
            }
        } else {
            link = null;
        }
        if (!silent) {
            this.trigger("filter:render:module");
            this.trigger("filter:change:module", module, link);
        }
        if (setLastViewed) {
            // Asks filterpanel to update user.lastState with new panel name as last viewed
            this.layout.trigger('filterpanel:lastviewed:set', name);
        }
    },

    /**
     * handles filter change
     * @param id
     * @param preventCache
     */
    handleFilterChange: function(id, preventCache) {
        if (id  && !preventCache) {
            this.setLastFilter(this.layout.currentModule, this.layoutType, id);
        }

        var filter, editState = this.retrieveFilterEditState();
        // Figure out if we have an edit state. This would mean user was editing the filter so we want him to retrieve
        // the filter form in the state he left it.
        if (editState) {
            filter = app.data.createBean('Filters');
            filter.set(editState);
            // Open the filter form with last edit state
            this.trigger("filter:create:open", filter);
            if (!filter.id ||
                (this.filters.get(filter.id) && !_.isEqual(editState, this.filters.get(filter.id).toJSON()))) {
                // Validate so `Save` button is available
                this.layout.trigger('filter:toggle:savestate', true);
            }
        } else {
            filter = this.filters.get(id) || this.emptyFilter;
        }

        this.context.set('currentFilterId', filter.get('id'));

        // If the user selects a filter template, open the filterpanel
        // to indicate it is ready for further editing.
        if (filter.get('filter_template') &&
            JSON.stringify(filter.get('filter_definition')) !== JSON.stringify(filter.get('filter_template'))
        ) {
            this.trigger('filter:create:open', filter);
        }

        var ctxList = this.getRelevantContextList();
        var clear = false;
        //Determine if we need to clear the collections
        _.each(ctxList, function(ctx) {
            var filterDef = filter.get('filter_definition');
            var orig = ctx.get('collection').origFilterDef;
            ctx.get('collection').origFilterDef = filterDef;  //Set new filter def on each collection
            if (_.isUndefined(orig) || !_.isEqual(orig, filterDef)) {
                clear = true;
            }
        });
        //If so, reset collections and trigger quicksearch to repopulate
        if (clear) {
            _.each(ctxList, function(ctx) {
                ctx.get('collection').resetPagination();
                // Silently reset the collection otherwise the view is re-rendered.
                // It will be re-rendered on request response.
                ctx.get('collection').reset(null, { silent: true });
            });
            this.trigger('filter:clear:quicksearch');
        }
    },
    /**
     * Applies filter on current contexts
     * @param {String} query search string
     * @param {Object} dynamicFilterDef(optional)
     */
    applyFilter: function(query, dynamicFilterDef) {
        // TODO: getRelevantContextList needs to be refactored to handle filterpanels in drawer layouts,
        // as it will return the global context instead of filtering a list view within the drawer context.
        // As a result, this flag is needed to prevent filtering on the global context.
        var filterOptions = this.context.get('filterOptions') || {};
        if (filterOptions.auto_apply === false) {
            return;
        }

        //If the quicksearch field is not empty, append a remove icon so the user can clear the search easily
        this._toggleClearQuickSearchIcon(!_.isEmpty(query));
        // reset the selected on filter apply
        var massCollection = this.context.get('mass_collection');
        if (massCollection && massCollection.models && massCollection.models.length > 0) {
            massCollection.reset([],{silent: true});
        }
        var self = this,
            ctxList = this.getRelevantContextList();
        _.each(ctxList, function(ctx) {
            var ctxCollection = ctx.get('collection'),
                origFilterDef = dynamicFilterDef || ctxCollection.origFilterDef || [],
                filterDef = self.buildFilterDef(origFilterDef, query, ctx),
                options = {
                    //Show alerts for this request
                    showAlerts: true,
                    success: function(collection, response, options) {
                        // Close the preview pane to ensure that the preview
                        // collection is in sync with the list collection.
                        app.events.trigger('preview:close');
                    }};

            ctxCollection.filterDef = filterDef;
            ctxCollection.origFilterDef = origFilterDef;
            ctxCollection.resetPagination();

            options = _.extend(options, ctx.get('collectionOptions'));

            ctx.resetLoadFlag(false);
            if (!_.isEmpty(ctx._recordListFields)) {
                ctx.set('fields', ctx._recordListFields);
            }
            ctx.set('skipFetch', false);
            ctx.loadData(options);
        });
    },

    /**
     * Look for the relevant contexts. It can be
     * - the activity stream context
     * - the list view context on records layout
     * - the selection list view context on records layout
     * - the contexts of the subpanels on record layout
     * @returns {Array} array of contexts
     */
    getRelevantContextList: function() {
        var contextList = [];
        if (this.showingActivities) {
            _.each(this.layout._components, function(component) {
                var ctx = component.context;
                if (component.name == 'activitystream' && !ctx.get('modelId') && ctx.get('collection')) {
                    //FIXME: filter layout's _components array has multiple references to same activitystreams layout object
                    contextList.push(ctx);

                }
            });
        } else {
            if (this.layoutType === 'records') {
                var ctx = this.context.parent || this.context;
                if (!ctx.get('modelId') && ctx.get('collection')) {
                    contextList.push(ctx);
                }
            } else {
                //Locate and add subpanel contexts
                _.each(this.context.children, function(ctx) {
                    if (ctx.get('isSubpanel') && !ctx.get('hidden') && !ctx.get('modelId') && ctx.get('collection')) {
                        contextList.push(ctx);
                    }
                });
            }
        }
        return _.uniq(contextList);
    },

    /**
     * Builds the filter definition based on preselected filter and module quick search fields
     * @param {Object} oSelectedFilter
     * @param {String} searchTerm
     * @param {Context} context
     * @returns {Array} array containing filter def
     */
    buildFilterDef: function(oSelectedFilter, searchTerm, context) {
        var selectedFilter = app.utils.deepCopy(oSelectedFilter),
            isSelectedFilter = _.size(selectedFilter) > 0,
            module = context.get('module'),
            searchFilter = this.getFilterDef(module, searchTerm),
            isSearchFilter = _.size(searchFilter) > 0;

        if (isSelectedFilter && isSearchFilter) {
            selectedFilter = _.isArray(selectedFilter) ? selectedFilter : [selectedFilter];
            selectedFilter.push(searchFilter[0]);
            return [{'$and': selectedFilter }];
        } else if (isSelectedFilter) {
            return selectedFilter;
        } else if (isSearchFilter) {
            return searchFilter;
        }

        return [];
    },

    /**
     * Reset the filter to the previous state
     * @param {String} moduleName The module name.
     * @param {String} linkName The link module name.
     * @param {String} lastFilter The filter ID to initialize with.
     */
    initializeFilterState: function(moduleName, linkName, lastFilter) {
        moduleName = moduleName || this.module;
        lastFilter = lastFilter || this.getLastFilter(moduleName, this.layoutType);
        var filterData;
        if (this.layoutType === 'record' && !this.showingActivities) {
            linkName = app.user.lastState.get(app.user.lastState.key("subpanels-last", this)) || linkName;
            filterData = {
                link: linkName || 'all_modules',
                filter: lastFilter || 'all_records'
            };
        } else {
            filterData = {
                filter: lastFilter || null
            };
        }
        this.applyPreviousFilter(moduleName, linkName, filterData);
    },
    /**
     * applies previous filter
     * @param {String} moduleName
     * @param {String} linkName
     * @param {Object} data
     */
    applyPreviousFilter: function (moduleName, linkName, data) {
        var module = moduleName || this.module,
            link = linkName || data.link;
        if (this.showingActivities) module = "Activities";
        if (this.layoutType === 'record' && link !== 'all_modules' && !this.showingActivities) {
            var moduleMeta = app.metadata.getModule(module);
            // only switch modules if this link actually exists on the module
            if (!_.isUndefined(moduleMeta.fields[link])) {
                module = app.data.getRelatedModule(module, link) || module;
            }

        }

        this.trigger('filter:change:module', module, link, true);
        this.getFilters(module, data.filter);
    },

    /**
     * Retrieves the appropriate list of filters from the server.
     * @param  {String} moduleName
     * @param  {String} defaultId
     */
    getFilters: function(moduleName, defaultId) {
        var filter = [
            {'created_by': app.user.id},
            {'module_name': moduleName}
        ], self = this;
        // TODO: Add filtering on subpanel vs. non-subpanel filters here.
        var filterLayout = app.view._getController({type:'layout',name:'filter'});
        if (filterLayout.loadedModules[moduleName] && !_.isUndefined(app.user.lastState.get(app.user.lastState.key("saved-" + moduleName, this))))
        {
            this.filters.reset();
            var filters = app.user.lastState.get(app.user.lastState.key("saved-" + moduleName, this));
            _.each(filters, function(f){
                self.filters.add(app.data.createBean("Filters", f));
            });
            self.handleFilterRetrieve(moduleName, defaultId);
        }
        else {
            this.filters.fetch({
                //Don't show alerts for this request
                showAlerts: false,
                filter: filter,
                success:function(){
                    if (self.disposed) return;
                    filterLayout.loadedModules[moduleName] = true;
                    app.user.lastState.set(app.user.lastState.key("saved-" + moduleName, self), self.filters.toJSON());
                    self.handleFilterRetrieve(moduleName, defaultId);
                }
            });
        }
    },
    /**
     * handles return from filter retrieve per module
     * @param moduleName
     * @param defaultId
     */
    handleFilterRetrieve: function(moduleName, defaultId) {
        var lastFilter = this.getLastFilter(moduleName, this.layoutType);
        var defaultFilterFromMeta,
            possibleFilters = [],
            filterMeta = this.getModuleFilterMeta(moduleName);

        if (filterMeta) {
            _.each(filterMeta, function(value) {
                if (_.isObject(value)) {
                    if (_.isObject(value.meta.filters)) {
                        this.filters.add(value.meta.filters);
                    }
                    if (value.meta.default_filter) {
                        defaultFilterFromMeta = value.meta.default_filter;
                    }
                }
            }, this);

            possibleFilters = [defaultId, defaultFilterFromMeta, 'all_records'];
            possibleFilters = _.filter(possibleFilters, this.filters.get, this.filters);
        }

        if (!lastFilter || (!this.filters.get(lastFilter) && lastFilter !== 'create')) {
            this.clearLastFilter(moduleName, this.layoutType);
            lastFilter = _.first(possibleFilters) || 'all_records';
            this.setLastFilter(moduleName, this.layoutType, lastFilter);
        }
        this.layout.trigger('filterpanel:change:module', moduleName);
        this.trigger('filter:render:filter');
        this.trigger('filter:change:filter', lastFilter, true);
    },

    /**
     * Utility function to know if the create filter panel is opened.
     * @returns {Boolean} true if opened
     */
    createPanelIsOpen: function() {
        return !this.layout.$(".filter-options").is(":hidden");
    },

    /**
     * Determines whether a user can create a filter for the current module.
     * @return {Boolean} true if creatable
     */
    canCreateFilter: function() {
        // Check for create in meta and make sure that we're only showing one
        // module, then return false if any is false.
        var contexts = this.getRelevantContextList(),
            creatable = app.acl.hasAccess("create", "Filters"),
            meta;
        // Short circuit if we don't have the ACLs to create Filter beans.

        if (creatable && contexts.length === 1) {
            meta = app.metadata.getModule(contexts[0].get("module"));
            if (_.isObject(meta.filters)) {
                _.each(meta.filters, function(value) {
                    if (_.isObject(value)) {
                        creatable = creatable && value.meta.create !== false;
                    }
                });
            }
        }

        return creatable;
    },

    /**
     * Get filters metadata from module metadata for a module
     * @param {String} moduleName
     * @returns {Object} filters metadata
     */
    getModuleFilterMeta: function(moduleName) {
        var meta;
        if (moduleName !== 'all_modules') {
            meta = app.metadata.getModule(moduleName);
            if (_.isObject(meta)) {
                meta = meta.filters;
            }
        }

        return meta;
    },

    /**
     * Append or remove an icon to the quicksearch input so the user can clear the search easily
     * @param {Boolean} addIt TRUE if you want to add it, FALSO to remove
     */
    _toggleClearQuickSearchIcon: function(addIt) {
        if (addIt && !this.$('.add-on.icon-remove')[0]) {
            this.$el.append('<i class="add-on icon-remove"></i>');
        } else if (!addIt) {
            this.$('.add-on.icon-remove').remove();
        }
    },

    /**
     * "sort" comparator functions take two models, and return -1 if the first model should come before the second,
     * 0 if they are of the same rank and 1 if the first model should come after.
     *
     * @param {Bean} model1
     * @param {Bean} model2
     */
    filterCollectionSorting: function(model1, model2) {
        if (model1.get('editable') === false && model2.get('editable') !== false) {
            return +1;
        }
        if (model1.get('editable') !== false && model2.get('editable') === false) {
            return -1;
        }
        if (this._getTranslatedFilterName(model1).toLowerCase() < this._getTranslatedFilterName(model2).toLowerCase()) {
            return -1;
        }
        return +1;
    },

    /**
     * If a model is editable, just return the name. If the model is not editable, it must be defined as a label that
     * is internationalized. We need to retrieve the translated text. Also we allow injecting the translated module
     * name into filter names.
     *
     * @param {Bean} model filter
     * @returns {String} translated filter name
     * @private
     */
    _getTranslatedFilterName: function(model) {
        if (model.get('editable') === false) {
            var moduleName = app.lang.get('LBL_MODULE_NAME', this.layout.currentModule);
            var text = app.lang.get(model.get('name'), ['Filters', this.layout.currentModule]) || '';
            return app.utils.formatString(text, [moduleName]);
        } else {
            return model.get('name') || '';
        }
    },

    /**
     * @override
     * @private
     */
    _render: function() {
        if (app.acl.hasAccess(this.aclToCheck, this.module)) {
            app.view.Layout.prototype._render.call(this);
        }
    },

    /**
     * @override
     */
    unbind: function() {
        this.filters.off();
        this.filters = null;
        app.view.Layout.prototype.unbind.call(this);
    }

})
