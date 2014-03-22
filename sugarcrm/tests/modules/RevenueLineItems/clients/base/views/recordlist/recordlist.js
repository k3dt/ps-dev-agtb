//FILE SUGARCRM flav=pro ONLY
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
describe("RevenueLineItems.Base.Views.RecordList", function() {
    var app, view, options, context, layout, message;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set({
            module: 'RevenueLineItems'
        });
        context.prepare();
        
        options = {
            meta: {
                panels: [{
                    fields: [{
                        name: "commit_stage"
                    },{
                        name: "best_case"
                    },{
                        name: "likely_case"
                    },{
                        name: "name"
                    }]
                }]
            }
        };

        
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadComponent('base', 'view', 'flex-list');
        SugarTest.loadComponent('base', 'view', 'recordlist');
        SugarTest.testMetadata.set();
        
        SugarTest.seedMetadata(true);
        app.metadata.getModule("Forecasts", "config").is_setup = 1;
        layout = SugarTest.createLayout("base", "RevenueLineItems", "list", null, null);
        
    });
    
    afterEach(function() {
        app.metadata.getModule("Forecasts", "config").show_worksheet_best = null;
        view.dispose();
        layout.dispose();
    });

    it("should not contain best_case field", function() {
        app.metadata.getModule("Forecasts", "config").show_worksheet_best = 0;
        view = SugarTest.createView('base', 'RevenueLineItems', 'recordlist', options.meta, context, true, layout);
        expect(view._fields.visible.length).toEqual(3);
        _.each(view._fields.visible, function(field) {
            expect(field.name).not.toEqual('best_case');
        });
    });

    it("should not contain commit_stage field", function() {
        app.metadata.getModule("Forecasts", "config").is_setup = 0;
        view = SugarTest.createView('base', 'RevenueLineItems', 'recordlist', options.meta, context, true, layout);
        expect(view._fields.visible.length).toEqual(3);
        _.each(view._fields.visible, function(field) {
            expect(field.name).not.toEqual('commit_stage');
        });
    });
    
    describe("when deleteCommitWarning is called", function() {
        var model;
        beforeEach(function() {
            message = null;
            model = new Backbone.Model({
                id: "aaa",
                name: "boo",
                module: "RevenueLineItems"
            });
            view = SugarTest.createView('base', 'RevenueLineItems', 'recordlist', options.meta, context, true, layout);
        });
        
        afterEach(function() {
            model = null;
        });
        
        it("should should return WARNING_DELETED_RECORD_RECOMMIT_1 and _2 combined when commit_stage = include", function() {
            model.set("commit_stage", "include");
            message = view.deleteCommitWarning(model);
            expect(message).toEqual('WARNING_DELETED_RECORD_RECOMMIT_1<a href="#Forecasts">LBL_MODULE_NAME_SINGULAR</a>.  WARNING_DELETED_RECORD_RECOMMIT_2<a href="#Forecasts">LBL_MODULE_NAME_SINGULAR</a>.');
        });
        
        it("should should return NULL when commit_stage != include", function() {
            model.commit_stage = "exclude";
            message = view.deleteCommitWarning(model);
            expect(message).toEqual(null);
        });
    });

    describe('_checkMergeModels', function() {
        var model1, model2, models = [], view;
        beforeEach(function() {
            sinon.stub(app.alert, 'show', function() {});
            model1 = new Backbone.Model({opportunity_id : 'test_1'});
            model2 = new Backbone.Model({opportunity_id : 'test_1'});
            models = [model1, model2];
            view = SugarTest.createView('base', 'RevenueLineItems', 'recordlist', options.meta, context, true, layout);
        });

        afterEach(function() {
            app.alert.show.restore();
        });

        it('should return true', function() {
            var ret = view._checkMergeModels(models);

            expect(ret).toBeTruthy();
            expect(app.alert.show).not.toHaveBeenCalled();
        });

        it('should return false', function() {
            model2.set('opportunity_id', 'test_2');

            var ret = view._checkMergeModels(models);

            expect(ret).toBeFalsy();
            expect(app.alert.show).toHaveBeenCalled();
        });
    });
});
