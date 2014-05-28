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
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
/**
 * @class View.Views.Base.CasessummaryView
 * @alias SUGAR.App.view.views.BaseCasessummaryView
 * @extends View.View
 */
({
    plugins: ['Dashlet', 'Chart', 'EllipsisInline'],
    className: 'cases-summary-wrapper',

    tabData: null,
    tabClass: '',

    /**
     * @inheritDoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        this.chart = nv.models.pieChart()
                .x(function(d) {
                    return d.key;
                })
                .y(function(d) {
                    return d.value;
                })
                .margin({top: 10, right: 10, bottom: 15, left: 10})
                .donut(true)
                .donutLabelsOutside(true)
                .donutRatio(0.447)
                .hole(this.total)
                .showTitle(false)
                .tooltips(true)
                .showLegend(false)
                .colorData('class')
                .tooltipContent(function(key, x, y, e, graph) {
                    return '<p><b>' + key + ' ' + parseInt(y, 10) + '</b></p>';
                })
                .strings({
                    noData: app.lang.getAppString('LBL_CHART_NO_DATA')
                });
    },

    /**
     * Generic method to render chart with check for visibility and data.
     * Called by _renderHtml and loadData.
     */
    renderChart: function() {
        if (!this.isChartReady()) {
            return;
        }

        // Set value of label inside donut chart
        this.chart.hole(this.total);

        d3.select('svg#' + this.cid)
            .datum(this.chartCollection)
            .transition().duration(500)
            .call(this.chart);

        this.chart_loaded = _.isFunction(this.chart.update);
        this.displayNoData(!this.chart_loaded);
    },

    /**
     * Build content with favorite fields for content tabs
     */
    addFavs: function() {
        var self = this;
        //loop over metricsCollection
        _.each(this.tabData, function(tabGroup) {
            if (tabGroup.models && tabGroup.models.length > 0) {
                _.each(tabGroup.models, function(model) {
                    var field = app.view.createField({
                            def: {type: 'favorite'},
                            model: model,
                            meta: {view: 'detail'},
                            viewName: 'detail',
                            view: self
                        });
                    field.setElement(self.$('.favTarget.[data-model-id="' + model.id + '"]'));
                    field.render();
                });
            }
        });
    },

    /* Process data loaded from REST endpoint so that d3 chart can consume
     * and set general chart properties
     */
    evaluateResult: function(data) {
        this.total = data.models.length;

        var countClosedCases = data.where({status: 'Closed'})
                .concat(data.where({status: 'Rejected'}))
                .concat(data.where({status: 'Duplicate'})).length,
            countOpenCases = this.total - countClosedCases;

        this.chartCollection = {
            data: [],
            properties: {
                title: app.lang.getAppString('LBL_CASE_SUMMARY_CHART'),
                value: 3,
                label: this.total
            }
        };
        this.chartCollection.data.push({
            key: app.lang.getAppString('LBL_DASHLET_CASESSUMMARY_CLOSE_CASES'),
            class: 'nv-fill-green',
            value: countClosedCases
        });
        this.chartCollection.data.push({
            key: app.lang.getAppString('LBL_DASHLET_CASESSUMMARY_OPEN_CASES'),
            class: 'nv-fill-red',
            value: countOpenCases
        });

        if (!_.isEmpty(data.models)) {
            this.processCases(data);
        }
    },

    /**
     * Build tab related data and set tab class name based on number of tabs
     * @param {data} object The chart related data.
     */
    processCases: function(data) {
        this.tabData = [];

        var status2css = {
                'Rejected': 'label-success',
                'Closed': 'label-success',
                'Duplicate': 'label-success'
            },
            stati = _.uniq(data.pluck('status')),
            statusOptions = app.metadata.getModule('Cases', 'fields').status.options || 'case_status_dom';

        _.each(stati, function(status, index) {
            if (!status2css[status]) {
                this.tabData.push({
                    index: index,
                    status: status,
                    statusLabel: app.lang.getAppListStrings(statusOptions)[status],
                    models: data.where({'status': status}),
                    cssClass: status2css[status] ? status2css[status] : 'label-important'
                });
            }
        }, this);

        this.tabClass = ['one', 'two', 'three', 'four', 'five'][this.tabData.length] || 'four';
    },

    /**
     * @inheritDoc
     */
    loadData: function(options) {
        var self = this,
            oppID,
            accountBean,
            relatedCollection;
        if (this.meta.config) {
            return;
        }
        oppID = this.model.get('account_id');
        if (oppID) {
            accountBean = app.data.createBean('Accounts', {id: oppID});
        }
        relatedCollection = app.data.createRelatedCollection(accountBean || this.model, 'cases');
        relatedCollection.fetch({
            relate: true,
            success: function(data) {
                self.evaluateResult(data);
                if (!self.disposed) {
                    // we have to rerender the entire dashlet, not just the chart,
                    // because the HBS file is dependant on processCases completion
                    self.render();
                    self.addFavs();
                }
            },
            error: _.bind(function() {
                this.displayNoData(true);
            }, this),
            complete: options ? options.complete : null
        });
    }
})
