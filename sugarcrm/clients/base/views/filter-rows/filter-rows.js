({
    /**
     * Form for creating a filter
     * Part of BaseFilterpanelLayout layout
     *
     * @class BaseFilterRowsView
     * @extends View
     */

    events: {
        'click a.addme': 'addRow',
        'click a.removeme': 'removeRow',
        'change .filter-field select': 'handleFieldSelected',
        'change .filter-operator select': 'handleOperatorSelected'
    },

    className: 'filter-definition-container',

    filterFields: [],

    /**
     * Map of fields types.
     *
     * Specifies correspondence between field types and field operator types.
     */
    fieldTypeMap: {
        'datetime' : 'date',
        'datetimecombo' : 'date'
    },

    /**
     * @override
     * @param {Object} opts
     */
    initialize: function(opts) {
        //Load partial
        this.formRowTemplate = app.template.get("filter-rows.filter-row-partial");

        this.filterOperatorMap = app.lang.getAppListStrings("filter_operators_dom");
        app.view.View.prototype.initialize.call(this, opts);

        this.listenTo(this.layout, "filterpanel:change:module", this.handleFilterChange);
        this.listenTo(this.layout, "filter:create:open", this.openForm);
        this.listenTo(this.layout, "filter:create:close", this.render);
        this.listenTo(this.layout, "filter:create:save", this.saveFilter);
        this.listenTo(this.layout, "filter:create:delete", this.deleteFilter);
    },

    /**
     * Handler for filter:change event
     * Loads filterable fields for specified module
     * @param moduleName
     */
    handleFilterChange: function(moduleName) {
        var moduleMeta = app.metadata.getModule(moduleName);
        if (!moduleMeta) {
            return;
        }
        this.fieldList = this.getFilterableFields(moduleName);

        // This is the Select2 data for the enum field. 1st value must be blank.
        this.filterFields = {"": ""};
        this.moduleName = moduleName;

        _.each(this.fieldList, function(value, key) {
            var text = app.lang.get(value.vname, moduleName);
            // Check if we support this field type.
            var type = this.fieldTypeMap[value.type] || value.type;
            //Predefined filters don't have operators defined
            if ((this.filterOperatorMap[type] || value.predefined_filter === true) && !_.isUndefined(text)) {
                this.filterFields[key] = text;
            }
        }, this);
    },

    /**
     * Handler for filter:create:open event
     * @param filterModel
     */
    openForm: function(filterModel) {
        if (!filterModel.get('filter_definition')) {
            this.render();
            this.addRow();
        } else {
            this.populateFilter();
        }
    },

    /**
     * Save the filter
     * @param {String} name
     */
    saveFilter: function(name) {
        var self = this,
            obj = {
                filter_definition: this.buildFilterDef(),
                name: name,
                module_name: this.moduleName
            };

        this.layout.editingFilter.save(obj, {
            success: function(model) {
                self.layout.trigger("filter:add", model);
                self.layout.trigger("filter:create:rowsValid", false);
            },
            alerts: {
                'success': {
                    title: app.lang.get("LBL_EMAIL_SUCCESS") + ":",
                    messages: app.lang.get("LBL_FILTER_SAVE") + " " + name
                }
            }
        });

        this.layout.trigger('filter:create:close');
    },

    /**
     * Delete the filter
     */
    deleteFilter: function() {
        var self = this,
            name = this.layout.editingFilter.get('name');
        this.layout.editingFilter.destroy({
            success: function(model) {
                self.layout.trigger("filter:remove", model);
            },
            alerts: {
                'success': {
                    title: app.lang.get('LBL_EMAIL_SUCCESS') + ':',
                    message: app.lang.get('LBL_DELETED') + ' ' + name
                }
            }
        });
        this.layout.trigger('filter:create:close');
    },


    /**
     * Get filterable fields from the module metadata
     * @param {String} moduleName
     * @returns {Object}
     */
    getFilterableFields: function(moduleName) {
        var moduleMeta = app.metadata.getModule(moduleName),
            fieldMeta = moduleMeta.fields,
            fields = {};
        if (moduleMeta.filters) {
            _.each(moduleMeta.filters, function(templateMeta) {
                if (templateMeta.meta && templateMeta.meta.fields) {
                    fields = _.extend(fields, templateMeta.meta.fields);
                }
            });
        }

        _.each(fields, function(fieldFilterDef, fieldName) {
            if (_.isEmpty(fieldFilterDef)) {
                fields[fieldName] = fieldMeta[fieldName];
            } else {
                fields[fieldName] = _.extend({name: fieldName}, fieldFilterDef, fieldMeta[fieldName]);
            }
            delete fields[fieldName]['readonly'];
        });

        return fields;
    },

    /**
     * Utility function to instanciate an enum field
     *
     * @param {Model} model
     * @param {Object} def
     * @returns {Field}
     */
    createField: function(model, def) {
        var obj = {
            meta: {
                view: "edit"
            },
            def: def,
            model: model,
            context: app.controller.context,
            viewName: "edit",
            view: this
        };
        var field = app.view.createField(obj);
        return field;
    },

    /**
     * Add a row
     * @param {Event} e
     * @returns {Object}
     */
    addRow: function(e) {
        var $row, model, field, $fieldValue, $fieldContainer;

        if (e) {
            // Triggered by clicking the plus sign. Add the row to that point.
            $row = this.$(e.currentTarget).parents('.filter-body');
            $row.after(this.formRowTemplate());
            $row = $row.next();
        } else {
            // Add the initial row.
            $row = $(this.formRowTemplate()).appendTo(this.$el);
        }
        model = app.data.createBean(this.moduleName);
        field = this.createField(model, {
            type: 'enum',
            options: this.filterFields
        });

        $fieldValue = $row.find('.filter-field');
        $fieldContainer = $(field.getPlaceholder().string);
        $fieldContainer.appendTo($fieldValue);

        $row.data('nameField', field);

        this._renderField(field);
        this.layout.trigger("filter:create:rowsValid", false);

        return $row;
    },


    /**
     * Remove a row
     * @param {Event} e
     */
    removeRow: function(e) {
        var $row = this.$(e.currentTarget).parents('article.filter-body'),
            $rows = this.$('article.filter-body'),
            fieldOpts = [
                {'field': 'nameField', 'value': 'name'},
                {'field': 'operatorField', 'value': 'operator'},
                {'field': 'valueField', 'value': 'value'}
            ];

        this._disposeFields($row, fieldOpts);
        $row.remove();
        this.validateRows($rows.not($row));
        if ($rows.length === 1) {
            this.addRow();
        }
    },

    /**
     * Validate rows
     * @param {Array} $rows
     */
    validateRows: function($rows) {
        $rows = $rows ? $rows : this.$('article.filter-body');
        this.layout.trigger("filter:create:rowsValid", true);
        _.each($rows, function(row) {
            var data = $(row).data();
            if (_.isEmpty(data.value) && !data.isDateRange && !data.isPredefinedFilter) {
                this.layout.trigger("filter:create:rowsValid", false);
            }
        }, this);
    },

    /**
     * Rerender the view with selected filter
     */
    populateFilter: function() {
        var filterDef = this.layout.editingFilter.get("filter_definition"),
            name = this.layout.editingFilter.get("name");

        this.render();
        this.layout.trigger("filter:set:name", name);

        _.each(filterDef, function(row) {
            this.populateRow(row);
        }, this);
    },

    /**
     * Populate filter edition row
     * @param {Object} rowObj
     */
    populateRow: function(rowObj) {
        var $row = this.addRow();
        _.each(rowObj, function(value, key) {
            if (key === "$or") {
                var keys = _.reduce(value, function(memo, obj) {
                    return memo.concat(_.keys(obj));
                }, []);

                key = _.find(_.keys(this.fieldList), function(key) {
                    if (_.has(this.fieldList[key], 'dbFields')) {
                        return _.isEqual(this.fieldList[key].dbFields.sort(), keys.sort());
                    }
                }, this);

                // Predicates are identical, so we just use the first.
                value = _.values(value[0])[0];
            }

            $row.find('.filter-field select').select2('val', key).trigger('change');
            if (_.isString(value)) {
                value = {"$equals": value};
            }
            _.each(value, function(value, operator) {
                $row.data('value', value);
                $row.find('.filter-operator select').select2('val', operator).trigger('change');
            });
        }, this);
    },

    /**
     * Fired when a user selects a field to filter by
     * @param {Event} e
     */
    handleFieldSelected: function(e) {
        var $el = this.$(e.currentTarget),
            $row = $el.parents('.filter-body'),
            $fieldWrapper = $row.find('.filter-operator'),
            data = $row.data(),
            fieldName = $el.val(),
            fieldOpts = [
                {'field': 'operatorField', 'value': 'operator'},
                {'field': 'valueField', 'value': 'value'}
            ];
        this._disposeFields($row, fieldOpts);

        data['name'] = fieldName;
        if (!fieldName) {
            return;
        }

        //Reset flags
        data.isDateRange = false;
        data.isPredefinedFilter = false;

        //Predefined filters don't need operators and value field
        if (this.fieldList[fieldName].predefined_filter === true) {
            data.isPredefinedFilter = true;
            this.fireSearch();
            return;
        }

        // Get operators for this filter type
        var fieldType = this.fieldTypeMap[this.fieldList[fieldName].type] || this.fieldList[fieldName].type,
            payload = {"": ""},
            types = _.keys(this.filterOperatorMap[fieldType]);

        $fieldWrapper.removeClass('hide').empty();
        $row.find('.filter-value').addClass('hide').empty();

        // If the user is editing a filter, clear the operator.
        //$row.find('.field-operator select').select2('val', '');

        _.each(types, function(operand) {
            payload[operand] = this.filterOperatorMap[fieldType][operand];
        }, this);

        // Render the operator field
        var model = app.data.createBean(this.moduleName);
        var field = this.createField(model, {
                type: 'enum',
                // minimumResultsForSearch set to 9999 to hide the search field,
                // See: https://github.com/ivaynberg/select2/issues/414
                searchBarThreshold: 9999,
                options: payload
            }),
            $field = $(field.getPlaceholder().string);

        $field.appendTo($fieldWrapper);
        data['operatorField'] = field;

        this._renderField(field);
    },

    /**
     * Fired when a user selects an operator to filter by
     * @param {Event} e
     */
    handleOperatorSelected: function(e) {
        var $el = this.$(e.currentTarget),
            $row = $el.parents('.filter-body'),
            data = $row.data(),
            operation = $el.val(),
            fieldOpts = [
                {'field': 'valueField', 'value': 'value'}
            ];

        this._disposeFields($row, fieldOpts);

        data['operator'] = operation;
        if (!operation) {
            return;
        }

        // Patching fields metadata
        var moduleName = this.moduleName,
            module = app.metadata.getModule(moduleName),
            fields = app.metadata._patchFields(moduleName, module, app.utils.deepCopy(this.fieldList));

        // More patch for some field types
        var fieldName = $row.find('.filter-field select').val(),
            fieldType = this.fieldTypeMap[this.fieldList[fieldName].type] || this.fieldList[fieldName].type,
            fieldDef = fields[fieldName];

        switch (fieldType) {
            case 'enum':
                fieldDef.isMultiSelect = true;
                // minimumResultsForSearch set to 9999 to hide the search field,
                // See: https://github.com/ivaynberg/select2/issues/414
                fieldDef.searchBarThreshold = 9999;
                break;
            case 'bool':
                fieldDef.type = 'enum';
                // minimumResultsForSearch set to 9999 to hide the search field,
                // See: https://github.com/ivaynberg/select2/issues/414
                fieldDef.searchBarThreshold = 9999;
                break;
            case 'int':
                fieldDef.auto_increment = false;
                break;
            case 'date':
                fieldDef.type = 'date';
                //Flag to indicate the value needs to be formatted correctly
                data.isDate = true;
                if (operation.charAt(0) !== '$') {
                    //Flag to indicate we need to build the date filter definition based on the date operator
                    data.isDateRange = true;
                    this.fireSearch();
                    return;
                }
                break;
        }

        // Create new model with the value set
        var model = app.data.createBean(moduleName);
        var $fieldValue = $row.find('.filter-value');

        $fieldValue.removeClass('hide').empty();

        //If the operation is $between we need to set two inputs.
        if (operation === '$between' || operation === '$dateBetween') {
            var minmax = [],
                value = $row.data('value') || [];

            model.set(fieldName + '_min', value[0] || '');
            model.set(fieldName + '_max', value[1] || '');
            minmax.push(this.createField(model, _.extend({}, fieldDef, {name: fieldName + '_min'})));
            minmax.push(this.createField(model, _.extend({}, fieldDef, {name: fieldName + '_max'})));

            data['valueField'] = minmax;
            _.each(minmax, function(field) {
                var fieldContainer = $(field.getPlaceholder().string);
                $fieldValue.append(fieldContainer);
                this.listenTo(field, 'render', function() {
                    field.$('input, select, textarea').addClass('inherit-width');
                    // .date makes .inherit-width on input have no effect so we need to remove it.
                    field.$('.input-append').removeClass('date');
                    field.$('input, textarea').on('keyup', _.debounce(_.bind(function(e) {
                        this.value = $(e.currentTarget).val();
                        // We use "silent" update because we don't need re-render the field.
                        model.set(fieldName, this.unformat($(e.currentTarget).val()), {silent: true});
                        model.trigger('change');
                    }, field), 400));
                });
                this._renderField(field);
            }, this);
        } else {
            model.set(fieldName, $row.data('value') || undefined);
            // Render the value field
            var field = this.createField(model, fieldDef),
                fieldContainer = $(field.getPlaceholder().string);
            $fieldValue.append(fieldContainer);
            data['valueField'] = field;

            this.listenTo(field, 'render', function() {
                field.$('input, select, textarea').addClass('inherit-width');
                // .date makes .inherit-width on input have no effect so we need to remove it.
                field.$('.input-append').removeClass('date');
                field.$('input, textarea').on('keyup', _.debounce(_.bind(function(e) {
                    this.value = $(e.currentTarget).val();
                    // We use "silent" update because we don't need re-render the field.
                    model.set(fieldName, this.unformat($(e.currentTarget).val()), {silent: true});
                    model.trigger('change');
                }, field), 400));
            });
            //Have to format the Blank value
            //Be careful using fieldType and not fieldDef.type to not mess with converted fields
            if (fieldType === 'enum') {
                var _changeBlankLabel = function() {
                    if (!_.isUndefined(this.items['']) && this.items[''] === '') {
                        this.items = _.clone(this.items);
                        this.items[''] = app.lang.getAppString('LBL_BLANK_VALUE');
                    }
                };
                field.items = field.loadEnumOptions(false, function() {
                    _changeBlankLabel.call(field);
                });
                _changeBlankLabel.call(field);
            }

            this._renderField(field);
        }

        // When the value change a quicksearch should be fired to update the results
        this.listenTo(model, "change", (function($row) {
            return function() {
                var fields = $row.data("valueField"),
                    valueForFilter = '';

                //If we have multiple fields we have to build an array of values
                if (_.isArray(fields)) {
                    valueForFilter = [];
                    _.each(fields, function(field) {
                        var value = !field.disposed && field.model.has(field.name) ? field.model.get(field.name) : '';
                        value = $row.data('isDate') ? app.date.stripIsoTimeDelimterAndTZ(value) : value;
                        valueForFilter.push(value);
                    });
                } else {
                    var value = !field.disposed && field.model.has(field.name) ? field.model.get(field.name) : '';
                    valueForFilter = $row.data('isDate') ? app.date.stripIsoTimeDelimterAndTZ(value) : value;
                }

                $row.data("value", valueForFilter);
                this.fireSearch();
            };
        })($row));
    },

    /**
     * Check each row, builds the filter definition and trigger the filtering
     */
    fireSearch: function() {
        var filterDef = this.buildFilterDef();
        this.layout.trigger('filter:apply', null, filterDef);
    },

    /**
     * Build filter definition
     * @returns {Array}
     */
    buildFilterDef: function() {
        var $rows = this.$('article.filter-body'),
            filter = [];

        _.each($rows, function(row) {
            var rowFilter = this.buildRowFilterDef($(row));

            if (rowFilter) {
                filter.push(rowFilter);
            }
        }, this);

        this.validateRows($rows);

        return filter;
    },

    /**
     * Build filter definition for one row
     * @param {Object} $row
     * @returns {Object}
     */
    buildRowFilterDef: function($row) {
        var data = $row.data(),
            name = data['name'],
            operator = data['operator'],
            value = data['value'],
            filter = {};

        if (this.fieldList[name] && this.fieldList[name].id_name && this.fieldList[this.fieldList[name].id_name]) {
            name = this.fieldList[name].id_name;
        }
        if (data.isPredefinedFilter) {
            filter[name] = "";
            return filter;
        } else if (!_.isEmpty(value) || data.isDateRange) {
            if (_.has(this.fieldList[name], 'dbFields')) {
                var subfilters = [];
                _.each(this.fieldList[name].dbFields, function(dbField) {
                    var filter = {};
                    filter[dbField] = {};
                    filter[dbField][operator] = value;
                    subfilters.push(filter);
                });
                filter["$or"] = subfilters;
            } else {
                if (operator === "$equals") {
                    filter[name] = value;
                } else if (data.isDateRange) {
                    //Once here the value is actually a key of date_range_selector_dom and we need to build a real
                    //filter definition on it.
                    filter[name] = {};
                    filter[name].$dateRange = operator;
                } else {
                    filter[name] = {};
                    filter[name][operator] = value;
                }
            }

            return filter;
        }
    },

    /**
     * Internal function that disposes fields stored in the data attribute of the row el.
     * @param  {jQuery el} $row The row which fields are to be disposed.
     * @param  {array} opts An array of objects, corresponding with the data obj of the row.
     * Example: opts = [{'field': 'nameField', 'value': 'name'},
     {'field': 'operatorField', 'value': 'operator'},
     {'field': 'valueField', 'value': 'value'}]
     */
    _disposeFields: function($row, opts) {
        var data = $row.data(), trigger = false, model;

        if (_.isObject(data) && _.isArray(opts)) {
            _.each(opts, function(val) {
                if (data[val.field]) {
                    //For in between filter we have an array of fields so we need to cover all cases
                    var fields = _.isArray(data[val.field]) ? data[val.field] : [data[val.field]];
                    data[val.value] = "";
                    _.each(fields, function(field) {
                        model = field.model;
                        if (val.field === "valueField" && model) {
                            model.clear({silent: true});
                            trigger = true;
                        }
                        field.dispose();
                        field = null;
                    });
                }
            }, this);
        }
        $row.data(data);

        if (trigger) {
            this.stopListening(model);
            this.fireSearch();
        }
    }
})
