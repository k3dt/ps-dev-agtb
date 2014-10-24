describe('View.Fields.Base.Meetings.TypeField', function() {
    var app, field, sandbox, createFieldProperties,
        module = 'Meetings';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'enum');
        SugarTest.testMetadata.set();
        sandbox = sinon.sandbox.create();
        createFieldProperties = {
            client: 'base',
            name: 'type',
            type: 'type',
            viewName: 'edit',
            module: module,
            loadFromModule: true
        };
        sandbox.stub(app.lang, 'getAppListStrings', function() {
            return {
                'foo': 'Foo',
                'bar': 'Bar',
                'baz': 'Baz'
            };
        });
    });

    afterEach(function() {
        sandbox.restore();
        if (field) {
            field.dispose();
        }
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
    });

    it('should add selected value to list if in main app list string and it was not there initially', function() {
        field = SugarTest.createField(createFieldProperties);
        field.items = {
            'foo': 'Foo'
        };
        field.model.set('type', 'baz');
        field.render();
        expect(field.items).toEqual({
            'foo': 'Foo',
            'baz': 'Baz'
        });
    });

    it('should not add selected value to list if not in main app list string', function() {
        field = SugarTest.createField(createFieldProperties);
        field.items = {
            'foo': 'Foo'
        };
        field.model.set('type', 'bap');
        field.render();
        expect(field.items).toEqual({
            'foo': 'Foo'
        });
    });

    it('should not add additional value to list if it is already in the list', function() {
        field = SugarTest.createField(createFieldProperties);
        field.items = {
            'foo': 'Foo',
            'bar': 'Bar'
        };
        field.model.set('type', 'bar');
        field.render();
        expect(field.items).toEqual({
            'foo': 'Foo',
            'bar': 'Bar'
        });
    });
});
