describe("EmailTemplates.View.ComposeTemplates", function() {
    var app,
        moduleName = 'EmailTemplates',
        listMeta,
        filterDef,
        view;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        filterDef = [{"$or":[{"type":{"$is_null":""}},{"type":{"$equals":""}},{"type":{"$equals":"email"}}]}];
        listMeta = {
            "type": "list",
            "filterDef" : filterDef,
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
        SugarTest.loadComponent("base", "view", "compose-templates", "EmailTemplates");
        SugarTest.testMetadata.set();
        view = SugarTest.createView("base", moduleName, "compose-templates", listMeta, null, true);
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        SugarTest.testMetadata.dispose();
    });

    it("should turn off sorting on all fields", function(){
        var allNonSortable;


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

        view.$el = $('<div>' + htmlBefore + '</div>');
        view._removeLinks();
        expect(view.$el.html()).toEqual(htmlAfter);
    });

    it("should be able to add preview rowaction with meta flag", function(){
        var view, previewField;
        listMeta['rowactions'] = {};
        listMeta['showPreview'] = true;
        //Create another view with the new metadata
        view = SugarTest.createView("base", moduleName, "compose-templates", listMeta);
        previewField = _.last(view.meta.panels[0].fields);
        expect(previewField.event).toEqual('list:preview:fire');
    });

    it("should be setting collections filter", function() {
        expect(view.collection.filterDef).toMatch(filterDef);
    });

});
