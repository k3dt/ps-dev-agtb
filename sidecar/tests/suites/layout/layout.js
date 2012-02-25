describe("Layout", function() {

    it('should create views', function () {
        expect(SUGAR.App.layout.get({
            view : "editView",
            module: "Contacts"
        })).not.toBe(null);
    });

    it('should create layouts', function () {
        expect(SUGAR.App.layout.get({
            layout : "edit",
            module: "Contacts"
        })).not.toBe(null);
    });
    it('should register sugar_field Handlebars helper', function () {
        expect(Handlebars.helpers.sugar_field).not.toBe(null);
    });
});

describe("Layout.View", function(){
    var syncResult, view, layout, html;
    SUGAR.App.metadata.set({sugarFields:sugarFieldsFixtures.fieldsData});

    var App = SUGAR.App.init({el: "#sidecar"});

    App.dataManager.declareModels(fixtures.metadata);
    //Need a sample Bean
    var bean = App.dataManager.createBean("Contacts", {
        first_name: "Foo",
        last_name: "Bar"
    });
    var collection = new App.BeanCollection([bean]);
    //Setup a context
    var context = SUGAR.App.context.getContext({
        url: "someurl",
        module: "Contacts",
        model : bean,
        collection : collection
    });

    it('should get metadata from the manager', function(){
        view = App.layout.get({
            context : context,
            view:"editView"
        });
        expect(view.meta).toEqual(fixtures.metadata.Contacts.views.editView);
    });

    it('should accept metadata overrides', function(){
        var testMeta = {
            "panels" : [{
                "label" : "TEST",
                "fields" : []
            }]
        }
        view = App.layout.get({
            context : context,
            view:"editView",
            meta: testMeta
        });
        expect(view.meta).toEqual(testMeta);
    })

    it('should retrieve the default context', function(){
        App.controller.context = context;
        view = App.layout.get({
            view:"editView"
        });
        expect(view.context).not.toBe(null);
        expect(view.context).toEqual(App.controller.context);
    })


    it('should render edit views', function(){
        view = App.layout.get({
            context : context,
            view:"editView"
        });
        expect(view.meta).toBeDefined();
        view.render();
        html = view.$el.html();
        expect(html).toContain('editView');
        expect(view.$el).toContain('input=[value="Foo"]');
    })

    it('should render detail views', function(){
        layout = App.layout.get({
            context : context,
            view:"detailView"
        });
        layout.render();
        html = layout.$el.html();
        expect(html).toContain('detailView');
    })

})

describe("Layout.Layout", function(){
    var syncResult, view, layout, html;
    //Fake the cache
    SUGAR.App.cache = SUGAR.App.cache || {
        get:function (key) {
            var parts = key.split(".");
            if (parts.length == 1){
                return fixtures[parts[0]]
            }

            return fixtures[parts[0]][parts[1]];
        }
    };
    //Fake template manager
    SUGAR.App.template = SUGAR.App.template || {
        get:function (key) {
            if (fixtures.templates[key])
                return Handlebars.compile(fixtures.templates[key]);
        }
    };
    //Fake a field list
    SUGAR.App.metadata.set({sugarFields:sugarFieldsFixtures.fieldsData});

    var App = SUGAR.App.init({el: "#sidecar"});
    App.dataManager.declareModels(fixtures.metadata);
    //Need a sample Bean
    var bean = App.dataManager.createBean("Contacts", {
        first_name: "Foo",
        last_name: "Bar"
    });
    var collection = new App.BeanCollection([bean]);
    //Setup a context
    var context = SUGAR.App.context.getContext({
        url: "someurl",
        module: "Contacts",
        model : bean,
        collection : collection
    });

    it('should get metadata from the manager', function(){
        layout = App.layout.get({
            context : context,
            layout: "edit"
        });
        expect(layout.meta).toEqual(fixtures.metadata.Contacts.layouts.edit);
    });

    it('should accept metadata overrides', function(){
        var testMeta = {
            //Default layout is a single view
            "type" : "simple",
            "components" : [
                {view : "testComp"}
            ]
        }
        layout = App.layout.get({
            context : context,
            layout: "edit",
            meta: testMeta
        });
        expect(layout.meta).toEqual(testMeta);
    });

    //TODO: Need to defined tests for sublayout, complex layouts, and inline defined layouts

})