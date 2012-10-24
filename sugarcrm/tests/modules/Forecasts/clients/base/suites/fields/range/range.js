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

describe("Range field", function() {
    var app, field, fieldDef;

    beforeEach(function() {
        app = SugarTest.app;
        fieldDef = {
            "name": "forecastRange",
            "type": "range",
            "view": "edit"
        };
        // theshark - fix this or move the field to core...
        field = SugarTest.createField("../modules/Forecasts/clients/base", "rangeSlider", "range", "detail", fieldDef);
        sinon.stub(app.view.Field.prototype, "initialize");
        sinon.stub(app.view.Field.prototype, "_render");
    });

    afterEach(function() {
        app.view.Field.prototype._render.restore();
        app.view.Field.prototype.initialize.restore();
        delete field;
        delete fieldDef;
        delete app;
    });

    it("should have the selector stored for the relevant DOM element", function() {
        expect(field.fieldTag).toBeDefined();
    });

    it("should have a function to handle slider change events", function() {
        expect(field._sliderChange).toBeDefined();
    });

    it("should have a function to handle saving when the slider changing is done", function() {
        expect(field._sliderChangeComplete).toBeDefined();
    });

    describe("_sliderChangeComplete method", function() {

        beforeEach(function() {
            field.model = {
                set: function(key, value) {  }
            };

            // we aren't calling from the event, so we have to fake a context switch, instead, we just add the things
            // expected to be on this, to what this will be defined as in the context of this test, which is the field.
            field.settings = {
                field: field
            };
            field.data = function(key) {
                return this[key];
            };
            sinon.spy(field, "data");
            sinon.spy(field.model, 'set');
            sinon.stub(field, "getSliderValues");
            field._sliderChangeComplete('click');
        });

        afterEach(function() {
            field.data.restore();
            field.model.set.restore();
            field.getSliderValues.restore();
            delete field.data;
            delete field.settings;
            delete field.model;
        });

        it("should have access to the field object", function() {
            expect(field.data).toHaveBeenCalledWith('settings');
        });

        it("should set the value on the model for the field", function() {
            expect(field.model.set).toHaveBeenCalledWith(field.name);
        });

        it("should get the value for the slider from the noUiSliderElement", function() {
            expect(field.getSliderValues).toHaveBeenCalled();
        });

    });

    describe("rendering", function() {

        beforeEach(function() {
            sinon.stub(field, "_setupSlider");
            field._render();
        });

        afterEach(function() {
            field._setupSlider.restore();
        })

        it("should render the field", function() {
            expect(app.view.Field.prototype._render).toHaveBeenCalled();
        });

        it("should set up the sliders for the field", function() {
            expect(field._setupSlider).toHaveBeenCalled();
        });
    });

    // using a mini data-provider type loop to exercise the handle calculation function
    describe("_calculateHandles method", function() {
        _.each(
            {
                single: {handles: 1},
                upper: {handles: 1},
                lower: {handles: 1},
                double: {handles: 2},
                connected: {handles: 2}
            },
            function(value, key) {
                it("should return " + value.handles + " handles for the " + key +" sliderType as set in metadata", function() {
                    field.def.sliderType = key;
                    expect(field._calculateHandles()).toEqual(value.handles);
                });
            }
        );

        it("should default to single if sliderType is not set in metadata", function () {
            delete field.def.sliderType;
            expect(field._calculateHandles()).toEqual(1);
        });
    });

    describe("_setupHandleConnections method", function() {
        _.each(
            {
                single: {connect: false},
                upper: {connect: 'upper'},
                lower: {connect: 'lower'},
                double: {connect: false},
                connected: {connect: true}
            },
            function(value, key) {
                it("should return connect set to " + value.connect + " for the " + key +" sliderType as set in metadata", function() {
                    field.def.sliderType = key;
                    expect(field._setupHandleConnections()).toEqual(value.connect);
                });
            }
        );

        it("should default to single if sliderType is not set in metadata", function () {
            delete field.def.sliderType;
            expect(field._setupHandleConnections()).toEqual(false);
        });
    });

    describe("_setupSliderEndpoints method", function() {
        beforeEach(function() {
            sinon.spy(field, "_setupSliderEndpoints");
            field.initialize({});
        });

        afterEach(function() {
            field._setupSliderEndpoints.restore();
        });

        it("should return an array of the minimum and maximum slider range as defined in metadata", function() {
            field.def.minRange = 1;
            field.def.maxRange = 99;
            expect(field._setupSliderEndpoints()).toEqual([1, 99]);
        });

        it("should default to a min range of 0 if minRange is undefined in metadata", function() {
            field.def.maxRange = 99;
            expect(field._setupSliderEndpoints()).toEqual([0, 99]);
        });

        it("should default to a max range of 100 if maxRange is undefined in metadata", function() {
            field.def.minRange = 0;
            expect(field._setupSliderEndpoints()).toEqual([0, 100]);
        });
    });

    describe("_setupSliderStartPositions method", function() {
        beforeEach(function() {
            field.model = {
                attributes: {
                },
                set: function(key, value) {
                    this.attributes.key = value;
                },
                get: function(key) {
                    return this.attributes[key];
                }
            };
            sinon.spy(field.model, 'get');
            sinon.spy(field.model, 'set');
        });

        afterEach(function() {
            field.model.set.restore();
            field.model.get.restore();
            delete field.model;
        })

        it("should retrieve the start positions from the model", function() {
            field._setupSliderStartPositions();
            expect(field.model.get).toHaveBeenCalledWith(field.name);
        });

        it("should default to [minRange, maxRange] if the value from the model is an empty array", function() {
            field.def.minRange = 15;
            field.def.maxRange = 65;
            field.model.attributes[field.name] = [];
            expect(field._setupSliderStartPositions()).toEqual([field.def.minRange, 65]);
            delete field.def.minRange;
            delete field.def.maxRange;
        });

        it("should default to [minRange, maxRange] if there is no value in the model", function() {
            field.def.minRange = 15;
            field.def.maxRange = 65;
            expect(field._setupSliderStartPositions()).toEqual([field.def.minRange, field.def.maxRange]);
            delete field.def.minRange;
            delete field.def.maxRange;
        });

        it("should default to [0, maxRange] if there is no value in the model, and minRange is not set in metadata", function() {
            field.def.maxRange = 65;
            expect(field._setupSliderStartPositions()).toEqual([0, field.def.maxRange]);
            delete field.def.maxRange;
        });

        it("should default to [minRange, 100] if there is no value in the model, and maxRange is not set in metadata", function() {
            field.def.minRange = 15;
            expect(field._setupSliderStartPositions()).toEqual([field.def.minRange, 100]);
            delete field.def.minRange;
        })

        it("should be [0, 100] if there is no value in the model and both minRange and maxRange are not set in metadata", function() {
            expect(field._setupSliderStartPositions()).toEqual([0, 100]);
        });
    });

    describe("_setupSlider method", function() {

        var el;

        beforeEach(function() {
            sinon.stub(field, "_calculateHandles");
            sinon.stub(field, "_setupHandleConnections");
            sinon.stub(field, "_setupSliderEndpoints");
            sinon.stub(field, "_setupSliderStartPositions");
            el = {
                noUiSlider: function() { return this; }
            };
            sinon.spy(el, "noUiSlider");
            field._setupSlider(el);
        });

        afterEach(function() {
            field._setupHandleConnections.restore();
            field._calculateHandles.restore();
            field._setupSliderEndpoints.restore();
            field._setupSliderStartPositions.restore();
            el.noUiSlider.restore();
            delete el;
        });

        it("should add a noUiSlider jquery object on the fieldTag element", function () {
            expect(el.noUiSlider).toHaveBeenCalled();
        });

        it("should initialize the slider", function() {
            expect(el.noUiSlider).toHaveBeenCalled();
        });

        it("should calculate the amount of handles for the slider", function() {
            expect(field._calculateHandles).toHaveBeenCalled();
        });

        it("should setup the connection to be displayed for the slider", function() {
            expect(field._setupHandleConnections).toHaveBeenCalled();
        });

        it("should have the settings for sliderType defined", function() {
            expect(field._sliderTypeSettings).toBeDefined();
        });

        it("should set up the scale for the noUiSlider", function() {
            expect(field._setupSliderEndpoints).toHaveBeenCalled();
        });

        it("should set the starting point of the slider", function() {
            expect(field._setupSliderStartPositions).toHaveBeenCalled();
        });
    });

    describe("getSliderValues method", function() {

        beforeEach(function() {
            el = {
                noUiSlider: function(action, settings) { }
            };
            sinon.stub(el, "noUiSlider", function(){ return [43, 78]; });
        });

        afterEach(function() {
            el.noUiSlider.restore();
            delete el;
        });

        it("should get the values from noUiSlider", function() {
            field.getSliderValues(el);
            expect(el.noUiSlider).toHaveBeenCalledWith('value');
        });
    });

    describe("unformat method", function() {

        it("should return a single value for sliderType set to 'single'", function() {
            field.def.sliderType = 'single';
            expect(field.unformat([57, NaN])).toEqual(57);
        });

        it("should default to a single value if sliderType is not defined in metadata", function() {
            delete field.def.sliderType;
            expect(field.unformat([57, NaN])).toEqual(57);
        });

        it("should return an object with the range from the slider to the maxRange for sliderType of 'upper'", function(){
            field.def.sliderType = 'upper';
            field.def.maxRange = 100;
            expect(field.unformat([57, NaN])).toEqual({min: 57, max: 100});
        });

        it("should return an object with the range from the minRange to the slider for sliderType of 'lower'", function(){
            field.def.sliderType = 'lower';
            field.def.minRange = 0;
            expect(field.unformat([NaN, 57])).toEqual({min: 0, max: 57});
        });

        it("should return an array with the two values for sliderType of 'double'", function(){
            field.def.sliderType = 'double';
            expect(field.unformat([43, 78])).toEqual([43, 78]);
        });

        it("should return an object with the range for sliderType of 'connected'", function(){
            field.def.sliderType = 'connected';
            expect(field.unformat([43, 78])).toEqual({min: 43, max: 78});
        });
    });

    describe("format method", function() {
        it("should return an array with a single value for sliderType set to 'single'", function() {
            field.def.sliderType = 'single';
            expect(field.format(57)).toEqual([57]);
        });

        it("should default to a single value if sliderType is not defined in metadata", function() {
            delete field.def.sliderType;
            expect(field.format(57)).toEqual([57]);
        });

        it("should return an object with the range from the slider to the maxRange for sliderType of 'upper'", function(){
            field.def.sliderType = 'upper';
            expect(field.format({min: 57, max: 100})).toEqual([57]);
        });

        it("should return an array of the range from the minRange to the slider for sliderType of 'lower'", function(){
            field.def.sliderType = 'lower';
            expect(field.format({min:0, max: 57})).toEqual([57]);
        });

        it("should return an array of the range for sliderType of 'double'", function(){
            field.def.sliderType = 'double';
            expect(field.format([43, 78])).toEqual([43, 78]);
        });

        it("should return an array of the range for sliderType of 'connected'", function(){
            field.def.sliderType = 'connected';
            expect(field.format({min: 43, max: 78})).toEqual([43, 78]);
        });
    });

});