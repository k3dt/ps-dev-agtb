(function(app) {
    var sfid = 0;

    //Register Handlebars helper to create fields with unique id's
    Handlebars.registerHelper('sugar_field', function (context, view, bean) {
        var ret = '<span sfuuid="' + (++sfid) + '"></span>',
            name = this.name,
            label = this.label || this.name,
            def = this,
            sf;

        bean = bean || context.get("model");

        if (bean.fields && bean.fields[name]) {
            def = bean.fields[name];
        }

        sf = view.sugarFields[sfid] || (view.sugarFields[sfid] = app.sugarFieldManager.get({
            def: def,
            view : view,
            context : context,
            label: label,
            model : bean || context.get("model")
        }));

        sf.sfid = sfid;

        return new Handlebars.SafeString(ret);
    });

    /**
     * SugarField widget. A sugarfield widget is a low level field widget. Some examples of sugarfields are
     * text boxes, date pickers, drop down menus.
     * @class SugarField
     */
    app.augment('SugarField', Backbone.View.extend({
        /**
         * Reference to the application
         * @property {Object}
         */
        app: app,

        /**
         * Id of the SugarField
         * TODO: This is a shared property on the SugarField
         * @property {Number}
         */
        sfid: -1,

        initialize: function(options) {
            var templateKey;
            _.extend(this, options.def);

            this.view = options.view;
            this.label = options.label;
            this.bind(options.context, options.model || options.context.get("model"));
            this.viewName = this.view.name;
            this.meta = app.metadata.get({sugarField:this});

            templateKey = "sugarField." + this.name + "." + this.view.name;

            this.templateC = app.template.get(templateKey) || app.template.compile(this.meta.template, templateKey);
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
        delegateEvents : function(events) {
            if (!(events || (events = this.events))) {
                return;
            }

            events = _.clone(events);

            _.each(events, function(eventHandler, handlerName) {
                var callback = this[eventHandler];

                // If our callbacks / events have not been registered, go ahead and registered.
                if (!callback && _.isString(eventHandler)) {
                    try {
                        callback = eval("(" + eventHandler + ")");

                        // Store this callback if it is a function. Prefix it with "callback_"
                        if (_.isFunction(callback)) {
                            this["callback_" + handlerName] = callback;
                            events[handlerName] = "callback_" + handlerName;
                        }
                    } catch(e) {
                        app.logger.error("invalid event callback " + handlerName + " : " + eventHandlerG);
                        delete events[handlerName];
                    }
                }

            }, this);

            Backbone.View.prototype.delegateEvents.call(this, events);
        },

        /**
         * Renders the SugarField widget
         * TODO: Seems like we are rendering too many times, maybe add some checks for data / state before rendering
         * @method
         * @return {Object} this Reference to the SugarField
         */
        render: function() {
            // If we don't have any data in the model yet
            if (_.isEmpty(this.model.attributes)) {
                return null;
            }

            this.value = this.model.has(this.name) ? this.model.get(this.name) : "";
            this.$el.html(this.templateC(this));

            var model = this.model;
            var field = this.name;
            var el = this.$el.find("input");

            //Bind input to the model
            el.on("change", function(ev) {
                model.set(field, el.val());
            });

            //And bind the model to the input
            model.on("change:" + field, function(model, value) {
                if (el[0] && el[0].tagName.toLowerCase() == "input")
                    el.val(value);
                else
                    el.html(value);
            });

            return this;
        },

        /**
         * Binds render to model changes
         * @param {Context} context
         * @param {Bean} model Data to bind the sugarfield to
         */
        bind: function(context, model) {
            this.unBind();
            this.context = context;
            this.model = model;

            if (this.model.on){
                this.model.on("change:" + this.name, this.render, this);
            }
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
            delete this.context;
        }
    }));
}(SUGAR.App));