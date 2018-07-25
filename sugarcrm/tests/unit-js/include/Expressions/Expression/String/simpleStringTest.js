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
describe('Simple Numeric Functions Test', function() {
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

    describe('Character At Expression Function', function() {
        var a = new SUGAR.expressions.StringLiteralExpression(['Hello ']);
        var b = new SUGAR.expressions.ConstantExpression([0]);
        var c = new SUGAR.expressions.ConstantExpression([1]);
        var d = new SUGAR.expressions.ConstantExpression([2]);
        var e = new SUGAR.expressions.ConstantExpression([3]);
        var f = new SUGAR.expressions.ConstantExpression([4]);
        var g = new SUGAR.expressions.ConstantExpression([5]);
        var neg = new SUGAR.expressions.ConstantExpression([-3]);
        var above = new SUGAR.expressions.ConstantExpression([13]);
        it('should return character at a certain place', function() {
            var res =  new SUGAR.expressions.CharacterAtExpression([a,b], getSLContext(model));
            expect(res.evaluate()).toBe('H');
            res =  new SUGAR.expressions.CharacterAtExpression([a,c], getSLContext(model));
            expect(res.evaluate()).toBe('e');
            res =  new SUGAR.expressions.CharacterAtExpression([a,d], getSLContext(model));
            expect(res.evaluate()).toBe('l');
            res =  new SUGAR.expressions.CharacterAtExpression([a,e], getSLContext(model));
            expect(res.evaluate()).toBe('l');
            res =  new SUGAR.expressions.CharacterAtExpression([a,f], getSLContext(model));
            expect(res.evaluate()).toBe('o');
            res =  new SUGAR.expressions.CharacterAtExpression([a,g], getSLContext(model));
            expect(res.evaluate()).toBe(' ');
            res =  new SUGAR.expressions.CharacterAtExpression([a,neg], getSLContext(model));
            expect(res.evaluate()).toBe('');
            res =  new SUGAR.expressions.CharacterAtExpression([a,above], getSLContext(model));
            expect(res.evaluate()).toBe('');
        });
    });

    describe('Concatenate Expression Function', function() {
        var a = new SUGAR.expressions.StringLiteralExpression(['Hello ']);
        var b = new SUGAR.expressions.StringLiteralExpression(['World']);

        it('should return a string that combines Hello World', function() {
            var res =  new SUGAR.expressions.ConcatenateExpression([a,b], getSLContext(model));
            expect(res.evaluate()).toBe('Hello World');
        });
    });

    describe('Contains Expression Function', function() {
        var a = new SUGAR.expressions.StringLiteralExpression(['HelloWorld']);
        var b = new SUGAR.expressions.StringLiteralExpression(['World']);

        it('should return whether string A contains string B (true or false)', function() {
            var res =  new SUGAR.expressions.ContainsExpression([a,b], getSLContext(model));
            expect(res.evaluate()).toBe('true');
            res =  new SUGAR.expressions.ContainsExpression([b,a], getSLContext(model));
            expect(res.evaluate()).toBe('false');
        });
    });

    describe('String to Lower Expression Function', function() {
        var stringA = new SUGAR.expressions.StringLiteralExpression(['HelloWorld']);
        var stringB = new SUGAR.expressions.StringLiteralExpression((['HelloWorld']));
        it('should return all lowercase version of string A', function() {
            var res =  new SUGAR.expressions.StrToLowerExpression([stringA], getSLContext(model));
            expect(res.evaluate()).toBe('helloworld');
            res =  new SUGAR.expressions.StrToLowerExpression([stringB], getSLContext(model));
            expect(res.evaluate()).toBe('helloworld');
        });
    });

    describe('String to Upper Expression Function', function() {
        var stringA = new SUGAR.expressions.StringLiteralExpression(['helloworld']);
        var stringB = new SUGAR.expressions.StringLiteralExpression((['HelloWorld']));

        it('should return all uppercase version of strings A and B', function() {
            var res =  new SUGAR.expressions.StrToUpperExpression([stringA], getSLContext(model));
            expect(res.evaluate()).toBe('HELLOWORLD');
            res =  new SUGAR.expressions.StrToUpperExpression([stringB], getSLContext(model));
            expect(res.evaluate()).toBe('HELLOWORLD');
        });
    });

    describe('Substring of String Expression Function', function() {
        var a = new SUGAR.expressions.StringLiteralExpression(['Hello World']);
        var b = new SUGAR.expressions.ConstantExpression([0]);  // for beginning of string
        var c = new SUGAR.expressions.ConstantExpression([5]);  // for ending of first word
        var d = new SUGAR.expressions.ConstantExpression([6]);  // for beginning of second word
        var e = new SUGAR.expressions.ConstantExpression([11]); // for end of string

        it('should return substring of A', function() {
            var res =  new SUGAR.expressions.SubStrExpression([a,b,c], getSLContext(model));
            expect(res.evaluate()).toBe('Hello');
            res =  new SUGAR.expressions.SubStrExpression([a,d,e], getSLContext(model));
            expect(res.evaluate()).toBe('World');
        });
    });

    describe('Formatted Name Expression Function', function() {
        var a = new SUGAR.expressions.StringLiteralExpression(['Mr.']);
        var b = new SUGAR.expressions.StringLiteralExpression(['Prashanth']);
        var c = new SUGAR.expressions.StringLiteralExpression(['Koushik']);
        var d = new SUGAR.expressions.StringLiteralExpression(['testing_t']);
        it('should return formatted name string', function() {
            name_format = 's f l t'; // jscs:ignore
            var res = new SUGAR.expressions.FormatedNameExpression([a, b, c, d], getSLContext(model));
            expect(res.evaluate()).toEqual(`${a.evaluate()} ${b.evaluate()} ${c.evaluate()} ${d.evaluate()}`);
        });
    });
});
