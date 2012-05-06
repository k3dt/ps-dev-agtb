(function(app) {

    /**
     * SugarField widget. A field widget is a low level field widget. Some examples of fields are
     * text boxes, date pickers, drop down menus.
     *
     * ##Creating a SugarField
     * SugarCRM allows for customized "fields" which are visual representations of a type of data (e.g. url would
     * be displayed as a hyperlink).
     *
     * ###Anatomy of a SugarField
     * Field files reside in the **`sugarcrm/clients/base/fields/{field_type}`** folder.
     *
     * Inside the {field_type} directory is a set of files that define templates for different views and field controller.
     * A typical directory structure will look like the following:
     * <pre>
     * clients
     * |- base
     *    |- bool
     *       |- bool.js
     *       |- detail.hbt
     *       |- edit.hbt
     *       |- list.hbt
     *    |- int
     *       ...
     *    |- text
     *       ...
     * |- portal
     *    |- portal specific overrides
     * |- mobile
     *    |- mobile specific overrides
     * </pre>
     * **`[sugarFieldType].js`** files are optional.
     * Sometimes a SugarField needs to do more than just display a simple input element, other times input elements
     * need additional data such as drop down menu choices. To support advanced functionality, just add your additional
     * controller logic to **`[sugarFieldType].js`** javascript file where sugarFieldType is the type of the SugarField.
     * Example for `bool.js` controller:
     * <pre><code>
     * ({
     *    events: {
     *         handler: function() {
     *             // Actions
     *         }
     *    },
     *
     *    initialize: function(options) {
     *       app.view.Field.prototype.initialize(options);
     *       // Your constructor code here follows...
     *    },
     *
     *    unformat: function(value) {
     *        value = this.el.children[0].children[1].checked ? "1" : "0";
     *        return value;
     *    },
     *    format: function(value) {
     *        value = (value == "1") ? true : false;
     *        return value;
     *    }
     * })
     * </code></pre>
     *
     * **`.hbt`** files contain your templates corresponding to the type of {@link View.View} the field is to be displayed on.
     * Sugar uses Handlebars.js as its client side template of choice. At this time no other templating engines are
     * supported. Sample:
     * <pre><code>
     * &lt;span name="{{name}}"&gt;{{value}}&lt;/span&gt;
     * </code></pre>
     *
     * These files will be used by the metadata manager to generate metadata for your SugarFields and pass them onto the
     * Sugar JavaScript client.
     *
     * </pre></code>
     *
     * ####SugarField Template Values
     * TODO:
     *
     *
     * @class View.Field
     */
    app.view.Field = app.view.Component.extend({

        /**
         * HTML tag of the field.
         * @property {String}
         */
        fieldTag: "input",

        /**
         * TODO: add docs (describe options, see Component class for details)
         * @param options
         */
        initialize: function(options) {
            app.view.Component.prototype.initialize.call(this, options);

            // Here options.def is field viewdef (name, type, optional label, etc.)
            _.extend(this, options.def);

            /**
             * ID of the field (autogenerated).
             * @property {Number}
             * @member View.Field
             */
            this.sfId = options.sfId;

            /**
             * Reference to the view this field is attached to.
             * @property {View.View}
             * @member View.Field
             */
            this.view = options.view;

            /**
             * Label key (used for i18n).
             * @property {String}
             * @member View.Field
             */
            this.label = this.label || this.name;

            // this is experimental to try to see if we can have custom events on sugarfields themselves.
            // the following line doesn't work, need to _.extend it or something.
            // this.events = this.meta.events;

            // Set module field definition (vardef)
            if (this.model && this.model.fields) {
                /**
                 * Field metadata (vardef).
                 * @property {Object}
                 * @member View.Field
                 */
                this.fieldDef = this.model.fields[this.name];
            }

            /**
             * Compiled template.
             * @property {Function}
             * @member View.Field
             */
            this.template = app.template.empty;
        },

        /**
         * Loads template for this field.
         * @private
         */
        _loadTemplate: function() {
            var viewFallbackMap = {
                'edit': 'detail'
            };

            // options.viewName is used to override the template
            var viewName = this.options.viewName || this.view.name;
            while (viewName) {
                if (app.acl.hasAccess(viewName, this.model, this.name)) break;
                viewName = viewFallbackMap[viewName];
            }

            if (viewName) {
                this.template = app.template.getField(this.type, viewName) || app.template.empty;
            }
        },

        /**
         * Override default Backbone.Events to also use custom handlers
         * TODO: Convert string function names to references to the callback function
         * The events hash is similar to the backbone events. We store the eventHandlers as
         * part of the SugarField with the `"callback_"` prefix.
         * <pre><code>
         * events: {
         *     handler: "function() {}";
         * }
         * </code></pre>
         * Is stored as:
         * <pre><code>
         * this.callback_handler
         * </code></pre>
         * @private
         * @param {Object} events Hash of events and their handlers
         */
        delegateEvents: function(events) {
            if (!(events || (events = this.events))) {
                return;
            }

            events = _.clone(events);

            _.each(events, function(eventHandler, handlerName) {
                var callback = this[eventHandler];

                // If our callbacks / events have not been registered, go ahead and registered.
                if (!callback && _.isString(eventHandler)) {
                    try {
                        callback = eval("[" + eventHandler + "][0]");
                        // Store this callback if it is a function. Prefix it with "callback_"
                        if (_.isFunction(callback)) {
                            this["callback_" + handlerName] = callback;
                            events[handlerName] = "callback_" + handlerName;
                        }
                    } catch (e) {
                        app.logger.error("invalid event callback " + handlerName + " : " + eventHandler);
                        delete events[handlerName];
                    }
                }

            }, this);

            Backbone.View.prototype.delegateEvents.call(this, events);
        },

        /**
         * Renders the SugarField widget
         * @method
         * @return {Object} this Reference to the SugarField
         */
        render: function() {
            this._loadTemplate();

            if (this.model instanceof Backbone.Model) {
                /**
                 * Model property value.
                 * @property {String}
                 * @member View.Field
                 */
                this.value = this.format(this.model.has(this.name) ? this.model.get(this.name) : "");
            }

            this.$el.html(this.template(this));

            this.bindDomChange(this.model, this.name);

            return this;
        },

        /**
         * Binds DOM changes to set field value on model.
         * @param {Backbone.Model} model model this field is bound to.
         * @param {String} fieldName field name.
         */
        bindDomChange: function(model, fieldName) {
            if (!(model instanceof Backbone.Model)) return;

            var self = this;
            var el = this.$el.find(this.fieldTag);
            // Bind input to the model
            el.on("change", function(ev) {
                model.set(fieldName, self.unformat(el.val()));
            });
        },

        /**
         * Binds render to model changes.
         */
        bindDataChange: function() {
            if (this.model) {
                this.model.on("change:" + this.name, this.render, this);
            }
        },

        /**
         * Formats values for display.
         *
         * This function is meant to be overridden by a sugarFieldType.js controller class.
         * @param {Mixed} value
         * @return {Mixed}
         */
        format: function(value) {
            return value;
        },

        /**
         * Unformats values for display.
         *
         * This function is meant to be overridden by a sugarFieldType.js controller class
         * @param {Mixed} value
         * @return {Mixed}
         */
        unformat: function(value) {
            return value;
        },

        /**
         * Unbinds model event callbacks
         * @method
         */
        unBind: function() {
            //this will only work if all events we listen to, we set the scope to this
            if (this.model && this.model.offByScope) {
                this.model.offByScope(this);
            }

            delete this.model;
        }

    });

})(SUGAR.App);
