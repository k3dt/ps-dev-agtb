/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
/**
 * Datapoints in the info pane for Forecasts
 */
({

    /**
     * Tracking the type of totals we are seeing
     */
    previous_type: '',

    /**
     * Arrow Colors
     */
    arrow: '',

    /**
     * What was the first total we got for a given type
     */
    initial_total: '',

    /**
     * The total we want to display
     */
    total: 0,

    /**
     * Can we actually display this field and have the data binding on it
     */
    hasAccess: true,

    /**
     * Do we have access from the ForecastWorksheet Level to show data here?
     */
    hasDataAccess: true,

    /**
     * What to show when we don't have access to the data
     */
    noDataAccessTemplate: undefined,

    /**
     * Holds the totals field name
     */
    total_field: '',

    initialize: function(options) {
        app.view.Field.prototype.initialize.call(this, options);

        this.total_field = this.total_field || this.name;

        this.hasAccess = app.utils.getColumnVisFromKeyMap(this.name, 'forecastsWorksheet');
        this.hasDataAccess = app.acl.hasAccess('read', 'ForecastWorksheets', app.user.get('id'), this.name);
        if(this.hasDataAccess === false) {
            this.noDataAccessTemplate = app.template.getField('base', 'noaccess')(this);
        }

        // before we try and render, lets see if we can actually render this field
        this.before('render', function() {
            if (!this.hasAccess) {
                return false;
            }
            // adjust the arrow
            this.arrow = app.utils.getArrowIconColorClass(this.total, this.initial_total);
            this.checkIfNeedsCommit()
            return true;
        }, this);
        //if user resizes browser, adjust datapoint layout accordingly
        $(window).on('resize.datapoints', _.bind(this.resize, this));
        this.on('render', function() {
            if (!this.hasAccess) {
                return false;
            }
            this.resize();
            return true;
        }, this);
    },
    
    /**
     * Check to see if the worksheet needs commit
     */
    checkIfNeedsCommit: function(){
        // if the initial_total is an empty string (default value) don't run this
        if (!_.isEqual(this.initial_total, '') && !_.isEqual(+this.total, +this.initial_total)) {
            this.context.trigger('forecasts:worksheet:needs_commit', null);
        }
    },

    /**
     * @inheritdoc
     */
    _dispose: function() {
        $(window).off('resize.datapoints');
        app.view.Field.prototype._dispose.call(this);
    },

    /**
     * Overwrite this to only place the placeholder if we actually have access to view it
     *
     * @returns {*}
     */
    getPlaceholder: function() {
        if(this.hasAccess) {
            return app.view.Field.prototype.getPlaceholder.call(this);
        }

        return '';
    },

    adjustDatapointLayout: function(){
        if(this.hasAccess) {
            var parentMarginLeft = this.view.$el.find(".topline .datapoints").css("margin-left");
            var parentMarginRight = this.view.$el.find(".topline .datapoints").css("margin-right");
            var timePeriodWidth = this.view.$el.find(".topline .span4").outerWidth(true);
            var toplineWidth = this.view.$el.find(".topline ").width();
            var collection = this.view.$el.find(".topline div.pull-right").children("span");
            var collectionWidth = parseInt(parentMarginLeft) + parseInt(parentMarginRight);
            collection.each(function(index){
                collectionWidth += $(this).children("div.datapoint").outerWidth(true);
            });
            //change width of datapoint div to span entire row to make room for more numbers
            if((collectionWidth+timePeriodWidth) > toplineWidth) {
                this.view.$el.find(".topline div.hr").show();
                this.view.$el.find(".info .last-commit").find("div.hr").show();
                this.view.$el.find(".topline .datapoints").removeClass("span8").addClass("span12");
                this.view.$el.find(".info .last-commit .datapoints").removeClass("span8").addClass("span12");
                this.view.$el.find(".info .last-commit .commit-date").removeClass("span4").addClass("span12");

            } else {
                this.view.$el.find(".topline div.hr").hide();
                this.view.$el.find(".info .last-commit").find("div.hr").hide();
                this.view.$el.find(".topline .datapoints").removeClass("span12").addClass("span8");
                this.view.$el.find(".info .last-commit .datapoints").removeClass("span12").addClass("span8");
                this.view.$el.find(".info .last-commit .commit-date").removeClass("span12").addClass("span4");
                var lastCommitHeight = this.view.$el.find(".info .last-commit .commit-date").height();
                this.view.$el.find(".info .last-commit .datapoints div.datapoint").height(lastCommitHeight);
            }
            //adjust height of last commit datapoints
            var index = this.$el.index();
            var width = this.$el.find("div.datapoint").outerWidth();
            var datapointLength = this.view.$el.find(".info .last-commit .datapoints div.datapoint").length;
            var sel = this.view.$el.find('.last-commit .datapoints div.datapoint:nth-child('+index+')');
            if (datapointLength > 2 && index <= 2 || datapointLength == 2 && index == 1) {
                $(sel).width(width-8);
            }  else {
                $(sel).width(width);
            }
        }
    },
    resize: function() {
        var self = this;
        //The resize event is fired many times during the resize process. We want to be sure the user has finished
        //resizing the window that's why we set a timer so the code should be executed only once
        if (self.resizeDetectTimer) {
            clearTimeout(this.resizeDetectTimer);
        }
        self.resizeDetectTimer = setTimeout(function() {
            self.adjustDatapointLayout();
        }, 250);
    },
    bindDataChange: function() {
        if (!this.hasAccess) {
            return;
        }

        this.context.on('change:selectedUser change:selectedTimePeriod', function() {
            this.initial_total = '';
            this.total = 0;
            this.arrow = '';
        }, this);

        // any time the main forecast collection is reset
        // this contains the commit history
        this.collection.on('reset', function() {
            // get the first line
            var model = _.first(this.collection.models)
            if (!_.isUndefined(model)) {
                this.initial_total = model.get(this.total_field);
            } else {
                this.initial_total = '';
                this.total = 0;
                this.arrow = '';
            }
            if (!this.disposed) this.render();
        }, this);
        this.context.on('forecasts:worksheet:totals', function(totals, type) {
            var field = this.total_field;
            if (type == "manager") {
                // split off "_case"
                field = field.split('_')[0] + '_adjusted'
            }
            this.total = totals[field];
            this.previous_type = type;

            if (!this.disposed) this.render();
        }, this);
    }
})
