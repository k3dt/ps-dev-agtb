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
/**
 * Render help page for keyboard shortcuts.
 *
 * @class View.Views.Base.ShortcutsHelpView
 * @alias SUGAR.App.view.views.BaseShortcutsHelpView
 * @extends View.View
 */
({
    shortcutsHelpTableTemplate: '',

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        this.context.on('button:cancel_button:click', function() {
            app.drawer.close();
        }, this);

        this.context.on('button:configure_button:click', this.configure, this);

        // get templates
        this.shortcutsHelpTableTemplate = app.template.getView(this.name + '.shortcuts-help-table');
    },

    /**
     * @inheritdoc
     */
    _renderHtml: function(ctx, options) {
        this._super('_renderHtml', [ctx, options]);

        // populate help tables
        this.$('[data-render=global]').append(this.buildGlobalHelpTable().children());
        this.$('[data-render=contextual]').append(this.buildContextualHelpTable().children());
    },

    /**
     * Build the help table for global shortcuts.
     * @returns {jQuery}
     */
    buildGlobalHelpTable: function() {
        var $html = $('<div/>'),
            globalShortcuts = app.shortcuts.getRegisteredGlobalShortcuts(),
            help = this.prepareShorcutsHelpDataForDisplay(globalShortcuts);

        $html.append(this.shortcutsHelpTableTemplate(help));

        return $html;
    },

    /**
     * Build the help table for contextual shortcuts.
     * @returns {jQuery}
     */
    buildContextualHelpTable: function() {
        var $html = $('<div/>'),
            lastShortcutSession = app.shortcuts.getLastSavedSession(),
            contextualShortcuts,
            help;

        if (lastShortcutSession) {
            contextualShortcuts = lastShortcutSession.getRegisteredShortcuts();
            if (contextualShortcuts) {
                help = this.prepareShorcutsHelpDataForDisplay(contextualShortcuts);
                $html.append(this.shortcutsHelpTableTemplate(help));
            }
        }

        return $html;
    },

    /**
     * Take the available shortcuts data and transform it for displaying the data
     * in a help table.
     * @param {Array} shortcuts
     * @returns {Array}
     */
    prepareShorcutsHelpDataForDisplay: function(shortcuts) {
        var help = [];

        _.each(shortcuts, function(shortcut) {
            help.push({
                keys: this.getKeyString(shortcut.keys),
                help: app.lang.get(shortcut.description)
            });
        }, this);

        return help;
    },

    /**
     * Build text for keys needed to perform shortcut action.
     * @param {Array} keys
     * @returns {String}
     */
    getKeyString: function(keys) {
        return keys.join(', ');
    },

    /**
     * Open the drawer to configure shortcut keys.
     */
    configure: function() {
        var self = this;

        app.drawer.open({
            layout: 'shortcuts-config',
            context: {
                shortcutSession: app.shortcuts.getLastSavedSession()
            }
        }, function(shouldRefresh) {
            if (shouldRefresh) {
                self.render();
            }
        });
    }
})
