({
    className: 'designer',

    events: {
        'click .btn-close-designer': 'closeDesigner'
    },

    closeDesigner: function() {
        app.router.goBack();
    },

    loadData: function (options) {
        this.prj_uid = this.options.context.attributes.modelId;
    },

    initialize: function (options) {
        _.bindAll(this);
        app.view.View.prototype.initialize.call(this, options);
        app.routing.before('route', this.beforeRouteChange, this, true);
    },

    render: function () {
        app.view.View.prototype.render.call(this);
        renderProject(this.prj_uid);
    },

    beforeRouteChange: function(params) {
        var self = this,
            resp = false;
        if (project.isDirty){
            project.showWarning = true;
            var targetUrl = Backbone.history.getFragment();
            //Replace the url hash back to the current staying page
            app.router.navigate(targetUrl, {trigger: false, replace: true});
            app.alert.show('leave_confirmation', {
                level: 'confirmation',
                messages: app.lang.get('LBL_WARN_UNSAVED_CHANGES', this.module),
                onConfirm: function () {
                    var targetUrl = Backbone.history.getFragment();
                    app.router.navigate(targetUrl , {trigger: true, replace: true });
                    window.location.reload()
                },
                onCancel: $.noop
            });
            return false;
        }
        return true;
    }
})