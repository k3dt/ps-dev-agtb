({
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.context.on('quickcreate:clear', this.clear, this);
        this.context.on('quickcreate:edit', this.editExisting, this);
        this.context.on('quickcreate:restore', this.restoreModel, this);
        this.context.on('quickcreate:validateModel', this.validateModel, this);
        this.context.on('quickcreate:highlightDuplicateFields', this);
        this.model.on("error:validation", this.handleValidationError, this);
    },

    render: function() {
        var totalFieldCount = 0;

        _.each(this.meta.panels, function(panel) {
            var columns = (panel.columns) || 2,
                count = 0,
                rows = [],
                row = [];

            _.each(panel.fields, function(field) {
                var maxSpan;

                if (_.isUndefined(panel.labels)) {
                    panel.labels = true;
                }
                //8 for span because we are using a 2/3 ratio between field span and label span with a max of 12
                maxSpan = (panel.labels) ? 8 : 12;

                if (_.isUndefined(field.span)) {
                    field.span = Math.floor(maxSpan / columns);
                }

                //4 for label span because we are using a 1/3 ratio between field span and label span with a max of 12
                if (_.isUndefined(field.labelSpan)) {
                    field.labelSpan = Math.floor(4 / columns);
                }

                totalFieldCount++;
                field.index = totalFieldCount;
                row.push(field);

                if (count % columns == columns - 1) {
                    rows.push(row);
                    row = [];
                }

                count++;
            }, this);

            panel.grid = rows;
        }, this);

        app.view.View.prototype.render.call(this);
    },

    // Overloaded functions
    _renderHtml: function() { // Use original original
        app.view.View.prototype._renderHtml.call(this);
    },

    handleValidationError:function (errors) {
        var self = this;

        _.each(errors, function (fieldErrors, fieldName) {
            //retrieve the field by name
            var field = self.getField(fieldName);
            var ftag = this.fieldTag || '';

            if (field) {
                var controlGroup = field.$el.parents('.control-group:first');

                if (controlGroup) {
                    controlGroup.addClass("error");
                    controlGroup.find('.add-on').remove();
                    controlGroup.find('.help-block').html("");

                    if (field.$el.parent().parent().find('.input-append').length > 0) {
                        field.$el.unwrap()
                    }
                    // Add error styling
                    field.$el.wrap('<div class="input-append  '+ftag+'">');

                    _.each(fieldErrors, function (errorContext, errorName) {
                        controlGroup.find('.help-block').append(self.app.error.getErrorString(errorName, errorContext));
                    });

                    $('<span class="add-on"><i class="icon-exclamation-sign"></i></span>').insertBefore(controlGroup.find('.help-block'));
                }
            }
        });
    },

    /**
     * Clears out field values
     */
    clear: function() {
        this.model.clear();
        this.model.set(this.model._defaults);
    },
    
    editExisting: function(model) {
        var origModel = this.storeModel();
        this.model.clear();
        this.model.set(this.extendModel(model, origModel));
        var newTitle = this.app.lang.get('LBL_EDIT_LEAD_TITLE', this.module);
        this.context.parent.trigger("modal:changetitle", newTitle);
    },

    extendModel: function(newModel, oldModel) {
        var modelAttributes = newModel.previousAttributes();

        _.each(modelAttributes, function(value, key, list) {
            if ( _.isUndefined(value)|| _.isEmpty(value)) {
                delete modelAttributes[key];
            }
        });

        return _.extend({}, oldModel, modelAttributes);
    },
    
    restoreModel: function() {
        if ( this.origModel ) {
            this.model.clear();
            this.model.set(this.origModel.toJSON());
            var newTitle = this.app.lang.get('LBL_CREATE_LEAD_TITLE', this.module);
            this.context.parent.trigger("modal:changetitle", newTitle);
        }
    },
    
    storeModel: function() {
        this.origModel = this.model.previousAttributes();
        return this.origModel;
    },

    /**
     * Check to make sure that all fields are valid
     * @param callback
     */
    validateModel: function(callback) {
        var isValid = this.model.isValid(this.getFields(this.module));
        callback(isValid);
        return isValid;
    }
})



