
({
    extendsFrom: 'HeaderpaneView',
    events:{
        'click [name=log_pmse_button]': 'getLogPmse',
        'click [name=log_sugarcrm_button]': 'getLogSugarcrm',
        'click [name=log_cron_button]': 'getLogCron'
    },
    initialize: function(options) {
        this._super('initialize', [options]);
        this.getLogPmse();
        this.context.on('list:cancelCase:fire', this.cancelCases, this);
        //this.context.on('configLog:fire', this.getLogConfig, this);
    },
    getLogPmse: function() {
        app.alert.show('getLog', {level: 'process', title: 'Loading', autoclose: false});
        var self = this;
        var pmseInboxUrl = app.api.buildURL(this.module + '/getLog/pmse');
        app.api.call('READ', pmseInboxUrl, {},{
            success: function(data)
            {
                $('#logPmseId').html(app.lang.get('LBL_PMSE_BUTTON_PROCESS_AUTHOR_LOG', self.module));
                self.getLog(data)
            }
        });
    },
    getLogSugarcrm: function() {
        app.alert.show('getLog', {level: 'process', title: 'Loading', autoclose: false});
        var self = this;
        var pmseInboxUrl = app.api.buildURL(this.module + '/getLog/sugar');
        app.api.call('READ', pmseInboxUrl, {},{
            success: function(data)
            {
                $('#logPmseId').html(app.lang.get('LBL_PMSE_BUTTON_SUGARCRM_LOG', self.module));
                self.getLog(data)
            }
        });
    },
    getLogCron : function() {
        app.alert.show('getLog', {level: 'process', title: 'Loading', autoclose: false});
        var self = this;
        var pmseInboxUrl = app.api.buildURL(this.module + '/getLog/cron');
        app.api.call('READ', pmseInboxUrl, {},{
            success: function(data)
            {
                $('#logPmseId').html('Cron Log');
                self.getLog(data)
            }
        });
    },
    /*getLogConfig : function() {
        *//**
         * Callback to add recipients, from a closing drawer, to the target Recipients field.
         * @param {undefined|Backbone.Collection} recipients
         *//*
        app.drawer.open(
            {
                layout:  "config-log",
                context: {
                    module: "pmse_Inbox",
                    mixed:  true
                }
            }
        );
    },*/
    getLog: function(data) {
        $("textarea").html(data);
        app.alert.dismiss('getLog');
    }
})
