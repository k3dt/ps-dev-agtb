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
    className: 'list-view',

    plugins: ['Pagination'],

    /**
     * View that displays a list of models pulled from the context's collection.
     * @class View.Views.ListView
     * @alias SUGAR.App.layout.ListView
     * @extends View.Views.EditableView
     */
    events: {
        'click [class*="orderBy"]':'setOrderBy'
    },

    defaultLayoutEvents: {
        "list:search:fire": "fireSearch",
        "list:filter:toggled": "filterToggled",
        "list:alert:show": "showAlert",
        "list:alert:hide": "hideAlert",
        "list:sort:fire": "sort"
    },

    defaultContextEvents: {},

    // Model being previewed (if any)
    _previewed: null,
    //Store left column fields
    _leftActions: [],
    //Store right column fields
    _rowActions: [],
    //Store default and available(+visible) field names
    _fields: {},

    initialize: function(options) {
        //Grab the list of fields to display from the main list view (assuming initialize is being called from a subclass)
        var listViewMeta = app.metadata.getView(options.module, 'list') || {};
        //Extend from an empty object to prevent polution of the base metadata
        options.meta = _.extend({}, listViewMeta, options.meta || {});
        options.meta.type = options.meta.type || 'list';
        options.meta.action = 'list';
        options = this.parseFieldMetadata(options);

        app.view.View.prototype.initialize.call(this, options);

        this.attachEvents();

        this.orderByLastStateKey = app.user.lastState.key('order-by', this);
        this.orderBy = _.extend({
                field : '',
                direction : 'desc'
            },
            listViewMeta.orderBy,
            app.user.lastState.get(this.orderByLastStateKey) || {});

        if(this.collection) {
            this.collection.orderBy = this.orderBy;
        }
        // Dashboard layout injects shared context with limit: 5.
        // Otherwise, we don't set so fetches will use max query in config.
        this.limit = this.context.has('limit') ? this.context.get('limit') : null;
        this.metaFields = this.meta.panels ? _.first(this.meta.panels).fields : [];
    },

    /**
     * @override
     * @private
     */
    _render: function () {
        app.view.View.prototype._render.call(this);
        //If user has no `list` access, render `noaccess.hbs` template
        if (!app.acl.hasAccessToModel(this.action, this.model)) {
            this._noAccessTemplate = this._noAccessTemplate || app.template.get("list.noaccess");
            this.$el.html(this._noAccessTemplate());
        }
    },

    /**
     * Parse the metadata to make sure that the follow attributes conform to specific standards
     *  - Align: valid options are left, center and right
     *  - Width: any percentage below 100 is valid
     *
     * @param options
     * @returns {*}
     */
    parseFieldMetadata: function(options) {
        // standardize the align and width param in the defs if they exist
        _.each(options.meta.panels, function(panel, panelIdx) {
            _.each(panel.fields, function(field, fieldIdx) {
                if (!_.isUndefined(field.align)) {
                    var alignClass = '';
                    if (_.contains(['left', 'center', 'right'], field.align)) {
                        alignClass = 't' + field.align;
                    }
                    options.meta.panels[panelIdx].fields[fieldIdx].align = alignClass;
                }

                if (!_.isUndefined(field.width)) {
                    // make sure it's a percentage
                    var parts = field.width.toString().match(/^(\d{0,3})\%$/);
                    var widthValue = '';
                    if(parts) {
                        if(parseInt(parts[1]) < 100) {
                            widthValue = parts[0];
                        }
                    }

                    options.meta.panels[panelIdx].fields[fieldIdx].width = widthValue;
                }
            }, this);
        }, this);

        return options;
    },

    /**
     * Takes the defaultListEventMap and listEventMap and binds the events. This is to allow views that
     * extend ListView to specify their own events.
     */
    attachEvents: function() {
        this.layoutEventsMap = _.extend(this.defaultLayoutEvents, this.layoutEvents); // If undefined nothing will be added.
        this.contextEventsMap = _.extend(this.defaultContextEvents, this.contextEvents);

        if (this.layout) {
            _.each(this.layoutEventsMap, function(callback, event) {
                this.layout.on(event, this[callback], this);
            }, this);
        }

        if (this.context) {
            _.each(this.contextEventsMap, function(callback, event) {
                this.context.on(event, this[callback], this);
            }, this);
        }
    },

    sort: function() {
        //When sorting the list view, we need to close the preview panel
        app.events.trigger("preview:close");
    },

    showAlert: function(message) {
        this.$("[data-target=alert]").html(message);
        this.$("[data-target=alert-container]").removeClass("hide");
    },

    hideAlert: function() {
        this.$("[data-target=alert-container]").addClass("hide");
        this.$("[data-target=alert]").empty();
    },
    filterToggled:function (isOpened) {
        this.filterOpened = isOpened;
    },
    fireSearch:function (term) {
        term = term || "";
        var options = {
            limit: this.limit || null,
            query: term
        };
        this.context.get("collection").resetPagination();
        this.context.resetLoadFlag(false);
        this.context.set('skipFetch', false);
        this.context.loadData(options);
    },

    /**
     * Sets order by on collection and view.
     *
     * The event is canceled if an element being dragged is found.
     *
     * @param {Event} event jQuery event object.
     */
    setOrderBy: function(event) {
        if ($(event.currentTarget).find('ui-draggable-dragging').length) {
            return;
        }
        var collection, options, eventTarget, orderBy;
        var self = this;

        collection = self.collection;
        eventTarget = self.$(event.currentTarget);

        // first check if alternate orderby is set for column
        orderBy = eventTarget.data('orderby');
        // if no alternate orderby, use the field name
        if (!orderBy) {
            orderBy = eventTarget.data('fieldname');
        }
        // if same field just flip
        if (orderBy === self.orderBy.field) {
            self.orderBy.direction = self.orderBy.direction === 'desc' ? 'asc' : 'desc';
        } else {
            self.orderBy.field = orderBy;
            self.orderBy.direction = 'desc';
        }

        collection.orderBy = self.orderBy;

        collection.resetPagination();

        options = self.getSortOptions(collection);

        if(this.triggerBefore('list:orderby', options)) {
            self._setOrderBy(options);
        }
    },

    /**
     * Run the order by on the collection
     *
     * @param {Object} options
     * @private
     */
    _setOrderBy: function(options) {
        if(this.orderByLastStateKey) {
            app.user.lastState.set(this.orderByLastStateKey, this.orderBy);
        }
        // refetch the collection
        this.context.resetLoadFlag(false);
        this.context.set('skipFetch', false);
        this.context.loadData(options);
    },
    /**
     * Gets options for fetch call for list sorting
     * @param collection
     * @returns {Object}
     */
    getSortOptions: function(collection) {
        var self = this, options = {};
        // Treat as a "sorted search" if the filter is toggled open
        options = self.filterOpened ? self.getSearchOptions() : {};

        //Show alerts for this request
        options.showAlerts = true;

        // If injected context with a limit (dashboard) then fetch only that
        // amount. Also, add true will make it append to already loaded records.
        options.limit = self.limit || null;
        options.success = function (collection, response, options) {
            self.layout.trigger("list:sort:fire", collection, self);
            // reset the collection with what we fetched to trigger rerender
            if(!self.disposed) collection.reset(response);
        };

        // if we have a bunch of models already fetch at least that many
        if (collection.offset) {
            options.limit = collection.offset;
            options.offset = 0;
        }

        return options;
    },
    getSearchOptions:function () {
        var collection, options, previousTerms, term = '';
        collection = this.context.get('collection');

        // If we've made a previous search for this module grab from cache
        if (app.cache.has('previousTerms')) {
            previousTerms = app.cache.get('previousTerms');
            if (previousTerms) {
                term = previousTerms[this.module];
            }
        }
        // build search-specific options and return
        options = {
            params:{},
            fields:collection.fields ? collection.fields : this.collection
        };
        if (term) {
            options.params.q = term;
        }
        if (this.context.get('link')) {
            options.relate = true;
        }
        return options;
    },
    bindDataChange:function () {
        if (this.collection) {
            this.collection.on("reset", this.render, this);
        }
    },

    _dispose: function() {
        this._fields = null;
        app.view.View.prototype._dispose.call(this);
    }
})
