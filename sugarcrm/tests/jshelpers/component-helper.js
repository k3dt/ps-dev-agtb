(function(test) {
    var app = SUGAR.App;
    test.loadComponent = function(client, type, name, module) {
        var path = "/clients/" + client + "/" + type + "s/" + name;
        path = (module) ? "../modules/" + module + path : ".." + path;

        SugarTest.loadFile(path, name, "js", function(data) {
            try {
                data = eval("[" + data + "][0]");
            } catch (e) {
                app.logger.error("Failed to eval view controller for " + name + ": " + e + ":\n" + data);
            }
            app.view.declareComponent(type, name, module, data, true);
            test.testMetadata.addController(name, type, data, module);
        });

    };

    test.loadHandlebarsTemplate = function(name, type, client, template, module) {
        var templateName = template || name;
        var path = "/clients/" + client + "/" + type + "s/" + name;
        path = (module) ? "../modules/" + module + path : ".." + path;
        SugarTest.loadFile(path, templateName, "hbt", function(data) {
            test.testMetadata.addTemplate(name, type, data, templateName, module);
        });
    };

    test.createField = function(client, name, type, viewName, fieldDef, module, model, context) {
        test.loadComponent(client, "field", type);

        var view = new app.view.View({ name: viewName, context: context });
        var def = { name: name, type: type, events: (fieldDef) ? fieldDef.events : {} };
        var context = context || app.context.getContext();

        model = model || new Backbone.Model();

        if (fieldDef) {
            model.fields = {};
            model.fields[name] = fieldDef;
        }

        return app.view.createField({
            def: def,
            view: view,
            context: context,
            model: model
        });
    };

    test.createView = function(client, module, viewName, meta, context, loadFromModule, layout) {
        if (loadFromModule) {
            test.loadComponent(client, "view", viewName, module);
        } else {
            test.loadComponent(client, "view", viewName);
        }
        if (!context) {
            context = app.context.getContext();
            context.set({
                module: module
            });
            context.prepare();
        }

        return app.view.createView({
            name : viewName,
            context : context,
            module : module,
            meta : meta,
            layout: layout
        });
    };

    test.createLayout = function(client, module, layoutName, meta, context, loadFromModule) {
        if (loadFromModule) {
            test.loadComponent(client, "layout", layoutName, module);
        } else {
            test.loadComponent(client, "layout", layoutName);
        }
        if (!context) {
            context = app.context.getContext();
            context.set({
                module: module,
                layout: layoutName
            });
            context.prepare();
        }

        return app.view.createLayout({
            name : layoutName,
            context : context,
            module : module,
            meta : meta
        });
    };

    test.testMetadata = {
        _data: null,

        init: function() {
            this._data = $.extend(true, {}, fixtures.metadata);
            this._data.layouts = this._data.layouts || {};
            this._data.views = this._data.views || {};
            this._data.fields = this._data.fields || {};
        },

        addController: function(name, type, controller, module) {
            type = type + 's';
            if (this.isInitialized()) {
                if (module) {
                    this._initModuleStructure(module, type, name);
                    this._data.modules[module][type][name].controller = controller;
                } else {
                    this._data[type][name] = this._data[type][name] || {};
                    this._data[type][name].controller = controller;
                }
            }
        },

        addTemplate: function(name, type, template, templateName, module) {
            type = type + 's';
            if (this.isInitialized()) {
                if (module) {
                    this._initModuleStructure(module, type, name);
                    this._data.modules[module][type][name].template = template;
                } else {
                    this._data[type][name] = this._data[type][name] || {};
                    this._data[type][name].templates = this._data[type][name].templates || {};
                    this._data[type][name].templates[templateName] = template;
                }
            }
        },

        addViewDefinition: function(name, viewDef, module) {
            this._addDefinition(name, 'views', viewDef, module);
        },

        addLayoutDefinition: function(name, layoutDef, module) {
            this._addDefinition(name, 'layouts', layoutDef, module);
        },

        _initModuleStructure: function(module, type, name) {
            this._data.modules[module] = this._data.modules[module] || {};
            this._data.modules[module][type] = this._data.modules[module][type] || {};
            this._data.modules[module][type][name] = this._data.modules[module][type][name] || {};
        },

        _addDefinition: function(name, type, layoutDef, module) {
            if (this.isInitialized()) {
                if (module) {
                    this._initModuleStructure(module, type, name);
                    this._data.modules[module][type][name].meta = layoutDef;
                } else {
                    this._data[type][name] = this._data[type][name] || {};
                    this._data[type][name].meta = layoutDef;
                }
            }
        },

        set: function() {
            if (this.isInitialized()) {
                _.each(this._data.modules, function(module) {
                    module._patched = false;
                });
                SugarTest.app.metadata.set(this._data, true, true);
            }
        },

        revert: function() {
            if (this.isInitialized()) {
                SugarTest.app.metadata.set(fixtures.metadata, true, true);
            }
        },

        dispose: function() {
            this.revert();
            this._data = null;
        },

        isInitialized: function() {
            if (this._data) {
                return true;
            } else {
                return false;
            }
        },

        get: function() {
            return this._data;
        }
    };
}(SugarTest));
