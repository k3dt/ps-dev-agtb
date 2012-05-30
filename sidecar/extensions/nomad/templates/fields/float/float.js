(function(app) {

    app.view.fields.FloatField = app.view.Field.extend({

        unformat: function(value) {
            value = this.app.utils.unformatNumberString(value, this.def.number_group_seperator, this.def.decimal_seperator, false);
            return value
        },
        format: function(value) {
            value = this.app.utils.formatNumber(value, this.def.round, this.def.precision, this.def.number_group_seperator, this.def.decimal_seperator);
            return value
        }

    });

})(SUGAR.App);