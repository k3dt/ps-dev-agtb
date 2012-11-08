//FILE SUGARCRM flav=pro ONLY
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

describe("The forecasts worksheet totals calculation test", function(){

    var app, view, context, formatWithRateStub;

    beforeEach(function() {
        SugarTest.seedMetadata(true);
        SugarTest.seedApp();
        app = SugarTest.app;
        view = SugarTest.loadFile("../modules/Forecasts/clients/base/views/forecastsWorksheet", "forecastsWorksheet", "js", function(d) { return eval(d); });
        var model1 = new Backbone.Model({amount: 100, sales_stage: 'Closed Won', probability: 70, commit_stage: 'include',  best_case : 100, likely_case : 100, base_rate : 1 });
        var model2 = new Backbone.Model({amount: 100, sales_stage: 'Closed Lost', probability: 70, commit_stage: 'exclude', best_case : 100, likely_case : 100, base_rate : 1 });
        var model3 = new Backbone.Model({amount: 100, sales_stage: 'Negotiating', probability: 70, commit_stage: 'exclude',  best_case : 100, likely_case : 100, base_rate : 1 });
        var model4 = new Backbone.Model({amount: 100, sales_stage: 'Lost Custom', probability: 70, commit_stage: 'exclude',  best_case : 100, likely_case : 100, base_rate : 1 });
        var model5 = new Backbone.Model({amount: 100, sales_stage: 'Won Custom', probability: 70, commit_stage: 'exclude',  best_case : 100, likely_case : 100, base_rate : 1 });
        var collection = new Backbone.Collection([model1, model2, model3, model4, model5]);
        view._collection = collection;
        view.includedModel = new Backbone.Model();
        view.overallModel = new Backbone.Model();

        formatWithRateStub = sinon.stub(app.currency, "convertWithRate", function(amount, rate) {
            return Math.round(parseFloat(amount / rate) * Math.pow(10, 2)) / Math.pow(10, 2);
        });
    });

    afterEach(function() {
        formatWithRateStub.restore();
    });

    /*
     * Skip this test.  Expected Opportunities is not a part of nutmeg
     */
    describe("updateTotals worksheet calculation test with expected opportunities", function() {

        xit("should calculate the included values based on forecast value along with expected opportunities", function() {
            //Expected opportunities model
            var expectedModel = new Backbone.Model({expected_commit_stage : 'include', status : 'Active', expected_amount : 20, expected_best_case : 20, base_rate : 1});
            var expectedCollection = new Backbone.Collection([expectedModel]);

            context = app.context.getContext({module:'Forecasts'});
            view.context = { forecasts :
                {
                        forecastschedule : expectedCollection,

                        set : function(model, updatedTotals) {
                            expect(model).toEqual('updatedTotals');
                            expect(updatedTotals.best_case).toEqual(120);
                            expect(updatedTotals.amount).toEqual(120);
                            expect(updatedTotals.included_opp_count).toEqual(1);
                        }
                }
            };
            view.context.forecasts.config = new (Backbone.Model.extend({
                "defaults": fixtures.metadata.modules.Forecasts.config
            }));
            view.calculateTotals();
        });
    });

    /*
     * Skip this test.  Expected Opportunities is not a part of nutmeg
     */
    describe("updateTotals worksheet calculation test without expected opportunities", function() {

        xit("should calculate the included values based on forecast value without expected opportunities", function() {
            //Expected opportunities model
            var expectedModel = new Backbone.Model({expected_commit_stage : 'exclude', status : 'Active', expected_amount : 20, expected_best_case : 20, base_rate : 1});
            var expectedCollection = new Backbone.Collection([expectedModel]);

            context = app.context.getContext({module:'Forecasts'});
            view.context = { forecasts :
                {
                        forecastschedule : expectedCollection,

                        set : function(model, updatedTotals) {
                            expect(model).toEqual('updatedTotals');
                            expect(updatedTotals.best_case).toEqual(100);
                            expect(updatedTotals.amount).toEqual(100);
                            expect(updatedTotals.included_opp_count).toEqual(1);
                        }
                }
            };
            view.context.forecasts.config = new (Backbone.Model.extend({
                "defaults": fixtures.metadata.modules.Forecasts.config
            }));
            view.calculateTotals();
        });
    });

    /*
     * Skip this test.  Expected Opportunities is not a part of nutmeg
     */
    describe("updateTotals worksheet calculation test with null values in expected opportunities", function() {

        xit("should default the included values to 0 when the amounts are null", function() {
            //Expected opportunities model
            var expectedModel = new Backbone.Model({commit_stage : 'include', status : 'Active', expected_amount : null, expected_best_case : null, base_rate : 1});
            var expectedCollection = new Backbone.Collection([expectedModel]);

            context = app.context.getContext({module:'Forecasts'});
            view.context = { forecasts :
                {
                        forecastschedule : expectedCollection,

                        set : function(model, updatedTotals) {
                            expect(model).toEqual('updatedTotals');
                            expect(updatedTotals.best_case).toEqual(100);
                            expect(updatedTotals.amount).toEqual(100);
                            expect(updatedTotals.included_opp_count).toEqual(1);
                        }
                }
            };
            view.context.forecasts.config = new (Backbone.Model.extend({
                "defaults": fixtures.metadata.modules.Forecasts.config
            }));
            view.calculateTotals();
        });
    });


    describe("calculate excluded sales stages correctly", function() {

        it("should calculate the closed_opp_count and closed_amount values", function() {
            context = app.context.getContext({module:'Forecasts'});
            view.context = { forecasts :
                {
                        set : function(model, updatedTotals) {
                            expect(model).toEqual('updatedTotals');
                            expect(updatedTotals.best_case).toEqual(100);
                            expect(updatedTotals.amount).toEqual(100);
                            expect(updatedTotals.included_opp_count).toEqual(1);
                            expect(updatedTotals.won_count).toEqual(1);
                            expect(updatedTotals.won_amount).toEqual(100);
                            expect(updatedTotals.lost_count).toEqual(1);
                            expect(updatedTotals.lost_amount).toEqual(100);
                            expect(updatedTotals.total_opp_count).toEqual(5);
                        },
                    unset : function(test) {}
                }
            };
            view.context.forecasts.config = new (Backbone.Model.extend({
                "defaults": fixtures.metadata.modules.Forecasts.config
            }));
            view.calculateTotals();
        });
    })


    describe("calculate custom won and lost stages correctly", function() {

        it("should calculate the correct values for custom sales stages", function() {

            context = app.context.getContext({module:'Forecasts'});
            view.context = { forecasts :
                {
                        set : function(model, updatedTotals) {
                            expect(model).toEqual('updatedTotals');
                            expect(updatedTotals.best_case).toEqual(100);
                            expect(updatedTotals.amount).toEqual(100);
                            expect(updatedTotals.won_count).toEqual(1);
                            expect(updatedTotals.won_amount).toEqual(100);
                            expect(updatedTotals.lost_count).toEqual(1);
                            expect(updatedTotals.lost_amount).toEqual(100);
                            expect(updatedTotals.included_opp_count).toEqual(1);
                            expect(updatedTotals.total_opp_count).toEqual(5);
                        },
                    unset : function(test) {}
                }
            };
            view.context.forecasts.config = new (Backbone.Model.extend({
                "defaults": fixtures.metadata.modules.Forecasts.config
            }));
            view.context.forecasts.config.set('sales_stage_won', ['Won Custom']);
            view.context.forecasts.config.set('sales_stage_lost', ['Lost Custom']);
            view.calculateTotals();
        });
    })


    describe("calculate multiple custom won and lost stages correctly", function() {

        it("should calculate the correct values for multiple custom sales stages", function() {
            context = app.context.getContext({module:'Forecasts'});
            view.context = { forecasts :
                {

                        set : function(model, updatedTotals) {
                            expect(model).toEqual('updatedTotals');
                            expect(updatedTotals.best_case).toEqual(100);
                            expect(updatedTotals.amount).toEqual(100);
                            expect(updatedTotals.won_count).toEqual(2);
                            expect(updatedTotals.won_amount).toEqual(200);
                            expect(updatedTotals.lost_count).toEqual(2);
                            expect(updatedTotals.lost_amount).toEqual(200);
                            expect(updatedTotals.included_opp_count).toEqual(1);
                            expect(updatedTotals.total_opp_count).toEqual(5);
                        },
                    unset : function(test) {}
                }
            };
            view.context.forecasts.config = new (Backbone.Model.extend({
                "defaults": fixtures.metadata.modules.Forecasts.config
            }));
            view.context.forecasts.config.set('sales_stage_won', ['Won Custom', 'Closed Won']);
            view.context.forecasts.config.set('sales_stage_lost', ['Lost Custom', 'Closed Lost']);
            view.calculateTotals();
        });
    })
});