/*
 * SugarCRM Javascript API
 */

//create the SUGAR namespace if one does not exist already
var SUGAR = SUGAR || {};

/**
 * SugarCRM Javascript API allows users to interact with SugarCRM instance via its REST interface.
 *
 * Use {@link SugarApi#getInstance} method to create instances of Sugar API.
 * This method accepts arguments object with the following properties:
 * <pre>
 * {
 *   serverUrl: Sugar REST URL end-point
 *   platform: platform name ("portal", "mobile", etc.)
 *   keyValueStore: reference to key/value store provider used to read/save auth token from/to
 * }
 * </pre>
 *
 * The key/value store provider must implement three methods:
 * <pre><code>
 *   set: void function(String key, String value)
 *   get: String function(key)
 *   cut: void function(String key)
 * </code></pre>
 * The authentication tokens are kept in memory if the key/value store is not specified.
 *
 * Most of Sugar API methods accept `callbacks` object:
 * <pre>
 * {
 *   success: function(data) { }
 *   error: function(xhr) { }
 * }
 * </pre>
 *
 * @class SugarApi
 * @singleton
 * @alias SUGAR.Api
 */
SUGAR.Api = (function() {
    var _instance;
    var _methodsToRequest = {
            "read": "GET",
            "update": "PUT",
            "create": "POST",
            "delete": "DELETE"
        };
    var _baseActions = ["read", "update", "create", "delete"];

    /**
     * @constructor
     * @param args API options.
     * @private
     * @ignore
     */
    function SugarApi(args) {
        var _serverUrl, _platform, _keyValueStore;
        var _accessToken = null;
        //var _refreshToken = null; // reserved for the future use

        // if no key/value store is provided, the auth token is kept in memory
        _keyValueStore = args && args.keyValueStore;
        _serverUrl = (args && args.serverUrl) || "/rest/v10";
        _platform = (args && args.platform) || "";
        if (_keyValueStore) {
            if (!$.isFunction(_keyValueStore.set) ||
                !$.isFunction(_keyValueStore.get) ||
                !$.isFunction(_keyValueStore.cut))
            {
                throw new Error("Failed to initialize Sugar API: key/value store provider is invalid");
            }
            _accessToken = _keyValueStore.get("AuthAccessToken");
            //_refreshToken = _keyValueStore.get("AuthRefreshToken");
        }

        function _resetAuth(data) {
            // data is the response from the server
            if (data) {
                _accessToken = data.token;
                if (_keyValueStore) _keyValueStore.set("AuthAccessToken", _accessToken);
                //_refreshToken = data.refreshToken;
                //if (_keyValueStore) _keyValueStore.set("AuthRefreshToken", _refreshToken);
            }
            else {
                _accessToken = null;
                if (_keyValueStore) _keyValueStore.cut("AuthAccessToken");
                //_refreshToken = null;
                //if (_keyValueStore) _keyValueStore.remove("AuthRefreshToken");
            }
        }

        return {

            /**
             * URL of Sugar REST end-point.
             * @property {String}
             */
            serverUrl: _serverUrl,

            /**
             * Flag indicating if API should run in debug mode (console debugging of API calls).
             * @property {Boolean}
             */
            debug: false,

            /**
             * Makes AJAX call via jquery/zepto AJAX API.
             *
             * @param  {String} method CRUD action to make (read, create, update, delete) are mapped to corresponding HTTP verb: GET, POST, PUT, DELETE.
             * @param  {String} url resource URL.
             * @param  {Object} data(optional) data will be stringified into JSON and set to request body.
             * @param  {Object} callbacks(optional) callbacks object.
             * @param  {Object} options(optional) options for request that map directly to the jquery/zepto Ajax options.
             * @return {Object} XHR request object.
             * @private
             */
            call: function(method, url, data, callbacks, options) {
                var i, server;
                var type = _methodsToRequest[method];

                // by default use json headers
                var params = {type: type, dataType: 'json', headers: {}};

                options = options || {};
                callbacks = callbacks || {};

                // if we dont have a url from options take arg url
                if (!options.url) {
                    params.url = url;
                }

                //add callbacks
                if (callbacks.success) {
                    params.success = callbacks.success;
                }

                if (callbacks.error) {
                    params.error = callbacks.error;
                }

                if (_accessToken) {
                    params.headers["OAuth-Token"] = _accessToken;
                }

                if ((method == 'read') && data && data.date_modified) {
                    params.headers["If-Modified-Since"] = data.date_modified;
                }

                // set data for create and update
                if (data && (method == 'create' || method == 'update')) {
                    params.contentType = 'application/json';
                    params.data = JSON.stringify(data);
                }

                // Don't process data on a non-GET request.
                if (params.type !== 'GET') {
                    params.processData = false;
                }

                if (this.debug) {
                    console.log("====== Ajax Request Begin ======");
                    console.log("Request URL: " + url);
                    console.log("Request Type: " + type);
                    console.log("Payload: ", data);
                    console.log("options: ", params);
                    console.log("callbacks: ", callbacks);
                    console.log("====== Request End ======");
                }

                if (SUGAR.demoRestServer && SUGAR.restDemoData) {
                    for (i = 0; i < SUGAR.restDemoData.length; i++) {
                        if (SUGAR.restDemoData[i].route.test(url)) {
                            console.log("===Matched demo server route starting demo server===");
                            console.log("====== url ======");
                            console.log(url);
                            console.log("====== ds route ======");
                            console.log(SUGAR.restDemoData[i].route);
                            server = SUGAR.demoRestServer();
                        }
                    }
                }

                // Make the request, allowing override of any Ajax options.
                var result = $.ajax(_.extend(params, options));


                if (SUGAR.demoRestServer && SUGAR.restDemoData) {
                    for (i = 0; i < SUGAR.restDemoData.length; i++) {
                        if (SUGAR.restDemoData[i].route.test(url)) {
                            console.log("===Demo Server Responding and Restoring===");
                            server.respond();
                            server.restore();
                        }
                    }
                }

                return result;
            },

            /**
             * Builds URL based on module name action and attributes of the format rooturl/module/id/action.
             *
             * The `attributes` hash must contain `id` of the resource being actioned upon
             * for record CRUD and `relatedId` if the URL is build for relationship CRUD.
             *
             * @param  {String} module module name.
             * @param  {String} action CRUD method.
             * @param  {Object} attributes(optional) object of resource being actioned upon, e.g. `{name: "bob", id:"123"}`.
             * @param  {Object} params(optional) URL parameters.
             * @return {String} URL for specified resource.
             * @private
             */
            buildURL: function(module, action, attributes, params) {
                params = params || {};
                var parts = [];
                var url;
                parts.push(this.serverUrl);

                if (module) {
                    parts.push(module);
                }

                if ((action != "create") && attributes && attributes.id) {
                    parts.push(attributes.id);
                }

                if (attributes && attributes.link) {
                    parts.push('link');
                }

                if (action && $.inArray(action, _baseActions) === -1) {
                    parts.push(action);
                }

                if (attributes && attributes.relatedId) {
                    parts.push(attributes.relatedId);
                }

                url = parts.join("/");

                // URL parameters
                params = $.param(params);
                if (params.length > 0) {
                    url += "?" + params;
                }

                return url;
            },

            /**
             * Fetches metadata.
             *
             * @param  {Array} types(optional) array of metadata types, e.g. `['vardefs','detailviewdefs']`.
             * @param  {Array} modules(optional) array of module names, e.g. `['accounts','contacts']`.
             * @param  {Object} callbacks(optional) callback object.
             * @return {Object} XHR request object.
             */
            getMetadata: function(types, modules, callbacks) {
                var params = {};
                if (types) {
                    params.typeFilter = types.join(",");
                }

                if (modules) {
                    params.moduleFilter = modules.join(",");
                }

                if (_platform) {
                    params.platform = _platform;
                }

                var method = 'read';
                var url = this.buildURL("metadata", method, null, params);
                return this.call(method, url, null, callbacks);
            },

            /**
             * Executes CRUD on records.
             *
             * @param {String} method operation type: create, read, update, or delete.
             * @param {String} module module name.
             * @param {Object} data object to pass in the request body.
             * @param {Object} params(optional) URL parameters.
             * @param {Object} callbacks(optional) callback object.
             * @return XHR request object.
             */
            records: function(method, module, data, params, callbacks) {
                var url = this.buildURL(module, method, data, params);
                return this.call(method, url, data, callbacks);
            },

            /**
             * Executes CRUD on relationships.
             *
             * The data paramerer represents relationship information:
             * <pre>
             * {
             *    id: record ID
             *    link: relationship link name
             *    relatedId: ID of the related record
             *    related: object that contains request payload (related record or relationship fields)
             * }
             * </pre>
             *
             * @param {String} method operation type: create, read, update, or delete.
             * @param {String} module module name.
             * @param {Object} data object with relationship information.
             * @param {Object} params(optional) URL parameters.
             * @param {Object} callbacks(optional) callback object.
             * @return XHR request object.
             */
            relationships: function(method, module, data, params, callbacks) {
                var url = this.buildURL(module, data.link, data, params);
                return this.call(method, url, data.related, callbacks);
            },

            /**
             * Searches a module for a specified query.
             * @param {String} module
             * @param {String} query
             * @param {String} fields
             * @param {Object} callbacks hash with with callbacks of the format {Success: function(data){}, error: function(data){}}
             */
            search: function(module, query, fields, callbacks) {
                var params = {
                    "q": query,
                    "fields": fields
                };
                var method = 'search';
                var payload = {};
                var url = this.buildURL(module, method, null, params);

                return this.call('read', url, payload, callbacks);
            },

            /**
             * Performs login.
             *
             * Credentials:
             * <pre>
             *     username: user's login name or email,
             *     password: user's password in clear text
             * </pre>
             *
             * @param  {Object} credentials user credentials.
             * @param  {Object} data(optional) extra data to be passed in login request such as client user agent, etc.
             * @param  {Object} callbacks(optional) callback object.
             * @return XHR request object.
             */
            login: function(credentials, data, callbacks) {
                data = data || {};
                callbacks = callbacks || {};

                var success = function(data) {
                    _resetAuth(data);
                    if (callbacks.success) callbacks.success(data);
                };

                var error = function(xhr) {
                    _resetAuth();
                    if (callbacks.error) callbacks.error(xhr);
                };

                var payload = _.extend(data, {
                    username: credentials.username,
                    password: credentials.password
                });

                var method = 'create';
                var url = this.buildURL("login", method, payload);
                return this.call(method, url, payload, { success: success, error: error });
            },

            /**
             * Performs logout.
             *
             * @param  {Object} callbacks(optional) callback object.
             * @return {Object} XHR request object.
             */
            logout: function(callbacks) {
                var payload = { "token": _accessToken };

                // Reset the auth regardless of the outcome
                _resetAuth();

                var method = 'create';
                var url = this.buildURL("logout", method, payload);
                return this.call(method, url, payload, callbacks);
            },

            /**
             * Checks if API instance is currently authenticated.
             *
             * @return {Boolean} true if authenticated, false otherwise.
             */
            isAuthenticated: function() {
                return typeof(_accessToken) === "string" && _accessToken.length > 0;
            }

        };
    }

    return {
        /**
         * Gets an instance of Sugar API class.
         * @param args
         * @return {Object} an instance of Sugar API class.
         * @member SugarApi
         */
        getInstance: function(args) {
            return _instance || this.createInstance(args);
        },

        /**
         * Creates a new instance of Sugar API class.
         * @param args
         * @return {Object} a new instance of Sugar API class.
         * @member SugarApi
         * @private
         */
        createInstance: function(args) {
            _instance = new SugarApi(args);
            return _instance;
        }

    };

})();
