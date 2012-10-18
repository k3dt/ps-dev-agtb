({
    /**
     * Listen to events to resize the header to fit the browser width
     * @param options
     */
    initialize: function(options) {
        app.view.Layout.prototype.initialize.call(this, options);

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
        var totalWidth = 100, //100px: spacing for submegamenu, padding and border lines
            modulelist, maxMenuWidth,
            container = this.$('.container-fluid');

        _.each(this._components, function(component) {
            if (component.name !== 'modulelist') {
                totalWidth += component.$el.children().first().outerWidth(true);
                component.$el.children().first().attr('log',component.$el.children().first().outerWidth(true));
            } else {
                modulelist = component.$el;
                modulelist.hide();
            }
        });

        maxMenuWidth = container.first().width();
        modulelist.show();

        this.trigger('view:resize', maxMenuWidth - totalWidth);
    }
})
