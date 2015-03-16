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
(function(app) {
    app.events.on('app:init', function() {

        app.utils = _.extend(app.utils, {

            'GlobalSearch': {

                /**
                 * Formats models returned by the globalsearch api.
                 *
                 * @param {Data.BeanCollection} collection The collection of models to format.
                 * @param {boolean} linkableHighlights Whether the highlighted fields' `link` flag should be `true` or
                 *   not.
                 */
                formatRecords: function(collection, linkableHighlights) {
                    collection.each(function(model) {
                        if (model.formatted) {
                            return;
                        }
                        var module = app.metadata.getModule(model.get('_module'));
                        var nameFormatValues = _.values(module.nameFormat);
                        var personModuleType = !!nameFormatValues.length;
                        var attrs = {};
                        _.each(model.toJSON(), function(val, key) {
                            attrs[key] = Handlebars.Utils.escapeExpression(val);
                        });

                        // If it's a Person module type, we build the name and
                        // set it in the model.
                        if (personModuleType && !model.get('name')) {
                            var name = new Handlebars.SafeString(
                                app.utils.formatNameModel(model.get('_module'), attrs));

                            model.set('name', name);
                        }

                        var highlights = _.map(model.get('_highlights'), function(val, key) {
                            val = new Handlebars.SafeString(_.first(val));
                            attrs[key] = val;
                            return {
                                name: key,
                                value: val,
                                label: module.fields[key].vname,
                                link: linkableHighlights,
                                highlighted: true
                            };
                        });
                        // For Person module type we build the name and push
                        // it in the highlights.
                        if (personModuleType) {
                            var personName = new Handlebars.SafeString(
                                    app.utils.formatNameModel(model.get('_module'), attrs));

                            highlights.push({
                                name: 'name',
                                value: personName,
                                label: module.fields.name.vname,
                                link: linkableHighlights,
                                highlighted: true
                            });
                        }

                        model.set('_highlights', highlights);

                        // We add a flag here so that when the user clicks on
                        // `More results...` we won't reformat the existing ones.
                        model.formatted = true;
                    });
                },

                /**
                 * Gets the view metadata from the given module, patches it to distinguish
                 * primary fields from secondary fields and disables the native inline
                 * ellipsis feature of fields.
                 *
                 * @param {string} module The module to get the metadata from.
                 * @param {Object} [options]
                 * @param {boolean} [options.linkablePrimary] Set to `false` if you want to
                 *   force preventing the primary fields from containing a link.
                 * @return {Object} The metadata object.
                 */
                getFieldsMeta: function(module, options) {
                    options = options || {};
                    var fieldsMeta = {};
                    var meta = _.extend({}, this.meta, app.metadata.getView(module, 'search-list'));
                    _.each(meta.panels, function(panel) {
                        if (panel.name === 'primary') {
                            fieldsMeta.primaryFields = this._setFieldsCategory(panel.fields, 'primary', options);
                        } else if (panel.name === 'secondary') {
                            fieldsMeta.secondaryFields = this._setFieldsCategory(panel.fields, 'secondary', options);
                        }
                    }, this);
                    fieldsMeta.rowactions = meta.rowactions;

                    return fieldsMeta;
                },

                /**
                 * Sets `primary` or `secondary` boolean to fields. Also, we set the
                 * `ellipsis` flag to `false` so that the field doesn't render in a div with
                 * the `ellipsis_inline` class.
                 *
                 * @param {Object} fields The fields.
                 * @param {String} category The field category. It can be `primary` or
                 *   `secondary`.
                 * @param {Object} [options] See {@link #getFieldsMeta} options param for the
                 *   list of available options.
                 * @return {Object} The enhanced fields object.
                 * @private
                 */
                _setFieldsCategory: function(fields, category, options) {
                    var fieldList = {};

                    _.each(fields, function(field) {
                        if (!fieldList[field.name]) {
                            fieldList[field.name] = _.extend({}, fieldList[field.name], field);
                        }
                        fieldList[field.name][category] = true;
                        fieldList[field.name].ellipsis = false;
                        if (category === 'primary' && options.linkablePrimary === false) {
                            fieldList[field.name].link = false;
                        }
                        if (category === 'secondary') {
                            fieldList[field.name].link = false;
                            if (field.type === 'email') {
                                fieldList[field.name].emailLink = false;
                            }
                        }
                    });

                    return fieldList;
                },

                /**
                 * Adds `highlighted` attribute to fields sent as `highlights` by the
                 * globalsearch API for a given model.
                 *
                 * This method clones viewdefs fields and replace them by
                 * the highlighted fields sent by the API.
                 *
                 * @param {Data.Bean} model The model.
                 * @param {Object} viewDefs The view definitions of the fields.
                 *   Could be definition of primary fields or secondary fields.
                 * @param {boolean} [add=false] `true` to add in the viewdefs the highlighted
                 *   fields if they don't already exist. `false` to skip them if they don't
                 *   exist in the viewdefs.
                 */
                highlightFields: function(model, viewDefs, add) {
                    //The array of highlighted fields
                    var highlighted = model.get('_highlights');
                    //The fields vardefs of the model.
                    var varDefs = model.fields;
                    viewDefs = _.clone(viewDefs);

                    _.each(highlighted, function(field) {
                        var hasViewDefs = viewDefs[field.name]; // covers patching existing.
                        var addOrPatchExisting = (hasViewDefs || add); // shall we proceed.

                        // We want to patch the field def only if there is an existing
                        // viewdef for this field or if we want to add it if it doesn't exist
                        // (This is the case for secondary fields).
                        if (!addOrPatchExisting) {
                            return;
                        }

                        // For person type modules.
                        //nameFormatValues is equal to
                        // ["first_name", "last_name", "salutation", "title"] if
                        // model.module is a Person type. It's an empty array otherwise.
                        var nameFormatValues = _.values(app.metadata.getModule(model.module).nameFormat);
                        // We skip the highlight if the field is part of the `name` field.
                        if (_.contains(nameFormatValues, field.name) && !hasViewDefs) {
                            return;
                        }

                        // Checks if the model has the field in its primary fields, if it
                        // does, we don't patch the field def because we don't want it to
                        // be in both secondary and primary fields.
                        if (!_.isUndefined(model.primaryFields) && model.primaryFields[field.name]) {
                            return;
                        }
                        viewDefs[field.name] = _.extend({}, varDefs[field.name], viewDefs[field.name], field);
                    });
                    return viewDefs;
                }
            }
        });
    });
})(SUGAR.App);
