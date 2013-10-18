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

describe("Opportunities.Base.Views.CreateActions", function() {
    var app, view, options, sinonSandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sinonSandbox = sinon.sandbox.create();
        options = {
            meta: {
                panels: [{
                    fields: [{
                        name: "name"
                    }]
                }]
            }
        };

        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', 'create');
        SugarTest.loadComponent('base', 'view', 'create-actions');
        SugarTest.testMetadata.set();

        SugarTest.seedMetadata(true, './fixtures');

        view = SugarTest.createView('base', 'Opportunities', 'create-actions', options.meta, null, true);
    });

    afterEach(function() {
        sinonSandbox.restore();
    });

    describe('getCustomSaveOptions', function() {
        var opts;

        beforeEach(function() {
            opts = {
                success: function() {}
            };
        });

        afterEach(function() {
            opts = null;
        });

        it('createdModel should not be undefined', function() {
            view.getCustomSaveOptions(opts);
            expect(_.isUndefined(view.createdModel)).toBeFalsy();
        });
        it('listContext should not be undefined', function() {
            view.getCustomSaveOptions(opts);
            expect(_.isUndefined(view.listContext)).toBeFalsy();
        });
    });

    describe('createLinkModel', function() {
        var parentModel, createBeanStub, relateFieldStub;

        beforeEach(function() {
            parentModel = new Backbone.Model({
                id: '101-model-id',
                name: 'parent product name',
                account_id: 'abc-111-2222',
                account_name: 'parent account name',
                assigned_user_name: 'admin'
            }),
            createBeanStub = sinonSandbox.stub(app.data, 'createRelatedBean', function() {
               return new Backbone.Model();
            }),
            relateFieldStub = sinonSandbox.stub(app.data, 'getRelateFields', function() {
                return [{
                    name: 'product_template_name',
                    rname: 'name',
                    id_name: 'product_template_id',
                    populate_list: {
                        account_id: 'account_id',
                        account_name: 'account_name',
                        assigned_user_name: 'user_name'
                    }
                }];
            });
        });
        afterEach(function() {
            parentModel = null;
        });

        it('should populate related fields when it creates linked record', function() {
            var newModel = view.createLinkModel(parentModel, 'blah');
            expect(newModel.get('product_template_id')).toBe(parentModel.get('id'));
            expect(newModel.get('product_template_name')).toBe(parentModel.get('name'));
            expect(newModel.get('account_id')).toBe(parentModel.get('account_id'));
            expect(newModel.get('account_name')).toBe(parentModel.get('account_name'));
            expect(newModel.get('user_name')).toBe(parentModel.get('assigned_user_name'));
        });
        it('should store the relate fields in default to keep the values for [Save and create new]', function() {
            var newModel = view.createLinkModel(parentModel, 'blah');
            expect(newModel.relatedAttributes['product_template_id']).toBe(parentModel.get('id'));
            expect(newModel.relatedAttributes['product_template_name']).toBe(parentModel.get('name'));
            expect(newModel.relatedAttributes['account_id']).toBe(parentModel.get('account_id'));
            expect(newModel.relatedAttributes['account_name']).toBe(parentModel.get('account_name'));
            expect(newModel.relatedAttributes['user_name']).toBe(parentModel.get('assigned_user_name'));
        });
    });


});
