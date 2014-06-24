//FILE SUGARCRM flav=ent ONLY
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
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
