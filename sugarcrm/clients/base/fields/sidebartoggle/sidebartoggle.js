({
    extendsFrom: 'button',

    events: {
        'click .drawerTrig': 'toggle'//ensure "hit area" big enough
    },
    _render: function() {
        app.view.Field.prototype._render.call(this);
        // Broadcast when we've fully rendered sidebar toggle
        app.controller.context.trigger("sidebarRendered");
    },
    bindDataChange:function () {
        // These corresponding to the toggleSide & openSide events in default layout
        app.controller.context.on("toggleSidebarArrows", this.updateArrows, this);
        app.controller.context.on("openSidebarArrows", this.sidebarArrowsOpen, this);
    },
    updateArrows: function() {
        var chevron = this.$('.drawerTrig i'),
            pointRightClass = 'icon-double-angle-right';
        if (chevron.hasClass(pointRightClass)) {
            this.updateArrowsWithDirection('close');
        } else {
            this.updateArrowsWithDirection('open');
        }
    },
    sidebarArrowsOpen: function() {
        this.updateArrowsWithDirection('open');
    },
    updateArrowsWithDirection: function(state) {
        var chevron = this.$('.drawerTrig i'),
            pointRightClass = 'icon-double-angle-right',
            pointLeftClass = 'icon-double-angle-left';
        if (state === 'open') {
            chevron.removeClass(pointLeftClass).addClass(pointRightClass);
            app.events.trigger('app:toggle:sidebar', 'open');
        } else if (state === 'close') {
            chevron.removeClass(pointRightClass).addClass(pointLeftClass);
            app.events.trigger('app:toggle:sidebar', 'close');
        } else {
            app.logger.warn("updateArrowsWithDirection called with invalid state; should be 'open' or 'close', but was: "+state)
        }
    },
    // If toggled from a user clicking on anchor simply trigger toggleSidebar
    toggle: function() {
        this.context.trigger('toggleSidebar');
        //toggling sidebar can affect the width of content in the same way as a window resize
        //notify of a window resize so that any content listening for a resize can react in the same way for this sidebar toggle
        $(window).trigger('resize');
    },
    _dispose: function () {
        app.view.invokeParent(this, {type: 'field', name: 'button', method: '_dispose'});
        app.controller.context.off(null, null, this);//remove all events for context `this`
    }
})
