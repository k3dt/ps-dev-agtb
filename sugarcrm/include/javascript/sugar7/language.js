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
        app.lang = _.extend(app.lang, {

            /**
             * Retrieves module singular from name.
             *
             * @deprecated Deprecated since 7.6.0. Use
             *   {@link Core.Language#getModuleName} instead. Will be removed
             *   in 7.8.0.
             * @param {String} module Module name.
             * @return {String} Module singular form.
             */
            getModuleSingular: function(module) {
                app.logger.warn('The `app.lang.getModuleSingular` function has been deprecated since 7.6.0. Please ' +
                    + 'upgrade your code to use `app.lang.getModuleName`.');

                var modString = app.metadata.getStrings('mod_strings')[module],
                    moduleSingular = (modString ? modString['LBL_MODULE_NAME_SINGULAR'] : '') ||
                        app.lang.getAppListStrings('moduleListSingular')[module] ||
                        app.lang.getAppListStrings('moduleList')[module] ||
                        module;

                return moduleSingular;
            }
        });

    });

    /**
     * When application finishes syncing.
     */
    app.events.on('app:sync:complete', function() {
        app.date.lang(app.user.getPreference('language'));
    });

    app.events.on('lang:direction:change', function() {
        var direction = app.lang.direction,
            isRTL = direction === 'rtl';
        $('html').toggleClass('rtl', isRTL);
    });

})(SUGAR.App);
