({
    events: {
        'click [data-direction]': 'triggerPagination'
    },

    initialize: function(opts) {
        _.bindAll(this);

        this.template = app.template.get("l.preview");
        this.renderHtml();
        app.view.Layout.prototype.initialize.call(this, opts);
    },

    renderHtml: function() {
        this.$el.html(this.template(this));
    },

    triggerPagination: function(e) {
        this.trigger("preview:pagination:fire", this.$(e.currentTarget).data());
    }
})