({
    events: {
        "click .login-submit": "login",
        "keypress": "handleKeypress"
    },
    fallbackFieldTemplate: "modal",

    handleKeypress: function(e) {
        if (e.keyCode === 13) {
            this.$("input").trigger("blur");
            this.login();
        }
    },

    _render: function() {
        if (app.config && app.config.logoURL) {
            this.logoURL = app.config.logoURL;
        }
        app.view.View.prototype._render.call(this);
        this.refreshAddtionalComponents();
        /**
         * Added browser version check for MSIE since we are dropping support
         * for MSIE 9.0 for SugarCon
         */
        if (!this._isSupportedBrowser()) {
            app.alert.show('unsupported_browser', {
                level:'warning',
                title: '',
                messages: [
                    app.lang.getAppString('LBL_ALERT_BROWSER_NOT_SUPPORTED'),
                    app.lang.getAppString('LBL_ALERT_BROWSER_SUPPORT')
                ]
            });
        }
        return this;
    },
    refreshAddtionalComponents: function() {
        _.each(app.additionalComponents, function(component) {
            component.render();
        });
    },
    login: function() {
        var self = this;
        if (this.model.isValid()) {
            app.$contentEl.hide();
            var args = {password: this.model.get("password"), username: this.model.get("username")};

            app.alert.show('login', {level: 'process', title: app.lang.getAppString('LBL_LOADING'), autoClose: false});
            app.login(args, null, {
                error: function() {
                    app.$contentEl.show();
                    app.logger.debug("login failed!");
                },
                success: function() {
                    app.logger.debug("logged in successfully!");
                    app.events.on('app:sync:complete', function() {
                        app.logger.debug("sync in successfully!");
                        this.refreshAddtionalComponents();
                        app.$contentEl.show();
                    }, self);
                },
                complete: function() {
                    app.alert.dismiss('login');
                }
            });
        }
    },

    /**
     * Taken from sugar_3. returns true if the users browser is recognized
     * @return {Boolean}
     * @private
     */
    _isSupportedBrowser:function () {
        var supportedBrowsers = {
            msie:{min:10},
            mozilla:{min:18},
            // For Safari & Chrome jQuery.Browser returns the webkit revision instead of the browser version
            // and it's hard to determine this number.
            safari:{min:536},
            chrome:{min:537}
        };
        for (var b in supportedBrowsers) {
            if ($.browser[b]) {
                var current = parseInt($.browser.version);
                var supported = supportedBrowsers[b];
                return current >= supported.min;
            }
        }
    }
})
