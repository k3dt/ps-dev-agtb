({
    plugins: ['Dropdown'],
    events: {
      'click .navbar':'closeOpenDrops'
    },
    /**
     * Listen to events to resize the header to fit the browser width
     * @param options
     */
    initialize: function(options) {
        app.view.Layout.prototype.initialize.call(this, options);
        this.on("header:update:route", this.resize, this);
        app.events.on("app:sync:complete", this.resize, this);
        app.events.on("app:view:change", this.resize, this);

        var resize = _.bind(this.resize, this);
        $(window)
            .off("resize", resize)
            .on("resize", resize);
    },

    /**
     * Places all components within this layout inside nav-collapse div
     * @param component
     * @private
     */
    _placeComponent: function(component) {
        this.$el.find('.nav-collapse').append(component.$el);
    },

    /**
     * Calculates the width that the module list should resize to and triggers an event
     * that tells the module list to resize
     */
    resize: function() {
        var totalWidth = 0,
            modulelist, maxMenuWidth, componentElement,
            container = this.$('.navbar-inner');

        _.each(this._components, function(component) {
            componentElement = component.$el.children().first();
            if (component.name !== 'modulelist') {
                // only calculate width for visible components
                if (componentElement.is(':visible')) {
                    totalWidth += component.$el.outerWidth(true);
                }
            } else {
                modulelist = component.$el;
            }
        });

        maxMenuWidth = container.parent('.navbar-fixed-top').width();

        this.trigger('view:resize', maxMenuWidth - totalWidth);
    },

    _render: function() {
        var result = app.view.Layout.prototype._render.call(this);
        if(app.api.isAuthenticated()) {
            this.$el.show();
            this.resize();
        } else {
            this.$el.hide();
            return this;
        }
        return result;
    }
})
