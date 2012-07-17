/**
 * View that displays a chart
 * @class View.Views.ChartView
 * @alias SUGAR.App.layout.ChartView
 * @extends View.View
 */
({

    hasSelectedTimePeriod:false,
    hasSelectedGroupBy:false,
    hasSelectedDataset:false,
    hasSelectedCategory:false,
    hasSelectedUser:false,

    hasFilterOptions:false,

    values:{},
    url:'rest/v10/Forecasts/chart',

    chart: null,


    /**
     * Initialize the View
     *
     * @constructor
     * @param {Object} options
     */
    initialize:function (options) {
        app.view.View.prototype.initialize.call(this, options);
        this.handleRenderOptions({user_id: app.user.get('id')});
        this.hasSelectedUser = true;
        this.handleRenderOptions({display_manager : app.user.get('isManager')});
    },

    /**
     * Listen to changes in values in the context
     */
    bindDataChange:function () {
        var self = this;
        this.context.forecasts.on('change:selectedUser', function (context, user) {
            self.handleRenderOptions({user_id: user.id});
            self.hasSelectedUser = true;
            self.handleRenderOptions({display_manager : (user.showOpps === false && user.isManager === true)});
        });
        this.context.forecasts.on('change:selectedTimePeriod', function (context, timePeriod) {
            self.hasSelectedTimePeriod = true;
            self.handleRenderOptions({timeperiod_id: timePeriod.id});
        });
        this.context.forecasts.on('change:selectedGroupBy', function (context, groupBy) {
            self.hasSelectedGroupBy = true;
            self.handleRenderOptions({group_by: groupBy.id});
        });
        this.context.forecasts.on('change:selectedDataSet', function (context, dataset) {
            self.hasSelectedDataset = true;
            self.handleRenderOptions({dataset: dataset.id});
        });
        this.context.forecasts.on('change:selectedCategory', function(context, value) {
            self.hasSelectedCategory = true;
            self.handleRenderOptions({category: value.id});
        });
        this.context.forecasts.on('change:updatedTotals', function(context, totals){
            if (self.canRender()) {
                self.updateChart();
            }
        });
    },

    handleRenderOptions:function (options) {
        var self = this;
        _.each(options, function (value, key) {
            self.values[key] = value;
        });

        if (self.canRender()) {
            self.renderChart();
        }
    },

    canRender: function() {
        var self = this;
        return (self.hasSelectedTimePeriod &&
            self.hasSelectedGroupBy &&
            self.hasSelectedDataset &&
            self.hasSelectedCategory &&
            self.hasSelectedUser);
    },

    /**
     * Initialize or update the chart
     */
    renderChart:function () {
        this.chart = this._initializeChart();
    },

    /**
     * Only update the json on the chart
     */
    updateChart: function() {
        var self = this;
        SUGAR.charts.update(self.chart, self.url, self.values, function(chart){
            SUGAR.charts.generateLegend(chart, chart.config.injectInto)
        });
    },

    /**
     * Render the chart for the first time
     *
     * @return {Object}
     * @private
     */
    _initializeChart:function () {
        var chart,
            chartId = "db620e51-8350-c596-06d1-4f866bfcfd5b",
            css = {
                "gridLineColor":"#cccccc",
                "font-family":"Arial",
                "color":"#000000"
            },
            chartConfig = {
                "orientation":"vertical",
                "barType":"stacked",
                "tip":"name",
                "chartType":"barChart",
                "imageExportType":"png",
                "showNodeLabels":false,
                "showAggregates":false,
                "saveImageTo":"",
                "dataPointSize":"5"
            };
        app.view.View.prototype.render.call(this);
        chart = new loadSugarChart(chartId, this.url, css, chartConfig, this.values);
        return chart.chartObject;
    }
})