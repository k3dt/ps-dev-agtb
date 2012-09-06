({
    unformat: function(value) {
        value = this.app.utils.unformatNumberString(value, this.app.user.get('number_grouping_separator'), this.app.user.get('decimal_separator'), false);
        return value
    },
    format: function(value) {
        //If we don't have numbers only, unformat number value before formatting it again
        if(/[^\d]/.test(value))
        {
           value = this.unformat(value);
        }
        value = this.app.utils.formatNumber(value, this.def.round, this.app.user.get('decimal_precision'), this.app.user.get('number_grouping_separator'), this.app.user.get('decimal_separator'));
        return value
    }
})