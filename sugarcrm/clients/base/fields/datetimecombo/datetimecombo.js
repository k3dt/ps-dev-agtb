({
    _render:function(value) {
        app.view.Field.prototype._render.call(this);//call proto render
        $(function() {
            $(".datepicker").datepicker({
                showOn: "button",
                buttonImage: "../lib/jquery-ui/css/smoothness/images/calendar.gif",
                buttonImageOnly: true
            });
        });
    },

    unformat:function(value) {
        return value
    },

    format:function(value) {
        var jsDate = this.app.utils.date.parse(value);
        jsDate = this.app.utils.date.roundTime(jsDate);
        value = {
            dateTime: value,
            //TODO Account for user prefs
            date: this.app.utils.date.format(jsDate, 'Y-m-d'),
            time: this.app.utils.date.format(jsDate, 'h:i:s'),
            hours: this.app.utils.date.format(jsDate, 'H'),
            minutes: this.app.utils.date.format(jsDate, 'i'),
            seconds: this.app.utils.date.format(jsDate, 's'),
            amPm: this.app.utils.date.format(jsDate, 'H') < 12 ? 'am' : 'pm'
        };
        return value
    },

    timeOptions:{  //TODO set this via a call to userPrefs in a overloaded initalize
        hours:[
            {key: "00", value: "00"},
            {key: "01", value: "01"},
            {key: "02", value: "02"},
            {key: "03", value: "03"},
            {key: "04", value: "04"},
            {key: "05", value: "05"},
            {key: "06", value: "06"},
            {key: "07", value: "07"},
            {key: "08", value: "08"},
            {key: "09", value: "09"},
            {key: "10", value: "10"},
            {key: "11", value: "11"},
            {key: "12", value: "12"},
            {key: "13", value: "13"},
            {key: "14", value: "14"},
            {key: "15", value: "15"},
            {key: "16", value: "16"},
            {key: "17", value: "17"},
            {key: "18", value: "18"},
            {key: "19", value: "19"},
            {key: "20", value: "20"},
            {key: "21", value: "21"},
            {key: "22", value: "22"},
            {key: "23", value: "23"},
            {key: "24", value: "24"}
        ],
            minutes: [
            {key: "00", value: "00"},
            {key: "15", value: "15"},
            {key: "30", value: "30"},
            {key: "45", value: "45"}
        ],
            amPm: [
            {key: "am", value: "am"},
            {key: "pm", value: "pm"}
        ]
    },
    bindDomChange: function() {
        var self = this;
        var date = this.$el.find('input');
        var model = this.model;
        var fieldName = this.name;

        var hour = this.$el.find('.date_time_hours');
        var minute = this.$el.find('.date_time_minutes');

        //TODO add AM PM support depending on user prefs
        date.on('change', function(ev) {
            model.set(fieldName, self.unformat(date.val() + ' ' + hour.val() + ':' + minute.val() + ':00'));
        });
        hour.on('change', function(ev) {
            model.set(fieldName, self.unformat(date.val() + ' ' + hour.val() + ':' + minute.val() + ':00'));
        });
        minute.on('change', function(ev) {
            model.set(fieldName, self.unformat(date.val() + ' ' + hour.val() + ':' + minute.val() + ':00'));
        });
    }
})