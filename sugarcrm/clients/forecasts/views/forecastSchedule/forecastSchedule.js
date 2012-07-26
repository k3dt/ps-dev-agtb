/**
 * View that displays expected opportunities
 * @extends View.View
 */
({

    url: 'rest/v10/ForecastSchedule',
    tagName: 'tr',
    class: 'view-forecastSchedule',
    id: 'expected_opportunities',
    viewModule: {},
    selectedUser: {},
    _collection:{},

    /**
     * This function handles updating the totals calculation and calling the render function.  It takes the model entry
     * that was updated by the toggle event and calls the Backbone save function on the model to invoke the REST APIs
     * to handle persisting the changes
     *
     * @param model Backbone model entry that was affected by the toggle event
     */
    toggleIncludeInForecast:function(model)
    {
        var self = this;
        self._collection.url = self.url;
        model.save(null, { success:_.bind(function() { self.render(); }, this)});
    },

    /**
     * Initialize the View
     *
     * @constructor
     * @param {Object} options
     */
    initialize:function (options) {
        app.view.View.prototype.initialize.call(this, options);
        this._collection = this.context.forecasts.forecastschedule;
        this.selectedUser = this.context.forecasts.get('selectedUser');
        this.timePeriodId = app.defaultSelections.timeperiod_id.id;
    },

    /**
     * This is a helper function to fetch the collection given the existing filters for timeperiod and selected user
     */
    fetchCollection: function()
    {
        var args = {};
        if(this.timePeriod) {
           args.timeperiod_id = this.timePeriod;
        }

        if(this.selectedUser)
        {
           args.user_id = this.selectedUser.id;
        }

        this._collection.fetch({
            params : args
        });
    },


    /**
     * Renders a field.
     *
     * This method sets field's view element and invokes render on the given field.  If clickToEdit is set to true
     * in metadata, it will also render it as clickToEditable.
     * @param {View.Field} field The field to render
     * @protected
     */
    _renderField: function(field) {

        app.view.View.prototype._renderField.call(this, field);

        if(this.isMyWorksheet() && this.showMe())
        {
            if (field.def.clickToEdit === true) {
                new app.view.ClickToEditField(field, this);
            }

            if (field.name == "commit_stage") {
                new app.view.BucketGridEnum(field, this);
            }
        }
    },

    bindDataChange: function(params) {
        var self = this;
        this._collection = this.context.forecasts.forecastschedule;
        this._collection.on("reset", function() { self.render() }, this);

        if (this.context.forecasts) {
            this.context.forecasts.on("change:selectedUser",
                function(context, selectedUser) {
                    this.updateScheduleBySelectedUser(selectedUser);
                }, this);
            this.context.forecasts.on("change:selectedTimePeriod",
                function(context, timePeriod) {
                    this.updateScheduleBySelectedTimePeriod(timePeriod);
                }, this);
        }
    },

    _setForecastColumn: function(fields) {
        var self = this;
        var forecastField, commitStageField;
        var isOwner = self.isMyWorksheet();

        _.each(fields, function(field) {
            if (field.name == "forecast") {
                field.enabled = !app.config.showBuckets;
                forecastField = field;
            } else if (field.name == "commit_stage") {
                field.enabled = app.config.showBuckets;
                if(!isOwner)
                {
                   field.view = 'default';
                }
                commitStageField = field;
            }
        });
        return app.config.showBuckets?forecastField:commitStageField;
    },

    /**
     * Determines if this Worksheet belongs to the current user, applicable for determining if this view should show,
     * or whether to render the clickToEdit field
     * @return {Boolean} true if it is the worksheet of the logged in user, false if not.
     */
    isMyWorksheet: function() {
        return _.isEqual(app.user.get('id'), this.selectedUser.id);
    },

    /**
     * Determines if this Worksheet should be rendered
     */
    showMe: function()
    {
        return this.selectedUser.showOpps || !this.selectedUser.isManager;
    },


    /**
     * Event Handler for updating the worksheet by a selected user
     *
     * @param params is always a context
     */
    updateScheduleBySelectedUser:function (selectedUser) {
        this.selectedUser = selectedUser;
        if(this.selectedUser.showOpps)
        {
            this.fetchCollection();
        }
    },

    /**
     * Event Handler for updating the worksheet by a timeperiod id
     *
     * @param params is always a context
     */
    updateScheduleBySelectedTimePeriod:function (params) {
        this.timePeriod = params.id;
        if(this.selectedUser && this.selectedUser.showOpps)
        {
            this.fetchCollection();
        }
    }

})

