({
    events: {
        'click [data-event]': 'actionClicked'
    },
    cssIconDefault: 'icon-cog',
    cssIconRefresh: 'icon-refresh icon-spin',
    defaultActions: {
        'dashlet:edit:clicked' : 'editClicked',
        'dashlet:refresh:clicked' : 'refreshClicked',
        'dashlet:delete:clicked' : 'removeClicked',
        'dashlet:toggle:clicked' : 'toggleMinify'
    },
    initialize: function (options) {
        _.extend(options.meta, app.metadata.getView(null, 'dashlet-toolbar'), options.meta.toolbar);
        app.view.View.prototype.initialize.call(this, options);
        this.on("dashletaction", this.actionClicked, this);
    },
    /**
     * Handles rowaction's event trigger and propagate the event to the main dashlet
     */
    actionClicked: function(action, evt) {
        if (!action) {
            return;
        }
        var dashlet = _.first(this.layout._components);
        if (_.isFunction(dashlet[action])) {
            dashlet[action](evt);
        } else if (_.isFunction(this[action])) {
            this[action](evt);
        }
    },
    /**
     * Change to the spinning icon to indicate that loading process is triggered
     */
    refreshClicked: function() {
        var $el = this.$("[data-action=loading]"),
            self = this,
            options = {};
        if($el.length > 0) {
            $el.removeClass(this.cssIconDefault).addClass(this.cssIconRefresh);
            options.complete = function() {
                if(self.disposed) {
                    return;
                }
                $el.removeClass(self.cssIconRefresh).addClass(self.cssIconDefault);
            };
        }
        this.layout.reloadDashlet(options);
    },
    removeClicked: function(evt) {
        this.layout.removeDashlet();
    },
    editClicked: function(evt) {
        this.layout.editDashlet();
    },
    /**
     * Toggle current dashlet frame when user clicks the toolbar action
     *
     * @param {Event} mouse event.
     */
    toggleClicked: function(evt) {
        var $btn = $(evt.currentTarget),
            expanded = _.isUndefined($btn.data('expanded')) ? true : $btn.data('expanded'),
            label = expanded ? 'LBL_DASHLET_MAXIMIZE' : 'LBL_DASHLET_MINIMIZE';

        $btn.html(app.lang.get(label, this.module));
        this.layout.collapse(expanded);
        $btn.data('expanded', !expanded);
    },
    /**
     * Toggle current dashlet frame when user clicks chevron icon
     *
     * @param {Window.Event} mouse event.
     */
    toggleMinify: function(evt) {
        var $el = this.$('.dashlet-toggle > i'),
            collapsed = $el.is('.icon-chevron-up');
        this.layout.collapse(collapsed);
    }
})
