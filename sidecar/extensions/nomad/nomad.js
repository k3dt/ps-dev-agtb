(function(app) {

    var _rrh = {

        associate: function(module, id, link) {
            var relatedModule = app.data.getRelatedModule(module, link);
            app.logger.debug("Route changed to associate rels: " + module + "/" + id + "/" + link + "/" + relatedModule);            
            app.controller.loadView({
                module: relatedModule,
                layout: "associate",
                viaLink: link,
                toModule: module,
                toId: id
            });
        },

        list: function(module, id, link) {
            app.logger.debug("Route changed to list rels: " + module + "/" + id + "/" + link);
            app.controller.loadView({
                parentModule: module,
                parentModelId: id,
                link: link,
                layout: "list"
            });
        },

        create: function(module, id, link) {
            app.logger.debug("Route changed to create rel: " + module + "/" + id + "/" + link + "/create");
            app.controller.loadView({
                parentModule: module,
                parentModelId: id,
                link: link,
                create: true,
                layout: "edit"
            });
        },

        record: function(module, id, link, relatedId, action) {
            app.logger.debug("Route changed to action rel: " + module + "/" + id + "/" + link + "/" + relatedId);

            action = action || "detail";

            app.controller.loadView({
                parentModule: module,
                parentModelId: id,
                link: link,
                modelId: relatedId,
                action: action,
                layout: action
            });
        }

    };

    app.events.on("app:init", function() {
        app.metadata.set(app.baseMetadata);
        app.data.declareModels();

        // Register relationship routes
        app.router.route(":module/:id/link/:link", "relationships:list", _rrh.list);
        app.router.route(":module/:id/link/:link/:relatedId", "relationships:detail", _rrh.record);
        app.router.route(":module/:id/link/:link/:relatedId/:action", "relationships:action", _rrh.record);
        app.router.route(":module/:id/link/:link/create", "relationships:create", _rrh.create);
        app.router.route(":module/:id/link/:link/associate", "relationships:associate", _rrh.associate);

        app.api.serverUrl = app.isNative ? app.user.get("serverUrl") : app.config.serverUrl;

        app.logger.debug('App initialized in ' + (app.isNative ? "native shell" : "browser"));
        app.logger.debug('REST URL: ' + app.api.serverUrl);
    });

    app.augment("nomad", {

        deviceReady: function(authToken) {
            app.isNative = !_.isUndefined(window.cordova);
            app.logger.debug("Device is ready, auth-token: " + authToken);

            app.AUTH_ACCESS_TOKEN = authToken;
            app.config.authStore = app.isNative ? 'keychain': 'cache';
            app.init({el: "#nomad" });
            app.api.debug = app.config.debugSugarApi;
            app.start();
            app.logger.debug('App started');
        },

        buildLinkRoute: function(moduleOrContext, id, link, relatedId, action) {
            var route = (_.isString(moduleOrContext)) ? moduleOrContext : moduleOrContext.get("module");
            route += "/" + id + "/link/" + link;

            if (relatedId && action) {
                route += "/" + relatedId + "/" + action;
            }
            else if (relatedId) {
                route += "/" + relatedId;
            }

            return route;
        },

        /**
         * Filters out link fields that support multiple relationships and belong to any module managed by the app.
         * @param {Data.Bean} model Instance of the model to
         * @return {Array} Array of filtered link names.
         */
        getLinks: function (model) {
            var modules = app.metadata.getModuleList();
            return _.filter(model.fields, function (field) {
                var relationship;
                return ((field.type == "link") &&
                   (relationship = model.relationships[field.relationship]) && // this check is redundant but necessary 'cause currently the server doesn't return all relationships
                    app.data.canHaveMany(model.module, field.name) &&
                   (_.any(modules, function(module) {
                        return (module == relationship.lhs_module) ||
                               (module == relationship.rhs_module);
                   })));
            });

        },

        /**
         * Displays email chooser UI.
         * @param {Array} emails
         * @param {String} subject(optional)
         * @param {String} body(optional)
         */
        sendEmail: function(emails, subject, body) {
            // TODO: Implement HTML action sheet view
        },

        /**
         * Displays phone chooser UI.
         * @param {Array} phones
         */
        callPhone: function(phones) {
            // TODO: Implement HTML action sheet view
        },

        /**
         * Displays phone chooser UI.
         * @param {Array} phones
         */
        sendSms: function(phones) {
            // TODO: Implement HTML action sheet view
        }

    });

})(SUGAR.App);