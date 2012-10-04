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

describe("The forecasts worksheet", function(){

    var app, view, field, _renderClickToEditStub, _renderFieldStub, _setUpCommitStageSpy, testMethodStub, safeFetch, showMe;

    beforeEach(function() {
        app = SugarTest.app;
        view = SugarTest.loadFile("../modules/Forecasts/clients/base/views/forecastsWorksheet", "forecastsWorksheet", "js", function(d) { return eval(d); });
        var cte = SugarTest.loadFile("../modules/Forecasts/clients/base/lib", "ClickToEdit", "js", function(d) { return eval(d); });
        var bge = SugarTest.loadFile("../modules/Forecasts/clients/base/lib", "BucketGridEnum", "js", function(d) { return eval(d); });
    });

    describe("clickToEdit field", function() {

        beforeEach(function() {
            _renderClickToEditStub = sinon.stub(app.view, "ClickToEditField");
            _renderFieldStub = sinon.stub(app.view.View.prototype, "_renderField");
            field = {
                viewName:'worksheet',
                def:{
                    clickToEdit:true
                }
            };
        });

        afterEach(function(){
            _renderClickToEditStub.restore();
            _renderFieldStub.restore();
            testMethodStub.restore();
        })

        describe("should render", function() {
            beforeEach(function() {
                view.isEditableWorksheet = true;
                testMethodStub = sinon.stub(view, "isMyWorksheet", function() {
                    return true;
                });
            });

            afterEach(function() {
                testMethodStub.restore();
            });


            it("has clickToEdit set to true in metadata and a user is viewing their own worksheet", function() {
                view._renderField(field);
                expect(_renderFieldStub).toHaveBeenCalled();
                expect(_renderClickToEditStub).toHaveBeenCalled();
            });
        });

        describe("should not render", function() {
            beforeEach(function(){
                testMethodStub = sinon.stub(view, "isMyWorksheet", function() {
                    return true;
                });
            });

            afterEach(function(){
                testMethodStub.restore();
            });

            it("does not contain a value for clickToEdit in metadata", function() {
                field = {
                    viewName:'worksheet',
                    def:{}
                };
                view._renderField(field);
                expect(_renderFieldStub).toHaveBeenCalled();
                expect(_renderClickToEditStub).not.toHaveBeenCalled();
            });

            it("has clickToEdit set to something other than true in metadata", function() {
                field = {
                    viewName:'worksheet',
                    def:{
                        clickToEdit: 'true'
                    }
                };
                view._renderField(field);
                expect(_renderFieldStub).toHaveBeenCalled();
                expect(_renderClickToEditStub).not.toHaveBeenCalled();
            });

            it("has clickToEdit set to false in metadata", function() {
                field = {
                    viewName:'worksheet',
                    def:{
                        clickToEdit: false
                    }
                };
                view._renderField(field);
                expect(_renderFieldStub).toHaveBeenCalled();
                expect(_renderClickToEditStub).not.toHaveBeenCalled();
            });

            it("is an edit view", function() {
                field = {
                    viewName:'edit',
                    def:{
                        clickToEdit: true
                    }
                };
                view._renderField(field);
                expect(_renderFieldStub).toHaveBeenCalled();
                expect(_renderClickToEditStub).not.toHaveBeenCalled();
            });

            it("is a user not viewing their own worksheet (i.e. manager viewing a reportee)", function() {
                testMethodStub.restore();
                testMethodStub = sinon.stub(view, "isMyWorksheet", function() {
                    return false;
                });
                view._renderField(field);
                expect(_renderFieldStub).toHaveBeenCalled();
                expect(_renderClickToEditStub).not.toHaveBeenCalled();
            });
        });
    });

    describe("isMyWorksheet method", function() {
        beforeEach(function() {
            testMethodStub = sinon.stub(app.user, "get", function(id) {
                return 'a_user_id';
            });
        });

        afterEach(function(){
            testMethodStub.restore();
            view.selectedUser = '';
        });

        describe("should return true", function() {
            it("is a user viewing their own worksheet", function() {
                view.selectedUser = {
                    id: 'a_user_id'
                };
                expect(view.isMyWorksheet()).toBeTruthy();
            });
        });

        describe("should return false", function() {
            it("is a user not viewing their own worksheet (i.e. manager viewing a reportee)", function() {
                view.selectedUser = {
                    id: 'a_different_user_id'
                };
                expect(view.isMyWorksheet()).toBeFalsy();
            });

            it("receives a selectedUser that is not the expected object", function() {
                view.selectedUser = 'a_user_id';
                expect(view.isMyWorksheet()).toBeFalsy();
            });
        });
    });

    describe("commit_stage fields", function() {

        beforeEach(function() {
            view.isEditableWorksheet = true;

            var model = new Backbone.Model();

            context = { forecasts : {
                            config : model,
                            on : function() {}
                      }
            };

            view.context = context;
            _renderClickToEditStub = sinon.stub(app.view, "ClickToEditField");
            _renderFieldStub = sinon.stub(app.view.View.prototype, "_renderField");
            field = {
                viewName:'worksheet',
                name:'commit_stage',
                type:'enum',
                def: {
                    clickToEdit: 'false'
                },
                delegateEvents: function() {}
            };
        });

        afterEach(function(){
            view.isEditableWorksheet = true;
            _renderClickToEditStub.restore();
            _renderFieldStub.restore();
        });

        it("should have format and unformat handlers on field when config is set to forecast_categories show_binary", function() {
            sinon.stub(view.context.forecasts.config, "get", function(key) {
                return "show_binary";
            });

            expect(field.format).not.toBeDefined();
            expect(field.unformat).not.toBeDefined();
            field = view._setUpCommitStage(field);
            expect(field.format).toBeDefined();
            expect(field.unformat).toBeDefined();
        })

        it("should be rendered on a user's own worksheet", function() {
            testMethodStub = sinon.stub(view, "isMyWorksheet", function() {
                return true;
            });
            _setUpCommitStageSpy = sinon.spy(view, "_setUpCommitStage");
            view._renderField(field);
            expect(_setUpCommitStageSpy).toHaveBeenCalled();
            _setUpCommitStageSpy.restore();
            testMethodStub.restore();
        });

        it("should not be rendered when a user is viewing a worksheet that is not their own", function() {
            view.isEditableWorksheet = false;
            testMethodStub = sinon.stub(view, "isMyWorksheet", function() {
                return false;
            });
            _setUpCommitStageSpy = sinon.spy(view, "_setUpCommitStage");
            view._renderField(field);
            expect(_setUpCommitStageSpy).not.toHaveBeenCalled();
            _setUpCommitStageSpy.restore();
            testMethodStub.restore();
        });
    });

    describe("Forecast Worksheet Config functions tests", function() {
        it("should test checkConfigForColumnVisibility passing in Null", function() {
            var testVal = null;
            expect(view.checkConfigForColumnVisibility(testVal)).toBe(true);
        });
        it("should test checkConfigForColumnVisibility passing in found key", function() {
            var testVal = 'best_case';
            // have to build the context.forecasts.config model
            view.context = {};
            view.context.forecasts = {};
            view.context.forecasts.config = new (Backbone.Model.extend({
                "defaults": fixtures.metadata.modules.Forecasts.config
            }));
            expect(view.checkConfigForColumnVisibility(testVal)).toBe(true);
        });
        it("should test checkConfigForColumnVisibility passing in random key not found", function() {
            var testVal = 'abc9def!';
            expect(view.checkConfigForColumnVisibility(testVal)).toBe(true);
        });
    });
   
    describe("Forecast Worksheet _collection.fetch", function(){
    	beforeEach(function(){    		  		
    		view._collection.fetch = function(){};
    	});
    	 
    	afterEach(function(){
    	
    	});
    	
    	it("should not have been called with safeFetch(false) ", function(){
    		sinon.spy(view._collection, "fetch");
    		view.safeFetch(false);
    		expect(view._collection.fetch).not.toHaveBeenCalled();
    	});
    	
    	it("should have been called with safeFetch(true) ", function(){
    		sinon.spy(view._collection, "fetch");
    		view.safeFetch();
    		expect(view._collection.fetch).toHaveBeenCalled();
    	});
    });
    
    describe("Forecasts worksheet bindings ", function(){
    	beforeEach(function(){
    		view.context = {
    				forecasts:{
    					on: function(event, fcn){},
    					worksheet:{
    						on: function(event, fcn){}
    					},
    					config:{
        					on: function(event, fcn){}
        				} 
    				},
    				   				
    		};
    		view._collection.on = function(){};
    		
    		sinon.spy(view._collection, "on");
    		sinon.spy(view.context.forecasts, "on");
    		sinon.spy(view.context.forecasts.worksheet, "on");
    		sinon.spy(view.context.forecasts.config, "on");
    		view.bindDataChange();
    	});
    	
    	it("_collection.on should have been called with reset", function(){
    		expect(view._collection.on).toHaveBeenCalledWith("reset");
    	});
    	
    	it("forecasts.on should have been called with selectedUser", function(){
    		expect(view.context.forecasts.on).toHaveBeenCalledWith("change:selectedUser");
    	});
    	
    	it("forecasts.on should have been called with selectedTimePeriod", function(){
    		expect(view.context.forecasts.on).toHaveBeenCalledWith("change:selectedTimePeriod");
    	});
    	
    	it("forecasts.on should have been called with selectedCategory", function(){
    		expect(view.context.forecasts.on).toHaveBeenCalledWith("change:selectedCategory");
    	});
    	
    	it("forecasts.worksheet.on should have been called with change", function(){
    		expect(view.context.forecasts.worksheet.on).toHaveBeenCalledWith("change");
    	});
    	
    	it("forecasts.on should have been called with expectedOpportunities", function(){
    		expect(view.context.forecasts.on).toHaveBeenCalledWith("change:expectedOpportunities");
    	});
    	
    	it("forecasts.on should have been called with reloadWorksheetFlag", function(){
    		expect(view.context.forecasts.on).toHaveBeenCalledWith("change:reloadWorksheetFlag");
    	});
    	
    	it("forecasts.on should have been called with checkDirtyWorksheetFlag", function(){
    		expect(view.context.forecasts.on).toHaveBeenCalledWith("change:checkDirtyWorksheetFlag");
    	});
    	
    	it("forecasts.config.on should have been called with show_worksheet_likely", function(){
    		expect(view.context.forecasts.config.on).toHaveBeenCalledWith("change:show_worksheet_likely");
    	});
    	
    	it("forecasts.config.on should have been called with show_worksheet_best", function(){
    		expect(view.context.forecasts.config.on).toHaveBeenCalledWith("change:show_worksheet_best");
    	});
    	
    	it("forecasts.config.on should have been called with show_worksheet_worst", function(){
    		expect(view.context.forecasts.config.on).toHaveBeenCalledWith("change:show_worksheet_worst");
    	});
    });
});