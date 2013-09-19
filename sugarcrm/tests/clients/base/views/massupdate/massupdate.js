describe("Base.View.Massupdate", function() {

    var view, app, layout, stub;

    beforeEach(function() {
        app = SugarTest.app;
        stub = sinon.stub(app.metadata, "getModule", function(){
            var moduleMetadata = { fields: {} };
            _.each(fixtures.metadata.modules.Contacts.fields, function(field, key){
                moduleMetadata.fields[key] = app.utils.deepCopy(field);
                moduleMetadata.fields[key].massupdate = true;
            });
            moduleMetadata.fields.test_boolean_field = {
                name: 'test_boolean_field',
                type: 'bool',
                vname: 'Test Boolean',
                options: 'test_dom',
                massupdate: true
            };
            moduleMetadata.fields.team_name = {
                name: 'team_name',
                type: 'teamset',
                massupdate: true
            };
            _.extend({}, fixtures.metadata.modules.Contacts.fields, moduleMetadata);
            return moduleMetadata;
        });
        layout = SugarTest.createLayout('base', 'Contacts', 'list');
        view = SugarTest.createView("base", "Contacts", "massupdate", {}, null, null, layout);
        view.model = new Backbone.Model();
    });


    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view.model = null;
        view = null;
        stub.restore();
        layout.dispose();
    });


    it("should generate its fields from metadata massupdate value", function() {
        var expected = view.meta.panels[0].fields.length,
            actual = _.size(_.filter(app.metadata.getModule('Contacts').fields, function(field) {
                return field.massupdate;
            }));


        expect(actual).toEqual(expected);
    });

    it("should convert boolean fields into enum fields", function() {
        var enumField = _.find(view.meta.panels[0].fields, function(field) {
                return field.name === 'test_boolean_field';
            }),
            booleanFieldsInViewMeta = _.size(_.filter(view.meta.panels[0].fields, function(field) {
                return field.type === 'bool';
            })),
            booleanFieldsInModuleMeta = _.size(_.filter(app.metadata.getModule('Contacts').fields, function(field) {
                return field.type === 'bool';
            }));

        expect(enumField).toBeDefined();
        expect(enumField.type).toEqual('enum');
        expect(enumField.options).toEqual('checkbox_dom');
        expect(enumField.label).toEqual('Test Boolean');

        //Make sure the view did not override module metadata
        expect(booleanFieldsInViewMeta).toEqual(0);
        expect(booleanFieldsInModuleMeta).not.toEqual(0);
    });

    it("should convert team_name field into a fieldset field", function() {
        var enumField = _.find(view.meta.panels[0].fields, function(field) {
            return field.name === 'team_name';
        });

        expect(enumField).toBeDefined();
        expect(enumField.type).toEqual('fieldset');
        expect(enumField.fields).not.toBeEmpty();
        expect(enumField.fields[0]).toEqual({
            name: 'team_name',
            type: 'teamset',
            massupdate: true,
            css_class: 'span9'
        });
    });

    it("should set the default option by the first available fields", function(){
        view.setDefault();

        var actual = view.defaultOption,
            expected = app.metadata.getModule('Contacts').fields[_.first(_.keys(app.metadata.getModule('Contacts').fields))];

        _.each(actual, function(value, key){
            expect(actual[key]).toBe(expected[key]);
        });
        //available options, exclusive from assigned field values
    });

    it("should set available fields out of assigned field values", function(){
        view.setDefault();
        var options = view.fieldOptions.length;

        view.addUpdateField();
        var expected = options - 1,
            actual = view.fieldOptions.length;

        expect(actual).toEqual(expected);

        view.addUpdateField();
        view.addUpdateField();

        expected = expected - 2;
        actual = view.fieldOptions.length;

        expect(actual).toEqual(expected);
    });

    it("should add, remove, and/or replace field values", function(){
        view.setDefault();
        var selectedOption = view.defaultOption;

        view.addUpdateField();
        var nextSelectedOption = view.defaultOption;
        expect(_.contains(view.fieldValues, selectedOption)).toBeTruthy();
        expect(_.contains(view.fieldOptions, selectedOption)).toBeFalsy();

        view.removeUpdateField(0);
        expect(_.contains(view.fieldValues, selectedOption)).toBeFalsy();
        expect(_.contains(view.fieldOptions, selectedOption)).toBeTruthy();
        expect(view.defaultOption).toBe(nextSelectedOption);

        view.replaceUpdateField(selectedOption, 0);
        expect(view.defaultOption).toBe(selectedOption);
    });

    describe("Warning delete", function() {
        var sinonSandbox, alertShowStub, routerStub;
        beforeEach(function() {
            sinonSandbox = sinon.sandbox.create();
            routerStub = sinonSandbox.stub(app.router, "navigate");
            sinonSandbox.stub(Backbone.history, "getFragment");
            alertShowStub = sinonSandbox.stub(app.alert, "show");
        });

        afterEach(function() {
            sinonSandbox.restore();
        });

        it("should not alert warning message if _modelToDelete is not defined", function() {
            app.routing.triggerBefore("route");
            expect(alertShowStub).not.toHaveBeenCalled();
        });
        it("should return true if _modelToDelete is not defined", function() {
            sinonSandbox.stub(view, 'warnDelete');
            expect(view.beforeRouteDelete()).toBeTruthy();
        });
        it("should return false if _modelToDelete is defined (to prevent routing to other views)", function() {
            sinonSandbox.stub(view, 'warnDelete');
            view._modelsToDelete = new Backbone.Collection();
            expect(view.beforeRouteDelete()).toBeFalsy();
        });
        it("should redirect the user to the targetUrl", function() {
            var unbindSpy = sinonSandbox.spy(view, 'unbindBeforeRouteDelete');
            view._modelsToDelete = new Backbone.Collection();
            sinonSandbox.stub(view._modelsToDelete, 'fetch', function(options) {
                if (options.success) {
                    options.success({}, {status: 'done'});
                }
                return;
            });
            view._currentUrl = 'Accounts';
            view._targetUrl = 'Contacts';
            view.deleteModels();
            expect(unbindSpy).toHaveBeenCalled();
            expect(routerStub).toHaveBeenCalled();
        });
    });
});
