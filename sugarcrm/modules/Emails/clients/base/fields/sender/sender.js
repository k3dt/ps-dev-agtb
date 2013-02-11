({
    fieldTag: 'input.select2',

    initialize: function(opts) {
        _.bindAll(this);
        this.endpoint = opts.def.endpoint;
        app.view.Field.prototype.initialize.call(this, opts);
    },

    _render: function() {
        var result = app.view.Field.prototype._render.call(this);

        if(this.tplName === 'edit') {
            var action = (this.endpoint.action) ? this.endpoint.action : null,
                attributes = (this.endpoint.attributes) ? this.endpoint.attributes : null,
                params = (this.endpoint.params) ? this.endpoint.params : null,
                myURL = app.api.buildURL(this.endpoint.module, action, attributes, params);

            app.api.call('GET', myURL, null,{
                    success: this.populateValues,
                    error: function(e) {
                        app.logger.error('Failed to retrieve the outbound configs: ' + e);
                    }
                }
            );
        }

        return result;
    },

    populateValues: function(results) {
        var self = this,
            defaultResult,
            defaultValue = {};

        //sets the default value
        if (!_.isEmpty(results)) {
            defaultResult = _.find(results, function(result) {
                return result.default;
            });

            defaultValue = (defaultResult) ? defaultResult : results[0];

            if (!this.model.has(this.name)) {
                this.model.set(this.name, defaultValue.id);
            }
        }

        var format = function(item) {
            return item.display;
        };

        this.$(this.fieldTag).select2({
            data:{ results: results, text: 'display' },
            formatSelection: format,
            formatResult: format,
            width: '100%',
            placeholder: app.lang.get('LBL_SELECT_FROM_SENDER', this.module),
            initSelection: function(el, callback) {
                if (!_.isEmpty(defaultValue)) {
                      callback(defaultValue);
                }
            }

        }).on("change", function(e) {
                var id = e.val,
                    plugin = $(this).data('select2'),
                    value = (id) ? plugin.selection.find("span").text() : '';

                self.setValue({id: e.val, value: value});
         });

        this.$(".select2-container").addClass("tleft");

    },

    setValue: function(model) {
        this.model.set(this.def.id_name, model.id, {silent: true});
        this.model.set(this.def.name, model.value, {silent: true});
    },

    /**
     * {@inheritdoc}
     *
     * We need this empty so it won't affect refresh the select2 plugin
     */
    bindDomChange: function() {
    }
})
