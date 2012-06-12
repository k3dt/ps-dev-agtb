/**
 * Application configuration.
 * @class Config
 * @alias SUGAR.App.config
 * @singleton
 */
(function(app) {

    app.augment("config", {
        /**
         * Application identifier.
         * @cfg {String}
         */
        appId: 'portal',

        /**
         * Application environment. Possible values: 'dev', 'test', 'prod'
         * @cfg {String}
         */
        env: 'dev',

        /**
         * Flag indicating whether to output Sugar API debug information.
         * @cfg {Boolean}
         */
        debugSugarApi: true,

        /**
         * Logging level.
         * @cfg {Object} [logLevel=Utils.Logger.Levels.DEBUG]
         */
        logLevel: 'DEBUG',

        /**
         * Logging writer.
         * @cfg [logWrtiter=Utils.Logger.ConsoleWriter]
         */
        logWriter: 'ConsoleWriter',

        /**
         * Logging formatter.
         * @cfg [logFormatter=Utils.Logger.SimpleFormatter]
         */
        logFormatter: 'SimpleFormatter',

        /**
         * Sugar REST server URL.
         *
         * The URL can relative or absolute.
         * @cfg {String}
         */
        serverUrl: '../../sugarcrm/rest/v10',

        /**
         * Max query result set size.
         * @cfg {Number}
         */
        maxQueryResult: 20,

        /**
         * Max search query result set size (for global search)
         * @cfg {Number}        
         */
        maxSearchQueryResult: 3,

        /**
        * # of fields to display on the detail view
        * @cfg {Number}
        */
        fieldsToDisplay: 5,

        /**
         * A list of routes that don't require authentication (in addition to `login`).
         * @cfg {Array}
         */
        unsecureRoutes: ["signup"],

        /**
         * Platform name.
         * @cfg {String}
         */
        platform: "portal",

        /**
         * Default module to load for the home route (index).
         * If not specified, the framework loads `home` layout for the module `Home`.
         */
        defaultModule: "Cases",

        /**
         * A list of metadata types to fetch by default.
         * @cfg {Array}
         */
        metadataTypes: [],
        
        /**
         * The field and direction to order by.
         *
         * For list views, the default ordering. 
         * <pre><code>
         *         orderByDefaults: {
         *            moduleName: {
         *                field: '<name_of_field>',
         *                direction: '(asc|desc)'
         *            }
         *        }
         * </pre></code>
         * 
         * @cfg {Object}
         */
        orderByDefaults: {
            'Cases': {
                field: 'case_number',
                direction: 'asc'
            },
            'Bugs': {
                field: 'bug_number',
                direction: 'asc'
            },
            'Notes': {
                field: 'date_modified',
                direction: 'desc'
            }
        },
 
        /**
         * Hash of addtional views of the format below to init and render on app start
         ** <pre><code>
         *         additionalComponents: {
         *            viewName: {
         *                target: 'CSSDomselector'
         *            }
         *        }
         * </pre></code>
         * @cfg {Object}
         */
        additionalComponents: {
            header: {
                target: '#header'
            },
            alert: {
                target: '#alert'
            },
            footer: {
                target: '#footer'
            }
        },

        /**
         * Array of modules to display in the nav bar
         ** <pre><code>
         *         displayModules: [
         *            'Bugs',
         *            'Cases
         *        ]
         * </pre></code>
         * @cfg {Array}
         */
        displayModules : [
            'Bugs',
            'Cases',
            'KBDocuments'
        ],
        /**
         * Client ID for oAuth
         * Defaults to sugar other values are support_portal
         * @cfg {Array}
         */
        clientID: "sugar"
    }, false);

})(SUGAR.App);
