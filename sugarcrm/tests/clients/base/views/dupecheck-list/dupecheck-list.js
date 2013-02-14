describe("Base.View.DupeCheckList", function() {
    var app,
        moduleName = 'Contacts',
        listMeta;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        listMeta = {
            "type": "list",
            "panels":[
                {
                    "name":"panel_header",
                    "fields":[
                        {
                            "name":"first_name"
                        },
                        {
                            "name":"name"
                        },
                        {
                            "name":"status"
                        }
                    ]
                }
            ]
        };
        SugarTest.loadHandlebarsTemplate("list", "view", "base", "list");
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadComponent('base', 'view', 'recordlist');
        SugarTest.loadComponent("base", "view", "dupecheck-list");
        SugarTest.testMetadata.set();
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        SugarTest.testMetadata.dispose();
    });

    it("should turn off sorting on all fields", function(){
        var allNonSortable;

        var view = SugarTest.createView("base", moduleName, "dupecheck-list", listMeta);
        var fields = view.meta.panels[0].fields;

        expect(fields.length).toBeGreaterThan(0);
        allNonSortable = _.all(fields, function (field) {
            return (field.sortable === false);
        });
        expect(allNonSortable).toBeTruthy();
    });

    it("should removing all links except rowactions", function(){
        var htmlBefore = '<a href="javascript:void(0)">unwrapped</a><a href="" class="rowaction">wrapped</a>',
            htmlAfter = 'unwrapped<a href="" class="rowaction">wrapped</a>';

        var view = SugarTest.createView("base", moduleName, "dupecheck-list", listMeta);
        view.$el = $('<div>' + htmlBefore + '</div>');
        view._removeLinks();
        expect(view.$el.html()).toEqual(htmlAfter);
    });

    it("should be able to set the model via context", function(){
        var model, context, view;

        model = new Backbone.Model();
        model.set('foo', 'bar');
        context = app.context.getContext({
            module: moduleName,
            dupeCheckModel: model
        });
        context.prepare();

        view = SugarTest.createView("base", moduleName, "dupecheck-list", listMeta, context);
        expect(view.model.get('foo')).toEqual('bar');
    });

    it("should be able to add preview rowaction with meta flag", function(){
        var view, previewField;
        listMeta['rowactions'] = {};
        listMeta['showPreview'] = true;

        view = SugarTest.createView("base", moduleName, "dupecheck-list", listMeta);
        previewField = _.last(view.meta.panels[0].fields);
        expect(previewField.event).toEqual('list:preview:fire');
    });

    it("should be calling the duplicate check api", function() {
        var ajaxStub;
        var view = SugarTest.createView("base", moduleName, "dupecheck-list", listMeta);

        //mock out collectionSync which gets called by overridden sync
        view.collectionSync = function(method, model, options) {
            options.endpoint(options, {'success':$.noop});
        };

        ajaxStub = sinon.stub($, 'ajax', $.noop);

        view.fetchDuplicates(new Backbone.Model());
        expect(ajaxStub.lastCall.args[0].url).toMatch(/.*\/Contacts\/duplicateCheck/);

        ajaxStub.restore();
    });

});
