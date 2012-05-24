(function(app) {

    /**
     * Base View class. Use {@link View.ViewManager} to create instances of views.
     *
     * @class View.View
     * @alias SUGAR.App.view.View
     */
    app.view.View = app.view.Component.extend({

        className: "view",

        /**
         * TODO: add docs (describe options parameter, see Component class for an example).
         * @constructor
         * @param options
         */
        initialize: function(options) {
            app.view.Component.prototype.initialize.call(this, options);

            // TODO: Do we need this?
            //_.bindAll(this);

            /**
             * Name of the view (required).
             * @cfg {String}
             */
            this.name = options.name;

            /**
             * Id of the View. Autogenerated if not specified.
             * @property {String}
             * @member {View.View}
             */
            this.id = options.id || this.getID();
            /**
             * Template to render (optional).
             * @cfg {Function}
             */
            this.template = options.template || app.template.getView(this.name, this.module) ||
                            app.template.getView(this.name);

            /**
             * Dictionary of field widgets.
             *
             * - keys: field IDs (sfuuid)
             * - value: instances of `app.view.Field` class
             */
            this.fields = {};

            /**
             * CSS class.
             *
             * CSS class which is specified as the `className` parameter
             * in `params` hash for {@link View.ViewManager#createView} method.
             *
             * By default the view is rendered as `div` element with CSS class `"view <viewName>"`.
             * @cfg {String} className
             */
            this.$el.addClass(options.className || this.name || "");

            /**
             * A template to use for view fields if a field does not have a template defined for its parent view.
             * Defaults to `"default"`.
             *
             * For example, if you have a subview and don't want to define subview template for all field types,
             * you may choose to use existing templates like `detail` if your subview is in fact a detail view.
             *
             * @property {String}
             */
            this.fallbackFieldTemplate = "default";

            /**
             * Reference to the parent layout instance.
             * @property {View.Layout}
             */
            this.layout = this.options.layout;
        },

        /**
         * Renders a view for the given context.
         *
         * This method uses this view's {@link View.View#template} property to render itself.
         * @param ctx Template context.
         * @protected
         */
        _renderWithContext: function(ctx) {
            if (this.template) {
                try {
                    this.$el.html(this.template(ctx));
                } catch (e) {
                    app.logger.error("Failed to render " + this + "\n" + e);
                    // TODO: trigger app event to render an error message
                }
            }
        },

        /**
         * Renders the view onto the page.
         *
         * This method uses this view as the context for the view's Handlebars {@link View.View#template}.
         * You can override this method if you have custom rendering logic and don't use Handlebars templating
         * or if you need to pass different context object for the template.
         *
         * Example:
         * <pre><code>
         * app.view.views.CustomView = app.view.View.extend({
         *    _render: function() {
         *      var customCtx = {
         *         // Your custom context for this view template
         *      };
         *      this._renderWithContext(customCtx);
         *    }
         * });
         *
         * // Or totally different logic that doesn't use this.template
         * app.view.views.AnotherCustomView = app.view.View.extend({
         *    _render: function() {
         *       // Never do this :)
         *       return "&lt;div&gt;Hello, world!&lt;/div&gt;";
         *    }
         * });
         *
         *
         * </code></pre>
         * @protected
         */
        _render: function() {
            this._renderWithContext(this);
        },

        /**
         * Renders a field.
         *
         * This method sets field's view element and invokes render on the given field.
         * @param {View.Field} field The field to render
         * @protected
         */
        _renderField: function(field) {
            field.setElement(this.$("span[sfuuid='" + field.sfId + "']"));
            try {
                field.render();
            } catch (e) {
                app.logger.error("Failed to render " + field + " on " + this + "\n" + e);
                // TODO: trigger app event to render an error message
            }
        },

        /**
         * Renders the view onto the page.
         * See Backbone.View documentation for details.
         * @return {Object} Reference to this view.
         */
        render: function() {
            if (app.acl.hasAccess(this.name, this.module)) {
                this._render();
                // Render will create a placeholder for sugar fields. we now need to populate those fields
                _.each(this.fields, function(field) {
                    this._renderField(field);
                }, this);
            } else {
                app.logger.info("Current user does not have access to this module view.");
                //TODO trigger app event to notify user about no access or render a "no access" template
            }

            return this;
        },

        /**
         * Extracts the field names from the metadata for directly related views/panels.
         * @param {String} module(optional) Module name.
         * @return {Array} List of fields used on this view
         */
        getFieldNames: function(module) {
            var fields = [];
            module = module || this.context.get('module');

            if (this.meta && this.meta.panels) {
                _.each(this.meta.panels, function(panel) {
                    fields = fields.concat(_.pluck(panel.fields, 'name'));
                });
            }

            fields = _.compact(_.uniq(fields));

            var fieldMetadata = app.metadata.getModule(module, 'fields');
            if (fieldMetadata) {
                // Filter out all fields that are not actual bean fields
                fields = _.reject(fields, function(name) {
                    return _.isUndefined(fieldMetadata[name]);
                });

                // we need to find the relates and add the actual id fields
                var relates = [];
                _.each(fields, function(name) {
                    if (fieldMetadata[name].type == 'relate') {
                        relates.push(fieldMetadata[name].id_name);
                    }
                });

                fields = fields.concat(relates);
            }

            return fields;
        },

        /**
         * Gets a hash of fields that are currently displayed on this view.
         *
         * The hash has field names as keys and field definitions as values.
         * @param {String} module(optional) Module name.
         * @return {Object} The currently displayed fields.
         */
        getFields: function(module) {
            var fields = {};
            var fieldNames = this.getFieldNames(module);
            _.each(this.fields, function(field) {
                if (_.include(fieldNames, field.name)) {
                    fields[field.name] = field.def;
                }
            });
            return fields;
        },

        /**
         * Returns the html id of this view's el. Will create one if one doesn't exist.
         * @return {String} id of this view.
         */
        getID: function() {
            return (this.id || this.module || "") + "_" + this.name;
        },

        /**
         * Gets a string representation of this view.
         * @return {String} String representation of this view.
         */
        toString: function() {
            return "view-" + this.name + "-" + app.view.Component.prototype.toString.call(this);
        }

    });


})(SUGAR.App);