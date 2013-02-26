(function(app) {
    app.events.on("app:init", function() {
        var routes;

        routes = [
            {
                name: "index",
                route: ""
            },
            {
                name: "logout",
                route: "logout/?clear=:clear"
            },
            {
                name: "logout",
                route: "logout"
            },
            {
                name: "bwc",
                route: "bwc/*url",
                callback: function(url) {
                    app.logger.debug("BWC: " + url);

                    var frame = $('#bwc-frame');
                    if (frame.length === 1 &&
                        'index.php' + frame.get(0).contentWindow.location.search === url
                        ) {
                        // update hash link only
                        return;
                    }

                    // if only index.php is given, redirect to Home
                    if (url === 'index.php') {
                        app.router.navigate('#Home', {trigger: true});
                        return;
                    }
                    var params = {
                        layout: 'bwc',
                        url: url
                    };
                    var module = /module=([^&]*)/.exec(url);
                    if (!_.isNull(module) && !_.isEmpty(module[1])) {
                        params.module = module[1];
                    }

                    app.controller.loadView(params);
                }
            },
            {
                name: "list",
                route: ":module"
            },
            {
                name: "listCreate",
                route: ":module/create",
                callback: function(module){
                    app.controller.loadView({
                        module: module,
                        layout: "records"
                    });

                    app.drawer.open({
                        layout:'create',
                        context:{
                            create:true
                        }
                    }, _.bind(function (refresh) {
                        if (refresh) {
                            var collection = app.controller.context.get('collection');
                            if (collection) {
                                collection.fetch();
                            }
                        }
                    }, this));
                }
            },
            {
                name: "layout",
                route: ":module/layout/:view"
            },
            {
                name: "create",
                route: ":module/:id"
            },
            {
                name: "record",
                route: ":module/:id/:action"
            }
        ];

        app.routing.setRoutes(routes);
    });

    /**
     * Performs backward compatibility login.
     *
     * The OAuth token is passed and we do automatic in bwc mode by
     * getting a cookie with the PHPSESSIONID.
     */
    app.bwcLogin = function(redirectUrl) {
        var url = app.api.buildURL('oauth2', 'bwc/login');
        return app.api.call('create', url, {}, {
            success: function() {
                app.router.navigate('#bwc/' + redirectUrl, {trigger: true});
            }
        });
    };

})(SUGAR.App);
