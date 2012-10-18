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

describe("The forecastsConfigTimeperiods view", function(){

    var app, view, field, dayField, monthField, intervalField, _renderFieldStub, testMonthMethodStub,testDayMethodStub, testIntervalMethodStub, testValue, testIntervalValue;

    beforeEach(function() {
        app = SugarTest.app;
        view = SugarTest.loadFile("../modules/Forecasts/clients/base/views/forecastsConfigTimeperiods", "forecastsConfigTimeperiods", "js", function(d) { return eval(d); });
        _renderFieldStub = sinon.stub(app.view.View.prototype, "_renderField");
    });

    afterEach(function() {
        _renderFieldStub.restore();
        delete view;
        delete app;
    });

    describe("timeperiod selects setup method", function() {
        beforeEach(function() {
            testMonthMethodStub = sinon.stub(view, "_setUpTimeperiodStartMonthBind", function() {return field;});
            testDayMethodStub = sinon.stub(view, "_setUpTimeperiodStartDayBind", function() {return field;});
            testIntervalMethodStub = sinon.stub(view, "_setUpTimeperiodIntervalBind", function() {return field;});
            field = {
            }
        });

        afterEach(function() {
            testMonthMethodStub.restore();
            testDayMethodStub.restore();
            testIntervalMethodStub.restore();
            delete field;
        });

        it("should set up month field", function() {
            field.name = "timeperiod_start_month";
            view._renderField(field);
            expect(_renderFieldStub).toHaveBeenCalledWith(field);
            expect(testMonthMethodStub).toHaveBeenCalledWith(field);
        });

        it("should set up day field", function() {
            field.name = "timeperiod_start_day";
            view._renderField(field);
            expect(_renderFieldStub).toHaveBeenCalledWith(field);
            expect(testDayMethodStub).toHaveBeenCalledWith(field);
        });

        //BEGIN SUGARCRM flav=pro ONLY
        it("should set up day field", function() {
            field.name = "timeperiod_interval";
            view._renderField(field);
            expect(_renderFieldStub).toHaveBeenCalledWith(field);
            expect(testIntervalMethodStub).toHaveBeenCalledWith(field);
        });
        //END SUGARCRM flav=pro ONLY

        it("should not set up non-date selecting fields", function() {
            field.name = "timeperiod_config_other";
            view._renderField(field);
            expect(_renderFieldStub).toHaveBeenCalledWith(field);
            expect(testMonthMethodStub).not.toHaveBeenCalled();
            expect(testDayMethodStub).not.toHaveBeenCalled();
            //BEGIN SUGARCRM flav=pro ONLY
                expect(testIntervalMethodStub).not.toHaveBeenCalled();
            //END SUGARCRM flav=pro ONLY
        });
    });

    describe("timeperiod date field setup", function() {

        beforeEach(function() {
            testValue = 3;
            testIntervalValue = "Annual";
            view.model = {
                get: function(param) {
                    return {};
                }
            };
            monthField = {
                model: {
                    get: function(param) {
                        return {};
                    },
                    set: function(key, value) {}
                },
                name: 'timeperiod_start_month',
                def: {
                    options: {}
                }
            }
            dayField = {
                model: {
                    get: function(param) {
                        return {};
                    },
                    set: function(key, value) {}
                },
                name: 'timeperiod_start_day',
                def: {
                    options: {}
                }
            }
            intervalField = {
                model: {
                    get: function(param) {
                        return {};
                    },
                    set: function(key, value) {}
                },
                name: 'timeperiod_interval',
                def: {
                    options: {}
                }
            }
            monthField = view._setUpTimeperiodStartMonthBind(monthField);
            dayField = view._setUpTimeperiodStartDayBind(dayField);
            intervalField = view._setUpTimeperiodIntervalBind(intervalField);

        });

        afterEach(function() {
            delete monthField;
            delete dayField;
            delete intervalField;
            delete testValue;
            delete testIntervalValue;
        });

        it("should add the event handlers to update the selections for the field", function() {
            expect(monthField.events["change select"]).toBeDefined();
            expect(monthField.events["change select"]).toEqual("_updateDaysForMonth");
            expect(monthField._updateDaysForMonth).toBeDefined();
            expect(monthField._buildDaysOptions).toBeDefined();
            expect(dayField.events["change select"]).toBeDefined();
            expect(dayField.events["change select"]).toEqual("_updateDays");
            expect(dayField._updateDays).toBeDefined();
            expect(intervalField.events["change select"]).toBeDefined();
            expect(intervalField.events["change select"]).toEqual("_updateIntervals");
            expect(intervalField._updateIntervals).toBeDefined();
        });

        it("should check that the method to build the day options was called with the correct month", function() {
            var testDayMethodStub = sinon.stub(monthField, "_buildDaysOptions", function() {return '';});
            monthField._updateDaysForMonth({}, {selected: testValue});
            expect(testDayMethodStub).toHaveBeenCalledWith(testValue);
        });

        it("should change the number of days in the day selector when the user selects a month", function() {
            var options = monthField._buildDaysOptions(testValue);

            //build expected string
            var expectedOptions = '<option value=""></option>';
            for(var i = 1; i <= 31; i++) {
                expectedOptions +='<option value="'+i+'">'+i+'</option>';
            }
            expect(options).toEqual(expectedOptions);
        });

        it("should check that the method to select the interval and default the leaf was called", function() {
            var testIntervalMethodStub = sinon.stub(intervalField, "_updateIntervals", function() {return '';});
            intervalField._updateIntervals({}, {selected: testIntervalValue});
            expect(testIntervalMethodStub).toHaveBeenCalled;
        });
    });
});