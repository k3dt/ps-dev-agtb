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
if (!(fixtures)) {
    var fixtures = {};
}
// Make this play nice if fixtures has already been defined for other tests
// so we dont overwrite data
if (!_.has(fixtures, 'metadata')) {
    fixtures.metadata = {};
}
fixtures.metadata.currencies = {
    "-99": {
        id: '-99',
        symbol: "$",
        conversion_rate: "1.0",
        iso4217: "USD"
    },
    //Because obviously everyone loves 1970's Jackson5 hits
    "abc123": {
        id: 'abc123',
        symbol: "€",
        conversion_rate: "0.9",
        iso4217: "EUR"
    }
}
describe("RevenueLineItems.Base.View.Record", function() {
    var app, view, options;

    beforeEach(function() {
        options = {
            meta: {
                panels: [
                    {
                        fields: [
                            {
                                name: "commit_stage"
                            }
                        ]
                    }
                ]
            }
        };

        app = SugarTest.app;
        SugarTest.seedMetadata(true, './fixtures');
        app.user.setPreference('decimal_precision', 2);
        SugarTest.loadComponent('base', 'view', 'record');

        view = SugarTest.createView('base', 'RevenueLineItems', 'record', options.meta, null, true);
    });

    describe("initialization", function() {
        beforeEach(function() {
            sinon.stub(app.view.views.BaseRecordView.prototype, "initialize");

            sinon.stub(app.metadata, "getModule", function() {
                return {
                    is_setup: true,
                    buckets_dom: "commit_stage_binary_dom"
                }
            })
            sinon.stub(view, "_parsePanelFields");

        });

        afterEach(function() {
            view._parsePanelFields.restore();
            app.metadata.getModule.restore();
            app.view.views.BaseRecordView.prototype.initialize.restore();
        });

        it("should set up the commit_stage field for products", function() {
            view.initialize(options);
            expect(view._parsePanelFields).toHaveBeenCalled();//With(options.meta.panels);
        });
    });

    describe("_parsePanelFields method", function() {
        var panels;
        beforeEach(function() {
            panels = [
                {
                    fields: [
                        {
                            name: "commit_stage"
                        }
                    ]
                }
            ];
        });

        afterEach(function() {
            panels = undefined;
        });

        it("should replace commit_stage with a spacer", function() {
            sinon.stub(app.metadata, "getModule", function() {
                return {
                    is_setup: false
                }
            });
            view._parsePanelFields(panels);
            expect(panels[0].fields).toEqual([
                { name: 'spacer', span: 6, readonly: true }
            ]);
            app.metadata.getModule.restore();
        });

        it("should set the proper options on the commit_stage field if forecasts has been setup", function() {
            sinon.stub(app.metadata, "getModule", function() {
                return {
                    is_setup: true,
                    buckets_dom: "something_testable"
                }
            });
            view._parsePanelFields(panels);
            expect(panels[0].fields[0].options).toEqual("something_testable");
            app.metadata.getModule.restore();
        });
    });

    describe("convertCurrencyFields", function() {
        beforeEach(function() {
            view.currencyFields = new Array('likely_case', 'best_case', 'worst_case');
            view.model = new Backbone.Model({likely_case: 25000, best_case: 30000, worst_case: 20000});
        });

        it("should convert fields from USD to our false Euro", function() {
            view.convertCurrencyFields("-99", "abc123");

            expect(view.model.get("likely_case")).toEqual(app.math.mul(app.math.div(25000, 1.0), .9));
            expect(view.model.get("best_case")).toEqual(app.math.mul(app.math.div(30000, 1.0), .9));
            expect(view.model.get("worst_case")).toEqual(app.math.mul(app.math.div(20000, 1.0), .9));
        });

        it("should convert fields from Euro to our false USD", function() {
            view.convertCurrencyFields("abc123", "-99");

            expect(view.model.get("likely_case")).toEqual(app.math.mul(app.math.div(25000, .9), 1.0));
            expect(view.model.get("best_case")).toEqual(app.math.mul(app.math.div(30000, .9), 1.0));
            expect(view.model.get("worst_case")).toEqual(app.math.mul(app.math.div(20000, .9), 1.0));
        });

        it("should not convert anything", function() {
            view.convertCurrencyFields(jasmine.undefined, "abc123");

            expect(view.model.get("likely_case")).toEqual(25000);
            expect(view.model.get("best_case")).toEqual(30000);
            expect(view.model.get("worst_case")).toEqual(20000);
        });
    });

})
