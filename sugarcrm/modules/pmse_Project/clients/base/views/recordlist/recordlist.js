({
    extendsFrom: 'RecordlistView',

    /**
     * @override
     * @param {Object} options
     */
    initialize: function(options) {
        this.contextEvents = _.extend({}, this.contextEvents, {
            "list:opendesigner:fire": "openDesigner",
            "list:exportprocess:fire": "exportProcess",
            "list:enabledRow:fire": "enabledProcess",
            "list:disabledRow:fire": "disabledProcess"
        });
        app.view.invokeParent(this, {type: 'view', name: 'recordlist', method: 'initialize', args:[options]});
    },

    openDesigner: function(model) {
        app.navigate(this.context, model, 'layout/designer');
    },

    exportProcess: function(model) {
        var url = app.api.buildURL(model.module, 'dproject', {id: model.id}, {platform: app.config.platform});

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
    },
    enabledProcess: function(model) {
        var self = this;
        var name = model.get('name') || '';
        app.alert.show(model.get('id') + ':deleted', {
            level: 'confirmation',
            messages: app.utils.formatString(app.lang.get('LBL_PRO_ENABLE_CONFIRMATION', model.module),[name.trim()]),
            onConfirm: function() {
                self._updateProStatusEnabled(model);
            }
        });
    },
    _updateProStatusEnabled: function(model) {
        var self = this;
        url = App.api.buildURL(model.module, null, {id: model.id});
        attributes = {prj_status: 'ACTIVE'};
        app.api.call('update', url, attributes,{
            success:function(){
                self.reloadList();
            }
        });
        app.alert.show(model.id + ':refresh', {
            level:"process",
            title: app.lang.get('LBL_PRO_ENABLE', model.module),
            autoClose: true
        });
//        self.reloadList();
    },
    disabledProcess: function(model) {
        var self = this;
        var name = model.get('name') || '';
        app.alert.show(model.get('id') + ':deleted', {
            level: 'confirmation',
            messages: app.utils.formatString(app.lang.get('LBL_PRO_DISABLE_CONFIRMATION', model.module),[name.trim()]),
            onConfirm: function() {
                self._updateProStatusDisabled(model);
            }
        });
    },
    _updateProStatusDisabled: function(model) {
        var self = this;
        url = App.api.buildURL(model.module, null, {id: model.id});
        attributes = {prj_status: 'INACTIVE'};
        app.api.call('update', url, attributes,{
            success:function(){
                self.reloadList();
            }
        });
        app.alert.show(model.id + ':refresh', {
            level:"process",
            title: app.lang.get('LBL_PRO_DISABLE', model.module),
            autoClose: true
        });
//        self.reloadList();
    },
    reloadList: function() {
        var self = this;
        self.context.reloadData({
            recursive:false,
            error:function(error){
                console.log(error);
            }
        });
    }
})
