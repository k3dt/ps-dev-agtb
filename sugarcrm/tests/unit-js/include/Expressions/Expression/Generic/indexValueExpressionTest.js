/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
describe('Sugar Index Value Expression', function() {
    var app;
    var dm;
    var sinonSandbox;
    var meta;
    var model;

    var getSLContext = function(modelOrCollection, context) {
        var isCollection = (modelOrCollection instanceof dm.beanCollection);
        var model = isCollection ? new modelOrCollection.model() : modelOrCollection;
        context = context || new app.Context({
            url: 'someurl',
            module: model.module,
            model: model
        });
        var view = SugarTest.createComponent('View', {
            context: context,
            type: 'edit',
            module: model.module
        });
        return new SUGAR.expressions.SidecarExpressionContext(view, model, isCollection ? modelOrCollection : false);
    };

    beforeEach(function() {
        sinonSandbox = sinon.sandbox.create();
        SugarTest.seedMetadata();
        app = SugarTest.app;
        meta = SugarTest.loadFixture('revenue-line-item-metadata');
        app.metadata.set(meta);
        dm = app.data;
        dm.reset();
        dm.declareModels();
        model = dm.createBean('RevenueLineItems', SugarTest.loadFixture('rli'));

    });

    afterEach(function() {
        sinonSandbox.restore();
    });

    describe('Sugar Index Value Expression', function() {
        it('returns value at a certain index for an enum expression', function() {
            var a = new SUGAR.expressions.ConstantExpression([4]);
            var b = new SUGAR.expressions.ConstantExpression([5]);
            var c = new SUGAR.expressions.ConstantExpression([6]);
            var d = new SUGAR.expressions.ConstantExpression([7]);
            var e = new SUGAR.expressions.ConstantExpression([8]);
            var indices = [0,1,2,3,4];
            var test = new SUGAR.expressions.DefineEnumExpression([a, b, c, d, e]);
            for (var i = 0; i < indices.length; i = i + 1) {
                expect(new SUGAR.expressions.IndexValueExpression([new SUGAR.expressions.ConstantExpression([i]),test],
                    getSLContext(model)).evaluate()).toBe(i + 4);
            }

        });
    });
});
