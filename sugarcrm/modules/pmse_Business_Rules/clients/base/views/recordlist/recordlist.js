({
    extendsFrom: 'RecordlistView',

    /**
     * @override
     * @param {Object} options
     */
    initialize: function(options) {
        this.contextEvents = _.extend({}, this.contextEvents, {
            "list:editbusinessrules:fire": "openBusinessRules",
            "list:exportbusinessrules:fire": "exportBusinessRules"
        });
        app.view.invokeParent(this, {type: 'view', name: 'recordlist', method: 'initialize', args:[options]});
    },

    openBusinessRules: function(model) {
        app.navigate(this.context, model, 'layout/businessrules');
    },

    exportBusinessRules: function(model) {
        var url = app.api.buildURL(model.module, 'brules', {id: model.id}, {platform: app.config.platform});

        if (_.isEmpty(url)) {
            app.logger.error('Unable to get the Project download uri.');
            return;
        }

        app.api.fileDownload(url, {
            error: function(data) {
                // refresh token if it has expired
                app.error.handleHttpError(data, {});
            }
        }, {iframe: this.$el});
    }
})
