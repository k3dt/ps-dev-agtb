/**
 * View that displays a list of models pulled from the context's collection.
 * @class View.Views.FilterView
 * @alias SUGAR.App.layout.FilterView
 * @extends View.View
 */
({

    viewSelector: '.filterOptions',

    bindDataChange: function() {
        var self = this,
            model = this.context.forecasts.filters;

        model.on('change', function() {
            self.buildDropdowns(this);
        });
    },

    buildDropdowns: function(model) {
        var self = this,
            default_values = {};
        self.$el.find(self.viewSelector).empty();
        _.each(model.attributes, function(data, key) {
            var modelData = model.get(key),
                chosen = app.view.createField({
                    def: {
                        name: key,
                        type: 'enum',
                        options: modelData.options
                    },
                    view: self
                }),
                $chosenPlaceholder = $(chosen.getPlaceholder().toString());

            self.$el.find(self.viewSelector).append($chosenPlaceholder);

            chosen.options.viewName = 'drawer';
            chosen.label = modelData.label;
            default_values[key] = '';
            if (modelData.default) {
                chosen.model.set(key, modelData.default);
                default_values.id = modelData.default;
                default_values.label = modelData.options[modelData.default];
            }
            chosen.def.options = modelData.options;
            chosen.setElement($chosenPlaceholder);
            chosen.render();

            if (key == 'timeperiod_id') {
                var defaultTimePeriod = {
                    "id" : modelData.default,
                    "label" : modelData.options[modelData.default]
                }
                self.context.forecasts.set("selectedTimePeriod", defaultTimePeriod);
                self.handleTimePeriodEvents($chosenPlaceholder);
            } else if (key == 'category') {
                var defaultCategory = {
                    "id" : modelData.default,
                    "label" : modelData.options[modelData.default]
                }
                self.context.forecasts.set("selectedCategory", defaultCategory);
                self.handleCategoryEvents($chosenPlaceholder);
            }
        });
    },

    handleCategoryEvents: function(dropdown) {
        var self = this;
        dropdown.on('change', 'select', function(event, data) {
            var label = $(this).find('option:[value='+data.selected+']').text();
            var id = $(this).find('option:[value='+data.selected+']').val();
            self.context.forecasts.set('selectedCategory', {"id": id, "label": label});
        });
    },

    handleTimePeriodEvents: function(dropdown) {
        var self = this;
        dropdown.on('change', 'select', function(event, data) {
            var label = $(this).find('option:[value='+data.selected+']').text();
            var id = $(this).find('option:[value='+data.selected+']').val();
            self.context.forecasts.set('selectedTimePeriod', {"id": id, "label": label});
        });
    }

})
