(function (app) {
    app.events.on("app:init", function () {
        app.plugins.register('error-decoration', ['view'], {

            /**
             * Clears validation errors on start and success.
             *
             * @param {Object} component
             * @param {Object} plugin
             * @return {void}
             */
            onAttach: function(component, plugin) {
                this.on('init', function() {
                    this.model.on('validation:start validation:success', this.clearValidationErrors, this);
                }, this);
            },

            /**
             * We need to add those events to the view to show/hide the tooltip that contains the error message
             */
            events:{
                'focus input':'showTooltip',
                'blur input':'hideTooltip',
                'focus textarea':'showTooltip',
                'blur textarea':'hideTooltip'
            },
            showTooltip:function (e) {
                _.defer(function () {
                    var $addon = this.$(e.currentTarget).next('.add-on');
                    if ($addon && _.isFunction($addon.tooltip)) {
                        $addon.tooltip('show');
                    }
                }, this);
            },
            hideTooltip:function (e) {
                var $addon = this.$(e.currentTarget).next('.add-on');
                if ($addon && _.isFunction($addon.tooltip)) $addon.tooltip('hide');
            },

            /**
             * Remove validation error decoration from fields
             *
             * @param fields Fields to remove error from
             */
            clearValidationErrors:function (fields) {
                fields = fields || _.toArray(this.fields);
                if (fields.length > 0) {
                    _.defer(function () {
                        _.each(fields, function (field) {
                            if (_.isFunction(field.clearErrorDecoration) && field.disposed !== true) {
                                field.isErrorState = false;
                                field.clearErrorDecoration();
                            }
                        });
                    }, fields);
                }
                _.defer(function() {
                    if (this.disposed) {
                        return;
                    }
                    this.$('.error').removeClass('error');
                    this.$('.error-tooltip').remove();
                }, this);
            }
        });
    });
})(SUGAR.App);
