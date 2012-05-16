(function(app) {
    var contextCache = {};

    /**
     * The Context object is a state variable to hold the current application state. The context contains various
     * states of the current {@link View.View View} or {@link View.Layout Layout} -- this includes the current model and collection, as well as the current
     * module focused and also possibly the url hash that was matched.
     *
     * ###Creating a Context Object
     *
     * Use the getContext method to get a new instance of a context.
     * <pre><code>
     * var myContext = SUGAR.app.context.getContext({
     *     module: "Contacts",
     *     url: "contacts/id"
     * });
     * </code></pre>
     *
     * ###Retrieving Data from the Context
     *
     * <pre><code>
     * var module = myContext.get("module"); // module = "Contacts"
     * </pre></code>
     *
     * ###Global Context Object
     *
     * The Application has a global context that applies to top level layer. Contexts used within
     * nested {@link View.View Views} / {@link View.Layout Layouts} can be derived from the global context
     * object.
     *
     *
     * The global context object is stored in **`App.controller.context`**.
     *
     *
     * @class Core.Context
     * @extends Backbone.Model
     */
    app.Context = Backbone.Model.extend({

        initialize: function(attributes) {
            Backbone.Model.prototype.initialize.call(this, attributes);
            this.id = this.cid;
            this.parent = null;
            this.children = [];
        },

        clear: function(options) {
            this.children = [];
            this.parent = null;
            Backbone.Model.prototype.clear.call(this, options);
        },

        // TODO: Do we need this?
//        /**
//         * Changes the focus of the context. Fires the context:focus event.
//         * @param {Object} focus The model / bean to change the focus to
//         * @method
//         */
//        focus: function(focus) {
//            this.trigger("context:focus", focus);
//        }

        /**
         * Gets a related context.
         * @param {Object} def Related context definition.
         * <pre>
         * {
         *    module: module name,
         *    link: link name
         * }
         * </pre>
         * @return {Core.Context} New instance of the child context.
         */
        getChildContext: function(def) {
            var context;

            // Re-use a child context if it already exists
            // We search by either link name or module name
            // Consider refactoring the way we store children: hash v.s. array
            var name = def.link || def.module;
            if (name) {
                context = _.find(this.children, function(child) {
                    return ((child.get("link") == name) || (child.get("module") == name));
                });
            }

            if (!context) {
                context = app.context.getContext(def);
                this.children.push(context);
                context.parent = this;
            }

            if (def.link) {
                var parentModel = this.get("model");
                context.set({
                    parentModel: parentModel,
                    parentModule: parentModel ? parentModel.module : null
                });
            }

            return context;
        },

        /**
         * Prepares instances of model and collection.
         *
         * This method does nothing if this context already contains an instance of a model or a collection.
         * Pass `true` to re-create model and collection.
         *
         * @param {Boolean} force(optional) Flag indicating if data instances must be re-created.
         */
        prepare: function(force) {
            if (!force && (this.get("model") || this.get("collection"))) return;

            var modelId = this.get("modelId"),
                create = this.get("create"),
                link = this.get("link");

            this.set(link ?
                this._prepareRelated(link, modelId, create) :
                this._prepare(modelId, create)
            );

        },

        /**
         * Prepares instances of model and collection.
         *
         * This method assumes that the module name (`module`) is set on the context.
         * If not, instances of standard Backbone.Model and Backbone.Collection are created.
         *
         * @param {String} modelId Bean ID.
         * @param {Boolean} create Create flag.
         * @return {Object} State to set on this context.
         * @private
         */
        _prepare: function(modelId, create) {
            var model, collection,
                module = this.get("module");

            if (modelId) {
                model = app.data.createBean(module, { id: modelId });
                collection = app.data.createBeanCollection(module, [model]);
            } else if (create === true) {
                model = app.data.createBean(module);
                collection = app.data.createBeanCollection(module, [model]);
            } else {
                model = app.data.createBean(module);
                collection = app.data.createBeanCollection(module);
            }

            return {
                collection: collection,
                model: model
            };
        },

        /**
         * Prepares instances of related model and collection.
         *
         * This method assumes that either a parent model (`parentModel`) or
         * parent model ID (`parentModelId`) and parent model module name (`parentModule`) are set on this context.
         *
         * @param {String} link Relationship link name.
         * @param {String} modelId Related bean ID.
         * @param {Boolean} create Create flag.
         * @return {Object} State to set on this context.
         * @private
         */
        _prepareRelated: function(link, modelId, create) {
            var model, collection,
                parentModel = this.get("parentModel");

            parentModel = parentModel || app.data.createBean(this.get("parentModule"), { id: this.get("parentModelId") });
            if (modelId) {
                model = app.data.createRelatedBean(parentModel, modelId, link);
                collection = app.data.createRelatedCollection(parentModel, link, [model]);
            } else if (create === true) {
                model = app.data.createRelatedBean(parentModel, null, link);
                collection = app.data.createRelatedCollection(parentModel, link, [model]);
            } else {
                model = app.data.createRelatedBean(parentModel, null, link);
                collection = app.data.createRelatedCollection(parentModel, link);
            }

            return {
                parentModel: parentModel,
                collection: collection,
                model: model
            };
        },


        /**
         * Loads data (calls fetch on either model or collection).
         */
        loadData: function() {
            if (this.get("create") === true) return;

            var objectToFetch, options = {},
                modelId = this.get("modelId"),
                module = this.get("module"),
                defaultOrdering = (app.config.orderByDefaults && module) ? app.config.orderByDefaults[module] : null;

            objectToFetch = modelId ? this.get("model") : this.get("collection");

            // If we have an orderByDefaults in the config, and this is a bean collection,
            // try to use ordering from there (only if orderBy is not already set.)
            if (defaultOrdering &&
                objectToFetch instanceof app.BeanCollection &&
                !objectToFetch.orderBy)
            {
                objectToFetch.orderBy = defaultOrdering;
            }

            // TODO: Figure out what to do when models are not
            // instances of Bean or BeanCollection. No way to fetch.
            if (objectToFetch && (objectToFetch instanceof app.Bean ||
                objectToFetch instanceof app.BeanCollection)) {

                if (this.get("link")) {
                    options.relate = true;
                }
                if (this.get("layout")) {
                    options.fields = this.get("layout").getFields();
                } else if (this.get("view")) {
                    options.fields = this.get("view").getFields();
                }

                objectToFetch.fetch(options);
            } else {
                app.logger.warn("Skipping fetch because model is not Bean, Bean Collection, or it is not defined.");
            }


            _.each(this.children, function(child) { //TODO optimize for batch
                child.loadData();
            });
        }

    });

    app.augment("context", {

        /**
         * Returns a new instance of the context object.
         * @param {Object} attributes Any parameters and state properties to attach to the context.
         * @return {Core.Context} New context instance.
         * @member Core.Context
         */
        getContext: function(attributes) {
            return new app.Context(attributes);
        }
    });

})(SUGAR.App);
