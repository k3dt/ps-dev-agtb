({
    fieldTag: "select",
    _render: function() {
        this.app.view.Field.prototype._render.call(this);

        //After rendering the dropdown, the selected value should be the value set in the model,
        //or the default value. The default value fallbacks to the first option if no other is selected.
        //The chosen plugin displays it correclty, but the value is not set to the select and the model.
        //Below the workaround to save this option to the model manually.
        if (_.isUndefined(this.value)) {
            var optionsObject = _.keys(app.lang.getAppListStrings(this.def.options));
            var defaultValue = _.first(optionsObject);
            if (defaultValue) {
                this.$(this.fieldTag).val(defaultValue);
                this.model.set(this.name, defaultValue);
            }
        }
        this.$(this.fieldTag).chosen();
        this.$(".chzn-container").addClass("tleft");
        return this;
    },
    unformat:function(value) {
        return value;
    },
    format:function(value) {
        var newval = '', optionsObject, optionLabels;

        if(this.def.isMultiSelect && this.view.name !== 'edit') {

            // Gets the dropdown options e.g. {foo:foolbl, bar:barlbl ...}
            optionsObject = app.lang.getAppListStrings(this.def.options);

            // value are selected option keys .. grab corresponding labels
            _.each(value, function(p) {
                if(_.has(optionsObject, p)) {
                    newval += optionsObject[p]+', ';
                }
            });
            newval = newval.slice(0, newval.length - 2); // strips extra ', '
        } else {
            // Normal dropdown, just get selected
            newval = this.model.get(this.name);
        }
        // dropdown with default string so convert it to something we understand
        if(this.def.isMultiSelect && this.view.name === 'edit' && this.def.default && typeof newval ==='string') {
            newval = this.convertMultiSelectDefaultString(newval);
        }
        return newval;
    },
    /**
     * Converts multiselect default strings into array of option keys for template
     * @param {String} defaultString string of the format "^option1^,^option2^,^option3^"
     * @return {Array} of the format ["option1","option2","option3"]
     */
    convertMultiSelectDefaultString: function(defaultString) {
        var result = defaultString.split(",");
        _.each(result, function(value, key) {
            result[key] = value.replace(/\^/g,"");
        })
        return result;
    }
})
