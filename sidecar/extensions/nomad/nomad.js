(function(app) {

    var onPause = function() {
        // This is going to get logged after the app gets resumed
        // See iOS quirks in cordova docs
        app.logger.debug("App was paused");
    };
    var onResume = function(elapsed) {
        app.logger.debug("App resumed after " + elapsed + " seconds");
    };
    var onMemoryWarning = function() {
        app.logger.debug("App received memory warning");
    };

    app.augment("nomad", {

        deviceReady: function() {
            app.isNative = !_.isUndefined(window.cordova);

            var accessToken = null, refreshToken = null;
            var deviceInfo = {};
            var self = this;
            var _ready = function() {
                self._deviceReady(accessToken, refreshToken, deviceInfo);
            };

            deviceInfo.browser = $.browser;
            deviceInfo.os = $.os;

            if (app.isNative) {
                async.waterfall([
                    // Fetch access token
                    function(callback) {
                        window.plugins.keychain.getForKey('AuthAccessToken', 'SugarCRM',
                            function(token) {
                                accessToken = token;
                                callback(null);
                            },
                            function() {
                                callback(null);
                            }
                        );
                    },

                    // Fetch refresh token
                    function(callback) {
                        window.plugins.keychain.getForKey('AuthRefreshToken', 'SugarCRM',
                            function(token) {
                                refreshToken = token;
                                callback(null);
                            },
                            function() {
                                callback(null);
                            }
                        );
                    },

                    // Fetch device info
                    function(callback) {
                        // TODO: Call device info plugin
                        callback(null);
                    }
                ],
                function(err) {
                    if (err) console.log(err);
                    _ready();
                });
            }
            else {
                _ready();
            }

        },

        _deviceReady: function(authAccessToken, authRefreshToken, deviceInfo) {
            app.logger.debug("Device: " + JSON.stringify(deviceInfo));
            app.logger.debug("Layout cache enabled: " + app.config.layoutCacheEnabled);

            if (app.config.layoutCacheEnabled !== true) app.NomadController = null;

            if (app.isNative) {
                app.logger.debug("access/refresh tokens: " + authAccessToken + "/" + authRefreshToken);
                app.OAUTH = {};
                app.OAUTH["AuthAccessToken"] = authAccessToken;
                app.OAUTH["AuthRefreshToken"] = authRefreshToken;
                app.config.authStore = "keychain";

                // TODO KV-NATIVE: Uncomment 'app.nativestore.load' to use native kv store
                //app.nativestore.init();
                //app.cache.store = app.nativestore;
            }

            app.init({el: "#nomad" });
            app.api.debug = app.config.debugSugarApi;

            var startApp = function() {
                app.start();
                app.logger.debug('App started');
            };

            if (app.isNative) {
                // TODO KV-NATIVE: Uncomment 'app.nativestore.load' to use native kv store and comment out startApp
                //app.nativestore.load(startApp);
                startApp();

                document.addEventListener("pause", onPause, false);
                document.addEventListener("resume", onResume, false);
                document.addEventListener("memoryWarning", onMemoryWarning, false);
            }
            else {
                startApp();
            }
        },

        buildLinkRoute: function(moduleOrContext, id, link, relatedId, action) {
            var route = (_.isString(moduleOrContext)) ? moduleOrContext : moduleOrContext.get("module");
            route += "/" + id + "/link/" + link;

            if (relatedId) {
                route += "/" + relatedId;
            }

            if (action) {
                route += "/" + action;
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
                    (relationship = app.metadata.getRelationship([field.relationship])) && // this check is redundant but necessary 'cause currently the server doesn't return all relationships
                    app.data.canHaveMany(model.module, field.name) &&
                    _.has(modules, relationship.lhs_module) &&
                    _.has(modules, relationship.rhs_module));
            });

        },

        /**
         * Shows a confirmation dialog.
         * @param {String} message
         * @param {Function} confirmCallback callback: `function(index)`. Index will be 1 or 2.
         * @param {String} title(optional) Dialog title.
         * @param {String} buttonLabels(optional) Comma-separated two button labels. `Cancel,OK` if not specified.
         */
        showConfirm: function(message, confirmCallback, title, buttonLabels) {
            this._showConfirm(message, confirmCallback, title, buttonLabels || "Cancel,OK");
        },

        /**
         * Displays email chooser UI and pops up native mailer once email is selected.
         * @param {Array/String} emails email or array of emails
         * @param {String} subject(optional) email subject.
         * @param {String} body(optional) email body.
         */
        sendEmail: function(emails, subject, body) {
            if (_.isArray(emails) && emails.length > 1) {
                this._showActionSheet("Select recepient", emails, function(buttonValue, buttonIndex) {
                    if (buttonIndex < emails.length) this._showEmailComposer(subject, body, buttonValue);
                });
            } else {
                this._showEmailComposer(subject, body, this._extractValue(emails));
            }
        },

        /**
         * Displays phone chooser UI and initiates a phone call once a phone is selected.
         *
         * @param {Array/String} phones phone or array of phone objects.
         * Array of phones consists of objects:
         * <pre><code>
         * [
         *   {'Mobile': '(408) 555-7890' },
         *   {'Home': '(650) 333-3456' }
         * ]
         * </code></pre>
         */
        callPhone: function(phones) {
            var self = this;
            if (_.isArray(phones) && phones.length > 1) {
                var numbers = this._buildNamedList(phones);
                this._showActionSheet("Select phone number", numbers, function(buttonValue, buttonIndex) {
                    if (buttonIndex < phones.length) self._callPhone(self._extractValue(phones, buttonIndex));
                });
            } else {
                this._callPhone(this._extractValue(phones));
            }
        },

        /**
         * Displays phone chooser UI and sends SMS once a phone is selected.
         *
         * @param {Array/String} phones phone or array of phone objects.
         * Array of phones consists of objects:
         * <pre><code>
         * [
         *   {'Mobile': '(408) 555-7890' },
         *   {'Home': '(650) 333-3456' }
         * ]
         * </code></pre>
         * @param {String} message(optional) SMS message to send.
         */
        sendSms: function(phones, message) {
            var self = this;
            if (_.isArray(phones) && phones.length > 1){
                var numbers = this._buildNamedList(phones);
                this._showActionSheet("Select phone number", numbers, function(buttonValue, buttonIndex) {
                    if (buttonIndex < phones.length) self._showSmsComposer(self._extractValue(phones, buttonIndex), message);
                });
            } else {
                this._showSmsComposer(this._extractValue(phones), message);
            }
        },

        /**
         * Opens URL in mobile Safari.
         *
         * @param {String/Array} urls URL or array of URL objects.
         * Array of URLs consists of objects:
         * <pre><code>
         * [
         *   {'Corporate site': 'http://example.com' },
         *   {'Other': 'http://example2.com' }
         * ]
         * </code></pre>
         */
        openUrl: function(urls) {
            if (_.isArray(urls) && urls.length > 1){
                var urlNames = _.map(urls, function(item) { return _.keys(item)[0]; });
                var self = this;
                this._showActionSheet("Select URL to open", urlNames, function(buttonValue, buttonIndex) {
                    if (buttonIndex < urls.length){
                        self._browseUrl(self._normalizeUrl(self._extractValue(urls, buttonIndex)));
                    }
                });
            } else {
                this._browseUrl(this._normalizeUrl(this._extractValue(urls)));
            }
        },

        /**
         * Opens native map application to display a physical address.
         *
         * @param {String/Array} address Address or array of address objects.
         * <pre><code>
         * [
         *   {'Billing Address': {street: '360 Acalanes Dr', city: 'Sunnyvale', state: 'CA', postalcode: '94086' }},
         *   {'Shipping Address': {street: '412 Del Medio Ave', city: 'Mountain View', state: 'CA', postalcode: '94040' }}
         * ]
         * </code></pre>
         */
        openAddress: function(addresses) {
            if (_.isArray(addresses) && addresses.length > 1){
                var self = this;
                var locationNames = _.map(addresses, function(item) { return _.keys(item)[0]; });
                this._showActionSheet("Select location to show", locationNames, function(buttonValue, buttonIndex) {
                    if (buttonIndex < addresses.length)
                        self._openGoogleMap(self._extractValue(addresses, buttonIndex));
                });
            } else {
                this._openGoogleMap(this._extractValue(addresses));
            }
        },

        // Generates googlemap URL from location data and opens it in external browser
        _openGoogleMap: function(locationObj) {
            app.logger.debug("Opening map");
            var location = "";
            if (locationObj.street) location += locationObj.street;
            if (locationObj.city) location += (",+" + locationObj.city);
            if (locationObj.state) location += (",+" + locationObj.state);
            if (locationObj.postalcode) location += (",+" + locationObj.postalcode);
            this._browseUrl("http://maps.google.com/maps?q=" + encodeURI(location));
        },

        // Builds an array of named phone numbers: "<phone-name> - <phone-number>"
        _buildNamedList: function(items) {
            return _.map(items, function(item) {
                return _.keys(item)[0] + " - " + _.values(item)[0];
            });
        },

        _extractValue: function(data, index) {
            if (!index) index = 0;
            return _.isString(data) ? data : (_.values(data[index])[0] || data[index].value || data[index]);
        },

        // Pre-pend with 'http://' is absent
        _normalizeUrl: function(url) {
            if ((url.indexOf("http://") == 0) || (url.indexOf("https://") == 0)) return url;
            return "http://" + url;
        }

    });

})(SUGAR.App);