({

/**
 * View that displays a list of models pulled from the context's collection.
 * @class View.Views.ListView
 * @alias SUGAR.App.layout.ListView
 * @extends View.View
 */
    events: {
        'click [class*="orderBy"]': 'setOrderBy',
        'mouseenter tr': 'showActions',
        'mouseleave tr': 'hideActions'
    },
    _renderSelf: function() {
        app.view.View.prototype._renderSelf.call(this);
        // off prevents multiple bindings for each render
        this.layout.off("list:search:fire", null, this);
        this.layout.off("list:paginate:success", null, this);
        this.layout.on("list:search:fire", this.fireSearch, this);
        this.layout.on("list:paginate:success", this.render, this);
        this.layout.off("list:filter:toggled", null, this);
        this.layout.on("list:filter:toggled", this.filterToggled, this);

        // Dashboard layout injects shared context with limit: 5. 
        // Otherwise, we don't set so fetches will use max query in config.
        this.limit = this.context.get('limit') ? this.context.get('limit') : null;
    },
    filterToggled: function(isOpened) {
        this.filterOpened = isOpened;
    },
    fireSearch: function(term) {
        var options = {
            limit: this.limit || null,
            params: { 
                q: term
            },
            fields: this.collection.fields || {}
        };
        this.collection.fetch(options);
    },

    /**
     * Sets order by on collection and view
     * @param {Object} event jquery event object
     */
    setOrderBy: function(event) {
        var orderMap, collection, fieldName, nOrder, options,
            self = this;
        //set on this obj and not the prototype
        self.orderBy = self.orderBy || {};

        //mapping for css
        orderMap = {
            "desc": "_desc",
            "asc": "_asc"
        };

        //TODO probably need to check if we can sort this field from metadata
        collection = self.collection;
        fieldName = self.$(event.target).data('fieldname');

        if (!collection.orderBy) {
            collection.orderBy = {
                field: "",
                direction: ""
            };
        }

        nOrder = "desc";

        // if same field just flip
        if (fieldName === collection.orderBy.field) {
            if (collection.orderBy.direction === "desc") {
                nOrder = "asc";
            }
            collection.orderBy.direction = nOrder;
        } else {
            collection.orderBy.field = fieldName;
            collection.orderBy.direction = "desc";
        }

        // set it on the view
        self.orderBy.field = fieldName;
        self.orderBy.direction = orderMap[collection.orderBy.direction];

        // Treat as a "sorted search" if the filter is toggled open
        options = self.filterOpened ? self.getSearchOptions() : {};

        // If injected context with a limit (dashboard) then fetch only that 
        // amount. Also, add true will make it append to already loaded records.
        options.limit   = self.limit || null;
        options.success = function() {
                self.render();
                window.scrollTo(0, document.body.scrollHeight);
        };
        
        // refetch the collection
        collection.fetch(options);
    },
    getSearchOptions: function() {
        var collection, options, previousTerms, term = '';
        collection = this.context.get('collection');

        // If we've made a previous search for this module grab from cache
        if(app.cache.has('previousTerms')) {
            previousTerms = app.cache.get('previousTerms');
            if(previousTerms) {
                term = previousTerms[this.module];
            } 
        }
        // build search-specific options and return
        options = {
            params: { 
                q: term
            },
            fields: collection.fields ? collection.fields : this.collection
        };
        return options;
    },
    showActions: function(e) {
        $(e.currentTarget).children("td").children("span").children(".btn-group").show();
    },
    hideActions: function(e) {
        $(e.currentTarget).children("td").children("span").children(".btn-group").hide();
    },
    bindDataChange: function() {
        if (this.collection) {
            this.collection.on("reset", this.render, this);
        }
    }
})

