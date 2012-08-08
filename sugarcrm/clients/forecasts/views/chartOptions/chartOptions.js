/**
 * View that displays the chart options for forecasts module
 * @class View.Views.ChartOptionsView
 * @alias SUGAR.App.layout.ChartOptionsView
 * @extends View.View
 */
({

    /**
     * Overriding _renderField because we need to set up the events to set the proper value depending on which field is
     * being changed.
     * binary for forecasts and adjusts the category filter accordingly
     * @param field
     * @private
     */
    _renderField: function(field) {
        field = this._setUpCategoryField(field);
        app.view.View.prototype._renderField.call(this, field);
    },

    /**
     * Sets up the save event and handler for the  dropdown fields in the chart options view.
     * @param field the commit_stage field
     * @return {*}
     * @private
     */
    _setUpCategoryField: function (field) {
        field.events = _.extend({"change select": "_updateSelections"}, field.events);
        field.bindDomChange = function() {};

        /**
         * updates the selection when a change event is triggered from a dropdown/multiselect
         * @param event the event that was triggered
         * @param input the (de)selection
         * @private
         */
        field._updateSelections = function(fieldName) {
            var contextMap = {
                group_by: 'selectedGroupBy',
                dataset: 'selectedDataSet'
            };
            return function(event, input) {
                debugger;
                this.view.context.forecasts.set(contextMap[fieldName], input.selected);
            };
        }(field.name);

        return field;
    }

})
