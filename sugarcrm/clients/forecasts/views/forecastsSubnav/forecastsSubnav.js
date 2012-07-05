/**
 * View that displays a list of models pulled from the context's collection.
 * @class View.Views.ForecastsSubnavView
 * @alias SUGAR.App.layout.ForecastsSubnavView
 * @extends View.View
 */
({

    bindDataChange: function() {
        var self = this;
        app.view.View.prototype.bindDataChange.call(this);

        this.context.forecasts.on('change:selectedUser', function(context, user) {
            this.fullName = user.full_name;
            this.render();
        }, self);
        this.context.forecasts.on('change:selectedTimePeriod', function(context, timePeriod) {
            this.timePeriod = timePeriod.label;
            this.render();
        }, self);
    }

})
