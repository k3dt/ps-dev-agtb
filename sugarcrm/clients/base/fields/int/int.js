({
    unformat:function(value){
        value = app.utils.formatNumber(value, 0, 0, "", ".");
        return value;
        },
    format:function(value){
        if (value) {
            value = app.utils.formatNumber(value, 0, 0, this.def.number_group_seperator, ".");
            return value;
        }
    }
})