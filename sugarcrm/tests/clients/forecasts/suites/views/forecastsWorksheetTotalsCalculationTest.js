//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

describe("The forecasts worksheet totals calculation test", function(){

    var app, view, context;

    beforeEach(function() {
        SugarTest.seedMetadata(true);
        SugarTest.seedApp();
        app = SugarTest.app;
        view = SugarTest.loadFile("../../../../clients/forecasts/views/forecastsWorksheet", "forecastsWorksheet", "js", function(d) { return eval(d); });
        var model1 = new Backbone.Model({amount: 100, probability: 70, forecast: 1,  best_case : 100, likely_case : 100 });
        var model2 = new Backbone.Model({amount: 100, probability: 70, forecast: 0, best_case : 100, likely_case : 100 });
        var model3 = new Backbone.Model({amount: 100, probability: 70, forecast: 0,  best_case : 100, likely_case : 100 });
        var collection = new Backbone.Collection([model1, model2, model3]);
        view._collection = collection;
        view.includedModel = new Backbone.Model();
        view.overallModel = new Backbone.Model();
    });


    describe("updateTotals worksheet calculation test with expected opportunities", function() {

        it("should calculate the included values based on forecast value along with expected opportunities", function() {
            //Expected opportunities model
            var expectedModel = new Backbone.Model({include_expected : 1, status : 'Active', expected_amount : 20, expected_best_case : 20, expected_likely_case : 10});
            var expectedCollection = new Backbone.Collection([expectedModel]);

            context = app.context.getContext({module:'Forecasts'});
            view.context = { forecasts :
                {
                        forecastschedule : expectedCollection,

                        set : function(model, updatedTotals) {
                            expect(model).toEqual('updatedTotals');
                            expect(updatedTotals.best_case).toEqual(120);
                            expect(updatedTotals.likely_case).toEqual(110);
                            expect(updatedTotals.amount).toEqual(120);
                            expect(updatedTotals.opp_count).toEqual(1);
                        }
                }
            };
            view.calculateTotals();
        });
    });


    describe("updateTotals worksheet calculation test without expected opportunities", function() {

        it("should calculate the included values based on forecast value without expected opportunities", function() {
            //Expected opportunities model
            var expectedModel = new Backbone.Model({include_expected : 0, status : 'Active', expected_amount : 20, expected_best_case : 20, expected_likely_case : 10});
            var expectedCollection = new Backbone.Collection([expectedModel]);

            context = app.context.getContext({module:'Forecasts'});
            view.context = { forecasts :
                {
                        forecastschedule : expectedCollection,

                        set : function(model, updatedTotals) {
                            expect(model).toEqual('updatedTotals');
                            expect(updatedTotals.best_case).toEqual(100);
                            expect(updatedTotals.likely_case).toEqual(100);
                            expect(updatedTotals.amount).toEqual(100);
                            expect(updatedTotals.opp_count).toEqual(1);
                        }
                }
            };
            view.calculateTotals();
        });
    });


    describe("updateTotals worksheet calculation test with null values in expected opportunities", function() {

        it("should default the included values to 0 when the amounts are null", function() {
            //Expected opportunities model
            var expectedModel = new Backbone.Model({include_expected : 1, status : 'Active', expected_amount : null, expected_best_case : null, expected_likely_case : null});
            var expectedCollection = new Backbone.Collection([expectedModel]);

            context = app.context.getContext({module:'Forecasts'});
            view.context = { forecasts :
                {
                        forecastschedule : expectedCollection,

                        set : function(model, updatedTotals) {
                            expect(model).toEqual('updatedTotals');
                            expect(updatedTotals.best_case).toEqual(100);
                            expect(updatedTotals.likely_case).toEqual(100);
                            expect(updatedTotals.amount).toEqual(100);
                            expect(updatedTotals.opp_count).toEqual(1);
                        }
                }
            };
            view.calculateTotals();
        });
    });
});