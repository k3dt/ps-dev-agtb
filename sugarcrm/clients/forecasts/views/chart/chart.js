/**
 * View that displays a chart
 * @class View.Views.ChartView
 * @alias SUGAR.App.layout.ChartView
 * @extends View.View
 */
({
    values:{},
    url:'rest/v10/Forecasts/chart',

    chart: null,

    chartTitle: '',

    /**
     * Override the _render function
     *
     * @private
     */
    _render: function() {
        this.chartTitle = app.lang.get("LBL_CHART_FORECAST_FOR", "Forecasts") + ' ' + app.defaultSelections.timeperiod_id.label;

        var values = {
            user_id: app.user.get('id'),
            display_manager : app.user.get('isManager'),
            timeperiod_id : app.defaultSelections.timeperiod_id.id,
            group_by : app.defaultSelections.group_by.id,
            dataset : app.defaultSelections.dataset.id,
            category : app.defaultSelections.category.id
        };

        app.view.View.prototype._render.call(this);
        this.handleRenderOptions(values);
    },

    /**
     * Listen to changes in values in the context
     */
    bindDataChange:function () {
        var self = this;
        this.context.forecasts.on('change:selectedUser', function (context, user) {
            self.handleRenderOptions({user_id: user.id, display_manager : (user.showOpps === false && user.isManager === true)});
        });
        this.context.forecasts.on('change:selectedTimePeriod', function (context, timePeriod) {
            self.handleRenderOptions({timeperiod_id: timePeriod.id});
        });
        this.context.forecasts.on('change:selectedGroupBy', function (context, groupBy) {
            self.handleRenderOptions({group_by: groupBy.id});
        });
        this.context.forecasts.on('change:selectedDataSet', function (context, dataset) {
            self.handleRenderOptions({dataset: dataset.id});
        });
        this.context.forecasts.on('change:selectedCategory', function(context, value) {
            self.handleRenderOptions({category: value.id});
        });
        this.context.forecasts.on('change:updatedTotals', function(context, totals){
            if(!_.isEmpty(self.chart)) {
                self.updateChart();
            }
        });
    },

    handleRenderOptions:function (options) {
        var self = this;
        _.each(options, function (value, key) {
            self.values[key] = value;
        });

        self.renderChart();
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

        var oldChart = $("#" + chartId + "-canvaswidget");
        if(!_.isEmpty(oldChart)) {
            oldChart.remove();
        }

        SUGAR.charts = $.extend(SUGAR.charts,
            {
              get : function(url, params, success)
              {
                  var data = {
                      r: new Date().getTime()
                  };
                  data = $.extend(data, params);

                  url = app.api.buildURL('Forecasts', 'chart', '', data);

                  app.api.call('read', url, data, {success : success});
              }
            }
        );

        chart = new loadSugarChart(chartId, this.url, css, chartConfig, this.values);
        return chart.chartObject;
    }
})