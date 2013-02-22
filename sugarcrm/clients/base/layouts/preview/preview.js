({
    events: {
        "click .closeSubdetail": "hidePreviewPanel"
    },
    initialize: function(opts) {
        app.view.Layout.prototype.initialize.call(this, opts);
        app.events.on("preview:open", this.showPreviewPanel, this);
        app.events.on("preview:close", this.hidePreviewPanel, this);
    },
    renderHtml: function() {
        app.view.Layout.prototype.renderHtml.call(this);
        this.$el.addClass("preview-pane");
    },

    /**
     * Show the preview panel, if it is part of the active drawer
     * @param event (optional) DOM event
     */
    showPreviewPanel: function(event) {
        if(_.isUndefined(app.drawer) || app.drawer.isActive(this.$el)){
            var layout = this.$el.parents(".sidebar-content");
            layout.find(".side-pane").removeClass("active");
            layout.find(".preview-pane").addClass("active");
        }
    },

    /**
     * Hide the preview panel, if it is part of the active drawer
     * @param event (optional) DOM event
     */
    hidePreviewPanel: function(event) {
        if(_.isUndefined(app.drawer) || app.drawer.isActive(this.$el)){
            var layout = this.$el.parents(".sidebar-content");
            layout.find(".side-pane").addClass("active");
            layout.find(".preview-pane").removeClass("active");
        }
    }

})
