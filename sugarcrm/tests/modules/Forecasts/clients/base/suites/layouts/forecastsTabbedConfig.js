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

describe("The forecastsTabbedConfig layout controller", function(){

    var app, layout, stubs;

    beforeEach(function() {
        var options = {
            context: new Backbone.Model(),
            meta: {
                type: 'tabbedConfig',
                components: {}
            }
        };

        options.context.forecasts = new Backbone.Model();

        app = SugarTest.app;
        var tabbedController = SugarTest.loadFile("../modules/Forecasts/clients/base/layouts/tabbedConfig", "tabbedConfig", "js", function(d) {
            return eval(d);
        });
        stubs = [];
        app.viewModule = "";
        app.initData = {};

        stubs.push(sinon.stub(app.metadata, "getLayout", function(layout){
            return {
                forecasts: {
                    meta: {
                        components: {}
                    }
                },
                componentsMeta: {}
            };
        }));
        stubs.push(sinon.stub(app.view.Layout.prototype, "initialize", function (options) {}));

        layout = SugarTest.createComponent('Layout', {
            name: "tabbedConfig",
            module: "Forecasts",
            context: options.context,
            meta : options.meta,
            controller: tabbedController
        });
    });

    afterEach(function() {
        // restore the local stubs
        _.each(stubs, function(stub) {
            stub.restore();
        });
        layout = '';
    });

    describe("breadcrumb register", function() {

        it("should return an array containing passed in value", function() {
            layout.registerBreadCrumbLabel('Test1');
            expect(layout.getBreadCrumbLabels()).toContain({
                'index': 0,
                'label': 'Test1'
            });
        });

        it("should only contain contain 1 unique value", function() {
            layout.registerBreadCrumbLabel('Test1');
            layout.registerBreadCrumbLabel('Test1');
            expect(layout.getBreadCrumbLabels().length).toEqual(1);
            expect(layout.getBreadCrumbLabels()).toContain({
                'index': 0,
                'label': 'Test1'
            });
        })
    });

    describe("initialize", function () {
        var options, testLayout;

        beforeEach(function() {
            options = {
                context: new Backbone.Model({}),
                meta: {
                    components: {}
                }
            };
            options.context.forecasts = new Backbone.Model({});
        });

        afterEach(function() {
            delete options;
            delete testLayout;
        });

        it("should create a new model if one does not exist", function () {
            testLayout = new app.view.layouts.ForecastsTabbedConfigLayout(options);
            expect(testLayout.options.context.attributes.model).toBeDefined();
        });

        it("should create a copy of the model if one exists, so a cancel will not keep values lying around", function() {

            options.context.forecasts.config = new Backbone.Model({
                defaults: {
                    test: 'test'
                }
            });

            testLayout = new app.view.layouts.ForecastsTabbedConfigLayout(options);
            expect(testLayout.options.context.attributes.model).not.toBe(options.context.forecasts.config);
            expect(testLayout.options.context.attributes.model.attributes).toEqual(options.context.forecasts.config.attributes);
        });
    });
});