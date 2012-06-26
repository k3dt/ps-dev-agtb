/**
 * View that displays a chart
 * @class View.Views.ChartView
 * @alias SUGAR.App.layout.ChartView
 * @extends View.View
 */
({

    currentUserId: null,
    filters: {},
    url: 'rest/v10/Forecasts/chart',

    /**
     * Initialize the View
     *
     * @constructor
     * @param {Object} options
     */
    initialize:function (options) {
        app.view.View.prototype.initialize.call(this, options);
        this.filters.user = app.user.get('id');
    },

    /**
     * Listen to changes in values in the context
     */
    bindDataChange: function() {
        var self = this,
            chart = null;

        this.context.on('change:selectedUser', function(context, user) {
            self.filters.user = user.id;
            self.renderChart(chart);
        });
        this.context.on('change:selectedTimePeriod', function(context, timePeriod) {
            self.filters.tp = timePeriod.id;
            self.renderChart(chart);
        });
        this.context.on('change:selectedCategory', function(context, category) {
            self.filters.c = category.id;
            self.renderChart(chart);
        });
        this.context.on('change:selectedGroupBy', function(context, groupby) {
            self.filters.gb = groupby.id;
            self.renderChart(chart);
        });
        this.context.on('change:selectedDataSet', function(context, dataset) {
            self.filters.ds = dataset.id;
            self.renderChart(chart);
        });
    },

    /**
     * Initialize or update the chart
     *
     * @param chart
     */
    renderChart: function(chart) {
        var loadingMessage;

        if (this._isFilterValid()) {
            loadingMessage= SUGAR.App.alert.show('loading', {level: 'process', messages: 'Loading...'});
            if (chart === null) {
                chart = this._initializeChart(function() {
                    loadingMessage.close();
                });
            } else {
                SUGAR.charts.update(chart, this.url, this.filters, function() {
                    loadingMessage.close();
                });
            }
        }
    },

    /**
     * Does filter have all the values that it needs?
     *
     * @return {Boolean}
     * @private
     */
    _isFilterValid: function() {
        if (this.filters.tp && this.filters.c && this.filters.gb) {
            return true;
        } else {
            return false;
        }
    },

    /**
     * Render the chart for the first time
     *
     * @param callback
     * @return {Object}
     * @private
     */
    _initializeChart: function (callback) {
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
        chart = new loadSugarChart(chartId, this.url, css, chartConfig, this.filters, callback);
        return chart.chartObject;
    }

})