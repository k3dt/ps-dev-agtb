/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
(function(app) {
    app.events.on('app:init', function() {

        /**
         * Gets system actions.
         *
         * These action items should have a `callback` string that maps to a
         * system action on
         * {@link View.Layouts.Base.SpotlightLayout#_systemActions}.
         *
         * @return {Array} Formatted items.
         */
        var getSystemActions = function() {
            var actions = [
                {
                    callback: 'toggleHelp',
                    action: 'help',
                    name: app.lang.get('LBL_HELP'),
                    icon: 'fa-exclamation-circle'
                }
            ];
            return actions;
        };

        /**
         * Gets all the mega menu actions.
         *
         * @return {Array} Formatted items.
         */
        var getModuleLinks = function() {
            var actions = [];
            var moduleList = app.metadata.getModuleNames({filter: 'display_tab'});
            _.each(moduleList, function(module) {
                var menuMeta = app.metadata.getModule(module).menu;
                var headerMeta = menuMeta && menuMeta.header && menuMeta.header.meta;
                _.each(headerMeta, function(action) {
                    var name;
                    var jsFunc = 'push';
                    var weight;;
                    if (action.route === '#'+module) {
                        jsFunc = 'unshift';
                        name = app.lang.getModuleName(module, {plural: true});
                        weight = 10;
                    }
                    else if (action.route === '#'+module+'/create') {
                        weight = 20;
                        name = app.lang.get(action.label, module)
                    } else {
                        weight = 30;
                        name = app.lang.get(action.label, module)
                    }
                    actions[jsFunc]({
                        module: module,
                        label: module.substr(0, 2),
                        name: name,
                        route: action.route,
                        icon: action.icon,
                        weight: weight
                    })
                });
            });
            actions.push({
                name: app.lang.getModuleName('Forecasts', {plural: true}),
                module: 'Forecasts',
                label: 'Fo',
                route: '#Forecasts',
                icon: 'fa-bars'
            });
            var profileActions = app.metadata.getView(null, 'profileactions');
            _.each(profileActions, function(action) {
                actions.push({
                    name: app.lang.get(action.label),
                    route: action.route,
                    icon: action.icon
                });
            });
            return actions;
        };

        /**
         * Gets all the spotlight actions.
         *
         * @returns {Object} The list of actions.
         */
        app.metadata.getSpotlightActions = function() {
            var collection = {};
            var actions = getModuleLinks().concat(getSystemActions());
            _.each(actions, function(action) {
                collection[action.route || action.callback] = action;
            });
            return collection;
        };

    });
})(SUGAR.App);
