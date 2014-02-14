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

/**
 * @class View.FlexListView
 * @alias SUGAR.App.view.views.FlexListView
 * @extends View.ListView
 */
({
    extendsFrom: 'ListView',
    className: 'flex-list-view',
    // Model being previewed (if any)
    _previewed: null,

    plugins: ['Tooltip'],

    /**
     * @property {String} The last state key that contains the full list of
     * fields displayable in list views of this module.
     */
    _allListViewsFieldListKey: null,

    /**
     * @property {String} The last state key that contains the visible state of
     * the fields and their position in the table.
     */
    _thisListViewFieldListKey: null,

    /**
     * {@inheritDoc}
     */
    initialize: function (options) {
        this._super("initialize", [options]);
        this.template = app.template.getView('flex-list');
        this.events = _.clone(this.events);

        //Store left column fields
        this.leftColumns = [];
        //Store right column fields
        this.rightColumns = [];
        this.addActions();

        this._allListViewsFieldListKey = app.user.lastState.buildKey('field-list', 'list-views', this.module);
        this._thisListViewFieldListKey = app.user.lastState.key('visible-fields', this);

        this._fields = this.parseFields();

        this.addPreviewEvents();

        //add debounce in initialize so that subclasses will not all use the same prototype function
        this.resize = _.bind(_.debounce(this.resize, 200), this);
        this.bindResize();

        //add an event delegate for right action dropdown buttons onclick events
        if (this.rightColumns.length) {
            this.events = _.extend({}, this.events, {
                'hidden.bs.dropdown .flex-list-view .actions': 'resetDropdownDelegate',
                'shown.bs.dropdown .flex-list-view .actions': 'delegateDropdown'
            });
        }

        this.on('list:reorder:columns', this.reorderCatalog, this);
        this.on('list:save:laststate', this.saveCurrentState, this);
    },

    // fn to turn off all event listeners and reenable tooltips
    resetAllDelegates: function() {
        $(this).parents('.main-pane').off('scroll.right-actions');
        this.$('.flex-list-view .actions').trigger('resetDropdownDelegate.right-actions');
    },

    // fn to turn off event listeners and reenable tooltips
    resetDropdownDelegate: function(e) {
        var $b = this.$(e.currentTarget).first();
        $b.parent('.list').removeClass('open');
        $b.off('resetDropdownDelegate.right-actions');
    },

    delegateDropdown: function(e) {
        var $buttonGroup = this.$(e.currentTarget).first(), // the button group
            windowHeight = $(window).height() - 65; // height of window less padding

        // fn to detect menu colliding with window bottom
        var needsDropupClass = function($b) {
                var menuHeight = $b.height() + $b.children('ul').first().height();
                return (
                     windowHeight < $b.offset().top + menuHeight
                );
            };

        // add open class to parent list to elevate absolute z-index for iOS
        $buttonGroup.parent('.list').addClass('open');
        // detect window bottom collision
        $buttonGroup.toggleClass('dropup', needsDropupClass($buttonGroup));
        // listen for delegate reset
        $buttonGroup.on('resetDropdownDelegate.right-actions', this.resetDropdownDelegate);
        // add a listener to scrolling container
        $buttonGroup.parents('.main-pane')
            .on('scroll.right-actions', _.bind(_.debounce(function() {
                // detect window bottom collision on scroll
                $buttonGroup.toggleClass('dropup', needsDropupClass($buttonGroup));
            }, 30), this));
    },

    addPreviewEvents: function () {
        //When clicking on eye icon, we need to trigger preview:render with model&collection
        this.context.on("list:preview:fire", function (model) {
            app.events.trigger("preview:render", model, this.collection, true);
        }, this);

        //When switching to next/previous record from the preview panel, we need to update the highlighted row
        app.events.on("list:preview:decorate", this.decorateRow, this);
        if (this.layout) {
            this.layout.on("list:sort:fire", function () {
                //When sorting the list view, we need to close the preview panel
                app.events.trigger("preview:close");
            }, this);
            this.layout.on("list:paginate:success", function () {
                //When fetching more records, we need to update the preview collection
                app.events.trigger("preview:collection:change", this.collection);
                // If we have a model in preview, redecorate the row as previewed
                if (this._previewed) {
                    this.decorateRow(this._previewed);
                }
            }, this);
        }
    },

    /**
     * Parse fields to identify which fields are visible and which fields are
     * hidden.
     *
     * In practice, it creates a catalog that lists the fields that are
     * visible (user configuration if exists, otherwise default metadata
     * configuration) and all the fields (no matter their visible state) used to
     * populate the ellipsis dropdown.
     *
     * By default the catalog is sorted by the order defined in the metadata. If
     * user configuration is found, the catalog is sorted per user preference.
     *
     * @return {Object} The catalog object.
     */
    parseFields: function() {
        var catalog = this._createCatalog();

        this._thisListViewFieldList = this._getFieldsLastState();

        if (this._thisListViewFieldList) {
            catalog = this._toggleFields(catalog, this._thisListViewFieldList);
            catalog = this.reorderCatalog(catalog, this._thisListViewFieldList.position);
        }
        return catalog;
    },

    /**
     * Retrieves the user configuration from the cache.
     *
     * The cached value changed in 7.2. In an entry is found in the local
     * storage and is at the wrong format, the value is converted to the new
     * format. If no entry found, or the entry has an unexpected format, it
     * throws an exception and return undefined.
     *
     * @return {Object/undefined} An object whom keys are field names, and
     * values are an object containing the position and the visible state,
     * or `undefined` in case of failure.
     *
     * @private
     */
    _getFieldsLastState: function() {
        if (!this._thisListViewFieldListKey) {
            return;
        }
        var data = app.user.lastState.get(this._thisListViewFieldListKey);
        if (_.isUndefined(data)) {
            return;
        }
        if (!_.isArray(data) || _.isEmpty(data)) {
            app.logger.error('The format of "' + this._thisListViewFieldListKey + '" is unexpected, skipping.');
            return;
        }
        if (_.isString(data[0])) {
            // Old format detected.
            return this._convertFromOldFormat(data);
        }
        return this._decodeCacheData(data);
    },

    /**
     * Create an object that contains 2 keys. Each key is associated to an array
     * that contains the field metadata.
     * List of keys:
     * - `visible`  lists fields user wants to see,
     * - `all`      lists all the fields, with a `selected` attribute that
     *                    indicates their visible state (used to populate the
     *                    ellipsis dropdown).
     *
     * @return {Object} The catalog object.
     * @private
     */
    _createCatalog: function() {
        var catalog = {
            'visible': [],
            'all': []
        };

        _.each(this.meta.panels, function(panel) {
            _.each(panel.fields, function(fieldMeta, i) {
                var isVisible = (fieldMeta['default'] !== false);
                catalog.all.push(_.extend({
                    selected: isVisible
                }, fieldMeta));
            }, this);
        }, this);

        catalog.visible = _.where(catalog.all, { selected: true });
        return catalog;
    },

    /**
     * Take the existing catalog and toggle field visibility based on the last
     * state found in the cache.
     *
     * If for some reason, the field is not found at all in the cached data, it
     * fallbacks to the default visible state of that field (defined in the
     * metadata).
     *
     * @param {Object} catalog The catalog of fields.
     * @param {Object} fields The decoded cached data that contains fields
     * wanted visible and fields wanted hidden.
     * @return {Object} The catalog with visible state of fields based on user
     * preference.
     * @private
     */
    _toggleFields: function(catalog, fields) {
        _.each(fields.visible, function(fieldName) {
            var f = _.find(catalog.all, function(fieldMeta) {
               return fieldMeta.name === fieldName;
            });
            if (f) {
                f.selected = true;
            }
        }, this);
        _.each(fields.hidden, function(fieldName) {
            var f = _.find(catalog.all, function(fieldMeta) {
               return fieldMeta.name === fieldName;
            });
            if (f) {
                f.selected = false;
            }
        }, this);
        catalog.visible = _.where(catalog.all, { selected: true });
        return catalog;
    },

    /**
     * Sort the catalog of fields per the list of field names passed as
     * argument.
     *
     * @param {Object} catalog Field definitions listed in 2 categories:
     * `visible` / `all`.
     * @param {Array} order Array of field names used to sort the catalog.
     * @return {Object} catalog The catalog of fields entirely sorted.
     */
    reorderCatalog: function(catalog, order) {
        order = _.union(order, _.pluck(catalog.all, 'name'));
        var catalogAll = [];

        _.each(order, function(fieldName) {
            var fieldMeta = _.find(catalog.all, function(fieldMeta) {
                return fieldMeta.name === fieldName;
            });
            if (!fieldMeta) {
                return;
            }
            catalogAll.push(fieldMeta);
        });
        catalog.all = catalogAll;
        catalog.visible = _.where(catalog.all, { selected: true });
        return catalog;
    },

    /**
     * Takes the minimized value stored into the cache and decode it to make
     * it more readable and more manipulable.
     *
     * @example
     *      If field storage entry is:
     * <pre><code>
     *      [
     *          'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'
     *      ]
     * </code></pre>
     *      And encoded data is:
     * <pre><code>
     *      [
     *          0, [1,5], [1,2], 0, [0,1], [1,3], 0, [1,4]
     *      ]
     * </code></pre>
     *      The decoded data will be:
     * <pre><code>
     *      {
     *          visible: ['B', 'C', 'F', 'H'],
     *          hidden: ['E'],
     *          position: ['E', 'C', 'B', 'F', 'H']
     *      }
     * </code></pre>
     *      `visible` contains the list of visible fields,
     *      `hidden` contains the list of hidden fields,
     *      `position` is the order of fields,
     *      indexes whom value is `0` are skipped (fields not displayable).
     *
     * @param {Array} encodedData The minimized data.
     * @return {Object} The decoded data.
     * @private
     */
    _decodeCacheData: function(encodedData) {
        var decodedData = {
            visible: [],
            hidden: [],
            position: []
        };

        var fieldList = this._appendFieldsToAllListViewsFieldList();
        _.each(encodedData, function(fieldArray, i) {
            if (!_.isArray(fieldArray)) {
                return;
            }
            var name = fieldList[i];
            if (fieldArray[0]) {
                decodedData.visible.push(name);
            } else {
                decodedData.hidden.push(name);
            }
            decodedData.position[fieldArray[1]] = name;
        });
        decodedData.position = _.difference(decodedData.position, [undefined]);
        return decodedData;
    },

    /**
     * Takes the decoded data and minimize it to save cache size.
     *
     * @example
     *      If field storage entry is:
     * <pre><code>
     *      [
     *          'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'
     *      ]
     * </code></pre>
     *      And decoded data is:
     * <pre><code>
     *      {
     *          visible: ['B', 'C', 'F', 'H'],
     *          hidden: ['E'],
     *          position: ['E', 'C', 'B', 'F', 'H']
     *      }
     * </code></pre>
     *      The encoded data will be:
     * <pre><code>
     *      [
     *          0, [1,5], [1,2], 0, [0,1], [1,3], 0, [1,4]
     *      ]
     * </code></pre>
     *      `0` means the field is not displayable. (i.e: `A`, `D`, `G`),
     *      the first item is the visible state: `1` visible, `0` hidden,
     *      the second item of the array is the position of the field.
     *
     * @param {Object} decodedData The decoded data.
     * @return {Array} The minimized data.
     * @private
     */
    _encodeCacheData: function(decodedData) {
        var encodedData = [];

        var fieldList = this._appendFieldsToAllListViewsFieldList();
        _.each(fieldList, function(fieldName) {
            var value = 0;
            if (_.contains(decodedData.position, fieldName)) {
                value = [
                    _.contains(decodedData.visible, fieldName) ? 1 : 0,
                    _.indexOf(decodedData.position, fieldName) + 1
                ];
            }
            encodedData.push(value);
        });
        return encodedData;
    },

    /**
     * Append the list of fields defined in the metadata that are missing in the
     * field storage cache entry.
     *
     * @return {Array} The list of all the fields that are displayable in
     *                  list views of this module.
     * @private
     */
    _appendFieldsToAllListViewsFieldList: function() {
        this._allListViewsFieldList = app.user.lastState.get(this._allListViewsFieldListKey) || [];

        _.each(this.meta.panels, function(panel) {
            _.each(panel.fields, function(fieldMeta, i) {
                this._allListViewsFieldList.push(fieldMeta.name);
            }, this);
        }, this);
        this._allListViewsFieldList = _.uniq(this._allListViewsFieldList);
        app.user.lastState.set(this._allListViewsFieldListKey, this._allListViewsFieldList);
        return this._allListViewsFieldList;
    },

    /**
     * Converts the old localStorage data for fields visibility to the new
     * decoded format.
     *
     * @example Only visible fields used to be stored. Example of data stored:
     * <pre><code>
     *      [
     *          'B', 'C', 'F', 'H'
     *      ]
     * </code></pre>
     *      If the list of fields defined in the metadata was:
     * <pre><code>
     *      [
     *          'E', 'C', 'B', 'F', 'H'
     *      ]
     * </code></pre>
     *      The decoded data would be:
     * <pre><code>
     *      {
     *          visible: ['B', 'C', 'F', 'H'],
     *          hidden: ['E'],
     *          position: ['E', 'C', 'B', 'F', 'H']
     *      }
     * </code></pre>
     *
     * @return {Array} The data converted to the new decoded format.
     * @see _encodeCacheData
     * @private
     */
    _convertFromOldFormat: function(visibleFieldList) {
        var thisViewFieldList = _.reduce(_.map(this.meta.panels, function(panel) {
            return _.pluck(panel.fields, 'name');
        }), function(memo, field) {
            return memo.concat(field);
        }, []);

        var decoded = {
            visible: [],
            hidden: [],
            position: []
        };
        _.each(thisViewFieldList, function(fieldName, i) {
            if (_.contains(visibleFieldList, fieldName)) {
                decoded.visible.push(fieldName);
            } else {
                decoded.hidden.push(fieldName);
            }
            decoded.position.push(fieldName);
        });
        app.user.lastState.set(this._thisListViewFieldListKey, this._encodeCacheData(decoded));
        return decoded;
    },

    /**
     * Save to the cache the current order of fields, and their visible state.
     *
     * @example Example of value stored in the cache:
     * <pre><code>
     *      [
     *          ['A', 'B', 'D', 'C'],
     *          [0, 1, 0, 1]
     *      ]
     * </code></pre>
     * Means the current order is `ABDC`, and only `B` and `C` are visible
     * fields.
     */
    saveCurrentState: function() {
        if (!this._thisListViewFieldListKey) {
            return;
        }
        var allFields = _.pluck(this._fields.all, 'name'),
            visibleFields = _.pluck(this._fields.visible, 'name');
        var decoded = {
            visible: visibleFields,
            hidden: _.difference(allFields, visibleFields),
            position: allFields
        };
        app.user.lastState.set(this._thisListViewFieldListKey, this._encodeCacheData(decoded));
    },

    /**
     * Add actions to left and right columns
     */
    addActions: function () {
        var meta = this.meta;
        if (_.isObject(meta.selection)) {
            switch (meta.selection.type) {
                case 'single':
                    this.addSingleSelectionAction();
                    break;
                case 'multi':
                    this.addMultiSelectionAction();
                    break;
                default:
                    break;
            }
        }
        if (meta && _.isObject(meta.rowactions)) {
            this.addRowActions();
        }
    },
    /**
     * Add single selection field to left column
     */
    addSingleSelectionAction: function () {
        var _generateMeta = function (name, label) {
            return {
                'type': 'selection',
                'name': name,
                'sortable': false,
                'label': label || ''
            };
        };
        var def = this.meta.selection;
        this.leftColumns.push(_generateMeta(def.name || this.module + '_select', def.label));
    },
    /**
     * Add multi selection field to left column
     */
    addMultiSelectionAction: function () {
        var _generateMeta = function (buttons, disableSelectAllAlert) {
            return {
                'type': 'fieldset',
                'fields': [
                    {
                        'type': 'actionmenu',
                        'buttons': buttons || [],
                        'disable_select_all_alert': !!disableSelectAllAlert
                    }
                ],
                'value': false,
                'sortable': false
            };
        };
        var buttons =               this.meta.selection.actions,
            disableSelectAllAlert = !!this.meta.selection.disable_select_all_alert;
        this.leftColumns.push(_generateMeta(buttons, disableSelectAllAlert));
    },
    /**
     * Add fieldset of rowactions to the right column
     */
    addRowActions: function () {
        var _generateMeta = function (label, css_class, buttons) {
            return {
                'type': 'fieldset',
                'fields': [
                    {
                        'type': 'rowactions',
                        'label': label || '',
                        'css_class': css_class,
                        'buttons': buttons || []
                    }
                ],
                'value': false,
                'sortable': false
            };
        };
        var def = this.meta.rowactions;
        this.rightColumns.push(_generateMeta(def.label, def.css_class, def.actions));
    },
    /**
     * Decorate a row in the list that is being shown in Preview
     * @param model Model for row to be decorated.  Pass a falsy value to clear decoration.
     */
    decorateRow: function (model) {
        // If there are drawers, make sure we're updating only list views on active drawer.
        if (_.isUndefined(app.drawer) || app.drawer.isActive(this.$el)) {
            this._previewed = model;
            this.$("tr.highlighted").removeClass("highlighted current above below");
            if (model) {
                var rowName = model.module + "_" + model.get("id");
                var curr = this.$("tr[name='" + rowName + "']");
                curr.addClass("current highlighted");
                curr.prev("tr").addClass("highlighted above");
                curr.next("tr").addClass("highlighted below");
            }
        }
    },

    _renderHtml: function (ctx, options) {
        this.colSpan = this._fields.visible.length || 0;
        if (this.leftColumns.length) {
            this.colSpan++;
        }
        if (this.rightColumns.length) {
            this.colSpan++;
        }
        if (this.colSpan < 2) {
            this.colSpan = null;
        }
        this._super('_renderHtml', [ctx, options]);

        if (this.leftColumns.length) {
            this.$el.addClass('left-actions');
        }
        if (this.rightColumns.length) {
            this.$el.addClass('right-actions');
        }

        this.resize();
    },
    unbind: function() {
        $(window).off("resize.flexlist-" + this.cid);
        this._super("unbind");
    },

    bindResize: function() {
        $(window).on("resize.flexlist-" + this.cid, _.bind(this.resize, this));
    },
    /**
     * Updates the class of this flex list as scrollable or not.
     */
    resize: function() {
        if (this.disposed) {
            return;
        }
        var $content = this.$('.flex-list-view-content');
        if (!$content.length) {
            return;
        }
        var toggle = $content.get(0).scrollWidth > $content.width() + 1;
        this.$el.toggleClass('scroll-width', toggle);
    },

    _dispose: function() {
        // remove all right action dropdown delegates
        this.resetAllDelegates();
        this._super('_dispose');
    }
})
