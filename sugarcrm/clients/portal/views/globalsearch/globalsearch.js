({
    extendsFrom:'GlobalsearchView',
    _renderHtml: function() {
        if (!app.api.isAuthenticated() || app.config.appStatus == 'offline') return;

        app.view.View.prototype._renderHtml.call(this);

        // Search ahead drop down menu stuff
        var self = this,
            menuTemplate = app.template.getView(this.name + '.result');

        this.$('.search-query').searchahead({
            request: function(term) {
                self.fireSearchRequest.call(self, term, this);
            },
            compiler: menuTemplate,
            throttleMillis: (app.config.requiredElapsed || 500),
            throttle: function(callback, millis) {
                if(!self.debounceFunction) {
                    self.debounceFunction = _.debounce(function(){
                        callback();
                    }, millis || 500);
                }
                self.debounceFunction();
            },
            onEnterFn: function(hrefOrTerm, isHref) {
                if(isHref) {  
                   window.location = hrefOrTerm;
                } else {
                    // It's the term only (user didn't select from drop down
                    // so this is essentially the term typed
                    var term = $.trim(self.$('.search-query').attr('value'));
                    if (!_.isEmpty(term)) {
                        self.fireSearchRequest.call(self, term, this);
                    }
                }
            }
        });
        // Prevent the form from being submitted
        this.$('.navbar-search').submit(function() {
            return false;
        });
    },
    /**
     * Callback for the searchahead plugin .. note that
     * 'this' points to the plugin (not the header view!)
     */
    fireSearchRequest: function (term, plugin) {
        var searchModuleNames = this._getSearchModuleNames(),
            maxNum = app.config && app.config.maxSearchQueryResult ? app.config.maxSearchQueryResult : 5,
            params = {
                q: term,
                fields: 'name, id',
                module_list: searchModuleNames.join(","),
                max_num: maxNum
            };
        app.api.search(params, {
            success:function(data) {
                var formattedRecords = [],
                    modList = app.metadata.getModuleNames(true,"create");

                _.each(data.records, function(record) {
                    if (!record.id) {
                        return; // Elastic Search may return records without id and record names.
                    }
                    var formattedRecord = {id:record.id,name:record.name,module:record._module},
                        meta = app.metadata.getModule(record._module);

                    if (meta && meta.isBwcEnabled) {
                        formattedRecord.link = '#' + app.bwc.buildRoute(record._module, record.id, 'DetailView');
                    }
                    else {
                        formattedRecord.link = '#' + app.router.buildRoute(record._module, record.id);
                    }
                    if ((record._search.highlighted)) { // full text search
                        _.each(record._search.highlighted, function(val, key) {
                            if (key !== 'name') { // found in a related field
                               formattedRecord.field_name = app.lang.get(val.label, val.module);
                               formattedRecord.field_value = val.text;
                            }
                        });
                    }
                    formattedRecords.push(formattedRecord);
                });
                plugin.provide({module_list: modList, next_offset: data.next_offset, records: formattedRecords});
            },
            error:function(error) {
                app.error.handleHttpError(error, plugin);
                app.logger.error("Failed to fetch search results in search ahead. " + error);
            }
        });
    },
    /**
     * Show full search results when the search button is clicked
     * (Show searchahead results for sugarcon because we don't have full results page yet)
     */
    gotoFullSearchResultsPage: function(evt) {
        var term;
        // Force navigation to full results page and don't let plugin get control
        evt.preventDefault();
        evt.stopPropagation();
        // URI encode search query string so that it can be safely
        // decoded by search handler (bug55572)
        term = encodeURIComponent(this.$('.search-query').val());
        if(term && term.length) {
            // Bug 57853 Shouldn't show the search result pop up window after click the global search button.
            // This prevents anymore dropdowns (note we re-init if/when _renderHtml gets called again)
            this.$('.search-query').searchahead('disable', 1000);
            app.router.navigate('#search/'+term, {trigger: true});
        }
    }
})
