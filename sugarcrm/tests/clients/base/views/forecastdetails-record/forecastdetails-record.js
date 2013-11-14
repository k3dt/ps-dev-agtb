//FILE SUGARCRM flav=ent ONLY
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

describe("Base.View.Forecastdetails-record", function() {
    var app, view, cfg, result, sandbox;

    beforeEach(function() {
        app = SugarTest.app;

        sandbox = sinon.sandbox.create();
        sandbox.stub(app.metadata, 'getModule', function() {
            return {
                is_setup: 1
            }
        });
        sandbox.stub(app.utils, 'checkForecastConfig', function() {
            return true;
        });
        sandbox.stub(app.user, 'getAcls', function() {
            return {
                'Forecasts': {
                    admin: true
                }
            };
        });

        var context = app.context.getContext();
        context.set({
            module: 'RevenueLineItems',
            model: new Backbone.Model()
        });
        context.parent = new Backbone.Model();
        context.parent.set({
            selectedUser: {id: 'test_user', is_manager: false},
            selectedTimePeriod: 'test_timeperiod',
            module: 'RevenueLineItems',
            model: new Backbone.Model()
        });

        var meta = {
            config: false
        }

        app.user.setPreference('decimal_precision', 2);

        SugarTest.createView('base', '', 'forecastdetails', meta, context, false, null, true);
        view = SugarTest.createView('base', '', 'forecastdetails-record', meta, context, false, null, true);
    });

    afterEach(function() {
        sandbox.restore();
        cfg = null;
        result = null;
    });

    describe("processCases()", function() {
        beforeEach(function() {
            sinon.stub(view, 'mapAllTheThings', function(data) {
                return data;
            });
            sinon.stub(view, 'calculateData', function(data) {
                return data;
            });

            sinon.stub(app.user, 'get', function() { return 'userID-1' });
            sinon.stub(app.user, 'getPreference', function() { return 2 });
        });

        afterEach(function() {
            view.calculateData.restore();
            view.mapAllTheThings.restore();
            app.user.get.restore();
            app.user.getPreference.restore();
        });

        describe("when there is no model", function() {
            it("should not run calculateData", function() {
                view.processCases(undefined);
                expect(view.calculateData).not.toHaveBeenCalled()
            });
        });

        describe("when the logged-in user and the assigned_user_id of the record are different", function() {
            it("should not run calculateData", function() {
                // change to different user ID
                view.context.parent.get('model').set({assigned_user_id: 'userID-2'});
                view.processCases(new Backbone.Model());
                expect(view.calculateData).not.toHaveBeenCalled()
            });
        });

        describe("when there is a model, and logged-in/assigned_user_id match", function() {
            var model;
            beforeEach(function() {
                model = new Backbone.Model();
                // letting the sub-tests set likely_case or amount
                model.set({
                    id: 'modelId',
                    best_case: 200,
                    worst_case: 50,
                    assigned_user_id: 'userID-1'
                });
                view.rliCollection = new Backbone.Collection();
                view.currentModule = 'Opportunities';
            });

            it("should make likely_case using amount", function() {
                view.oldTotals = {
                    best: 200,
                    likely: 100,
                    worst: 50,
                    models: new Backbone.Model()
                };

                view.oldTotals.models.set('modelId', {
                    best: 20,
                    likely: 10,
                    worst: 5
                });

                model.set({
                    amount: 100
                });
                view.includedIds = ['modelId'];
                result = view.processCases(model);
                expect(result.likely_case).toBe(90);
            });
        });
    });

    describe("getInitData()", function() {
        beforeEach(function() {
            view.context.parent.get('model').off();
            // letting the sub-tests set likely_case or amount
            view.context.parent.get('model').set({
                date_closed: '2013-07-19',
                best_case: 200,
                worst_case: 50
            });
            sinon.stub(app.api, 'call', function() {});
        });

        afterEach(function() {
            app.api.call.restore();
        });

        describe("when using amount and not likely case", function() {
            beforeEach(function() {
                view.context.parent.get('model').set({
                    amount: 100
                });
            });

            it("should set oldTotals", function() {
                view.getInitData({});
                expect(view.oldTotals.likely).toBe(view.context.parent.get('model').get('amount'));
            });
        });

        describe("when using likely case and not amount", function() {
            beforeEach(function() {
                view.context.parent.get('model').set({
                    likely_case: 100
                });
            });

            it("should return the 'No Data' message for amount", function() {
                view.getInitData({});
                expect(view.oldTotals.likely).toBe(view.context.parent.get('model').get('likely_case'));
            });
        });

        describe("when date_closed is undefined", function() {
            var fetchNewTPStub;
            beforeEach(function() {
                view.context.parent.get('model').set({
                    amount: 100,
                    date_closed: undefined
                });
                fetchNewTPStub = sinon.stub(view, 'fetchNewTPByDate');
            });

            afterEach(function() {
                fetchNewTPStub.restore();
            });

            it("should not call app.api.call", function() {
                view.getInitData({});
                expect(fetchNewTPStub).toHaveBeenCalled();
            });
        });
    });

    describe("isDateInTimePeriod()", function() {
        var date;
        beforeEach(function() {
            date = '2013-07-19';
        });

        describe("when currentTimeperiod is in the current timeperiod", function() {
            beforeEach(function() {
                // setting tp to way before the date and way after
                view.currentTimeperiod = {
                    start_date: '2013-07-01',
                    end_date:   '2013-07-30'
                };
            });

            it("should return false", function() {
                result = view.isDateInTimePeriod(date, view.currentTimeperiod);
                expect(result).toBeTruthy();
            });
        });

        describe("when currentTimeperiod is before the current timeperiod", function() {
            beforeEach(function() {
                // setting tp to way after the date
                view.currentTimeperiod = {
                    start_date: '2014-01-01',
                    end_date:   '2014-03-31'
                };
            });

            it("should return false", function() {
                result = view.isDateInTimePeriod(date, view.currentTimeperiod);
                expect(result).toBeFalsy();
            });
        });

        describe("when currentTimeperiod is after the current timeperiod", function() {
            beforeEach(function() {
                // setting tp to way before the date
                view.currentTimeperiod = {
                    start_date: '2011-01-01',
                    end_date:   '2011-03-31'
                };
            });

            it("should return false", function() {
                result = view.isDateInTimePeriod(date, view.currentTimeperiod);
                expect(result).toBeFalsy();
            });
        });
    });

    describe("processSalesStage()", function() {
        var calcDataStub, mdl;
        beforeEach(function() {
            calcDataStub = sinon.stub(view, 'calculateData', function() {});
            mdl = new Backbone.Model();
            view.salesStageWon = ['Closed Won'];
            view.commitStagesInIncluded= ['include']
        });

        afterEach(function() {
            calcDataStub.restore();
        });

        describe("when no data changed", function() {
            it("should not run calculateData", function() {
                mdl.set({
                    sales_stage: '',
                    commit_stage: '',
                    id: ''
                })
                view.processSalesStage(mdl);
                expect(calcDataStub).not.toHaveBeenCalled()
            });
        });

        describe("when model was in closedWonIds and should be removed", function() {
            beforeEach(function() {
                view.closedWonIds = ['mdl1', 'mdl2'];
                mdl.set({
                    sales_stage: 'Prospecting',
                    commit_stage: 'exclude',
                    id: 'mdl2',
                    likely_case: 20
                });
                view.serverData = new Backbone.Model();
                view.serverData.set({closed_amount: 100});
            });

            afterEach(function() {

            });

            it("should remove model id from closedWonIds", function() {
                view.processSalesStage(mdl);
                expect(view.closedWonIds).toEqual(['mdl1']);
            });

            it("should subtract likely_case from from closed_amount", function() {
                view.processSalesStage(mdl);
                expect(view.serverData.get('closed_amount')).toEqual(80);
            });

            it("should run calculateData", function() {
                view.processSalesStage(mdl);
                expect(calcDataStub).toHaveBeenCalled()
            });
        });

        describe("when model was not in closedWonIds and should be added", function() {
            beforeEach(function() {
                view.closedWonIds = ['mdl1'];
                mdl.set({
                    sales_stage: 'Closed Won',
                    commit_stage: 'include',
                    id: 'mdl2',
                    likely_case: 20
                });
                view.serverData = new Backbone.Model();
                view.serverData.set({closed_amount: 100});
            });

            it("should remove model id from closedWonIds", function() {
                view.processSalesStage(mdl);
                expect(view.closedWonIds).toEqual(['mdl1', 'mdl2']);
            });

            it("should add likely_case to closed_amount", function() {
                view.processSalesStage(mdl);
                expect(view.serverData.get('closed_amount')).toEqual(120);
            });

            it("should run calculateData", function() {
                view.processSalesStage(mdl);
                expect(calcDataStub).toHaveBeenCalled()
            });
        });
    });

    describe("processCommitStage()", function() {
        var calcDataStub, mdl;
        beforeEach(function() {
            calcDataStub = sinon.stub(view, 'calculateData', function() {});
            mdl = new Backbone.Model();
            view.commitStagesInIncluded= ['include']
        });

        afterEach(function() {
            calcDataStub.restore();
        });

        describe("when no data changed", function() {
            it("should not run calculateData", function() {
                mdl.set({
                    commit_stage: '',
                    id: ''
                })
                view.processCommitStage(mdl);
                expect(calcDataStub).not.toHaveBeenCalled()
            });
        });

        describe("when model was in includedIds and should be removed", function() {
            beforeEach(function() {
                view.includedIds = ['mdl1', 'mdl2'];
                mdl.set({
                    commit_stage: 'exclude',
                    id: 'mdl2',
                    likely_case: 20
                });
                view.serverData = new Backbone.Model();
                view.serverData.set({likely: 100});
            });

            afterEach(function() {

            });

            it("should remove model id from includedIds", function() {
                view.processCommitStage(mdl);
                expect(view.includedIds).toEqual(['mdl1']);
            });

            it("should subtract likely_case from from serverData likely", function() {
                view.processCommitStage(mdl);
                expect(view.serverData.get('likely')).toEqual(80);
            });

            it("should run calculateData", function() {
                view.processCommitStage(mdl);
                expect(calcDataStub).toHaveBeenCalled()
            });
        });

        describe("when model was not in includedIds and should be added", function() {
            beforeEach(function() {
                view.includedIds = ['mdl1'];
                mdl.set({
                    commit_stage: 'include',
                    id: 'mdl2',
                    likely_case: 20
                });
                view.serverData = new Backbone.Model();
                view.serverData.set({likely: 100});
            });

            it("should remove model id from includedIds", function() {
                view.processCommitStage(mdl);
                expect(view.includedIds).toEqual(['mdl1', 'mdl2']);
            });

            it("should add likely_case to serverData likely", function() {
                view.processCommitStage(mdl);
                expect(view.serverData.get('likely')).toEqual(120);
            });

            it("should run calculateData", function() {
                view.processCommitStage(mdl);
                expect(calcDataStub).toHaveBeenCalled()
            });
        });
    });
});
