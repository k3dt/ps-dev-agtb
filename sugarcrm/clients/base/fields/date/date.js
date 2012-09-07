({
    // date
    _render: function(value) {
        var self = this;
        
        // Although the server serves up iso date string with Z and all .. for date types going back it wants this
        self.serverDateFormat = 'Y-m-d';
        app.view.Field.prototype._render.call(this);//call proto render
        $(function() {
            if(self.view.name === 'edit') {
                $(".datepicker").datepicker({
                    showOn: "button",
                    buttonImage: app.config.siteUrl + "/sidecar/lib/jquery-ui/css/smoothness/images/calendar.gif",
                    buttonImageOnly: true
                });
            }
        });
    },

    unformat:function(value) {
        var jsDate, 
            usersDateFormatPreference = app.user.get('datepref');

        // In case ISO 8601 get it back to js native date which date.format understands
        jsDate = new Date(value);
        return app.date.format(jsDate, this.serverDateFormat);
    },

    format:function(value) {
        var jsDate, 
            usersDateFormatPreference = app.user.get('datepref');

        // If there is a default 'string' value like "yesterday", format it as a date
        if(!value && this.def.display_default) {
            value = app.date.parseDisplayDefault(this.def.display_default);
            jsDate = new Date(value);
            this.model.set(this.name, app.date.format(jsDate, this.serverDateFormat));
        } else {
            // In case ISO 8601 get it back to js native date which date.format understands
            jsDate = new Date(value);
            value  = app.date.format(jsDate, usersDateFormatPreference);
        }

        jsDate = app.date.parse(value);
        return app.date.format(jsDate, usersDateFormatPreference);
    }

})
