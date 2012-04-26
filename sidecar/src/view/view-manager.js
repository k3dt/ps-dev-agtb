/**
 * View manager is used to create views, layouts, and fields based on optional metadata inputs.
 *
 * The view manager's factory methods (`createView`, `createLayout`, and `createField`) first checks
 * `views`, `layouts`, and `fields` hashes respectively for custom class declaration before falling back the base class.
 *
 * Put declarations of your custom views, layouts, fields in the corresponding hash:
 * <pre><code>
 * app.view.views.MyCustomView = app.view.View.extend({
 *  // Put your custom logic here
 * });
 *
 * app.view.views.MyCustomLayout = app.view.Layout.extend({
 *  // Put your custom logic here
 * });
 *
 * app.view.views.MyCustomField = app.view.Field.extend({
 *  // Put your custom logic here
 * });
 *
 * </code></pre>
 *
 *
 * @class View.ViewManager
 * @alias SUGAR.App.view
 * @singleton
 */
(function(app) {

    // Create a new subclass of the given super class based on the controller definition passed.
    var _declareClass = function(cache, base, className, controller) {
        var klass = null;
        var evaledController = null;
        if (controller) {
            try {
                evaledController = eval("(" + controller + ")");
            } catch (e) {
                app.logger.error("Failed to eval view controller for " + className + ": " + e + ":\n" + controller);
            }
        }

        if (_.isObject(evaledController)) {
            klass = cache[className] = base.extend(evaledController);
        }

        return klass;
    };

    var _viewManager = {

        /**
         * Hash of view classes.
         */
        views: {},
        /**
         * Hash of layout classes.
         */
        layouts: {},
        /**
         * Hash of field classes.
         */
        fields: {},

        _createComponent: function(type, name, params) {
            var className = app.utils.capitalize(name) + type;
            var customClassName = (params.module || "") + className;
            var cache = this[type.toLowerCase() + "s"];
            var controller = params.meta ? params.meta.controller : null;
            var baseClass = app.view[type];
            var klass =
                // First check if custom class per module already exists
                cache[customClassName] ||
                // Fall back to base views
                cache[className] ||
                // Otherwise, create custom class if the metadata has a controller
                _declareClass(cache, baseClass, customClassName, controller) ||
                // Fall back to regular view class (ListView, FluidLayout, etc.)
                cache[className] ||
                // Fall back to base class (View, Layout, or Field)
                baseClass;

            return new klass(params);
        },

        /**
         * Creates an instance of {@link View.View} class.
         *
         * Parameters define creation rules as well as view properties.
         * The `param` hash must contain at least `name` property which is a view name.
         * Other parameters may be:
         *
         * - context: context to associate with the newly created view
         * - module: module name
         * - meta: custom metadata
         *
         * If context is not specified the controller's current context is assigned to the view (`SUGAR.App.controller.context`).
         *
         * Examples:
         *
         * * Create a list view. The view manager will use metadata for the view named 'list' defined in Contacts module.
         * The controller's current context will be set on the new view instance.
         * <pre><code>
         * var listView = app.view.createView({
         *    name: 'list',
         *    module: 'Contacts'
         * });
         * </code></pre>
         *
         * * Create a custom view class.
         * <pre><code>
         * // Declare your custom view class.
         * app.view.views.MyCustomView = app.view.View.extend({
         *  // Put your custom logic here
         * });
         *
         * var myCustomView = app.view.createView({
         *    name: 'myCustom'
         * });
         * </code></pre>
         *
         * * Create a view with custom metadata payload.
         * <pre><code>
         * var view = app.view.createView({
         *     name: 'detail',
         *     meta: { ... some custom metadata ... }
         * });
         * </code></pre>
         *
         * @param params view parameters
         * @return {View.View} new instance of view.
         */
        createView: function(params) {
            params.context = params.context || app.controller.context; // context is always defined on the controller
            params.module = params.module || params.context.get("module");
            params.meta = params.meta || app.metadata.getView(params.module, params.name);
            return this._createComponent("View", params.name, params);
        },

        createLayout: function(params) {
            var clonedParams = _.clone(params);
            clonedParams.module = params.module || params.context.get("module");
            clonedParams.meta = params.meta || app.metadata.getLayout(clonedParams.module, params.name) || {};

            clonedParams.meta.type = clonedParams.meta.type || clonedParams.name;
            clonedParams.name = clonedParams.name || clonedParams.meta.type;

            return this._createComponent("Layout", clonedParams.meta.type, clonedParams);
        },

        /**
         * Creates an instance of {@link View.Field} class.
         *
         * The parameters define creation rules as well as field properties.
         * The `params` hash must contain `def` property which is the field definition and `view`
         * property which is the reference to the parent view. For example,
         * <pre>
         * var params = {
         *    view: new Backbone.View,
         *    def: {
         *      type: 'text',
         *      name: 'first_name',
         *      label: 'LBL_FIRST_NAME'
         *    },
         *    context: optional context (if not specified, app.controller.context is used)
         *    model: optional model (if not specified, the model which is set on the context is used)
         *    meta: optional custom metadata
         * }
         * </pre>
         *
         * View manager queries metadata manager for field type specific metadata (templates and JS controller) unless custom metadata
         * is passed in the `params` hash.
         *
         * To create instances of custom fields, first declare its class in `app.view.fields` hash:
         * <pre><code>
         * app.view.views.MyCustomField = app.view.Field.extend({
         *  // Put your custom logic here
         * });
         *
         * var myCustomField = app.view.createField({
         *   view: someView,
         *   def: {
         *      type: 'myCustom',
         *      name: 'my_custom'
         *   }
         * });
         * </code></pre>
         *
         * @param params field parameters.
         * @return {View.Field} a new instance of field.
         */
        createField: function(params) {
            var type = params.def.type;
            params.meta = params.meta || app.metadata.getField(type);
            params.context = params.context || app.controller.context;
            params.model = params.model || params.context.get("model");
            return this._createComponent("Field", type, params);
        }

    };

    app.augment("view", _viewManager, false);

})(SUGAR.App);